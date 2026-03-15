<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Cuenta - SkillTrack AI</title>
    <link rel="stylesheet" href="css/style2.css">
</head>
<body>

<div class="form-container">

    <h1 class="form-title">
        Crear <span>Cuenta</span>
    </h1>

    <!-- MENSAJES DENTRO DEL FORMULARIO -->
    <?php if(isset($_GET['success'])): ?>
        <div class="alert-success">
            Usuario registrado correctamente.
            <a href="index.php">Inicia sesión aquí</a>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error']) && $_GET['error'] == 'email'): ?>
        <div class="alert-error">
            Ese correo ya está registrado.
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error']) && $_GET['error'] == 'general'): ?>
        <div class="alert-error">
            Ocurrió un error al registrar.
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error']) && $_GET['error'] == 'campos'): ?>
        <div class="alert-error">
            Todos los campos son obligatorios.
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error']) && $_GET['error'] == 'email_invalido'): ?>
        <div class="alert-error">
            El correo no es válido.
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error']) && $_GET['error'] == 'password'): ?>
        <div class="alert-error">
            La contraseña debe tener al menos 6 caracteres.
        </div>
    <?php endif; ?>

    <!-- FORMULARIO SIEMPRE VISIBLE -->
    <form id="registroForm" action="conexion/registrar.php" method="POST" autocomplete="off">

        <div class="input-group">
            <label>Nombre</label>
            <input type="text" name="nombre" placeholder="Ingresa tu nombre completo" required>
        </div>

        <div class="input-group">
    <label>Email</label>
    <input type="email" name="email" 
           placeholder="ejemplo@gmail.com" 
           pattern=".+@gmail\.com" 
           title="Solo se permiten correos de @gmail.com" 
           required>
</div>

        <div class="input-group">
            <label>Carrera</label>
            <input type="text" name="carrera" placeholder="Ingresa tu carrera" required>
        </div>

        <div class="input-group">
            <label>Universidad</label>
            <input type="text" name="universidad" placeholder="Nombre de tu universidad" required>
        </div>

        <div class="input-group">
            <label>Contraseña</label>
            <input type="password" name="password" placeholder="Crea una contraseña segura" required>
        </div>

        <button type="submit" class="btn-register">Registrarse</button>

    </form>

    <p class="back">
        ¿Ya tienes cuenta? 
        <a href="index.php">Volver</a>
    </p>

</div>

<script>
    // 1. Limpiar el formulario al cargar la página (especialmente al volver atrás)
    window.addEventListener('pageshow', function(event) {
        const form = document.getElementById('registroForm');
        if (form) {
            form.reset(); // Borra todos los campos de texto
        }
    });

    // 2. Limpiar la URL de los parámetros GET (?success o ?error) 
    // para que no se repitan si el usuario recarga la página
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.pathname);
    }
</script>

</body>
</html>