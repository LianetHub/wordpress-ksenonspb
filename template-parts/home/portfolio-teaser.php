<?php
/**
 * Home: Portfolio teaser
 *
 * @package ksenonspb
 */

$limit = (int) ksenon_home_get( 'limit', 4 );
$query = ksenon_query_portfolio(
	array(
		'posts_per_page' => $limit > 0 ? $limit : 4,
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
		<div class="section-head section-head--row portfolio-teaser__head">
			<h2 class="section-head__title title-md portfolio-teaser__title">
				<?php echo esc_html( (string) ksenon_home_get( 'title', __( 'Наши работы', 'ksenonspb' ) ) ); ?>
			</h2>
			<a class="section-head__more portfolio-teaser__more" href="<?php echo esc_url( ksenon_portfolio_archive_url() ); ?>">
				<span class="section-head__more-text"><?php esc_html_e( 'Все работы по вашей марке', 'ksenonspb' ); ?></span>
				<?php ksenon_render_home_arrow(); ?>
			</a>
		</div>
		<div class="portfolio-teaser__slider swiper">
			<div class="portfolio-teaser__grid swiper-wrapper">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					?>
					<div class="portfolio-teaser__slide swiper-slide">
						<?php get_template_part( 'template-parts/blocks/portfolio-card', null, array( 'post' => get_post() ) ); ?>
					</div>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<div class="portfolio-teaser__nav">
				<button class="portfolio-teaser__prev" type="button" aria-label="<?php esc_attr_e( 'Предыдущие работы', 'ksenonspb' ); ?>"></button>
				<button class="portfolio-teaser__next" type="button" aria-label="<?php esc_attr_e( 'Следующие работы', 'ksenonspb' ); ?>"></button>
			</div>
		</div>
	</div>
</section>
