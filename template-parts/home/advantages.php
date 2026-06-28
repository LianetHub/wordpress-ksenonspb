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
		'tag'   => (string) ksenon_home_get( 'tag' ),
		'title' => (string) ksenon_home_get( 'title' ),
		'items' => ksenon_home_rows( 'items' ),
	)
);
