<?php

/**
 * Home: Portfolio teaser
 *
 * @package ksenonspb
 */

$limit = (int) ksenon_home_get('limit', 4);
$query = ksenon_query_portfolio(
	array(
		'posts_per_page' => $limit > 0 ? $limit : 4,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

if (! $query->have_posts()) {
	return;
}

get_template_part(
	'template-parts/blocks/portfolio-teaser',
	null,
	array(
		'query' => $query,
		'title' => (string) ksenon_home_get('title', __('Наши работы', 'ksenonspb')),
	)
);
