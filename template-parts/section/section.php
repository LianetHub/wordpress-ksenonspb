<?php
/**
 * Flexible content section
 *
 * @package ksenonspb
 */

$file = str_replace( '_', '-', get_row_layout() );
get_template_part( 'template-parts/home/' . $file );
