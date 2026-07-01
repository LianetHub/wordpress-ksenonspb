<?php

/**
 * CTA + contacts block
 *
 * @package ksenonspb
 *
 * @var array $args { @type string $title }
 */

$args = wp_parse_args(
	isset($args) && is_array($args) ? $args : array(),
	array(
		'title' => __('Оценим ваш случай за 1 рабочий день', 'ksenonspb'),
	)
);

$phones = ksenon_get_phones();
$email  = ksenon_get_option('email');
$addr   = ksenon_get_option('address');
$hours  = ksenon_get_option('hours');
$map    = ksenon_get_map_settings();
?>
<section class="cta-contacts request" id="contacts">
	<div class="cta-contacts__box request__box">
		<div class="cta-contacts__container request__container container">
			<div class="cta-contacts__layout">
				<div class="cta-contacts__form-col">
					<?php if ($args['title']) : ?>
						<h2 class="cta-contacts__title title-md"><?php echo esc_html($args['title']); ?></h2>
					<?php endif; ?>
					<p class="cta-contacts__lead">
						<?php esc_html_e('Прикрепите фото фары — пришлём предварительную смету и сроки. Без обязательств, без звонков с продажами.', 'ksenonspb'); ?>
					</p>
					<div class="cta-contacts__form">
						<?php ksenon_cf7_form('cf7_konsultaciya', __('CTA + контакты', 'ksenonspb')); ?>
					</div>
				</div>

				<div class="cta-contacts__aside">
					<div class="cta-contacts__messengers contacts__card">
						<p class="cta-contacts__messengers-label"><?php esc_html_e('Не любите формы?', 'ksenonspb'); ?></p>
						<h3 class="cta-contacts__messengers-title"><?php esc_html_e('Напишите в мессенджер — ответим за 15 минут', 'ksenonspb'); ?></h3>
						<p class="cta-contacts__messengers-text"><?php esc_html_e('В рабочее время. Пришлите фото — оценим без долгих переписок.', 'ksenonspb'); ?></p>
						<?php ksenon_render_messenger_links('cta-contacts__messenger-links'); ?>
					</div>

					<div class="cta-contacts__info contacts__card">
						<?php
						$map_link = '';
						if (! empty($map['coords'])) {
							$parts = array_map('trim', explode(',', (string) $map['coords']));
							if (count($parts) >= 2) {
								$map_link = 'https://yandex.ru/maps/?ll=' . rawurlencode($parts[1] . ',' . $parts[0]) . '&z=16&pt=' . rawurlencode($parts[1] . ',' . $parts[0]);
							}
						}
						?>
						<?php if ($addr) : ?>
							<div class="cta-contacts__info-item">
								<span class="cta-contacts__info-label"><?php esc_html_e('Адрес', 'ksenonspb'); ?></span>
								<p class="cta-contacts__info-value"><?php echo nl2br(esc_html($addr)); ?></p>
								<?php if ($map_link) : ?>
									<a class="cta-contacts__info-link" href="<?php echo esc_url($map_link); ?>" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e('Открыть на карте →', 'ksenonspb'); ?>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ($hours) : ?>
							<div class="cta-contacts__info-item">
								<span class="cta-contacts__info-label"><?php esc_html_e('Часы работы', 'ksenonspb'); ?></span>
								<p class="cta-contacts__info-value"><?php echo nl2br(esc_html($hours)); ?></p>
							</div>
						<?php endif; ?>

						<?php if ($phones) : ?>
							<div class="cta-contacts__info-item">
								<span class="cta-contacts__info-label"><?php esc_html_e('Телефон', 'ksenonspb'); ?></span>
								<?php foreach ($phones as $phone) : ?>
									<a class="cta-contacts__info-value cta-contacts__phone" href="tel:+<?php echo esc_attr(ksenon_phone_clean($phone)); ?>">
										<?php echo esc_html($phone); ?>
									</a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<?php if ($email) : ?>
							<a class="cta-contacts__email" href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
						<?php endif; ?>
					</div>

					<div class="cta-contacts__map">
						<?php echo ksenon_get_map_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>