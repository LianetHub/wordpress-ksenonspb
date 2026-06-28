<?php
/**
 * Home: Portfolio teaser
 *
 * @package ksenonspb
 */

$limit = (int) ksenon_home_get( 'limit', 8 );
$query = ksenon_query_portfolio(
	array(
		'posts_per_page' => $limit > 0 ? $limit : 8,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

if ( ! $query->have_posts() ) {
	return;
}
?>
<section class="portfolio-teaser">
	<div class="portfolio-teaser__container _container">
		<?php if ( ksenon_home_get( 'tag' ) ) : ?>
			<span class="portfolio-teaser__tag tag"><?php echo esc_html( (string) ksenon_home_get( 'tag' ) ); ?></span>
		<?php endif; ?>
		<h2 class="portfolio-teaser__title title-md"><?php echo esc_html( (string) ksenon_home_get( 'title', __( 'Наши работы', 'ksenonspb' ) ) ); ?></h2>
		<div class="portfolio-teaser__grid">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				get_template_part( 'template-parts/blocks/portfolio-card', null, array( 'post' => get_post() ) );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<a class="btn portfolio-teaser__more" href="<?php echo esc_url( ksenon_portfolio_archive_url() ); ?>"><?php esc_html_e( 'Все работы', 'ksenonspb' ); ?></a>
	</div>
</section>
