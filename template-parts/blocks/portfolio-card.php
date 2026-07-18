<?php

/**
 * Portfolio card
 *
 * @package ksenonspb
 *
 * @var array $args { @type WP_Post $post }
 */

$post = $args['post'] ?? null;
if (! $post instanceof WP_Post) {
	return;
}

$before = ksenon_get_post_field('before_image', $post->ID);
$after  = ksenon_get_post_field('after_image', $post->ID);
$quote  = (string) ksenon_get_post_field('card_quote', $post->ID);
$price  = (string) ksenon_get_post_field('price', $post->ID);
$title  = (string) ksenon_get_post_field('hero_title', $post->ID);

if (! $title) {
	$title = get_the_title($post);
}

if (! $quote && has_excerpt($post)) {
	$quote = get_the_excerpt($post);
}

if (! $before) {
	$before = ksenon_get_post_field('hero_image', $post->ID);
}
if (! $after && has_post_thumbnail($post)) {
	$after = get_post_thumbnail_id($post);
}
?>
<article class="portfolio-card">
	<div class="portfolio-card__media">
		<?php if ($before) : ?>
			<div class="portfolio-card__image portfolio-card__image--before">
				<?php echo ksenon_acf_image($before, 'medium_large', array('class' => 'portfolio-card__img cover-image')); ?>
			</div>
		<?php endif; ?>
		<?php if ($after) : ?>
			<div class="portfolio-card__image portfolio-card__image--after">
				<?php echo ksenon_acf_image($after, 'medium_large', array('class' => 'portfolio-card__img cover-image')); ?>
			</div>
		<?php endif; ?>
		<?php if ($before && $after) : ?>
			<span class="portfolio-card__compare" aria-hidden="true">
				<?php ksenon_icon('icon-compare', 40, 42, 'portfolio-card__compare-icon'); ?>
			</span>
		<?php endif; ?>
	</div>
	<div class="portfolio-card__body">
		<h3 class="portfolio-card__title"><?php echo esc_html($title); ?></h3>
		<?php if ($quote) : ?>
			<p class="portfolio-card__quote"><?php echo esc_html($quote); ?></p>
		<?php endif; ?>
		<div class="portfolio-card__footer">
			<?php if ($price) : ?>
				<div class="portfolio-card__price"><?php echo esc_html($price); ?></div>
			<?php endif; ?>
			<a class="portfolio-card__link btn" href="<?php echo esc_url(get_permalink($post)); ?>">
				<span class="btn__text"><?php esc_html_e('Подробнее', 'ksenonspb'); ?></span>
				<?php ksenon_btn_arrow_icon(); ?>
			</a>
		</div>
	</div>
</article>