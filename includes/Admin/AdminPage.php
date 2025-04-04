<?php
namespace VESChangeGetter\Admin;

use VESChangeGetter\Models\RatesModel;

/**
 * The admin area functionality
 */
class AdminPage {
    /**
     * Register the admin menu
     */
    public function register_admin_menu() {
        add_menu_page(
            __('VES Change Getter', 'ves-change-getter'),
            __('VES Rates', 'ves-change-getter'),
            'manage_options',
            'ves-change-getter',
            [$this, 'display_admin_page'],
            'dashicons-chart-line',
            100
        );
    }

    /**
     * Register the stylesheets for the admin area
     */
    public function enqueue_styles() {
        $screen = get_current_screen();
        
        if (isset($screen->id) && $screen->id === 'toplevel_page_ves-change-getter') {
            // Generar un timestamp para forzar la recarga y evitar caché
            $version = VES_CHANGE_GETTER_VERSION . '.' . time();
            
            // Asegurar que los dashicons estén cargados
            wp_enqueue_style('dashicons');
            
            // Enqueue Tailwind CSS
            wp_enqueue_style(
                'ves-change-getter-tailwind',
                VES_CHANGE_GETTER_URL . 'assets/css/tailwind.min.css',
                [],
                $version
            );
            
            // Enqueue custom styles
            wp_enqueue_style(
                'ves-change-getter-admin',
                VES_CHANGE_GETTER_URL . 'assets/css/admin.css',
                ['ves-change-getter-tailwind', 'dashicons'],
                $version
            );
        }
    }

    /**
     * Register the JavaScript for the admin area
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();
        
        if (isset($screen->id) && $screen->id === 'toplevel_page_ves-change-getter') {
            wp_enqueue_script(
                'ves-change-getter-admin',
                VES_CHANGE_GETTER_URL . 'assets/js/admin.js',
                ['jquery'],
                VES_CHANGE_GETTER_VERSION,
                true
            );
            
            // Add the AJAX URL to the script
            wp_localize_script(
                'ves-change-getter-admin',
                'ves_change_getter',
                [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('ves_change_getter_nonce')
                ]
            );
        }
    }

    /**
     * Display the admin page
     */
    public function display_admin_page() {
        error_log('VES Change Getter - Iniciando display_admin_page');
        
        // Verificar si se solicita actualización forzada
        $force_update = isset($_GET['force_update']) && $_GET['force_update'] == '1';
        
        if ($force_update) {
            error_log('VES Change Getter - Solicitada actualización forzada');
            $result = RatesModel::fetch_and_store_rates();
            if ($result) {
                error_log('VES Change Getter - Actualización forzada exitosa, ID: ' . $result);
                echo '<div class="notice notice-success is-dismissible"><p>Datos actualizados correctamente.</p></div>';
            } else {
                error_log('VES Change Getter - Error en actualización forzada');
                echo '<div class="notice notice-error is-dismissible"><p>Error al actualizar datos. Revise el registro de errores.</p></div>';
            }
        }
        
        // Ensure we have data to display (fetch if empty)
        $latest_rate = RatesModel::get_latest_rate();
        
        if (!$latest_rate) {
            error_log('VES Change Getter - No se encontró último registro, intentando obtener datos');
            // Try to fetch data if no data exists
            $result = RatesModel::fetch_and_store_rates();
            if ($result) {
                error_log('VES Change Getter - Datos obtenidos y guardados exitosamente, ID: ' . $result);
                $latest_rate = RatesModel::get_latest_rate();
                if (!$latest_rate) {
                    error_log('VES Change Getter - Error: Aún no se puede obtener latest_rate después de fetch_and_store_rates');
                }
            } else {
                error_log('VES Change Getter - Error al obtener y guardar datos');
                echo '<div class="notice notice-error is-dismissible"><p>Error al obtener datos de la API. Revise el registro de errores.</p></div>';
            }
        } else {
            error_log('VES Change Getter - Se encontró último registro, ID: ' . $latest_rate['id']);
        }
        
        // Get all rates for display
        $all_rates = RatesModel::get_all_rates();
        error_log('VES Change Getter - Obtenidos ' . count($all_rates) . ' registros para mostrar');
        
        // Mostrar enlace para actualización forzada
        echo '<div style="margin: 10px 0;"><a href="' . admin_url('admin.php?page=ves-change-getter&force_update=1') . '" class="button">Forzar actualización desde API</a></div>';
        
        // Include the admin view
        include VES_CHANGE_GETTER_PATH . 'views/admin/main.php';
    }
    
    /**
     * AJAX callback to fetch rates data
     */
    public function fetch_rates_data() {
        // Check nonce for security
        check_ajax_referer('ves_change_getter_nonce', 'nonce');
        
        // Only allow admins to access this
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permiso denegado.']);
            return;
        }
        
        // Fetch data from API and store it
        $result = RatesModel::fetch_and_store_rates();
        
        if ($result) {
            wp_send_json_success([
                'message' => 'Datos actualizados correctamente.',
                'result_id' => $result
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Error al actualizar los datos. Por favor revisa el registro de errores para más detalles.'
            ]);
        }
    }
} 