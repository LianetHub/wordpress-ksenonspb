<?php
/**
 * Main template fallback
 *
 * @package ksenonspb
 */

get_header();
?>
<div class="_container">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
	endif;
	?>
</div>
<?php
get_footer();
