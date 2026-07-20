<?php

/**
 * Popups
 *
 * @package ksenonspb
 */

$success_title = ksenon_get_option('popup_success_title');
$error_title   = ksenon_get_option('popup_error_title');

?>

<div class="popups" hidden>
	<!-- Связаться с нами -->
	<div id="popup-consultation" class="popup-modal popup-modal--consult">
		<div class="popup-modal__inner">
			<h2 class="popup-modal__title title title-popup"><?php esc_html_e('Свяжитесь с нами', 'ksenonspb'); ?></h2>
			<div class="popup-modal__content popup-modal__content--center">
				<div class="popup-modal__form">
					<?php ksenon_cf7_form('cf7_zakaz', __('Заявка (попап)', 'ksenonspb')); ?>
				</div>
			</div>
		</div>
	</div>


	<!-- Заявка отправлена -->
	<div id="popup-success" class="popup-modal popup-modal--status">
		<div class="popup-modal__inner">
			<div class="popup-modal__status">
				<svg class="popup-modal__status-icon icon" width="28" height="28" aria-hidden="true">
					<use href="<?php echo esc_url(ksenon_assets_uri('img/icons.svg')); ?>#icon-check-circle"></use>
				</svg>
				<?php if ($success_title) : ?>
					<h2 class="popup-modal__status-title"><?php echo wp_kses_post($success_title); ?></h2>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<!-- Ошибка -->
	<div id="popup-error" class="popup-modal popup-modal--status">
		<div class="popup-modal__inner">
			<div class="popup-modal__status">
				<svg class="popup-modal__status-icon popup-modal__status-icon--error icon" width="28" height="28" aria-hidden="true">
					<use href="<?php echo esc_url(ksenon_assets_uri('img/icons.svg')); ?>#icon-close-circle"></use>
				</svg>
				<?php if ($error_title) : ?>
					<h2 class="popup-modal__status-title"><?php echo wp_kses_post($error_title); ?></h2>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>