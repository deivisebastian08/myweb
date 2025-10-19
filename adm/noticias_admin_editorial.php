<?php
session_start();
require_once __DIR__ . '/script/Supabase.php';

$slug = strtolower(str_replace(['_',' '], '-', $_SESSION['user_rol_slug'] ?? ''));
$rolId = isset($_SESSION['rol_id']) ? (int)$_SESSION['rol_id'] : null;
$isSuper = in_array($slug, ['super-admin','super_admin','admin-principal'], true) || ($rolId === 1);
$isAdmin = in_array($slug, ['administrador','admin'], true);
$isEditor = in_array($slug, ['editor'], true);
$isVisualizador = in_array($slug, ['visualizador'], true);
$isLector = in_array($slug, ['lector-informes'], true);
$isUsuarioPublico = in_array($slug, ['usuario-publico','usuario_publico','usuario-base','usuario_base'], true);
$isInvitado = in_array($slug, ['invitado-demo'], true);

// Redirigir roles sin acceso a noticias a sus respectivos dashboards
if ($isVisualizador) {
    header('Location: roles/visualizador/visualizador_dashboard.php?mensaje=' . urlencode('No tienes acceso a gestión de noticias.'));
    exit();
}
if ($isLector) {
    header('Location: roles/lector-informes/lector-informes_dashboard.php?mensaje=' . urlencode('No tienes acceso a gestión de noticias.'));
    exit();
}
if ($isUsuarioPublico) {
    header('Location: roles/usuario-base/usuario-base_dashboard.php?mensaje=' . urlencode('No tienes acceso al panel administrativo.'));
    exit();
}
if ($isInvitado) {
    header('Location: roles/invitado-demo/invitado-demo_dashboard.php?mensaje=' . urlencode('No tienes acceso al panel administrativo.'));
    exit();
}

// Solo Super Admin, Admin y Editor pueden gestionar noticias
if (!isset($_SESSION['user_id']) || (!($isSuper || $isAdmin || $isEditor))) {
    header('Location: index.php?mensaje=' . urlencode('Acceso denegado.'));
    exit();
}

$sb = new Supabase();
$msg = '';
$err = '';

// CREAR O ACTUALIZAR NOTICIA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'crear' || $accion === 'actualizar') {
        $id = isset($_POST['id']) && $_POST['id'] ? (int)$_POST['id'] : null;
        $titulo = trim($_POST['titulo'] ?? '');
        $slug_noticia = trim($_POST['slug'] ?? '');
        $extracto = trim($_POST['extracto'] ?? '');
        $contenido = trim($_POST['contenido'] ?? '');
        $imagen_destacada = trim($_POST['imagen_destacada'] ?? '');
        $categoria_id = $_POST['categoria_id'] && $_POST['categoria_id'] !== '' ? (int)$_POST['categoria_id'] : null;
        $estado = trim($_POST['estado'] ?? 'borrador');
        $destacada = isset($_POST['destacada']) ? true : false;
        $tags = trim($_POST['tags'] ?? '');
        
        // Auto-generar slug si está vacío
        if (empty($slug_noticia)) {
            $slug_noticia = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titulo)));
        }
        
        $data = [
            'titulo' => $titulo,
            'slug' => $slug_noticia,
            'extracto' => $extracto,
            'contenido' => $contenido,
            'imagen_destacada' => $imagen_destacada ?: null,
            'categoria_id' => $categoria_id,
            'estado' => $estado,
            'destacada' => $destacada,
            'tags' => $tags ?: null,
            'autor_id' => (int)$_SESSION['user_id']
        ];
        
        // Si es "publicado", establecer fecha de publicación
        if ($estado === 'publicado' && $accion === 'crear') {
            $data['fecha_publicacion'] = date('Y-m-d H:i:s');
        }
        
        if ($accion === 'crear') {
            $ins = $sb->insert('noticias', $data);
            $msg = $ins ? 'Noticia creada exitosamente.' : 'Error al crear la noticia.';
        } else {
            // Actualizar - Editor solo puede editar sus propias noticias
            if ($isEditor) {
                $noticia = $sb->from('noticias', [
                    'select' => 'autor_id',
                    'id' => 'eq.' . $id,
                    'limit' => 1
                ]);
                if (!$noticia || !isset($noticia[0]) || (int)$noticia[0]['autor_id'] !== (int)$_SESSION['user_id']) {
                    $err = 'No puedes editar noticias de otros autores.';
                } else {
                    unset($data['autor_id']);
                    $data['editor_id'] = (int)$_SESSION['user_id'];
                    $upd = $sb->update('noticias', $data, ['id' => 'eq.' . $id]);
                    $msg = $upd ? 'Noticia actualizada.' : 'Error al actualizar.';
                }
            } else {
                unset($data['autor_id']);
                $data['editor_id'] = (int)$_SESSION['user_id'];
                $upd = $sb->update('noticias', $data, ['id' => 'eq.' . $id]);
                $msg = $upd ? 'Noticia actualizada.' : 'Error al actualizar.';
            }
        }
    } elseif ($accion === 'eliminar') {
        if ($isEditor) {
            $err = 'No tienes permisos para eliminar noticias.';
        } else {
            $id = (int)($_POST['id'] ?? 0);
            $ok = $sb->delete('noticias', ['id' => 'eq.' . $id]);
            $msg = $ok ? 'Noticia eliminada.' : 'Error al eliminar.';
        }
    }
}

// Obtener categorías
$categorias = $sb->from('categorias', [
    'select' => 'id,nombre,slug',
    'tipo' => 'eq.noticia',
    'estado' => 'eq.activo',
    'order' => 'nombre.asc'
]) ?? [];

// Obtener todas las noticias con información del autor
$params = [
    'select' => 'id,titulo,slug,extracto,contenido,imagen_destacada,categoria_id,estado,destacada,fecha_publicacion,vistas,autor_id,created_at,tags',
    'order' => 'created_at.desc'
];

// Editor solo puede ver sus propias noticias
if ($isEditor) {
    $params['autor_id'] = 'eq.' . $_SESSION['user_id'];
}

$noticias = $sb->from('noticias', $params) ?? [];

// Obtener información de usuarios (autores)
$usuariosMap = [];
$usuarios = $sb->from('usuarios', ['select' => 'id,nombre']) ?? [];
foreach ($usuarios as $u) {
    $usuariosMap[$u['id']] = $u['nombre'];
}

// Enriquecer noticias con información adicional
foreach ($noticias as &$noticia) {
    $noticia['autor_nombre'] = $usuariosMap[$noticia['autor_id']] ?? 'Autor Desconocido';
    $noticia['puede_eliminar'] = !$isEditor; // Editor no puede eliminar
    
    // Agregar nombre de categoría
    $noticia['categoria_nombre'] = '';
    foreach ($categorias as $cat) {
        if ($cat['id'] == $noticia['categoria_id']) {
            $noticia['categoria_nombre'] = $cat['nombre'];
            break;
        }
    }
}
unset($noticia);
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Artículos de Noticia</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/admin-style.css?v=3.0">
  <link rel="stylesheet" href="css/editorial-style.css?v=1.0">
  <style>
    .news-list-view, .article-view {
        transition: opacity 0.3s ease;
    }
  </style>
</head>
<body>
<div class="admin-wrapper">
  <nav class="admin-sidebar">
    <?php if ($isEditor): ?>
      <a class="sidebar-brand" href="roles/editor/editor_dashboard.php">Editor</a>
    <?php elseif ($isAdmin): ?>
      <a class="sidebar-brand" href="roles/admin/admin_dashboard.php">Admin</a>
    <?php else: ?>
      <a class="sidebar-brand" href="roles/super-admin/super-admin_dashboard.php">SuperAdmin</a>
    <?php endif; ?>
    
    <ul class="sidebar-nav nav flex-column">
      <?php if ($isEditor): ?>
        <li class="nav-item"><a class="nav-link" href="roles/editor/editor_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="noticias_admin_editorial.php"><i class="fas fa-newspaper"></i>Artículos de Noticia</a></li>
        <li class="nav-item"><a class="nav-link" href="banners_admin.php"><i class="fas fa-images"></i>Banners</a></li>
      <?php elseif ($isAdmin): ?>
        <li class="nav-item"><a class="nav-link" href="roles/admin/admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="usuarios_admin.php"><i class="fas fa-users-cog"></i>Usuarios</a></li>
        <li class="nav-item"><a class="nav-link" href="banners_admin.php"><i class="fas fa-images"></i>Banners</a></li>
        <li class="nav-item"><a class="nav-link active" href="noticias_admin_editorial.php"><i class="fas fa-newspaper"></i>Artículos de Noticia</a></li>
        <li class="nav-item"><a class="nav-link" href="reportes_visitas.php"><i class="fas fa-chart-line"></i>Reportes</a></li>
      <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="roles/super-admin/super-admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="usuarios_admin.php"><i class="fas fa-users-cog"></i>Usuarios</a></li>
        <li class="nav-item"><a class="nav-link" href="banners_admin.php"><i class="fas fa-images"></i>Banners</a></li>
        <li class="nav-item"><a class="nav-link active" href="noticias_admin_editorial.php"><i class="fas fa-newspaper"></i>Artículos de Noticia</a></li>
        <li class="nav-item"><a class="nav-link" href="reportes_visitas.php"><i class="fas fa-chart-line"></i>Reportes</a></li>
        <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-cogs"></i>Sistema</a></li>
      <?php endif; ?>
    </ul>
    <div class="sidebar-footer">
      <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
          <i class="fas fa-user-circle fa-2x me-2"></i>
          <strong><?php echo htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario'); ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
          <li><a class="dropdown-item" href="../index.php" target="_blank">Ver Sitio Web</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="admin-content">
    <div class="admin-topbar">
      <div class="navbar-admin">
        <div class="brand">📰 Artículos de Noticia</div>
        <div class="spacer"></div>
        <div class="user"><?php echo htmlspecialchars($_SESSION['user_nombre'] ?? ''); ?></div>
      </div>
    </div>

    <main class="admin-main container-fluid">
      
      <?php if ($msg): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      <?php if ($err): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($err); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- VISTA DE LISTA -->
      <div class="news-list-view" style="opacity: 1;">
        
        <!-- Toolbar de búsqueda y filtros -->
        <div class="news-toolbar">
          <div class="news-search">
            <i class="fas fa-search"></i>
            <input type="text" id="newsSearch" placeholder="Buscar noticias...">
          </div>
          <div class="news-filters">
            <select id="filterCategory">
              <option value="">Todas las categorías</option>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
              <?php endforeach; ?>
            </select>
            <select id="filterStatus">
              <option value="">Todos los estados</option>
              <option value="publicado">Publicado</option>
              <option value="borrador">Borrador</option>
              <option value="revision">En Revisión</option>
            </select>
            <select id="filterSort">
              <option value="recent">Más recientes</option>
              <option value="views">Más vistas</option>
              <option value="az">A-Z</option>
            </select>
          </div>
          <button class="btn btn-primary" id="createNewsBtn" style="white-space: nowrap;">
            <i class="fas fa-plus"></i> Nueva Noticia
          </button>
        </div>

        <!-- Grid de noticias -->
        <div class="news-grid-container">
          <div class="news-grid">
            <!-- Las tarjetas se generarán dinámicamente con JavaScript -->
          </div>
        </div>

      </div>

      <!-- VISTA DE ARTÍCULO COMPLETO -->
      <div class="article-view" style="display: none; opacity: 0;">
        <!-- El contenido del artículo se generará dinámicamente con JavaScript -->
      </div>

    </main>
  </div>
</div>

<!-- MODAL CREAR/EDITAR -->
<div class="news-modal" id="newsModal">
  <div class="news-modal-content">
    <div class="news-modal-header">
      <h2 class="news-modal-title">Crear Nueva Noticia</h2>
      <button class="news-modal-close" type="button">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <form method="post" id="newsForm">
      <input type="hidden" name="accion" value="crear" id="newsAction">
      <input type="hidden" name="id" id="newsId">
      
      <div class="form-group">
        <label>Título *</label>
        <input type="text" name="titulo" id="newsTitle" class="form-control" required maxlength="80">
        <div class="char-counter" id="titleCounter">0/80 caracteres</div>
      </div>
      
      <div class="form-group">
        <label>Extracto *</label>
        <textarea name="extracto" id="newsExcerpt" class="form-control" rows="3" maxlength="160" required></textarea>
        <div class="char-counter" id="excerptCounter">0/160 caracteres</div>
      </div>
      
      <div class="form-group">
        <label>Contenido *</label>
        <textarea name="contenido" id="newsContent" class="form-control" rows="12" required placeholder="Escribe el contenido completo del artículo..."></textarea>
      </div>
      
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Categoría</label>
            <select name="categoria_id" id="newsCategory" class="form-control">
              <option value="">Sin categoría</option>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Estado</label>
            <select name="estado" id="newsStatus" class="form-control">
              <option value="borrador">Borrador</option>
              <option value="revision">En Revisión</option>
              <option value="publicado">Publicado</option>
            </select>
          </div>
        </div>
      </div>
      
      <div class="form-group">
        <label>Imagen Destacada (URL)</label>
        <input type="text" name="imagen_destacada" id="newsImage" class="form-control" placeholder="https://...">
      </div>
      
      <div class="form-group">
        <label>Tags (separados por comas)</label>
        <input type="text" name="tags" id="newsTags" class="form-control" placeholder="tecnología, desarrollo, innovación">
      </div>
      
      <div class="text-end" style="margin-top: 2rem;">
        <button type="button" class="btn btn-secondary me-2" onclick="closeNewsModal()">Cancelar</button>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> Guardar Noticia
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Datos de noticias para JavaScript -->
<script type="application/json" id="articlesData">
<?php echo json_encode($noticias, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/editorial.js?v=1.0"></script>
</body>
</html>
