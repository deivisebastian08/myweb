<?php
/**
 * Configuración Centralizada de Sesión
 * Asegura que la cookie de sesión sea válida para todo el sitio.
 */

// Configurar la cookie de sesión para que sea válida en todo el sitio ('/myweb/')
session_set_cookie_params(['path' => '/myweb/']);

// Iniciar la sesión si no está ya iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>