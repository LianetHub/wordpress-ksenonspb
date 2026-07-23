<?php

/**
 * Promotions archive
 *
 * @package ksenonspb
 */

get_header();
?>
<section class="promotions-archive">
	<div class="promotions-archive__container container">
		<h1 class="promotions-archive__title title-lg"><?php post_type_archive_title(); ?></h1>
		<?php if (have_posts()) : ?>
			<div class="promotions-archive__list">
				<?php
				while (have_posts()) :
					the_post();
					get_template_part('template-parts/blocks/promotion-card', null, array('post' => get_post()));
				endwhile;
				?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<?php get_template_part('template-parts/blocks/promotions-empty'); ?>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
