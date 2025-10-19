<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mensaje = isset($_GET['mensaje']) ? urldecode($_GET['mensaje']) : "Por favor, ingrese sus credenciales para continuar.";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Inicio de Sesión - Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Great+Vibes&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    
    <link rel="stylesheet" href="css/login-style.css?v=5.0">

    <script language='JavaScript' type='text/javascript' src='js/generax.js'></script>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            
            <div class="logo-container">
                <svg class="bts-logo" viewBox="0 0 101 56" xmlns="http://www.w3.org/2000/svg"><path d="M0 0H36.2871L30.2392 56H0V0Z"/><path d="M64.7129 0H101L94.9521 56H64.7129V0Z"/></svg>
            </div>
            
            <h1 class="welcome-script">Welcome to the Website</h1>
            <p class="welcome-text"><?php echo htmlspecialchars($mensaje); ?></p>
            <h2 class="login-heading">USER LOGIN</h2>
            
            <form name='sesion' action='login.php' onSubmit='return iniciar();' method='POST'>
                
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="users" class="form-control" placeholder="Username" required>
                </div>
                
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="pass" class="form-control" placeholder="Password" required>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 form-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="rememberCheck">
                        <label class="form-check-label" for="rememberCheck">Remember</label>
                    </div>
                    <a href="#">Forgot password?</a>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fas fa-image"></i></span>
                     <img src='script/generax.php?img=true' alt="Captcha Image" class="captcha-img">
                </div>
                <div class="input-group mb-4">
                     <span class="input-group-text"><i class="fas fa-keyboard"></i></span>
                    <input type="text" name="clave" class="form-control" placeholder="Enter the text from the image" required>
                </div>
                
                <div class="d-grid">
                    <input type="submit" name="button" class="btn login_btn" value="LOGIN">
                </div>
            </form>

            <!-- COMENTARIO: Se añade el enlace a la página de registro. -->
            <div class="mt-4 text-center links">
                <span>Don't have an account? <a href="register.php">Sign Up</a></span>
            </div>
        </div>
    </div>
</body>
</html>
