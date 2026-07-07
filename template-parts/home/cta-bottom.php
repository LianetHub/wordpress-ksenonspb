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
		'title'                => (string) ksenon_home_get( 'title' ),
		'text'                 => (string) ksenon_home_get( 'text' ),
		'btn_primary'          => ksenon_home_get( 'btn_primary' ),
		'btn_secondary'        => ksenon_home_get( 'btn_secondary' ),
		'btn_primary_action'   => (string) ksenon_home_get( 'btn_primary_action', 'popup_order' ),
		'btn_secondary_action' => (string) ksenon_home_get( 'btn_secondary_action', 'popup_consultation' ),
	)
);
