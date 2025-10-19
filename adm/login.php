<?php
session_start();

// Verificación de captcha (generax.php guarda md5 en $_SESSION['key'])
function fnComprueba(&$msg){
  if (!isset($_POST['clave']) || !isset($_SESSION['key'])) return 0;
  return (md5(strtolower(trim($_POST['clave']))) === $_SESSION['key']) ? 1 : 0;
}

// Si ya hay sesión, usar enrutador por rol
if(isset($_SESSION['user_id'])){
    header("Location: router.php");
    exit();
}

// Procesar login
if(isset($_POST['email']) || isset($_POST['login']) || isset($_POST['users'])){
    if(fnComprueba($msg) != 1){
        header("Location: index.php?mensaje=".urlencode("El texto de la imagen es incorrecto."));
        exit();
    }

    // Tomar credenciales desde cualquiera de los nombres soportados
    if (isset($_POST['email'])) { $loginInput = trim($_POST['email']); }
    elseif (isset($_POST['login'])) { $loginInput = trim($_POST['login']); }
    else { $loginInput = trim($_POST['users'] ?? ''); }
    $pass = isset($_POST['password']) ? trim($_POST['password']) : trim($_POST['pass'] ?? '');

    require_once 'script/Supabase.php';
    $supabase = new Supabase();

    // Consultar por email o por nombre, trayendo rol embebido y rol_id para fallback
    $select = 'id,nombre,email,password,estado,rol_id,rol:rol_id(slug,nombre)';
    if (strpos($loginInput, '@') !== false) {
        $data = $supabase->from('usuarios', [
            'select' => $select,
            'email'  => 'eq.' . $loginInput,
            'limit'  => 1
        ]);
    } else {
        $data = $supabase->from('usuarios', [
            'select' => $select,
            'nombre' => 'eq.' . $loginInput,
            'limit'  => 1
        ]);
        if (!$data || empty($data)) {
            // fallback por email
            $data = $supabase->from('usuarios', [
                'select' => $select,
                'email'  => 'eq.' . $loginInput,
                'limit'  => 1
            ]);
        }
    }

    $user = ($data && isset($data[0])) ? $data[0] : null;

    // Validar usuario, estado y password
    if($user && isset($user['password']) && ($user['estado'] === 'activo') && password_verify($pass, $user['password'])){
        // Resolver slug del rol
        $rol_slug = null;
        if (isset($user['rol']) && is_array($user['rol']) && isset($user['rol']['slug'])) {
            $rol_slug = $user['rol']['slug'];
        } else if (isset($user['rol_id'])) {
            // Intento secundario: leer roles
            $roleRow = $supabase->from('roles', [
                'select' => 'slug',
                'id'     => 'eq.' . $user['rol_id'],
                'limit'  => 1
            ]);
            if ($roleRow && isset($roleRow[0]['slug'])) $rol_slug = $roleRow[0]['slug'];
        }
        if (!$rol_slug) { $rol_slug = 'usuario_publico'; }
        $rol_slug = strtolower(str_replace(['_', ' '], '-', trim($rol_slug)));

        // Guardar sesión unificada para router.php
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_nombre'] = $user['nombre'];
        $_SESSION['user_rol_slug'] = $rol_slug;
        $_SESSION['hora'] = date('Y-n-j H:i:s');

        // Compatibilidad con dashboards legados
        $_SESSION['login'] = $user['nombre'];
        $_SESSION['nombre'] = $user['nombre'];
        if (isset($user['rol_id'])) {
            $_SESSION['rol_id'] = (int)$user['rol_id'];
        }

        header('Location: router.php');
        exit();
    }

    header("Location: index.php?mensaje=".urlencode('Usuario/contraseña incorrectos o cuenta inactiva.'));
    exit();
}

// Acceso directo sin POST
header("Location: index.php");
exit();
?>
