<?php

/**
 * Brand card
 *
 * @package ksenonspb
 *
 * @var array $args { @type WP_Post $post }
 */

$post = $args['post'] ?? null;
if (! $post instanceof WP_Post) {
	return;
}

$image = has_post_thumbnail($post) ? get_post_thumbnail_id($post) : null;
?>
<li class="brand-card">
	<a class="brand-card__link" href="<?php echo esc_url(get_permalink($post)); ?>">
		<?php if ($image) : ?>
			<div class="brand-card__media">
				<?php echo ksenon_acf_image($image, 'medium', array('class' => 'brand-card__logo')); ?>
			</div>
		<?php endif; ?>
		<span class="brand-card__title"><?php echo esc_html(get_the_title($post)); ?> →</span>
	</a>
</li>