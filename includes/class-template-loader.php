<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Template_Loader {

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->init_hooks();
        }
        return self::$instance;
    }

    private function init_hooks() {
        add_filter( 'template_include', array( $this, 'load_templates' ) );
    }

    public function load_templates( $template ) {
        if ( is_singular( 'roofer' ) ) {
            $custom_template = ROOFER_DIR_PATH . 'templates/single-roofer.php';
            if ( file_exists( $custom_template ) ) {
                return $custom_template;
            }
        }

        if ( is_post_type_archive( 'roofer' ) || is_tax( array( 'state', 'county', 'area', 'zip_code' ) ) ) {
            $custom_template = ROOFER_DIR_PATH . 'templates/archive-roofer.php';
            if ( file_exists( $custom_template ) ) {
                return $custom_template;
            }
        }

        return $template;
    }
}
?>
