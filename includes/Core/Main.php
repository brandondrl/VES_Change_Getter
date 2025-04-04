<?php
namespace VESChangeGetter\Core;

use VESChangeGetter\Admin\AdminPage;
use VESChangeGetter\API\APIEndpoint;
use VESChangeGetter\Models\RatesModel;

/**
 * The core plugin class
 */
class Main {
    /**
     * The loader that's responsible for maintaining and registering all hooks
     */
    protected $loader;

    /**
     * Define the core functionality of the plugin
     */
    public function __construct() {
        $this->loader = new Loader();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_api_hooks();
    }

    /**
     * Register all of the hooks related to the admin area
     */
    private function define_admin_hooks() {
        $admin = new AdminPage();
        
        $this->loader->add_action('admin_menu', $admin, 'register_admin_menu');
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_scripts');
        
        // Register a custom admin AJAX action to fetch data
        $this->loader->add_action('wp_ajax_fetch_rates_data', $admin, 'fetch_rates_data');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     */
    private function define_public_hooks() {
        // For public-facing functionality if needed
    }

    /**
     * Register all of the hooks related to the API
     */
    private function define_api_hooks() {
        $api = new APIEndpoint();
        
        // Register REST API routes
        $this->loader->add_action('rest_api_init', $api, 'register_routes');
    }

    /**
     * Run the loader to execute all of the hooks
     */
    public function run() {
        $this->loader->run();
    }
} 