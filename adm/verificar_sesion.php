<?php
/**
 * Middleware de Seguridad de Sesión
 * Verifica si un usuario ha iniciado sesión.
 * Debe ser incluido al principio de cada página protegida.
 */

require_once 'session_config.php';

// Si no existe la variable de sesión 'user_id', significa que no ha iniciado sesión.
if (!isset($_SESSION['user_id'])) {
    // Redirigir a la página de login con un mensaje.
    header('Location: login.php?mensaje=' . urlencode('Debes iniciar sesión para acceder a esta página.'));
    exit();
}
?>