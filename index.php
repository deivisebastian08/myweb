<?php
// La lógica para obtener banners de la base de datos está comentada.
/*
require_once("adm/script/conex.php");
$cn = new MySQLcn();
$sql = "SELECT Titulo, Describir, Enlace, Imagen FROM banner WHERE estado = 1 ORDER BY fecha DESC";
$cn->Query($sql);
$banners = $cn->Rows();
$cn->Close();
*/
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calidad de Software</title>
    <!-- Google Fonts (Poppins) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- COMENTARIO: Versión restaurada a 2.3. -->
    <link rel="stylesheet" href="css/styles.css?v=2.3">
</head>
<body>
    <!-- Navigation Menu -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">Calidad de Software</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="#inicio">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#noticias">Noticias</a></li>
                    <li class="nav-item"><a class="nav-link" href="#servicios">Servicios</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
                    <li class="nav-item"><a class="nav-link" href="adm/index.php">Iniciar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Carrusel con imágenes de Internet -->
    <header id="inicio">
        <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>
                <button type="button" data-bs-target="#bannerCarousel" data-bs-slide="1"></button>
                <button type="button" data-bs-target="#bannerCarousel" data-bs-slide="2"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1950&q=80" class="d-block w-100" alt="Consultoría de Negocios">
                    <div class="carousel-caption d-none d-md-block"><h3>Consultoría Estratégica de Software</h3><p>Alineamos la tecnología con sus objetivos de negocio para un crecimiento sostenible.</p><a href="#contacto" class="btn btn-primary">Contáctanos</a></div>
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1517694712202-1428bc648c2a?auto=format&fit=crop&w=1950&q=80" class="d-block w-100" alt="Desarrollo de Software">
                    <div class="carousel-caption d-none d-md-block"><h3>Desarrollo y Calidad de Código</h3><p>Creamos soluciones robustas y escalables con los más altos estándares de calidad.</p><a href="#servicios" class="btn btn-primary">Nuestros Servicios</a></div>
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1555949963-ff9fe0c870eb?auto=format&fit=crop&w=1950&q=80" class="d-block w-100" alt="Seguridad Digital">
                    <div class="carousel-caption d-none d-md-block"><h3>Seguridad y Auditoría de Sistemas</h3><p>Protegemos sus activos digitales con auditorías de seguridad exhaustivas.</p><a href="#servicios" class="btn btn-primary">Saber Más</a></div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
            <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
        </div>
    </header>

    <!-- Sección de Noticias Modificada -->
    <section id="noticias" class="news-section py-5">
        <div class="container">
            <h2 class="text-center mb-5">Últimas Noticias</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="images/news/news_1.png" class="card-img-top" alt="Testing Automatizado">
                        <div class="card-body">
                            <div class="card-meta mb-2">
                                <span class="category">Testing</span> | <span class="date">15 de Julio, 2024</span>
                            </div>
                            <h5 class="card-title">El Futuro del Testing Automatizado</h5>
                            <p class="card-text">Descubre las nuevas tendencias y herramientas que están revolucionando las pruebas de software.</p>
                            <a href="#" class="btn btn-primary">Leer más</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="images/news/news_2.jpg" class="card-img-top" alt="DevOps y Calidad">
                        <div class="card-body">
                            <div class="card-meta mb-2">
                                <span class="category">DevOps</span> | <span class="date">10 de Julio, 2024</span>
                            </div>
                            <h5 class="card-title">DevOps y su Impacto en la Calidad</h5>
                            <p class="card-text">Cómo la integración continua y la entrega continua son clave para un software de alta calidad.</p>
                            <a href="#" class="btn btn-primary">Leer más</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="images/news/news_3.jpg" class="card-img-top" alt="Seguridad en el Desarrollo">
                        <div class="card-body">
                            <div class="card-meta mb-2">
                                <span class="category">Seguridad</span> | <span class="date">5 de Julio, 2024</span>
                            </div>
                            <h5 class="card-title">Seguridad Desde el Inicio (DevSecOps)</h5>
                            <p class="card-text">Integra la seguridad en cada fase del ciclo de vida del desarrollo para crear aplicaciones robustas.</p>
                            <a href="#" class="btn btn-primary">Leer más</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="#" class="btn btn-outline-primary">Ver Todas las Noticias</a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="servicios" class="services-section py-5">
        <div class="container">
            <h2 class="text-center mb-5">Nuestros Servicios</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="service-item">
                        <i class="fas fa-robot fa-3x mb-3"></i>
                        <h4>Testing Automatizado</h4>
                        <p>Implementamos pruebas automatizadas para asegurar la calidad continua de tu software.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="service-item">
                        <i class="fas fa-shield-alt fa-3x mb-3"></i>
                        <h4>Auditoría de Seguridad</h4>
                        <p>Analizamos y fortalecemos la seguridad de tus aplicaciones contra vulnerabilidades.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="service-item">
                        <i class="fas fa-rocket fa-3x mb-3"></i>
                        <h4>Consultoría DevOps</h4>
                        <p>Optimizamos tus ciclos de desarrollo y despliegue con las mejores prácticas DevOps.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="cta-section text-white text-center py-5">
        <div class="container">
            <h2 class="display-4">¿Listo para empezar?</h2>
            <p class="lead">Lleva la calidad de tu software al siguiente nivel con nosotros.</p>
            <a href="#contacto" class="btn btn-light btn-lg">¡Contáctanos!</a>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contacto" class="contact-section py-5">
        <div class="container">
            <h2 class="text-center mb-5">Contacto</h2>
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <p class="text-center">¿Tienes alguna pregunta? Envíanos un mensaje y te responderemos a la brevedad.</p>
                    <form>
                        <div class="mb-3"><input type="text" class="form-control" placeholder="Nombre"></div>
                        <div class="mb-3"><input type="email" class="form-control" placeholder="Correo Electrónico"></div>
                        <div class="mb-3"><textarea class="form-control" rows="5" placeholder="Mensaje"></textarea></div>
                        <div class="text-center"><button type="submit" class="btn btn-primary">Enviar Mensaje</button></div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <div class="social-icons mb-3">
                <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-white me-3"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" class="text-white"><i class="fab fa-github"></i></a>
            </div>
            <p>&copy; 2024 Calidad de Software. Todos los derechos reservados.</p>
        </div>
    </footer>

    <a href="#inicio" class="scroll-to-top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/main.js"></script>
</body>
</html>