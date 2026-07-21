<?php

/**
 * Single service
 *
 * @package ksenonspb
 */

get_header();

while (have_posts()) :
	the_post();
	$post_id = get_the_ID();
?>
	<article class="service-page">
		<section class="service-hero">
			<div class="service-hero__container container">
				<h1 class="service-hero__title title-lg"><?php the_title(); ?></h1>
				<?php
				$hero_text = (string) (ksenon_get_post_field('card_excerpt', $post_id) ?: get_the_excerpt());
				if ($hero_text) :
					?>
					<p class="service-hero__text"><?php echo nl2br(esc_html($hero_text)); ?></p>
				<?php endif; ?>
				<?php
				$hero_image = ksenon_get_post_field('card_image', $post_id);
				if ($hero_image) {
					echo ksenon_acf_image($hero_image, 'large', array('class' => 'service-hero__img'));
				} elseif (has_post_thumbnail($post_id)) {
					echo get_the_post_thumbnail($post_id, 'large', array('class' => 'service-hero__img'));
				}
				?>
			</div>
		</section>

		<?php
		$price_sections = array(
			array(
				'key'   => 'price_main',
				'title' => __('Основные работы', 'ksenonspb'),
				'mod'   => 'main',
			),
			array(
				'key'   => 'price_extra',
				'title' => __('Дополнительные работы (по необходимости)', 'ksenonspb'),
				'mod'   => 'extra',
			),
			array(
				'key'   => 'price_diagnostics',
				'title' => __('Диагностика', 'ksenonspb'),
				'mod'   => 'diagnostics',
			),
		);
		$price_has_rows = false;
		foreach ($price_sections as $section) {
			$rows = ksenon_get_post_field($section['key'], $post_id);
			if (is_array($rows) && $rows) {
				foreach ($rows as $row) {
					if (! empty($row['name'])) {
						$price_has_rows = true;
						break 2;
					}
				}
			}
		}
		$warranty_text = (string) (ksenon_get_post_field('warranty_text', $post_id) ?: '');
		if ($price_has_rows || $warranty_text) :
		?>
			<section class="service-pricing">
				<div class="service-pricing__container container">
					<?php if ($price_has_rows) : ?>
						<div class="service-pricing__tables">
							<?php foreach ($price_sections as $section) : ?>
								<?php
								$rows = ksenon_get_post_field($section['key'], $post_id);
								if (! is_array($rows) || ! $rows) {
									continue;
								}
								$visible_rows = array_values(
									array_filter(
										$rows,
										static function ($row) {
											return ! empty($row['name']);
										}
									)
								);
								if (! $visible_rows) {
									continue;
								}
								?>
								<div class="service-pricing__block service-pricing__block--<?php echo esc_attr($section['mod']); ?>">
									<div class="service-pricing__head">
										<span class="service-pricing__section-title"><?php echo esc_html($section['title']); ?></span>
										<span class="service-pricing__col-label"><?php esc_html_e('Цена', 'ksenonspb'); ?></span>
										<span class="service-pricing__col-label"><?php esc_html_e('Срок', 'ksenonspb'); ?></span>
										<span class="service-pricing__col-label"><?php esc_html_e('Гарантия', 'ksenonspb'); ?></span>
									</div>
									<ul class="service-pricing__list">
										<?php foreach ($visible_rows as $row) : ?>
											<li class="service-pricing__row">
												<span class="service-pricing__name"><?php echo esc_html((string) $row['name']); ?></span>
												<span class="service-pricing__price"><?php echo esc_html((string) ($row['price'] ?? '')); ?></span>
												<span class="service-pricing__duration"><?php echo esc_html((string) ($row['duration'] ?? '')); ?></span>
												<span class="service-pricing__warranty"><?php echo esc_html((string) ($row['warranty'] ?? '')); ?></span>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<?php if ($warranty_text) : ?>
						<p class="service-pricing__warranty-text"><?php echo nl2br(esc_html($warranty_text)); ?></p>
					<?php endif; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$brand_ids = ksenon_get_related_ids('related_brands', $post_id);
		if ($brand_ids) :
			$brands = ksenon_query_brands(array('post__in' => $brand_ids, 'orderby' => 'post__in'));
			if ($brands->have_posts()) :
		?>
				<section class="service-brands">
					<div class="service-brands__container container">
						<h2 class="service-brands__title title-md"><?php esc_html_e('Марки', 'ksenonspb'); ?></h2>
						<ul class="service-brands__grid">
							<?php
							while ($brands->have_posts()) :
								$brands->the_post();
								get_template_part('template-parts/blocks/brand-card', null, array('post' => get_post()));
							endwhile;
							wp_reset_postdata();
							?>
						</ul>
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
		if ($portfolio->have_posts()) :
		?>
			<section class="service-portfolio">
				<div class="service-portfolio__container container">
					<h2 class="service-portfolio__title title-md"><?php esc_html_e('Портфолио', 'ksenonspb'); ?></h2>
					<div class="service-portfolio__grid">
						<?php
						while ($portfolio->have_posts()) :
							$portfolio->the_post();
							get_template_part('template-parts/blocks/portfolio-card', null, array('post' => get_post()));
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
				'title' => (string) (ksenon_get_post_field('faq_title', $post_id) ?: __('FAQ', 'ksenonspb')),
				'items' => ksenon_normalize_faq_items((array) ksenon_get_post_field('faq', $post_id)),
			)
		);
		?>

		<?php
		get_template_part(
			'template-parts/blocks/cta-contacts',
			null,
			array(
				'title' => __('Запишитесь на бесплатный осмотр', 'ksenonspb'),
			)
		);
		?>
	</article>
<?php
endwhile;

get_footer();
