<?php

/**
 * Service category taxonomy archive
 *
 * @package ksenonspb
 */

get_header();

$term = get_queried_object();
get_template_part(
	'template-parts/pages/services-archive',
	null,
	array(
		'term' => $term instanceof WP_Term ? $term : null,
	)
);

get_footer();
