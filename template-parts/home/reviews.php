<?php

/**
 * Home: Reviews
 *
 * @package ksenonspb
 */

get_template_part(
	'template-parts/blocks/reviews',
	null,
	array(
		'title' => (string) ksenon_home_get('title'),
	)
);
