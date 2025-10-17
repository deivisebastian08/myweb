<?php
session_start();

// COMENTARIO: Verificación de sesión y rol para acceso a esta página.
// Solo los usuarios con rol_id = 4 (Administrador) o 5 (Super Administrador) pueden acceder.
if(!isset($_SESSION['login']) || ($_SESSION['rol_id'] != 4 && $_SESSION['rol_id'] != 5)){
    header("location:index.php?mensaje=Acceso denegado. Permisos insuficientes.");
    exit();
}

// Verificación de tiempo de sesión
$fecGuar = $_SESSION["hora"];
$ahora = date("Y-n-j H:i:s");
$tmpTrans = (strtotime($ahora)-strtotime($fecGuar));
if($tmpTrans >= 12000){
    session_destroy();
    header("Location: index.php?mensaje=Su sesión ha expirado.");
    exit();
} else {
    $_SESSION["hora"] = $ahora;
}

// COMENTARIO: Lógica para obtener usuarios y roles de la base de datos.
require_once("script/conex.php");
$cn = new MySQLcn();

// Obtener todos los usuarios con su rol
$cn->Query("SELECT u.id, u.nombre, u.email, r.nombre as rol_nombre, r.id as rol_id FROM usuarios u JOIN roles r ON u.rol_id = r.id ORDER BY u.id ASC");
$users = $cn->Rows();

// Obtener todos los roles disponibles para el selector
$cn->Query("SELECT id, nombre FROM roles ORDER BY id ASC");
$roles = $cn->Rows();

$cn->Close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- COMENTARIO: Enlazamos la hoja de estilos del panel de administración. -->
    <link rel="stylesheet" href="css/admin-style.css?v=1.2">
</head>
<body>

<div class="admin-wrapper">
    <!-- COMENTARIO: Barra Lateral de Navegación (Sidebar) -->
    <nav class="admin-sidebar">
        <a class="sidebar-brand" href="manage_users.php">AdminPanel</a>
        <ul class="sidebar-nav nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="user.php"><i class="fas fa-images"></i>Banners</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="manage_users.php"><i class="fas fa-users"></i>Usuarios</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="fas fa-cogs"></i>Configuración</a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle fa-2x me-2"></i>
                    <strong><?php echo $_SESSION["nombre"]; ?></strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                    <li><a class="dropdown-item" href="../index.php">Ver Sitio Web</a></li>
                    <li><a class="dropdown-item" href="#">Cambiar Contraseña</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- COMENTARIO: Contenido Principal de Gestión de Usuarios -->
    <main class="admin-content">
        <div class="container-fluid">
            <h1 class="h3 mb-4">Gestión de Usuarios</h1>

            <?php if (isset($_GET['mensaje'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['mensaje']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    Lista de Usuarios
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol Actual</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No hay usuarios registrados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge 
                                                <?php 
                                                    if($user['rol_id'] == 5) echo 'bg-danger';
                                                    else if($user['rol_id'] == 4) echo 'bg-warning text-dark';
                                                    else if($user['rol_id'] == 3) echo 'bg-info text-dark';
                                                    else if($user['rol_id'] == 2) echo 'bg-primary';
                                                    else echo 'bg-secondary';
                                                ?>">
                                                <?php echo htmlspecialchars($user['rol_nombre']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form action="process_user_action.php" method="POST" class="d-inline-flex">
                                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                                <select name="new_rol_id" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                                                    <?php foreach ($roles as $rol): ?>
                                                        <option value="<?php echo htmlspecialchars($rol['id']); ?>" <?php echo ($rol['id'] == $user['rol_id']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($rol['nombre']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar a este usuario?');">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
