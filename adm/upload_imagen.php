<?php
/**
 * Script para subir imágenes
 * Soporta: JPG, JPEG, PNG, GIF, WEBP
 * Tamaño máximo: 5MB
 */

session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Configuración
$uploadDir = __DIR__ . '/../uploads/';
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

// Crear directorio si no existe
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'No se pudo crear el directorio de uploads']);
        exit;
    }
}

// Verificar que se subió un archivo
if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    $errorMsg = 'Error al subir el archivo';
    if (isset($_FILES['imagen']['error'])) {
        switch ($_FILES['imagen']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg = 'El archivo es demasiado grande (máximo 5MB)';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg = 'No se seleccionó ningún archivo';
                break;
        }
    }
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

$file = $_FILES['imagen'];

// Validar tipo de archivo
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido. Solo: JPG, PNG, GIF, WEBP']);
    exit;
}

// Validar tamaño
if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El archivo es demasiado grande (máximo 5MB)']);
    exit;
}

// Generar nombre único
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('img_') . '_' . time() . '.' . $extension;
$filepath = $uploadDir . $filename;

// Mover archivo
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo']);
    exit;
}

// Redimensionar imagen si es muy grande (opcional)
try {
    $imageInfo = getimagesize($filepath);
    if ($imageInfo !== false) {
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        // Si la imagen es muy grande, redimensionar
        if ($width > 1920 || $height > 1080) {
            redimensionarImagen($filepath, $filepath, 1920, 1080);
        }
    }
} catch (Exception $e) {
    // Ignorar errores de redimensionamiento
}

// Retornar URL relativa de la imagen
$urlImagen = '/myweb/uploads/' . $filename;

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Imagen subida correctamente',
    'url' => $urlImagen,
    'filename' => $filename
]);

/**
 * Función para redimensionar imagen manteniendo proporción
 */
function redimensionarImagen($origen, $destino, $maxWidth, $maxHeight) {
    $imageInfo = getimagesize($origen);
    if ($imageInfo === false) return false;
    
    list($width, $height, $type) = $imageInfo;
    
    // Calcular nuevas dimensiones manteniendo proporción
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    if ($ratio >= 1) return true; // No redimensionar si ya es más pequeña
    
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);
    
    // Crear imagen según tipo
    switch ($type) {
        case IMAGETYPE_JPEG:
            $imageOrigen = imagecreatefromjpeg($origen);
            break;
        case IMAGETYPE_PNG:
            $imageOrigen = imagecreatefrompng($origen);
            break;
        case IMAGETYPE_GIF:
            $imageOrigen = imagecreatefromgif($origen);
            break;
        case IMAGETYPE_WEBP:
            $imageOrigen = imagecreatefromwebp($origen);
            break;
        default:
            return false;
    }
    
    if (!$imageOrigen) return false;
    
    // Crear nueva imagen redimensionada
    $imageDestino = imagecreatetruecolor($newWidth, $newHeight);
    
    // Mantener transparencia para PNG y GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($imageDestino, false);
        imagesavealpha($imageDestino, true);
        $transparent = imagecolorallocatealpha($imageDestino, 255, 255, 255, 127);
        imagefilledrectangle($imageDestino, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    imagecopyresampled($imageDestino, $imageOrigen, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Guardar según tipo
    $resultado = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $resultado = imagejpeg($imageDestino, $destino, 90);
            break;
        case IMAGETYPE_PNG:
            $resultado = imagepng($imageDestino, $destino, 9);
            break;
        case IMAGETYPE_GIF:
            $resultado = imagegif($imageDestino, $destino);
            break;
        case IMAGETYPE_WEBP:
            $resultado = imagewebp($imageDestino, $destino, 90);
            break;
    }
    
    imagedestroy($imageOrigen);
    imagedestroy($imageDestino);
    
    return $resultado;
}
?>
