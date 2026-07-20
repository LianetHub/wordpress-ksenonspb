<?php

/**
 * CTA + contacts block
 *
 * @package ksenonspb
 *
 * @var array $args { @type string $title Optional title override. }
 */

$args = wp_parse_args(
	isset($args) && is_array($args) ? $args : array(),
	array(
		'title' => '',
	)
);

$title_default = 'Оценим ваш случай за <span class="color-accent">1 рабочий день</span>';
$title         = '' !== trim((string) $args['title'])
	? (string) $args['title']
	: (string) ksenon_get_option('cta_contacts_title', $title_default);

$lead = (string) ksenon_get_option(
	'cta_contacts_lead',
	__('Прикрепите фото фары — пришлём предварительную смету и сроки. Без обязательств, без звонков с продажами.', 'ksenonspb')
);

$messenger_label = (string) ksenon_get_option(
	'cta_contacts_messenger_label',
	'Не любите <span class="color-accent">формы?</span>'
);

$messenger_title = (string) ksenon_get_option(
	'cta_contacts_messenger_title',
	'Напишите в мессенджер — ответим за <span class="color-accent">15 минут</span>'
);

$messenger_text = (string) ksenon_get_option(
	'cta_contacts_messenger_text',
	__('В рабочее время. Пришлите фото — оценим без долгих переписок.', 'ksenonspb')
);

$car_image = ksenon_get_option('cta_contacts_car_image');
$phones    = ksenon_get_phones();
$addr      = ksenon_get_option('address');
$hours     = ksenon_get_option('hours');

$hours_lines = array_values(
	array_filter(
		array_map('trim', preg_split('/\R/u', (string) $hours)),
		static function ($line) {
			return '' !== $line;
		}
	)
);
$hours_main  = $hours_lines[0] ?? '';
$hours_extra = array_slice($hours_lines, 1);
?>
<section class="cta-contacts" id="contacts">
	<div class="cta-contacts__wrapper">
		<?php if ($car_image) : ?>
			<div class="cta-contacts__car" aria-hidden="true">
				<?php
				echo ksenon_acf_image(
					$car_image,
					'full',
					array(
						'class'   => 'cta-contacts__car-img',
						'loading' => 'lazy',
						'alt'     => '',
					)
				);
				?>
			</div>
		<?php endif; ?>

		<div class="cta-contacts__inner">
			<div class="cta-contacts__layout">
				<div class="cta-contacts__form-col">
					<?php if ($title) : ?>
						<h2 class="cta-contacts__title title-md"><?php echo nl2br(ksenon_kses_inline($title)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																	?></h2>
					<?php endif; ?>
					<?php if ($lead) : ?>
						<p class="cta-contacts__lead"><?php echo esc_html($lead); ?></p>
					<?php endif; ?>
					<div class="cta-contacts__form">
						<?php ksenon_cf7_form('cf7_konsultaciya', __('CTA + контакты', 'ksenonspb')); ?>
					</div>
				</div>

				<div class="cta-contacts__aside">
					<div class="cta-contacts__messengers">
						<?php if ($messenger_label) : ?>
							<p class="cta-contacts__messengers-label"><?php echo ksenon_kses_inline($messenger_label); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																		?></p>
						<?php endif; ?>
						<?php if ($messenger_title) : ?>
							<h3 class="cta-contacts__messengers-title"><?php echo nl2br(ksenon_kses_inline($messenger_title)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																		?></h3>
						<?php endif; ?>
						<?php if ($messenger_text) : ?>
							<p class="cta-contacts__messengers-text"><?php echo esc_html($messenger_text); ?></p>
						<?php endif; ?>
						<?php ksenon_render_messenger_links('cta-contacts__messenger-links messenger-links'); ?>
					</div>

					<div class="cta-contacts__info">
						<?php if ($addr) : ?>
							<div class="cta-contacts__info-item">
								<div class="cta-contacts__info-row">
									<span class="cta-contacts__info-icon"><?php ksenon_icon('icon-location', 28, 28, 'cta-contacts__info-icon-svg'); ?></span>
									<div class="cta-contacts__info-body">
										<span class="cta-contacts__info-label"><?php esc_html_e('Адрес', 'ksenonspb'); ?></span>
										<p class="cta-contacts__info-value"><?php echo nl2br(esc_html($addr)); ?></p>
									</div>
								</div>
							</div>
						<?php endif; ?>

						<?php if ($addr && ($hours_main || $phones)) : ?>
							<div class="cta-contacts__info-divider" aria-hidden="true"></div>
						<?php endif; ?>

						<?php if ($hours_main) : ?>
							<div class="cta-contacts__info-item">
								<div class="cta-contacts__info-row">
									<span class="cta-contacts__info-icon"><?php ksenon_icon('icon-clock', 28, 28, 'cta-contacts__info-icon-svg'); ?></span>
									<div class="cta-contacts__info-body">
										<span class="cta-contacts__info-label"><?php esc_html_e('Часы работы', 'ksenonspb'); ?></span>
										<p class="cta-contacts__info-value"><?php echo esc_html($hours_main); ?></p>
										<?php foreach ($hours_extra as $hours_line) : ?>
											<p class="cta-contacts__info-note"><?php echo esc_html($hours_line); ?></p>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						<?php endif; ?>

						<?php if ($hours_main && $phones) : ?>
							<div class="cta-contacts__info-divider" aria-hidden="true"></div>
						<?php endif; ?>

						<?php if ($phones) : ?>
							<div class="cta-contacts__info-item">
								<div class="cta-contacts__info-row">
									<span class="cta-contacts__info-icon"><?php ksenon_icon('icon-phone-outline', 26, 26, 'cta-contacts__info-icon-svg'); ?></span>
									<div class="cta-contacts__info-body">
										<span class="cta-contacts__info-label"><?php esc_html_e('Телефон', 'ksenonspb'); ?></span>
										<?php foreach ($phones as $phone) : ?>
											<a class="cta-contacts__info-value cta-contacts__phone" href="tel:+<?php echo esc_attr(ksenon_phone_clean($phone)); ?>">
												<?php echo esc_html($phone); ?>
											</a>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>