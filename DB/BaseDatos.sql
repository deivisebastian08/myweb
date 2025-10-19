-- ============================================================================
-- BASE DE DATOS COMPLETA: SISTEMA DE CALIDAD DE SOFTWARE - POSTGRESQL
-- Incluye: Roles, Permisos, Registro con Email, Notificaciones, Auditoría
-- VERSIÓN SIN META-COMANDOS DE PSQL (Compatible con todos los clientes)
-- ============================================================================

-- NOTA: Primero crea la base de datos manualmente o ejecuta esto en 2 pasos:
-- PASO 1: Ejecutar solo las líneas de DROP/CREATE DATABASE
-- PASO 2: Conectarte a la base de datos 'myweb' y ejecutar el resto

-- ============================================================================
-- PASO 1: CREACIÓN DE LA BASE DE DATOS (ejecutar en BD 'postgres')
-- ============================================================================
-- DROP DATABASE IF EXISTS myweb;
-- CREATE DATABASE myweb WITH ENCODING 'UTF8' LC_COLLATE='es_PE.UTF-8' LC_CTYPE='es_PE.UTF-8';

-- ============================================================================
-- PASO 2: CONECTARSE A LA BD 'myweb' Y EJECUTAR TODO LO SIGUIENTE
-- ============================================================================

-- ===============================
-- TIPOS ENUM PERSONALIZADOS
-- ===============================

-- Estado general: activo / inactivo
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'estado_general') THEN
        CREATE TYPE estado_general AS ENUM ('activo', 'inactivo');
    END IF;
END;
$$;

-- Estado de usuario: diferentes estados del registro
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'estado_usuario') THEN
        CREATE TYPE estado_usuario AS ENUM ('pendiente', 'activo', 'inactivo', 'suspendido');
    END IF;
END;
$$;

-- Tipos de notificación (para alertas o mensajes del sistema)
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'tipo_notificacion') THEN
        CREATE TYPE tipo_notificacion AS ENUM ('info', 'success', 'warning', 'error', 'mensaje');
    END IF;
END;
$$;

-- Tipos de correos electrónicos (para plantillas de email)
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'tipo_email') THEN
        CREATE TYPE tipo_email AS ENUM ('verificacion', 'recuperacion', 'notificacion', 'bienvenida', 'otro');
    END IF;
END;
$$;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'prioridad_email') THEN
        CREATE TYPE prioridad_email AS ENUM ('alta', 'media', 'baja');
    END IF;
END;
$$;

-- ==============================================
-- TIPOS ENUM PERSONALIZADOS (versión segura)
-- ==============================================

-- Estado general
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'estado_general') THEN
        CREATE TYPE estado_general AS ENUM ('activo', 'inactivo');
    END IF;
END;
$$;

-- Estado de usuario
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'estado_usuario') THEN
        CREATE TYPE estado_usuario AS ENUM ('pendiente', 'activo', 'inactivo', 'suspendido');
    END IF;
END;
$$;

-- Tipo de notificación
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'tipo_notificacion') THEN
        CREATE TYPE tipo_notificacion AS ENUM ('info', 'success', 'warning', 'error', 'mensaje');
    END IF;
END;
$$;

-- Tipo de email
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'tipo_email') THEN
        CREATE TYPE tipo_email AS ENUM ('verificacion', 'recuperacion', 'notificacion', 'bienvenida', 'otro');
    END IF;
END;
$$;

-- Prioridad de email
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'prioridad_email') THEN
        CREATE TYPE prioridad_email AS ENUM ('alta', 'media', 'baja');
    END IF;
END;
$$;

-- Estado de email
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'estado_email') THEN
        CREATE TYPE estado_email AS ENUM ('pendiente', 'enviando', 'enviado', 'fallido');
    END IF;
END;
$$;

-- Tipo de categoría
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'tipo_categoria') THEN
        CREATE TYPE tipo_categoria AS ENUM ('noticia', 'servicio', 'general');
    END IF;
END;
$$;

-- Estado de banner
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'estado_banner') THEN
        CREATE TYPE estado_banner AS ENUM ('activo', 'inactivo', 'programado');
    END IF;
END;
$$;

-- Estado de noticia
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'estado_noticia') THEN
        CREATE TYPE estado_noticia AS ENUM ('borrador', 'revision', 'publicado', 'archivado');
    END IF;
END;
$$;

-- Estado de mensaje
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'estado_mensaje') THEN
        CREATE TYPE estado_mensaje AS ENUM ('nuevo', 'leido', 'respondido', 'archivado', 'spam');
    END IF;
END;
$$;

-- Prioridad de mensaje
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'prioridad_mensaje') THEN
        CREATE TYPE prioridad_mensaje AS ENUM ('baja', 'media', 'alta', 'urgente');
    END IF;
END;
$$;

-- Tipo de acción en logs
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'tipo_accion_log') THEN
        CREATE TYPE tipo_accion_log AS ENUM ('crear', 'editar', 'eliminar', 'ver', 'login', 'logout', 'otro');
    END IF;
END;
$$;

-- Tipo de configuración
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'tipo_config') THEN
        CREATE TYPE tipo_config AS ENUM ('texto', 'numero', 'boolean', 'json', 'archivo');
    END IF;
END;
$$;


-- ============================================================================
-- 1. TABLA DE ROLES
-- ============================================================================
CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    nivel INT NOT NULL,
    estado estado_general DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_roles_slug ON roles(slug);
CREATE INDEX idx_roles_nivel ON roles(nivel);

COMMENT ON COLUMN roles.nivel IS '4=Super Admin, 3=Admin, 2=Editor, 1=Visualizador, 0=Usuario público';

INSERT INTO roles (nombre, slug, descripcion, nivel) VALUES
('Super Administrador', 'super_admin', 'Control total del sistema', 4),
('Administrador', 'administrador', 'Gestiona contenido y usuarios', 3),
('Editor', 'editor', 'Crea y edita contenido', 2),
('Visualizador', 'visualizador', 'Solo lectura de reportes', 1),
('Usuario Público', 'usuario_publico', 'Usuario registrado sin acceso al admin', 0);

-- ============================================================================
-- 2. TABLA DE PERMISOS
-- ============================================================================
CREATE TABLE permisos (
    id SERIAL PRIMARY KEY,
    modulo VARCHAR(50) NOT NULL,
    accion VARCHAR(20) NOT NULL,
    descripcion VARCHAR(200),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (modulo, accion)
);

CREATE INDEX idx_permisos_modulo ON permisos(modulo);

COMMENT ON COLUMN permisos.modulo IS 'banners, noticias, servicios, usuarios, etc.';
COMMENT ON COLUMN permisos.accion IS 'crear, ver, editar, eliminar, exportar';

INSERT INTO permisos (modulo, accion, descripcion) VALUES
('dashboard', 'ver', 'Ver dashboard completo'),
('dashboard', 'estadisticas_basicas', 'Ver estadísticas básicas'),
('banners', 'crear', 'Crear nuevos banners'),
('banners', 'ver', 'Ver banners'),
('banners', 'editar', 'Editar banners'),
('banners', 'eliminar', 'Eliminar banners'),
('noticias', 'crear', 'Crear noticias'),
('noticias', 'ver', 'Ver noticias'),
('noticias', 'editar', 'Editar noticias'),
('noticias', 'eliminar', 'Eliminar noticias'),
('noticias', 'publicar', 'Publicar/despublicar noticias'),
('servicios', 'crear', 'Crear servicios'),
('servicios', 'ver', 'Ver servicios'),
('servicios', 'editar', 'Editar servicios'),
('servicios', 'eliminar', 'Eliminar servicios'),
('usuarios', 'crear', 'Crear usuarios'),
('usuarios', 'ver', 'Ver usuarios'),
('usuarios', 'editar', 'Editar usuarios'),
('usuarios', 'eliminar', 'Eliminar usuarios'),
('usuarios', 'cambiar_rol', 'Cambiar rol de usuarios'),
('mensajes', 'ver', 'Ver mensajes de contacto'),
('mensajes', 'responder', 'Responder mensajes'),
('mensajes', 'eliminar', 'Eliminar mensajes'),
('mensajes', 'exportar', 'Exportar mensajes'),
('estadisticas', 'ver', 'Ver estadísticas'),
('estadisticas', 'exportar', 'Exportar reportes'),
('logs', 'ver', 'Ver logs de actividad'),
('logs', 'exportar', 'Exportar logs'),
('configuracion', 'ver', 'Ver configuración'),
('configuracion', 'editar', 'Editar configuración'),
('configuracion', 'avanzada', 'Configuración avanzada (BD, seguridad)');

-- ============================================================================
-- 3. TABLA ROLES_PERMISOS
-- ============================================================================
CREATE TABLE roles_permisos (
    id SERIAL PRIMARY KEY,
    rol_id INT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    permiso_id INT NOT NULL REFERENCES permisos(id) ON DELETE CASCADE,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (rol_id, permiso_id)
);

CREATE INDEX idx_roles_permisos_rol ON roles_permisos(rol_id);
CREATE INDEX idx_roles_permisos_permiso ON roles_permisos(permiso_id);

INSERT INTO roles_permisos (rol_id, permiso_id)
SELECT 1, id FROM permisos;

INSERT INTO roles_permisos (rol_id, permiso_id)
SELECT 2, id FROM permisos WHERE id NOT IN (
    SELECT id FROM permisos WHERE accion IN ('cambiar_rol', 'avanzada')
);

INSERT INTO roles_permisos (rol_id, permiso_id)
SELECT 3, id FROM permisos WHERE
    modulo IN ('banners', 'noticias', 'servicios', 'mensajes')
    AND accion IN ('crear', 'ver', 'editar')
UNION
SELECT 3, id FROM permisos WHERE modulo = 'dashboard' AND accion = 'estadisticas_basicas';

INSERT INTO roles_permisos (rol_id, permiso_id)
SELECT 4, id FROM permisos WHERE accion = 'ver';

-- ============================================================================
-- 4. TABLA DE USUARIOS
-- ============================================================================
CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100),
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL DEFAULT 5 REFERENCES roles(id),
    foto VARCHAR(255) DEFAULT 'default-avatar.jpg',
    telefono VARCHAR(20),
    estado estado_usuario DEFAULT 'pendiente',
    email_verificado BOOLEAN DEFAULT FALSE,
    token_verificacion VARCHAR(100) UNIQUE,
    token_expiracion TIMESTAMP,
    token_recuperacion VARCHAR(100) UNIQUE,
    token_recuperacion_expira TIMESTAMP,
    intentos_fallidos INT DEFAULT 0,
    bloqueado_hasta TIMESTAMP NULL,
    ultimo_acceso TIMESTAMP NULL,
    ip_ultimo_acceso VARCHAR(50),
    preferencias_notificaciones JSONB,
    idioma VARCHAR(5) DEFAULT 'es',
    zona_horaria VARCHAR(50) DEFAULT 'America/Lima',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    creado_por INT REFERENCES usuarios(id) ON DELETE SET NULL,
    actualizado_por INT REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_usuarios_rol ON usuarios(rol_id);
CREATE INDEX idx_usuarios_estado ON usuarios(estado);
CREATE INDEX idx_usuarios_token_verificacion ON usuarios(token_verificacion);
CREATE INDEX idx_usuarios_token_recuperacion ON usuarios(token_recuperacion);

CREATE OR REPLACE FUNCTION actualizar_fecha_modificacion()
RETURNS TRIGGER AS $$
BEGIN
    NEW.fecha_actualizacion = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_usuarios_fecha_actualizacion
BEFORE UPDATE ON usuarios
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();

INSERT INTO usuarios (nombre, apellido, email, password, rol_id, estado, email_verificado) VALUES
('Super', 'Administrador', 'admin@calidadsoftware.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'activo', TRUE);

-- ============================================================================
-- 5. TABLA DE SESIONES ACTIVAS
-- ============================================================================
CREATE TABLE sesiones_activas (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(50) NOT NULL,
    user_agent TEXT,
    dispositivo VARCHAR(100),
    navegador VARCHAR(50),
    so VARCHAR(50),
    ubicacion VARCHAR(100),
    ultima_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activa BOOLEAN DEFAULT TRUE
);

CREATE INDEX idx_sesiones_usuario ON sesiones_activas(usuario_id);
CREATE INDEX idx_sesiones_session ON sesiones_activas(session_id);
CREATE INDEX idx_sesiones_activa ON sesiones_activas(activa);

-- ============================================================================
-- 6. TABLA DE INTENTOS DE LOGIN
-- ============================================================================
CREATE TABLE intentos_login (
    id SERIAL PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    ip_address VARCHAR(50) NOT NULL,
    user_agent TEXT,
    exitoso BOOLEAN DEFAULT FALSE,
    razon_fallo VARCHAR(100),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_intentos_email_ip ON intentos_login(email, ip_address);
CREATE INDEX idx_intentos_fecha ON intentos_login(fecha);
CREATE INDEX idx_intentos_exitoso ON intentos_login(exitoso);

-- ============================================================================
-- 7. TABLA DE LOGS DE ACTIVIDAD
-- ============================================================================
CREATE TABLE logs_actividad (
    id BIGSERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    accion VARCHAR(200) NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    tipo_accion tipo_accion_log NOT NULL,
    descripcion TEXT,
    datos_anteriores JSONB,
    datos_nuevos JSONB,
    ip_address VARCHAR(50) NOT NULL,
    user_agent TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_logs_usuario ON logs_actividad(usuario_id);
CREATE INDEX idx_logs_modulo ON logs_actividad(modulo);
CREATE INDEX idx_logs_fecha ON logs_actividad(fecha);
CREATE INDEX idx_logs_tipo_accion ON logs_actividad(tipo_accion);

-- ============================================================================
-- 8. TABLA DE NOTIFICACIONES
-- ============================================================================
CREATE TABLE notificaciones (
    id BIGSERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    tipo tipo_notificacion NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    icono VARCHAR(50),
    url VARCHAR(255),
    leida BOOLEAN DEFAULT FALSE,
    fecha_leida TIMESTAMP NULL,
    archivada BOOLEAN DEFAULT FALSE,
    enviada_email BOOLEAN DEFAULT FALSE,
    fecha_envio_email TIMESTAMP NULL,
    modulo_origen VARCHAR(50),
    registro_id INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_notificaciones_usuario ON notificaciones(usuario_id);
CREATE INDEX idx_notificaciones_leida ON notificaciones(leida);
CREATE INDEX idx_notificaciones_tipo ON notificaciones(tipo);
CREATE INDEX idx_notificaciones_fecha ON notificaciones(fecha_creacion);

-- ============================================================================
-- 9. TABLA DE EMAILS PENDIENTES
-- ============================================================================
CREATE TABLE emails_pendientes (
    id BIGSERIAL PRIMARY KEY,
    destinatario_email VARCHAR(100) NOT NULL,
    destinatario_nombre VARCHAR(100),
    asunto VARCHAR(200) NOT NULL,
    cuerpo_html TEXT NOT NULL,
    cuerpo_texto TEXT,
    tipo tipo_email NOT NULL,
    prioridad prioridad_email DEFAULT 'media',
    estado estado_email DEFAULT 'pendiente',
    intentos_envio INT DEFAULT 0,
    max_intentos INT DEFAULT 3,
    fecha_envio TIMESTAMP NULL,
    error_mensaje TEXT,
    token_apertura VARCHAR(100) UNIQUE,
    abierto BOOLEAN DEFAULT FALSE,
    fecha_apertura TIMESTAMP NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_procesado TIMESTAMP NULL
);

CREATE INDEX idx_emails_estado ON emails_pendientes(estado);
CREATE INDEX idx_emails_tipo ON emails_pendientes(tipo);
CREATE INDEX idx_emails_prioridad ON emails_pendientes(prioridad);
CREATE INDEX idx_emails_fecha_creacion ON emails_pendientes(fecha_creacion);

-- ============================================================================
-- 10. TABLA DE CATEGORÍAS
-- ============================================================================
CREATE TABLE categorias (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    descripcion TEXT,
    tipo tipo_categoria NOT NULL,
    icono VARCHAR(50),
    color VARCHAR(7) DEFAULT '#7B2CBF',
    orden INT DEFAULT 0,
    estado estado_general DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_categorias_slug ON categorias(slug);
CREATE INDEX idx_categorias_tipo ON categorias(tipo);
CREATE INDEX idx_categorias_estado ON categorias(estado);

INSERT INTO categorias (nombre, slug, tipo, icono, color) VALUES
('Testing Automatizado', 'testing-automatizado', 'noticia', 'fa-robot', '#5A189A'),
('Seguridad', 'seguridad', 'noticia', 'fa-shield-alt', '#ef4444'),
('Metodologías Ágiles', 'metodologias-agiles', 'noticia', 'fa-project-diagram', '#10b981'),
('Performance', 'performance', 'noticia', 'fa-tachometer-alt', '#f59e0b'),
('Consultoría QA', 'consultoria-qa', 'servicio', 'fa-users-cog', '#7B2CBF'),
('Testing Manual', 'testing-manual', 'servicio', 'fa-hand-pointer', '#9D4EDD'),
('Auditoría de Código', 'auditoria-codigo', 'servicio', 'fa-code', '#C77DFF');

-- ============================================================================
-- 11. TABLA DE BANNERS
-- ============================================================================
CREATE TABLE banners (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    enlace VARCHAR(255),
    imagen VARCHAR(255) NOT NULL,
    estado estado_banner DEFAULT 'activo',
    orden INT DEFAULT 0,
    fecha_inicio DATE NULL,
    fecha_fin DATE NULL,
    vistas INT DEFAULT 0,
    clics INT DEFAULT 0,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE RESTRICT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_banners_estado ON banners(estado);
CREATE INDEX idx_banners_orden ON banners(orden);
CREATE INDEX idx_banners_usuario ON banners(usuario_id);

CREATE TRIGGER trigger_banners_fecha_actualizacion
BEFORE UPDATE ON banners
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();

-- ============================================================================
-- 12. TABLA DE NOTICIAS
-- ============================================================================
CREATE TABLE noticias (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(250) NOT NULL,
    slug VARCHAR(250) UNIQUE NOT NULL,
    extracto TEXT,
    contenido TEXT NOT NULL,
    imagen_destacada VARCHAR(255),
    categoria_id INT REFERENCES categorias(id) ON DELETE SET NULL,
    tags VARCHAR(255),
    meta_titulo VARCHAR(70),
    meta_descripcion VARCHAR(160),
    meta_keywords VARCHAR(255),
    estado estado_noticia DEFAULT 'borrador',
    fecha_publicacion TIMESTAMP NULL,
    destacada BOOLEAN DEFAULT FALSE,
    vistas INT DEFAULT 0,
    tiempo_lectura INT,
    autor_id INT NOT NULL REFERENCES usuarios(id) ON DELETE RESTRICT,
    editor_id INT REFERENCES usuarios(id) ON DELETE SET NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_noticias_slug ON noticias(slug);
CREATE INDEX idx_noticias_estado ON noticias(estado);
CREATE INDEX idx_noticias_categoria ON noticias(categoria_id);
CREATE INDEX idx_noticias_autor ON noticias(autor_id);
CREATE INDEX idx_noticias_fecha_publicacion ON noticias(fecha_publicacion);
CREATE INDEX idx_noticias_destacada ON noticias(destacada);
CREATE INDEX idx_noticias_busqueda ON noticias USING gin(to_tsvector('spanish', titulo || ' ' || COALESCE(extracto, '') || ' ' || contenido));

CREATE TRIGGER trigger_noticias_fecha_actualizacion
BEFORE UPDATE ON noticias
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();

-- ============================================================================
-- 13. TABLA DE SERVICIOS
-- ============================================================================
CREATE TABLE servicios (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    descripcion_corta TEXT,
    descripcion_completa TEXT,
    icono VARCHAR(50),
    imagen VARCHAR(255),
    categoria_id INT REFERENCES categorias(id) ON DELETE SET NULL,
    precio_basico DECIMAL(10,2) DEFAULT 0.00,
    precio_profesional DECIMAL(10,2) DEFAULT 0.00,
    precio_enterprise DECIMAL(10,2) DEFAULT 0.00,
    moneda VARCHAR(3) DEFAULT 'USD',
    caracteristicas_basico JSONB,
    caracteristicas_profesional JSONB,
    caracteristicas_enterprise JSONB,
    orden INT DEFAULT 0,
    estado estado_general DEFAULT 'activo',
    destacado BOOLEAN DEFAULT FALSE,
    solicitudes INT DEFAULT 0,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE RESTRICT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_servicios_slug ON servicios(slug);
CREATE INDEX idx_servicios_estado ON servicios(estado);
CREATE INDEX idx_servicios_orden ON servicios(orden);
CREATE INDEX idx_servicios_categoria ON servicios(categoria_id);

CREATE TRIGGER trigger_servicios_fecha_actualizacion
BEFORE UPDATE ON servicios
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();

-- ============================================================================
-- 14. TABLA DE MENSAJES DE CONTACTO
-- ============================================================================
CREATE TABLE mensajes_contacto (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    empresa VARCHAR(100),
    servicio_id INT REFERENCES servicios(id) ON DELETE SET NULL,
    asunto VARCHAR(200),
    mensaje TEXT NOT NULL,
    estado estado_mensaje DEFAULT 'nuevo',
    prioridad prioridad_mensaje DEFAULT 'media',
    respuesta TEXT,
    respondido_por INT REFERENCES usuarios(id) ON DELETE SET NULL,
    fecha_respuesta TIMESTAMP NULL,
    ip_address VARCHAR(50),
    user_agent TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_mensajes_estado ON mensajes_contacto(estado);
CREATE INDEX idx_mensajes_email ON mensajes_contacto(email);
CREATE INDEX idx_mensajes_fecha ON mensajes_contacto(fecha_creacion);

-- ============================================================================
-- 15. TABLA DE VISITAS
-- ============================================================================
CREATE TABLE visitas (
    id BIGSERIAL PRIMARY KEY,
    ip VARCHAR(50) NOT NULL,
    so VARCHAR(50),
    navegador VARCHAR(100),
    dispositivo VARCHAR(50),
    url_visitada VARCHAR(255),
    url_referencia VARCHAR(255),
    tiempo_permanencia INT,
    pais VARCHAR(2),
    ciudad VARCHAR(100),
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    timestamp_visita TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_visitas_fecha ON visitas(fecha);
CREATE INDEX idx_visitas_ip ON visitas(ip);
CREATE INDEX idx_visitas_so ON visitas(so);
CREATE INDEX idx_visitas_navegador ON visitas(navegador);

-- ============================================================================
-- 16. TABLA DE CONFIGURACIÓN DEL SISTEMA
-- ============================================================================
CREATE TABLE configuracion (
    id SERIAL PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    tipo tipo_config DEFAULT 'texto',
    grupo VARCHAR(50),
    descripcion TEXT,
    editable BOOLEAN DEFAULT TRUE,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_configuracion_clave ON configuracion(clave);
CREATE INDEX idx_configuracion_grupo ON configuracion(grupo);

CREATE TRIGGER trigger_configuracion_fecha_actualizacion
BEFORE UPDATE ON configuracion
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();

INSERT INTO configuracion (clave, valor, tipo, grupo, descripcion) VALUES
('site_name', 'Calidad de Software', 'texto', 'general', 'Nombre del sitio web'),
('site_email', 'info@calidadsoftware.com', 'texto', 'general', 'Email principal del sitio'),
('site_telefono', '+51 999 888 777', 'texto', 'general', 'Teléfono de contacto'),
('site_direccion', 'Juliaca, Puno, Perú', 'texto', 'general', 'Dirección física'),
('smtp_host', 'smtp.gmail.com', 'texto', 'email', 'Servidor SMTP'),
('smtp_port', '587', 'numero', 'email', 'Puerto SMTP'),
('smtp_usuario', '', 'texto', 'email', 'Usuario SMTP'),
('smtp_password', '', 'texto', 'email', 'Contraseña SMTP'),
('smtp_encryption', 'tls', 'texto', 'email', 'Tipo de cifrado (tls/ssl)'),
('email_verificacion_requerida', '1', 'boolean', 'seguridad', 'Requiere verificación de email al registrarse'),
('max_intentos_login', '5', 'numero', 'seguridad', 'Máximo de intentos de login'),
('tiempo_bloqueo_minutos', '15', 'numero', 'seguridad', 'Minutos de bloqueo tras exceder intentos'),
('session_timeout_minutos', '30', 'numero', 'seguridad', 'Tiempo de inactividad para cerrar sesión'),
('meta_descripcion', 'Servicios de calidad de software y testing', 'texto', 'seo', 'Meta descripción del sitio'),
('google_analytics_id', '', 'texto', 'seo', 'ID de Google Analytics'),
('facebook_url', '', 'texto', 'redes_sociales', 'URL de Facebook'),
('twitter_url', '', 'texto', 'redes_sociales', 'URL de Twitter'),
('linkedin_url', '', 'texto', 'redes_sociales', 'URL de LinkedIn'),
('instagram_url', '', 'texto', 'redes_sociales', 'URL de Instagram');

-- ============================================================================
-- TRIGGERS
-- ============================================================================

CREATE OR REPLACE FUNCTION trigger_after_usuario_insert()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, icono, modulo_origen, registro_id)
    SELECT
        u.id,
        'info',
        'Nuevo usuario registrado',
        'El usuario ' || NEW.nombre || ' ' || COALESCE(NEW.apellido, '') || ' se ha registrado en el sistema.',
        'fa-user-plus',
        'usuarios',
        NEW.id
    FROM usuarios u
    INNER JOIN roles r ON u.rol_id = r.id
    WHERE r.nivel >= 3 AND u.id != NEW.id;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER after_usuario_insert
AFTER INSERT ON usuarios
FOR EACH ROW
EXECUTE FUNCTION trigger_after_usuario_insert();

CREATE OR REPLACE FUNCTION trigger_after_usuario_update()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO logs_actividad (usuario_id, accion, modulo, tipo_accion, descripcion, ip_address)
    VALUES (
        COALESCE(NEW.actualizado_por, NEW.id),
        'Actualizó usuario: ' || NEW.nombre,
        'usuarios',
        'editar',
        'Usuario ID: ' || NEW.id,
        COALESCE(current_setting('app.user_ip', TRUE), '0.0.0.0')
    );

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER after_usuario_update
AFTER UPDATE ON usuarios
FOR EACH ROW
EXECUTE FUNCTION trigger_after_usuario_update();

CREATE OR REPLACE FUNCTION trigger_after_mensaje_contacto_insert()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, icono, url, modulo_origen, registro_id)
    SELECT
        u.id,
        'warning',
        'Nuevo mensaje de contacto',
        'Has recibido un mensaje de ' || NEW.nombre || ' (' || NEW.email || ')',
        'fa-envelope',
        'mensajes_admin.php?id=' || NEW.id::TEXT,
        'mensajes',
        NEW.id
    FROM usuarios u
    INNER JOIN roles r ON u.rol_id = r.id
    WHERE r.nivel >= 2;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER after_mensaje_contacto_insert
AFTER INSERT ON mensajes_contacto
FOR EACH ROW
EXECUTE FUNCTION trigger_after_mensaje_contacto_insert();

-- ============================================================================
-- PROCEDIMIENTOS ALMACENADOS (Continuación en siguiente bloque)
-- ============================================================================

-- El resto de procedimientos y vistas van en el siguiente bloque...
-- ============================================================================
-- SCRIPT DE PRUEBA Y VERIFICACIÓN - SISTEMA DE CALIDAD DE SOFTWARE
-- Ejecutar DESPUÉS de la instalación completa
-- ============================================================================

-- ============================================================================
-- 1. VERIFICAR ESTRUCTURA DE BASE DE DATOS
-- ============================================================================

-- Listar todas las tablas creadas
SELECT
    table_name,
    (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = t.table_name) AS columnas
FROM information_schema.tables t
WHERE table_schema = 'public'
AND table_type = 'BASE TABLE'
ORDER BY table_name;

-- Verificar tipos ENUM creados
SELECT
    t.typname AS tipo_enum,
    string_agg(e.enumlabel, ', ' ORDER BY e.enumsortorder) AS valores
FROM pg_type t
JOIN pg_enum e ON t.oid = e.enumtypid
WHERE t.typtype = 'e'
GROUP BY t.typname
ORDER BY t.typname;

-- Verificar funciones creadas
SELECT
    routine_name,
    routine_type,
    data_type
FROM information_schema.routines
WHERE routine_schema = 'public'
AND routine_type = 'FUNCTION'
ORDER BY routine_name;

-- Verificar vistas creadas
SELECT
    table_name AS vista_nombre
FROM information_schema.views
WHERE table_schema = 'public'
ORDER BY table_name;

-- Verificar índices creados
SELECT
    tablename,
    indexname,
    indexdef
FROM pg_indexes
WHERE schemaname = 'public'
ORDER BY tablename, indexname;

-- ============================================================================
-- 2. VERIFICAR DATOS INICIALES
-- ============================================================================

-- Verificar roles
SELECT id, nombre, slug, nivel, estado FROM roles ORDER BY nivel DESC;

-- Verificar usuario admin
SELECT id, nombre, email, rol_id, estado, email_verificado FROM usuarios;

-- Verificar categorías
SELECT id, nombre, tipo, estado FROM categorias ORDER BY tipo, nombre;

-- Verificar configuración
SELECT clave, valor, tipo, grupo FROM configuracion ORDER BY grupo, clave;

-- Verificar permisos
SELECT COUNT(*) AS total_permisos FROM permisos;

-- Verificar asignación de permisos a roles
SELECT
    r.nombre AS rol,
    COUNT(rp.permiso_id) AS cantidad_permisos
FROM roles r
LEFT JOIN roles_permisos rp ON r.id = rp.rol_id
GROUP BY r.id, r.nombre
ORDER BY r.nivel DESC;

-- ============================================================================
-- 3. PROBAR FUNCIONES DE ESTADÍSTICAS
-- ============================================================================

-- ===========================================
-- FUNCIÓN: obtener_estadisticas_dashboard()
-- ===========================================
CREATE OR REPLACE FUNCTION obtener_estadisticas_dashboard()
RETURNS TABLE (
    total_usuarios INT,
    total_roles INT,
    total_mensajes INT,
    total_servicios INT,
    total_notificaciones INT,
    total_sesiones_activas INT
)
LANGUAGE plpgsql
AS $$
BEGIN
    RETURN QUERY
    SELECT
        (SELECT COUNT(*) FROM usuarios) AS total_usuarios,
        (SELECT COUNT(*) FROM roles) AS total_roles,
        (SELECT COUNT(*) FROM mensajes_contacto) AS total_mensajes,
        (SELECT COUNT(*) FROM servicios) AS total_servicios,
        (SELECT COUNT(*) FROM notificaciones) AS total_notificaciones,
        (SELECT COUNT(*) FROM sesiones_activas WHERE activa = TRUE) AS total_sesiones_activas;
END;
$$;

-- ===========================================
-- FUNCIÓN: obtener_visitas_ultimos_dias(dias INT)
-- ===========================================
CREATE OR REPLACE FUNCTION obtener_visitas_ultimos_dias(dias INT)
RETURNS TABLE (
    fecha DATE,
    total_visitas INT
)
LANGUAGE plpgsql
AS $$
BEGIN
    RETURN QUERY
    SELECT
        gs::date AS fecha,
        COALESCE(COUNT(v.id), 0) AS total_visitas
    FROM generate_series(
        CURRENT_DATE - (dias - 1),
        CURRENT_DATE,
        interval '1 day'
    ) AS gs
    LEFT JOIN visitas v ON DATE(v.fecha) = gs::date
    GROUP BY gs
    ORDER BY gs;
END;
$$;


-- ============================================================================
-- 4. PROBAR FUNCIONES DE PERMISOS
-- ============================================================================

-- ===========================================
-- FUNCIÓN: verificar_permiso(usuario_id, modulo, accion)
-- ===========================================
CREATE OR REPLACE FUNCTION verificar_permiso(
    p_usuario_id INT,
    p_modulo TEXT,
    p_accion TEXT
)
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $$
DECLARE
    v_tiene_permiso BOOLEAN := FALSE;
BEGIN
    -- Si el usuario tiene rol de 'admin' o 'superadmin', siempre tiene acceso total
    IF EXISTS (
        SELECT 1
        FROM usuarios u
        JOIN roles r ON u.rol_id = r.id
        WHERE u.id = p_usuario_id
          AND (LOWER(r.slug) IN ('admin', 'superadmin'))
    ) THEN
        RETURN TRUE;
    END IF;

    -- Caso general: verificar permiso en la tabla permisos o permisos_roles
    SELECT TRUE
    INTO v_tiene_permiso
    FROM permisos p
    JOIN roles_permisos rp ON rp.permiso_id = p.id
    JOIN usuarios u ON u.rol_id = rp.rol_id
    WHERE u.id = p_usuario_id
      AND LOWER(p.modulo) = LOWER(p_modulo)
      AND LOWER(p.accion) = LOWER(p_accion)
    LIMIT 1;

    RETURN COALESCE(v_tiene_permiso, FALSE);
END;
$$;

-- Listar todos los permisos del Super Admin
SELECT
    p.modulo,
    p.accion,
    p.descripcion
FROM usuarios u
INNER JOIN roles r ON u.rol_id = r.id
INNER JOIN roles_permisos rp ON r.id = rp.rol_id
INNER JOIN permisos p ON rp.permiso_id = p.id
WHERE u.id = 1
ORDER BY p.modulo, p.accion;

-- ============================================================================
-- 5. INSERTAR DATOS DE PRUEBA
-- ============================================================================

-- Insertar una noticia de prueba
INSERT INTO noticias (
    titulo,
    slug,
    extracto,
    contenido,
    categoria_id,
    estado,
    fecha_publicacion,
    destacada,
    autor_id
) VALUES (
    'Introducción al Testing Automatizado',
    'introduccion-testing-automatizado',
    'El testing automatizado revoluciona la forma en que desarrollamos software de calidad.',
    '<p>El testing automatizado es una práctica fundamental en el desarrollo moderno de software. Permite ejecutar pruebas de manera repetitiva y confiable, detectando errores antes de que lleguen a producción.</p><p>En este artículo exploraremos las mejores prácticas y herramientas disponibles.</p>',
    1, -- Testing Automatizado
    'publicado',
    NOW(),
    TRUE,
    1
);

-- Insertar un servicio de prueba
INSERT INTO servicios (
    titulo,
    slug,
    descripcion_corta,
    descripcion_completa,
    categoria_id,
    icono,
    precio_basico,
    precio_profesional,
    precio_enterprise,
    estado,
    destacado,
    usuario_id
) VALUES (
    'Testing de Aplicaciones Web',
    'testing-aplicaciones-web',
    'Servicios completos de testing para aplicaciones web modernas',
    '<p>Ofrecemos servicios profesionales de testing para garantizar la calidad de tu aplicación web.</p>',
    5, -- Consultoría QA
    'fa-globe',
    299.00,
    599.00,
    1299.00,
    'activo',
    TRUE,
    1
);

INSERT INTO banners (
    titulo,
    descripcion,
    enlace,
    imagen,
    estado,
    orden,
    usuario_id
) VALUES (
    '¡Bienvenido a Calidad de Software!',
    'Expertos en testing y aseguramiento de calidad',
    '/servicios',
    'banner-principal.jpg',
    'activo',
    1,
    1
);
