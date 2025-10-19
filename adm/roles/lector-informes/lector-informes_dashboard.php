<?php
session_start();
require_once __DIR__ . '/../../script/Supabase.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

// Verificar rol (Lector de Informes)
$slug = strtolower(str_replace(['_',' '], '-', $_SESSION['user_rol_slug'] ?? ''));
if ($slug !== 'lector-informes') {
    header('Location: ../../router.php');
    exit();
}

$sb = new Supabase();

// Obtener estadísticas básicas para reportes
$visitas = $sb->from('visitas', ['select' => 'id']) ?? [];
$visitasHoy = $sb->from('visitas', ['select' => 'id', 'fecha' => 'eq.' . date('Y-m-d')]) ?? [];
$noticias = $sb->from('noticias', ['select' => 'id,estado']) ?? [];

$totalVisitas = is_array($visitas) ? count($visitas) : 0;
$visitasDia = is_array($visitasHoy) ? count($visitasHoy) : 0;
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
    <title>Dashboard - Lector de Informes</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin-style.css?v=3.0">
</head>
<body>

<div class="admin-wrapper">
    <nav class="admin-sidebar">
        <a class="sidebar-brand" href="lector-informes_dashboard.php">Lector</a>
        <ul class="sidebar-nav nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="#"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../reportes_visitas.php"><i class="fas fa-chart-line"></i>Reportes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../../articulos.php" target="_blank"><i class="fas fa-book-open"></i>Artículos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../../perfil.php" target="_blank"><i class="fas fa-user-circle"></i>Mi Perfil</a>
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
                <div class="brand">Dashboard Lector de Informes</div>
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
                    <div class="alert alert-primary">
                        <i class="fas fa-book-reader"></i> <strong>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_nombre']); ?>!</strong> 
                        Como Lector tienes acceso a reportes, artículos publicados y gestión de tu perfil personal.
                    </div>
                </div>
            </div>

            <!-- Tarjetas de Estadísticas -->
            <div class="row mb-4">
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Visitas Totales</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalVisitas); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-eye fa-2x text-gray-300"></i>
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
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Visitas Hoy</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($visitasDia); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Noticias Publicadas</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($noticiasPublicadas); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-newspaper fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accesos Principales -->
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-primary text-white">
                            <h6 class="m-0 font-weight-bold"><i class="fas fa-chart-line"></i> Reportes</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">Visualiza estadísticas y exporta datos</p>
                            <div class="d-grid gap-2">
                                <a href="../../reportes_visitas.php" class="btn btn-primary">
                                    <i class="fas fa-chart-bar me-2"></i> Ver Reportes
                                </a>
                                <a href="../../reportes_visitas.php?export=csv" class="btn btn-outline-primary">
                                    <i class="fas fa-file-csv me-2"></i> Exportar CSV
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-success text-white">
                            <h6 class="m-0 font-weight-bold"><i class="fas fa-newspaper"></i> Artículos</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">Lee las noticias y artículos publicados</p>
                            <div class="d-grid gap-2">
                                <a href="../../../articulos.php" class="btn btn-success" target="_blank">
                                    <i class="fas fa-book-open me-2"></i> Ver Artículos
                                </a>
                                <a href="../../../index.php" class="btn btn-outline-success" target="_blank">
                                    <i class="fas fa-globe me-2"></i> Sitio Web
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-info text-white">
                            <h6 class="m-0 font-weight-bold"><i class="fas fa-user"></i> Mi Cuenta</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">Gestiona tu perfil personal</p>
                            <div class="d-grid gap-2">
                                <a href="../../../perfil.php" class="btn btn-info" target="_blank">
                                    <i class="fas fa-user-circle me-2"></i> Mi Perfil
                                </a>
                                <a href="../../../cuenta_usuario.php" class="btn btn-outline-info" target="_blank">
                                    <i class="fas fa-cog me-2"></i> Mi Cuenta
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permisos -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Permisos del Lector</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-success"><i class="fas fa-check-circle"></i> Puedes:</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> Ver todos los reportes y estadísticas</li>
                                        <li><i class="fas fa-check text-success"></i> Exportar datos a CSV</li>
                                        <li><i class="fas fa-check text-success"></i> Leer artículos y noticias publicadas</li>
                                        <li><i class="fas fa-check text-success"></i> Comentar en artículos</li>
                                        <li><i class="fas fa-check text-success"></i> Gestionar tu perfil personal</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-danger"><i class="fas fa-times-circle"></i> No puedes:</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-times text-danger"></i> Crear o editar noticias</li>
                                        <li><i class="fas fa-times text-danger"></i> Gestionar banners</li>
                                        <li><i class="fas fa-times text-danger"></i> Administrar usuarios</li>
                                        <li><i class="fas fa-times text-danger"></i> Modificar configuración del sistema</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow border-primary">
                        <div class="card-header py-3 bg-light">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle"></i> Tu Rol: Lector de Informes</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">
                                <strong>Como Lector tienes un perfil completo que incluye:</strong>
                            </p>
                            <ul class="mb-3">
                                <li><strong>Reportes Completos:</strong> Acceso total a estadísticas y análisis de visitas con exportación CSV</li>
                                <li><strong>Contenido Público:</strong> Lectura de artículos y noticias publicadas en el sitio</li>
                                <li><strong>Interacción:</strong> Capacidad de comentar en artículos y participar en la comunidad</li>
                                <li><strong>Perfil Personal:</strong> Gestión de tu cuenta, configuración y preferencias</li>
                            </ul>
                            <p class="mb-0 text-muted">
                                <i class="fas fa-lightbulb"></i> <strong>Nota:</strong> Si necesitas permisos adicionales para crear o editar contenido, 
                                contacta al administrador del sistema para solicitar un cambio de rol.
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
