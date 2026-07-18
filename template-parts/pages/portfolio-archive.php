<?php

/**
 * Portfolio archive page
 *
 * @package ksenonspb
 */

$categories      = ksenon_get_service_categories();
$active_slug     = ksenon_get_portfolio_filter_category_slug();
$active_top_slug = '';
$active_term     = null;

if ($active_slug) {
	$active_term = get_term_by('slug', $active_slug, 'service_category');
	if ($active_term instanceof WP_Term) {
		$active_top_slug = ksenon_get_service_category_top_parent_slug($active_term);
	} else {
		$active_slug = '';
		$active_term = null;
	}
}

$subcategories = $active_top_slug ? ksenon_get_service_subcategories($active_top_slug) : array();
$brand_query   = ksenon_get_portfolio_filter_brand_query();
$brand_post    = ksenon_resolve_portfolio_brand($brand_query);
$brand_value   = $brand_post instanceof WP_Post ? get_the_title($brand_post) : $brand_query;
$brands        = ksenon_query_brands(
	array(
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	)
);
$paged         = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
$query_args    = array(
	'posts_per_page' => 6,
	'paged'          => $paged,
	'no_found_rows'  => false,
);

$meta_query = array();

if ($brand_post instanceof WP_Post) {
	$brand_id = (int) $brand_post->ID;
	$meta_query[] = array(
		'relation' => 'OR',
		array(
			'key'     => 'related_brands',
			'value'   => '"' . $brand_id . '"',
			'compare' => 'LIKE',
		),
		array(
			'key'     => 'related_brands',
			'value'   => 'i:' . $brand_id . ';',
			'compare' => 'LIKE',
		),
	);
} elseif ($brand_query !== '') {
	$query_args['s'] = $brand_query;
}

if ($active_term instanceof WP_Term) {
	$service_ids = ksenon_get_service_ids_for_category((int) $active_term->term_id);
	if ($service_ids) {
		$services_meta = array('relation' => 'OR');
		foreach ($service_ids as $service_id) {
			$services_meta[] = array(
				'key'     => 'related_services',
				'value'   => '"' . (int) $service_id . '"',
				'compare' => 'LIKE',
			);
			$services_meta[] = array(
				'key'     => 'related_services',
				'value'   => 'i:' . (int) $service_id . ';',
				'compare' => 'LIKE',
			);
		}
		$meta_query[] = $services_meta;
	} else {
		$query_args['post__in'] = array(0);
	}
}

if (count($meta_query) === 1) {
	$query_args['meta_query'] = $meta_query;
} elseif (count($meta_query) > 1) {
	$query_args['meta_query'] = array_merge(array('relation' => 'AND'), $meta_query);
}

$query = ksenon_query_portfolio($query_args);
?>
<section class="cpt-archive cpt-archive--portfolio">
	<div class="cpt-archive__container container">
		<h1 class="cpt-archive__title title-lg"><?php echo esc_html__('Наше портфолио', 'ksenonspb'); ?></h1>
	</div>
	<div class="cpt-archive__container container container--large">
		<div class="cpt-archive__panel">
			<form class="cpt-archive__search" method="get" action="<?php echo esc_url(ksenon_portfolio_archive_url()); ?>" role="search">
				<?php if ($active_slug) : ?>
					<input type="hidden" name="category" value="<?php echo esc_attr($active_slug); ?>">
				<?php endif; ?>
				<label class="visually-hidden" for="portfolio-brand-search"><?php esc_html_e('Поиск по марке', 'ksenonspb'); ?></label>
				<input
					id="portfolio-brand-search"
					class="cpt-archive__search-field"
					type="search"
					name="brand"
					value="<?php echo esc_attr($brand_value); ?>"
					placeholder="<?php esc_attr_e('Выберите марку..', 'ksenonspb'); ?>"
					list="portfolio-brands"
					autocomplete="off">
				<?php if ($brands->have_posts()) : ?>
					<datalist id="portfolio-brands">
						<?php
						while ($brands->have_posts()) :
							$brands->the_post();
						?>
							<option value="<?php echo esc_attr(get_the_title()); ?>"></option>
						<?php endwhile; ?>
						<?php wp_reset_postdata(); ?>
					</datalist>
				<?php endif; ?>
				<button class="cpt-archive__search-submit" type="submit" aria-label="<?php esc_attr_e('Искать', 'ksenonspb'); ?>">
					<img
						class="cpt-archive__search-icon"
						src="<?php echo esc_url(ksenon_assets_uri('img/icon-search.png')); ?>"
						width="32"
						height="32"
						alt=""
						decoding="async">
				</button>
			</form>

			<?php if ($categories) : ?>
				<nav class="cpt-archive__filters swiper" aria-label="<?php esc_attr_e('Фильтр портфолио по категориям', 'ksenonspb'); ?>">
					<div class="swiper-wrapper">
						<a
							class="cpt-archive__tab swiper-slide<?php echo $active_slug ? '' : ' _active'; ?>"
							href="<?php echo esc_url(ksenon_portfolio_archive_url('', $brand_query)); ?>"
							<?php echo $active_slug ? '' : ' aria-current="page"'; ?>>
							<?php esc_html_e('Все', 'ksenonspb'); ?>
						</a>
						<?php foreach ($categories as $category) : ?>
							<?php $is_active = $active_top_slug === $category->slug; ?>
							<a
								class="cpt-archive__tab swiper-slide<?php echo $is_active ? ' _active' : ''; ?>"
								href="<?php echo esc_url(ksenon_portfolio_archive_url($category->slug, $brand_query)); ?>"
								<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
								<?php echo esc_html($category->name); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</nav>
			<?php endif; ?>

			<?php if ($subcategories) : ?>
				<nav class="cpt-archive__subfilters swiper" aria-label="<?php esc_attr_e('Подкатегории портфолио', 'ksenonspb'); ?>">
					<div class="swiper-wrapper">
						<a
							class="cpt-archive__tab swiper-slide<?php echo ($active_term && $active_term->slug === $active_top_slug) ? ' _active' : ''; ?>"
							href="<?php echo esc_url(ksenon_portfolio_archive_url($active_top_slug, $brand_query)); ?>"
							<?php echo ($active_term && $active_term->slug === $active_top_slug) ? ' aria-current="page"' : ''; ?>>
							<?php esc_html_e('Все', 'ksenonspb'); ?>
						</a>
						<?php foreach ($subcategories as $subcategory) : ?>
							<?php $is_sub_active = $active_slug === $subcategory->slug; ?>
							<a
								class="cpt-archive__tab swiper-slide<?php echo $is_sub_active ? ' _active' : ''; ?>"
								href="<?php echo esc_url(ksenon_portfolio_archive_url($subcategory->slug, $brand_query)); ?>"
								<?php echo $is_sub_active ? ' aria-current="page"' : ''; ?>>
								<?php echo esc_html($subcategory->name); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</nav>
			<?php endif; ?>

			<?php if ($query->have_posts()) : ?>
				<ul class="cpt-archive__grid">
					<?php
					while ($query->have_posts()) :
						$query->the_post();
						get_template_part('template-parts/blocks/portfolio-card', null, array('post' => get_post()));
					endwhile;
					wp_reset_postdata();
					?>
				</ul>
				<?php
				ksenon_render_pagination(
					$query,
					'',
					static function ($page) use ($active_slug, $brand_query) {
						return ksenon_portfolio_pagination_url($page, $active_slug, $brand_query);
					}
				);
				?>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php get_template_part('template-parts/blocks/cta-form', null, array('variant' => 'same_result')); ?>