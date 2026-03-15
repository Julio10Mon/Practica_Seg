<?php
include("conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Validar formato Gmail
    if (!str_ends_with($email, '@gmail.com')) {
        header("Location: ../index.php?error=1");
        exit();
    }

    // Consulta preparada contra Inyecciones SQL
    $stmt = $conexion->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($user = $resultado->fetch_assoc()) {
        // Verificar contraseña con el hash
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true); // Previene fijación de sesión
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nombre'] = htmlspecialchars($user['nombre']);
             $_SESSION['usuario_email'] = $email;
            
            header("Location: ../dashboard.php");
            exit();
        }
    }

    // Si llegó aquí, los datos son incorrectos
    header("Location: ../index.php?error=1");
    exit();
}
?>