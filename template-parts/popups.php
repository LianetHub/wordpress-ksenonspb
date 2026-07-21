<?php

/**
 * Popups
 *
 * @package ksenonspb
 */

$success_title       = ksenon_get_option('popup_success_title');
$error_title         = ksenon_get_option('popup_error_title');
$installment_title   = ksenon_get_option('popup_installment_title') ?: __('Оформить рассрочку', 'ksenonspb');
$installment_lead    = ksenon_get_option('popup_installment_lead');
$certificate_title   = ksenon_get_option('popup_certificate_title') ?: __('Оформить сертификат', 'ksenonspb');
$certificate_lead    = ksenon_get_option('popup_certificate_lead');

?>

<div class="popups" hidden>
	<!-- Связаться с нами -->
	<div id="popup-consultation" class="popup-modal popup-modal--consult">
		<div class="popup-modal__inner">
			<header class="popup-modal__header">
				<h2 class="popup-modal__title title title-popup"><?php esc_html_e('Свяжитесь с нами', 'ksenonspb'); ?></h2>
				<p class="popup-modal__lead"><?php esc_html_e('Оставьте контакты — перезвоним и ответим на вопросы', 'ksenonspb'); ?></p>
			</header>
			<div class="popup-modal__content">
				<div class="popup-modal__form">
					<?php ksenon_cf7_form('cf7_zakaz', __('Заявка (попап)', 'ksenonspb')); ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Рассрочка -->
	<div id="popup-installment" class="popup-modal popup-modal--consult">
		<div class="popup-modal__inner">
			<header class="popup-modal__header">
				<h2 class="popup-modal__title title title-popup"><?php echo esc_html((string) $installment_title); ?></h2>
				<?php if ($installment_lead) : ?>
					<p class="popup-modal__lead"><?php echo esc_html((string) $installment_lead); ?></p>
				<?php endif; ?>
			</header>
			<div class="popup-modal__content">
				<div class="popup-modal__form">
					<?php ksenon_cf7_form('cf7_installment', __('Рассрочка (попап)', 'ksenonspb')); ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Сертификат -->
	<div id="popup-certificate" class="popup-modal popup-modal--consult">
		<div class="popup-modal__inner">
			<header class="popup-modal__header">
				<h2 class="popup-modal__title title title-popup"><?php echo esc_html((string) $certificate_title); ?></h2>
				<?php if ($certificate_lead) : ?>
					<p class="popup-modal__lead"><?php echo esc_html((string) $certificate_lead); ?></p>
				<?php endif; ?>
			</header>
			<div class="popup-modal__content">
				<div class="popup-modal__form">
					<?php ksenon_cf7_form('cf7_certificate', __('Сертификат (попап)', 'ksenonspb')); ?>
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
	<div id="popup-error" class="popup-modal popup-modal--status popup-modal--status-error">
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
