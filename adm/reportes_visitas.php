<?php
session_start();
require_once __DIR__ . '/script/Supabase.php';

$slug = strtolower(str_replace(['_',' '], '-', $_SESSION['user_rol_slug'] ?? ''));
$rolId = isset($_SESSION['rol_id']) ? (int)$_SESSION['rol_id'] : null;
$isSuper = in_array($slug, ['super-admin','super_admin','admin-principal'], true) || ($rolId === 1);
$isAdmin = in_array($slug, ['administrador','admin'], true);
$isVisualizador = in_array($slug, ['visualizador'], true);
$isLector = in_array($slug, ['lector-informes'], true);
$isEditor = in_array($slug, ['editor'], true);

// Editor NO puede acceder a reportes
if ($isEditor) {
    header('Location: roles/editor/editor_dashboard.php?mensaje=' . urlencode('No tienes acceso a reportes.'));
    exit();
}

// Solo Super Admin, Admin, Visualizador y Lector pueden ver reportes
if (!isset($_SESSION['user_id']) || (!($isSuper || $isAdmin || $isVisualizador || $isLector))) {
    header('Location: index.php?mensaje=' . urlencode('Acceso denegado.'));
    exit();
}

$sb = new Supabase();

// Filtros
$fechaDesde = $_GET['desde'] ?? date('Y-m-d', strtotime('-7 days'));
$fechaHasta = $_GET['hasta'] ?? date('Y-m-d');
$dispositivo = $_GET['dispositivo'] ?? '';
$navegador = $_GET['navegador'] ?? '';

// Parámetros base
$params = [
    'select' => 'id,ip,so,navegador,dispositivo,url_visitada,url_referencia,tiempo_permanencia,fecha,hora,timestamp_visita',
    'order' => 'timestamp_visita.desc',
    'fecha' => 'gte.' . $fechaDesde
];
if ($fechaHasta) {
    $params['fecha'] = 'gte.' . $fechaDesde;
}
if ($dispositivo) {
    $params['dispositivo'] = 'eq.' . $dispositivo;
}
if ($navegador) {
    $params['navegador'] = 'ilike.*' . $navegador . '*';
}

// Obtener visitas
$visitas = $sb->from('visitas', $params) ?? [];

// Filtrar por rango de fechas en PHP
if ($fechaHasta && is_array($visitas)) {
    $visitas = array_filter($visitas, function($v) use ($fechaHasta) {
        return ($v['fecha'] ?? '') <= $fechaHasta;
    });
}

// Estadísticas agregadas
$stats = [
    'total_visitas' => count($visitas),
    'visitas_hoy' => 0,
    'por_dispositivo' => ['desktop' => 0, 'mobile' => 0, 'tablet' => 0],
    'por_navegador' => [],
    'por_so' => [],
    'tiempo_promedio' => 0,
    'ips_unicas' => 0
];

$ips = [];
$tiempos = [];
foreach ($visitas as $v) {
    if (($v['fecha'] ?? '') === date('Y-m-d')) $stats['visitas_hoy']++;
    
    $disp = strtolower($v['dispositivo'] ?? 'desktop');
    if (isset($stats['por_dispositivo'][$disp])) {
        $stats['por_dispositivo'][$disp]++;
    }
    
    $nav = $v['navegador'] ?? 'Desconocido';
    $stats['por_navegador'][$nav] = ($stats['por_navegador'][$nav] ?? 0) + 1;
    
    $so = $v['so'] ?? 'Desconocido';
    $stats['por_so'][$so] = ($stats['por_so'][$so] ?? 0) + 1;
    
    $ip = $v['ip'] ?? '';
    if ($ip && !in_array($ip, $ips)) $ips[] = $ip;
    
    if (isset($v['tiempo_permanencia']) && $v['tiempo_permanencia'] > 0) {
        $tiempos[] = (int)$v['tiempo_permanencia'];
    }
}
$stats['ips_unicas'] = count($ips);
if (count($tiempos) > 0) {
    $stats['tiempo_promedio'] = round(array_sum($tiempos) / count($tiempos));
}

// Top navegadores
arsort($stats['por_navegador']);
$stats['por_navegador'] = array_slice($stats['por_navegador'], 0, 5, true);

// Top sistemas operativos
arsort($stats['por_so']);
$stats['por_so'] = array_slice($stats['por_so'], 0, 5, true);

?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reportes de Visitas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/admin-style.css?v=3.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="admin-wrapper">
  <nav class="admin-sidebar">
    <?php if ($isVisualizador): ?>
      <a class="sidebar-brand" href="roles/visualizador/visualizador_dashboard.php">Visualizador</a>
    <?php elseif ($isLector): ?>
      <a class="sidebar-brand" href="roles/lector-informes/lector-informes_dashboard.php">Lector</a>
    <?php elseif ($isAdmin): ?>
      <a class="sidebar-brand" href="roles/admin/admin_dashboard.php">Admin</a>
    <?php else: ?>
      <a class="sidebar-brand" href="roles/super-admin/super-admin_dashboard.php">SuperAdmin</a>
    <?php endif; ?>
    
    <ul class="sidebar-nav nav flex-column">
      <?php if ($isVisualizador): ?>
        <!-- Sidebar para Visualizador: solo Dashboard y Reportes -->
        <li class="nav-item"><a class="nav-link" href="roles/visualizador/visualizador_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="reportes_visitas.php"><i class="fas fa-chart-line"></i>Reportes</a></li>
      <?php elseif ($isLector): ?>
        <!-- Sidebar para Lector: solo Dashboard y Reportes -->
        <li class="nav-item"><a class="nav-link" href="roles/lector-informes/lector-informes_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="reportes_visitas.php"><i class="fas fa-chart-line"></i>Reportes</a></li>
      <?php elseif ($isAdmin): ?>
        <!-- Sidebar para Admin: Dashboard, Usuarios, Banners, Noticias, Reportes -->
        <li class="nav-item"><a class="nav-link" href="roles/admin/admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="usuarios_admin.php"><i class="fas fa-users-cog"></i>Usuarios</a></li>
        <li class="nav-item"><a class="nav-link" href="banners_admin.php"><i class="fas fa-images"></i>Banners</a></li>
        <li class="nav-item"><a class="nav-link" href="noticias_admin.php"><i class="fas fa-newspaper"></i>Noticias</a></li>
        <li class="nav-item"><a class="nav-link active" href="reportes_visitas.php"><i class="fas fa-chart-line"></i>Reportes</a></li>
      <?php else: ?>
        <!-- Sidebar para Super Admin: Todo -->
        <li class="nav-item"><a class="nav-link" href="roles/super-admin/super-admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="usuarios_admin.php"><i class="fas fa-users-cog"></i>Usuarios</a></li>
        <li class="nav-item"><a class="nav-link" href="banners_admin.php"><i class="fas fa-images"></i>Banners</a></li>
        <li class="nav-item"><a class="nav-link" href="noticias_admin.php"><i class="fas fa-newspaper"></i>Noticias</a></li>
        <li class="nav-item"><a class="nav-link active" href="reportes_visitas.php"><i class="fas fa-chart-line"></i>Reportes</a></li>
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
        <div class="brand">Reportes de Visitas</div>
        <div class="spacer"></div>
        <div class="user"><?php echo htmlspecialchars($_SESSION['user_nombre'] ?? ''); ?></div>
      </div>
    </div>

    <main class="admin-main container-fluid">

      <!-- Filtros -->
      <div class="card mb-4">
        <div class="card-header">Filtros</div>
        <div class="card-body">
          <form class="row g-3" method="get">
            <div class="col-md-3">
              <label class="form-label">Desde</label>
              <input type="date" class="form-control" name="desde" value="<?php echo htmlspecialchars($fechaDesde); ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Hasta</label>
              <input type="date" class="form-control" name="hasta" value="<?php echo htmlspecialchars($fechaHasta); ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Dispositivo</label>
              <select name="dispositivo" class="form-select">
                <option value="">Todos</option>
                <option value="desktop" <?php echo ($dispositivo==='desktop')?'selected':''; ?>>Desktop</option>
                <option value="mobile" <?php echo ($dispositivo==='mobile')?'selected':''; ?>>Mobile</option>
                <option value="tablet" <?php echo ($dispositivo==='tablet')?'selected':''; ?>>Tablet</option>
              </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <button class="btn btn-primary w-100" type="submit">Aplicar Filtros</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Tarjetas de Estadísticas -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card text-center">
            <div class="card-body">
              <h6 class="text-muted">Total Visitas</h6>
              <h2><?php echo number_format($stats['total_visitas']); ?></h2>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card text-center">
            <div class="card-body">
              <h6 class="text-muted">Visitas Hoy</h6>
              <h2><?php echo number_format($stats['visitas_hoy']); ?></h2>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card text-center">
            <div class="card-body">
              <h6 class="text-muted">IPs Únicas</h6>
              <h2><?php echo number_format($stats['ips_unicas']); ?></h2>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card text-center">
            <div class="card-body">
              <h6 class="text-muted">Tiempo Promedio</h6>
              <h2><?php echo $stats['tiempo_promedio']; ?>s</h2>
            </div>
          </div>
        </div>
      </div>

      <!-- Gráficos -->
      <div class="row mb-4">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">Visitas por Dispositivo</div>
            <div class="card-body">
              <canvas id="chartDispositivos"></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">Top 5 Navegadores</div>
            <div class="card-body">
              <canvas id="chartNavegadores"></canvas>
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">Top 5 Sistemas Operativos</div>
            <div class="card-body">
              <canvas id="chartSO"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabla de Visitas Detalladas -->
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Visitas Detalladas</span>
          <a href="?desde=<?php echo $fechaDesde; ?>&hasta=<?php echo $fechaHasta; ?>&export=csv" class="btn btn-sm btn-success">
            <i class="fas fa-download"></i> Exportar CSV
          </a>
        </div>
        <div class="table-responsive">
          <table class="table table-hover table-sm mb-0">
            <thead>
              <tr>
                <th>Fecha/Hora</th>
                <th>IP</th>
                <th>SO</th>
                <th>Navegador</th>
                <th>Dispositivo</th>
                <th>URL</th>
                <th>Tiempo (s)</th>
              </tr>
            </thead>
            <tbody>
              <?php if (is_array($visitas) && count($visitas)): foreach (array_slice($visitas, 0, 100) as $v): ?>
              <tr>
                <td><?php echo htmlspecialchars(substr($v['timestamp_visita'] ?? '', 0, 19)); ?></td>
                <td><code><?php echo htmlspecialchars($v['ip'] ?? ''); ?></code></td>
                <td><?php echo htmlspecialchars($v['so'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($v['navegador'] ?? 'N/A'); ?></td>
                <td>
                  <?php 
                  $disp = $v['dispositivo'] ?? 'desktop';
                  $icon = $disp === 'mobile' ? 'mobile-alt' : ($disp === 'tablet' ? 'tablet-alt' : 'desktop');
                  echo "<i class='fas fa-{$icon}'></i> " . ucfirst($disp);
                  ?>
                </td>
                <td><small><?php echo htmlspecialchars($v['url_visitada'] ?? ''); ?></small></td>
                <td><?php echo isset($v['tiempo_permanencia']) ? (int)$v['tiempo_permanencia'] : '-'; ?></td>
              </tr>
              <?php endforeach; else: ?>
                <tr><td colspan="7" class="text-center p-4">No hay visitas en el período seleccionado.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <?php if (count($visitas) > 100): ?>
        <div class="card-footer text-muted text-center">
          Mostrando las primeras 100 de <?php echo count($visitas); ?> visitas. Usa filtros para afinar.
        </div>
        <?php endif; ?>
      </div>

    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin-mobile.js"></script>
<script>
// Gráfico de Dispositivos
const ctxDisp = document.getElementById('chartDispositivos').getContext('2d');
new Chart(ctxDisp, {
  type: 'doughnut',
  data: {
    labels: ['Desktop', 'Mobile', 'Tablet'],
    datasets: [{
      data: [<?php echo $stats['por_dispositivo']['desktop']; ?>, <?php echo $stats['por_dispositivo']['mobile']; ?>, <?php echo $stats['por_dispositivo']['tablet']; ?>],
      backgroundColor: ['#033f63', '#28666e', '#7c9885']
    }]
  }
});

// Gráfico de Navegadores
const ctxNav = document.getElementById('chartNavegadores').getContext('2d');
new Chart(ctxNav, {
  type: 'bar',
  data: {
    labels: <?php echo json_encode(array_keys($stats['por_navegador'])); ?>,
    datasets: [{
      label: 'Visitas',
      data: <?php echo json_encode(array_values($stats['por_navegador'])); ?>,
      backgroundColor: '#28666e'
    }]
  },
  options: { scales: { y: { beginAtZero: true } } }
});

// Gráfico de Sistemas Operativos
const ctxSO = document.getElementById('chartSO').getContext('2d');
new Chart(ctxSO, {
  type: 'bar',
  data: {
    labels: <?php echo json_encode(array_keys($stats['por_so'])); ?>,
    datasets: [{
      label: 'Visitas',
      data: <?php echo json_encode(array_values($stats['por_so'])); ?>,
      backgroundColor: '#7c9885'
    }]
  },
  options: { 
    indexAxis: 'y',
    scales: { x: { beginAtZero: true } } 
  }
});
</script>
</body>
</html>
<?php
// Exportar CSV si se solicitó
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=visitas_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Fecha/Hora', 'IP', 'SO', 'Navegador', 'Dispositivo', 'URL', 'Tiempo (s)']);
    foreach ($visitas as $v) {
        fputcsv($output, [
            $v['timestamp_visita'] ?? '',
            $v['ip'] ?? '',
            $v['so'] ?? '',
            $v['navegador'] ?? '',
            $v['dispositivo'] ?? '',
            $v['url_visitada'] ?? '',
            $v['tiempo_permanencia'] ?? 0
        ]);
    }
    fclose($output);
    exit;
}
?>
