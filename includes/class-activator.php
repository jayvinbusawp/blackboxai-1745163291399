<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Roofer_Directory_Activator {

    public static function activate() {
        // Create custom tables
        Location_Manager::instance()->create_tables();

        // Flush rewrite rules to register CPT and taxonomies URLs
        flush_rewrite_rules();
    }
}
?>
