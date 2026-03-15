<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

include("conexion/conexion.php");

$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$usuario_email = $_SESSION['usuario_email'] ?? 'correo@ejemplo.com';

$usuario = $usuario_nombre;
$inicial = strtoupper(substr($usuario_nombre, 0, 1));
$email = $usuario_email;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SkillMapAI · Ruta IA</title>
  <link rel="stylesheet" href="css/style6.css?v=999">
</head>

<body class="app--dark page--ruta">
  <div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar" aria-label="Navegación">
      <div class="brand">
        <div class="brand__icon" aria-hidden="true">⚡</div>
        <div class="brand__text">
          <div class="brand__name">SkillMapAI</div>
          <div class="brand__tag">Potencia tu carrera</div>
        </div>
      </div>

      <nav class="nav">
        <a class="nav__item" href="dashboard.php">
          <span class="nav__ico" aria-hidden="true">▦</span>
          <span class="nav__label">Dashboard</span>
        </a>

        <a class="nav__item" href="perfil.php">
          <span class="nav__ico" aria-hidden="true">◉</span>
          <span class="nav__label">Mi Perfil</span>
        </a>

        <a class="nav__item" href="skill_gap.php">
          <span class="nav__ico" aria-hidden="true">◎</span>
          <span class="nav__label">Skill Gap</span>
        </a>

        <a class="nav__item nav__item--active" href="ruta_ia.php" aria-current="page">
          <span class="nav__ico" aria-hidden="true">✦</span>
          <span class="nav__label">Ruta IA</span>
        </a>

        <a class="nav__item" href="tendencias.php">
          <span class="nav__ico" aria-hidden="true">↗</span>
          <span class="nav__label">Tendencias</span>
        </a>
      </nav>

      <div class="sidebar__footer">
        <div class="userchip">
          <div class="userchip__avatar" aria-hidden="true"><?= $inicial ?></div>
          <div class="userchip__info">
            <div class="userchip__name"><?= htmlspecialchars($usuario) ?></div>
            <div class="userchip__mail"><?= htmlspecialchars($email) ?></div>
          </div>
        </div>

        <a class="logout" href="conexion/logout.php">
          <span class="logout__ico" aria-hidden="true">⟵</span>
          <span class="logout__text">Cerrar sesión</span>
        </a>
      </div>
    </aside>

    <!-- MAIN -->
    <main class="main" role="main">
      <div class="container">

        <header class="pagehead">
          <div class="pagehead__left">
            <h1 class="pagehead__title">Ruta IA Personalizada</h1>
            <p class="pagehead__subtitle">Tu plan paso a paso para mejorar habilidades y alcanzar tu meta</p>
          </div>

          <button class="btn btn--primary" disabled>Generar ruta</button>
        </header>

        <section class="card card--wide">
          <div class="card__body">
            <div class="grid2">
              <div class="statbox">
                <div class="statbox__label">Meta seleccionada</div>
                <div class="statbox__value">Sin meta</div>
                <div class="statbox__meta">Ve a Skill Gap para elegir una</div>
              </div>

              <div class="statbox">
                <div class="statbox__label">Progreso</div>
                <div class="statbox__value">0%</div>
                <div class="statbox__meta">Aún no hay tareas</div>
              </div>

              <div class="statbox">
                <div class="statbox__label">Tareas</div>
                <div class="statbox__value">0</div>
                <div class="statbox__meta">Pendientes</div>
              </div>

              <div class="statbox">
                <div class="statbox__label">Tiempo estimado</div>
                <div class="statbox__value">-</div>
                <div class="statbox__meta">Se calcula al generar ruta</div>
              </div>
            </div>
          </div>
        </section>

        <section class="card card--wide card--empty">
          <div class="empty">
            <div class="empty__icon" aria-hidden="true">✦</div>
            <div class="empty__title">Aún no hay Ruta IA</div>
            <div class="empty__text">
              Primero selecciona una meta en <strong>Skill Gap</strong>.
            </div>
            <div class="actions">
              <a class="btn btn--ghost" href="skill_gap.php">Ir a Skill Gap</a>
              <button class="btn btn--primary" disabled>Generar ruta</button>
            </div>
          </div>
        </section>

      </div>
    </main>
  </div>
</body>
</html>