<?php

/**
 * Advantages block
 *
 * @package ksenonspb
 *
 * @var array $args { @type array $items }
 */

$args = wp_parse_args(
	isset($args) && is_array($args) ? $args : array(),
	array(
		'items' => array(),
	)
);

$items = array_filter(
	(array) $args['items'],
	static function ($item) {
		return is_array($item) && ('' !== trim((string) ($item['title'] ?? '')) || '' !== trim((string) ($item['text'] ?? '')));
	}
);

if (! $items) {
	return;
}
?>
<section class="advantages">
	<div class="advantages__container container">
		<ul class="advantages__grid">
			<?php foreach ($items as $item) : ?>
				<?php
				$card_class = 'advantages__card';
				if (! empty($item['selected'])) {
					$card_class .= ' _selected';
				}
				?>
				<li class="<?php echo esc_attr($card_class); ?>">
					<?php if (! empty($item['title'])) : ?>
						<div class="advantages__value"><?php echo esc_html($item['title']); ?></div>
					<?php endif; ?>
					<?php if (! empty($item['text'])) : ?>
						<div class="advantages__label"><?php echo esc_html($item['text']); ?></div>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
