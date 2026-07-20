<?php

/**
 * Popups
 *
 * @package ksenonspb
 */

$order_title   = ksenon_get_option('popup_order_title');
$order_lead    = ksenon_get_option('popup_order_lead');
$success_title = ksenon_get_option('popup_success_title');
$error_title   = ksenon_get_option('popup_error_title');

?>

<div class="popups" hidden>
	<!-- Консультация -->
	<div id="popup-consultation" class="popup-modal popup-modal--consult">
		<div class="popup-modal__inner">
			<h2 class="popup-modal__title title title-popup"><?php esc_html_e('Свяжитесь с нами', 'ksenonspb'); ?></h2>
			<div class="popup-modal__content popup-modal__content--center">
				<div class="popup-modal__form">
					<?php ksenon_cf7_form('cf7_konsultaciya', __('Консультация (попап)', 'ksenonspb')); ?>
				</div>
			</div>
		</div>
	</div>
	<!-- Заказ -->
	<div id="popup-order" class="popup-modal popup-modal--order">
		<div class="popup-modal__inner">
			<div class="popup-modal__layout">
				<div class="popup-modal__content">
					<?php if ($order_title) : ?>
						<h2 class="popup-modal__title title title-popup"><?php echo esc_html($order_title); ?></h2>
					<?php endif; ?>
					<?php if ($order_lead) : ?>
						<p class="popup-modal__lead"><?php echo esc_html($order_lead); ?></p>
					<?php endif; ?>
					<div class="popup-modal__form">
						<?php ksenon_cf7_form('cf7_zakaz', __('Заказ (попап)', 'ksenonspb')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Презентация -->
	<div id="popup-presentation" class="popup-modal popup-modal--presentation">
		<div class="popup-modal__inner">
			<div class="popup-modal__layout popup-modal__layout--reverse">
				<div class="popup-modal__content">
					<div class="popup-modal__form">
						<?php ksenon_cf7_form('cf7_zakaz', __('Презентация (попап)', 'ksenonspb')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Запись к партнёрам -->
	<div id="popup-partners" class="popup-modal popup-modal--partners">
		<div class="popup-modal__inner">
			<?php
			$partners = ksenon_get_partners();
			if ($partners) :
			?>
				<div class="popup-modal__partners">
					<?php
					foreach ($partners as $partner) :
						$thumb_id = get_post_thumbnail_id($partner);
						if (! $thumb_id) {
							continue;
						}
						$link = ksenon_get_partner_link($partner->ID);
					?>
						<a class="popup-modal__partner" href="<?php echo esc_url($link ?: '#'); ?>" target="_blank" rel="noopener noreferrer">
							<?php
							echo ksenon_acf_image(
								$thumb_id,
								'full',
								array(
									'class'   => 'popup-modal__partner-logo',
									'alt'     => get_the_title($partner),
									'title'   => get_the_title($partner),
									'loading' => 'lazy',
								)
							);
							?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
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