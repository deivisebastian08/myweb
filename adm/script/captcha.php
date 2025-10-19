<?php
/**
 * Generador de CAPTCHA Seguro y Moderno
 *
 * - Genera una imagen con texto aleatorio.
 * - Añade ruido visual (líneas y puntos) para dificultar la lectura por bots.
 * - Usa una fuente TTF para rotar y posicionar caracteres de forma aleatoria.
 * - Almacena el código en la sesión para su posterior validación.
 */

// 1. Cargar la configuración de sesión para asegurar consistencia.
require_once '../session_config.php';

// --- CONFIGURACIÓN DEL CAPTCHA ---
$width = 150;
$height = 50;
$length = 5; // Número de caracteres en el CAPTCHA
$font_path = '../fonts/Poppins-Bold.ttf'; // Ruta a tu fuente TTF
$font_size = 20;

// Caracteres a usar (sin ambigüedades como 0/O, 1/l/I)
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

// --- GENERACIÓN DEL CÓDIGO ---
$captcha_code = '';
for ($i = 0; $i < $length; $i++) {
    $captcha_code .= $chars[rand(0, strlen($chars) - 1)];
}

// 2. Almacenar el código en la sesión (en minúsculas para validación insensible a mayúsculas).
$_SESSION['clave'] = strtolower($captcha_code);

// --- CREACIÓN DE LA IMAGEN ---
$image = imagecreatetruecolor($width, $height);

// 3. Definir colores (tema oscuro para coincidir con el diseño).
$bg_color = imagecolorallocate($image, 30, 30, 30); // Fondo gris oscuro
$text_color = imagecolorallocate($image, 200, 200, 200); // Texto gris claro
$noise_color1 = imagecolorallocate($image, 50, 50, 50);
$noise_color2 = imagecolorallocate($image, 80, 80, 80);

// Rellenar el fondo.
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// 4. Añadir ruido visual (líneas y puntos).
// Añadir líneas aleatorias.
for ($i = 0; $i < 5; $i++) {
    imageline($image, 0, rand() % $height, $width, rand() % $height, $noise_color1);
}
// Añadir puntos (píxeles) aleatorios.
for ($i = 0; $i < 500; $i++) {
    imagesetpixel($image, rand() % $width, rand() % $height, $noise_color2);
}

// 5. Escribir el texto del CAPTCHA en la imagen.
// Se calcula la posición inicial y se usa TTF si está disponible; sino, fallback a imagestring.
$x = 15;
$use_ttf = is_file($font_path) && function_exists('imagettftext');
for ($i = 0; $i < $length; $i++) {
    $char = $captcha_code[$i];
    if ($use_ttf) {
        $angle = rand(-15, 15); // Rotación aleatoria para cada letra.
        $y = rand(30, 40);      // Posición vertical aleatoria.
        imagettftext($image, $font_size, $angle, $x, $y, $text_color, $font_path, $char);
        $x += 25; // Espacio entre caracteres.
    } else {
        // Fallback: usar fuentes internas de GD
        $y = 18; // Altura aproximada para font GD 5
        imagestring($image, 5, $x, $y, $char, $text_color);
        $x += 20; // Espacio entre caracteres para GD
    }
}

// 6. Enviar las cabeceras correctas para la imagen.
// Evita que el navegador guarde la imagen en caché.
header('Content-Type: image/png');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// 7. Mostrar la imagen PNG.
imagepng($image);

// 8. Liberar la memoria usada por la imagen.
imagedestroy($image);
?>