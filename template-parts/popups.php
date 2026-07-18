<?php

/**
 * Popups
 *
 * @package ksenonspb
 */

$consult_title      = ksenon_get_option('popup_consult_title');
$consult_lead       = ksenon_get_option('popup_consult_lead');
$order_title        = ksenon_get_option('popup_order_title');
$order_lead         = ksenon_get_option('popup_order_lead');
$order_image_1      = ksenon_get_option_raw('popup_order_image_1');
$order_image_2      = ksenon_get_option_raw('popup_order_image_2');
$presentation_title = ksenon_get_option('popup_presentation_title');
$presentation_lead  = ksenon_get_option('popup_presentation_lead');
$presentation_image = ksenon_get_option_raw('popup_presentation_image');
$partners_title     = ksenon_get_option('popup_partners_title');
$partners_lead      = ksenon_get_option('popup_partners_lead');
$partners_action    = ksenon_get_option('popup_partners_action');
$success_title      = ksenon_get_option('popup_success_title');
$error_title        = ksenon_get_option('popup_error_title');

?>

<div class="popups" hidden>
	<!-- Консультация -->
	<div id="popup-consultation" class="popup-modal popup-modal--consult">
		<div class="popup-modal__inner">
			<?php if ($consult_title) : ?>
				<h2 class="popup-modal__title title title-popup"><?php echo esc_html($consult_title); ?></h2>
			<?php endif; ?>
			<?php if ($consult_lead) : ?>
				<p class="popup-modal__lead"><?php echo esc_html($consult_lead); ?></p>
			<?php endif; ?>
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
				<?php if ($order_image_1 || $order_image_2) : ?>
					<div class="popup-modal__visual" aria-hidden="true">
						<?php if ($order_image_1) : ?>
							<div class="popup-modal__photo">
								<?php
								echo ksenon_acf_image(
									$order_image_1,
									'full',
									array(
										'alt'     => '',
										'width'   => '182',
										'height'  => '169',
										'loading' => 'lazy',
									)
								);
								?>
							</div>
						<?php endif; ?>
						<?php if ($order_image_2) : ?>
							<div class="popup-modal__photo">
								<?php
								echo ksenon_acf_image(
									$order_image_2,
									'full',
									array(
										'alt'     => '',
										'width'   => '182',
										'height'  => '169',
										'loading' => 'lazy',
									)
								);
								?>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<!-- Презентация -->
	<div id="popup-presentation" class="popup-modal popup-modal--presentation">
		<div class="popup-modal__inner">
			<div class="popup-modal__layout popup-modal__layout--reverse">
				<?php if ($presentation_image) : ?>
					<div class="popup-modal__media">
						<?php
						echo ksenon_acf_image(
							$presentation_image,
							'full',
							array(
								'width'   => '327',
								'height'  => '372',
								'loading' => 'lazy',
							)
						);
						?>
					</div>
				<?php endif; ?>
				<div class="popup-modal__content">
					<?php if ($presentation_title) : ?>
						<h2 class="popup-modal__title title title-popup"><?php echo wp_kses_post($presentation_title); ?></h2>
					<?php endif; ?>
					<?php if ($presentation_lead) : ?>
						<p class="popup-modal__lead"><?php echo esc_html($presentation_lead); ?></p>
					<?php endif; ?>
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
			<?php if ($partners_title) : ?>
				<h2 class="popup-modal__title title title-popup"><?php echo esc_html($partners_title); ?></h2>
			<?php endif; ?>
			<?php if ($partners_lead) : ?>
				<p class="popup-modal__lead"><?php echo esc_html($partners_lead); ?></p>
			<?php endif; ?>
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
							<?php if ($partners_action) : ?>
								<span class="popup-modal__partner-action">
									<?php echo esc_html($partners_action); ?>
									<svg class="icon popup-modal__partner-icon" width="24" height="24" aria-hidden="true">
										<use href="<?php echo esc_url(ksenon_assets_uri('img/icons.svg')); ?>#icon-arrow-up-right"></use>
									</svg>
								</span>
							<?php endif; ?>
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