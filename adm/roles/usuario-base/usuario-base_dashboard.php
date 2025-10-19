<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

// Verificar rol (Usuario Público/Base)
$slug = strtolower(str_replace(['_',' '], '-', $_SESSION['user_rol_slug'] ?? ''));
if (!in_array($slug, ['usuario-publico', 'usuario_publico', 'usuario-base', 'usuario_base'], true)) {
    header('Location: ../../router.php');
    exit();
}

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Usuario Público</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin-style.css?v=3.0">
</head>
<body>

<div class="admin-wrapper">
    <nav class="admin-sidebar">
        <a class="sidebar-brand" href="usuario-base_dashboard.php">Mi Panel</a>
        <ul class="sidebar-nav nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="#"><i class="fas fa-home"></i>Inicio</a>
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
                <div class="brand">Mi Panel Personal</div>
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
                    <div class="alert alert-info">
                        <i class="fas fa-user"></i> <strong>¡Hola, <?php echo htmlspecialchars($_SESSION['user_nombre']); ?>!</strong> 
                        Bienvenido a tu panel personal. Desde aquí puedes gestionar tu perfil y explorar el sitio.
                    </div>
                </div>
            </div>

            <!-- Tarjetas de Información -->
            <div class="row mb-4">
                <div class="col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user-circle fa-4x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title">Mi Perfil</h5>
                                    <p class="card-text text-muted">Información de tu cuenta</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-globe fa-4x text-success"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title">Sitio Web</h5>
                                    <p class="card-text text-muted">Explora el contenido público</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de la Cuenta -->
            <div class="row mb-4">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-id-card"></i> Información de la Cuenta</h6>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Nombre:</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($_SESSION['user_nombre'] ?? 'N/A'); ?></dd>
                                
                                <dt class="col-sm-4">Email:</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'N/A'); ?></dd>
                                
                                <dt class="col-sm-4">Rol:</dt>
                                <dd class="col-sm-8"><span class="badge bg-info">Usuario Público</span></dd>
                                
                                <dt class="col-sm-4">Estado:</dt>
                                <dd class="col-sm-8"><span class="badge bg-success">Activo</span></dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-key"></i> Acciones Rápidas</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="../../../index.php" target="_blank" class="btn btn-success btn-lg">
                                    <i class="fas fa-globe me-2"></i> Ver Sitio Web
                                </a>
                                <a href="../../logout.php" class="btn btn-outline-danger btn-lg">
                                    <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
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
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-shield-alt"></i> Permisos de tu Cuenta</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-success"><i class="fas fa-check-circle"></i> Puedes:</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> Ver contenido público del sitio</li>
                                        <li><i class="fas fa-check text-success"></i> Acceder a tu panel personal</li>
                                        <li><i class="fas fa-check text-success"></i> Ver tu información de perfil</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-danger"><i class="fas fa-times-circle"></i> No puedes:</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-times text-danger"></i> Crear o editar contenido</li>
                                        <li><i class="fas fa-times text-danger"></i> Acceder al panel administrativo</li>
                                        <li><i class="fas fa-times text-danger"></i> Ver reportes o estadísticas</li>
                                    </ul>
                                </div>
                            </div>
                            <hr>
                            <p class="mb-0 text-muted">
                                <i class="fas fa-info-circle"></i> <strong>Nota:</strong> Si necesitas permisos adicionales para crear contenido o acceder a funciones administrativas, 
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
