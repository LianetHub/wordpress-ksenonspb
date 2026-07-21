<?php

/**
 * Home: Brands
 *
 * @package ksenonspb
 */

$limit    = 12;
$featured = ksenon_home_get('featured_brands', array());
$query_args = array(
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
		$query_args['post__in'] = $ids;
		$query_args['orderby']  = 'post__in';
	}
}

if (empty($query_args['post__in']) && function_exists('ksenon_get_popular_brand_ids')) {
	$popular_ids = ksenon_get_popular_brand_ids($limit);
	if ($popular_ids) {
		$query_args['post__in'] = $popular_ids;
		$query_args['orderby']  = 'post__in';
	}
}

$query = ksenon_query_brands($query_args);
if (! $query->have_posts()) {
	return;
}

get_template_part(
	'template-parts/blocks/brands-section',
	null,
	array(
		'query' => $query,
		'title' => (string) ksenon_home_get('title', __('Работаем со всеми марками', 'ksenonspb')),
	)
);
