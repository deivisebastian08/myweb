<?php
session_start();

// COMENTARIO: Verificación de sesión y rol para acceso a esta página.
// Solo los usuarios con rol_id >= 2 (Colaborador, Editor, Admin, SuperAdmin) pueden acceder.
if(!isset($_SESSION['login'])){
    header("location:index.php?mensaje=Acceso denegado. Inicie sesión.");
    exit();
} else {
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
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Contenido</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- COMENTARIO: Versión restaurada a 1.1 para las mejoras de UX. -->
    <link rel="stylesheet" href="css/admin-style.css?v=1.1">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Panel de Control</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="user-info nav-link">
                            <i class="fas fa-user me-2"></i><?php echo $_SESSION["nombre"]; ?>
                        </span>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-2"></i>Opciones
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../index.php"><i class="fas fa-home me-2"></i>Página Principal</a></li>
                            <li><a class="dropdown-item" href="cambiar_password.php"><i class="fas fa-key me-2"></i>Cambiar Contraseña</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if (isset($_GET['mensaje'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($_GET['mensaje']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Subir Nueva Imagen para Banner</h4>
                    </div>
                    <div class="card-body p-4">
                        <form id="upload-form" action="procesar_imagen.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="titulo" class="form-label fw-bold">Título</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required>
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label fw-bold">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="link" class="form-label fw-bold">Enlace (Opcional)</label>
                                <input type="text" class="form-control" id="link" name="link">
                            </div>
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" id="estado" name="estado" checked>
                                <label class="form-check-label" for="estado">Publicar inmediatamente</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Imagen del Banner</label>
                                <div class="upload-area" id="uploadArea">
                                    <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                                    <p class="mb-0">Arrastra y suelta una imagen aquí o haz clic para seleccionar</p>
                                    <input type="file" class="d-none" id="imagen" name="imagen" accept="image/*" required>
                                </div>
                                <div id="preview" class="text-center mt-3"></div>
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button id="submit-button" type="submit" class="btn btn-primary btn-lg">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    <span class="button-text"><i class="fas fa-upload me-2"></i>Subir Imagen</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin-script.js"></script>

    <script>
        document.getElementById('upload-form').addEventListener('submit', function() {
            const submitButton = document.getElementById('submit-button');
            submitButton.classList.add('is-loading');
            submitButton.disabled = true;
        });
    </script>
</body>
</html>
