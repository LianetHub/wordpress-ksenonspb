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
				<div class="service-hero__top">
					<h1 class="service-hero__title title-lg"><?php the_title(); ?></h1>
					<?php
					ksenon_render_btn_arrow(
						array(
							'url'    => '#contacts',
							'title'  => __('К заявке', 'ksenonspb'),
							'target' => '',
						),
						'btn btn--primary btn--large service-hero__btn',
						__('К заявке', 'ksenonspb')
					);
					?>
				</div>
				<?php
				$hero_image = ksenon_get_post_field('card_image', $post_id);
				if ($hero_image || has_post_thumbnail($post_id)) :
					?>
					<div class="service-hero__media">
						<?php
						if ($hero_image) {
							echo ksenon_acf_image($hero_image, 'large', array('class' => 'service-hero__img'));
						} else {
							echo get_the_post_thumbnail($post_id, 'large', array('class' => 'service-hero__img'));
						}
						?>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<?php
		$price_sections = array(
			array(
				'key'   => 'price_main',
				'title' => __('Основные работы', 'ksenonspb'),
				'note'  => '',
				'mod'   => 'main',
			),
			array(
				'key'   => 'price_extra',
				'title' => __('Дополнительные работы', 'ksenonspb'),
				'note'  => __('(по необходимости)', 'ksenonspb'),
				'mod'   => 'extra',
			),
			array(
				'key'   => 'price_diagnostics',
				'title' => __('Диагностика', 'ksenonspb'),
				'note'  => '',
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
						<h2 class="service-pricing__title title-md"><?php esc_html_e('Что входит в услугу', 'ksenonspb'); ?></h2>
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
										<span class="service-pricing__section-title">
											<?php echo esc_html($section['title']); ?>
											<?php if (! empty($section['note'])) : ?>
												<span class="service-pricing__section-note"><?php echo esc_html($section['note']); ?></span>
											<?php endif; ?>
										</span>
										<?php if ('main' === $section['mod']) : ?>
											<span class="service-pricing__cols" aria-hidden="true">
												<span class="service-pricing__col-label"><?php esc_html_e('Цена', 'ksenonspb'); ?></span>
												<span class="service-pricing__col-label"><?php esc_html_e('Срок', 'ksenonspb'); ?></span>
												<span class="service-pricing__col-label"><?php esc_html_e('Гарантия', 'ksenonspb'); ?></span>
											</span>
										<?php endif; ?>
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
						<div class="service-pricing__note">
							<span class="service-pricing__note-icon" aria-hidden="true">
								<?php ksenon_icon('icon-location', 28, 35, 'service-pricing__note-icon-svg'); ?>
							</span>
							<p class="service-pricing__warranty-text"><?php echo nl2br(esc_html($warranty_text)); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$brand_ids = ksenon_get_related_ids('related_brands', $post_id);
		if ($brand_ids) {
			$brands = ksenon_query_brands(
				array(
					'post__in'       => $brand_ids,
					'orderby'        => 'post__in',
					'posts_per_page' => count($brand_ids),
				)
			);
			if ($brands->have_posts()) {
				get_template_part(
					'template-parts/blocks/brands-section',
					null,
					array(
						'query' => $brands,
						'title' => __('Подойдет <span class="color-accent">для вашей</span> марки', 'ksenonspb'),
					)
				);
			}
		}
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
		if ($portfolio->have_posts()) {
			get_template_part(
				'template-parts/blocks/portfolio-teaser',
				null,
				array(
					'query' => $portfolio,
					'title' => __('Примеры работ', 'ksenonspb'),
				)
			);
		}
		?>

		<?php
		ksenon_render_faq(
			array(
				'title' => (string) (ksenon_get_post_field('faq_title', $post_id) ?: __('Частые вопросы по услуге', 'ksenonspb')),
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
