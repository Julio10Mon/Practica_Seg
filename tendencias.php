<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

include("conexion/conexion.php");

$usuario_id     = $_SESSION['usuario_id'];
$usuario_nombre = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : '';
$usuario_email  = isset($_SESSION['usuario_email']) ? $_SESSION['usuario_email'] : '';

$usuario = $usuario_nombre;
$email   = $usuario_email;
$inicial = !empty($usuario) ? strtoupper(substr($usuario, 0, 1)) : '';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SkillMapAI · Tendencias</title>

  <!-- SOLO ESTE CSS PARA TENDENCIAS -->
  <link rel="stylesheet" href="css/style7.css?v=999">
</head>

<body class="app--dark page--tendencias">
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

        <a class="nav__item" href="ruta_ia.php">
          <span class="nav__ico" aria-hidden="true">✦</span>
          <span class="nav__label">Ruta IA</span>
        </a>

        <a class="nav__item nav__item--active" href="tendencias.php" aria-current="page">
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

        <a class="logout" href="logout.php">
          <span class="logout__ico" aria-hidden="true">⟵</span>
          <span class="logout__text">Cerrar sesión</span>
        </a>
      </div>
    </aside>

    <!-- MAIN -->
    <main class="main" role="main">
      <div class="container">

        <!-- HEADER -->
        <header class="pagehead">
          <div class="pagehead__left">
            <h1 class="pagehead__title">Tendencias del Mercado</h1>
            <p class="pagehead__subtitle">Explora habilidades en demanda, roles y tecnologías que están creciendo</p>
          </div>

          <div class="pagehead__right">
            <!-- Solo visual -->
            <div class="filters">
              <div class="select">
                <select class="select__control" disabled>
                  <option>Área: General</option>
                </select>
                <span class="select__chev" aria-hidden="true">▾</span>
              </div>

              <div class="select">
                <select class="select__control" disabled>
                  <option>País: MX</option>
                </select>
                <span class="select__chev" aria-hidden="true">▾</span>
              </div>
            </div>
          </div>
        </header>

        <!-- TOP GRID -->
        <section class="grid grid--3">
          <article class="card stat">
            <div class="stat__head">
              <div class="stat__label">ROL EN CRECIMIENTO</div>
              <div class="stat__iconbox">↗</div>
            </div>
            <div class="stat__value">Data Analyst</div>
            <div class="stat__meta">Alta demanda en vacantes</div>
            <div class="stat__hint">↑ crecimiento</div>
          </article>

          <article class="card stat">
            <div class="stat__head">
              <div class="stat__label">TECNOLOGÍA TOP</div>
              <div class="stat__iconbox">⚡</div>
            </div>
            <div class="stat__value">Python</div>
            <div class="stat__meta">Usada en datos y automatización</div>
            <div class="stat__hint">↑ tendencia</div>
          </article>

          <article class="card stat">
            <div class="stat__head">
              <div class="stat__label">SOFT SKILL</div>
              <div class="stat__iconbox">◎</div>
            </div>
            <div class="stat__value">Comunicación</div>
            <div class="stat__meta">Clave para roles junior</div>
            <div class="stat__hint">↑ demanda</div>
          </article>
        </section>

        <!-- MAIN GRID -->
        <section class="grid grid--2 grid--gap-lg mt-lg">
          <!-- LISTA DE TENDENCIAS -->
          <article class="card card--lg">
            <div class="card__head">
              <div class="card__titlewrap">
                <div class="badgeico" aria-hidden="true">↗</div>
                <h2 class="card__title">Habilidades más demandadas</h2>
              </div>
              <button class="btn btn--ghost" type="button" disabled>Actualizar</button>
            </div>

            <div class="bars">
              <div class="bar">
                <div class="bar__label">Python</div>
                <div class="bar__track"><div class="bar__fill w-95"></div></div>
              </div>
              <div class="bar">
                <div class="bar__label">SQL</div>
                <div class="bar__track"><div class="bar__fill w-92"></div></div>
              </div>
              <div class="bar">
                <div class="bar__label">JavaScript</div>
                <div class="bar__track"><div class="bar__fill w-88"></div></div>
              </div>
              <div class="bar">
                <div class="bar__label">Cloud / AWS</div>
                <div class="bar__track"><div class="bar__fill w-85"></div></div>
              </div>
              <div class="bar">
                <div class="bar__label">React</div>
                <div class="bar__track"><div class="bar__fill w-82"></div></div>
              </div>
              <div class="bar">
                <div class="bar__label">Power BI</div>
                <div class="bar__track"><div class="bar__fill w-80"></div></div>
              </div>
            </div>
          </article>

          <!-- CARD: ROLES SUGERIDOS -->
          <article class="card card--lg">
            <div class="card__head">
              <div class="card__titlewrap">
                <div class="badgeico" aria-hidden="true">◎</div>
                <h2 class="card__title">Roles sugeridos</h2>
              </div>
            </div>

            <div class="list">
              <div class="list__item">
                <div class="list__left">
                  <div class="pill">Junior</div>
                  <div class="list__title">Data Analyst</div>
                  <div class="list__meta">SQL · Excel · Python</div>
                </div>
                <button class="mini" type="button" disabled>Ver</button>
              </div>

              <div class="list__item">
                <div class="list__left">
                  <div class="pill">Junior</div>
                  <div class="list__title">Frontend Developer</div>
                  <div class="list__meta">HTML · CSS · JS · React</div>
                </div>
                <button class="mini" type="button" disabled>Ver</button>
              </div>

              <div class="list__item">
                <div class="list__left">
                  <div class="pill">Entry</div>
                  <div class="list__title">Cloud Support</div>
                  <div class="list__meta">Linux · Redes · AWS</div>
                </div>
                <button class="mini" type="button" disabled>Ver</button>
              </div>
            </div>
          </article>
        </section>

        <!-- EMPTY CARD -->
        <section class="card card--wide card--empty mt-lg">
          <div class="empty">
            <div class="empty__icon" aria-hidden="true">↗</div>
            <div class="empty__title">Conecta tu meta</div>
            <div class="empty__text">
              Si seleccionas una meta en <strong>Skill Gap</strong>, aquí te mostramos tendencias personalizadas para tu objetivo.
            </div>

            <div class="actions">
              <a class="btn btn--ghost" href="skill_gap.php">Ir a Skill Gap</a>
              <button class="btn btn--primary" type="button" disabled>Ver tendencias personalizadas</button>
            </div>
          </div>
        </section>

      </div>
    </main>
  </div>
</body>
</html>