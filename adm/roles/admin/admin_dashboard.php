<?php
session_start();
require_once __DIR__ . '/../../script/Supabase.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

// Verificar rol (Administrador)
$slug = strtolower(str_replace(['_',' '], '-', $_SESSION['user_rol_slug'] ?? ''));
if (!in_array($slug, ['admin','administrador'], true)) {
    header('Location: ../../router.php');
    exit();
}

$sb = new Supabase();

// Obtener estadísticas básicas
$usuarios = $sb->from('usuarios', ['select' => 'id']) ?? [];
$banners = $sb->from('banners', ['select' => 'id']) ?? [];
$noticias = $sb->from('noticias', ['select' => 'id']) ?? [];

$totalUsuarios = is_array($usuarios) ? count($usuarios) : 0;
$totalBanners = is_array($banners) ? count($banners) : 0;
$totalNoticias = is_array($noticias) ? count($noticias) : 0;

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administrador</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin-style.css?v=3.0">
</head>
<body>

<div class="admin-wrapper">
    <nav class="admin-sidebar">
        <a class="sidebar-brand" href="admin_dashboard.php">Administrador</a>
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
        </ul>
        <div class="sidebar-footer">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown">
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
                <div class="brand">Dashboard Administrador</div>
                <div class="spacer"></div>
                <div class="user"><?php echo htmlspecialchars($_SESSION['user_nombre'] ?? ''); ?></div>
            </div>
        </div>

        <main class="admin-main container-fluid">
            
            <!-- Mensaje de bienvenida -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_nombre']); ?>!</strong> 
                        Como Administrador puedes gestionar usuarios, contenido y ver reportes del sistema.
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
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Usuarios</div>
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
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Noticias</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalNoticias); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-newspaper fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Banners</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalBanners); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-images fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accesos Rápidos -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Acciones Rápidas</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="../../usuarios_admin.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-users-cog me-2"></i> Gestionar Usuarios
                                </a>
                                <a href="../../noticias_admin_editorial.php" class="btn btn-success btn-lg">
                                    <i class="fas fa-newspaper me-2"></i> Gestionar Artículos
                                </a>
                                <a href="../../banners_admin.php" class="btn btn-warning btn-lg text-dark">
                                    <i class="fas fa-images me-2"></i> Gestionar Banners
                                </a>
                                <a href="../../reportes_visitas.php" class="btn btn-info btn-lg">
                                    <i class="fas fa-chart-line me-2"></i> Ver Reportes
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Permisos del Administrador</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Gestionar usuarios (sin cambiar roles)</li>
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Crear y editar noticias</li>
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Gestionar banners</li>
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Ver reportes y estadísticas</li>
                                <li class="list-group-item"><i class="fas fa-times text-danger"></i> Modificar configuración del sistema</li>
                                <li class="list-group-item"><i class="fas fa-times text-danger"></i> Cambiar roles de usuarios</li>
                            </ul>
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
