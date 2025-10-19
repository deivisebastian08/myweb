<?php
/**
 * Sistema de Registro de Visitas
 * Registra cada visita al sitio web en Supabase
 */

// Solo ejecutar si no se ha registrado esta visita en esta sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Evitar registrar la misma visita múltiples veces en una sesión
if (isset($_SESSION['visita_registrada'])) {
    return; // Ya registrada en esta sesión
}

require_once __DIR__ . '/adm/script/Supabase.php';

// Detectar información del visitante
function detectar_info_visitante() {
    $info = [
        'ip' => '',
        'so' => '',
        'navegador' => '',
        'dispositivo' => '',
        'url_visitada' => '',
        'url_referencia' => ''
    ];
    
    // IP del visitante
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $info['ip'] = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $info['ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $info['ip'] = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    // User Agent
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Detectar SO
    if (preg_match('/windows/i', $userAgent)) {
        if (preg_match('/windows nt 10/i', $userAgent)) $info['so'] = 'Windows 10';
        elseif (preg_match('/windows nt 11/i', $userAgent)) $info['so'] = 'Windows 11';
        else $info['so'] = 'Windows';
    } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
        $info['so'] = 'macOS';
    } elseif (preg_match('/linux/i', $userAgent)) {
        $info['so'] = 'Linux';
    } elseif (preg_match('/android/i', $userAgent)) {
        $info['so'] = 'Android';
    } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
        $info['so'] = 'iOS';
    } else {
        $info['so'] = 'Desconocido';
    }
    
    // Detectar Navegador
    if (preg_match('/edg/i', $userAgent)) {
        $info['navegador'] = 'Edge';
    } elseif (preg_match('/chrome/i', $userAgent) && !preg_match('/edg/i', $userAgent)) {
        $info['navegador'] = 'Chrome';
    } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) {
        $info['navegador'] = 'Safari';
    } elseif (preg_match('/firefox/i', $userAgent)) {
        $info['navegador'] = 'Firefox';
    } elseif (preg_match('/opera|opr/i', $userAgent)) {
        $info['navegador'] = 'Opera';
    } else {
        $info['navegador'] = 'Otro';
    }
    
    // Detectar tipo de dispositivo
    if (preg_match('/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/i', $userAgent)) {
        $info['dispositivo'] = 'mobile';
    } elseif (preg_match('/tablet|ipad/i', $userAgent)) {
        $info['dispositivo'] = 'tablet';
    } else {
        $info['dispositivo'] = 'desktop';
    }
    
    // URL visitada
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $info['url_visitada'] = $protocol . '://' . $host . $uri;
    
    // URL de referencia
    $info['url_referencia'] = $_SERVER['HTTP_REFERER'] ?? null;
    
    return $info;
}

try {
    $sb = new Supabase();
    $info = detectar_info_visitante();
    
    // Preparar datos para insertar
    $visitaData = [
        'ip' => substr($info['ip'], 0, 50),
        'so' => substr($info['so'], 0, 50),
        'navegador' => substr($info['navegador'], 0, 100),
        'dispositivo' => $info['dispositivo'],
        'url_visitada' => substr($info['url_visitada'], 0, 255),
        'url_referencia' => $info['url_referencia'] ? substr($info['url_referencia'], 0, 255) : null,
        'fecha' => date('Y-m-d'),
        'hora' => date('H:i:s')
    ];
    
    // Insertar en Supabase
    $result = $sb->insert('visitas', $visitaData);
    
    // Marcar como registrada en esta sesión
    if ($result) {
        $_SESSION['visita_registrada'] = true;
        $_SESSION['visita_id'] = $result[0]['id'] ?? null;
    }
    
} catch (Exception $e) {
    // Silenciar errores para no afectar la experiencia del usuario
    error_log("Error registrando visita: " . $e->getMessage());
}
?>
