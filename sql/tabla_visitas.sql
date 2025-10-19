-- ============================================================================
-- TABLA DE VISITAS - SISTEMA DE REGISTRO DE TRÁFICO
-- ============================================================================

-- Crear tabla visitas si no existe
CREATE TABLE IF NOT EXISTS visitas (
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

-- Índices para mejorar rendimiento en consultas
CREATE INDEX IF NOT EXISTS idx_visitas_fecha ON visitas(fecha);
CREATE INDEX IF NOT EXISTS idx_visitas_ip ON visitas(ip);
CREATE INDEX IF NOT EXISTS idx_visitas_so ON visitas(so);
CREATE INDEX IF NOT EXISTS idx_visitas_navegador ON visitas(navegador);
CREATE INDEX IF NOT EXISTS idx_visitas_timestamp ON visitas(timestamp_visita);

-- Comentarios descriptivos
COMMENT ON TABLE visitas IS 'Registro de visitas al sitio web para análisis de tráfico';
COMMENT ON COLUMN visitas.ip IS 'Dirección IP del visitante';
COMMENT ON COLUMN visitas.so IS 'Sistema operativo detectado';
COMMENT ON COLUMN visitas.navegador IS 'Navegador web utilizado';
COMMENT ON COLUMN visitas.dispositivo IS 'Tipo de dispositivo (desktop, mobile, tablet)';
COMMENT ON COLUMN visitas.url_visitada IS 'URL de la página visitada';
COMMENT ON COLUMN visitas.url_referencia IS 'URL de referencia (de dónde viene)';
COMMENT ON COLUMN visitas.fecha IS 'Fecha de la visita (para agregaciones)';
COMMENT ON COLUMN visitas.hora IS 'Hora de la visita';

-- Policies RLS para permitir registro y lectura de visitas
-- IMPORTANTE: Permitir INSERT para registrar visitas desde la web pública
CREATE POLICY IF NOT EXISTS anon_insert_visitas ON public.visitas 
  FOR INSERT TO anon 
  WITH CHECK (true);

-- Permitir SELECT para estadísticas en dashboard
CREATE POLICY IF NOT EXISTS anon_select_visitas ON public.visitas 
  FOR SELECT TO anon 
  USING (true);

-- Función para limpiar visitas antiguas (opcional, ejecutar manualmente o por cron)
CREATE OR REPLACE FUNCTION limpiar_visitas_antiguas(dias_antiguedad INT DEFAULT 365)
RETURNS INT
LANGUAGE plpgsql
AS $$
DECLARE
    registros_eliminados INT;
BEGIN
    DELETE FROM visitas 
    WHERE fecha < CURRENT_DATE - (dias_antiguedad || ' days')::INTERVAL;
    
    GET DIAGNOSTICS registros_eliminados = ROW_COUNT;
    
    RETURN registros_eliminados;
END;
$$;

COMMENT ON FUNCTION limpiar_visitas_antiguas IS 'Elimina visitas anteriores a X días (por defecto 365). Uso: SELECT limpiar_visitas_antiguas(180);';

-- Insertar algunas visitas de prueba para verificar
INSERT INTO visitas (ip, so, navegador, dispositivo, url_visitada, fecha, hora) VALUES
('192.168.1.100', 'Windows 10', 'Chrome 120', 'desktop', '/index.php', CURRENT_DATE, CURRENT_TIME),
('192.168.1.101', 'Android 14', 'Chrome Mobile 120', 'mobile', '/index.php', CURRENT_DATE, CURRENT_TIME),
('192.168.1.102', 'macOS 14', 'Safari 17', 'desktop', '/index.php', CURRENT_DATE - 1, '14:30:00'),
('192.168.1.103', 'iOS 17', 'Safari Mobile', 'mobile', '/index.php', CURRENT_DATE - 1, '10:15:00'),
('192.168.1.104', 'Windows 11', 'Firefox 121', 'desktop', '/index.php', CURRENT_DATE - 2, '09:45:00'),
('192.168.1.105', 'Linux', 'Chrome 120', 'desktop', '/servicios', CURRENT_DATE - 2, '16:20:00'),
('192.168.1.106', 'Windows 10', 'Edge 120', 'desktop', '/noticias', CURRENT_DATE - 3, '11:00:00');

-- Verificar que se creó correctamente
SELECT 
    COUNT(*) as total_visitas,
    COUNT(DISTINCT fecha) as dias_con_visitas,
    MIN(fecha) as primera_visita,
    MAX(fecha) as ultima_visita
FROM visitas;
