-- ============================================================================
-- DATOS DE PRUEBA PARA VISITAS
-- Genera visitas de los últimos 7 días para testing
-- ============================================================================

-- Limpiar visitas de prueba anteriores (opcional)
-- DELETE FROM visitas WHERE ip LIKE '192.168.%';

-- Insertar visitas de prueba de los últimos 7 días
INSERT INTO visitas (ip, so, navegador, dispositivo, url_visitada, url_referencia, tiempo_permanencia, fecha, hora) VALUES
-- Día 0 (HOY)
('192.168.1.100', 'Windows 10', 'Chrome 120', 'desktop', '/index.php', NULL, 120, CURRENT_DATE, '09:15:00'),
('192.168.1.101', 'Android 14', 'Chrome Mobile 120', 'mobile', '/index.php', 'https://google.com', 45, CURRENT_DATE, '10:30:00'),
('192.168.1.102', 'iOS 17', 'Safari Mobile', 'mobile', '/servicios', '/index.php', 90, CURRENT_DATE, '11:45:00'),
('192.168.1.103', 'Windows 11', 'Edge 120', 'desktop', '/noticias', '/index.php', 180, CURRENT_DATE, '13:20:00'),
('192.168.1.104', 'macOS 14', 'Safari 17', 'desktop', '/contacto', '/servicios', 60, CURRENT_DATE, '14:05:00'),
('192.168.1.105', 'Linux', 'Firefox 121', 'desktop', '/index.php', NULL, 150, CURRENT_DATE, '15:30:00'),
('192.168.1.106', 'Android 13', 'Chrome Mobile 119', 'mobile', '/index.php', 'https://facebook.com', 30, CURRENT_DATE, '16:45:00'),
('192.168.1.107', 'Windows 10', 'Chrome 120', 'desktop', '/servicios', 'https://google.com', 200, CURRENT_DATE, '18:00:00'),

-- Día -1 (AYER)
('192.168.1.110', 'Windows 10', 'Chrome 120', 'desktop', '/index.php', NULL, 95, CURRENT_DATE - 1, '08:00:00'),
('192.168.1.111', 'Android 14', 'Chrome Mobile 120', 'mobile', '/index.php', 'https://google.com', 50, CURRENT_DATE - 1, '09:30:00'),
('192.168.1.112', 'macOS 14', 'Safari 17', 'desktop', '/noticias', '/index.php', 130, CURRENT_DATE - 1, '11:15:00'),
('192.168.1.113', 'iOS 17', 'Safari Mobile', 'mobile', '/servicios', 'https://google.com', 70, CURRENT_DATE - 1, '13:00:00'),
('192.168.1.114', 'Windows 11', 'Edge 120', 'desktop', '/index.php', NULL, 110, CURRENT_DATE - 1, '14:30:00'),
('192.168.1.115', 'iPad OS', 'Safari', 'tablet', '/contacto', '/index.php', 85, CURRENT_DATE - 1, '16:00:00'),

-- Día -2
('192.168.1.120', 'Windows 10', 'Chrome 120', 'desktop', '/index.php', 'https://linkedin.com', 140, CURRENT_DATE - 2, '10:00:00'),
('192.168.1.121', 'Android 13', 'Chrome Mobile', 'mobile', '/index.php', NULL, 40, CURRENT_DATE - 2, '11:30:00'),
('192.168.1.122', 'Windows 11', 'Firefox 121', 'desktop', '/servicios', '/index.php', 160, CURRENT_DATE - 2, '13:00:00'),
('192.168.1.123', 'macOS 14', 'Safari 17', 'desktop', '/noticias', 'https://google.com', 120, CURRENT_DATE - 2, '15:30:00'),
('192.168.1.124', 'iOS 16', 'Safari Mobile', 'mobile', '/index.php', NULL, 55, CURRENT_DATE - 2, '17:00:00'),

-- Día -3
('192.168.1.130', 'Windows 10', 'Chrome 120', 'desktop', '/index.php', NULL, 100, CURRENT_DATE - 3, '09:00:00'),
('192.168.1.131', 'Android 14', 'Chrome Mobile', 'mobile', '/contacto', '/index.php', 75, CURRENT_DATE - 3, '10:30:00'),
('192.168.1.132', 'Windows 11', 'Edge 120', 'desktop', '/servicios', 'https://google.com', 145, CURRENT_DATE - 3, '12:00:00'),
('192.168.1.133', 'macOS 13', 'Safari 16', 'desktop', '/index.php', NULL, 90, CURRENT_DATE - 3, '14:30:00'),
('192.168.1.134', 'Linux', 'Firefox 120', 'desktop', '/noticias', '/index.php', 180, CURRENT_DATE - 3, '16:00:00'),
('192.168.1.135', 'iOS 17', 'Safari Mobile', 'mobile', '/index.php', 'https://twitter.com', 45, CURRENT_DATE - 3, '18:00:00'),

-- Día -4
('192.168.1.140', 'Windows 10', 'Chrome 119', 'desktop', '/index.php', NULL, 125, CURRENT_DATE - 4, '08:30:00'),
('192.168.1.141', 'Android 13', 'Chrome Mobile', 'mobile', '/index.php', 'https://google.com', 60, CURRENT_DATE - 4, '10:00:00'),
('192.168.1.142', 'macOS 14', 'Safari 17', 'desktop', '/servicios', '/index.php', 150, CURRENT_DATE - 4, '12:30:00'),
('192.168.1.143', 'Windows 11', 'Edge 120', 'desktop', '/noticias', 'https://google.com', 95, CURRENT_DATE - 4, '14:00:00'),

-- Día -5
('192.168.1.150', 'Windows 10', 'Chrome 120', 'desktop', '/index.php', NULL, 110, CURRENT_DATE - 5, '09:00:00'),
('192.168.1.151', 'Android 14', 'Chrome Mobile', 'mobile', '/index.php', 'https://google.com', 50, CURRENT_DATE - 5, '11:00:00'),
('192.168.1.152', 'iOS 17', 'Safari Mobile', 'mobile', '/contacto', '/index.php', 80, CURRENT_DATE - 5, '13:00:00'),
('192.168.1.153', 'macOS 14', 'Safari 17', 'desktop', '/servicios', 'https://google.com', 170, CURRENT_DATE - 5, '15:00:00'),
('192.168.1.154', 'Windows 11', 'Firefox 121', 'desktop', '/index.php', NULL, 100, CURRENT_DATE - 5, '17:00:00'),

-- Día -6
('192.168.1.160', 'Windows 10', 'Chrome 120', 'desktop', '/index.php', 'https://google.com', 135, CURRENT_DATE - 6, '10:00:00'),
('192.168.1.161', 'Android 14', 'Chrome Mobile', 'mobile', '/index.php', NULL, 45, CURRENT_DATE - 6, '12:00:00'),
('192.168.1.162', 'macOS 13', 'Safari 16', 'desktop', '/servicios', '/index.php', 160, CURRENT_DATE - 6, '14:00:00'),
('192.168.1.163', 'iPad OS', 'Safari', 'tablet', '/noticias', '/index.php', 90, CURRENT_DATE - 6, '16:00:00');

-- Verificar que se insertaron correctamente
SELECT 
    COUNT(*) as total_visitas,
    COUNT(DISTINCT DATE(fecha)) as dias_con_visitas,
    MIN(fecha) as primera_visita,
    MAX(fecha) as ultima_visita,
    ROUND(AVG(tiempo_permanencia)) as tiempo_promedio_segundos
FROM visitas
WHERE ip LIKE '192.168.%';

-- Ver distribución por dispositivo
SELECT 
    dispositivo,
    COUNT(*) as cantidad,
    ROUND(AVG(tiempo_permanencia)) as tiempo_promedio
FROM visitas
WHERE ip LIKE '192.168.%'
GROUP BY dispositivo
ORDER BY cantidad DESC;

-- Ver distribución por navegador
SELECT 
    navegador,
    COUNT(*) as cantidad
FROM visitas
WHERE ip LIKE '192.168.%'
GROUP BY navegador
ORDER BY cantidad DESC
LIMIT 5;
