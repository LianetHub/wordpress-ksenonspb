<?php

/**
 * Home: Brands
 *
 * @package ksenonspb
 */

$limit    = 12;
$featured = ksenon_home_get('featured_brands', array());
$args     = array(
	'posts_per_page' => $limit,
);

if (is_array($featured) && $featured) {
	$ids = array();
	foreach ($featured as $item) {
		if ($item instanceof WP_Post) {
			$ids[] = (int) $item->ID;
		} elseif (is_numeric($item)) {
			$ids[] = (int) $item;
		}
	}

	$ids = array_slice(array_values(array_filter(array_unique($ids))), 0, $limit);

	if ($ids) {
		$args['post__in'] = $ids;
		$args['orderby']  = 'post__in';
	}
}

if (empty($args['post__in']) && function_exists('ksenon_get_popular_brand_ids')) {
	$popular_ids = ksenon_get_popular_brand_ids($limit);
	if ($popular_ids) {
		$args['post__in'] = $popular_ids;
		$args['orderby']  = 'post__in';
	}
}

$query = ksenon_query_brands($args);
if (! $query->have_posts()) {
	return;
}

$brands_count = ksenon_count_cpt('brand');
$title        = (string) ksenon_home_get('title', __('Работаем со всеми марками', 'ksenonspb'));
$title_html   = function_exists('ksenon_title_accent_html')
	? ksenon_title_accent_html($title)
	: esc_html($title);
$more_label   = function_exists('ksenon_brands_count_label')
	? ksenon_brands_count_label($brands_count)
	: __('Все марки', 'ksenonspb');
$more_link    = array(
	'url'    => ksenon_brands_archive_url(),
	'title'  => $more_label,
	'target' => '',
);
?>
<section class="brands-section">
	<div class="brands-section__container container">
		<div class="section-head section-head--row brands-section__head">
			<h2 class="section-head__title title-md brands-section__title">
				<?php echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
				?>
			</h2>
			<?php ksenon_render_btn_arrow($more_link, 'btn btn--primary btn--large brands-section__more', $more_label); ?>
		</div>
		<div class="brands-section__grid">
			<?php
			while ($query->have_posts()) :
				$query->the_post();
				get_template_part('template-parts/blocks/brand-card', null, array('post' => get_post()));
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>