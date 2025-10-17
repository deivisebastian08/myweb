<?php
session_start();

// COMENTARIO: Verificación de sesión y rol para acceso a este dashboard.
// Solo los usuarios con rol_id = 1 (Lector) pueden acceder.
if(!isset($_SESSION['login']) || $_SESSION['rol_id'] != 1){
    header("location:index.php?mensaje=Acceso denegado. Inicie sesión con un rol válido.");
    exit();
}

// Verificación de tiempo de sesión (se mantiene la lógica existente)
$fecGuar = $_SESSION["hora"];
$ahora = date("Y-n-j H:i:s");
$tmpTrans = (strtotime($ahora)-strtotime($fecGuar));
if($tmpTrans >= 12000){
    session_destroy();
    header("Location: index.php?mensaje=Su sesión ha expirado.");
    exit();
} else {
    $_SESSION["hora"] = $ahora;
}

// COMENTARIO: Incluir la conexión a la base de datos si fuera necesario para mostrar contenido.
// require_once("script/conex.php");
// $cn = new MySQLcn();
// ... (código para obtener noticias o contenido de lectura)
// $cn->Close();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Lector</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- COMENTARIO: Enlazamos la hoja de estilos del panel de administración. -->
    <link rel="stylesheet" href="css/admin-style.css?v=1.2">
</head>
<body>

<div class="admin-wrapper">
    <!-- COMENTARIO: Barra Lateral de Navegación (Sidebar) -->
    <nav class="admin-sidebar">
        <a class="sidebar-brand" href="reader_dashboard.php">LectorPanel</a>
        <ul class="sidebar-nav nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="reader_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../index.php"><i class="fas fa-home"></i>Ver Sitio Web</a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle fa-2x me-2"></i>
                    <strong><?php echo $_SESSION["nombre"]; ?></strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                    <li><a class="dropdown-item" href="#">Perfil</a></li>
                    <li><a class="dropdown-item" href="#">Configuración</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- COMENTARIO: Contenido Principal del Dashboard de Lector -->
    <main class="admin-content">
        <div class="container-fluid">
            <h1 class="h3 mb-4">Bienvenido, <?php echo $_SESSION["nombre"]; ?></h1>
            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle me-2"></i>Estás en el Dashboard de Lector. Aquí puedes ver contenido publicado.
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    Noticias Recientes
                </div>
                <div class="card-body">
                    <p>Aquí se mostrarían las últimas noticias o artículos a los que tienes acceso.</p>
                    <p class="text-muted">Funcionalidad de visualización de contenido para el rol de Lector.</p>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
