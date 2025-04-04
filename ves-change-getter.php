<?php
/**
 * Plugin Name: VES Change Getter
 * Description: Obtiene datos de tasas de cambio desde una API externa y los procesa
 * Version: 1.0.0
 * Author: IDSI
 * Text Domain: ves-change-getter
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('VES_CHANGE_GETTER_VERSION', '1.0.0');
define('VES_CHANGE_GETTER_PATH', plugin_dir_path(__FILE__));
define('VES_CHANGE_GETTER_URL', plugin_dir_url(__FILE__));
define('VES_CHANGE_GETTER_API_URL', 'https://pydolarve.org/api/v1/dollar');

// Autoload classes
spl_autoload_register(function ($class_name) {
    // Only autoload classes from our namespace
    if (strpos($class_name, 'VESChangeGetter\\') === 0) {
        // Convert namespace to file path
        $class_file = str_replace('\\', '/', str_replace('VESChangeGetter\\', '', $class_name));
        $class_file = VES_CHANGE_GETTER_PATH . 'includes/' . $class_file . '.php';
        
        // If the file exists, require it
        if (file_exists($class_file)) {
            require_once $class_file;
        }
    }
});

// Activation hook
register_activation_hook(__FILE__, ['VESChangeGetter\\Core\\Activator', 'activate']);

// Deactivation hook
register_deactivation_hook(__FILE__, ['VESChangeGetter\\Core\\Deactivator', 'deactivate']);

// Initialize the plugin
function run_ves_change_getter() {
    $plugin = new VESChangeGetter\Core\Main();
    $plugin->run();
}

run_ves_change_getter(); 