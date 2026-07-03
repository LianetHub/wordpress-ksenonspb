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
 * }
 */

$args = wp_parse_args(
	isset($args) && is_array($args) ? $args : array(),
	array(
		'title'         => __('Опишите проблему — оценим быстро и по делу', 'ksenonspb'),
		'text'          => '',
		'btn_primary'   => null,
		'btn_secondary' => null,
	)
);

$btn_primary   = $args['btn_primary'];
$btn_secondary = $args['btn_secondary'];

if (! is_array($btn_primary) || empty($btn_primary['url'])) {
	$btn_primary = array(
		'url'   => '#contacts',
		'title' => __('Оценить ремонт', 'ksenonspb'),
	);
}

if (! is_array($btn_secondary) || empty($btn_secondary['url'])) {
	$btn_secondary = array(
		'url'   => '#contacts',
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
			<?php ksenon_render_btn_arrow($btn_primary, 'btn btn--primary-inverse btn--large cta-bottom__btn', __('Оценить ремонт', 'ksenonspb')); ?>
			<a
				class="btn btn--white-outline btn--large cta-bottom__btn-secondary"
				href="<?php echo esc_url(ksenon_acf_link_url($btn_secondary)); ?>"
				<?php echo ksenon_acf_link_target($btn_secondary) ? ' target="' . esc_attr(ksenon_acf_link_target($btn_secondary)) . '"' : ''; ?>>
				<?php echo esc_html(ksenon_acf_link_title($btn_secondary)); ?>
			</a>
		</div>
	</div>
</section>