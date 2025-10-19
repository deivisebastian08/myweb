<?php
require_once 'adm/session_config.php';

// 1. Seguridad: Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: adm/index.php?mensaje=' . urlencode('Debes iniciar sesión para ver tu cuenta.'));
    exit();
}

// 2. Seguridad: Este panel es principalmente para 'usuario-publico'.
// Si es otro rol, podría ser redirigido a su panel principal.
if ($_SESSION['user_rol_slug'] !== 'usuario-publico') {
    // Opcional: Redirigir a los roles admin a su panel si llegan aquí por error.
    // header('Location: adm/login.php');
    // exit();
}

$nombre_usuario = htmlspecialchars($_SESSION['user_nombre']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Mi Cuenta - MiWeb</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="adm/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #121212; color: #e0e0e0; font-family: 'Poppins', sans-serif; }
        .dashboard-card { background: #1f1f1f; border: 1px solid #333; border-radius: 15px; }
        .welcome-script { font-family: 'Great Vibes', cursive; color: #0d6efd; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-5" style="background-color: #1f1f1f; border-bottom: 1px solid #333;">
        <div class="container">
            <a class="navbar-brand welcome-script" href="index.php" style="font-size: 2rem;">MiWeb</a>
            <div class="ms-auto d-flex align-items-center">
                <a href="index.php" class="nav-link me-3">Inicio</a>
                <a href="articulos.php" class="nav-link me-3">Artículos</a>
                <a href="perfil.php" class="nav-link me-3">Mi Perfil</a>
                <a href="adm/cerrar_sesion.php" class="btn btn-outline-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="dashboard-card p-4 p-md-5 text-center">
                    <h1 class="welcome-script" style="font-size: 3rem;">Mi Cuenta</h1>
                    <p class="lead text-white-50">¡Hola, <?php echo $nombre_usuario; ?>! Bienvenido a tu espacio personal.</p>
                    <hr style="border-color: #444;">
                    <div class="mt-4">
                        <a href="perfil.php" class="btn btn-primary btn-lg m-2"><i class="fas fa-user-edit"></i> Ver/Editar Mi Perfil</a>
                        <a href="articulos.php" class="btn btn-secondary btn-lg m-2"><i class="fas fa-book-reader"></i> Explorar Artículos</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>