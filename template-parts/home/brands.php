<?php
/**
 * Home: Brands
 *
 * @package ksenonspb
 */

$featured = ksenon_home_get( 'featured_brands', array() );
$args     = array();

if ( is_array( $featured ) && $featured ) {
	$ids = array();
	foreach ( $featured as $item ) {
		if ( $item instanceof WP_Post ) {
			$ids[] = (int) $item->ID;
		} elseif ( is_numeric( $item ) ) {
			$ids[] = (int) $item;
		}
	}
	if ( $ids ) {
		$args['post__in'] = $ids;
		$args['orderby']  = 'post__in';
	}
}

$query = ksenon_query_brands( $args );
if ( ! $query->have_posts() ) {
	return;
}
?>
<section class="brands-section">
	<div class="brands-section__container _container">
		<?php if ( ksenon_home_get( 'tag' ) ) : ?>
			<span class="brands-section__tag tag"><?php echo esc_html( (string) ksenon_home_get( 'tag' ) ); ?></span>
		<?php endif; ?>
		<h2 class="brands-section__title title-md"><?php echo esc_html( (string) ksenon_home_get( 'title', __( 'Марки автомобилей', 'ksenonspb' ) ) ); ?></h2>
		<div class="brands-section__grid">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				get_template_part( 'template-parts/blocks/brand-card', null, array( 'post' => get_post() ) );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<a class="btn brands-section__more" href="<?php echo esc_url( ksenon_brands_archive_url() ); ?>"><?php esc_html_e( 'Все марки', 'ksenonspb' ); ?></a>
	</div>
</section>
