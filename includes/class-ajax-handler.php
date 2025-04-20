<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AJAX_Handler {

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->init_hooks();
        }
        return self::$instance;
    }

    private function init_hooks() {
        add_action( 'wp_ajax_roofer_get_location_data', array( $this, 'ajax_get_location_data' ) );
        add_action( 'wp_ajax_roofer_import_csv', array( $this, 'ajax_import_csv' ) );
    }

    public function ajax_get_location_data() {
        check_ajax_referer( 'roofer_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
        }

        $area_id = isset( $_POST['area_id'] ) ? intval( $_POST['area_id'] ) : 0;
        if ( ! $area_id ) {
            wp_send_json_error( array( 'message' => 'Invalid area ID' ), 400 );
        }

        global $wpdb;
        $table_relations = $wpdb->prefix . 'roofer_location_relations';

        // Get zip codes, county, state for area
        $relations = $wpdb->get_results( $wpdb->prepare(
            "SELECT zip_code_id, county_id, state_id FROM {$table_relations} WHERE area_id = %d",
            $area_id
        ), ARRAY_A );

        if ( empty( $relations ) ) {
            wp_send_json_error( array( 'message' => 'No location data found for this area' ), 404 );
        }

        $zip_codes = array();
        $county_id = 0;
        $state_id = 0;

        foreach ( $relations as $rel ) {
            $zip_codes[] = $rel['zip_code_id'];
            $county_id = $rel['county_id'];
            $state_id = $rel['state_id'];
        }

        // Get term names
        $zip_code_names = array();
        foreach ( $zip_codes as $zip_id ) {
            $term = get_term( $zip_id, 'zip_code' );
            if ( $term && ! is_wp_error( $term ) ) {
                $zip_code_names[] = $term->name;
            }
        }

        $county_term = get_term( $county_id, 'county' );
        $state_term = get_term( $state_id, 'state' );

        $response = array(
            'zip_codes' => $zip_code_names,
            'county' => $county_term ? $county_term->name : 'Unknown',
            'state' => $state_term ? $state_term->name : 'Unknown',
        );

        wp_send_json_success( $response );
    }

    public function ajax_import_csv() {
        check_ajax_referer( 'roofer_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
        }

        if ( empty( $_FILES['csv_file'] ) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK ) {
            wp_send_json_error( array( 'message' => 'No CSV file uploaded or upload error.' ), 400 );
        }

        $file = $_FILES['csv_file'];

        // Validate file type (CSV)
        $file_type = wp_check_filetype( $file['name'] );
        if ( $file_type['ext'] !== 'csv' ) {
            wp_send_json_error( array( 'message' => 'Invalid file type. Please upload a CSV file.' ), 400 );
        }

        // Move uploaded file to a temporary location
        $upload_dir = wp_upload_dir();
        $target_path = trailingslashit( $upload_dir['basedir'] ) . 'roofer-imports/';
        if ( ! file_exists( $target_path ) ) {
            wp_mkdir_p( $target_path );
        }
        $target_file = $target_path . basename( $file['name'] );

        if ( ! move_uploaded_file( $file['tmp_name'], $target_file ) ) {
            wp_send_json_error( array( 'message' => 'Failed to move uploaded file.' ), 500 );
        }

        // Determine import type: locations or roofers
        $import_type = isset( $_POST['import_type'] ) ? sanitize_text_field( $_POST['import_type'] ) : '';

        if ( $import_type === 'locations' ) {
            $location_manager = Location_Manager::instance();
            $result = $location_manager->import_locations_csv( $target_file );
            if ( $result['success'] ) {
                wp_send_json_success( array(
                    'message' => "Locations imported: {$result['imported']}",
                    'errors' => $result['errors'],
                ) );
            } else {
                wp_send_json_error( array( 'message' => $result['message'] ) );
            }
        } elseif ( $import_type === 'roofers' ) {
            // Placeholder for roofer CSV import
            wp_send_json_error( array( 'message' => 'Roofer CSV import not implemented yet.' ), 501 );
        } else {
            wp_send_json_error( array( 'message' => 'Invalid import type.' ), 400 );
        }
    }
}
?>
