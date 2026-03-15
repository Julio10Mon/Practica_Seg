<?php
session_start();
include 'conexion/conexion.php';

// 1. Validar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// 2. Obtener datos básicos del usuario (Nombre, Email, Meta)
$stmt = $conexion->prepare("SELECT nombre, email, meta_profesional FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

// Definimos variables para usar en el HTML (con fallback por si están vacías)
$usuario = $userData['nombre'] ?? 'Usuario';
$email   = $userData['email'] ?? 'Sin correo';
$meta    = $userData['meta_profesional'] ?? null;

// Guardamos el nombre en la sesión por si acaso no estaba
$_SESSION['usuario_nombre'] = $usuario;

// 3. Obtener conteo de Certificaciones
$res_cert = $conexion->query("SELECT COUNT(*) as total FROM usuario_certificaciones WHERE usuario_id = $usuario_id");
$total_certificaciones = $res_cert->fetch_assoc()['total'] ?? 0;

// 4. Obtener conteo de Proyectos
$res_proy = $conexion->query("SELECT COUNT(*) as total FROM usuario_proyectos WHERE usuario_id = $usuario_id");
$total_proyectos = $res_proy->fetch_assoc()['total'] ?? 0;

// 5. Cálculo de Compatibilidad (Simple para el dashboard)
$compatibilidad = 0;
$meta_nombre_display = "Sin meta";

if ($meta) {
    // Si la meta es numérica (ID), buscamos el nombre y el ID
    if (is_numeric($meta)) {
        $meta_id = $meta;
        $stmt_m = $conexion->prepare("SELECT nombre FROM metas WHERE id = ?");
        $stmt_m->bind_param("i", $meta_id);
        $stmt_m->execute();
        $meta_nombre_display = $stmt_m->get_result()->fetch_assoc()['nombre'] ?? "Meta Desconocida";
        $stmt_m->close();
    } else {
        // Si es texto, buscamos el ID por nombre
        $meta_nombre_display = $meta;
        $stmt_m = $conexion->prepare("SELECT id FROM metas WHERE nombre = ?");
        $stmt_m->bind_param("s", $meta);
        $stmt_m->execute();
        $meta_id = $stmt_m->get_result()->fetch_assoc()['id'] ?? null;
        $stmt_m->close();
    }

    if ($meta_id) {
        $res_gap = $conexion->query("
            SELECT 
                ( (SELECT COUNT(*) FROM usuario_tecnologias WHERE usuario_id = $usuario_id AND tecnologia_id IN (SELECT tecnologia_id FROM meta_tecnologias WHERE meta_id = $meta_id)) / 
                (SELECT COUNT(*) FROM meta_tecnologias WHERE meta_id = $meta_id) ) * 100 as pct
        ");
        $row_gap = $res_gap->fetch_assoc();
        $compatibilidad = round($row_gap['pct'] ?? 0);
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SkillMapAI · Dashboard</title>
  <link rel="stylesheet" href="css/style3.css?v=<?= time() ?>">
</head>

<body class="page--dashboard app--dark">
  <div class="layout">

    <aside class="sidebar">
      <div class="sidebar__brand">
        <div class="brand__icon" aria-hidden="true">⚡</div>
        <div class="brand__text">
          <div class="brand__name">SkillMapAI</div>
          <div class="brand__tag">Potencia tu carrera</div>
        </div>
      </div>

      <nav class="sidebar__nav">
        <a class="nav__item is-active" href="dashboard.php">
          <span class="nav__icon" aria-hidden="true">▦</span>
          <span class="nav__label">Dashboard</span>
        </a>

        <a class="nav__item" href="perfil.php">
          <span class="nav__icon" aria-hidden="true">◉</span>
          <span class="nav__label">Mi Perfil</span>
        </a>

        <a class="nav__item" href="skill_gap.php">
          <span class="nav__icon" aria-hidden="true">◎</span>
          <span class="nav__label">Skill Gap</span>
        </a>

        <a class="nav__item" href="ruta_ia.php">
          <span class="nav__icon" aria-hidden="true">✦</span>
          <span class="nav__label">Ruta IA</span>
        </a>

        <a class="nav__item" href="tendencias.php">
          <span class="nav__icon" aria-hidden="true">↗︎</span>
          <span class="nav__label">Tendencias</span>
        </a>
      </nav>

      <div class="sidebar__footer">
        <div class="userchip">
          <div class="userchip__avatar"><?= strtoupper(substr($usuario, 0, 1)) ?></div>
          <div class="userchip__meta">
            <div class="userchip__name"><?= htmlspecialchars($usuario) ?></div>
            <div class="userchip__mail"><?= htmlspecialchars($email) ?></div>
          </div>
        </div>

        <a class="logout" href="logout.php">
          <span class="logout__icon" aria-hidden="true">⟵</span>
          <span>Cerrar sesión</span>
        </a>
      </div>
    </aside>

    <main class="main">

      <header class="pageheader">
        <div class="pageheader__left">
          <h1 class="pageheader__title">Hola, <?= htmlspecialchars($usuario) ?></h1>
          <p class="pageheader__subtitle">
            <?= $meta ? "Tu meta actual: <strong>$meta_nombre_display</strong>" : "Define tu meta profesional para comenzar" ?>
          </p>
        </div>

        <div class="pageheader__right">
          <a href="perfil.php" class="btn btn--primary">
            <span class="btn__icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M12 20h9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
              </svg>
            </span>
            Actualizar Perfil
          </a>
        </div>
      </header>

      <section class="grid grid--4">
        <article class="card stat">
          <div class="stat__head">
            <div class="stat__label">EMPLEABILIDAD</div>
            <div class="stat__iconbox" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M4 14l4-4 4 4 8-8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
          </div>
          <div class="stat__value"><?= $compatibilidad ?>%</div>
          <div class="stat__meta">Nivel general</div>
          <div class="stat__hint">↑ +5% este mes</div>
        </article>

        <article class="card stat">
          <div class="stat__head">
            <div class="stat__label">COMPATIBILIDAD</div>
            <div class="stat__iconbox" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M12 22c5.5 0 10-4.5 10-10S17.5 2 12 2 2 6.5 2 12s4.5 10 10 10Z" stroke="currentColor" stroke-width="1.7"/>
                <path d="M12 12 18 12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                <path d="M12 12 12 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
              </svg>
            </div>
          </div>
          <div class="stat__value"><?= $compatibilidad ?>%</div>
          <div class="stat__meta"><?= htmlspecialchars($meta_nombre_display) ?></div>
        </article>

        <article class="card stat">
          <div class="stat__head">
            <div class="stat__label">CERTIFICACIONES</div>
            <div class="stat__iconbox" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M12 2l3 5 6 1-4 4 1 6-6-3-6 3 1-6-4-4 6-1 3-5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
              </svg>
            </div>
          </div>
          <div class="stat__value"><?= $total_certificaciones ?></div>
          <div class="stat__meta">Obtenidas</div>
        </article>

        <article class="card stat">
          <div class="stat__head">
            <div class="stat__label">PROYECTOS</div>
            <div class="stat__iconbox" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M4 4h16v16H4V4Z" stroke="currentColor" stroke-width="1.7"/>
                <path d="M8 20V8h12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
              </svg>
            </div>
          </div>
          <div class="stat__value"><?= $total_proyectos ?></div>
          <div class="stat__meta">Completados</div>
        </article>
      </section>

      <div class="spacer-xxl"></div>
    </main>

  </div>
</body>
</html>