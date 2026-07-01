<?php

/**
 * Advantages block
 *
 * @package ksenonspb
 *
 * @var array $args { @type string $tag, @type string $title, @type array $items }
 */

$args = wp_parse_args(
	isset($args) && is_array($args) ? $args : array(),
	array(
		'tag'   => '',
		'title' => '',
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
		<div class="advantages__grid">
			<?php foreach ($items as $item) : ?>
				<div class="advantages__card <?php echo ksenon_anim_class('fade-up'); ?>">
					<?php if (! empty($item['title'])) : ?>
						<div class="advantages__value"><?php echo esc_html($item['title']); ?></div>
					<?php endif; ?>
					<?php if (! empty($item['text'])) : ?>
						<div class="advantages__label"><?php echo esc_html($item['text']); ?></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>