<?php
session_start();
require_once __DIR__ . '/../../script/Supabase.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

// Verificar rol (Visualizador)
$slug = strtolower(str_replace(['_',' '], '-', $_SESSION['user_rol_slug'] ?? ''));
if ($slug !== 'visualizador') {
    header('Location: ../../router.php');
    exit();
}

$sb = new Supabase();

// Obtener estadísticas de solo lectura
$noticias = $sb->from('noticias', ['select' => 'id,estado']) ?? [];
$usuarios = $sb->from('usuarios', ['select' => 'id']) ?? [];
$visitas = $sb->from('visitas', ['select' => 'id', 'fecha' => 'eq.' . date('Y-m-d')]) ?? [];

$totalNoticias = is_array($noticias) ? count($noticias) : 0;
$totalUsuarios = is_array($usuarios) ? count($usuarios) : 0;
$visitasHoy = is_array($visitas) ? count($visitas) : 0;

// Contar noticias publicadas
$noticiasPublicadas = 0;
if (is_array($noticias)) {
    foreach ($noticias as $n) {
        if (($n['estado'] ?? '') === 'publicado') $noticiasPublicadas++;
    }
}

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Visualizador</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin-style.css?v=3.0">
</head>
<body>

<div class="admin-wrapper">
    <nav class="admin-sidebar">
        <a class="sidebar-brand" href="visualizador_dashboard.php">Visualizador</a>
        <ul class="sidebar-nav nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="#"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../reportes_visitas.php"><i class="fas fa-chart-line"></i>Reportes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../../index.php" target="_blank"><i class="fas fa-globe"></i>Ver Sitio Web</a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle fa-2x me-2"></i>
                    <strong><?php echo htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario'); ?></strong>
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

    <div class="admin-content">
        <div class="admin-topbar">
            <div class="navbar-admin">
                <div class="brand">Dashboard Visualizador</div>
                <div class="spacer"></div>
                <div class="user"><?php echo htmlspecialchars($_SESSION['user_nombre'] ?? ''); ?></div>
            </div>
        </div>

        <main class="admin-main container-fluid">
            
            <!-- Mensaje de error o advertencia si viene de acceso denegado -->
            <?php if (isset($_GET['mensaje'])): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle"></i> <strong><?php echo htmlspecialchars($_GET['mensaje']); ?></strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Mensaje de bienvenida -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-success">
                        <i class="fas fa-eye"></i> <strong>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_nombre']); ?>!</strong> 
                        Como Visualizador tienes acceso de solo lectura a reportes y estadísticas del sistema.
                    </div>
                </div>
            </div>

            <!-- Tarjetas de Estadísticas (Solo Lectura) -->
            <div class="row mb-4">
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Usuarios Totales</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalUsuarios); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Noticias Publicadas</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($noticiasPublicadas); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-newspaper fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Visitas Hoy</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($visitasHoy); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-eye fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accesos a Reportes -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Reportes Disponibles</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="../../reportes_visitas.php" class="btn btn-success btn-lg">
                                    <i class="fas fa-chart-line me-2"></i> Reportes de Visitas
                                </a>
                                <a href="../../../index.php" target="_blank" class="btn btn-info btn-lg">
                                    <i class="fas fa-globe me-2"></i> Ver Sitio Público
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Permisos del Visualizador</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Ver reportes y estadísticas</li>
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Exportar datos a CSV</li>
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Ver contenido publicado</li>
                                <li class="list-group-item"><i class="fas fa-times text-danger"></i> Crear o editar contenido</li>
                                <li class="list-group-item"><i class="fas fa-times text-danger"></i> Gestionar usuarios</li>
                                <li class="list-group-item"><i class="fas fa-times text-danger"></i> Modificar configuraciones</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle"></i> Información</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">
                                <strong>Rol de Solo Lectura:</strong> Tu cuenta tiene permisos de visualización únicamente. 
                                Puedes consultar todos los reportes y estadísticas del sistema, pero no realizar cambios. 
                                Si necesitas permisos adicionales, contacta al administrador del sistema.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/admin-mobile.js"></script>
</body>
</html>