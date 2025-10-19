-- ============================================================================
-- SCRIPT SQL PARA CREAR TABLAS EN SUPABASE
-- Ejecuta este script en: Supabase Dashboard > SQL Editor > New query
-- ============================================================================

-- 1. TABLA DE CATEGORÍAS
CREATE TABLE IF NOT EXISTS public.categorias (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    tipo VARCHAR(50) DEFAULT 'noticia', -- 'noticia' o 'banner'
    estado VARCHAR(20) DEFAULT 'activo', -- 'activo' o 'inactivo'
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 2. TABLA DE NOTICIAS
CREATE TABLE IF NOT EXISTS public.noticias (
    id BIGSERIAL PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    extracto TEXT,
    contenido TEXT NOT NULL,
    imagen_destacada VARCHAR(500),
    categoria_id BIGINT REFERENCES public.categorias(id) ON DELETE SET NULL,
    estado VARCHAR(20) DEFAULT 'borrador', -- 'borrador', 'revision', 'publicado', 'archivado'
    destacada BOOLEAN DEFAULT false,
    tags VARCHAR(255),
    autor_id BIGINT REFERENCES public.usuarios(id) ON DELETE SET NULL,
    editor_id BIGINT,
    fecha_publicacion TIMESTAMP WITH TIME ZONE,
    vistas INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 3. ÍNDICES PARA MEJORAR RENDIMIENTO
CREATE INDEX IF NOT EXISTS idx_noticias_estado ON public.noticias(estado);
CREATE INDEX IF NOT EXISTS idx_noticias_categoria ON public.noticias(categoria_id);
CREATE INDEX IF NOT EXISTS idx_noticias_autor ON public.noticias(autor_id);
CREATE INDEX IF NOT EXISTS idx_noticias_fecha ON public.noticias(fecha_publicacion);
CREATE INDEX IF NOT EXISTS idx_noticias_slug ON public.noticias(slug);

-- 4. POLÍTICAS RLS (Row Level Security)
-- Habilitar RLS
ALTER TABLE public.categorias ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.noticias ENABLE ROW LEVEL SECURITY;

-- Políticas para CATEGORÍAS (acceso público para lectura)
CREATE POLICY "Permitir lectura pública de categorías"
    ON public.categorias FOR SELECT
    TO anon, authenticated
    USING (estado = 'activo');

CREATE POLICY "Permitir inserción de categorías a usuarios autenticados"
    ON public.categorias FOR INSERT
    TO authenticated
    WITH CHECK (true);

CREATE POLICY "Permitir actualización de categorías a usuarios autenticados"
    ON public.categorias FOR UPDATE
    TO authenticated
    USING (true);

-- Políticas para NOTICIAS (lectura pública solo de publicadas)
CREATE POLICY "Permitir lectura pública de noticias publicadas"
    ON public.noticias FOR SELECT
    TO anon, authenticated
    USING (estado = 'publicado');

CREATE POLICY "Permitir inserción de noticias a usuarios autenticados"
    ON public.noticias FOR INSERT
    TO authenticated
    WITH CHECK (true);

CREATE POLICY "Permitir actualización de noticias propias"
    ON public.noticias FOR UPDATE
    TO authenticated
    USING (true); -- En producción: usar auth.uid() = autor_id

CREATE POLICY "Permitir eliminación de noticias a usuarios autenticados"
    ON public.noticias FOR DELETE
    TO authenticated
    USING (true); -- En producción: restringir por roles

-- 5. FUNCIÓN PARA ACTUALIZAR updated_at AUTOMÁTICAMENTE
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- 6. TRIGGERS PARA updated_at
CREATE TRIGGER update_categorias_updated_at
    BEFORE UPDATE ON public.categorias
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_noticias_updated_at
    BEFORE UPDATE ON public.noticias
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- 7. INSERTAR CATEGORÍAS DE EJEMPLO
INSERT INTO public.categorias (nombre, slug, descripcion, tipo, estado) VALUES
('Tecnología', 'tecnologia', 'Noticias sobre tecnología y desarrollo', 'noticia', 'activo'),
('Negocios', 'negocios', 'Noticias del mundo empresarial', 'noticia', 'activo'),
('Diseño', 'diseno', 'Tendencias y mejores prácticas de diseño', 'noticia', 'activo'),
('Marketing', 'marketing', 'Estrategias y noticias de marketing digital', 'noticia', 'activo'),
('Seguridad', 'seguridad', 'Seguridad informática y ciberseguridad', 'noticia', 'activo'),
('DevOps', 'devops', 'DevOps, CI/CD y automatización', 'noticia', 'activo')
ON CONFLICT (slug) DO NOTHING;

-- 8. INSERTAR NOTICIAS DE EJEMPLO (ajusta el autor_id según tu BD)
-- IMPORTANTE: Reemplaza '1' con el ID de un usuario válido en tu tabla usuarios
INSERT INTO public.noticias (
    titulo, 
    slug, 
    extracto, 
    contenido, 
    imagen_destacada,
    categoria_id,
    estado,
    destacada,
    tags,
    autor_id,
    fecha_publicacion,
    vistas
) VALUES
(
    'El Futuro del Testing Automatizado en 2025',
    'futuro-testing-automatizado-2025',
    'Descubre las nuevas tendencias y herramientas que están revolucionando las pruebas de software en la industria tecnológica.',
    '<h2>Introducción al Testing Moderno</h2><p>El testing automatizado ha evolucionado significativamente en los últimos años. Las herramientas modernas permiten una cobertura de código más completa y pruebas más eficientes.</p><h3>Herramientas Destacadas</h3><ul><li>Playwright para pruebas end-to-end</li><li>Jest para testing unitario</li><li>Cypress para pruebas de integración</li></ul><p>La implementación de estas herramientas ha reducido el tiempo de testing en un 60% en promedio.</p>',
    'https://images.unsplash.com/photo-1555949963-ff9fe0c870eb?auto=format&fit=crop&w=1200&q=80',
    1,
    'publicado',
    true,
    'testing,automatización,qa,desarrollo',
    1,
    NOW() - INTERVAL '2 days',
    156
),
(
    'DevOps: Mejores Prácticas para CI/CD',
    'devops-mejores-practicas-cicd',
    'Cómo la integración continua y la entrega continua son clave para un software de alta calidad y despliegues más rápidos.',
    '<h2>¿Qué es CI/CD?</h2><p>CI/CD es una práctica de desarrollo que permite a los equipos entregar cambios de código de forma más frecuente y confiable.</p><h3>Beneficios Principales</h3><ul><li>Detección temprana de errores</li><li>Despliegues más rápidos</li><li>Mayor calidad del código</li><li>Feedback inmediato</li></ul><p>Implementar un pipeline de CI/CD efectivo requiere planificación y las herramientas adecuadas.</p>',
    'https://images.unsplash.com/photo-1517694712202-1428bc648c2a?auto=format&fit=crop&w=1200&q=80',
    6,
    'publicado',
    true,
    'devops,cicd,automatización,desarrollo',
    1,
    NOW() - INTERVAL '5 days',
    243
),
(
    'Seguridad desde el Inicio: DevSecOps',
    'seguridad-inicio-devsecops',
    'Integra la seguridad en cada fase del ciclo de vida del desarrollo para crear aplicaciones robustas y protegidas.',
    '<h2>DevSecOps en Acción</h2><p>DevSecOps integra la seguridad como parte fundamental del proceso de desarrollo, no como una reflexión tardía.</p><h3>Principios Clave</h3><ul><li>Shift Left Security: seguridad desde el diseño</li><li>Automatización de pruebas de seguridad</li><li>Cultura de responsabilidad compartida</li></ul><p>Las organizaciones que adoptan DevSecOps reportan una reducción del 50% en vulnerabilidades críticas.</p>',
    'https://images.unsplash.com/photo-1563986768494-4dee2763ff3f?auto=format&fit=crop&w=1200&q=80',
    5,
    'publicado',
    false,
    'seguridad,devsecops,desarrollo,ciberseguridad',
    1,
    NOW() - INTERVAL '1 week',
    187
),
(
    'Diseño UX: Tendencias 2025',
    'diseno-ux-tendencias-2025',
    'Las últimas tendencias en diseño de experiencia de usuario que están transformando la industria digital.',
    '<h2>Tendencias UX para 2025</h2><p>El diseño de experiencia de usuario continúa evolucionando con nuevas tecnologías y expectativas de los usuarios.</p><h3>Principales Tendencias</h3><ul><li>Diseño minimalista y limpio</li><li>Microinteracciones significativas</li><li>Accesibilidad como prioridad</li><li>Diseño adaptativo e inclusivo</li></ul><p>Estas tendencias están redefiniendo cómo interactuamos con la tecnología.</p>',
    'https://images.unsplash.com/photo-1561070791-2526d30994b5?auto=format&fit=crop&w=1200&q=80',
    3,
    'publicado',
    false,
    'diseño,ux,tendencias,ui',
    1,
    NOW() - INTERVAL '3 days',
    312
),
(
    'Marketing Digital: Estrategias Efectivas',
    'marketing-digital-estrategias-efectivas',
    'Descubre las estrategias de marketing digital que están generando mejores resultados en 2025.',
    '<h2>Estrategias que Funcionan</h2><p>El marketing digital requiere un enfoque estratégico y basado en datos para obtener resultados medibles.</p><h3>Canales Principales</h3><ul><li>SEO y contenido de calidad</li><li>Marketing en redes sociales</li><li>Email marketing personalizado</li><li>Publicidad programática</li></ul><p>La clave está en la personalización y el análisis de datos.</p>',
    'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80',
    4,
    'publicado',
    false,
    'marketing,digital,estrategia,seo',
    1,
    NOW() - INTERVAL '4 days',
    198
),
(
    'Cloud Computing: Migración Exitosa',
    'cloud-computing-migracion-exitosa',
    'Guía completa para migrar tu infraestructura a la nube de manera segura y eficiente.',
    '<h2>Migración a la Nube</h2><p>La migración a la nube es un paso fundamental para modernizar la infraestructura tecnológica de cualquier organización.</p><h3>Pasos Clave</h3><ul><li>Evaluación de la infraestructura actual</li><li>Selección del proveedor cloud adecuado</li><li>Planificación de la migración</li><li>Pruebas y validación</li></ul><p>Una migración bien planificada puede reducir costos en un 30% y mejorar el rendimiento significativamente.</p>',
    'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1200&q=80',
    1,
    'publicado',
    false,
    'cloud,aws,azure,infraestructura',
    1,
    NOW() - INTERVAL '6 days',
    275
)
ON CONFLICT (slug) DO NOTHING;

-- 9. VERIFICAR INSERCIÓN
SELECT 
    n.id,
    n.titulo,
    n.estado,
    c.nombre as categoria,
    n.vistas,
    n.fecha_publicacion
FROM public.noticias n
LEFT JOIN public.categorias c ON n.categoria_id = c.id
ORDER BY n.fecha_publicacion DESC;

-- ============================================================================
-- INSTRUCCIONES DE USO:
-- ============================================================================
-- 1. Copia todo este script
-- 2. Ve a tu proyecto en Supabase
-- 3. Abre "SQL Editor" en el menú lateral
-- 4. Haz clic en "New query"
-- 5. Pega este script completo
-- 6. Haz clic en "Run" para ejecutarlo
-- 7. Verifica que las tablas se crearon correctamente en "Table Editor"
-- ============================================================================
