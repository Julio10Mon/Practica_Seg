<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SkillTrack AI</title>
    <link rel="stylesheet" href="css/style1.css">
    <meta http-equiv="Cache-Control" content="no-store" />
</head>
<body>

    <div class="container">
        <h2 class="subtitle">Bienvenido a</h2>

        <h1 class="title">
            <span id="typing"></span><span class="cursor">|</span>
        </h1>

        <p class="description">
            <span id="subtext"></span>
            <span class="dots">
                <span class="square s1"></span>
                <span class="square s2"></span>
                <span class="square s3"></span>
            </span>
        </p>

       <div class="buttons">
            <button type="button" class="btn-primary" id="btn-login-toggle">Iniciar sesión</button>
            
            <button type="button" class="btn-secondary" onclick="window.location.href='cuenta.php'">
                Crear cuenta
            </button>
        </div>

        <div id="login-form-container" style="display: none; margin-top: 20px;">
            <form action="conexion/login_proceso.php" method="POST" autocomplete="off">
                <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                    <input type="email" name="email" placeholder="Correo electrónico" required 
                           style="padding: 10px; border-radius: 8px; border: 1px solid #28D1ED; background: #141B2E; color: white; outline: none;">
                    
                    <input type="password" name="password" placeholder="Contraseña" required 
                           style="padding: 10px; border-radius: 8px; border: 1px solid #28D1ED; background: #141B2E; color: white; outline: none;">
                </div>
                <button type="submit" class="btn-primary" style="width: 200px;">Entrar</button>
            </form>
        </div>

        <?php if (isset($_GET['error']) && $_GET['error'] == '1'): ?>
            <div id="error-message" style="color: #ff4d4d; margin-top: 15px; font-size: 14px;">
                Usuario o contraseña incorrectos.
            </div>
        <?php endif; ?>

    </div> <script src="js/script1.js"></script>
    
    <script>
        // Lógica de emergencia integrada
        const toggleBtn = document.getElementById('btn-login-toggle');
        const loginBox = document.getElementById('login-form-container');

        if (toggleBtn && loginBox) {
            toggleBtn.onclick = function() {
                if (loginBox.style.display === 'none' || loginBox.style.display === '') {
                    loginBox.style.display = 'block';
                } else {
                    loginBox.style.display = 'none';
                }
            };
        }

        // Si vienes de un error, abrirlo automáticamente
        const params = new URLSearchParams(window.location.search);
        if (params.has('error')) {
            loginBox.style.display = 'block';
        }
    </script>
</body>
</html>