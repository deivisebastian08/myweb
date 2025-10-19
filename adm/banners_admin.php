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

// Redirigir roles sin acceso a banners a sus respectivos dashboards
if ($isVisualizador) {
    header('Location: roles/visualizador/visualizador_dashboard.php?mensaje=' . urlencode('No tienes acceso a gestión de banners.'));
    exit();
}
if ($isLector) {
    header('Location: roles/lector-informes/lector-informes_dashboard.php?mensaje=' . urlencode('No tienes acceso a gestión de banners.'));
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

// Solo Super Admin, Admin y Editor pueden gestionar banners
if (!isset($_SESSION['user_id']) || (!($isSuper || $isAdmin || $isEditor))) {
    header('Location: index.php?mensaje=' . urlencode('Acceso denegado.'));
    exit();
}

$sb = new Supabase();
$msg = '';
$err = '';

// Crear/Actualizar/Eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'crear') {
        $data = [
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'enlace' => trim($_POST['enlace'] ?? ''),
            'imagen' => trim($_POST['imagen'] ?? ''),
            'estado' => trim($_POST['estado'] ?? 'activo'),
            'orden' => (int)($_POST['orden'] ?? 0),
            'usuario_id' => (int)($_SESSION['user_id'] ?? 0)
        ];
        $ins = $sb->insert('banners', $data);
        $msg = $ins ? 'Banner creado.' : 'No se pudo crear el banner.';
    } elseif ($accion === 'actualizar_estado') {
        $id = (int)($_POST['id'] ?? 0);
        $estado = trim($_POST['estado'] ?? 'activo');
        $res = $sb->update('banners', ['estado' => $estado], ['id' => 'eq.' . $id]);
        $msg = $res ? 'Estado actualizado.' : 'No se pudo actualizar estado.';
    } elseif ($accion === 'actualizar_orden') {
        $id = (int)($_POST['id'] ?? 0);
        $orden = (int)($_POST['orden'] ?? 0);
        $res = $sb->update('banners', ['orden' => $orden], ['id' => 'eq.' . $id]);
        $msg = $res ? 'Orden actualizado.' : 'No se pudo actualizar orden.';
    } elseif ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? 0);
        $ok = $sb->delete('banners', ['id' => 'eq.' . $id]);
        $msg = $ok ? 'Banner eliminado.' : 'No se pudo eliminar.';
    }
}

// Listado de banners
$banners = $sb->from('banners', [
    'select' => 'id,titulo,descripcion,enlace,imagen,estado,orden,fecha_creacion',
    'order' => 'orden.asc'
]) ?? [];

?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administrar Banners</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/admin-style.css?v=3.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<div class="admin-wrapper">
  <nav class="admin-sidebar">
    <?php
    $isEditor = in_array($slug, ['editor'], true);
    $isAdmin = in_array($slug, ['administrador','admin'], true);
    ?>
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
        <li class="nav-item"><a class="nav-link" href="noticias_admin.php"><i class="fas fa-newspaper"></i>Mis Noticias</a></li>
        <li class="nav-item"><a class="nav-link active" href="banners_admin.php"><i class="fas fa-images"></i>Banners</a></li>
      <?php elseif ($isAdmin): ?>
        <!-- Sidebar para Admin: Dashboard, Usuarios, Banners, Noticias, Reportes -->
        <li class="nav-item"><a class="nav-link" href="roles/admin/admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="usuarios_admin.php"><i class="fas fa-users-cog"></i>Usuarios</a></li>
        <li class="nav-item"><a class="nav-link active" href="banners_admin.php"><i class="fas fa-images"></i>Banners</a></li>
        <li class="nav-item"><a class="nav-link" href="noticias_admin.php"><i class="fas fa-newspaper"></i>Noticias</a></li>
        <li class="nav-item"><a class="nav-link" href="reportes_visitas.php"><i class="fas fa-chart-line"></i>Reportes</a></li>
      <?php else: ?>
        <!-- Sidebar para Super Admin: Todo -->
        <li class="nav-item"><a class="nav-link" href="roles/super-admin/super-admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="usuarios_admin.php"><i class="fas fa-users-cog"></i>Usuarios</a></li>
        <li class="nav-item"><a class="nav-link active" href="banners_admin.php"><i class="fas fa-images"></i>Banners</a></li>
        <li class="nav-item"><a class="nav-link" href="noticias_admin.php"><i class="fas fa-newspaper"></i>Noticias</a></li>
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
      <div class="navbar-admin"><div class="brand">Banners</div><div class="spacer"></div></div>
    </div>

    <main class="admin-main container-fluid">
      <?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>

      <div class="card mb-4">
        <div class="card-header">Crear Banner</div>
        <div class="card-body">
          <form class="row g-3" method="post">
            <input type="hidden" name="accion" value="crear">
            <div class="col-md-6"><label class="form-label">Título</label><input name="titulo" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Enlace</label><input name="enlace" class="form-control" placeholder="/servicios"></div>
            <div class="col-12"><label class="form-label">Descripción</label><textarea name="descripcion" class="form-control" rows="2" required></textarea></div>
            <div class="col-md-6"><label class="form-label">Imagen (URL)</label><input name="imagen" class="form-control" placeholder="https://..." required></div>
            <div class="col-md-3"><label class="form-label">Estado</label><select name="estado" class="form-select"><option value="activo">Activo</option><option value="inactivo">Inactivo</option><option value="programado">Programado</option></select></div>
            <div class="col-md-3"><label class="form-label">Orden</label><input name="orden" type="number" value="0" class="form-control"></div>
            <div class="col-12 text-end"><button class="btn btn-primary" type="submit">Crear</button></div>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header">Listado</div>
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>ID</th><th>Preview</th><th>Título</th><th>Estado</th><th>Orden</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
              <?php if (is_array($banners) && count($banners)): foreach ($banners as $b): ?>
                <tr>
                  <td><?php echo (int)$b['id']; ?></td>
                  <td><?php if ($b['imagen']): ?><img src="<?php echo htmlspecialchars($b['imagen']); ?>" style="height:40px;border-radius:6px;"/><?php endif; ?></td>
                  <td>
                    <div class="fw-bold"><?php echo htmlspecialchars($b['titulo']); ?></div>
                    <div class="text-muted small"><?php echo htmlspecialchars($b['descripcion']); ?></div>
                  </td>
                  <td>
                    <form method="post" class="d-inline">
                      <input type="hidden" name="accion" value="actualizar_estado">
                      <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                      <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach (['activo','inactivo','programado'] as $est): ?>
                          <option value="<?php echo $est; ?>" <?php echo ($b['estado']===$est?'selected':''); ?>><?php echo ucfirst($est); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </form>
                  </td>
                  <td>
                    <form method="post" class="d-inline">
                      <input type="hidden" name="accion" value="actualizar_orden">
                      <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                      <input type="number" class="form-control form-control-sm" style="width:90px" name="orden" value="<?php echo (int)$b['orden']; ?>" onchange="this.form.submit()">
                    </form>
                  </td>
                  <td class="text-end">
                    <form method="post" onsubmit="return confirm('¿Eliminar banner?');">
                      <input type="hidden" name="accion" value="eliminar">
                      <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                      <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; else: ?>
                <tr><td colspan="6" class="text-center p-4">No hay banners.</td></tr>
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
</body>
</html>
