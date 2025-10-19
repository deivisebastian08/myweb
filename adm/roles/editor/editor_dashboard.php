<?php
session_start();
require_once __DIR__ . '/../../script/Supabase.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

// Verificar rol (Editor)
$slug = strtolower(str_replace(['_',' '], '-', $_SESSION['user_rol_slug'] ?? ''));
if ($slug !== 'editor') {
    header('Location: ../../router.php');
    exit();
}

$sb = new Supabase();

// Obtener noticias del editor
$noticias = $sb->from('noticias', [
    'select' => 'id,estado',
    'autor_id' => 'eq.' . $_SESSION['user_id']
]) ?? [];

$borradores = 0;
$publicadas = 0;
foreach ($noticias as $n) {
    if (($n['estado'] ?? '') === 'publicado') $publicadas++;
    if (($n['estado'] ?? '') === 'borrador') $borradores++;
}

$totalNoticias = count($noticias);

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Editor</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin-style.css?v=3.0">
</head>
<body>

<div class="admin-wrapper">
    <nav class="admin-sidebar">
        <a class="sidebar-brand" href="editor_dashboard.php">Editor</a>
        <ul class="sidebar-nav nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="#"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../noticias_admin_editorial.php"><i class="fas fa-newspaper"></i>Artículos de Noticia</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../banners_admin.php"><i class="fas fa-images"></i>Banners</a>
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
                <div class="brand">Dashboard Editor</div>
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
                    <div class="alert alert-warning">
                        <i class="fas fa-pen"></i> <strong>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_nombre']); ?>!</strong> 
                        Como Editor puedes crear y gestionar noticias y banners del sitio.
                    </div>
                </div>
            </div>

            <!-- Tarjetas de Estadísticas -->
            <div class="row mb-4">
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Mis Noticias</div>
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
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Publicadas</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($publicadas); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Borradores</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($borradores); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-edit fa-2x text-gray-300"></i>
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
                                <a href="../../noticias_admin.php" class="btn btn-warning btn-lg text-dark">
                                    <i class="fas fa-newspaper me-2"></i> Gestionar Noticias
                                </a>
                                <a href="../../banners_admin.php" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-images me-2"></i> Gestionar Banners
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Permisos del Editor</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Crear nuevas noticias</li>
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Editar mis noticias</li>
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Publicar noticias</li>
                                <li class="list-group-item"><i class="fas fa-check text-success"></i> Gestionar banners</li>
                                <li class="list-group-item"><i class="fas fa-times text-danger"></i> Gestionar usuarios</li>
                                <li class="list-group-item"><i class="fas fa-times text-danger"></i> Eliminar noticias de otros</li>
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