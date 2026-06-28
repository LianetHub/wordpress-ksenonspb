<?php
/**
 * Single service
 *
 * @package ksenonspb
 */

get_header();

while ( have_posts() ) :
	the_post();
	$post_id = get_the_ID();
	?>
	<article class="service-page">
		<section class="service-hero">
			<div class="service-hero__container _container">
				<h1 class="service-hero__title title-lg"><?php echo esc_html( (string) ksenon_get_post_field( 'hero_title', $post_id ) ?: get_the_title() ); ?></h1>
				<?php if ( ksenon_get_post_field( 'hero_text', $post_id ) ) : ?>
					<p class="service-hero__text"><?php echo nl2br( esc_html( (string) ksenon_get_post_field( 'hero_text', $post_id ) ) ); ?></p>
				<?php endif; ?>
				<?php
				$hero_image = ksenon_get_post_field( 'hero_image', $post_id );
				if ( $hero_image ) {
					echo ksenon_acf_image( $hero_image, 'large', array( 'class' => 'service-hero__img' ) );
				}
				?>
			</div>
		</section>

		<?php
		$included = ksenon_get_post_field( 'included_items', $post_id );
		if ( is_array( $included ) && $included ) :
			?>
			<section class="service-included">
				<div class="service-included__container _container">
					<h2 class="service-included__title title-md"><?php echo esc_html( (string) ( ksenon_get_post_field( 'included_title', $post_id ) ?: __( 'Что входит в услугу', 'ksenonspb' ) ) ); ?></h2>
					<ul class="service-included__list">
						<?php foreach ( $included as $row ) : ?>
							<?php if ( ! empty( $row['text'] ) ) : ?>
								<li><?php echo esc_html( $row['text'] ); ?></li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$brand_ids = ksenon_get_related_ids( 'related_brands', $post_id );
		if ( $brand_ids ) :
			$brands = ksenon_query_brands( array( 'post__in' => $brand_ids, 'orderby' => 'post__in' ) );
			if ( $brands->have_posts() ) :
				?>
				<section class="service-brands">
					<div class="service-brands__container _container">
						<h2 class="service-brands__title title-md"><?php esc_html_e( 'Марки', 'ksenonspb' ); ?></h2>
						<div class="service-brands__grid">
							<?php
							while ( $brands->have_posts() ) :
								$brands->the_post();
								get_template_part( 'template-parts/blocks/brand-card', null, array( 'post' => get_post() ) );
							endwhile;
							wp_reset_postdata();
							?>
						</div>
					</div>
				</section>
				<?php
			endif;
		endif;
		?>

		<?php
		$portfolio = ksenon_query_portfolio(
			array(
				'posts_per_page' => 4,
				'meta_query'     => array(
					array(
						'key'     => 'related_services',
						'value'   => '"' . $post_id . '"',
						'compare' => 'LIKE',
					),
				),
			)
		);
		if ( $portfolio->have_posts() ) :
			?>
			<section class="service-portfolio">
				<div class="service-portfolio__container _container">
					<h2 class="service-portfolio__title title-md"><?php esc_html_e( 'Портфолио', 'ksenonspb' ); ?></h2>
					<div class="service-portfolio__grid">
						<?php
						while ( $portfolio->have_posts() ) :
							$portfolio->the_post();
							get_template_part( 'template-parts/blocks/portfolio-card', null, array( 'post' => get_post() ) );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php
		ksenon_render_faq(
			array(
				'tag'   => (string) ksenon_get_post_field( 'faq_tag', $post_id ),
				'title' => (string) ( ksenon_get_post_field( 'faq_title', $post_id ) ?: __( 'FAQ', 'ksenonspb' ) ),
				'items' => ksenon_normalize_faq_items( (array) ksenon_get_post_field( 'faq', $post_id ) ),
			)
		);
		?>

		<?php
		get_template_part(
			'template-parts/blocks/cta-contacts',
			null,
			array(
				'title' => __( 'Запишитесь на бесплатный осмотр', 'ksenonspb' ),
			)
		);
		?>
	</article>
	<?php
endwhile;

get_footer();
