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
                // Verificar que la noticia le pertenece
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
                // Super Admin y Admin pueden editar cualquier noticia
                unset($data['autor_id']);
                $data['editor_id'] = (int)$_SESSION['user_id'];
                $upd = $sb->update('noticias', $data, ['id' => 'eq.' . $id]);
                $msg = $upd ? 'Noticia actualizada.' : 'Error al actualizar.';
            }
        }
    } elseif ($accion === 'eliminar') {
        // Solo Super Admin y Admin pueden eliminar noticias
        if ($isEditor) {
            $err = 'No tienes permisos para eliminar noticias.';
        } else {
            $id = (int)($_POST['id'] ?? 0);
            $ok = $sb->delete('noticias', ['id' => 'eq.' . $id]);
            $msg = $ok ? 'Noticia eliminada.' : 'Error al eliminar.';
        }
    } elseif ($accion === 'cambiar_estado') {
        $id = (int)($_POST['id'] ?? 0);
        $estado = trim($_POST['estado'] ?? '');
        
        // Editor solo puede cambiar estado de sus propias noticias
        if ($isEditor) {
            $noticia = $sb->from('noticias', [
                'select' => 'autor_id',
                'id' => 'eq.' . $id,
                'limit' => 1
            ]);
            if (!$noticia || !isset($noticia[0]) || (int)$noticia[0]['autor_id'] !== (int)$_SESSION['user_id']) {
                $err = 'No puedes cambiar el estado de noticias de otros autores.';
            } else {
                $updateData = ['estado' => $estado, 'editor_id' => (int)$_SESSION['user_id']];
                if ($estado === 'publicado') {
                    $updateData['fecha_publicacion'] = date('Y-m-d H:i:s');
                }
                $upd = $sb->update('noticias', $updateData, ['id' => 'eq.' . $id]);
                $msg = $upd ? 'Estado actualizado.' : 'Error al cambiar estado.';
            }
        } else {
            // Super Admin y Admin pueden cambiar estado de cualquier noticia
            $updateData = ['estado' => $estado, 'editor_id' => (int)$_SESSION['user_id']];
            if ($estado === 'publicado') {
                $updateData['fecha_publicacion'] = date('Y-m-d H:i:s');
            }
            $upd = $sb->update('noticias', $updateData, ['id' => 'eq.' . $id]);
            $msg = $upd ? 'Estado actualizado.' : 'Error al cambiar estado.';
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

// Obtener noticias
$filtroEstado = $_GET['estado'] ?? '';
$filtroCategoria = $_GET['categoria'] ?? '';
$params = [
    'select' => 'id,titulo,slug,extracto,imagen_destacada,categoria_id,estado,destacada,fecha_publicacion,vistas,autor_id,fecha_creacion',
    'order' => 'fecha_creacion.desc'
];
if ($filtroEstado) $params['estado'] = 'eq.' . $filtroEstado;
if ($filtroCategoria) $params['categoria_id'] = 'eq.' . $filtroCategoria;

// Editor solo puede ver sus propias noticias
if ($isEditor) {
    $params['autor_id'] = 'eq.' . $_SESSION['user_id'];
}

$noticias = $sb->from('noticias', $params) ?? [];

// Obtener noticia para editar si se solicitó
$noticiaEditar = null;
if (isset($_GET['editar'])) {
    $idEditar = (int)$_GET['editar'];
    $res = $sb->from('noticias', [
        'select' => '*',
        'id' => 'eq.' . $idEditar,
        'limit' => 1
    ]);
    if ($res && isset($res[0])) {
        $noticiaEditar = $res[0];
    }
}
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Noticias</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/admin-style.css?v=3.0">
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
        <!-- Sidebar para Editor: solo Dashboard, Noticias, Banners -->
        <li class="nav-item"><a class="nav-link" href="roles/editor/editor_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="noticias_admin.php"><i class="fas fa-newspaper"></i>Mis Noticias</a></li>
        <li class="nav-item"><a class="nav-link" href="banners_admin.php"><i class="fas fa-images"></i>Banners</a></li>
      <?php elseif ($isAdmin): ?>
        <!-- Sidebar para Admin: Dashboard, Usuarios, Banners, Noticias, Reportes -->
        <li class="nav-item"><a class="nav-link" href="roles/admin/admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="usuarios_admin.php"><i class="fas fa-users-cog"></i>Usuarios</a></li>
        <li class="nav-item"><a class="nav-link" href="banners_admin.php"><i class="fas fa-images"></i>Banners</a></li>
        <li class="nav-item"><a class="nav-link active" href="noticias_admin.php"><i class="fas fa-newspaper"></i>Noticias</a></li>
        <li class="nav-item"><a class="nav-link" href="reportes_visitas.php"><i class="fas fa-chart-line"></i>Reportes</a></li>
      <?php else: ?>
        <!-- Sidebar para Super Admin: Todo -->
        <li class="nav-item"><a class="nav-link" href="roles/super-admin/super-admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="usuarios_admin.php"><i class="fas fa-users-cog"></i>Usuarios</a></li>
        <li class="nav-item"><a class="nav-link" href="banners_admin.php"><i class="fas fa-images"></i>Banners</a></li>
        <li class="nav-item"><a class="nav-link active" href="noticias_admin.php"><i class="fas fa-newspaper"></i>Noticias</a></li>
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
        <div class="brand">Gestión de Noticias</div>
        <div class="spacer"></div>
        <div class="user"><?php echo htmlspecialchars($_SESSION['user_nombre'] ?? ''); ?></div>
      </div>
    </div>

    <main class="admin-main container-fluid">
      
      <?php if ($msg): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      <?php endif; ?>
      <?php if ($err): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo htmlspecialchars($err); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      <?php endif; ?>

      <!-- Formulario Crear/Editar -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
          <span><?php echo $noticiaEditar ? 'Editar Noticia' : 'Crear Nueva Noticia'; ?></span>
          <?php if ($noticiaEditar): ?>
            <a href="noticias_admin.php" class="btn btn-sm btn-secondary">Cancelar Edición</a>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <form method="post" id="formNoticia">
            <input type="hidden" name="accion" value="<?php echo $noticiaEditar ? 'actualizar' : 'crear'; ?>">
            <?php if ($noticiaEditar): ?>
              <input type="hidden" name="id" value="<?php echo $noticiaEditar['id']; ?>">
            <?php endif; ?>
            
            <div class="row">
              <div class="col-md-8">
                <div class="mb-3">
                  <label class="form-label">Título *</label>
                  <input type="text" name="titulo" class="form-control" required value="<?php echo htmlspecialchars($noticiaEditar['titulo'] ?? ''); ?>">
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Slug (URL amigable)</label>
                  <input type="text" name="slug" class="form-control" placeholder="Se genera automáticamente si se deja vacío" value="<?php echo htmlspecialchars($noticiaEditar['slug'] ?? ''); ?>">
                  <small class="text-muted">Ejemplo: mi-primera-noticia</small>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Extracto</label>
                  <textarea name="extracto" class="form-control" rows="2" placeholder="Resumen breve de la noticia"><?php echo htmlspecialchars($noticiaEditar['extracto'] ?? ''); ?></textarea>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Contenido *</label>
                  <textarea name="contenido" class="form-control" rows="8" required><?php echo htmlspecialchars($noticiaEditar['contenido'] ?? ''); ?></textarea>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Tags (separados por comas)</label>
                  <input type="text" name="tags" class="form-control" placeholder="testing, calidad, software" value="<?php echo htmlspecialchars($noticiaEditar['tags'] ?? ''); ?>">
                </div>
              </div>
              
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">Categoría</label>
                  <select name="categoria_id" class="form-select">
                    <option value="">Sin categoría</option>
                    <?php foreach ($categorias as $cat): ?>
                      <option value="<?php echo $cat['id']; ?>" <?php echo (isset($noticiaEditar['categoria_id']) && $noticiaEditar['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nombre']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Estado</label>
                  <select name="estado" class="form-select">
                    <option value="borrador" <?php echo (isset($noticiaEditar['estado']) && $noticiaEditar['estado'] === 'borrador') ? 'selected' : ''; ?>>Borrador</option>
                    <option value="revision" <?php echo (isset($noticiaEditar['estado']) && $noticiaEditar['estado'] === 'revision') ? 'selected' : ''; ?>>En Revisión</option>
                    <option value="publicado" <?php echo (isset($noticiaEditar['estado']) && $noticiaEditar['estado'] === 'publicado') ? 'selected' : ''; ?>>Publicado</option>
                    <option value="archivado" <?php echo (isset($noticiaEditar['estado']) && $noticiaEditar['estado'] === 'archivado') ? 'selected' : ''; ?>>Archivado</option>
                  </select>
                </div>
                
                <div class="mb-3 form-check">
                  <input type="checkbox" name="destacada" class="form-check-input" id="destacada" <?php echo (isset($noticiaEditar['destacada']) && $noticiaEditar['destacada']) ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="destacada">Noticia Destacada</label>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Imagen Destacada</label>
                  <input type="text" name="imagen_destacada" id="inputImagenUrl" class="form-control mb-2" placeholder="URL de la imagen" value="<?php echo htmlspecialchars($noticiaEditar['imagen_destacada'] ?? ''); ?>">
                  
                  <div class="d-grid mb-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('fileImagen').click()">
                      <i class="fas fa-upload"></i> Subir Imagen
                    </button>
                  </div>
                  <input type="file" id="fileImagen" accept="image/*" style="display:none;" onchange="subirImagen(this)">
                  
                  <div id="imagenPreview" style="max-width:100%;">
                    <?php if (isset($noticiaEditar['imagen_destacada']) && $noticiaEditar['imagen_destacada']): ?>
                      <img src="<?php echo htmlspecialchars($noticiaEditar['imagen_destacada']); ?>" class="img-fluid rounded" alt="Preview">
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="text-end">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo $noticiaEditar ? 'Actualizar Noticia' : 'Crear Noticia'; ?>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Filtros -->
      <div class="card mb-3">
        <div class="card-body">
          <form class="row g-3" method="get">
            <div class="col-md-4">
              <select name="estado" class="form-select">
                <option value="">Todos los estados</option>
                <option value="borrador" <?php echo ($filtroEstado==='borrador')?'selected':''; ?>>Borrador</option>
                <option value="revision" <?php echo ($filtroEstado==='revision')?'selected':''; ?>>En Revisión</option>
                <option value="publicado" <?php echo ($filtroEstado==='publicado')?'selected':''; ?>>Publicado</option>
                <option value="archivado" <?php echo ($filtroEstado==='archivado')?'selected':''; ?>>Archivado</option>
              </select>
            </div>
            <div class="col-md-4">
              <select name="categoria" class="form-select">
                <option value="">Todas las categorías</option>
                <?php foreach ($categorias as $cat): ?>
                  <option value="<?php echo $cat['id']; ?>" <?php echo ($filtroCategoria==(string)$cat['id'])?'selected':''; ?>>
                    <?php echo htmlspecialchars($cat['nombre']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Lista de Noticias -->
      <div class="card">
        <div class="card-header">Noticias (<?php echo count($noticias); ?>)</div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th style="width:60px;">ID</th>
                <th>Título</th>
                <th>Categoría</th>
                <th>Estado</th>
                <th style="width:100px;">Vistas</th>
                <th style="width:150px;">Fecha</th>
                <th style="width:200px;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($noticias)): foreach ($noticias as $n): ?>
              <tr>
                <td><?php echo $n['id']; ?></td>
                <td>
                  <?php if ($n['destacada']): ?><i class="fas fa-star text-warning" title="Destacada"></i><?php endif; ?>
                  <strong><?php echo htmlspecialchars(substr($n['titulo'], 0, 50)); ?></strong>
                  <?php if (strlen($n['titulo'] ?? '') > 50): ?>...<?php endif; ?>
                  <?php if ($n['imagen_destacada']): ?><i class="fas fa-image text-info ms-1" title="Con imagen"></i><?php endif; ?>
                </td>
                <td>
                  <?php 
                  $catNombre = 'Sin categoría';
                  foreach ($categorias as $cat) {
                      if ($cat['id'] == ($n['categoria_id'] ?? 0)) {
                          $catNombre = $cat['nombre'];
                          break;
                      }
                  }
                  echo htmlspecialchars($catNombre);
                  ?>
                </td>
                <td>
                  <?php 
                  $badgeClass = [
                      'borrador' => 'secondary',
                      'revision' => 'warning',
                      'publicado' => 'success',
                      'archivado' => 'dark'
                  ];
                  $estado = $n['estado'] ?? 'borrador';
                  ?>
                  <span class="badge bg-<?php echo $badgeClass[$estado] ?? 'secondary'; ?>">
                    <?php echo ucfirst($estado); ?>
                  </span>
                </td>
                <td><?php echo number_format($n['vistas'] ?? 0); ?></td>
                <td><small><?php echo substr($n['fecha_creacion'] ?? '', 0, 10); ?></small></td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <a href="?editar=<?php echo $n['id']; ?>" class="btn btn-warning" title="Editar">
                      <i class="fas fa-edit"></i>
                    </a>
                    <?php if ($estado !== 'publicado'): ?>
                      <form method="post" class="d-inline" onsubmit="return confirm('¿Publicar esta noticia?')">
                        <input type="hidden" name="accion" value="cambiar_estado">
                        <input type="hidden" name="id" value="<?php echo $n['id']; ?>">
                        <input type="hidden" name="estado" value="publicado">
                        <button type="submit" class="btn btn-success" title="Publicar">
                          <i class="fas fa-check"></i>
                        </button>
                      </form>
                    <?php endif; ?>
                    <?php if (!$isEditor): ?>
                      <!-- Solo Super Admin y Admin pueden eliminar -->
                      <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar esta noticia?')">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo $n['id']; ?>">
                        <button type="submit" class="btn btn-danger" title="Eliminar">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; else: ?>
                <tr><td colspan="7" class="text-center p-4">No hay noticias. Crea la primera.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin-mobile.js"></script>
<script>
function subirImagen(input) {
  if (!input.files || !input.files[0]) return;
  
  const formData = new FormData();
  formData.append('imagen', input.files[0]);
  
  // Mostrar indicador de carga
  const preview = document.getElementById('imagenPreview');
  preview.innerHTML = '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Subiendo imagen...</div>';
  
  fetch('upload_imagen.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      document.getElementById('inputImagenUrl').value = data.url;
      preview.innerHTML = '<img src="' + data.url + '" class="img-fluid rounded" alt="Preview">';
    } else {
      alert('Error: ' + data.message);
      preview.innerHTML = '';
    }
  })
  .catch(err => {
    alert('Error al subir la imagen');
    console.error(err);
    preview.innerHTML = '';
  });
}
</script>
</body>
</html>
