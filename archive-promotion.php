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
		<div class="promotions-archive__list">
			<?php
			if (have_posts()) :
				while (have_posts()) :
					the_post();
			?>
					<article class="promotion-card">
						<a class="promotion-card__link" href="<?php the_permalink(); ?>">
							<h2 class="promotion-card__title"><?php the_title(); ?></h2>
							<?php if (has_excerpt()) : ?>
								<p class="promotion-card__excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
							<?php endif; ?>
							<?php if (ksenon_get_post_field('valid_until')) : ?>
								<p class="promotion-card__date"><?php echo esc_html((string) ksenon_get_post_field('valid_until')); ?></p>
							<?php endif; ?>
						</a>
					</article>
			<?php
				endwhile;
			endif;
			?>
		</div>
		<?php the_posts_pagination(); ?>
	</div>
</section>
<?php
get_footer();
