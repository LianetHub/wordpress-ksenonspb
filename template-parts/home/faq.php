<?php
/**
 * Home: FAQ
 *
 * @package ksenonspb
 */

ksenon_render_faq(
	array(
		'tag'   => (string) ksenon_home_get( 'tag' ),
		'title' => (string) ksenon_home_get( 'title' ),
		'intro' => (string) ksenon_home_get( 'intro' ),
		'items' => ksenon_normalize_faq_items( ksenon_home_rows( 'items' ) ),
	)
);
