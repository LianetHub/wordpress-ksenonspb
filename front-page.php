<?php
/**
 * Front page template
 *
 * @package ksenonspb
 */

get_header();

if ( function_exists( 'have_rows' ) && have_rows( 'page_content' ) ) :
	while ( have_rows( 'page_content' ) ) :
		the_row();
		get_template_part( 'template-parts/section/section', get_row_layout() );
	endwhile;
endif;

get_footer();
