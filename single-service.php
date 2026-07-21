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
						<h2 class="service-pricing__title"><?php esc_html_e('Что входит в услугу', 'ksenonspb'); ?></h2>
						<table class="service-pricing__table">
							<colgroup>
								<col class="service-pricing__col service-pricing__col--name" />
								<col class="service-pricing__col service-pricing__col--price" />
								<col class="service-pricing__col service-pricing__col--duration" />
								<col class="service-pricing__col service-pricing__col--warranty" />
							</colgroup>
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

								$section_title_html  = esc_html($section['title']);
								if (! empty($section['note'])) {
									$section_title_html .= ' <span class="service-pricing__section-note">' . esc_html($section['note']) . '</span>';
								}
								?>
								<?php if ('main' === $section['mod']) : ?>
									<thead class="service-pricing__head service-pricing__head--main">
										<tr>
											<th scope="col" class="service-pricing__th service-pricing__th--name">
												<span class="service-pricing__section-title"><?php echo $section_title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
											</th>
											<th scope="col" class="service-pricing__th service-pricing__th--price">
												<span class="service-pricing__col-label"><?php esc_html_e('Цена', 'ksenonspb'); ?></span>
											</th>
											<th scope="col" class="service-pricing__th service-pricing__th--duration">
												<span class="service-pricing__col-label"><?php esc_html_e('Срок', 'ksenonspb'); ?></span>
											</th>
											<th scope="col" class="service-pricing__th service-pricing__th--warranty">
												<span class="service-pricing__col-label"><?php esc_html_e('Гарантия', 'ksenonspb'); ?></span>
											</th>
										</tr>
									</thead>
									<tbody class="service-pricing__body service-pricing__body--main">
										<?php foreach ($visible_rows as $row) : ?>
											<tr class="service-pricing__row">
												<td class="service-pricing__cell service-pricing__name" data-label="<?php esc_attr_e('Услуга', 'ksenonspb'); ?>">
													<?php echo esc_html((string) $row['name']); ?>
												</td>
												<td class="service-pricing__cell service-pricing__price" data-label="<?php esc_attr_e('Цена', 'ksenonspb'); ?>">
													<?php echo esc_html((string) ($row['price'] ?? '')); ?>
												</td>
												<td class="service-pricing__cell service-pricing__duration" data-label="<?php esc_attr_e('Срок', 'ksenonspb'); ?>">
													<?php echo esc_html((string) ($row['duration'] ?? '')); ?>
												</td>
												<td class="service-pricing__cell service-pricing__warranty" data-label="<?php esc_attr_e('Гарантия', 'ksenonspb'); ?>">
													<?php echo esc_html((string) ($row['warranty'] ?? '')); ?>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								<?php else : ?>
									<tbody class="service-pricing__body service-pricing__body--<?php echo esc_attr($section['mod']); ?>">
										<tr class="service-pricing__section-row">
											<th colspan="4" scope="colgroup" class="service-pricing__section-cell">
												<div class="service-pricing__head service-pricing__head--solo">
													<span class="service-pricing__section-title"><?php echo $section_title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
												</div>
											</th>
										</tr>
										<?php foreach ($visible_rows as $row) : ?>
											<tr class="service-pricing__row">
												<td class="service-pricing__cell service-pricing__name" data-label="<?php esc_attr_e('Услуга', 'ksenonspb'); ?>">
													<?php echo esc_html((string) $row['name']); ?>
												</td>
												<td class="service-pricing__cell service-pricing__price" data-label="<?php esc_attr_e('Цена', 'ksenonspb'); ?>">
													<?php echo esc_html((string) ($row['price'] ?? '')); ?>
												</td>
												<td class="service-pricing__cell service-pricing__duration" data-label="<?php esc_attr_e('Срок', 'ksenonspb'); ?>">
													<?php echo esc_html((string) ($row['duration'] ?? '')); ?>
												</td>
												<td class="service-pricing__cell service-pricing__warranty" data-label="<?php esc_attr_e('Гарантия', 'ksenonspb'); ?>">
													<?php echo esc_html((string) ($row['warranty'] ?? '')); ?>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								<?php endif; ?>
							<?php endforeach; ?>
						</table>
					<?php endif; ?>
					<?php if ($warranty_text) : ?>
						<div class="service-pricing__note">
							<span class="service-pricing__note-icon" aria-hidden="true">
								<?php ksenon_icon('icon-shield', 28, 35, 'service-pricing__note-icon-svg'); ?>
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
						'query'     => $brands,
						'title'     => __('Подойдет <span class="color-accent">для вашей</span> марки', 'ksenonspb'),
						'show_more' => false,
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
