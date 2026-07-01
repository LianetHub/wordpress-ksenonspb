<?php

/**
 * Home: Advantages
 *
 * @package ksenonspb
 */

get_template_part(
	'template-parts/blocks/advantages',
	null,
	array(
		'items' => ksenon_home_rows('items'),
	)
);
