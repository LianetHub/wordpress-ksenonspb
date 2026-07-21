<?php

/**
 * Single brand
 *
 * @package ksenonspb
 */

get_header();

while (have_posts()) :
	the_post();
	$post_id     = get_the_ID();
	$brand_title = get_the_title();
	$hero_title  = (string) (ksenon_get_post_field('hero_title', $post_id) ?: sprintf(
		/* translators: %s: brand name */
		__('Ремонт фар %s', 'ksenonspb'),
		$brand_title
	));
	?>
	<article class="brand-page">
		<section class="brand-hero">
			<div class="brand-hero__container container">
				<h1 class="brand-hero__title title-lg"><?php echo esc_html($hero_title); ?></h1>
			</div>
		</section>

		<?php
		$features       = (array) ksenon_get_post_field('features', $post_id);
		$features_title = (string) (ksenon_get_post_field('features_title', $post_id) ?: sprintf(
			/* translators: %s: brand name */
			__('Особенности ремонта фар %s', 'ksenonspb'),
			$brand_title
		));
		$feature_rows = array_values(
			array_filter(
				$features,
				static function ($row) {
					return is_array($row) && (! empty($row['feature_title']) || ! empty($row['feature_text']));
				}
			)
		);
		if ($feature_rows) :
			?>
			<section class="brand-features">
				<div class="brand-features__container container">
					<h2 class="brand-features__title title-md"><?php echo esc_html($features_title); ?></h2>
					<ul class="brand-features__grid">
						<?php foreach ($feature_rows as $feature) : ?>
							<li class="brand-features__card">
								<?php if (! empty($feature['feature_title'])) : ?>
									<h3 class="brand-features__card-title"><?php echo esc_html((string) $feature['feature_title']); ?></h3>
								<?php endif; ?>
								<?php if (! empty($feature['feature_text'])) : ?>
									<p class="brand-features__card-text"><?php echo esc_html((string) $feature['feature_text']); ?></p>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$services = ksenon_query_services(
			array(
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => 'related_brands',
						'value'   => '"' . $post_id . '"',
						'compare' => 'LIKE',
					),
				),
			)
		);

		if ($services instanceof WP_Query && $services->have_posts()) :
			$filter_terms = array();
			$cards_data   = array();

			while ($services->have_posts()) :
				$services->the_post();
				$service_post = get_post();
				$terms        = get_the_terms($service_post, 'service_category');
				$slugs        = array();

				if (is_array($terms)) {
					foreach ($terms as $term) {
						if (! $term instanceof WP_Term) {
							continue;
						}
						$top = $term;
						while ($top->parent) {
							$parent = get_term($top->parent, 'service_category');
							if (! $parent instanceof WP_Term || is_wp_error($parent)) {
								break;
							}
							$top = $parent;
						}
						$slugs[] = $top->slug;
						$filter_terms[ $top->term_id ] = $top;
					}
				}

				$cards_data[] = array(
					'post'  => $service_post,
					'slugs' => array_values(array_unique($slugs)),
				);
			endwhile;
			wp_reset_postdata();

			uasort(
				$filter_terms,
				static function ($a, $b) {
					return strcasecmp($a->name, $b->name);
				}
			);

			$services_title = sprintf(
				/* translators: %s: brand name wrapped in accent span */
				__('Услуги для %s', 'ksenonspb'),
				'<span class="color-accent">' . esc_html($brand_title) . '</span>'
			);
			?>
			<section class="brand-services">
				<div class="brand-services__container container container--large">
					<h2 class="brand-services__title title-lg">
						<?php echo wp_kses_post($services_title); ?>
					</h2>
					<div class="brand-services__panel" data-brand-services>
						<?php if ($filter_terms) : ?>
							<nav class="cpt-archive__filters swiper brand-services__filters" aria-label="<?php esc_attr_e('Фильтр услуг по категориям', 'ksenonspb'); ?>">
								<div class="swiper-wrapper">
									<button type="button" class="cpt-archive__tab swiper-slide _active" data-brand-filter="all" aria-pressed="true">
										<?php esc_html_e('Все', 'ksenonspb'); ?>
									</button>
									<?php foreach ($filter_terms as $term) : ?>
										<button
											type="button"
											class="cpt-archive__tab swiper-slide"
											data-brand-filter="<?php echo esc_attr($term->slug); ?>"
											aria-pressed="false">
											<?php echo esc_html($term->name); ?>
										</button>
									<?php endforeach; ?>
								</div>
							</nav>
						<?php endif; ?>

						<ul class="brand-services__grid">
							<?php foreach ($cards_data as $card) : ?>
								<?php
								get_template_part(
									'template-parts/blocks/service-card',
									null,
									array(
										'post'            => $card['post'],
										'filter_slugs'    => $card['slugs'],
									)
								);
								?>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$portfolio = ksenon_query_portfolio(
			array(
				'posts_per_page' => 4,
				'meta_query'     => array(
					array(
						'key'     => 'related_brands',
						'value'   => '"' . $post_id . '"',
						'compare' => 'LIKE',
					),
				),
			)
		);

		if ($portfolio->have_posts()) {
			$portfolio_title = sprintf(
				/* translators: %s: brand name */
				__('Примеры работ %s', 'ksenonspb'),
				$brand_title
			);
			get_template_part(
				'template-parts/blocks/portfolio-teaser',
				null,
				array(
					'query'      => $portfolio,
					'title'      => $portfolio_title,
					'show_more'  => false,
				)
			);
		}
		?>

		<?php get_template_part('template-parts/blocks/cta-form', null, array('variant' => 'service_not_found')); ?>
	</article>
	<?php
endwhile;

get_footer();
