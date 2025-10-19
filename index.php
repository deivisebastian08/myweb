<?php
session_start();

// Registrar visita automáticamente
require_once 'registro_visita.php';
require_once 'adm/script/Supabase.php';

// Detectar si el usuario es Super Admin
$rolSlug = strtolower(str_replace(['_',' '], '-', $_SESSION['user_rol_slug'] ?? ''));
$rolId = isset($_SESSION['rol_id']) ? (int)$_SESSION['rol_id'] : null;
$isSuperAdmin = in_array($rolSlug, ['super-admin','super_admin','admin-principal'], true) || ($rolId === 1);

// Obtener noticias publicadas desde Supabase
$sb = new Supabase();
$noticias = $sb->from('noticias', [
    'select' => 'id,titulo,slug,extracto,imagen_destacada,fecha_publicacion,vistas,tags',
    'estado' => 'eq.publicado',
    'order' => 'fecha_publicacion.desc',
    'limit' => 6
]) ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calidad de Software</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- COMENTARIO: Versión actualizada a 3.0 para la nueva paleta de colores. -->
    <link rel="stylesheet" href="css/styles.css?v=3.0">
    <link rel="stylesheet" href="css/apple-effects.css?v=1.0">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
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
                    <?php if ($isSuperAdmin) { ?>
                        <li class="nav-item"><a class="nav-link" href="adm/router.php">Volver a tu Panel</a></li>
                    <?php } else { ?>
                        <li class="nav-item"><a class="nav-link" href="adm/index.php">Iniciar Sesión</a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION with PARALLAX SCROLLING -->
    <header id="inicio" class="apple-hero">
        <div class="parallax-layer back" style="background: url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1950&q=80') center/cover; opacity: 0.3;"></div>
        <div class="parallax-layer middle" style="background: radial-gradient(circle, rgba(102,126,234,0.3) 0%, transparent 70%);"></div>
        
        <div class="apple-hero-content">
            <h1>Calidad de Software Premium</h1>
            <p>Llevamos tu negocio al siguiente nivel con soluciones tecnológicas de clase mundial</p>
            <div class="cta-buttons">
                <a href="#servicios" class="btn-primary-apple">Explorar Servicios</a>
                <a href="#contacto" class="btn-secondary-apple">Contáctanos</a>
            </div>
        </div>
    </header>

    <!-- NOTICIAS DESDE SUPABASE -->
    <section id="noticias" class="py-5" style="background: white;">
        <div class="container">
            <h2 class="text-center mb-2" style="font-size: 3rem; font-weight: 700; color: var(--apple-black);">Últimas Noticias</h2>
            <p class="text-center mb-5" style="font-size: 1.2rem; color: #666;">Mantente informado con las últimas tendencias en desarrollo de software</p>
            
            <?php if (count($noticias) > 0): ?>
            <div class="row">
                <?php 
                $gradients = [
                    'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                    'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                    'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                    'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
                    'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
                    'linear-gradient(135deg, #30cfd0 0%, #330867 100%)'
                ];
                foreach ($noticias as $index => $noticia): 
                    $gradiente = $gradients[$index % count($gradients)];
                    $imagen = $noticia['imagen_destacada'] ?? 'https://images.unsplash.com/photo-1517694712202-1428bc648c2a?auto=format&fit=crop&w=800&q=80';
                    $titulo = htmlspecialchars($noticia['titulo']);
                    $extracto = htmlspecialchars($noticia['extracto'] ?? 'Lee más sobre este tema...');
                    $slug = htmlspecialchars($noticia['slug']);
                    $fecha = date('d M Y', strtotime($noticia['fecha_publicacion'] ?? $noticia['created_at'] ?? 'now'));
                    $vistas = number_format($noticia['vistas'] ?? 0);
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm" style="border: none; border-radius: 15px; overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease;" 
                         onmouseover="this.style.transform='translateY(-10px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.15)';" 
                         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)';">
                        <div style="position: relative; height: 220px; overflow: hidden;">
                            <img src="<?php echo $imagen; ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo $titulo; ?>" 
                                 style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;">
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 1rem; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);">
                                <span style="background: rgba(255,255,255,0.9); color: #333; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                    <i class="far fa-calendar"></i> <?php echo $fecha; ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column" style="padding: 1.5rem;">
                            <h5 class="card-title" style="font-weight: 700; font-size: 1.2rem; color: #1a1a1a; margin-bottom: 1rem; line-height: 1.4;">
                                <?php echo $titulo; ?>
                            </h5>
                            <p class="card-text" style="color: #666; font-size: 0.95rem; line-height: 1.6; flex-grow: 1;">
                                <?php echo substr($extracto, 0, 120) . (strlen($extracto) > 120 ? '...' : ''); ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top: 1px solid #e0e0e0;">
                                <a href="articulos.php?slug=<?php echo $slug; ?>" class="btn btn-sm" style="background: <?php echo $gradiente; ?>; color: white; border: none; padding: 0.5rem 1.5rem; border-radius: 25px; font-weight: 600; transition: all 0.3s;" 
                                   onmouseover="this.style.transform='scale(1.05)';" 
                                   onmouseout="this.style.transform='scale(1)';">
                                    Leer más <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                                <small class="text-muted">
                                    <i class="far fa-eye"></i> <?php echo $vistas; ?> vistas
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="articulos.php" class="btn btn-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 1rem 3rem; border-radius: 50px; font-weight: 600; transition: all 0.3s;" 
                   onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 8px 20px rgba(102, 126, 234, 0.4)';" 
                   onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                    Ver Todos los Artículos <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
            <?php else: ?>
            <div class="alert alert-info text-center" style="font-size: 1.1rem;">
                <i class="fas fa-info-circle"></i> Aún no hay noticias publicadas. ¡Vuelve pronto!
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- REVEAL ON SCROLL SECTION -->
    <section id="servicios" class="reveal-section" style="background: white;">
        <div class="container">
            <h2 class="text-center mb-5" style="font-size: 3rem; font-weight: 700; color: var(--apple-black);">Nuestros Servicios</h2>
            
            <div class="reveal-item">
                <div class="reveal-image">
                    <img src="https://images.unsplash.com/photo-1555949963-ff9fe0c870eb?auto=format&fit=crop&w=800&q=80" alt="Testing">
                </div>
                <div class="reveal-content">
                    <h3>Testing Automatizado</h3>
                    <p>Implementamos pruebas automatizadas para asegurar la calidad continua de tu software. Nuestro enfoque incluye testing unitario, de integración y end-to-end.</p>
                    <a href="#contacto" class="btn-apple">Saber más</a>
                </div>
            </div>
            
            <div class="reveal-item">
                <div class="reveal-image">
                    <img src="https://images.unsplash.com/photo-1563986768494-4dee2763ff3f?auto=format&fit=crop&w=800&q=80" alt="Seguridad">
                </div>
                <div class="reveal-content">
                    <h3>Auditoría de Seguridad</h3>
                    <p>Analizamos y fortalecemos la seguridad de tus aplicaciones contra vulnerabilidades. Identificamos riesgos y proporcionamos soluciones efectivas.</p>
                    <a href="#contacto" class="btn-apple">Saber más</a>
                </div>
            </div>
            
            <div class="reveal-item">
                <div class="reveal-image">
                    <img src="https://images.unsplash.com/photo-1517694712202-1428bc648c2a?auto=format&fit=crop&w=800&q=80" alt="DevOps">
                </div>
                <div class="reveal-content">
                    <h3>Consultoría DevOps</h3>
                    <p>Optimizamos tus ciclos de desarrollo y despliegue con las mejores prácticas DevOps. CI/CD, automatización y monitoreo continuo.</p>
                    <a href="#contacto" class="btn-apple">Saber más</a>
                </div>
            </div>
        </div>
    </section>

    <!-- MAGNETIC CARD SECTION -->
    <section class="magnetic-container" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="magnetic-card">
            <div class="magnetic-card-content">
                <h3>¿Listo para empezar?</h3>
                <p>Lleva la calidad de tu software al siguiente nivel. Nuestro equipo de expertos está listo para ayudarte a alcanzar tus objetivos tecnológicos.</p>
                <div style="margin-top: 2rem;">
                    <a href="#contacto" class="btn-apple" style="display: inline-block;">Contáctanos ahora</a>
                </div>
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e0e0e0;">
                    <p style="font-size: 0.9rem; color: var(--apple-gray); margin: 0;">✓ Respuesta en menos de 24 horas<br>✓ Consulta inicial gratuita<br>✓ Sin compromiso</p>
                </div>
            </div>
        </div>
    </section>

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

    <a href="#inicio" class="scroll-to-top"><i class="fas fa-arrow-up"></i></a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/apple-effects.js"></script>
</body>
</html>