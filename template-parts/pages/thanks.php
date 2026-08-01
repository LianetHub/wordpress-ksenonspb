<?php

/**
 * Thank-you page after form submit
 *
 * @package ksenonspb
 */

$phones         = ksenon_get_phones();
$portfolio_url  = function_exists('ksenon_portfolio_archive_url') ? ksenon_portfolio_archive_url() : home_url('/portfolio/');
$image_url      = ksenon_assets_uri('img/hero-headlight.webp');
$success_title  = (string) ksenon_get_option('popup_success_title');
$title          = $success_title !== '' ? wp_strip_all_tags($success_title) : __('Спасибо! Ваша заявка отправлена', 'ksenonspb');
?>
<section class="thanks">
	<div class="thanks__container container">
		<div class="thanks__media">
			<img
				class="thanks__image"
				src="<?php echo esc_url($image_url); ?>"
				alt="<?php echo esc_attr__('Фары с ангельскими глазками', 'ksenonspb'); ?>"
				width="640"
				height="480"
				loading="eager"
				decoding="async">
		</div>

		<div class="thanks__content">
			<div class="thanks__badge" aria-hidden="true">
				<svg class="thanks__badge-icon icon" width="28" height="28">
					<use href="<?php echo esc_url(ksenon_assets_uri('img/icons.svg')); ?>#icon-check-circle"></use>
				</svg>
			</div>

			<h1 class="thanks__title title-lg"><?php echo esc_html($title); ?></h1>

			<p class="thanks__lead">
				<?php esc_html_e('Свяжемся с вами в течение 15 минут в рабочее время. Можете сразу написать в мессенджер или позвонить.', 'ksenonspb'); ?>
			</p>

			<?php if ($phones) : ?>
				<div class="thanks__phones">
					<?php foreach ($phones as $phone) : ?>
						<a class="thanks__phone" href="tel:+<?php echo esc_attr(ksenon_phone_clean($phone)); ?>">
							<?php echo esc_html($phone); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php ksenon_render_messenger_links('thanks__messengers messenger-links'); ?>

			<div class="thanks__actions">
				<a class="btn btn--primary btn--large thanks__btn" href="<?php echo esc_url($portfolio_url); ?>">
					<span class="btn__text"><?php esc_html_e('Пока ждёте — посмотрите работы', 'ksenonspb'); ?></span>
					<span class="btn__arrow" aria-hidden="true">
						<svg class="btn__arrow-icon" width="15" height="10" aria-hidden="true">
							<use href="<?php echo esc_url(ksenon_assets_uri('img/icons.svg')); ?>#icon-arrow-right"></use>
						</svg>
					</span>
				</a>
				<a class="btn btn--secondary thanks__btn-secondary" href="<?php echo esc_url(home_url('/')); ?>">
					<span class="btn__text"><?php esc_html_e('На главную', 'ksenonspb'); ?></span>
				</a>
			</div>
		</div>
	</div>
</section>
