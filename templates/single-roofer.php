<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        $phone = get_post_meta( get_the_ID(), '_roofer_phone', true );
        $email = get_post_meta( get_the_ID(), '_roofer_email', true );
        $address = get_post_meta( get_the_ID(), '_roofer_address', true );
        $services = get_post_meta( get_the_ID(), '_roofer_services', true );
        $experience_years = get_post_meta( get_the_ID(), '_roofer_experience_years', true );
        $total_score = get_post_meta( get_the_ID(), '_roofer_total_score', true );
        $review_count = get_post_meta( get_the_ID(), '_roofer_review_count', true );
        $price_range = get_post_meta( get_the_ID(), '_roofer_price_range', true );

        $states = wp_get_post_terms( get_the_ID(), 'state' );
        $counties = wp_get_post_terms( get_the_ID(), 'county' );
        $areas = wp_get_post_terms( get_the_ID(), 'area' );
        $zip_codes = wp_get_post_terms( get_the_ID(), 'zip_code' );
        ?>
        <div class="roofer-single">
            <h1><?php the_title(); ?></h1>
            <div class="roofer-meta">
                <p><strong><?php esc_html_e( 'Phone:', 'roofer-directory' ); ?></strong> <?php echo esc_html( $phone ); ?></p>
                <p><strong><?php esc_html_e( 'Email:', 'roofer-directory' ); ?></strong> <?php echo esc_html( $email ); ?></p>
                <p><strong><?php esc_html_e( 'Address:', 'roofer-directory' ); ?></strong> <?php echo esc_html( $address ); ?></p>
                <p><strong><?php esc_html_e( 'Services:', 'roofer-directory' ); ?></strong> <?php echo esc_html( $services ); ?></p>
                <p><strong><?php esc_html_e( 'Experience Years:', 'roofer-directory' ); ?></strong> <?php echo esc_html( $experience_years ); ?></p>
                <p><strong><?php esc_html_e( 'Total Score:', 'roofer-directory' ); ?></strong> <?php echo esc_html( $total_score ); ?></p>
                <p><strong><?php esc_html_e( 'Review Count:', 'roofer-directory' ); ?></strong> <?php echo esc_html( $review_count ); ?></p>
                <p><strong><?php esc_html_e( 'Price Range:', 'roofer-directory' ); ?></strong> <?php echo esc_html( $price_range ); ?></p>
                <p><strong><?php esc_html_e( 'State:', 'roofer-directory' ); ?></strong> <?php echo esc_html( ! empty( $states ) ? $states[0]->name : '' ); ?></p>
                <p><strong><?php esc_html_e( 'County:', 'roofer-directory' ); ?></strong> <?php echo esc_html( ! empty( $counties ) ? $counties[0]->name : '' ); ?></p>
                <p><strong><?php esc_html_e( 'Area:', 'roofer-directory' ); ?></strong> <?php echo esc_html( ! empty( $areas ) ? $areas[0]->name : '' ); ?></p>
                <p><strong><?php esc_html_e( 'Zip Codes:', 'roofer-directory' ); ?></strong> <?php echo esc_html( implode( ', ', wp_list_pluck( $zip_codes, 'name' ) ) ); ?></p>
            </div>
            <div class="roofer-content">
                <?php the_content(); ?>
            </div>
        </div>
        <?php
    endwhile;
endif;

get_footer();
?>
