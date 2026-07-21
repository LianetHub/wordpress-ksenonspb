<?php

/**
 * Single promotion
 *
 * @package ksenonspb
 */

get_header();
    10|
while (have_posts()) :
	the_post();
	$post_id = get_the_ID();

	$badge          = (string) ksenon_get_post_field('badge', $post_id);
	$hero_title     = (string) (ksenon_get_post_field('hero_title', $post_id) ?: get_the_title());
	$hero_subtitle  = (string) ksenon_get_post_field('hero_subtitle', $post_id);
	$price_old      = (string) ksenon_get_post_field('price_old', $post_id);
	$price_new      = (string) ksenon_get_post_field('price_new', $post_id);
	$price_savings  = (string) ksenon_get_post_field('price_savings', $post_id);
	$package_name   = (string) ksenon_get_post_field('package_name', $post_id);
	$package_items  = (array) ksenon_get_post_field('package_items', $post_id);
	$gallery        = (array) ksenon_get_post_field('before_after', $post_id);
	$benefits       = (array) ksenon_get_post_field('benefits_cards', $post_id);

	$package_items = array_values(
		array_filter(
			$package_items,
			static function ($row) {
				return is_array($row) && '' !== trim((string) ($row['text'] ?? ''));
			}
		)
	);
?>
	<article class="promotion-page">
		<section class="promotion-hero">
			<div class="promotion-hero__container container">
				<?php if ($badge) : ?>
					<span class="promotion-hero__badge"><?php echo esc_html($badge); ?></span>
				<?php endif; ?>
				<div class="promotion-hero__box">
					<div class="promotion-hero__intro">
						<h1 class="promotion-hero__title"><?php echo esc_html($hero_title); ?></h1>
						<?php if ($hero_subtitle) : ?>
							<p class="promotion-hero__subtitle"><?php echo esc_html($hero_subtitle); ?></p>
						<?php endif; ?>
					</div>
					<?php if ($price_old || $price_new || $price_savings) : ?>
						<div class="promotion-hero__pricing">
							<?php if ($price_old || $price_new) : ?>
								<div class="promotion-hero__prices">
									<?php if ($price_old) : ?>
										<span class="promotion-hero__price-old"><?php echo esc_html($price_old); ?></span>
									<?php endif; ?>
									<?php if ($price_new) : ?>
										<span class="promotion-hero__price-new"><?php echo esc_html($price_new); ?></span>
									<?php endif; ?>
								</div>
							<?php endif; ?>
							<?php if ($price_savings) : ?>
								<span class="promotion-hero__savings"><?php echo esc_html($price_savings); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<?php if ($gallery) : ?>
			<section class="promotion-gallery">
				<div class="promotion-gallery__container container">
					<h2 class="promotion-gallery__title">
						<?php
						echo wp_kses(
							sprintf(
								/* translators: %s: word "после" highlighted */
								__('Результат до и %s', 'ksenonspb'),
								'<span class="color-accent">' . esc_html__('после', 'ksenonspb') . '</span>'
							),
							array('span' => array('class' => true))
						);
						?>
					</h2>
					<div class="promotion-gallery__grid">
						<?php foreach ($gallery as $image) : ?>
							<div class="promotion-gallery__item">
								<?php echo ksenon_acf_image($image, 'large', array('class' => 'promotion-gallery__img cover-image')); ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ($package_items) : ?>
			<section class="promotion-package">
				<div class="promotion-package__container container">
					<div class="promotion-package__box">
						<h2 class="promotion-package__title">
							<?php if ($package_name) : ?>
								<?php
								echo wp_kses(
									sprintf(
										/* translators: %s: package name */
										__('Что входит в «%s»', 'ksenonspb'),
										'<span class="color-accent">' . esc_html($package_name) . '</span>'
									),
									array('span' => array('class' => true))
								);
								?>
							<?php else : ?>
								<?php esc_html_e('Что входит в пакет', 'ksenonspb'); ?>
							<?php endif; ?>
						</h2>
						<ol class="promotion-package__list">
							<?php foreach ($package_items as $index => $row) : ?>
								<li class="promotion-package__item">
									<span class="promotion-package__num" aria-hidden="true"><?php echo esc_html((string) ($index + 1)); ?></span>
									<span class="promotion-package__text"><?php echo esc_html($row['text']); ?></span>
								</li>
							<?php endforeach; ?>
						</ol>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php
		if ($benefits) :
			get_template_part(
				'template-parts/blocks/advantages',
				null,
				array(
					'items' => $benefits,
				)
			);
		endif;
		?>

		<?php get_template_part('template-parts/blocks/reviews'); ?>
		<?php get_template_part('template-parts/blocks/cta-form', null, array('variant' => 'appointment')); ?>
	</article>
<?php
endwhile;

get_footer();
