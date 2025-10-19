<?php
/**
 * Enrutador Inteligente por Rol
 * Recibe a los usuarios que han iniciado sesión y los redirige
 * a su panel de control correspondiente.
 */

// 1. Asegurarse de que solo usuarios logueados puedan usar el enrutador.
require_once 'verificar_sesion.php';

// 2. Obtener el rol del usuario de la sesión.
$rol_slug = $_SESSION['user_rol_slug'] ?? 'usuario-publico';
// Normalizar: trim, minúsculas, underscores y espacios a hyphens
$rol_slug = strtolower(trim($rol_slug));
$rol_slug = str_replace(['_', ' '], '-', $rol_slug);

// 3. Decidir la URL de destino según el rol.
switch ($rol_slug) {
    case 'super-admin':
        $redirect_url = 'roles/super-admin/super-admin_dashboard.php';
        break;
    case 'admin':
    case 'administrador': // slug alternativo desde BD
        $redirect_url = 'roles/admin/admin_dashboard.php';
        break;
    case 'admin-principal': // alias solicitado
        $redirect_url = 'roles/super-admin/super-admin_dashboard.php';
        break;
    case 'editor':
        $redirect_url = 'roles/editor/editor_dashboard.php';
        break;
    case 'visualizador':
        $redirect_url = 'roles/visualizador/visualizador_dashboard.php';
        break;
    case 'lector-informes':
        $redirect_url = 'roles/lector-informes/lector-informes_dashboard.php';
        break;
    case 'invitado-demo':
        $redirect_url = 'roles/invitado-demo/invitado-demo_dashboard.php';
        break;
    case 'usuario-base':
        $redirect_url = 'roles/usuario-base/usuario-base_dashboard.php';
        break;
    case 'usuario-publico':
        $redirect_url = 'roles/usuario-base/usuario-base_dashboard.php';
        break;
    default:
        $redirect_url = '../cuenta_usuario.php';
        break;
}

// 4. Construir URL absoluta bajo /myweb/adm/
$base = '/myweb/adm/';
if (strpos($redirect_url, '../') === 0) {
    // Ajustar subida de un nivel: /myweb/adm/../ -> /myweb/
    $absolute = '/myweb/' . substr($redirect_url, 3);
} else {
    $absolute = $base . $redirect_url;
}

// Modo debug opcional para diagnosticar redirección: /router.php?debug=1
if (isset($_GET['debug'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "rol_slug_normalizado: {$rol_slug}\n";
    echo "redirect_url_relativo: {$redirect_url}\n";
    echo "redirect_url_absoluto: {$absolute}\n";
    echo "session_user_id: ".($_SESSION['user_id'] ?? 'N/A')."\n";
    exit;
}

// 5. Redirigir al usuario a su destino con ruta absoluta
header("Location: " . $absolute);
exit();
?>