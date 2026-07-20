<?php

/**
 * 404 template
 *
 * @package ksenonspb
 */

status_header(404);
nocache_headers();

get_header();
?>
	<?php get_template_part('template-parts/pages/404'); ?>
<?php
get_footer();
