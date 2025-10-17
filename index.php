<?php
require_once("adm/script/conex.php");

// Obtener banners activos
$cn = new MySQLcn();
$sql = "SELECT Titulo, Describir, Enlace, Imagen FROM banner WHERE estado = 1 ORDER BY fecha DESC";
$cn->Query($sql);
$banners = $cn->Rows();
$cn->Close();

// Debug para verificar los datos
// echo "<pre>"; print_r($banners); echo "</pre>";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calidad de Software</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- Navigation Menu -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Calidad de Software</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Noticias</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Servicios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contacto</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="adm/index.php">Iniciar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Banner Slider -->
    <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
        <!-- Indicadores -->
        <div class="carousel-indicators">
            <?php for($i = 0; $i < count($banners); $i++): ?>
            <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="<?php echo $i; ?>" 
                    <?php echo $i === 0 ? 'class="active"' : ''; ?>></button>
            <?php endfor; ?>
        </div>

        <!-- Slides -->
        <div class="carousel-inner">
            <?php if(!empty($banners)): ?>
                <?php foreach($banners as $index => $banner): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <img src="images/banner/<?php echo htmlspecialchars($banner['Imagen']); ?>" 
                         class="d-block w-100" alt="<?php echo htmlspecialchars($banner['Titulo']); ?>">
                    <div class="carousel-caption d-none d-md-block">
                        <h3><?php echo htmlspecialchars($banner['Titulo']); ?></h3>
                        <p><?php echo htmlspecialchars($banner['Describir']); ?></p>
                        <?php if(!empty($banner['Enlace'])): ?>
                        <a href="<?php echo htmlspecialchars($banner['Enlace']); ?>" class="btn btn-primary" target="_blank">
                            Ver más <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="carousel-item active">
                    <img src="images/banner/default.jpg" class="d-block w-100" alt="Banner por defecto">
                    <div class="carousel-caption d-none d-md-block">
                        <h3>Bienvenido</h3>
                        <p>No hay banners activos en este momento.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Controles -->
        <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>
    </div>

    <!-- News Section -->
    <section class="news-section py-5">
        <div class="container">
            <h2 class="text-center mb-4">Últimas Noticias</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="images/news/news_1.png" class="card-img-top" alt="Testing Automatizado">
                        <div class="card-body">
                            <h5 class="card-title">Testing Automatizado</h5>
                            <p class="card-text">Descubre las mejores herramientas y prácticas para implementar pruebas automatizadas en tu proyecto.</p>
                            <a href="#" class="btn btn-primary">Leer más</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="images/news/news_2.jpg" class="card-img-top" alt="DevOps">
                        <div class="card-body">
                            <h5 class="card-title">DevOps y Calidad</h5>
                            <p class="card-text">Integración continua y entrega continua: claves para mantener la calidad del software.</p>
                            <a href="#" class="btn btn-primary">Leer más</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="images/news/news_3.jpg" class="card-img-top" alt="Seguridad">
                        <div class="card-body">
                            <h5 class="card-title">Seguridad en el Desarrollo</h5>
                            <p class="card-text">Mejores prácticas para implementar seguridad desde las primeras fases del desarrollo.</p>
                            <a href="#" class="btn btn-primary">Leer más</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2024 Calidad de Software. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/main.js"></script>
</body>
</html>