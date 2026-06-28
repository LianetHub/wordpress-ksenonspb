<?php
/**
 * Home: Services teaser
 *
 * @package ksenonspb
 */

$featured = ksenon_home_get( 'featured_services', array() );
$limit    = (int) ksenon_home_get( 'limit', 6 );
$args     = array( 'posts_per_page' => $limit > 0 ? $limit : -1 );

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

$query = ksenon_query_services( $args );
if ( ! $query->have_posts() ) {
	return;
}
?>
<section class="services-teaser">
	<div class="services-teaser__container _container">
		<?php if ( ksenon_home_get( 'tag' ) ) : ?>
			<span class="services-teaser__tag tag"><?php echo esc_html( (string) ksenon_home_get( 'tag' ) ); ?></span>
		<?php endif; ?>
		<h2 class="services-teaser__title title-md"><?php echo esc_html( (string) ksenon_home_get( 'title', __( 'Наши услуги', 'ksenonspb' ) ) ); ?></h2>
		<div class="services-teaser__grid">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				get_template_part( 'template-parts/blocks/service-card', null, array( 'post' => get_post() ) );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<a class="btn services-teaser__more" href="<?php echo esc_url( ksenon_services_archive_url() ); ?>"><?php esc_html_e( 'Все услуги', 'ksenonspb' ); ?></a>
	</div>
</section>
