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
		'title' => (string) ksenon_home_get('title'),
	)
);
