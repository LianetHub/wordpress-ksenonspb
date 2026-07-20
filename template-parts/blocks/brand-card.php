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

$logo = ksenon_get_post_field('logo', $post->ID);
if (! $logo && has_post_thumbnail($post)) {
	$logo = get_post_thumbnail_id($post);
}
?>
<li class="brand-card">
	<a class="brand-card__link" href="<?php echo esc_url(get_permalink($post)); ?>">
		<?php if ($logo) : ?>
			<div class="brand-card__media">
				<?php echo ksenon_acf_image($logo, 'medium', array('class' => 'brand-card__logo')); ?>
			</div>
		<?php endif; ?>
		<span class="brand-card__title"><?php echo esc_html(get_the_title($post)); ?> →</span>
	</a>
</li>