<?php
/**
 * Default page template
 *
 * @package ksenonspb
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="_container">
		<h1 class="title title-lg"><?php the_title(); ?></h1>
		<div class="page__content">
			<?php the_content(); ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
