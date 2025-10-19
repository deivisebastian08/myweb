<?php
/**
 * Clase para interactuar con la API REST de Supabase usando cURL.
 * Reemplaza la funcionalidad de la clase MySQLcn para proyectos con Supabase.
 */
class Supabase
{
    private $_url;
    private $_apiKey;

    /**
     * Constructor que inicializa la URL y la clave de la API de Supabase.
     */
    public function __construct()
    {
        // --- Credenciales de Supabase (Actualizadas) ---
        $this->_url = "https://xzgjclvdwglbokskmndn.supabase.co";
        $this->_apiKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inh6Z2pjbHZkd2dsYm9rc2ttbmRuIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjA4MDk5NDcsImV4cCI6MjA3NjM4NTk0N30.WQxWK-RYx0rttlnWiTA1B9G3ERSXBwrmlgnJPQgD6kw";
    }

    /**
     * Realiza una petición GET para obtener datos de una tabla.
     *
     * @param string $table El nombre de la tabla de la que quieres obtener datos.
     * @param array $params Parámetros de consulta (ej: ['select' => 'id,nombre', 'order' => 'id.desc']).
     * @return array|null Los datos decodificados como un array asociativo, o null si hay un error.
     */
    public function from(string $table, array $params = []): ?array
    {
        // 1. Construir la URL final con los parámetros
        $endpoint = $this->_url . '/rest/v1/' . $table;
        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }

        // 2. Configurar las cabeceras (headers) para la autenticación
        $headers = [
            'apikey: ' . $this->_apiKey,
            'Authorization: Bearer ' . $this->_apiKey,
            'Accept-Profile: public'
        ];

        // 3. Inicializar y configurar cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Devuelve la respuesta como string
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Añade las cabeceras
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Tiempo de espera de 10 segundos

        // 4. Ejecutar la petición
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 5. Procesar la respuesta
        if ($http_code >= 200 && $http_code < 300) {
            return json_decode($response, true); // Decodifica el JSON a un array asociativo
        } else {
            // Registrar error para diagnóstico
            error_log("Supabase GET error (HTTP $http_code): $response");
            return null;
        }
    }

    /**
     * Inserta una nueva fila en una tabla.
     *
     * @param string $table El nombre de la tabla.
     * @param array $data Un array asociativo con los datos a insertar (columna => valor).
     * @return array|null El registro insertado si la operación es exitosa, o null si falla.
     */
    public function insert(string $table, array $data): ?array
    {
        $endpoint = $this->_url . '/rest/v1/' . $table;

        // 1. Convertir los datos a formato JSON
        $jsonData = json_encode($data);

        // 2. Configurar las cabeceras para una operación POST
        $headers = [
            'apikey: ' . $this->_apiKey,
            'Authorization: Bearer ' . $this->_apiKey,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData),
            'Prefer: return=representation', // Pide a Supabase que devuelva el objeto insertado
            'Content-Profile: public'
        ];

        // 3. Inicializar y configurar cURL para POST
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        // 4. Ejecutar y procesar la respuesta
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 201) { // 201 Created es el código de éxito para inserciones
            return json_decode($response, true);
        }
        error_log("Supabase INSERT error (HTTP $http_code): $response");
        return null; // Hubo un error
    }

    /**
     * Actualiza filas de una tabla usando filtros (PATCH).
     * Ejemplo de $filters: ['id' => 'eq.123']
     */
    public function update(string $table, array $data, array $filters): ?array
    {
        $endpoint = $this->_url . '/rest/v1/' . $table;
        if (!empty($filters)) {
            $endpoint .= '?' . http_build_query($filters);
        }

        $jsonData = json_encode($data);
        $headers = [
            'apikey: ' . $this->_apiKey,
            'Authorization: Bearer ' . $this->_apiKey,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData),
            'Prefer: return=representation',
            'Content-Profile: public'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code >= 200 && $http_code < 300) {
            return json_decode($response, true);
        }
        error_log("Supabase UPDATE error (HTTP $http_code): $response");
        return null;
    }

    /**
     * Elimina filas de una tabla usando filtros (DELETE).
     * Ejemplo de $filters: ['id' => 'eq.123']
     */
    public function delete(string $table, array $filters): bool
    {
        $endpoint = $this->_url . '/rest/v1/' . $table;
        if (!empty($filters)) {
            $endpoint .= '?' . http_build_query($filters);
        }

        $headers = [
            'apikey: ' . $this->_apiKey,
            'Authorization: Bearer ' . $this->_apiKey,
            'Accept-Profile: public'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code >= 200 && $http_code < 300) {
            return true;
        }
        error_log("Supabase DELETE error (HTTP $http_code): $response");
        return false;
    }
}
?>