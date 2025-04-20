<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$state_slug = get_query_var( 'state' );
$county_slug = get_query_var( 'county' );
$area_slug = get_query_var( 'area' );
$zip_code = get_query_var( 'zip_code' );
$search_term = get_query_var( 'roofer_search' );

$args = array(
    'post_type' => 'roofer',
    'posts_per_page' => 10,
    'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
);

$tax_query = array( 'relation' => 'AND' );

if ( $state_slug ) {
    $tax_query[] = array(
        'taxonomy' => 'state',
        'field' => 'slug',
        'terms' => $state_slug,
    );
}

if ( $county_slug ) {
    $tax_query[] = array(
        'taxonomy' => 'county',
        'field' => 'slug',
        'terms' => $county_slug,
    );
}

if ( $area_slug ) {
    $tax_query[] = array(
        'taxonomy' => 'area',
        'field' => 'slug',
        'terms' => $area_slug,
    );
}

if ( $zip_code ) {
    $tax_query[] = array(
        'taxonomy' => 'zip_code',
        'field' => 'name',
        'terms' => $zip_code,
    );
}

if ( count( $tax_query ) > 1 ) {
    $args['tax_query'] = $tax_query;
}

if ( $search_term ) {
    $args['s'] = sanitize_text_field( $search_term );
}

$query = new WP_Query( $args );
?>

<div class="roofer-archive">
    <h1><?php post_type_archive_title(); ?></h1>

    <?php if ( $query->have_posts() ) : ?>
        <ul class="roofer-list">
            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                <li>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    <p><?php echo wp_trim_words( get_the_excerpt(), 20, '...' ); ?></p>
                </li>
            <?php endwhile; ?>
        </ul>

        <div class="pagination">
            <?php
            echo paginate_links( array(
                'total' => $query->max_num_pages,
                'current' => max( 1, get_query_var( 'paged' ) ),
            ) );
            ?>
        </div>

    <?php else : ?>
        <p><?php esc_html_e( 'No roofers found.', 'roofer-directory' ); ?></p>
    <?php endif; ?>

    <?php wp_reset_postdata(); ?>
</div>

<?php
get_footer();
?>
