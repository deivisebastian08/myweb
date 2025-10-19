<?php
session_start();

// 1. Verificación de Seguridad: Solo Super Administradores
// Esquema: 'Super Administrador' tiene id=1 en tabla roles (según tu script SQL)
// Además soportamos verificación por slug guardado en sesión
if (!isset($_SESSION['user_id'])) {
    header("location:../../index.php?mensaje=".urlencode("Inicia sesión para continuar."));
    exit();
}

$slug = strtolower(str_replace(['_',' '], '-', $_SESSION['user_rol_slug'] ?? ''));
$isSuperBySlug = in_array($slug, ['super-admin','super_admin','admin-principal'], true);
$rolId = isset($_SESSION['rol_id']) ? (int)$_SESSION['rol_id'] : null;
$isSuperById = ($rolId === 1); // Super Administrador segun script

if (!$isSuperBySlug && !$isSuperById) {
    header('Location: ../../router.php');
    exit();
}

// 2. Verificación de Tiempo de Sesión
$fecGuar = $_SESSION["hora"];
$ahora = date("Y-n-j H:i:s");
$tmpTrans = (strtotime($ahora)-strtotime($fecGuar));
if($tmpTrans >= 12000){
    session_destroy();
    header("Location: ../../index.php?mensaje=Su sesión ha expirado.");
    exit();
} else {
    $_SESSION["hora"] = $ahora;
}

// 3. Obtener Datos del Dashboard desde Supabase (REST)
require_once("../../script/Supabase.php");
$supabase = new Supabase();

// 3.1 Estadísticas básicas (conteos)
$stats = [
  'total_usuarios' => 0,
  'total_banners' => 0,
  'total_noticias' => 0,
  'total_visitas_hoy' => 0
];

$usuarios = $supabase->from('usuarios', ['select' => 'id']);
if (is_array($usuarios)) $stats['total_usuarios'] = count($usuarios);

$banners = $supabase->from('banners', ['select' => 'id', 'estado' => 'eq.activo']);
if (is_array($banners)) $stats['total_banners'] = count($banners);

$noticias = $supabase->from('noticias', ['select' => 'id']);
if (is_array($noticias)) $stats['total_noticias'] = count($noticias);

$hoy = date('Y-m-d');
$visitas_hoy = $supabase->from('visitas', ['select' => 'id', 'fecha' => 'eq.' . $hoy]);
if (is_array($visitas_hoy)) $stats['total_visitas_hoy'] = count($visitas_hoy);

// 3.2 Visitas últimos 7 días (agregación en PHP)
$desde = date('Y-m-d', strtotime('-6 days')); // incluye hoy
$visitas = $supabase->from('visitas', [
  'select' => 'fecha',
  'fecha'  => 'gte.' . $desde
]);

$serie = [];
for ($i = 6; $i >= 0; $i--) {
  $fecha = date('Y-m-d', strtotime("-{$i} days"));
  $serie[$fecha] = 0;
}
if (is_array($visitas)) {
  foreach ($visitas as $row) {
    $f = substr($row['fecha'], 0, 10);
    if (isset($serie[$f])) $serie[$f]++;
  }
}

$chart_labels = json_encode(array_keys($serie));
$chart_values = json_encode(array_values($serie));

// 3.3 Usuarios por Rol (para gráfico de barras)
$roles = $supabase->from('roles', ['select' => 'id,nombre,slug,nivel']);
$usuarios_roles = $supabase->from('usuarios', ['select' => 'rol_id']);
$roleCounts = [];
if (is_array($roles)) {
  foreach ($roles as $r) { $roleCounts[(int)$r['id']] = 0; }
}
if (is_array($usuarios_roles)) {
  foreach ($usuarios_roles as $u) {
    $rid = isset($u['rol_id']) ? (int)$u['rol_id'] : null;
    if ($rid !== null) {
      if (!isset($roleCounts[$rid])) $roleCounts[$rid] = 0;
      $roleCounts[$rid]++;
    }
  }
}
$roleLabels = [];
$roleValues = [];
if (is_array($roles)) {
  foreach ($roles as $r) {
    $roleLabels[] = $r['nombre'];
    $roleValues[] = $roleCounts[(int)$r['id']] ?? 0;
  }
}
$role_labels_json = json_encode($roleLabels);
$role_values_json = json_encode($roleValues);

// 3.4 Últimos registros (usuarios y mensajes)
$ult_usuarios = $supabase->from('usuarios', [
  'select' => 'id,nombre,email,fecha_creacion',
  'order'  => 'id.desc',
  'limit'  => 5
]);
$ult_mensajes = $supabase->from('mensajes_contacto', [
  'select' => 'id,nombre,email,asunto,fecha_creacion',
  'order'  => 'id.desc',
  'limit'  => 5
]);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Super Administrador</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- COMENTARIO: Versión actualizada a 3.0 para la nueva paleta de colores del dashboard. -->
    <link rel="stylesheet" href="../../css/admin-style.css?v=3.0">
</head>
<body>

<div class="admin-wrapper">
    <nav class="admin-sidebar">
        <a class="sidebar-brand" href="super-admin_dashboard.php">SuperAdmin</a>
        <ul class="sidebar-nav nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="#"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../usuarios_admin.php"><i class="fas fa-users-cog"></i>Usuarios</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../banners_admin.php"><i class="fas fa-images"></i>Banners</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../noticias_admin_editorial.php"><i class="fas fa-newspaper"></i>Artículos de Noticia</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../reportes_visitas.php"><i class="fas fa-chart-line"></i>Reportes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="fas fa-cogs"></i>Sistema</a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle fa-2x me-2"></i>
                    <strong><?php echo htmlspecialchars($_SESSION['user_nombre'] ?? $_SESSION['nombre'] ?? 'Usuario'); ?></strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                    <li><a class="dropdown-item" href="../../../perfil.php"><i class="fas fa-user-circle"></i> Mi Perfil</a></li>
                    <li><a class="dropdown-item" href="../../../index.php" target="_blank"><i class="fas fa-globe"></i> Ver Sitio Web</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="admin-content">
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>
            
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Usuarios Activos</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_usuarios']; ?></div></div><div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div></div></div></div></div>
                <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Banners Activos</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_banners']; ?></div></div><div class="col-auto"><i class="fas fa-images fa-2x text-gray-300"></i></div></div></div></div></div>
                <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">Noticias</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_noticias']; ?></div></div><div class="col-auto"><i class="fas fa-newspaper fa-2x text-gray-300"></i></div></div></div></div></div>
                <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-warning shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Visitas Hoy</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_visitas_hoy']; ?></div></div><div class="col-auto"><i class="fas fa-eye fa-2x text-gray-300"></i></div></div></div></div></div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3"><h6 class="m-0 font-weight-bold">Visitas en los Últimos 7 Días</h6></div>
                        <div class="card-body">
                            <div class="chart-area"><canvas id="visitsChart"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../../js/admin-mobile.js"></script>

<script>
    const ctx = document.getElementById('visitsChart').getContext('2d');
    const visitsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo $chart_labels; ?>,
            datasets: [{
                label: 'Visitas Diarias',
                data: <?php echo $chart_values; ?>,
                // COMENTARIO: Colores del gráfico actualizados a la nueva paleta.
                backgroundColor: 'rgba(3, 63, 99, 0.1)', // --primary con opacidad
                borderColor: '#033f63', // --primary
                borderWidth: 3,
                tension: 0.3,
                fill: true,
                pointBackgroundColor: '#033f63',
                pointRadius: 4
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>

</body>
</html>
