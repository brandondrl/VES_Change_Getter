<?php
namespace VESChangeGetter\API;

use VESChangeGetter\Models\RatesModel;

/**
 * API Endpoints for the plugin
 */
class APIEndpoint {
    /**
     * The namespace for the API
     */
    const API_NAMESPACE = 'ves-change-getter/v1';
    
    /**
     * IP permitida para acceder al endpoint de actualización
     */
    const ALLOWED_IP = VES_API_ALLOWED_IP;
    
    /**
     * Token secreto para autenticar solicitudes
     */
    const SECRET_TOKEN = VES_API_SECRET_TOKEN;

    /**
     * Register the routes for the API
     */
    public function register_routes() {
        // Register route for getting the latest rate
        register_rest_route(
            self::API_NAMESPACE,
            '/latest',
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_latest_rate'],
                'permission_callback' => '__return_true' // Public endpoint
            ]
        );
        
        // Register route for getting rates by date range
        register_rest_route(
            self::API_NAMESPACE,
            '/rates',
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_rates'],
                'permission_callback' => '__return_true', // Public endpoint
                'args' => [
                    'start_date' => [
                        'required' => false,
                        'validate_callback' => function($param) {
                            return is_string($param) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $param);
                        }
                    ],
                    'end_date' => [
                        'required' => false,
                        'validate_callback' => function($param) {
                            return is_string($param) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $param);
                        }
                    ],
                    'limit' => [
                        'required' => false,
                        'validate_callback' => function($param) {
                            return is_numeric($param) && $param > 0;
                        }
                    ]
                ]
            ]
        );
        
        // Register secured route for refreshing rates (only accessible from specified IP and with token)
        register_rest_route(
            self::API_NAMESPACE,
            '/refresh-rates',
            [
                'methods' => 'GET',
                'callback' => [$this, 'refresh_rates'],
                'permission_callback' => [$this, 'verify_refresh_request']
            ]
        );
    }
    
    /**
     * Verify if the request to refresh rates is valid
     * 
     * @param \WP_REST_Request $request The request
     * @return bool Whether the request is valid
     */
    public function verify_refresh_request($request) {
        // Registrar intento de acceso
        error_log('VES Change Getter - Intento de acceso al endpoint de actualización desde: ' . $this->get_client_ip());
        
        // Verificar IP
        $client_ip = $this->get_client_ip();
        if ($client_ip !== self::ALLOWED_IP) {
            error_log('VES Change Getter - Acceso denegado: IP no permitida: ' . $client_ip);
            return false;
        }
        
        // Verificar token
        $token = $request->get_param('token');
        if (!$token || $token !== self::SECRET_TOKEN) {
            error_log('VES Change Getter - Acceso denegado: Token inválido o ausente');
            return false;
        }
        
        return true;
    }
    
    /**
     * Get client IP address
     * 
     * @return string The client IP address
     */
    private function get_client_ip() {
        // Obtener IP desde varias fuentes posibles
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    /**
     * Refresh rates from external API
     * 
     * @param \WP_REST_Request $request The request
     * @return \WP_REST_Response The response
     */
    public function refresh_rates($request) {
        error_log('VES Change Getter - Ejecutando actualización programada desde endpoint seguro');
        
        // Ejecutar la actualización de tasas
        $result = RatesModel::fetch_and_store_rates();
        
        if ($result) {
            $latest_rate = RatesModel::get_latest_rate();
            return new \WP_REST_Response([
                'success' => true,
                'message' => 'Tasas actualizadas correctamente',
                'id' => $result,
                'timestamp' => current_time('mysql')
            ], 200);
        } else {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Error al actualizar las tasas',
                'timestamp' => current_time('mysql')
            ], 500);
        }
    }
    
    /**
     * Get the latest rate
     * 
     * @param \WP_REST_Request $request The request
     * @return \WP_REST_Response The response
     */
    public function get_latest_rate($request) {
        $latest_rate = RatesModel::get_latest_rate();
        
        if (!$latest_rate) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'No data found'
            ], 404);
        }
        
        $data = [
            'success' => true,
            'data' => [
                'id' => $latest_rate['id'],
                'fecha' => $latest_rate['fecha'],
                'update_date' => $latest_rate['update_date'],
                'rates' => $latest_rate['json_decoded']['rates']
            ]
        ];
        
        return new \WP_REST_Response($data, 200);
    }
    
    /**
     * Get rates by date range or get all rates
     * 
     * @param \WP_REST_Request $request The request
     * @return \WP_REST_Response The response
     */
    public function get_rates($request) {
        $params = $request->get_params();
        $start_date = isset($params['start_date']) ? $params['start_date'] : null;
        $end_date = isset($params['end_date']) ? $params['end_date'] : null;
        $limit = isset($params['limit']) ? intval($params['limit']) : 100;
        
        if ($start_date && $end_date) {
            $rates = RatesModel::get_rates_by_date_range($start_date, $end_date);
        } else {
            $rates = RatesModel::get_all_rates($limit);
        }
        
        if (empty($rates)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'No data found'
            ], 404);
        }
        
        $formatted_rates = [];
        foreach ($rates as $rate) {
            $formatted_rates[] = [
                'id' => $rate['id'],
                'fecha' => $rate['fecha'],
                'update_date' => $rate['update_date'],
                'rates' => $rate['json_decoded']['rates']
            ];
        }
        
        $data = [
            'success' => true,
            'count' => count($formatted_rates),
            'data' => $formatted_rates
        ];
        
        return new \WP_REST_Response($data, 200);
    }
} 