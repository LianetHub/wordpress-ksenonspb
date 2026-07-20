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
$quote  = has_excerpt($post) ? get_the_excerpt($post) : '';
$price  = (string) ksenon_get_post_field('price', $post->ID);
$title  = (string) ksenon_get_post_field('hero_title', $post->ID);
$permalink = get_permalink($post);
$has_compare = (bool) ($before && $after);

if (! $title) {
	$title = get_the_title($post);
}

$single = null;
if (! $has_compare) {
	$single = $after ?: $before ?: ksenon_get_post_field('hero_image', $post->ID);
	if (! $single && has_post_thumbnail($post)) {
		$single = get_post_thumbnail_id($post);
	}
}

$price_html = '';
if ($price) {
	$digits = preg_replace('/\D/u', '', $price);
	if ('' !== $digits && (int) $digits > 0) {
		$nbsp       = "\xc2\xa0";
		$price_html = esc_html(number_format((int) $digits, 0, '', $nbsp))
			. ' <span class="portfolio-card__currency">₽</span>';
	} else {
		$price_html = esc_html($price);
	}
}

$media_class = 'portfolio-card__media' . ($has_compare ? '' : ' portfolio-card__media--single');
$has_media   = $has_compare || $single;
?>
<article class="portfolio-card">
	<?php if ($has_media) : ?>
		<a class="<?php echo esc_attr($media_class); ?>" href="<?php echo esc_url($permalink); ?>">
			<?php if ($has_compare) : ?>
				<div class="portfolio-card__image portfolio-card__image--before">
					<?php echo ksenon_acf_image($before, 'medium_large', array('class' => 'portfolio-card__img cover-image')); ?>
				</div>
				<div class="portfolio-card__image portfolio-card__image--after">
					<?php echo ksenon_acf_image($after, 'medium_large', array('class' => 'portfolio-card__img cover-image')); ?>
				</div>
				<span class="portfolio-card__compare" aria-hidden="true">
					<?php ksenon_icon('icon-compare', 13, 20, 'portfolio-card__compare-icon'); ?>
				</span>
			<?php else : ?>
				<div class="portfolio-card__image">
					<?php echo ksenon_acf_image($single, 'medium_large', array('class' => 'portfolio-card__img cover-image')); ?>
				</div>
			<?php endif; ?>
		</a>
	<?php endif; ?>
	<div class="portfolio-card__body">
		<h3 class="portfolio-card__title">
			<a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
		</h3>
		<?php if ($quote) : ?>
			<p class="portfolio-card__quote"><?php echo esc_html($quote); ?></p>
		<?php endif; ?>
		<div class="portfolio-card__footer">
			<?php if ($price_html) : ?>
				<div class="portfolio-card__price"><?php echo $price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
													?></div>
			<?php endif; ?>
			<a class="portfolio-card__link btn" href="<?php echo esc_url($permalink); ?>">
				<span class="btn__text"><?php esc_html_e('Подробнее', 'ksenonspb'); ?></span>
				<?php ksenon_btn_arrow_icon(); ?>
			</a>
		</div>
	</div>
</article>