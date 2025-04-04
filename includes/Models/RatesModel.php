<?php
namespace VESChangeGetter\Models;

/**
 * Handles operations related to API rates data
 */
class RatesModel {
    /**
     * The table name
     */
    private static $table_name;

    /**
     * Initializes the model
     */
    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'ves_change_getter_rates';
    }

    /**
     * Fetches data from the API and stores it in the database
     */
    public static function fetch_and_store_rates() {
        self::init();
        
        error_log('VES Change Getter - Iniciando fetch_and_store_rates');
        
        // Verificar que la tabla existe
        global $wpdb;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '" . self::$table_name . "'");
        if (!$table_exists) {
            error_log('VES Change Getter - Error: La tabla ' . self::$table_name . ' no existe');
            
            // Intenta crear la tabla si no existe
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE " . self::$table_name . " (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                json longtext NOT NULL,
                fecha datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                update_date datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            
            // Verificar si ahora existe
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '" . self::$table_name . "'");
            if (!$table_exists) {
                error_log('VES Change Getter - Error: No se pudo crear la tabla ' . self::$table_name);
                return false;
            } else {
                error_log('VES Change Getter - Tabla creada exitosamente');
            }
        }
        
        // Verificar URL de la API
        error_log('VES Change Getter - URL de la API: ' . VES_CHANGE_GETTER_API_URL);
        
        // Get data from the API
        $response = wp_remote_get(VES_CHANGE_GETTER_API_URL, [
            'timeout' => 30, // Aumentado a 30 segundos
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'WordPress/VES-Change-Getter'
            ],
            'sslverify' => false // Desactivar verificación SSL para pruebas
        ]);
        
        if (is_wp_error($response)) {
            error_log('VES Change Getter - API Error: ' . $response->get_error_message());
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        error_log('VES Change Getter - API Status Code: ' . $status_code);
        
        if ($status_code !== 200) {
            error_log('VES Change Getter - API Error: Status code ' . $status_code);
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            error_log('VES Change Getter - API Error: Empty response body');
            return false;
        }
        
        error_log('VES Change Getter - API Response Body (first 255 chars): ' . substr($body, 0, 255));
        
        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
            error_log('VES Change Getter - API Error: Invalid JSON - ' . json_last_error_msg());
            error_log('VES Change Getter - API Response: ' . substr($body, 0, 255));
            return false;
        }
        
        // Process the data into the required format
        $processed_data = self::process_api_data($data);
        
        // Debug processed data
        error_log('VES Change Getter - Processed Data: ' . $processed_data);
        
        // Store the processed data
        $insert_result = self::insert_rate($processed_data);
        error_log('VES Change Getter - Insert Result: ' . ($insert_result ? $insert_result : 'false'));
        
        return $insert_result;
    }
    
    /**
     * Process the API data into the required format
     * 
     * @param array $data The data from the API
     * @return string JSON representation of the processed data
     */
    private static function process_api_data($data) {
        $processed = [
            'rates' => []
        ];
        
        // Debug data structure received
        error_log('VES Change Getter - API Data Structure: ' . print_r($data, true));
        
        // BCV data
        if (isset($data['monitors']['bcv'])) {
            $bcv = $data['monitors']['bcv'];
            $processed['rates']['bcv'] = [
                'value' => $bcv['price'],
                'catch_date' => $bcv['last_update']
            ];
            error_log('VES Change Getter - BCV data processed: ' . $bcv['price']);
        } else {
            error_log('VES Change Getter - BCV data not found in API response');
            // Intentar encontrar la estructura correcta
            error_log('VES Change Getter - Available keys in monitors: ' . (isset($data['monitors']) ? implode(', ', array_keys($data['monitors'])) : 'monitors not found'));
        }
        
        // Parallel data
        if (isset($data['monitors']['enparalelovzla'])) {
            $parallel = $data['monitors']['enparalelovzla'];
            $processed['rates']['parallel'] = [
                'value' => $parallel['price'],
                'catch_date' => $parallel['last_update']
            ];
            error_log('VES Change Getter - Parallel data processed: ' . $parallel['price']);
        } else {
            error_log('VES Change Getter - Parallel data not found in API response');
        }
        
        // Calculate average
        if (isset($processed['rates']['bcv']) && isset($processed['rates']['parallel'])) {
            $average_value = ($processed['rates']['bcv']['value'] + $processed['rates']['parallel']['value']) / 2;
            $processed['rates']['average'] = [
                'value' => round($average_value, 2),
                'catch_date' => date('m/d/Y, h:i A')
            ];
            error_log('VES Change Getter - Average calculated: ' . $average_value);
        }
        
        return json_encode($processed);
    }
    
    /**
     * Inserts a new rate record
     * 
     * @param string $json_data JSON data to store
     * @return bool|int The row ID on success, false on failure
     */
    public static function insert_rate($json_data) {
        self::init();
        
        global $wpdb;
        
        $result = $wpdb->insert(
            self::$table_name,
            [
                'json' => $json_data,
                'fecha' => current_time('mysql')
            ],
            [
                '%s',
                '%s'
            ]
        );
        
        if ($result === false) {
            error_log('VES Change Getter - Database insert error: ' . $wpdb->last_error);
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Gets the latest rate
     * 
     * @return array|null Rate data or null if no data found
     */
    public static function get_latest_rate() {
        self::init();
        
        global $wpdb;
        
        error_log('VES Change Getter - Consultando el último registro');
        
        // Verificar que la tabla existe
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '" . self::$table_name . "'");
        if (!$table_exists) {
            error_log('VES Change Getter - Error en get_latest_rate: La tabla ' . self::$table_name . ' no existe');
            return null;
        }
        
        // Contar registros
        $count = $wpdb->get_var("SELECT COUNT(*) FROM " . self::$table_name);
        error_log('VES Change Getter - Número de registros en la tabla: ' . $count);
        
        if ($count == 0) {
            error_log('VES Change Getter - No hay registros en la tabla');
            return null;
        }
        
        $query = "SELECT * FROM " . self::$table_name . " ORDER BY fecha DESC LIMIT 1";
        error_log('VES Change Getter - Query: ' . $query);
        
        $result = $wpdb->get_row($query, ARRAY_A);
        
        if ($result) {
            error_log('VES Change Getter - Registro encontrado con ID: ' . $result['id']);
            $result['json_decoded'] = json_decode($result['json'], true);
            
            // Verificar si el JSON se decodificó correctamente
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('VES Change Getter - Error al decodificar JSON: ' . json_last_error_msg());
                error_log('VES Change Getter - JSON original: ' . $result['json']);
            } else {
                error_log('VES Change Getter - JSON decodificado correctamente');
            }
        } else {
            error_log('VES Change Getter - No se encontró ningún registro en get_latest_rate');
            error_log('VES Change Getter - Último error de base de datos: ' . $wpdb->last_error);
        }
        
        return $result;
    }
    
    /**
     * Gets rates for a specific date range
     * 
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @return array Array of rate records
     */
    public static function get_rates_by_date_range($start_date, $end_date) {
        self::init();
        
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " 
            WHERE DATE(fecha) BETWEEN %s AND %s 
            ORDER BY fecha DESC",
            $start_date,
            $end_date
        );
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        foreach ($results as &$result) {
            $result['json_decoded'] = json_decode($result['json'], true);
        }
        
        return $results;
    }
    
    /**
     * Gets all rates
     * 
     * @param int $limit Optional. Number of records to retrieve
     * @return array Array of rate records
     */
    public static function get_all_rates($limit = 100) {
        self::init();
        
        global $wpdb;
        
        error_log('VES Change Getter - Consultando todos los registros (limit: ' . $limit . ')');
        
        // Verificar que la tabla existe
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '" . self::$table_name . "'");
        if (!$table_exists) {
            error_log('VES Change Getter - Error en get_all_rates: La tabla ' . self::$table_name . ' no existe');
            return [];
        }
        
        // Contar registros
        $count = $wpdb->get_var("SELECT COUNT(*) FROM " . self::$table_name);
        error_log('VES Change Getter - Número total de registros en la tabla: ' . $count);
        
        if ($count == 0) {
            error_log('VES Change Getter - No hay registros en la tabla para mostrar');
            return [];
        }
        
        $query = $wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " 
            ORDER BY fecha DESC LIMIT %d",
            $limit
        );
        
        error_log('VES Change Getter - Query para obtener registros: ' . $query);
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        if (!$results) {
            error_log('VES Change Getter - No rates found in database');
            error_log('VES Change Getter - Último error de base de datos: ' . $wpdb->last_error);
            $results = [];
        } else {
            error_log('VES Change Getter - Encontrados ' . count($results) . ' registros');
        }
        
        foreach ($results as &$result) {
            $result['json_decoded'] = json_decode($result['json'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('VES Change Getter - Error al decodificar JSON del registro ID ' . $result['id'] . ': ' . json_last_error_msg());
            }
        }
        
        return $results;
    }
} 