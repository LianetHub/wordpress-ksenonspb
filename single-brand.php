<?php
/**
 * Single brand
 *
 * @package ksenonspb
 */

get_header();

while ( have_posts() ) :
	the_post();
	$post_id = get_the_ID();
	?>
	<article class="brand-page">
		<section class="brand-hero">
			<div class="brand-hero__container _container">
				<h1 class="brand-hero__title title-lg"><?php echo esc_html( (string) ( ksenon_get_post_field( 'hero_title', $post_id ) ?: get_the_title() ) ); ?></h1>
				<?php
				$hero_image = ksenon_get_post_field( 'hero_image', $post_id );
				if ( $hero_image ) {
					echo ksenon_acf_image( $hero_image, 'large', array( 'class' => 'brand-hero__img' ) );
				}
				?>
			</div>
		</section>

		<?php if ( ksenon_get_post_field( 'features', $post_id ) ) : ?>
			<section class="brand-features">
				<div class="brand-features__container _container typography-block">
					<h2 class="brand-features__title title-md"><?php esc_html_e( 'Особенности марки', 'ksenonspb' ); ?></h2>
					<?php echo wp_kses_post( (string) ksenon_get_post_field( 'features', $post_id ) ); ?>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$service_ids = ksenon_get_related_ids( 'related_services', $post_id );
		if ( $service_ids ) :
			$services = ksenon_query_services( array( 'post__in' => $service_ids, 'orderby' => 'post__in' ) );
			if ( $services->have_posts() ) :
				?>
				<section class="brand-services">
					<div class="brand-services__container _container">
						<h2 class="brand-services__title title-md"><?php esc_html_e( 'Связанные услуги', 'ksenonspb' ); ?></h2>
						<div class="brand-services__grid">
							<?php
							while ( $services->have_posts() ) :
								$services->the_post();
								get_template_part( 'template-parts/blocks/service-card', null, array( 'post' => get_post() ) );
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
		$models = (array) ksenon_get_post_field( 'popular_models', $post_id );
		if ( $models ) :
			?>
			<section class="brand-models">
				<div class="brand-models__container _container">
					<h2 class="brand-models__title title-md"><?php esc_html_e( 'Популярные модели', 'ksenonspb' ); ?></h2>
					<div class="brand-models__grid">
						<?php foreach ( $models as $model ) : ?>
							<?php
							$link = is_array( $model['model_link'] ?? null ) ? ksenon_acf_link_url( $model['model_link'], '' ) : '';
							$tag  = $link ? 'a' : 'div';
							?>
							<<?php echo esc_html( $tag ); ?> class="brand-models__item"<?php echo $link ? ' href="' . esc_url( $link ) . '"' : ''; ?>>
								<?php if ( ! empty( $model['model_image'] ) ) : ?>
									<?php echo ksenon_acf_image( $model['model_image'], 'medium', array( 'class' => 'brand-models__img' ) ); ?>
								<?php endif; ?>
								<?php if ( ! empty( $model['model_title'] ) ) : ?>
									<h3><?php echo esc_html( $model['model_title'] ); ?></h3>
								<?php endif; ?>
							</<?php echo esc_html( $tag ); ?>>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/blocks/cta-form', null, array( 'variant' => 'service_not_found' ) ); ?>
	</article>
	<?php
endwhile;

get_footer();
