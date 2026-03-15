<?php  
// skill_gap.php (funcional)  
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];

include("conexion/conexion.php");
  
$usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';  
$email = $_SESSION['usuario_email'] ?? 'correo@ejemplo.com';  
$inicial = strtoupper(substr($usuario, 0, 1));  
  
$meta_id = null;  
$meta_nombre = null;  
$porcentaje = 0;  
$faltantes = [];  
$chart_data = null;  
  
// Guardar meta seleccionada  
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['meta_id'])) {  
    $meta_id = (int)$_POST['meta_id'];  
    // Actualizar meta_profesional en usuarios  
    $stmt = $conexion->prepare('UPDATE usuarios SET meta_profesional = ? WHERE id = ?');  
    $stmt->bind_param('si', $meta_id, $_SESSION['usuario_id']);  
    $stmt->execute();  
    $stmt->close();  
    // Redirigir para evitar reenvío  
    header('Location: skill_gap.php?analizado=1');  
    exit;  
}  
  
// Si se ha analizado, obtener meta_id desde usuarios  
if (isset($_GET['analizado'])) {  
    $stmt = $conexion->prepare('SELECT meta_profesional FROM usuarios WHERE id = ?');  
    $stmt->bind_param('i', $_SESSION['usuario_id']);  
    $stmt->execute();  
    $stmt->bind_result($meta_id);  
    $stmt->fetch();  
    $stmt->close();  
}  
  
if ($meta_id) {  
    // Obtener nombre de la meta  
    $stmt = $conexion->prepare('SELECT nombre FROM metas WHERE id = ?');  
    $stmt->bind_param('i', $meta_id);  
    $stmt->execute();  
    $stmt->bind_result($meta_nombre);  
    $stmt->fetch();  
    $stmt->close();  
  
    // Obtener tecnologías requeridas (IDs)  
    $stmt = $conexion->prepare('SELECT tecnologia_id FROM meta_tecnologias WHERE meta_id = ?');  
    $stmt->bind_param('i', $meta_id);  
    $stmt->execute();  
    $req_result = $stmt->get_result();  
    $requeridas = [];  
    while ($row = $req_result->fetch_assoc()) {  
        $requeridas[] = (int)$row['tecnologia_id'];  
    }  
    $stmt->close();  
  
    // Obtener tecnologías del usuario (IDs)  
    $stmt = $conexion->prepare('SELECT tecnologia_id FROM usuario_tecnologias WHERE usuario_id = ?');  
    $stmt->bind_param('i', $_SESSION['usuario_id']);  
    $stmt->execute();  
    $user_result = $stmt->get_result();  
    $usuario_techs = [];  
    while ($row = $user_result->fetch_assoc()) {  
        $usuario_techs[] = (int)$row['tecnologia_id'];  
    }  
    $stmt->close();  
  
    // Si no hay datos en usuario_tecnologias, usar simulados (temporal)  
    if (empty($usuario_techs)) {  
        $usuario_techs = [1, 3]; // IDs simulados  
    }  
  
    // Comparar por ID  
    $coincidencias = count(array_intersect($requeridas, $usuario_techs));  
    $total_requeridas = count($requeridas);  
    $porcentaje = $total_requeridas > 0 ? round(($coincidencias / $total_requeridas) * 100, 1) : 0;  
    $faltantes_ids = array_diff($requeridas, $usuario_techs);  
  
    // Nombres de tecnologías faltantes  
    if (!empty($faltantes_ids)) {  
        $placeholders = implode(',', array_fill(0, count($faltantes_ids), '?'));  
        $stmt = $conexion->prepare("SELECT nombre FROM tecnologias_base WHERE id IN ($placeholders)");  
        $types = str_repeat('i', count($faltantes_ids));  
        $stmt->bind_param($types, ...$faltantes_ids);  
        $stmt->execute();  
        $falt_result = $stmt->get_result();  
        while ($row = $falt_result->fetch_assoc()) {  
            $faltantes[] = htmlspecialchars($row['nombre']);  
        }  
        $stmt->close();  
    }  
  
    // Datos para Chart.js Doughnut  
    $chart_data = [  
        'compatibilidad' => $porcentaje,  
        'brecha' => 100 - $porcentaje,  
    ];  
}  
?>  
<!doctype html>  
<html lang="es">  
<head>  
  <meta charset="UTF-8" />  
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />  
  <title>SkillMapAI · Skill Gap</title>  
  <link rel="stylesheet" href="css/style5.css?v=999">  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>  
</head>  
<body class="app--dark page--skillgap">  
  <div class="app">  
    <!-- SIDEBAR (igual que antes) -->  
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
        <a class="nav__item nav__item--active" href="skill_gap.php" aria-current="page">  
          <span class="nav__ico" aria-hidden="true">◎</span>  
          <span class="nav__label">Skill Gap</span>  
        </a>  
        <a class="nav__item" href="ruta_ia.php">  
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
            <h1 class="pagehead__title">Análisis de Brecha de Habilidades</h1>  
            <p class="pagehead__subtitle">Compara tus habilidades con tu perfil profesional objetivo</p>  
          </div>  
        </header>  
  
        <!-- CARD: META OBJETIVO -->  
        <section class="card card--wide">  
          <div class="card__head">  
            <div class="card__titlewrap">  
              <div class="badgeico" aria-hidden="true">◎</div>  
              <h2 class="card__title">Meta Profesional Objetivo</h2>  
            </div>  
          </div>  
          <div class="card__body">  
            <form method="POST" action="skill_gap.php">  
              <div class="formrow">  
                <label class="label">Selecciona tu meta profesional</label>  
                <div class="select">  
                  <select name="meta_id" class="select__control" required>  
                    <option value="">Selecciona tu meta profesional</option>  
                    <?php  
                    $metas = $conexion->query('SELECT id, nombre FROM metas ORDER BY nombre');  
                    while ($row = $metas->fetch_assoc()):  
                    ?>  
                    <option value="<?= $row['id'] ?>" <?= (isset($meta_id) && $meta_id == $row['id']) ? 'selected' : '' ?>>  
                      <?= htmlspecialchars($row['nombre']) ?>  
                    </option>  
                    <?php endwhile; ?>  
                  </select>  
                  <span class="select__chev" aria-hidden="true">▾</span>  
                </div>  
                <button type="submit" class="btn btn--primary">Guardar y analizar</button>  
              </div>  
            </form>  
          </div>  
        </section>  
  
        <?php if ($chart_data !== null): ?>  
        <!-- RESULTADOS -->  
        <section class="card card--wide">  
          <div class="card__head">  
            <div class="card__titlewrap">  
              <div class="badgeico" aria-hidden="true">📊</div>  
              <h2 class="card__title">Resultados para: <?= htmlspecialchars($meta_nombre) ?></h2>  
            </div>  
          </div>  
          <div class="card__body">  
            <div class="grid2">  
              <div>  
                <h3>Compatibilidad</h3>  
                <p style="font-size: 2rem; font-weight: bold;"><?= $porcentaje ?>%</p>  
              </div>  
              <div>  
                <canvas id="skillGapChart" width="200" height="200"></canvas>  
              </div>  
            </div>  
            <?php if (!empty($faltantes)): ?>  
            <div class="mt-lg">  
              <h4>Tecnologías faltantes</h4>  
              <ul>  
                <?php foreach ($faltantes as $tech): ?>  
                <li><?= $tech ?></li>  
                <?php endforeach; ?>  
              </ul>  
            </div>  
            <?php endif; ?>  
          </div>  
        </section>  
        <?php endif; ?>  
  
      </div>  
    </main>  
  </div>  
  
  <?php if ($chart_data !== null): ?>  
  <script>  
    document.addEventListener('DOMContentLoaded', function () {  
      const ctx = document.getElementById('skillGapChart').getContext('2d');  
      new Chart(ctx, {  
        type: 'doughnut',  
        data: {  
          labels: ['Compatibilidad', 'Brecha restante'],  
          datasets: [{  
            data: [<?= $chart_data['compatibilidad'] ?>, <?= $chart_data['brecha'] ?>],  
            backgroundColor: ['#00e6c2', '#374151'],  
            borderWidth: 0,  
          }],  
        },  
        options: {  
          responsive: true,  
          maintainAspectRatio: false,  
          plugins: {  
            legend: {  
              position: 'bottom',  
              labels: { color: '#e9f1ff' }  
            },  
            tooltip: {  
              callbacks: {  
                label: function(context) {  
                  return context.label + ': ' + context.parsed + '%';  
                }  
              }  
            }  
          }  
        }  
      });  
    });  
  </script>  
  <?php endif; ?>  
</body>  
</html>