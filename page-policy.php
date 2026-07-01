<?php

/**
 * Template Name: Политика конфиденциальности
 *
 * @package ksenonspb
 */

get_header();
?>
<?php
while (have_posts()) :
	the_post();
?>
	<section class="policy">
		<div class="policy__container container">
			<div class="policy__inner typography-block <?php echo ksenon_anim_class('fade-up'); ?>">
				<h1> <?php the_title(); ?></h1>
				<?php the_content(); ?>
			</div>
		</div>
	</section>
<?php
endwhile;
?>
<?php
get_footer();
