<?php
session_start();

// 1. Seguridad: Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: adm/index.php?mensaje=' . urlencode('Debes iniciar sesión para ver tu perfil.'));
    exit();
}

// 2. Obtener datos del usuario desde Supabase
require_once("adm/script/Supabase.php");
$sb = new Supabase();
$user_id = $_SESSION['user_id'];

// Obtener datos completos del usuario
$userData = $sb->from('usuarios', [
    'select' => 'id,nombre,email,estado,created_at,rol_id',
    'id' => 'eq.' . $user_id,
    'limit' => 1
]);

// Si por alguna razón no se encuentra el usuario (ej. fue eliminado), cerrar sesión
if (!$userData || empty($userData)) {
    header('Location: adm/logout.php');
    exit();
}

$usuario = $userData[0];

// Obtener nombre del rol
$rolData = $sb->from('roles', [
    'select' => 'nombre,slug',
    'id' => 'eq.' . $usuario['rol_id'],
    'limit' => 1
]);
$usuario['rol_nombre'] = $rolData && count($rolData) > 0 ? $rolData[0]['nombre'] : 'Usuario';
$usuario['rol_slug'] = $rolData && count($rolData) > 0 ? $rolData[0]['slug'] : 'usuario';

// Obtener estadísticas del usuario
$estadisticas = [
    'noticias_creadas' => 0,
    'noticias_publicadas' => 0,
    'total_vistas' => 0
];

// Contar noticias creadas por el usuario
$noticiasUsuario = $sb->from('noticias', [
    'select' => 'id,estado,vistas',
    'autor_id' => 'eq.' . $user_id
]) ?? [];

$estadisticas['noticias_creadas'] = count($noticiasUsuario);
foreach ($noticiasUsuario as $noticia) {
    if ($noticia['estado'] === 'publicado') {
        $estadisticas['noticias_publicadas']++;
    }
    $estadisticas['total_vistas'] += $noticia['vistas'] ?? 0;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Mi Perfil - Calidad de Software</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css?v=3.0">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            font-weight: 700;
            color: #667eea;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
        }
        .info-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Calidad de Software</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="articulos.php">Artículos</a></li>
                    <li class="nav-item"><a class="nav-link active" href="perfil.php">Mi Perfil</a></li>
                    <li class="nav-item"><a class="nav-link" href="adm/router.php">Mi Panel</a></li>
                    <li class="nav-item"><a class="nav-link" href="adm/logout.php"><i class="fas fa-sign-out-alt"></i> Salir</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="profile-header">
        <div class="container text-center">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
            </div>
            <h1 class="mb-2"><?php echo htmlspecialchars($usuario['nombre']); ?></h1>
            <p class="lead mb-3">
                <span class="badge" style="background: rgba(255,255,255,0.3); padding: 8px 20px; font-size: 1rem;">
                    <i class="fas fa-user-tag"></i> <?php echo htmlspecialchars($usuario['rol_nombre']); ?>
                </span>
            </p>
            <p style="opacity: 0.9;">
                <i class="far fa-calendar"></i> Miembro desde <?php echo date('F Y', strtotime($usuario['created_at'])); ?>
            </p>
        </div>
    </div>

    <div class="container pb-5">
        
        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card text-center">
                    <i class="fas fa-newspaper fa-2x mb-3" style="color: #667eea;"></i>
                    <div class="stat-number"><?php echo $estadisticas['noticias_creadas']; ?></div>
                    <div class="text-muted">Artículos Creados</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card text-center">
                    <i class="fas fa-check-circle fa-2x mb-3" style="color: #10b981;"></i>
                    <div class="stat-number"><?php echo $estadisticas['noticias_publicadas']; ?></div>
                    <div class="text-muted">Artículos Publicados</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card text-center">
                    <i class="fas fa-eye fa-2x mb-3" style="color: #f59e0b;"></i>
                    <div class="stat-number"><?php echo number_format($estadisticas['total_vistas']); ?></div>
                    <div class="text-muted">Vistas Totales</div>
                </div>
            </div>
        </div>

        <!-- Información del Perfil -->
        <div class="row">
            <div class="col-md-8">
                <div class="info-card">
                    <h4 class="mb-4" style="color: #1a1a1a; font-weight: 700;">
                        <i class="fas fa-user-circle me-2" style="color: #667eea;"></i>
                        Información Personal
                    </h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted mb-1"><small>Nombre Completo</small></label>
                            <div style="font-size: 1.1rem; font-weight: 600; color: #1a1a1a;">
                                <?php echo htmlspecialchars($usuario['nombre']); ?>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted mb-1"><small>Correo Electrónico</small></label>
                            <div style="font-size: 1.1rem; font-weight: 600; color: #1a1a1a;">
                                <?php echo htmlspecialchars($usuario['email']); ?>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted mb-1"><small>Rol de Usuario</small></label>
                            <div style="font-size: 1.1rem; font-weight: 600; color: #1a1a1a;">
                                <?php echo htmlspecialchars($usuario['rol_nombre']); ?>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted mb-1"><small>Estado de la Cuenta</small></label>
                            <div>
                                <span class="badge <?php echo $usuario['estado'] === 'activo' ? 'bg-success' : 'bg-warning'; ?>" style="font-size: 0.9rem; padding: 6px 12px;">
                                    <?php echo ucfirst($usuario['estado']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="text-muted mb-1"><small>Fecha de Registro</small></label>
                            <div style="font-size: 1.1rem; font-weight: 600; color: #1a1a1a;">
                                <?php echo date('d \d\e F \d\e Y, H:i', strtotime($usuario['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="info-card">
                    <h5 class="mb-4" style="color: #1a1a1a; font-weight: 700;">
                        <i class="fas fa-link me-2" style="color: #667eea;"></i>
                        Accesos Rápidos
                    </h5>
                    <div class="d-grid gap-2">
                        <a href="adm/router.php" class="btn btn-outline-primary">
                            <i class="fas fa-tachometer-alt me-2"></i> Mi Panel de Control
                        </a>
                        <?php if ($estadisticas['noticias_creadas'] > 0): ?>
                        <a href="adm/noticias_admin_editorial.php" class="btn btn-outline-success">
                            <i class="fas fa-newspaper me-2"></i> Mis Artículos
                        </a>
                        <?php endif; ?>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i> Ir al Inicio
                        </a>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="d-grid gap-2">
                        <a href="adm/logout.php" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">© <?php echo date('Y'); ?> Calidad de Software. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>