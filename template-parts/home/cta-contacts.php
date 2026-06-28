<?php
/**
 * Home: CTA + contacts
 *
 * @package ksenonspb
 */

get_template_part(
	'template-parts/blocks/cta-contacts',
	null,
	array(
		'tag'   => (string) ksenon_home_get( 'tag' ),
		'title' => (string) ksenon_home_get( 'title' ),
	)
);
