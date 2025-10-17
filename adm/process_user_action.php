<?php
session_start();

// COMENTARIO: Verificación de sesión y rol para acceso a este script.
// Solo los usuarios con rol_id = 4 (Administrador) o 5 (Super Administrador) pueden ejecutar este script.
if(!isset($_SESSION['login']) || ($_SESSION['rol_id'] != 4 && $_SESSION['rol_id'] != 5)){
    header("location:index.php?mensaje=Acceso denegado. Permisos insuficientes para gestionar usuarios.");
    exit();
}

require_once("script/conex.php");
$cn = new MySQLcn();

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($user_id > 0) {
        // COMENTARIO: Evitar que un administrador se elimine o cambie su propio rol si es el único Super Admin/Admin.
        // Esto es una medida de seguridad básica.
        if ($user_id == $_SESSION['idUser'] && ($_SESSION['rol_id'] == 4 || $_SESSION['rol_id'] == 5)) {
            // Si es un Super Admin intentando cambiar su propio rol o eliminarse
            // Se podría añadir lógica más compleja aquí, por ahora, simplemente no permitirlo.
            $mensaje = "No puedes modificar o eliminar tu propia cuenta de administrador desde aquí.";
            header("location:manage_users.php?mensaje=" . urlencode($mensaje));
            exit();
        }

        if ($action === 'delete') {
            // Eliminar usuario
            $query = "DELETE FROM usuarios WHERE id = $user_id";
            if ($cn->Query($query)) {
                $mensaje = "Usuario eliminado exitosamente.";
            } else {
                $mensaje = "Error al eliminar usuario.";
            }
        } else if (isset($_POST['new_rol_id'])) {
            // Cambiar rol de usuario
            $new_rol_id = (int)$_POST['new_rol_id'];
            $query = "UPDATE usuarios SET rol_id = $new_rol_id WHERE id = $user_id";
            if ($cn->Query($query)) {
                $mensaje = "Rol de usuario actualizado exitosamente.";
            } else {
                $mensaje = "Error al actualizar rol de usuario.";
            }
        }
    } else {
        $mensaje = "ID de usuario no válido.";
    }
}

$cn->Close();
header("location:manage_users.php?mensaje=" . urlencode($mensaje));
exit();
?>
