<?php
// ====================================================================
// COMENTARIO: Script de conexión a la base de datos de Supabase (PostgreSQL)
// ====================================================================

function get_supabase_connection() {
    // --- ¡ACCIÓN REQUERIDA! RELLENA ESTOS DATOS ---
    // Encuentra esta información en tu panel de Supabase:
    // Settings -> Database -> Connection info
    $host = 'TU_HOST_DE_SUPABASE'; // ej. db.xxxxxxxx.supabase.co
    $port = 'TU_PUERTO'; // ej. 5432
    $dbname = 'postgres'; // normalmente es 'postgres'
    $user = 'postgres'; // normalmente es 'postgres'
    $password = 'TU_CONTRASENA_DE_LA_BD';
    // -----------------------------------------------------

    // Construir la cadena de conexión para PostgreSQL
    $conn_string = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password}";

    // Establecer la conexión
    $dbconn = pg_connect($conn_string);

    // Verificar si la conexión fue exitosa
    if (!$dbconn) {
        // Si la conexión falla, detiene el script y muestra un error genérico.
        // En un entorno de producción, también se debería registrar el error detallado.
        error_log("Error de conexión a Supabase: No se pudo conectar a la base de datos.");
        die("Error: No se pudo conectar a la base de datos. Por favor, intente más tarde.");
    }

    return $dbconn;
}
?>
