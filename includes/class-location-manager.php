<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Location_Manager {

    private static $instance = null;

    private $table_relations;
    private $table_logs;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    private function init() {
        global $wpdb;
        $this->table_relations = $wpdb->prefix . 'roofer_location_relations';
        $this->table_logs = $wpdb->prefix . 'roofer_logs';

        // Hook for plugin activation to create tables
        register_activation_hook( ROOFER_DIR_PATH . 'roofer-directory.php', array( $this, 'create_tables' ) );

        // Hook to validate and assign taxonomies on post save
        add_action( 'save_post_roofer', array( $this, 'assign_taxonomies_on_save' ), 20, 3 );
    }

    public function create_tables() {
        global $wpdb;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $charset_collate = $wpdb->get_charset_collate();

        $sql_relations = "CREATE TABLE {$this->table_relations} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            zip_code_id BIGINT(20) UNSIGNED NOT NULL UNIQUE,
            area_id BIGINT(20) UNSIGNED NOT NULL,
            county_id BIGINT(20) UNSIGNED NOT NULL,
            state_id BIGINT(20) UNSIGNED NOT NULL,
            PRIMARY KEY  (id),
            KEY state_id (state_id),
            KEY county_id (county_id),
            KEY area_id (area_id),
            CONSTRAINT fk_zip_code FOREIGN KEY (zip_code_id) REFERENCES {$wpdb->terms}(term_id) ON DELETE CASCADE,
            CONSTRAINT fk_area FOREIGN KEY (area_id) REFERENCES {$wpdb->terms}(term_id) ON DELETE CASCADE,
            CONSTRAINT fk_county FOREIGN KEY (county_id) REFERENCES {$wpdb->terms}(term_id) ON DELETE CASCADE,
            CONSTRAINT fk_state FOREIGN KEY (state_id) REFERENCES {$wpdb->terms}(term_id) ON DELETE CASCADE
        ) $charset_collate;";

        $sql_logs = "CREATE TABLE {$this->table_logs} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            message TEXT NOT NULL,
            details TEXT NULL,
            PRIMARY KEY  (id),
            KEY timestamp (timestamp)
        ) $charset_collate;";

        dbDelta( $sql_relations );
        dbDelta( $sql_logs );
    }

    public function assign_taxonomies_on_save( $post_id, $post, $update ) {
        // Check autosave, permissions, nonce etc.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        if ( $post->post_type !== 'roofer' ) {
            return;
        }

        // Get area selected from post meta (assumed stored as area term ID)
        $area_id = get_post_meta( $post_id, '_roofer_area_id', true );
        $override_zip_code = get_post_meta( $post_id, '_roofer_override_zip_code', true );

        if ( $override_zip_code ) {
            // Assign taxonomies based on zip code override
            $this->assign_taxonomies_by_zip_code( $post_id, $override_zip_code );
        } elseif ( $area_id ) {
            // Assign taxonomies based on area
            $this->assign_taxonomies_by_area( $post_id, $area_id );
        } else {
            // No area or zip code override, clear taxonomies
            wp_set_object_terms( $post_id, array(), 'state' );
            wp_set_object_terms( $post_id, array(), 'county' );
            wp_set_object_terms( $post_id, array(), 'area' );
            wp_set_object_terms( $post_id, array(), 'zip_code' );
        }
    }

    public function assign_taxonomies_by_area( $post_id, $area_id ) {
        global $wpdb;

        // Validate area exists
        $area_term = get_term( $area_id, 'area' );
        if ( ! $area_term || is_wp_error( $area_term ) ) {
            $this->log_error( "Invalid area ID $area_id for post $post_id", 'assign_taxonomies_by_area' );
            return;
        }

        // Get location relation rows for this area
        $relations = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$this->table_relations} WHERE area_id = %d",
            $area_id
        ), ARRAY_A );

        if ( empty( $relations ) ) {
            $this->log_error( "No location relations found for area ID $area_id for post $post_id", 'assign_taxonomies_by_area' );
            return;
        }

        // Get unique state, county, zip_code IDs from relations
        $state_ids = array();
        $county_ids = array();
        $zip_code_ids = array();

        foreach ( $relations as $rel ) {
            $state_ids[] = intval( $rel['state_id'] );
            $county_ids[] = intval( $rel['county_id'] );
            $zip_code_ids[] = intval( $rel['zip_code_id'] );
        }

        $state_ids = array_unique( $state_ids );
        $county_ids = array_unique( $county_ids );
        $zip_code_ids = array_unique( $zip_code_ids );

        // Assign taxonomies in a single transaction
        wp_set_object_terms( $post_id, $state_ids, 'state' );
        wp_set_object_terms( $post_id, $county_ids, 'county' );
        wp_set_object_terms( $post_id, array( $area_id ), 'area' );
        wp_set_object_terms( $post_id, $zip_code_ids, 'zip_code' );
    }

    public function assign_taxonomies_by_zip_code( $post_id, $zip_code_term_id ) {
        global $wpdb;

        // Validate zip code term exists
        $zip_term = get_term( $zip_code_term_id, 'zip_code' );
        if ( ! $zip_term || is_wp_error( $zip_term ) ) {
            $this->log_error( "Invalid zip code term ID $zip_code_term_id for post $post_id", 'assign_taxonomies_by_zip_code' );
            return;
        }

        // Get location relation row for this zip code
        $relation = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$this->table_relations} WHERE zip_code_id = %d",
            $zip_code_term_id
        ), ARRAY_A );

        if ( ! $relation ) {
            $this->log_error( "No location relation found for zip code ID $zip_code_term_id for post $post_id", 'assign_taxonomies_by_zip_code' );
            return;
        }

        // Assign taxonomies
        wp_set_object_terms( $post_id, array( intval( $relation['state_id']) ), 'state' );
        wp_set_object_terms( $post_id, array( intval( $relation['county_id']) ), 'county' );
        wp_set_object_terms( $post_id, array( intval( $relation['area_id']) ), 'area' );
        wp_set_object_terms( $post_id, array( intval( $zip_code_term_id ) ), 'zip_code' );
    }

    public function log_error( $message, $context = '' ) {
        global $wpdb;
        $details = maybe_serialize( array( 'context' => $context ) );
        $wpdb->insert(
            $this->table_logs,
            array(
                'timestamp' => current_time( 'mysql' ),
                'message'   => $message,
                'details'   => $details,
            ),
            array(
                '%s',
                '%s',
                '%s',
            )
        );
    }

    // Additional methods for bulk CSV import, caching, validation, etc. would be added here.

    /**
     * Import locations from CSV file.
     * Expected CSV columns: zip_code, area, county, state
     * Processes in batches to avoid memory issues.
     *
     * @param string $csv_file_path
     * @return array Import result with counts and errors
     */
    public function import_locations_csv( $csv_file_path ) {
        global $wpdb;

        $handle = fopen( $csv_file_path, 'r' );
        if ( ! $handle ) {
            return array( 'success' => false, 'message' => 'Unable to open CSV file.' );
        }

        $header = fgetcsv( $handle );
        if ( ! $header || count( $header ) < 4 ) {
            fclose( $handle );
            return array( 'success' => false, 'message' => 'Invalid CSV header.' );
        }

        $expected_headers = array( 'zip_code', 'area', 'county', 'state' );
        $header = array_map( 'strtolower', $header );
        if ( array_diff( $expected_headers, $header ) ) {
            fclose( $handle );
            return array( 'success' => false, 'message' => 'CSV headers do not match expected columns.' );
        }

        $col_indexes = array_flip( $header );

        $batch_size = 200;
        $batch = array();
        $imported = 0;
        $errors = array();

        while ( ( $row = fgetcsv( $handle ) ) !== false ) {
            $batch[] = $row;
            if ( count( $batch ) >= $batch_size ) {
                $result = $this->process_location_batch( $batch, $col_indexes );
                $imported += $result['imported'];
                $errors = array_merge( $errors, $result['errors'] );
                $batch = array();
            }
        }

        // Process remaining batch
        if ( count( $batch ) > 0 ) {
            $result = $this->process_location_batch( $batch, $col_indexes );
            $imported += $result['imported'];
            $errors = array_merge( $errors, $result['errors'] );
        }

        fclose( $handle );

        // Log import summary
        $this->log_error( "Locations CSV import completed. Imported: $imported, Errors: " . count( $errors ), 'import_locations_csv' );

        return array(
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
        );
    }

    private function process_location_batch( $batch, $col_indexes ) {
        global $wpdb;

        $imported = 0;
        $errors = array();

        foreach ( $batch as $row ) {
            $zip_code = isset( $row[ $col_indexes['zip_code'] ] ) ? sanitize_text_field( $row[ $col_indexes['zip_code'] ] ) : '';
            $area_name = isset( $row[ $col_indexes['area'] ] ) ? sanitize_text_field( $row[ $col_indexes['area'] ] ) : '';
            $county_name = isset( $row[ $col_indexes['county'] ] ) ? sanitize_text_field( $row[ $col_indexes['county'] ] ) : '';
            $state_name = isset( $row[ $col_indexes['state'] ] ) ? sanitize_text_field( $row[ $col_indexes['state'] ] ) : '';

            if ( empty( $zip_code ) || empty( $area_name ) || empty( $county_name ) || empty( $state_name ) ) {
                $errors[] = "Missing required fields in row: " . implode( ',', $row );
                continue;
            }

            // Get or create terms
            $state_term = $this->get_or_create_term( $state_name, 'state' );
            $county_term = $this->get_or_create_term( $county_name, 'county', $state_term->term_id );
            $area_term = $this->get_or_create_term( $area_name, 'area', $county_term->term_id );
            $zip_term = $this->get_or_create_term( $zip_code, 'zip_code' );

            if ( is_wp_error( $state_term ) || is_wp_error( $county_term ) || is_wp_error( $area_term ) || is_wp_error( $zip_term ) ) {
                $errors[] = "Error creating terms for row: " . implode( ',', $row );
                continue;
            }

            // Insert or update location relation
            $existing = $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM {$this->table_relations} WHERE zip_code_id = %d",
                $zip_term->term_id
            ) );

            if ( $existing ) {
                // Update existing
                $updated = $wpdb->update(
                    $this->table_relations,
                    array(
                        'area_id' => $area_term->term_id,
                        'county_id' => $county_term->term_id,
                        'state_id' => $state_term->term_id,
                    ),
                    array( 'zip_code_id' => $zip_term->term_id ),
                    array( '%d', '%d', '%d' ),
                    array( '%d' )
                );
                if ( $updated === false ) {
                    $errors[] = "Failed to update location relation for zip code {$zip_code}";
                    continue;
                }
            } else {
                // Insert new
                $inserted = $wpdb->insert(
                    $this->table_relations,
                    array(
                        'zip_code_id' => $zip_term->term_id,
                        'area_id' => $area_term->term_id,
                        'county_id' => $county_term->term_id,
                        'state_id' => $state_term->term_id,
                    ),
                    array( '%d', '%d', '%d', '%d' )
                );
                if ( ! $inserted ) {
                    $errors[] = "Failed to insert location relation for zip code {$zip_code}";
                    continue;
                }
            }

            $imported++;
        }

        return array( 'imported' => $imported, 'errors' => $errors );
    }

    private function get_or_create_term( $name, $taxonomy, $parent = 0 ) {
        $term = get_term_by( 'name', $name, $taxonomy );
        if ( $term && ! is_wp_error( $term ) ) {
            return $term;
        }
        $args = array();
        if ( $parent && is_numeric( $parent ) ) {
            $args['parent'] = $parent;
        }
        $new_term = wp_insert_term( $name, $taxonomy, $args );
        if ( is_wp_error( $new_term ) ) {
            return $new_term;
        }
        return get_term( $new_term['term_id'], $taxonomy );
    }
}
?>
