<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

// Verificar rol (Invitado Demo)
$slug = strtolower(str_replace(['_',' '], '-', $_SESSION['user_rol_slug'] ?? ''));
if ($slug !== 'invitado-demo') {
    header('Location: ../../router.php');
    exit();
}

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Invitado Demo</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin-style.css?v=3.0">
</head>
<body>

<div class="admin-wrapper">
    <nav class="admin-sidebar">
        <a class="sidebar-brand" href="invitado-demo_dashboard.php">Demo</a>
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
                <div class="brand">Panel Demo</div>
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
                        <i class="fas fa-exclamation-triangle"></i> <strong>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_nombre']); ?>!</strong> 
                        Esta es una cuenta de <strong>demostración</strong> con acceso muy limitado. No puedes realizar cambios en el sistema.
                    </div>
                </div>
            </div>

            <!-- Tarjeta de Información -->
            <div class="row mb-4">
                <div class="col-md-8 offset-md-2">
                    <div class="card border-left-warning shadow">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-user-clock fa-5x text-warning mb-3"></i>
                            <h3 class="mb-3">Cuenta de Demostración</h3>
                            <p class="lead text-muted">
                                Esta cuenta es solo para propósitos de demostración. 
                                Tienes acceso mínimo al sistema.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Disponibles -->
            <div class="row mb-4">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-check-circle"></i> Puedes hacer</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <i class="fas fa-check text-success"></i> Ver este panel de demostración
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-check text-success"></i> Acceder al sitio web público
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-check text-success"></i> Cerrar tu sesión
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-times-circle"></i> No puedes hacer</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <i class="fas fa-times text-danger"></i> Ver reportes o estadísticas
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-times text-danger"></i> Crear o editar contenido
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-times text-danger"></i> Acceder al panel administrativo
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-times text-danger"></i> Gestionar usuarios
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-times text-danger"></i> Modificar configuraciones
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-th"></i> Acciones Disponibles</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="../../../index.php" target="_blank" class="btn btn-primary btn-lg">
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

            <!-- Información de Contacto -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-body text-center">
                            <p class="mb-0 text-muted">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Si necesitas acceso completo:</strong> Contacta al administrador del sistema para solicitar 
                                una cuenta con permisos completos. Esta cuenta demo tiene restricciones de seguridad.
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
