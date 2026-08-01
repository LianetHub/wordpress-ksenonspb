<?php

/**
 * CTA form block
 *
 * @package ksenonspb
 *
 * @var array $args {
 *   @type string $variant      Ключ варианта из ksenon_cta_form_config().
 *   @type string $submit_label Опциональный текст кнопки (перекрывает конфиг варианта).
 * }
 */

$variant = (string) ($args['variant'] ?? 'service_not_found');
$config  = ksenon_cta_form_config($variant);

if (! empty($args['submit_label'])) {
	$config['submit_label'] = (string) $args['submit_label'];
}
?>
<section class="cta-form cta-form--<?php echo esc_attr($variant); ?>">
	<div class="cta-form__container container container--large">
		<div class="cta-form__box">
			<h2 class="cta-form__title title-lg"><?php echo esc_html($config['title']); ?></h2>
			<div
				class="cta-form__body"
				data-form-variant="<?php echo esc_attr($variant); ?>"
				data-submit-label="<?php echo esc_attr($config['submit_label'] ?? ''); ?>"
			>
				<?php ksenon_cf7_form($config['cf7_option'], $config['form_source'], '', $config['submit_label'] ?? ''); ?>
			</div>
		</div>
	</div>
</section>