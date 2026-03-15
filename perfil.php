<?php

session_start();
require_once 'conexion/conexion.php';

/* =========================
   1) Validar sesión
   ========================= */
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}
$usuario_id = (int)$_SESSION['usuario_id'];


/* =========================
   PROCESAR ELIMINACIÓN (GET)
   ========================= */
if (isset($_GET['delete_tech'])) {
    $id_a_eliminar = (int)$_GET['delete_tech'];
    $stmt = $conexion->prepare("DELETE FROM usuario_tecnologias WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id_a_eliminar, $usuario_id);
    
    if ($stmt->execute()) {
        header("Location: perfil.php?tab=" . ($_GET['tab'] ?? 'lenguajes'));
        exit();
    }
}


/* =========================
   ELIMINAR HABILIDAD (GET)
   ========================= */
if (isset($_GET['delete_hab'])) {
    $id_h_eliminar = (int)$_GET['delete_hab'];
    $stmt = $conexion->prepare("DELETE FROM usuario_habilidades WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id_h_eliminar, $usuario_id);
    
    if ($stmt->execute()) {
        header("Location: perfil.php?tab=habilidades");
        exit();
    }
}

/* =========================
   ELIMINAR CERTIFICACIÓN
   ========================= */
if (isset($_GET['delete_cert'])) {
    $id_c = (int)$_GET['delete_cert'];
    $stmt = $conexion->prepare("DELETE FROM usuario_certificaciones WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id_c, $usuario_id);
    
    if ($stmt->execute()) {
        header("Location: perfil.php?tab=certificaciones");
        exit();
    }
}

/* =========================
   LÓGICA PARA ELIMINAR PROYECTO
   ========================= */
if (isset($_GET['delete_proj'])) {
    $id_p_eliminar = (int)$_GET['delete_proj'];
    
    // Preparamos la consulta para asegurar que solo borre proyectos del usuario logueado
    $stmt = $conexion->prepare("DELETE FROM usuario_proyectos WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id_p_eliminar, $usuario_id);
    
    if ($stmt->execute()) {
        // Redirigimos de vuelta a la pestaña de proyectos para ver el cambio
        header("Location: perfil.php?tab=proyectos");
        exit();
    } else {
        echo "Error al eliminar: " . $conexion->error;
    }
}

/* =========================
   2) Asegurar nombre en sesión
   ========================= */
if (!isset($_SESSION['usuario_nombre']) || $_SESSION['usuario_nombre'] === '') {
    $stmt_n = $conexion->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmt_n->bind_param("i", $usuario_id);
    $stmt_n->execute();
    $res_n = $stmt_n->get_result();
    if ($row_n = $res_n->fetch_assoc()) {
        $_SESSION['usuario_nombre'] = $row_n['nombre'];
    } else {
        $_SESSION['usuario_nombre'] = "Usuario";
    }
    $stmt_n->close();
}

$usuario     = (string)$_SESSION['usuario_nombre'];
$tab_activa  = $_GET['tab'] ?? 'perfil';
$tabs_validas = ['perfil','lenguajes','db','habilidades','certificaciones','proyectos'];
if (!in_array($tab_activa, $tabs_validas, true)) $tab_activa = 'perfil';

/* =========================
   3) Datos del usuario
   ========================= */
$stmt = $conexion->prepare("
    SELECT nombre, email, universidad, carrera, semestre, meta_profesional
    FROM usuarios
    WHERE id = ?
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$uData = $stmt->get_result()->fetch_assoc() ?: [];
$stmt->close();

$email       = $uData['email'] ?? "-";
$universidad = $uData['universidad'] ?? "-";
$carrera     = $uData['carrera'] ?? "-";
$semestre    = $uData['semestre'] ?? "-";
$meta        = $uData['meta_profesional'] ?? "-";


/* =====================================================
   PROCESAR FORMULARIOS
   ===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* =========================
       1) GUARDAR TECNOLOGÍA
       ========================= */
    if (isset($_POST['btn_agregar_tech'])) {

        $tech_id = (int)($_POST['tecnologia_id'] ?? 0);
        $pct     = (int)($_POST['porcentaje'] ?? 0);
        $tab     = $_POST['active_tab'] ?? 'perfil';
        if (!in_array($tab, $tabs_validas, true)) $tab = 'perfil';

        if ($tech_id > 0 && $pct >= 0 && $pct <= 100) {
            $stmtT = $conexion->prepare("
                INSERT INTO usuario_tecnologias (usuario_id, tecnologia_id, porcentaje_dominio)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE porcentaje_dominio = VALUES(porcentaje_dominio)
            ");
            $stmtT->bind_param("iii", $usuario_id, $tech_id, $pct);
            $stmtT->execute();
            $stmtT->close();
        }

        header("Location: perfil.php?tab=" . urlencode($tab));
        exit();
    }

    /* =========================
       2) GUARDAR HABILIDAD
       ========================= */
    if (isset($_POST['btn_agregar_habilidad'])) {

        $nombre     = trim($_POST['nombre_habilidad'] ?? '');
        $categoria  = trim($_POST['categoria'] ?? '');
        $nivel      = (int)($_POST['nivel_dominio'] ?? 0);
        $url        = trim($_POST['url_evidencia'] ?? '');

        if ($nombre !== '' && $nivel >= 0 && $nivel <= 100) {
            // Normaliza vacíos opcionales
            if ($categoria === '') $categoria = 'Habilidad blanda';
            if ($url === '') $url = null;

            $stmtH = $conexion->prepare("
                INSERT INTO usuario_habilidades
                (usuario_id, nombre_habilidad, categoria, nivel_dominio, url_evidencia)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmtH->bind_param("issis", $usuario_id, $nombre, $categoria, $nivel, $url);
            $stmtH->execute();
            $stmtH->close();
        }

        header("Location: perfil.php?tab=habilidades");
        exit();
    }

    /* =========================
       3) GUARDAR CERTIFICACIÓN
       ========================= */
    if (isset($_POST['btn_agregar_certificacion'])) {

        $nombre   = trim($_POST['nombre_cert'] ?? '');
        $org      = trim($_POST['organizacion'] ?? '');
        $fecha_o  = $_POST['fecha_obt'] ?? '';
        $fecha_e  = !empty($_POST['fecha_exp']) ? $_POST['fecha_exp'] : null;
        $id_cred  = !empty($_POST['id_credencial']) ? trim($_POST['id_credencial']) : null;
        $url_ver  = !empty($_POST['url_verificacion']) ? trim($_POST['url_verificacion']) : null;
        $ruta_pdf = null;

        // Upload (pdf/png/jpg/jpeg)
        if (isset($_FILES['archivo_cert']) && $_FILES['archivo_cert']['error'] === UPLOAD_ERR_OK) {
            $extension  = strtolower(pathinfo($_FILES['archivo_cert']['name'], PATHINFO_EXTENSION));
            $permitidos = ['pdf', 'png', 'jpg', 'jpeg'];

            if (in_array($extension, $permitidos, true)) {
                if (!is_dir("uploads")) {
                    mkdir("uploads", 0755, true);
                }
                $nuevo_nombre = "cert_" . time() . "_" . $usuario_id . "." . $extension;
                $ruta_destino = "uploads/" . $nuevo_nombre;

                if (move_uploaded_file($_FILES['archivo_cert']['tmp_name'], $ruta_destino)) {
                    $ruta_pdf = $ruta_destino;
                }
            }
        }

        if ($nombre !== '' && $org !== '' && $fecha_o !== '') {
            $stmtC = $conexion->prepare("
                INSERT INTO usuario_certificaciones
                (usuario_id, nombre_certificacion, organizacion_emisora,
                 fecha_obtencion, fecha_expiracion, id_credencial, url_verificacion, archivo_ruta)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtC->bind_param(
                "isssssss",
                $usuario_id,
                $nombre,
                $org,
                $fecha_o,
                $fecha_e,
                $id_cred,
                $url_ver,
                $ruta_pdf
            );
            $stmtC->execute();
            $stmtC->close();
        }

        header("Location: perfil.php?tab=certificaciones");
        exit();
    }

    /* =========================
       4) GUARDAR PROYECTO
       ========================= */
    if (isset($_POST['btn_proyecto'])) {

        $titulo     = trim($_POST['titulo_proyecto'] ?? '');
        $desc       = trim($_POST['descripcion'] ?? '');
        $rol        = trim($_POST['tu_rol'] ?? '');
        $estado     = trim($_POST['estado'] ?? '');
        $f_inicio   = $_POST['fecha_inicio'] ?? '';
        $url_p      = trim($_POST['url_proyecto'] ?? '');
        $url_r      = trim($_POST['url_repositorio'] ?? '');
        $ruta_img   = null;

        // Normaliza opcionales
        if ($rol === '') $rol = null;
        if ($estado === '') $estado = null;
        if ($url_p === '') $url_p = null;
        if ($url_r === '') $url_r = null;

        // Upload imagen (solo imagen)
        if (isset($_FILES['imagen_proyecto']) && $_FILES['imagen_proyecto']['error'] === UPLOAD_ERR_OK) {

            $mime = mime_content_type($_FILES['imagen_proyecto']['tmp_name']);
            $mimePermitidos = ['image/jpeg','image/png','image/webp','image/gif'];

            if (in_array($mime, $mimePermitidos, true)) {

                if (!is_dir("uploads/proyectos")) {
                    mkdir("uploads/proyectos", 0755, true);
                }

                $extension = strtolower(pathinfo($_FILES['imagen_proyecto']['name'], PATHINFO_EXTENSION));
                $extPermitidas = ['jpg','jpeg','png','webp','gif'];

                if (in_array($extension, $extPermitidas, true)) {
                    $nombre_archivo = "proy_" . $usuario_id . "_" . time() . "." . $extension;
                    $ruta_destino   = "uploads/proyectos/" . $nombre_archivo;

                    if (move_uploaded_file($_FILES['imagen_proyecto']['tmp_name'], $ruta_destino)) {
                        $ruta_img = $ruta_destino;
                    }
                }
            }
        }

        if ($titulo !== '' && $f_inicio !== '') {
            $stmtP = $conexion->prepare("
                INSERT INTO usuario_proyectos
                (usuario_id, titulo, descripcion, rol, estado,
                 fecha_inicio, url_proyecto, url_repositorio, imagen_ruta)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtP->bind_param(
                "issssssss",
                $usuario_id,
                $titulo,
                $desc,
                $rol,
                $estado,
                $f_inicio,
                $url_p,
                $url_r,
                $ruta_img
            );

            $stmtP->execute();
            $stmtP->close();
        }

        header("Location: perfil.php?tab=proyectos");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SkillMapAI · Mi Perfil</title>
  <link rel="stylesheet" href="css/style4.css?v=<?= time() ?>">
</head>

<body class="page--perfil app--dark">
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
        <a href="dashboard.php" class="nav__item"><span class="nav__icon">▦</span><span class="nav__label">Dashboard</span></a>
        <a href="perfil.php" class="nav__item is-active"><span class="nav__icon">◉</span><span class="nav__label">Mi Perfil</span></a>
        <a href="skill_gap.php" class="nav__item"><span class="nav__icon">◎</span><span class="nav__label">Skill Gap</span></a>
        <a href="ruta_ia.php" class="nav__item"><span class="nav__icon">✦</span><span class="nav__label">Ruta IA</span></a>
        <a href="tendencias.php" class="nav__item"><span class="nav__icon">↗︎</span><span class="nav__label">Tendencias</span></a>
      </nav>

      <div class="sidebar__footer">
        <div class="userchip">
          <div class="userchip__avatar"><?= strtoupper(substr($usuario, 0, 1)) ?></div>
          <div class="userchip__info">
            <div class="userchip__name"><?= htmlspecialchars($usuario) ?></div>
            <div class="userchip__mail"><?= htmlspecialchars($email) ?></div>
          </div>
        </div>
        <a href="logout.php" class="logout">
          <span class="logout__ico">⟵</span><span class="logout__text">Cerrar sesión</span>
        </a>
      </div>
    </aside>

    <main class="main">
      <div class="pageheader">
        <h1 class="pageheader__title">Gestión de Perfil</h1>
      </div>

      <div class="profile-tabs">
        <button class="tab <?= $tab_activa=='perfil'?'is-active':'' ?>" onclick="switchTab(event, 'perfil')"><span>👤</span> Perfil</button>
        <button class="tab <?= $tab_activa=='lenguajes'?'is-active':'' ?>" onclick="switchTab(event, 'lenguajes')"><span>&lt;/&gt;</span> Lenguajes</button>
        <button class="tab <?= $tab_activa=='db'?'is-active':'' ?>" onclick="switchTab(event, 'db')"><span>📂</span> Base de datos</button>
        <button class="tab <?= $tab_activa=='habilidades'?'is-active':'' ?>" onclick="switchTab(event, 'habilidades')"><span>⚡</span> Habilidades</button>
        <button class="tab <?= $tab_activa=='certificaciones'?'is-active':'' ?>" onclick="switchTab(event, 'certificaciones')"><span>🏅</span> Certificaciones</button>
        <button class="tab <?= $tab_activa=='proyectos'?'is-active':'' ?>" onclick="switchTab(event, 'proyectos')"><span>💼</span> Proyectos</button>
      </div>

      <!-- =========================
           TAB: PERFIL
           ========================= -->
      <div id="perfil" class="tab-content <?= $tab_activa=='perfil'?'is-active':'' ?>">
        <div class="card card--lg mt-lg">
          <div class="profile-card-body">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
              <div style="display:flex; align-items:center; gap:10px;">
                <span style="color: var(--c-primary); font-size: 1.2rem;">🎓</span>
                <h3 style="margin:0; color:white; font-size:1.1rem;">Información personal</h3>
              </div>
            </div>

            <div class="profile-top" style="display:flex; align-items:center; gap:20px; margin-bottom:30px;">
              <div class="profile-avatar" style="width:80px; height:80px; font-size:2em; background:#3dd1ae; color:#0b0f1a; font-weight:bold; display:flex; align-items:center; justify-content:center; border-radius:50%;">
                <?= strtoupper(substr($usuario, 0, 1)) ?>
              </div>
              <div>
                <div class="profile-name" style="font-size:1.5em; font-weight:bold; color:white;"><?= htmlspecialchars($usuario) ?></div>
                <div class="profile-sub" style="color:#94a3b8;"><?= htmlspecialchars($email) ?></div>
              </div>
            </div>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap:20px; border-top:1px solid var(--c-border); padding-top:20px;">
              <div>
                <label style="color:#64748b; font-size:.85rem; display:block; margin-bottom:5px;">Universidad</label>
                <div style="color:white; font-weight:500;"><?= htmlspecialchars($universidad) ?></div>
              </div>
              <div>
                <label style="color:#64748b; font-size:.85rem; display:block; margin-bottom:5px;">Carrera</label>
                <div style="color:white; font-weight:500;"><?= htmlspecialchars($carrera) ?></div>
              </div>
              <div>
                <label style="color:#64748b; font-size:.85rem; display:block; margin-bottom:5px;">Semestre</label>
                <div style="color:white; font-weight:500;"><?= htmlspecialchars($semestre) ?></div>
              </div>
              <div>
                <label style="color:#64748b; font-size:.85rem; display:block; margin-bottom:5px;">Meta Profesional</label>
                <div style="color:white; font-weight:500; line-height:1.4;"><?= htmlspecialchars($meta) ?></div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- =========================
           TABS: LENGUAJES / DB
           ========================= -->
      <?php foreach (['lenguajes' => 'lenguaje', 'db' => 'base_datos'] as $id => $tipo): ?>
        <div id="<?= $id ?>" class="tab-content <?= $tab_activa == $id ? 'is-active' : '' ?>">
          <div class="card card--lg mt-lg">


            <div class="header-section">
              <div>
                <h3 class="section-title">
                  <span><?= $tipo == 'lenguaje' ? '</>' : '🗄️' ?></span>
                  <?= $tipo == 'lenguaje' ? '&lt;/&gt;</span>Lenguajes de Programación' : 'Bases de Datos' ?>
                </h3>
                <p class="section-subtitle">Gestiona tus conocimientos técnicos y su nivel de dominio.</p>
              </div>
              <button class="btn-green-add" onclick="toggleForm('form_<?= $id ?>')">+ Agregar</button>
            </div>

  <div class="tech-grid">
  <?php
  // Aseguramos que la consulta traiga el 'id' único de la tabla intermedia
  $stmtGrid = $conexion->prepare("
    SELECT ut.id, ut.porcentaje_dominio, tb.nombre
    FROM usuario_tecnologias ut
    JOIN tecnologias_base tb ON ut.tecnologia_id = tb.id
    WHERE ut.usuario_id = ? AND tb.tipo = ?
    ORDER BY tb.nombre ASC
  ");
  $stmtGrid->bind_param("is", $usuario_id, $tipo);
  $stmtGrid->execute();
  $resGrid = $stmtGrid->get_result();

  while ($ut = $resGrid->fetch_assoc()):
  ?>
    <div class="skill-wrapper" style="display: flex; flex-direction: column; align-items: flex-end; gap: 5px; margin-bottom: 15px;">
      
      <div class="skill-card" style="width: 100%; margin-bottom: 0;">
        <div class="skill-info">
          <strong><?= htmlspecialchars($ut['nombre']) ?></strong>
          <span class="skill-pct"><?= (int)$ut['porcentaje_dominio'] ?> %</span>
        </div>
        <div class="progress-container-static">
          <div class="progress-bar-fill" style="width:<?= (int)$ut['porcentaje_dominio'] ?>%"></div>
        </div>
      </div>

      <div class="skill-actions" style="padding-right: 5px;">
        <button type="button" class="btn-delete-icon" title="Eliminar"
                onclick="eliminarTecnologia(<?= $ut['id'] ?>)">
          🗑️
        </button>
      </div>

    </div>
  <?php 
  endwhile; 
  $stmtGrid->close();
  ?>
</div>



            <div id="form_<?= $id ?>" class="form-container-styled" style="display:none;">
              <form method="POST">
                <input type="hidden" name="active_tab" value="<?= htmlspecialchars($id) ?>">
                <input type="hidden" name="id_edicion" id="id_edicion_<?= $id ?>" value="0">
    <input type="hidden" name="active_tab" value="<?= $tipo == 'lenguaje' ? 'lenguajes' : 'db' ?>">
    
    


                <div class="form-group">
                  <label>Nombre</label>
                  <select name="tecnologia_id" class="form-control-styled" required>
                    <option value="" disabled selected>Ej: Python, JavaScript...</option>
                    <?php
                    $stmtOpt = $conexion->prepare("
                      SELECT id, nombre
                      FROM tecnologias_base
                      WHERE tipo = ?
                      ORDER BY nombre ASC
                    ");
                    $stmtOpt->bind_param("s", $tipo);
                    $stmtOpt->execute();
                    $resOpt = $stmtOpt->get_result();
                    while ($t = $resOpt->fetch_assoc()):
                    ?>
                      <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
                    <?php endwhile;
                    $stmtOpt->close();
                    ?>
                  </select>
                </div>

                <div class="form-group">
    <label>Dominio: <span class="skill-pct">50 %</span></label>
    <input type="range" 
           name="porcentaje" 
           min="0" 
           max="100" 
           value="50"
           class="range-styled" 
           oninput="updateRange(this)">
</div>



                <div class="form-actions">
                  <button type="submit" name="btn_agregar_tech" class="btn-green-action">Agregar</button>
                  <button type="button" class="btn-cancel" onclick="toggleForm('form_<?= $id ?>')">Cancelar</button>
                </div>
              </form>
            </div>

          </div>
        </div>
      <?php endforeach; ?>

      <!-- =========================
           TAB: HABILIDADES
           ========================= -->
      <div id="habilidades" class="tab-content <?= $tab_activa == 'habilidades' ? 'is-active' : '' ?>">
        <div class="card card--lg mt-lg">
          <?php
          $res_count = $conexion->query("SELECT COUNT(*) as total FROM usuario_habilidades WHERE usuario_id = $usuario_id");
          $row_count = $res_count ? $res_count->fetch_assoc() : ['total' => 0];
          $total_habilidades = (int)($row_count['total'] ?? 0);
          ?>

          <div class="header-section">
            <div>
              <h3 class="section-title"><span class="icon-bolt">⚡</span> Habilidades Personales ( <?= $total_habilidades ?> )</h3>
              <p class="section-subtitle">Idiomas, deportes, hobbies, liderazgo, trabajo en equipo, etc.</p>
            </div>
            <button class="btn-green-add" onclick="showModal('modal_habilidad')">+ Agregar</button>
          </div>

          <div class="tech-grid">
            <?php
            $sql_h = "SELECT * FROM usuario_habilidades WHERE usuario_id = $usuario_id ORDER BY id DESC";
            $res_h = $conexion->query($sql_h);
           while ($h = $res_h->fetch_assoc()): ?>
  <div class="skill-item-container" style="position: relative; display: inline-block; width: 100%; max-width: 400px; margin-bottom: 25px;">
    
    <div class="skill-card" style="margin-bottom: 0;">
      <div class="skill-info">
        <div>
          <strong><?= htmlspecialchars($h['nombre_habilidad']) ?></strong>
          <div class="skill-category"><?= htmlspecialchars($h['categoria']) ?></div>
        </div>
        <span class="skill-pct-purple"><?= (int)$h['nivel_dominio'] ?> %</span>
      </div>

      <div class="progress-container-static">
        <div class="progress-bar-fill purple-fill" style="width:<?= (int)$h['nivel_dominio'] ?>%"></div>
      </div>
    </div>

    <button type="button" class="btn-delete-hab" title="Eliminar"
            onclick="eliminarHabilidad(<?= $h['id'] ?>)">
      🗑️
    </button>
    
  </div>
            <?php endwhile; ?>
          </div>

        </div>
      </div>

      <!-- MODAL: HABILIDAD (ÚNICO) -->
      <div id="modal_habilidad" class="modal">
        <div class="modal-content">
          <h2 class="modal-title">Nueva Habilidad</h2>
          <form method="POST">
            <input type="hidden" name="active_tab" value="habilidades">

            <div class="form-group">
              <label>Nombre de la habilidad</label>
              <input type="text" name="nombre_habilidad" class="form-control-styled" placeholder="Ej: Liderazgo..." required>
            </div>

            <div class="form-group">
              <label>Categoría</label>
              <select name="categoria" class="form-control-styled" required>
                <option value="Habilidad blanda">Habilidad blanda</option>
                <option value="Idioma">Idioma</option>
                <option value="Afición / Deporte">Afición / Deporte</option>
              </select>
            </div>

            <div class="form-group">
              <label>Nivel de dominio: <span id="val_hab" class="skill-pct">50%</span></label>
              <input type="range" name="nivel_dominio" min="0" max="100" value="50" class="range-styled" oninput="updateRange(this, 'hab')">
            </div>

            <div class="form-group">
              <label>URL de evidencia (opcional)</label>
              <input type="url" name="url_evidencia" class="form-control-styled" placeholder="https://...">
            </div>

            <div class="form-actions-modal">
              <button type="submit" name="btn_agregar_habilidad" class="btn-purple-action">+ Agregar Habilidad</button>
              <button type="button" class="btn-cancel-modal" onclick="hideModal('modal_habilidad')">Cancelar</button>
            </div>
          </form>
        </div>
      </div>

      <!-- =========================
           TAB: CERTIFICACIONES
           ========================= -->
      <div id="certificaciones" class="tab-content <?= $tab_activa == 'certificaciones' ? 'is-active' : '' ?>">
        <div class="card card--lg mt-lg">
          <?php
          $res_count = $conexion->query("SELECT COUNT(*) as total FROM usuario_certificaciones WHERE usuario_id = $usuario_id");
          $total_certs = (int)(($res_count ? $res_count->fetch_assoc()['total'] : 0) ?? 0);

          $sql_c = "SELECT * FROM usuario_certificaciones WHERE usuario_id = $usuario_id ORDER BY fecha_obtencion DESC";
          $res_c = $conexion->query($sql_c);
          ?>

          <div class="header-section">
            <div>
              <h3 class="section-title"><span class="icon-medal">🏅</span> Certificaciones ( <?= $total_certs ?> )</h3>
            </div>
            <button class="btn-green-add" onclick="showModal('modal_certificacion')">+ Agregar</button>
          </div>

          <?php if ($total_certs === 0): ?>
            <div class="empty-state">
              <div class="empty-icon">🏅</div>
              <p>No has agregado certificaciones aún</p>
            </div>
          <?php else: ?>
            <div class="certifications-grid">
              <?php while ($c = $res_c->fetch_assoc()): ?>
                <div class="cert-card" style="position: relative;"> <div class="cert-icon">📜</div>
    <div class="cert-details">
        <h4><?= htmlspecialchars($c['nombre_certificacion']) ?></h4>
        <p class="cert-org"><?= htmlspecialchars($c['organizacion_emisora']) ?></p>
        <p class="cert-date">Obtenido: <?= date('d/m/Y', strtotime($c['fecha_obtencion'])) ?></p>

        <div class="cert-links">
            <?php if (!empty($c['url_verificacion'])): ?>
                <a href="<?= htmlspecialchars($c['url_verificacion']) ?>" target="_blank" class="btn-verify">Verificar</a>
            <?php endif; ?>

            <?php if (!empty($c['archivo_ruta'])): ?>
                <a href="<?= htmlspecialchars($c['archivo_ruta']) ?>" target="_blank" class="btn-view-pdf">📄 Ver Certificado</a>
            <?php endif; ?>
        </div>
    </div>
    <button type="button" class="btn-delete-absolute" onclick="eliminarCert(<?= $c['id'] ?>)" title="Eliminar">
        🗑️
    </button>
</div>
                
              <?php endwhile; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- MODAL: CERTIFICACIÓN (ÚNICO) -->
      <div id="modal_certificacion" class="modal">
        <div class="modal-box" style="max-width: 700px;">
          <h2 style="margin-bottom: 20px;">Nueva Certificación</h2>

          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="active_tab" value="certificaciones">

            <div class="grid-2">
              <div class="form-group">
                <label>Nombre de la certificación</label>
                <input type="text" name="nombre_cert" class="form-control" placeholder="Ej: AWS Cloud Practitioner" required>
              </div>
              <div class="form-group">
                <label>Organización emisora</label>
                <input type="text" name="organizacion" class="form-control" placeholder="Ej: Amazon Web Services" required>
              </div>
            </div>

            <div class="grid-2" style="margin-top: 15px;">
              <div class="form-group">
                <label>Fecha de obtención</label>
                <input type="date" name="fecha_obt" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Fecha de expiración (opcional)</label>
                <input type="date" name="fecha_exp" class="form-control">
              </div>
            </div>

            <div class="grid-2" style="margin-top: 15px;">
              <div class="form-group">
                <label>ID de credencial</label>
                <input type="text" name="id_credencial" class="form-control" placeholder="ABC123XYZ">
              </div>
              <div class="form-group">
                <label>URL de verificación</label>
                <input type="url" name="url_verificacion" class="form-control" placeholder="https://...">
              </div>
            </div>

            <div class="form-group" style="margin-top: 20px;">
              <label>Archivo del certificado</label>
              <div class="upload-area" id="drop_zone" onclick="document.getElementById('file_input').click();">
                <span id="upload_icon">📤</span>
                <span id="upload_text">Subir certificado (PDF, PNG, JPG)</span>
                <input type="file" id="file_input" name="archivo_cert" accept=".pdf,.png,.jpg,.jpeg" style="display:none;">
                <p id="file_name_display" style="font-size:14px; color:#8b5cf6; margin-top:10px; font-weight:bold;"></p>
              </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:25px;">
              <button type="submit" name="btn_agregar_certificacion" class="btn-purple" style="flex:3;">🏅 Agregar Certificación</button>
              <button type="button" class="btn-secondary-cancel" onclick="hideModal('modal_certificacion')">Cancelar</button>
            </div>
          </form>
        </div>
      </div>

      <!-- =========================
           TAB: PROYECTOS
           ========================= -->
      <div id="proyectos" class="tab-content <?= $tab_activa=='proyectos'?'is-active':'' ?>">
        <div class="card card--lg mt-lg">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <h3>🚀 Portafolio de Proyectos</h3>
            <button onclick="showModal('modalProy')" class="btn-purple" style="width:auto; margin:0; padding:8px 15px;">+ Nuevo</button>
          </div>

          <div class="project-grid">
            <?php
            $stmtProj = $conexion->prepare("SELECT * FROM usuario_proyectos WHERE usuario_id = ? ORDER BY fecha_inicio DESC");
            $stmtProj->bind_param("i", $usuario_id);
            $stmtProj->execute();
            $resP = $stmtProj->get_result();

            while ($p = $resP->fetch_assoc()):
            ?>
              <div class="project-card" style="position: relative;">
    <?php if (!empty($p['imagen_ruta'])): ?>
        <img src="<?= htmlspecialchars($p['imagen_ruta']) ?>" alt="Proyecto">
    <?php endif; ?>

    <div style="padding:15px;">
        <h4 style="margin:0; padding-right: 25px;">
            <?= htmlspecialchars($p['titulo']) ?>
        </h4>

        <p style="font-size:.85em; color:#94a3b8;">
            <?= htmlspecialchars(mb_strimwidth($p['descripcion'] ?? '', 0, 100, "...")) ?>
        </p>

        <div style="margin-top:10px; display:flex; gap:12px; flex-wrap:wrap;">
            <?php if (!empty($p['url_proyecto'])): ?>
                <a class="btn-view-file"
                   href="<?= htmlspecialchars($p['url_proyecto']) ?>"
                   target="_blank"
                   rel="noopener noreferrer">
                   🔗 Ver proyecto
                </a>
            <?php endif; ?>

            <?php if (!empty($p['url_repositorio'])): ?>
                <a class="btn-view-file"
                   href="<?= htmlspecialchars($p['url_repositorio']) ?>"
                   target="_blank"
                   rel="noopener noreferrer">
                   🐙 GitHub
                </a>
            <?php endif; ?>
        </div>
    </div>

    <button type="button"
            class="btn-delete-absolute"
            onclick="eliminarProj(<?= $p['id'] ?>)"
            title="Eliminar">
        🗑️
    </button>
</div>

<?php endwhile; 
$stmtProj->close(); 
?>

      <!-- MODAL: PROYECTO (ÚNICO) -->
<div id="modalProy" class="modal" role="dialog" aria-labelledby="modalProyTitle" aria-hidden="true">
  <div class="modal-box dark-theme">
    <div class="modal-header">
      <h2 id="modalProyTitle">🚀 Nuevo Proyecto</h2>
      <button onclick="hideModal('modalProy')" class="close-btn" aria-label="Cerrar modal" title="Cerrar">
        &times;
      </button>
    </div>

    <form method="POST" enctype="multipart/form-data" novalidate>
      <div class="form-group">
        <label for="titulo_proyecto">Título del proyecto</label>
        <input 
          class="form-control" 
          type="text" 
          id="titulo_proyecto"
          name="titulo_proyecto" 
          placeholder="Ej: E-commerce Platform" 
          required
          maxlength="100"
          aria-describedby="titulo-help"
        >
        <small id="titulo-help" class="form-help">Máximo 100 caracteres</small>
      </div>

      <div class="form-group">
        <label for="descripcion">Descripción</label>
        <textarea 
          class="form-control" 
          id="descripcion"
          name="descripcion" 
          rows="3" 
          placeholder="Describe el proyecto, objetivos y resultados..."
          maxlength="500"
          aria-describedby="desc-help"
        ></textarea>
        <small id="desc-help" class="form-help">Máximo 500 caracteres</small>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label for="tu_rol">Tu rol</label>
          <input 
            class="form-control-styled" 
            type="text" 
            id="tu_rol"
            name="tu_rol" 
            placeholder="Ej: Full Stack Developer"
            maxlength="50"
          >
        </div>
        <div class="form-group">
          <label for="estado">Estado</label>
          <select class="form-control-styled" id="estado" name="estado">
            <option value="">Selecciona un estado</option>
            <option value="Completado">Completado</option>
            <option value="En progreso" selected>En progreso</option>
            <option value="Pausado">Pausado</option>
          </select>
        </div>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label for="fecha_inicio">Fecha de inicio</label>
          <input 
            class="form-control" 
            type="date" 
            id="fecha_inicio"
            name="fecha_inicio" 
            required
            min="2020-01-01"
          >
        </div>
        <div class="form-group">
          <label for="fecha_fin">Fecha de fin</label>
          <input 
            class="form-control" 
            type="date" 
            id="fecha_fin"
            name="fecha_fin"
            min="2020-01-01"
          >
        </div>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label for="url_proyecto">URL del proyecto</label>
          <input 
            class="form-control" 
            type="url" 
            id="url_proyecto"
            name="url_proyecto" 
            placeholder="https://ejemplo.com"
          >
        </div>
        <div class="form-group">
          <label for="url_repositorio">URL del repositorio</label>
          <input 
            class="form-control" 
            type="url" 
            id="url_repositorio"
            name="url_repositorio" 
            placeholder="https://github.com/usuario/repo"
          >
        </div>
      </div>

      <div class="form-group">
        <label for="fileProy">Imagen del proyecto</label>
        <div class="upload-area" id="drop-zone" role="button" tabindex="0" 
             onclick="document.getElementById('fileProy').click()" 
             onkeypress="handleKeyPress(event, 'fileProy')">
          <input 
            type="file" 
            name="imagen_proyecto" 
            id="fileProy" 
            hidden 
            accept="image/jpeg,image/png,image/webp"
            aria-describedby="upload-help"
          >
          <div id="upload-content">
            <span style="font-size:24px;" aria-hidden="true">📤</span>
            <p id="file-name" class="upload-text">Subir imagen (JPG, PNG, WebP)</p>
            <small id="upload-help" class="form-help">Máximo 5MB</small>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn-purple-lg" name="btn_proyecto">
          💼 Agregar Proyecto
        </button>
        <button type="button" onclick="hideModal('modalProy')" class="btn-cancel" aria-label="Cancelar">
          Cancelar
        </button>
      </div>
    </form>
  </div>
</div>

    </main>
  </div>

  <script src="js/main.js"></script>
</body>
</html>