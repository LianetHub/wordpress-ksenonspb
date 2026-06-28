<?php
/**
 * Home: Bottom CTA
 *
 * @package ksenonspb
 */

get_template_part(
	'template-parts/blocks/cta-bottom',
	null,
	array(
		'title' => (string) ksenon_home_get( 'title' ),
		'text'  => (string) ksenon_home_get( 'text' ),
	)
);
