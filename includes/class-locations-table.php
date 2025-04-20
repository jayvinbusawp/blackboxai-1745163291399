<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Locations_Table {

    private static $instance = null;

    private $table_relations;
    private $table_logs;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->init_hooks();
        }
        return self::$instance;
    }

    private function init_hooks() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'Location Relationships', 'roofer-directory' ),
            __( 'Locations', 'roofer-directory' ),
            'manage_options',
            'roofer-locations',
            array( $this, 'render_locations_page' ),
            'dashicons-location-alt',
            26
        );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( $hook !== 'toplevel_page_roofer-locations' ) {
            return;
        }
        wp_enqueue_style( 'roofer-admin-style', ROOFER_DIR_URL . 'assets/css/admin-style.css', array(), ROOFER_DIR_VERSION );
        wp_enqueue_script( 'roofer-admin-js', ROOFER_DIR_URL . 'assets/js/admin-ajax.js', array( 'jquery' ), ROOFER_DIR_VERSION, true );
        wp_localize_script( 'roofer-admin-js', 'rooferAdmin', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'roofer_admin_nonce' ),
        ) );
    }

    public function render_locations_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'roofer-directory' ) );
        }

        global $wpdb;
        $this->table_relations = $wpdb->prefix . 'roofer_location_relations';
        $this->table_logs = $wpdb->prefix . 'roofer_logs';

        // Handle pagination, sorting, searching, filtering
        $paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $per_page = 50;
        $offset = ( $paged - 1 ) * $per_page;

        $orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'zip_code';
        $order = isset( $_GET['order'] ) && in_array( strtoupper( $_GET['order'] ), array( 'ASC', 'DESC' ) ) ? strtoupper( $_GET['order'] ) : 'ASC';

        $search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
        $filter_state = isset( $_GET['filter_state'] ) ? intval( $_GET['filter_state'] ) : 0;
        $filter_county = isset( $_GET['filter_county'] ) ? intval( $_GET['filter_county'] ) : 0;
        $filter_area = isset( $_GET['filter_area'] ) ? intval( $_GET['filter_area'] ) : 0;

        // Build where clause
        $where = 'WHERE 1=1';
        $params = array();

        if ( $filter_state ) {
            $where .= ' AND state_id = %d';
            $params[] = $filter_state;
        }
        if ( $filter_county ) {
            $where .= ' AND county_id = %d';
            $params[] = $filter_county;
        }
        if ( $filter_area ) {
            $where .= ' AND area_id = %d';
            $params[] = $filter_area;
        }

        if ( $search ) {
            // Search in zip code term name, area, county, state names
            $search_like = '%' . $wpdb->esc_like( $search ) . '%';
            $where .= " AND (
                (SELECT name FROM {$wpdb->terms} WHERE term_id = zip_code_id) LIKE %s
                OR (SELECT name FROM {$wpdb->terms} WHERE term_id = area_id) LIKE %s
                OR (SELECT name FROM {$wpdb->terms} WHERE term_id = county_id) LIKE %s
                OR (SELECT name FROM {$wpdb->terms} WHERE term_id = state_id) LIKE %s
            )";
            $params = array_merge( $params, array( $search_like, $search_like, $search_like, $search_like ) );
        }

        // Validate orderby column
        $valid_orderby = array( 'zip_code', 'area', 'county', 'state' );
        if ( ! in_array( $orderby, $valid_orderby ) ) {
            $orderby = 'zip_code';
        }

        // Map orderby to term name subquery
        $orderby_sql = " (SELECT name FROM {$wpdb->terms} WHERE term_id = {$orderby}_id) ";

        // Get total count
        $total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$this->table_relations} $where", $params ) );

        // Get rows
        $sql = "SELECT * FROM {$this->table_relations} $where ORDER BY $orderby_sql $order LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;
        $rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

        // Get taxonomy counts
        $states_count = wp_count_terms( 'state' );
        $counties_count = wp_count_terms( 'county' );
        $areas_count = wp_count_terms( 'area' );
        $zip_codes_count = wp_count_terms( 'zip_code' );

        // Get last 10 import logs
        $logs = $wpdb->get_results( "SELECT * FROM {$this->table_logs} ORDER BY timestamp DESC LIMIT 10", ARRAY_A );

        // Load view template
        include ROOFER_DIR_PATH . 'admin/partials/admin-dashboard.php';
    }
}
?>
