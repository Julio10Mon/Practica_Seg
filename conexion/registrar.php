<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $carrera = trim($_POST['carrera']);
    $universidad = trim($_POST['universidad']);
    $password = trim($_POST['password']);

    // 1. VALIDAR QUE SEA GMAIL (Seguridad en el servidor)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, '@gmail.com')) {
        header("Location: ../cuenta.php?error=email_invalido");
        exit();
    }

    // 2. VALIDAR LONGITUD
    if (strlen($password) < 6) {
        header("Location: ../cuenta.php?error=password");
        exit();
    }

    // 3. CONSULTA PREPARADA PARA VERIFICAR SI EL EMAIL YA EXISTE
    $stmt_check = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $res = $stmt_check->get_result();

    if ($res->num_rows > 0) {
        // El correo ya está registrado
        $stmt_check->close();
        header("Location: ../cuenta.php?error=email");
        exit();
    }
    $stmt_check->close();

    // 4. ENCRIPTAR Y GUARDAR
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt_insert = $conexion->prepare("INSERT INTO usuarios (nombre, email, carrera, universidad, password) VALUES (?, ?, ?, ?, ?)");
    $stmt_insert->bind_param("sssss", $nombre, $email, $carrera, $universidad, $passwordHash);

    if ($stmt_insert->execute()) {
        $stmt_insert->close();
        header("Location: ../cuenta.php?success=1");
        exit();
    } else {
        header("Location: ../cuenta.php?error=general");
        exit();
    }
}
?>