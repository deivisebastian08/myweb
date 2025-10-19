<?php
session_start();
require_once 'adm/script/Supabase.php';

$sb = new Supabase();
$mostrarArticulo = false;
$articulo = null;
$articulosRelacionados = [];

// Ver si se solicita un artículo específico por slug
if (isset($_GET['slug'])) {
    $slug = trim($_GET['slug']);
    
    // Obtener artículo por slug
    $resultado = $sb->from('noticias', [
        'select' => '*',
        'slug' => 'eq.' . $slug,
        'estado' => 'eq.publicado',
        'limit' => 1
    ]);
    
    if ($resultado && count($resultado) > 0) {
        $articulo = $resultado[0];
        $mostrarArticulo = true;
        
        // Incrementar contador de vistas
        $sb->update('noticias', 
            ['vistas' => ($articulo['vistas'] ?? 0) + 1], 
            ['id' => 'eq.' . $articulo['id']]
        );
        
        // Obtener autor
        $autorData = $sb->from('usuarios', [
            'select' => 'nombre',
            'id' => 'eq.' . $articulo['autor_id'],
            'limit' => 1
        ]);
        $articulo['autor_nombre'] = $autorData && count($autorData) > 0 ? $autorData[0]['nombre'] : 'Autor Desconocido';
        
        // Obtener categoría
        if ($articulo['categoria_id']) {
            $catData = $sb->from('categorias', [
                'select' => 'nombre',
                'id' => 'eq.' . $articulo['categoria_id'],
                'limit' => 1
            ]);
            $articulo['categoria_nombre'] = $catData && count($catData) > 0 ? $catData[0]['nombre'] : 'Sin categoría';
        } else {
            $articulo['categoria_nombre'] = 'Sin categoría';
        }
        
        // Obtener artículos relacionados (misma categoría o aleatorios)
        $articulosRelacionados = $sb->from('noticias', [
            'select' => 'id,titulo,slug,extracto,imagen_destacada,fecha_publicacion',
            'estado' => 'eq.publicado',
            'id' => 'neq.' . $articulo['id'],
            'limit' => 3,
            'order' => 'created_at.desc'
        ]) ?? [];
    }
}

// Si no se muestra artículo individual, obtener lista de todos
if (!$mostrarArticulo) {
    $articulos = $sb->from('noticias', [
        'select' => 'id,titulo,slug,extracto,imagen_destacada,fecha_publicacion,vistas,tags,categoria_id',
        'estado' => 'eq.publicado',
        'order' => 'fecha_publicacion.desc'
    ]) ?? [];
    
    // Enriquecer con nombres de categorías
    foreach ($articulos as &$art) {
        if ($art['categoria_id']) {
            $catData = $sb->from('categorias', [
                'select' => 'nombre',
                'id' => 'eq.' . $art['categoria_id'],
                'limit' => 1
            ]);
            $art['categoria_nombre'] = $catData && count($catData) > 0 ? $catData[0]['nombre'] : 'Sin categoría';
        } else {
            $art['categoria_nombre'] = 'Sin categoría';
        }
    }
    unset($art);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Artículos de Interés - Mi Web</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css?v=3.0">
    <link rel="stylesheet" href="css/apple-effects.css?v=1.0">
    <style>
        .article-hero {
            height: 60vh;
            min-height: 400px;
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            align-items: flex-end;
            margin-bottom: 3rem;
        }
        .article-hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
        }
        .article-hero-content {
            position: relative;
            z-index: 2;
            color: white;
            padding: 0 2rem 3rem;
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }
        .article-content {
            max-width: 750px;
            margin: 0 auto;
            font-size: 1.125rem;
            line-height: 1.8;
            color: #1a1a1a;
        }
        .article-content h2 {
            font-size: 2rem;
            font-weight: 700;
            margin: 2.5rem 0 1rem;
            color: #033f63;
        }
        .article-content h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 2rem 0 0.75rem;
            color: #28666e;
        }
        .article-content p {
            margin-bottom: 1.5rem;
        }
        .article-content img {
            width: 100%;
            border-radius: 8px;
            margin: 2rem 0;
        }
        .article-content ul, .article-content ol {
            margin-bottom: 1.5rem;
            padding-left: 2rem;
        }
        .article-content li {
            margin-bottom: 0.5rem;
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
                    <li class="nav-item"><a class="nav-link active" href="articulos.php">Artículos</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#servicios">Servicios</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#contacto">Contacto</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="adm/router.php">Mi Panel</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="adm/index.php">Iniciar Sesión</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if ($mostrarArticulo && $articulo): ?>
        <!-- VISTA DE ARTÍCULO COMPLETO -->
        <div class="article-hero" style="background-image: url('<?php echo $articulo['imagen_destacada'] ?? 'https://images.unsplash.com/photo-1517694712202-1428bc648c2a?auto=format&fit=crop&w=1200&q=80'; ?>');">
            <div class="article-hero-overlay"></div>
            <div class="article-hero-content">
                <div class="mb-3">
                    <a href="articulos.php" style="color: white; text-decoration: none; opacity: 0.9;">
                        <i class="fas fa-arrow-left"></i> Volver a artículos
                    </a>
                </div>
                <?php if ($articulo['categoria_nombre']): ?>
                    <span class="badge" style="background: rgba(254, 220, 151, 0.9); color: #033f63; padding: 6px 16px; border-radius: 20px; font-size: 0.9rem; margin-bottom: 1rem; display: inline-block;">
                        <?php echo htmlspecialchars($articulo['categoria_nombre']); ?>
                    </span>
                <?php endif; ?>
                <h1 style="font-size: 3rem; font-weight: 800; margin-bottom: 1.5rem; text-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                    <?php echo htmlspecialchars($articulo['titulo']); ?>
                </h1>
                <div style="display: flex; align-items: center; gap: 2rem; flex-wrap: wrap; opacity: 0.95;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: #7c9885; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.2rem;">
                            <?php echo strtoupper(substr($articulo['autor_nombre'], 0, 1)); ?>
                        </div>
                        <div>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($articulo['autor_nombre']); ?></div>
                            <div style="font-size: 0.9rem;"><?php echo date('d M Y', strtotime($articulo['fecha_publicacion'] ?? $articulo['created_at'])); ?></div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="far fa-clock"></i>
                        <?php 
                            $palabras = str_word_count(strip_tags($articulo['contenido']));
                            $minutos = ceil($palabras / 200);
                            echo $minutos;
                        ?> min lectura
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="far fa-eye"></i>
                        <?php echo number_format($articulo['vistas'] ?? 0); ?> vistas
                    </div>
                </div>
            </div>
        </div>

        <div class="container py-5">
            <article class="article-content">
                <?php echo $articulo['contenido']; ?>
            </article>

            <?php if ($articulo['tags']): ?>
            <div class="article-tags" style="max-width: 750px; margin: 3rem auto 0; padding-top: 2rem; border-top: 2px solid #e0e0e0;">
                <h4 style="margin-bottom: 1rem; color: #033f63;">Tags</h4>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <?php 
                    $tags = explode(',', $articulo['tags']);
                    foreach ($tags as $tag): 
                        $tag = trim($tag);
                    ?>
                        <span class="badge" style="background: #b5b682; color: #033f63; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 500;">
                            #<?php echo htmlspecialchars($tag); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (count($articulosRelacionados) > 0): ?>
            <div style="max-width: 750px; margin: 3rem auto;">
                <h3 style="font-size: 2rem; font-weight: 700; margin-bottom: 2rem; color: #033f63;">Artículos Relacionados</h3>
                <div class="row">
                    <?php foreach ($articulosRelacionados as $rel): ?>
                        <div class="col-md-4 mb-3">
                            <a href="articulos.php?slug=<?php echo htmlspecialchars($rel['slug']); ?>" style="text-decoration: none;">
                                <div class="card h-100 shadow-sm" style="border: none; border-radius: 12px; overflow: hidden; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)';" onmouseout="this.style.transform='translateY(0)';">
                                    <img src="<?php echo $rel['imagen_destacada'] ?? 'https://images.unsplash.com/photo-1517694712202-1428bc648c2a?auto=format&fit=crop&w=400&q=80'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($rel['titulo']); ?>" style="height: 150px; object-fit: cover;">
                                    <div class="card-body">
                                        <h6 class="card-title" style="color: #1a1a1a; font-weight: 600; font-size: 0.95rem;">
                                            <?php echo htmlspecialchars(substr($rel['titulo'], 0, 60)) . (strlen($rel['titulo']) > 60 ? '...' : ''); ?>
                                        </h6>
                                        <p class="card-text" style="font-size: 0.85rem; color: #666;">
                                            <?php echo date('d M Y', strtotime($rel['fecha_publicacion'] ?? $rel['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="text-center mt-5">
                <a href="articulos.php" class="btn btn-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 1rem 3rem; border-radius: 50px; font-weight: 600;">
                    <i class="fas fa-arrow-left me-2"></i> Volver a todos los artículos
                </a>
            </div>
        </div>

    <?php else: ?>
        <!-- VISTA DE LISTA DE ARTÍCULOS -->
        <div class="container py-5">
            <div class="text-center mb-5">
                <h1 style="font-size: 3.5rem; font-weight: 800; color: #1a1a1a;">Artículos de Tecnología</h1>
                <p class="lead" style="font-size: 1.3rem; color: #666;">Explora las últimas tendencias en desarrollo, seguridad y calidad de software</p>
            </div>

            <?php if (count($articulos) > 0): ?>
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
                foreach ($articulos as $index => $art): 
                    $gradiente = $gradients[$index % count($gradients)];
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm" style="border: none; border-radius: 15px; overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease;" 
                         onmouseover="this.style.transform='translateY(-10px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.15)';" 
                         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)';">
                        <div style="position: relative; height: 220px; overflow: hidden;">
                            <img src="<?php echo $art['imagen_destacada'] ?? 'https://images.unsplash.com/photo-1517694712202-1428bc648c2a?auto=format&fit=crop&w=800&q=80'; ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($art['titulo']); ?>" 
                                 style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;">
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 1rem; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);">
                                <?php if ($art['categoria_nombre']): ?>
                                <span style="background: rgba(255,255,255,0.9); color: #333; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                    <?php echo htmlspecialchars($art['categoria_nombre']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column" style="padding: 1.5rem;">
                            <h5 class="card-title" style="font-weight: 700; font-size: 1.2rem; color: #1a1a1a; margin-bottom: 1rem; line-height: 1.4;">
                                <?php echo htmlspecialchars($art['titulo']); ?>
                            </h5>
                            <p class="card-text" style="color: #666; font-size: 0.95rem; line-height: 1.6; flex-grow: 1;">
                                <?php echo htmlspecialchars(substr($art['extracto'] ?? '', 0, 120)) . (strlen($art['extracto'] ?? '') > 120 ? '...' : ''); ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top: 1px solid #e0e0e0;">
                                <div>
                                    <small class="text-muted">
                                        <i class="far fa-calendar"></i> <?php echo date('d M Y', strtotime($art['fecha_publicacion'] ?? $art['created_at'])); ?>
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="far fa-eye"></i> <?php echo number_format($art['vistas'] ?? 0); ?> vistas
                                    </small>
                                </div>
                                <a href="articulos.php?slug=<?php echo htmlspecialchars($art['slug']); ?>" class="btn btn-sm" style="background: <?php echo $gradiente; ?>; color: white; border: none; padding: 0.5rem 1.5rem; border-radius: 25px; font-weight: 600; transition: all 0.3s;" 
                                   onmouseover="this.style.transform='scale(1.05)';" 
                                   onmouseout="this.style.transform='scale(1)';">
                                    Leer <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-info text-center" style="font-size: 1.1rem;">
                <i class="fas fa-info-circle"></i> Aún no hay artículos publicados. ¡Vuelve pronto!
            </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>© <?php echo date('Y'); ?> Calidad de Software. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>