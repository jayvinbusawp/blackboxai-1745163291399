<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Plugin_Core {

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->init_hooks();
        }
        return self::$instance;
    }

    private function init_hooks() {
        add_action( 'init', array( $this, 'register_post_type_and_taxonomies' ), 0 );
        add_action( 'init', array( $this, 'add_rewrite_rules' ), 20 );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
        add_action( 'template_redirect', array( $this, 'handle_redirects' ) );
    }

    public function register_post_type_and_taxonomies() {
        // Register Custom Post Type: roofer
        $labels = array(
            'name'                  => _x( 'Roofers', 'Post Type General Name', 'roofer-directory' ),
            'singular_name'         => _x( 'Roofer', 'Post Type Singular Name', 'roofer-directory' ),
            'menu_name'             => __( 'Roofers', 'roofer-directory' ),
            'name_admin_bar'        => __( 'Roofer', 'roofer-directory' ),
            'archives'              => __( 'Roofer Archives', 'roofer-directory' ),
            'attributes'            => __( 'Roofer Attributes', 'roofer-directory' ),
            'parent_item_colon'     => __( 'Parent Roofer:', 'roofer-directory' ),
            'all_items'             => __( 'All Roofers', 'roofer-directory' ),
            'add_new_item'          => __( 'Add New Roofer', 'roofer-directory' ),
            'add_new'               => __( 'Add New', 'roofer-directory' ),
            'new_item'              => __( 'New Roofer', 'roofer-directory' ),
            'edit_item'             => __( 'Edit Roofer', 'roofer-directory' ),
            'update_item'           => __( 'Update Roofer', 'roofer-directory' ),
            'view_item'             => __( 'View Roofer', 'roofer-directory' ),
            'view_items'            => __( 'View Roofers', 'roofer-directory' ),
            'search_items'          => __( 'Search Roofer', 'roofer-directory' ),
            'not_found'             => __( 'Not found', 'roofer-directory' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'roofer-directory' ),
            'featured_image'        => __( 'Featured Image', 'roofer-directory' ),
            'set_featured_image'    => __( 'Set featured image', 'roofer-directory' ),
            'remove_featured_image' => __( 'Remove featured image', 'roofer-directory' ),
            'use_featured_image'    => __( 'Use as featured image', 'roofer-directory' ),
            'insert_into_item'      => __( 'Insert into roofer', 'roofer-directory' ),
            'uploaded_to_this_item' => __( 'Uploaded to this roofer', 'roofer-directory' ),
            'items_list'            => __( 'Roofers list', 'roofer-directory' ),
            'items_list_navigation' => __( 'Roofers list navigation', 'roofer-directory' ),
            'filter_items_list'     => __( 'Filter roofers list', 'roofer-directory' ),
        );
        $args = array(
            'label'                 => __( 'Roofer', 'roofer-directory' ),
            'description'           => __( 'Roofing contractors directory', 'roofer-directory' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
            'taxonomies'            => array(),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-building',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'rewrite'               => array( 'slug' => 'roofer', 'with_front' => false ),
            'show_in_rest'          => true,
        );
        register_post_type( 'roofer', $args );

        // Register Taxonomies

        // State (hierarchical, top-level)
        $labels_state = array(
            'name'              => _x( 'States', 'taxonomy general name', 'roofer-directory' ),
            'singular_name'     => _x( 'State', 'taxonomy singular name', 'roofer-directory' ),
            'search_items'      => __( 'Search States', 'roofer-directory' ),
            'all_items'         => __( 'All States', 'roofer-directory' ),
            'parent_item'       => null,
            'parent_item_colon' => null,
            'edit_item'         => __( 'Edit State', 'roofer-directory' ),
            'update_item'       => __( 'Update State', 'roofer-directory' ),
            'add_new_item'      => __( 'Add New State', 'roofer-directory' ),
            'new_item_name'     => __( 'New State Name', 'roofer-directory' ),
            'menu_name'         => __( 'States', 'roofer-directory' ),
        );
        register_taxonomy( 'state', array( 'roofer' ), array(
            'hierarchical'      => true,
            'labels'            => $labels_state,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'state' ),
            'show_in_rest'      => true,
        ) );

        // County (hierarchical, child of state)
        $labels_county = array(
            'name'              => _x( 'Counties', 'taxonomy general name', 'roofer-directory' ),
            'singular_name'     => _x( 'County', 'taxonomy singular name', 'roofer-directory' ),
            'search_items'      => __( 'Search Counties', 'roofer-directory' ),
            'all_items'         => __( 'All Counties', 'roofer-directory' ),
            'parent_item'       => __( 'Parent State', 'roofer-directory' ),
            'parent_item_colon' => __( 'Parent State:', 'roofer-directory' ),
            'edit_item'         => __( 'Edit County', 'roofer-directory' ),
            'update_item'       => __( 'Update County', 'roofer-directory' ),
            'add_new_item'      => __( 'Add New County', 'roofer-directory' ),
            'new_item_name'     => __( 'New County Name', 'roofer-directory' ),
            'menu_name'         => __( 'Counties', 'roofer-directory' ),
        );
        register_taxonomy( 'county', array( 'roofer' ), array(
            'hierarchical'      => true,
            'labels'            => $labels_county,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'county' ),
            'show_in_rest'      => true,
        ) );

        // Area (hierarchical, child of county)
        $labels_area = array(
            'name'              => _x( 'Areas', 'taxonomy general name', 'roofer-directory' ),
            'singular_name'     => _x( 'Area', 'taxonomy singular name', 'roofer-directory' ),
            'search_items'      => __( 'Search Areas', 'roofer-directory' ),
            'all_items'         => __( 'All Areas', 'roofer-directory' ),
            'parent_item'       => __( 'Parent County', 'roofer-directory' ),
            'parent_item_colon' => __( 'Parent County:', 'roofer-directory' ),
            'edit_item'         => __( 'Edit Area', 'roofer-directory' ),
            'update_item'       => __( 'Update Area', 'roofer-directory' ),
            'add_new_item'      => __( 'Add New Area', 'roofer-directory' ),
            'new_item_name'     => __( 'New Area Name', 'roofer-directory' ),
            'menu_name'         => __( 'Areas', 'roofer-directory' ),
        );
        register_taxonomy( 'area', array( 'roofer' ), array(
            'hierarchical'      => true,
            'labels'            => $labels_area,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'area' ),
            'show_in_rest'      => true,
        ) );

        // Zip Code (non-hierarchical)
        $labels_zip = array(
            'name'                       => _x( 'Zip Codes', 'taxonomy general name', 'roofer-directory' ),
            'singular_name'              => _x( 'Zip Code', 'taxonomy singular name', 'roofer-directory' ),
            'search_items'               => __( 'Search Zip Codes', 'roofer-directory' ),
            'popular_items'              => __( 'Popular Zip Codes', 'roofer-directory' ),
            'all_items'                  => __( 'All Zip Codes', 'roofer-directory' ),
            'edit_item'                  => __( 'Edit Zip Code', 'roofer-directory' ),
            'update_item'                => __( 'Update Zip Code', 'roofer-directory' ),
            'add_new_item'               => __( 'Add New Zip Code', 'roofer-directory' ),
            'new_item_name'              => __( 'New Zip Code Name', 'roofer-directory' ),
            'separate_items_with_commas' => __( 'Separate zip codes with commas', 'roofer-directory' ),
            'add_or_remove_items'        => __( 'Add or remove zip codes', 'roofer-directory' ),
            'choose_from_most_used'      => __( 'Choose from the most used zip codes', 'roofer-directory' ),
            'not_found'                  => __( 'No zip codes found.', 'roofer-directory' ),
            'menu_name'                  => __( 'Zip Codes', 'roofer-directory' ),
        );
        register_taxonomy( 'zip_code', array( 'roofer' ), array(
            'hierarchical'          => false,
            'labels'                => $labels_zip,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var'             => true,
            'rewrite'               => array( 'slug' => 'zip' ),
            'show_in_rest'          => true,
        ) );
    }

    public function add_rewrite_rules() {
        // Add rewrite rules for SEO-friendly URLs

        // /roofers/[state-slug]/
        add_rewrite_rule(
            '^roofers/([^/]+)/?$',
            'index.php?post_type=roofer&state=$matches[1]',
            'top'
        );

        // /roofers/[state-slug]/[county-slug]/
        add_rewrite_rule(
            '^roofers/([^/]+)/([^/]+)/?$',
            'index.php?post_type=roofer&state=$matches[1]&county=$matches[2]',
            'top'
        );

        // /roofers/[state-slug]/[county-slug]/[area-slug]/
        add_rewrite_rule(
            '^roofers/([^/]+)/([^/]+)/([^/]+)/?$',
            'index.php?post_type=roofer&state=$matches[1]&county=$matches[2]&area=$matches[3]',
            'top'
        );

        // /roofers/zip/[zip-code]/
        add_rewrite_rule(
            '^roofers/zip/([0-9]+)/?$',
            'index.php?post_type=roofer&zip_code=$matches[1]',
            'top'
        );

        // /roofers/search/[search-term]/
        add_rewrite_rule(
            '^roofers/search/([^/]+)/?$',
            'index.php?post_type=roofer&roofer_search=$matches[1]',
            'top'
        );

        // /roofer/[state-slug]/[county-slug]/[area-slug]/[roofer-slug]/
        add_rewrite_rule(
            '^roofer/([^/]+)/([^/]+)/([^/]+)/([^/]+)/?$',
            'index.php?post_type=roofer&name=$matches[4]&state=$matches[1]&county=$matches[2]&area=$matches[3]',
            'top'
        );
    }

    public function add_query_vars( $vars ) {
        $vars[] = 'state';
        $vars[] = 'county';
        $vars[] = 'area';
        $vars[] = 'zip_code';
        $vars[] = 'roofer_search';
        return $vars;
    }

    public function handle_redirects() {
        // Implement fallback redirects if terms are missing in URL

        if ( is_singular( 'roofer' ) ) {
            global $post;
            $state = get_query_var( 'state' );
            $county = get_query_var( 'county' );
            $area = get_query_var( 'area' );

            // Get assigned terms
            $states = wp_get_post_terms( $post->ID, 'state' );
            $counties = wp_get_post_terms( $post->ID, 'county' );
            $areas = wp_get_post_terms( $post->ID, 'area' );

            // Validate URL terms against assigned terms
            $redirect = false;
            $redirect_url = '';

            if ( $state ) {
                $state_slugs = wp_list_pluck( $states, 'slug' );
                if ( ! in_array( $state, $state_slugs ) ) {
                    // Redirect to first assigned state or archive
                    if ( ! empty( $state_slugs ) ) {
                        $redirect = true;
                        $redirect_url = home_url( '/roofers/' . $state_slugs[0] . '/' );
                    } else {
                        $redirect = true;
                        $redirect_url = get_post_type_archive_link( 'roofer' );
                    }
                }
            }

            if ( $county && ! $redirect ) {
                $county_slugs = wp_list_pluck( $counties, 'slug' );
                if ( ! in_array( $county, $county_slugs ) ) {
                    if ( ! empty( $county_slugs ) ) {
                        $redirect = true;
                        $redirect_url = home_url( '/roofers/' . $state . '/' . $county_slugs[0] . '/' );
                    } else {
                        $redirect = true;
                        $redirect_url = home_url( '/roofers/' . $state . '/' );
                    }
                }
            }

            if ( $area && ! $redirect ) {
                $area_slugs = wp_list_pluck( $areas, 'slug' );
                if ( ! in_array( $area, $area_slugs ) ) {
                    if ( ! empty( $area_slugs ) ) {
                        $redirect = true;
                        $redirect_url = home_url( '/roofers/' . $state . '/' . $county . '/' . $area_slugs[0] . '/' );
                    } else {
                        $redirect = true;
                        $redirect_url = home_url( '/roofers/' . $state . '/' . $county . '/' );
                    }
                }
            }

            if ( $redirect ) {
                wp_redirect( $redirect_url, 301 );
                exit;
            }
        }
    }
}
?>
