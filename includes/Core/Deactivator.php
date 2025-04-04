<?php
namespace VESChangeGetter\Core;

/**
 * Fired during plugin deactivation
 */
class Deactivator {
    /**
     * Deactivates the plugin
     * Performs cleanup operations if needed
     */
    public static function deactivate() {
        // Optional: Remove database tables if you want to clean up data on deactivation
        // global $wpdb;
        // $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ves_change_getter_rates");
    }
} 