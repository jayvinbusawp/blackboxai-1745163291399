<?php
/**
 * Plugin Name: Roofer Directory
 * Description: A comprehensive directory plugin for roofing contractors with advanced location management and SEO.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: roofer-directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

final class Roofer_Directory {

    private static $instance = null;

    private function __construct() {
        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        // Initialize classes
        add_action( 'plugins_loaded', array( $this, 'init_classes' ) );

        // Activation and deactivation hooks
        register_activation_hook( __FILE__, array( 'Roofer_Directory_Activator', 'activate' ) );
        register_deactivation_hook( __FILE__, array( 'Roofer_Directory_Deactivator', 'deactivate' ) );
    }

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function define_constants() {
        define( 'ROOFER_DIR_PATH', plugin_dir_path( __FILE__ ) );
        define( 'ROOFER_DIR_URL', plugin_dir_url( __FILE__ ) );
        define( 'ROOFER_DIR_VERSION', '1.0.0' );
    }

    private function includes() {
        require_once ROOFER_DIR_PATH . 'includes/class-plugin-core.php';
        require_once ROOFER_DIR_PATH . 'includes/class-location-manager.php';
        require_once ROOFER_DIR_PATH . 'includes/class-seo-manager.php';
        require_once ROOFER_DIR_PATH . 'includes/class-template-loader.php';
        require_once ROOFER_DIR_PATH . 'includes/class-locations-table.php';
        require_once ROOFER_DIR_PATH . 'includes/class-ajax-handler.php';
        require_once ROOFER_DIR_PATH . 'includes/class-activator.php';
        require_once ROOFER_DIR_PATH . 'includes/class-deactivator.php';
    }

    public function init_classes() {
        // Initialize core classes
        Plugin_Core::instance();
        Location_Manager::instance();
        SEO_Manager::instance();
        Template_Loader::instance();
        Locations_Table::instance();
        AJAX_Handler::instance();
    }
}

Roofer_Directory::instance();
