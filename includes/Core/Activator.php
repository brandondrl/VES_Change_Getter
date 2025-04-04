<?php
namespace VESChangeGetter\Core;

/**
 * Fired during plugin activation
 */
class Activator {
    /**
     * Activates the plugin
     * Creates the necessary database tables
     */
    public static function activate() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ves_change_getter_rates';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            json longtext NOT NULL,
            fecha datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            update_date datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
} 