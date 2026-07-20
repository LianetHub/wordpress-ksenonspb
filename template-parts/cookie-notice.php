<?php

/**
 * Cookie consent notice (FZ-152).
 *
 * @package ksenonspb
 */

$cookies_url = function_exists('ksenon_get_cookies_policy_url')
	? ksenon_get_cookies_policy_url()
	: home_url('/politika-v-otnoshenii-cookie/');

$has_consent = isset($_COOKIE['ksenon_cookie_consent']);
?>

<div
	id="cookie-notice"
	class="cookie-notice<?php echo $has_consent ? ' cookie-notice--has-consent' : ''; ?>"
	role="dialog"
	aria-labelledby="cookie-notice-title"
	aria-describedby="cookie-notice-text"
	<?php echo $has_consent ? 'hidden' : ''; ?>>
	<div class="cookie-notice__panel cookie-notice__panel--main" data-cookie-panel="main">
		<div class="cookie-notice__body">
			<p id="cookie-notice-title" class="cookie-notice__title">
				<?php esc_html_e('Мы используем файлы cookie', 'ksenonspb'); ?>
			</p>
			<p id="cookie-notice-text" class="cookie-notice__text">
				<?php
				printf(
					/* translators: %s: cookie policy URL */
					wp_kses(
						__('Мы используем файлы cookie, чтобы сайт работал корректно, а также — с вашего согласия — для аналитики. Подробнее в <a href="%s" target="_blank" rel="noopener noreferrer">Политике в отношении cookie</a>.', 'ksenonspb'),
						array(
							'a' => array(
								'href'   => true,
								'target' => true,
								'rel'    => true,
							),
						)
					),
					esc_url($cookies_url)
				);
				?>
			</p>
		</div>
		<div class="cookie-notice__actions">
			<button type="button" class="cookie-notice__btn cookie-notice__btn--accept" data-cookie-action="accept-all">
				<?php esc_html_e('Принять все', 'ksenonspb'); ?>
			</button>
			<button type="button" class="cookie-notice__btn cookie-notice__btn--necessary" data-cookie-action="necessary-only">
				<?php esc_html_e('Только необходимые', 'ksenonspb'); ?>
			</button>
			<button type="button" class="cookie-notice__btn cookie-notice__btn--settings" data-cookie-action="open-settings">
				<?php esc_html_e('Настройки', 'ksenonspb'); ?>
			</button>
		</div>
	</div>

	<div class="cookie-notice__panel cookie-notice__panel--settings" data-cookie-panel="settings" hidden>
		<div class="cookie-notice__body">
			<p class="cookie-notice__title">
				<?php esc_html_e('Настройки cookie', 'ksenonspb'); ?>
			</p>
			<p class="cookie-notice__text">
				<?php esc_html_e('Выберите категории cookie, которые разрешаете использовать. Необходимые cookie всегда включены — без них сайт не сможет работать корректно.', 'ksenonspb'); ?>
			</p>
			<ul class="cookie-notice__categories">
				<li class="cookie-notice__category">
					<label class="checkbox cookie-notice__checkbox">
						<input
							class="checkbox__input"
							type="checkbox"
							name="cookie_necessary"
							checked
							disabled
							data-cookie-category="necessary">
						<span class="checkbox__box" aria-hidden="true"></span>
						<span class="checkbox__text">
							<strong><?php esc_html_e('Необходимые', 'ksenonspb'); ?></strong>
							<span class="cookie-notice__category-desc"><?php esc_html_e('Технические cookie для работы сайта, форм и сохранения вашего выбора.', 'ksenonspb'); ?></span>
						</span>
					</label>
				</li>
				<li class="cookie-notice__category">
					<label class="checkbox cookie-notice__checkbox">
						<input
							class="checkbox__input"
							type="checkbox"
							name="cookie_analytics"
							data-cookie-category="analytics">
						<span class="checkbox__box" aria-hidden="true"></span>
						<span class="checkbox__text">
							<strong><?php esc_html_e('Аналитические', 'ksenonspb'); ?></strong>
							<span class="cookie-notice__category-desc"><?php esc_html_e('Помогают понять, как используют сайт (например, Яндекс.Метрика).', 'ksenonspb'); ?></span>
						</span>
					</label>
				</li>
				<li class="cookie-notice__category">
					<label class="checkbox cookie-notice__checkbox">
						<input
							class="checkbox__input"
							type="checkbox"
							name="cookie_marketing"
							data-cookie-category="marketing">
						<span class="checkbox__box" aria-hidden="true"></span>
						<span class="checkbox__text">
							<strong><?php esc_html_e('Маркетинговые', 'ksenonspb'); ?></strong>
							<span class="cookie-notice__category-desc"><?php esc_html_e('Рекламные и маркетинговые трекеры. Сейчас не используются.', 'ksenonspb'); ?></span>
						</span>
					</label>
				</li>
			</ul>
		</div>
		<div class="cookie-notice__actions">
			<button type="button" class="cookie-notice__btn cookie-notice__btn--accept" data-cookie-action="save-settings">
				<?php esc_html_e('Сохранить', 'ksenonspb'); ?>
			</button>
			<button type="button" class="cookie-notice__btn cookie-notice__btn--settings" data-cookie-action="back">
				<?php esc_html_e('Назад', 'ksenonspb'); ?>
			</button>
		</div>
	</div>
</div>