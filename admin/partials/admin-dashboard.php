<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Location Relationships', 'roofer-directory' ); ?></h1>

    <form method="get" action="">
        <input type="hidden" name="page" value="roofer-locations" />
        <p class="search-box">
            <label class="screen-reader-text" for="location-search-input"><?php esc_html_e( 'Search Locations:', 'roofer-directory' ); ?></label>
            <input type="search" id="location-search-input" name="s" value="<?php echo esc_attr( isset( $_GET['s'] ) ? $_GET['s'] : '' ); ?>" />
            <input type="submit" id="search-submit" class="button" value="<?php esc_attr_e( 'Search Locations', 'roofer-directory' ); ?>" />
        </p>

        <div class="tablenav top">
            <div class="alignleft actions">
                <select name="filter_state" id="filter_state">
                    <option value="0"><?php esc_html_e( 'All States', 'roofer-directory' ); ?></option>
                    <?php
                    $states = get_terms( array( 'taxonomy' => 'state', 'hide_empty' => false ) );
                    foreach ( $states as $state ) {
                        printf(
                            '<option value="%d" %s>%s</option>',
                            esc_attr( $state->term_id ),
                            selected( isset( $_GET['filter_state'] ) ? intval( $_GET['filter_state'] ) : 0, $state->term_id, false ),
                            esc_html( $state->name )
                        );
                    }
                    ?>
                </select>

                <select name="filter_county" id="filter_county">
                    <option value="0"><?php esc_html_e( 'All Counties', 'roofer-directory' ); ?></option>
                    <?php
                    $counties = get_terms( array( 'taxonomy' => 'county', 'hide_empty' => false ) );
                    foreach ( $counties as $county ) {
                        printf(
                            '<option value="%d" %s>%s</option>',
                            esc_attr( $county->term_id ),
                            selected( isset( $_GET['filter_county'] ) ? intval( $_GET['filter_county'] ) : 0, $county->term_id, false ),
                            esc_html( $county->name )
                        );
                    }
                    ?>
                </select>

                <select name="filter_area" id="filter_area">
                    <option value="0"><?php esc_html_e( 'All Areas', 'roofer-directory' ); ?></option>
                    <?php
                    $areas = get_terms( array( 'taxonomy' => 'area', 'hide_empty' => false ) );
                    foreach ( $areas as $area ) {
                        printf(
                            '<option value="%d" %s>%s</option>',
                            esc_attr( $area->term_id ),
                            selected( isset( $_GET['filter_area'] ) ? intval( $_GET['filter_area'] ) : 0, $area->term_id, false ),
                            esc_html( $area->name )
                        );
                    }
                    ?>
                </select>

                <input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'roofer-directory' ); ?>" />
            </div>

            <div class="tablenav-pages">
                <?php
                $total_pages = ceil( $total / $per_page );
                $current_page = max( 1, $paged );

                $base_url = remove_query_arg( array( 'paged' ), $_SERVER['REQUEST_URI'] );
                if ( strpos( $base_url, '?' ) === false ) {
                    $base_url .= '?';
                } else {
                    $base_url .= '&';
                }

                if ( $total_pages > 1 ) {
                    echo '<span class="pagination-links">';
                    if ( $current_page > 1 ) {
                        printf(
                            '<a class="first-page button" href="%s">%s</a>',
                            esc_url( $base_url . 'paged=1' ),
                            esc_html__( '« First', 'roofer-directory' )
                        );
                        printf(
                            '<a class="prev-page button" href="%s">%s</a>',
                            esc_url( $base_url . 'paged=' . ( $current_page - 1 ) ),
                            esc_html__( '‹ Prev', 'roofer-directory' )
                        );
                    }
                    printf(
                        '<span class="paging-input">%d / %d</span>',
                        esc_html( $current_page ),
                        esc_html( $total_pages )
                    );
                    if ( $current_page < $total_pages ) {
                        printf(
                            '<a class="next-page button" href="%s">%s</a>',
                            esc_url( $base_url . 'paged=' . ( $current_page + 1 ) ),
                            esc_html__( 'Next ›', 'roofer-directory' )
                        );
                        printf(
                            '<a class="last-page button" href="%s">%s</a>',
                            esc_url( $base_url . 'paged=' . $total_pages ),
                            esc_html__( 'Last »', 'roofer-directory' )
                        );
                    }
                    echo '</span>';
                }
                ?>
            </div>
        </div>
    </form>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><a href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'zip_code', 'order' => ( $orderby === 'zip_code' && $order === 'ASC' ) ? 'DESC' : 'ASC' ) ) ); ?>"><?php esc_html_e( 'Zip Code', 'roofer-directory' ); ?></a></th>
                <th><a href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'area', 'order' => ( $orderby === 'area' && $order === 'ASC' ) ? 'DESC' : 'ASC' ) ) ); ?>"><?php esc_html_e( 'Area', 'roofer-directory' ); ?></a></th>
                <th><a href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'county', 'order' => ( $orderby === 'county' && $order === 'ASC' ) ? 'DESC' : 'ASC' ) ) ); ?>"><?php esc_html_e( 'County', 'roofer-directory' ); ?></a></th>
                <th><a href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'state', 'order' => ( $orderby === 'state' && $order === 'ASC' ) ? 'DESC' : 'ASC' ) ) ); ?>"><?php esc_html_e( 'State', 'roofer-directory' ); ?></a></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $rows ) ) : ?>
                <?php foreach ( $rows as $row ) : ?>
                    <tr>
                        <td><?php
                            $zip_term = get_term( $row['zip_code_id'], 'zip_code' );
                            echo $zip_term && ! is_wp_error( $zip_term ) ? esc_html( $zip_term->name ) : esc_html__( 'Unknown', 'roofer-directory' );
                        ?></td>
                        <td><?php
                            $area_term = get_term( $row['area_id'], 'area' );
                            echo $area_term && ! is_wp_error( $area_term ) ? esc_html( $area_term->name ) : esc_html__( 'Unknown', 'roofer-directory' );
                        ?></td>
                        <td><?php
                            $county_term = get_term( $row['county_id'], 'county' );
                            echo $county_term && ! is_wp_error( $county_term ) ? esc_html( $county_term->name ) : esc_html__( 'Unknown', 'roofer-directory' );
                        ?></td>
                        <td><?php
                            $state_term = get_term( $row['state_id'], 'state' );
                            echo $state_term && ! is_wp_error( $state_term ) ? esc_html( $state_term->name ) : esc_html__( 'Unknown', 'roofer-directory' );
                        ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="4"><?php esc_html_e( 'No location relationships found.', 'roofer-directory' ); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2><?php esc_html_e( 'Taxonomy Counts', 'roofer-directory' ); ?></h2>
    <ul>
        <li><?php printf( esc_html__( 'States: %d', 'roofer-directory' ), $states_count ); ?></li>
        <li><?php printf( esc_html__( 'Counties: %d', 'roofer-directory' ), $counties_count ); ?></li>
        <li><?php printf( esc_html__( 'Areas: %d', 'roofer-directory' ), $areas_count ); ?></li>
        <li><?php printf( esc_html__( 'Zip Codes: %d', 'roofer-directory' ), $zip_codes_count ); ?></li>
    </ul>

    <h2><?php esc_html_e( 'Last 10 Import Logs', 'roofer-directory' ); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Timestamp', 'roofer-directory' ); ?></th>
                <th><?php esc_html_e( 'Message', 'roofer-directory' ); ?></th>
                <th><?php esc_html_e( 'Details', 'roofer-directory' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $logs ) ) : ?>
                <?php foreach ( $logs as $log ) : ?>
                    <tr>
                        <td><?php echo esc_html( $log['timestamp'] ); ?></td>
                        <td><?php echo esc_html( $log['message'] ); ?></td>
                        <td><?php echo esc_html( maybe_unserialize( $log['details'] ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="3"><?php esc_html_e( 'No import logs found.', 'roofer-directory' ); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
