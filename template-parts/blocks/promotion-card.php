<?php

/**
 * Promotion card
 *
 * @package ksenonspb
 *
 * @var array $args { @type WP_Post $post }
 */

$post = $args['post'] ?? null;
if (! $post instanceof WP_Post) {
	return;
}

$permalink = get_permalink($post);
$poster    = function_exists('get_field') ? get_field('poster', $post) : null;
$image     = $poster ?: (has_post_thumbnail($post) ? get_post_thumbnail_id($post) : null);
$excerpt   = has_excerpt($post) ? get_the_excerpt($post) : '';
?>
<article class="promotion-card">
	<?php if ($image) : ?>
		<a class="promotion-card__media" href="<?php echo esc_url($permalink); ?>">
			<?php echo ksenon_acf_image($image, 'medium_large', array('class' => 'promotion-card__img cover-image')); ?>
		</a>
	<?php else : ?>
		<a class="promotion-card__media promotion-card__media--empty" href="<?php echo esc_url($permalink); ?>" aria-hidden="true" tabindex="-1"></a>
	<?php endif; ?>
	<div class="promotion-card__body">
		<h2 class="promotion-card__title">
			<a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html(get_the_title($post)); ?></a>
		</h2>
		<?php if ($excerpt) : ?>
			<p class="promotion-card__excerpt"><?php echo esc_html($excerpt); ?></p>
		<?php endif; ?>
		<a class="promotion-card__link btn btn--primary" href="<?php echo esc_url($permalink); ?>">
			<span class="btn__text"><?php esc_html_e('Подробнее', 'ksenonspb'); ?></span>
		</a>
	</div>
</article>
