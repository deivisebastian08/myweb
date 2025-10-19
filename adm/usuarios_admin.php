<?php
session_start();
require_once __DIR__ . '/script/Supabase.php';

// Guardia: solo usuarios con rol super admin o admin pueden entrar
$slug = strtolower(str_replace(['_',' '], '-', $_SESSION['user_rol_slug'] ?? ''));
$rolId = isset($_SESSION['rol_id']) ? (int)$_SESSION['rol_id'] : null;
$isSuper = in_array($slug, ['super-admin','super_admin','admin-principal'], true) || ($rolId === 1);
$isAdmin = in_array($slug, ['administrador','admin'], true);
$isEditor = in_array($slug, ['editor'], true);

// Editor NO puede acceder a gestión de usuarios
if ($isEditor) {
    header('Location: roles/editor/editor_dashboard.php?mensaje=' . urlencode('No tienes acceso a gestión de usuarios.'));
    exit();
}

// Si no es Super Admin ni Admin, denegar acceso
if (!isset($_SESSION['user_id']) || (!($isSuper || $isAdmin))) {
    header('Location: index.php?mensaje=' . urlencode('Acceso denegado.'));
    exit();
}

$sb = new Supabase();
$mensaje = '';
$error = '';

// Manejo de acciones: cambiar rol, cambiar estado, eliminar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $uid = isset($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : 0;
    if ($accion === 'cambiar_rol') {
        // Solo Super Admin puede cambiar roles
        if (!$isSuper) {
            $error = 'No tienes permisos para cambiar roles de usuario.';
        } else {
            $nuevoRol = (int)($_POST['rol_id'] ?? 0);
            $res = $sb->update('usuarios', ['rol_id' => $nuevoRol], ['id' => 'eq.' . $uid]);
            $mensaje = $res ? 'Rol actualizado correctamente.' : 'No se pudo actualizar el rol.';
        }
    } elseif ($accion === 'cambiar_estado') {
        $nuevoEstado = $_POST['estado'] ?? '';
        $res = $sb->update('usuarios', ['estado' => $nuevoEstado], ['id' => 'eq.' . $uid]);
        $mensaje = $res ? 'Estado actualizado correctamente.' : 'No se pudo actualizar el estado.';
    } elseif ($accion === 'eliminar_usuario') {
        if ($uid === (int)($_SESSION['user_id'] ?? 0)) {
            $error = 'No puedes eliminar tu propio usuario.';
        } else {
            $ok = $sb->delete('usuarios', ['id' => 'eq.' . $uid]);
            $mensaje = $ok ? 'Usuario eliminado.' : 'No se pudo eliminar.';
        }
    }
}

// Filtros simples
$busqueda = trim($_GET['q'] ?? '');
$rolFiltro = trim($_GET['rol'] ?? '');
$estadoFiltro = trim($_GET['estado'] ?? '');

// Cargar roles
$roles = $sb->from('roles', ['select' => 'id,nombre,slug,nivel', 'order' => 'nivel.desc']);

// Armar parámetros de consulta de usuarios
$params = [
    'select' => 'id,nombre,email,estado,rol_id,rol:rol_id(nombre,slug,nivel),fecha_creacion',
    'order' => 'id.desc'
];
if ($busqueda !== '') {
    // Buscar por nombre o email (ilike si tienes habilitado operador, aquí usamos like simple)
    // Supabase REST requiere filtros exactos; para demo, filtraremos en PHP luego de traer resultados.
}
if ($rolFiltro !== '') {
    $params['rol_id'] = 'eq.' . $rolFiltro;
}
if ($estadoFiltro !== '') {
    $params['estado'] = 'eq.' . $estadoFiltro;
}

$usuarios = $sb->from('usuarios', $params) ?? [];

// Filtro por búsqueda en PHP (si RLS no permite ilike)
if ($busqueda !== '' && is_array($usuarios)) {
    $needle = mb_strtolower($busqueda);
    $usuarios = array_values(array_filter($usuarios, function($u) use ($needle){
        return (strpos(mb_strtolower($u['nombre'] ?? ''), $needle) !== false)
            || (strpos(mb_strtolower($u['email'] ?? ''), $needle) !== false);
    }));
}

?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administrar Usuarios</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/admin-style.css?v=3.0">
</head>
<body>
<div class="admin-wrapper">
  <nav class="admin-sidebar">
    <?php if ($isAdmin): ?>
      <a class="sidebar-brand" href="roles/admin/admin_dashboard.php">Admin</a>
    <?php else: ?>
      <a class="sidebar-brand" href="roles/super-admin/super-admin_dashboard.php">SuperAdmin</a>
    <?php endif; ?>
    
    <ul class="sidebar-nav nav flex-column">
      <?php if ($isAdmin): ?>
        <!-- Sidebar para Admin: Dashboard, Usuarios, Banners, Noticias, Reportes -->
        <li class="nav-item"><a class="nav-link" href="roles/admin/admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="usuarios_admin.php"><i class="fas fa-users-cog"></i>Usuarios</a></li>
        <li class="nav-item"><a class="nav-link" href="banners_admin.php"><i class="fas fa-images"></i>Banners</a></li>
        <li class="nav-item"><a class="nav-link" href="noticias_admin.php"><i class="fas fa-newspaper"></i>Noticias</a></li>
        <li class="nav-item"><a class="nav-link" href="reportes_visitas.php"><i class="fas fa-chart-line"></i>Reportes</a></li>
      <?php else: ?>
        <!-- Sidebar para Super Admin: Todo -->
        <li class="nav-item"><a class="nav-link" href="roles/super-admin/super-admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="usuarios_admin.php"><i class="fas fa-users-cog"></i>Usuarios</a></li>
        <li class="nav-item"><a class="nav-link" href="banners_admin.php"><i class="fas fa-images"></i>Banners</a></li>
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
      <div class="navbar-admin">
        <div class="brand">Gestión de Usuarios</div>
        <div class="spacer"></div>
        <div class="user"><?php echo htmlspecialchars($_SESSION['user_nombre'] ?? ''); ?></div>
      </div>
    </div>

    <main class="admin-main container-fluid">
      <?php if ($mensaje): ?><div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <div class="card mb-4">
        <div class="card-header">Filtros</div>
        <div class="card-body">
          <form class="row g-3" method="get">
            <div class="col-md-4">
              <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Buscar por nombre o email">
            </div>
            <div class="col-md-3">
              <select name="rol" class="form-select">
                <option value="">Todos los roles</option>
                <?php if (is_array($roles)) foreach ($roles as $r): ?>
                  <option value="<?php echo (int)$r['id']; ?>" <?php echo ($rolFiltro===(string)(int)$r['id'])?'selected':''; ?>><?php echo htmlspecialchars($r['nombre']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <select name="estado" class="form-select">
                <option value="">Todos los estados</option>
                <?php foreach (['pendiente','activo','inactivo','suspendido'] as $est): ?>
                  <option value="<?php echo $est; ?>" <?php echo ($estadoFiltro===$est)?'selected':''; ?>><?php echo ucfirst($est); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2 text-end">
              <button class="btn btn-primary" type="submit">Aplicar</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header">Usuarios</div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Creado</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (is_array($usuarios) && count($usuarios)): foreach ($usuarios as $u): ?>
              <tr>
                <td><?php echo (int)$u['id']; ?></td>
                <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td>
                  <?php if ($isSuper): ?>
                    <!-- Solo Super Admin puede cambiar roles -->
                    <form method="post" class="d-flex gap-2 align-items-center">
                      <input type="hidden" name="usuario_id" value="<?php echo (int)$u['id']; ?>">
                      <input type="hidden" name="accion" value="cambiar_rol">
                      <select name="rol_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php if (is_array($roles)) foreach ($roles as $r): ?>
                          <option value="<?php echo (int)$r['id']; ?>" <?php echo ((int)($u['rol_id'] ?? 0) === (int)$r['id'])?'selected':''; ?>><?php echo htmlspecialchars($r['nombre']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </form>
                  <?php else: ?>
                    <!-- Admin solo puede ver el rol, no cambiarlo -->
                    <?php 
                    $rolNombre = 'N/A';
                    if (is_array($roles)) {
                        foreach ($roles as $r) {
                            if ((int)$r['id'] === (int)($u['rol_id'] ?? 0)) {
                                $rolNombre = $r['nombre'];
                                break;
                            }
                        }
                    }
                    echo '<span class="badge bg-info">' . htmlspecialchars($rolNombre) . '</span>';
                    ?>
                  <?php endif; ?>
                </td>
                <td>
                  <form method="post" class="d-flex gap-2 align-items-center">
                    <input type="hidden" name="usuario_id" value="<?php echo (int)$u['id']; ?>">
                    <input type="hidden" name="accion" value="cambiar_estado">
                    <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                      <?php foreach (['pendiente','activo','inactivo','suspendido'] as $est): ?>
                        <option value="<?php echo $est; ?>" <?php echo (($u['estado'] ?? '')===$est)?'selected':''; ?>><?php echo ucfirst($est); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                </td>
                <td><?php echo htmlspecialchars(substr($u['fecha_creacion'] ?? '',0,19)); ?></td>
                <td class="text-end">
                  <form method="post" onsubmit="return confirm('¿Eliminar usuario? Esta acción no se puede deshacer.');">
                    <input type="hidden" name="usuario_id" value="<?php echo (int)$u['id']; ?>">
                    <input type="hidden" name="accion" value="eliminar_usuario">
                    <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; else: ?>
                <tr><td colspan="7" class="text-center p-4">No hay usuarios para mostrar.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin-mobile.js"></script>
</body>
</html>
