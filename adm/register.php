<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mensaje = isset($_GET['error']) ? urldecode($_GET['error']) : "Crea una nueva cuenta para empezar.";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Registro de Usuario</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Great+Vibes&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    
    <!-- COMENTARIO: Reutilizamos la misma hoja de estilos del login para mantener la consistencia. -->
    <link rel="stylesheet" href="css/login-style.css?v=4.2">

</head>
<body>
    <div class="login-container">
        <div class="login-card">
            
            <div class="logo-container">
                <svg class="bts-logo" viewBox="0 0 101 56" xmlns="http://www.w3.org/2000/svg"><path d="M0 0H36.2871L30.2392 56H0V0Z"/><path d="M64.7129 0H101L94.9521 56H64.7129V0Z"/></svg>
            </div>
            
            <h1 class="welcome-script">Join Our Community</h1>
            <p class="welcome-text"><?php echo htmlspecialchars($mensaje); ?></p>
            <h2 class="login-heading">CREATE ACCOUNT</h2>
            
            <form action="process_registration.php" method="POST">
                
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="users" class="form-control" placeholder="Username" required>
                </div>
                
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="pass" class="form-control" placeholder="Password" required>
                </div>

                <div class="input-group mb-4">
                    <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                    <input type="password" name="confirm_pass" class="form-control" placeholder="Confirm Password" required>
                </div>
                
                <div class="d-grid">
                    <input type="submit" name="button" class="btn login_btn" value="REGISTER">
                </div>
            </form>

            <div class="mt-4 text-center links">
                <a href="index.php">&larr; Already have an account? Login</a>
            </div>
        </div>
    </div>
</body>
</html>
