<?php

/**
 * Service or service category card
 *
 * @package ksenonspb
 *
 * @var array $args {
 *     @type WP_Post $post  Service post.
 *     @type WP_Term $term  Service category term.
 *     @type string  $class Optional extra CSS classes for the card root.
 * }
 */

$post = $args['post'] ?? null;
$term = $args['term'] ?? null;

if (! $post instanceof WP_Post && ! $term instanceof WP_Term) {
	return;
}

$card_class = 'service-card';

if (! empty($args['class'])) {
	$card_class .= ' ' . $args['class'];
}

$permalink = '';
$title     = '';
$image     = null;
$price_raw = 0;
$excerpt   = '';
$labels    = array();

if ($term instanceof WP_Term) {
	$permalink = function_exists('ksenon_service_category_url')
		? ksenon_service_category_url($term->slug)
		: get_term_link($term);

	if (is_wp_error($permalink)) {
		$permalink = '';
	}

	$title     = $term->name;
	$price_raw = function_exists('ksenon_get_service_category_min_price')
		? ksenon_get_service_category_min_price((int) $term->term_id)
		: 0;
	$excerpt   = wp_strip_all_tags((string) $term->description);
	$labels    = function_exists('ksenon_normalize_card_labels')
		? ksenon_normalize_card_labels(ksenon_get_term_field('card_labels', (int) $term->term_id))
		: array();
} else {
	$permalink = get_permalink($post);
	$title     = get_the_title($post);
	$image     = ksenon_get_post_field('card_image', $post->ID);
	$price_raw = ksenon_get_post_field('price_from', $post->ID);
	$excerpt   = (string) ksenon_get_post_field('card_excerpt', $post->ID);
	$labels    = function_exists('ksenon_normalize_card_labels')
		? ksenon_normalize_card_labels(ksenon_get_post_field('card_labels', $post->ID))
		: array();

	if (! $image && has_post_thumbnail($post)) {
		$image = get_post_thumbnail_id($post);
	}

	if (! $excerpt && has_excerpt($post)) {
		$excerpt = get_the_excerpt($post);
	}
}

$price = '';

if (function_exists('ksenon_format_price_from')) {
	$price = ksenon_format_price_from($price_raw);
} elseif ($price_raw) {
	$digits = preg_replace('/\D/u', '', (string) $price_raw);
	if ('' !== $digits) {
		$amount = (int) $digits;
		if ($amount > 0) {
			$nbsp  = "\xc2\xa0";
			$price = wp_kses(
				sprintf(
					'<small>%1$s</small> <span>%2$s</span> <small>%3$s</small>',
					esc_html__('от', 'ksenonspb'),
					esc_html(number_format($amount, 0, '', $nbsp)),
					'₽'
				),
				array(
					'small' => array(),
					'span'  => array(),
				)
			);
		}
	}
}

$bg_style = '';
if ($image) {
	$image_url = is_array($image) ? ($image['url'] ?? '') : wp_get_attachment_image_url($image, 'large');
	if ($image_url) {
		$bg_style = ' style="background-image:url(' . esc_url($image_url) . ')"';
	}
}
?>
<li class="<?php echo esc_attr($card_class); ?>" <?php echo $bg_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
													?>>
	<div class="service-card__inner">
		<div class="service-card__head">
			<a class="service-card__title" href="<?php echo esc_url($permalink); ?>">
				<?php echo esc_html($title); ?>
			</a>
			<?php if ($price) : ?>
				<div class="service-card__price"><?php echo $price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
													?></div>
			<?php endif; ?>
		</div>
		<?php if ($excerpt) : ?>
			<p class="service-card__excerpt"><?php echo esc_html($excerpt); ?></p>
		<?php endif; ?>
		<?php if ($labels) : ?>
			<ul class="service-card__labels">
				<?php foreach ($labels as $label) : ?>
					<li class="service-card__label"><?php echo esc_html($label); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<a class="service-card__link btn btn--white btn--arrow" href="<?php echo esc_url($permalink); ?>">
			<span class="btn__text"><?php esc_html_e('Перейти', 'ksenonspb'); ?></span>
			<?php ksenon_btn_arrow_icon(); ?>
		</a>
	</div>
</li>