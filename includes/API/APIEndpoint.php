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