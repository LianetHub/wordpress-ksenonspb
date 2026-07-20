<?php

/**
 * Bottom CTA block
 *
 * @package ksenonspb
 *
 * @var array $args {
 *     @type string $title
 *     @type string $text
 *     @type array  $btn_primary
 *     @type array  $btn_secondary
 *     @type string $btn_primary_action
 *     @type string $btn_secondary_action
 * }
 */

$args = wp_parse_args(
	isset($args) && is_array($args) ? $args : array(),
	array(
		'title'                => __('Опишите проблему - оценим быстро и по делу', 'ksenonspb'),
		'text'                 => '',
		'btn_primary'          => null,
		'btn_secondary'        => null,
		'btn_primary_action'   => 'popup_order',
		'btn_secondary_action' => 'popup_consultation',
	)
);

$btn_primary          = $args['btn_primary'];
$btn_secondary        = $args['btn_secondary'];
$btn_primary_action   = sanitize_key((string) $args['btn_primary_action']);
$btn_secondary_action = sanitize_key((string) $args['btn_secondary_action']);

if (! is_array($btn_primary) || empty($btn_primary['url'])) {
	$btn_primary = array(
		'url'   => '#popup-consultation',
		'title' => __('Оценить ремонт', 'ksenonspb'),
	);
}

if (! is_array($btn_secondary) || empty($btn_secondary['url'])) {
	$btn_secondary = array(
		'url'   => '#popup-consultation',
		'title' => __('Связаться с нами', 'ksenonspb'),
	);
}
?>
<section class="cta-bottom">
	<div class="cta-bottom__container container">
		<h2 class="cta-bottom__title title-md title--light"><?php echo esc_html($args['title']); ?></h2>
		<?php if ($args['text']) : ?>
			<p class="cta-bottom__text"><?php echo nl2br(esc_html($args['text'])); ?></p>
		<?php endif; ?>
		<div class="cta-bottom__actions">
			<?php
			ksenon_render_cta_bottom_button(
				$btn_primary,
				$btn_primary_action,
				'btn btn--primary-inverse btn--large cta-bottom__btn',
				__('Оценить ремонт', 'ksenonspb'),
				true
			);
			ksenon_render_cta_bottom_button(
				$btn_secondary,
				$btn_secondary_action,
				'btn btn--white-outline btn--large cta-bottom__btn-secondary',
				__('Связаться с нами', 'ksenonspb'),
				false
			);
			?>
		</div>
	</div>
</section>