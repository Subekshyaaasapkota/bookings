<?php
/**
 * Plugin Name: SV Bookings - Modern Booking & Form Builder
 * Plugin URI: https://wordpress.org/plugins/svbookings
 * Description: Complete booking management system with visual form builder for both admin and client interfaces, rich custom fields, booking slots, email notifications, and admin management.
 * Version: 1.0.0
 * Author: SV Plugins
 * Author URI: https://example.com
 * Text Domain: svbookings
 * Domain Path: /languages
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('SV_BOOKINGS_VERSION', '1.0.0');
define('SV_BOOKINGS_PLUGIN_FILE', __FILE__);
define('SV_BOOKINGS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SV_BOOKINGS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Core includes
require_once SV_BOOKINGS_PLUGIN_DIR . 'includes/class-sv-database.php';
require_once SV_BOOKINGS_PLUGIN_DIR . 'includes/class-sv-ajax.php';
require_once SV_BOOKINGS_PLUGIN_DIR . 'includes/class-sv-admin.php';
require_once SV_BOOKINGS_PLUGIN_DIR . 'includes/class-sv-frontend.php';

/**
 * Main Plugin Class
 */
class SV_Bookings_Plugin {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        register_activation_hook(SV_BOOKINGS_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(SV_BOOKINGS_PLUGIN_FILE, array($this, 'deactivate'));

        add_action('plugins_loaded', array($this, 'init'));
    }

    public function activate() {
        SV_Bookings_Database::create_tables();
        SV_Bookings_Database::insert_sample_data();
        flush_rewrite_rules();
    }

    public function deactivate() {
        flush_rewrite_rules();
    }

    public function init() {
        load_plugin_textdomain('svbookings', false, dirname(plugin_basename(__FILE__)) . '/languages');

        SV_Bookings_Database::init();
        SV_Bookings_Ajax::init();
        SV_Bookings_Admin::init();
        SV_Bookings_Frontend::init();
    }
}

// Instantiate the plugin
SV_Bookings_Plugin::get_instance();
