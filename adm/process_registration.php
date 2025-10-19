<?php
// Inicia la sesión para poder manejar mensajes.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php?error=' . urlencode('Método no permitido.'));
    exit();
}

// 2. Obtener datos del formulario (alineado a register.php)
$username = trim($_POST['users'] ?? '');
$emailRaw = trim($_POST['email'] ?? '');
$email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL) ? $emailRaw : '';
$password = trim($_POST['pass'] ?? '');
$confirm = trim($_POST['confirm_pass'] ?? '');

// 3. Validaciones básicas
if ($username === '' || $email === '' || $password === '' || $confirm === '') {
    header('Location: register.php?error=' . urlencode('Todos los campos son obligatorios y el correo debe ser válido.'));
    exit();
}
if (strlen($password) < 8) {
    header('Location: register.php?error=' . urlencode('La contraseña debe tener al menos 8 caracteres.'));
    exit();
}
if ($password !== $confirm) {
    header('Location: register.php?error=' . urlencode('Las contraseñas no coinciden.'));
    exit();
}

// 4. Usar cliente Supabase REST
require_once 'script/Supabase.php';
$supabase = new Supabase();

// 5. Verificar duplicados por email y por nombre de usuario
$existingEmail = $supabase->from('usuarios', [
    'select' => 'id',
    'email'  => 'eq.' . $email,
    'limit'  => 1
]);
if ($existingEmail && !empty($existingEmail)) {
    header('Location: register.php?error=' . urlencode('El correo ya está registrado.'));
    exit();
}

$existingUser = $supabase->from('usuarios', [
    'select' => 'id',
    'nombre' => 'eq.' . $username,
    'limit'  => 1
]);
if ($existingUser && !empty($existingUser)) {
    header('Location: register.php?error=' . urlencode('El nombre de usuario ya está en uso.'));
    exit();
}

// 6. Obtener rol por defecto (Usuario Público o el último rol)
$roles = $supabase->from('roles', ['select' => 'id,slug', 'order' => 'nivel.asc', 'limit' => 10]);
$rolDefecto = 5; // fallback
if ($roles && is_array($roles)) {
    foreach ($roles as $r) {
        if (in_array(strtolower($r['slug'] ?? ''), ['usuario_publico', 'usuario-publico', 'publico'])) {
            $rolDefecto = (int)$r['id'];
            break;
        }
    }
}

// 7. Insertar usuario nuevo (columna 'password' coherente con login.php)
$hash = password_hash($password, PASSWORD_DEFAULT);
$payload = [
    'nombre'   => $username,
    'email'    => $email,
    'password' => $hash,
    'estado'   => 'activo',
    'rol_id'   => $rolDefecto
];

$inserted = $supabase->insert('usuarios', $payload);
if ($inserted && isset($inserted[0]['id'])) {
    // 7. Redirigir al login con mensaje de éxito
    $mensaje = '¡Registro exitoso! Ahora puedes iniciar sesión.';
    header('Location: index.php?mensaje=' . urlencode($mensaje));
    exit();
}

// Intento de diagnóstico: verificar si existe el usuario recién creado
$check = $supabase->from('usuarios', [
    'select' => 'id',
    'nombre' => 'eq.' . $username,
    'limit'  => 1
]);

if ($check && !empty($check)) {
    // Existe pero la API de inserción no devolvió 201; aún así pedimos iniciar sesión
    $mensaje = 'Cuenta creada. Ahora puedes iniciar sesión.';
    header('Location: index.php?mensaje=' . urlencode($mensaje));
    exit();
}

// Si llegamos aquí, el INSERT falló. Verificar log de PHP para ver el error HTTP de Supabase
error_log("ERROR REGISTRO: No se pudo insertar usuario. Payload: " . json_encode($payload));
header('Location: register.php?error=' . urlencode('No se pudo registrar. Verifica que en Supabase tengas: create policy anon_insert_usuarios on public.usuarios for insert to anon with check (true);'));
exit();
?>
