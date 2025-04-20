<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $post;

// Get saved meta values
$phone = get_post_meta( $post->ID, '_roofer_phone', true );
$email = get_post_meta( $post->ID, '_roofer_email', true );
$address = get_post_meta( $post->ID, '_roofer_address', true );
$services = get_post_meta( $post->ID, '_roofer_services', true );
$experience_years = get_post_meta( $post->ID, '_roofer_experience_years', true );
$total_score = get_post_meta( $post->ID, '_roofer_total_score', true );
$review_count = get_post_meta( $post->ID, '_roofer_review_count', true );
$price_range = get_post_meta( $post->ID, '_roofer_price_range', true );
$area_id = get_post_meta( $post->ID, '_roofer_area_id', true );
$override_zip_code = get_post_meta( $post->ID, '_roofer_override_zip_code', true );

// Get all areas for dropdown
$areas = get_terms( array(
    'taxonomy' => 'area',
    'hide_empty' => false,
) );

wp_nonce_field( 'roofer_metabox_nonce_action', 'roofer_metabox_nonce' );
?>

<div id="roofer-metabox" style="max-width: 600px;">
    <p>
        <label for="roofer_phone"><?php esc_html_e( 'Phone:', 'roofer-directory' ); ?></label><br>
        <input type="text" id="roofer_phone" name="roofer_phone" value="<?php echo esc_attr( $phone ); ?>" class="widefat" />
    </p>
    <p>
        <label for="roofer_email"><?php esc_html_e( 'Email:', 'roofer-directory' ); ?></label><br>
        <input type="email" id="roofer_email" name="roofer_email" value="<?php echo esc_attr( $email ); ?>" class="widefat" />
    </p>
    <p>
        <label for="roofer_address"><?php esc_html_e( 'Address:', 'roofer-directory' ); ?></label><br>
        <textarea id="roofer_address" name="roofer_address" class="widefat" rows="3"><?php echo esc_textarea( $address ); ?></textarea>
    </p>
    <p>
        <label for="roofer_services"><?php esc_html_e( 'Services (comma-separated):', 'roofer-directory' ); ?></label><br>
        <input type="text" id="roofer_services" name="roofer_services" value="<?php echo esc_attr( $services ); ?>" class="widefat" />
    </p>
    <p>
        <label for="roofer_experience_years"><?php esc_html_e( 'Experience Years:', 'roofer-directory' ); ?></label><br>
        <input type="number" id="roofer_experience_years" name="roofer_experience_years" value="<?php echo esc_attr( $experience_years ); ?>" class="widefat" min="0" />
    </p>
    <p>
        <label for="roofer_total_score"><?php esc_html_e( 'Total Score (e.g., 4.5):', 'roofer-directory' ); ?></label><br>
        <input type="number" step="0.1" id="roofer_total_score" name="roofer_total_score" value="<?php echo esc_attr( $total_score ); ?>" class="widefat" min="0" max="5" />
    </p>
    <p>
        <label for="roofer_review_count"><?php esc_html_e( 'Review Count:', 'roofer-directory' ); ?></label><br>
        <input type="number" id="roofer_review_count" name="roofer_review_count" value="<?php echo esc_attr( $review_count ); ?>" class="widefat" min="0" />
    </p>
    <p>
        <label for="roofer_price_range"><?php esc_html_e( 'Price Range (e.g., $$):', 'roofer-directory' ); ?></label><br>
        <input type="text" id="roofer_price_range" name="roofer_price_range" value="<?php echo esc_attr( $price_range ); ?>" class="widefat" />
    </p>
    <p>
        <label for="roofer_area_id"><?php esc_html_e( 'Select Area:', 'roofer-directory' ); ?></label><br>
        <select id="roofer_area_id" name="roofer_area_id" class="widefat">
            <option value=""><?php esc_html_e( '-- Select Area --', 'roofer-directory' ); ?></option>
            <?php foreach ( $areas as $area ) : ?>
                <option value="<?php echo esc_attr( $area->term_id ); ?>" <?php selected( $area_id, $area->term_id ); ?>>
                    <?php echo esc_html( $area->name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label><?php esc_html_e( 'Associated Zip Codes:', 'roofer-directory' ); ?></label><br>
        <textarea id="roofer_zip_codes" readonly rows="3" class="widefat"><?php echo esc_textarea( '' ); ?></textarea>
    </p>
    <p>
        <label><?php esc_html_e( 'County:', 'roofer-directory' ); ?></label><br>
        <input type="text" id="roofer_county" readonly class="widefat" value="" />
    </p>
    <p>
        <label><?php esc_html_e( 'State:', 'roofer-directory' ); ?></label><br>
        <input type="text" id="roofer_state" readonly class="widefat" value="" />
    </p>
    <p>
        <label for="roofer_override_zip_code"><?php esc_html_e( 'Override Zip Code (optional):', 'roofer-directory' ); ?></label><br>
        <input type="text" id="roofer_override_zip_code" name="roofer_override_zip_code" value="<?php echo esc_attr( $override_zip_code ); ?>" class="widefat" />
    </p>
</div>
