<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Roofer_Directory_Deactivator {

    public static function deactivate() {
        // Flush rewrite rules to remove CPT and taxonomies URLs
        flush_rewrite_rules();
    }
}
?>
