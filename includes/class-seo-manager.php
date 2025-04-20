<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SEO_Manager {

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->init_hooks();
        }
        return self::$instance;
    }

    private function init_hooks() {
        // Add SEO meta tags and structured data hooks
        add_action( 'wp_head', array( $this, 'add_meta_tags' ) );
        add_action( 'wp_head', array( $this, 'add_structured_data' ) );
    }

    public function add_meta_tags() {
        if ( is_singular( 'roofer' ) ) {
            global $post;
            $title = get_the_title( $post );
            $description = get_the_excerpt( $post );
            $description = wp_strip_all_tags( $description );
            echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
            // Additional meta tags can be added here
        }
    }

    public function add_structured_data() {
        if ( is_singular( 'roofer' ) ) {
            global $post;
            $phone = get_post_meta( $post->ID, '_roofer_phone', true );
            $email = get_post_meta( $post->ID, '_roofer_email', true );
            $address = get_post_meta( $post->ID, '_roofer_address', true );
            $services = get_post_meta( $post->ID, '_roofer_services', true );
            $experience = get_post_meta( $post->ID, '_roofer_experience_years', true );
            $total_score = get_post_meta( $post->ID, '_roofer_total_score', true );
            $review_count = get_post_meta( $post->ID, '_roofer_review_count', true );
            $price_range = get_post_meta( $post->ID, '_roofer_price_range', true );

            $structured_data = array(
                '@context' => 'https://schema.org',
                '@type' => 'LocalBusiness',
                'name' => get_the_title( $post ),
                'telephone' => $phone,
                'email' => $email,
                'address' => $address,
                'description' => get_the_excerpt( $post ),
                'priceRange' => $price_range,
                'aggregateRating' => array(
                    '@type' => 'AggregateRating',
                    'ratingValue' => $total_score,
                    'reviewCount' => $review_count,
                ),
                'areaServed' => $services,
                'foundingDate' => '', // Could be added if available
                'url' => get_permalink( $post ),
                'experience' => $experience,
            );

            echo '<script type="application/ld+json">' . wp_json_encode( $structured_data ) . '</script>' . "\n";
        }
    }

    // Additional SEO related methods can be added here, e.g., sitemap generation, canonical URLs, etc.
}
?>
