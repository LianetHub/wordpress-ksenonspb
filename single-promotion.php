<?php
/**
 * Single promotion
 *
 * @package ksenonspb
 */

get_header();

while ( have_posts() ) :
	the_post();
	$post_id = get_the_ID();
	?>
	<article class="promotion-page">
		<section class="promotion-hero">
			<div class="promotion-hero__container _container">
				<h1 class="promotion-hero__title title-lg"><?php echo esc_html( (string) ( ksenon_get_post_field( 'hero_title', $post_id ) ?: get_the_title() ) ); ?></h1>
				<?php
				$hero_image = ksenon_get_post_field( 'hero_image', $post_id );
				if ( $hero_image ) {
					echo ksenon_acf_image( $hero_image, 'large', array( 'class' => 'promotion-hero__img' ) );
				}
				?>
			</div>
		</section>

		<?php
		$gallery = (array) ksenon_get_post_field( 'before_after', $post_id );
		if ( $gallery ) :
			?>
			<section class="promotion-gallery">
				<div class="promotion-gallery__container _container">
					<h2 class="promotion-gallery__title title-md"><?php esc_html_e( 'Результат до и после', 'ksenonspb' ); ?></h2>
					<div class="promotion-gallery__grid">
						<?php foreach ( $gallery as $image ) : ?>
							<?php echo ksenon_acf_image( $image, 'large', array( 'class' => 'promotion-gallery__img' ) ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$package = (array) ksenon_get_post_field( 'package_items', $post_id );
		if ( $package ) :
			?>
			<section class="promotion-package">
				<div class="promotion-package__container _container">
					<h2 class="promotion-package__title title-md"><?php esc_html_e( 'Что входит в пакет', 'ksenonspb' ); ?></h2>
					<ul class="promotion-package__list">
						<?php foreach ( $package as $row ) : ?>
							<?php if ( ! empty( $row['text'] ) ) : ?>
								<li><?php echo esc_html( $row['text'] ); ?></li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$benefits = (array) ksenon_get_post_field( 'benefits_cards', $post_id );
		if ( $benefits ) :
			get_template_part(
				'template-parts/blocks/advantages',
				null,
				array(
					'title' => __( 'Преимущества акции', 'ksenonspb' ),
					'items' => $benefits,
				)
			);
		endif;
		?>

		<?php get_template_part( 'template-parts/blocks/reviews' ); ?>
		<?php get_template_part( 'template-parts/blocks/cta-form', null, array( 'variant' => 'appointment' ) ); ?>
	</article>
	<?php
endwhile;

get_footer();
