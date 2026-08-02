<?php

/**
 * Contact Form 7 integration
 *
 * @package ksenonspb
 */

// Отключаем автоматические <p> и <br> в разметке формы.
add_filter('wpcf7_autop_or_not', '__return_false');

/**
 * Контекст рендера CF7: источник формы, мета-поля и опции вывода.
 *
 * @param string $source Источник заявки (форма-source).
 * @param array  $args {
 *     @type string $submit_label Текст кнопки отправки (подмена value у submit).
 * }
 */
function ksenon_cf7_set_render_context($source = '', $args = array())
{
	$GLOBALS['ksenon_cf7_render_context'] = array(
		'form-source'   => '' !== $source ? $source : __('Форма с сайта', 'ksenonspb'),
		'form-page'     => ksenon_cf7_build_form_page_meta(),
		'form-time'     => (string) time(),
		'_submit_label' => sanitize_text_field((string) ($args['submit_label'] ?? '')),
	);
}

/**
 * Подменяет value у кнопки submit CF7.
 */
function ksenon_cf7_replace_submit_label($content, $label)
{
	$label = trim((string) $label);	

	if ('' === $label) {
		return $content;
	}

	$escaped = esc_attr($label);

	return (string) preg_replace_callback(
		'/<input\b(?=[^>]*\b(?:type=["\']submit["\']|class=["\'][^"\']*\bwpcf7-submit\b))[^>]*>/i',
		static function ($matches) use ($escaped) {
			$tag = $matches[0];

			if (preg_match('/\bvalue=/i', $tag)) {
				return (string) preg_replace('/\bvalue=(["\'])[^"\']*\1/i', 'value="' . $escaped . '"', $tag, 1);
			}

			return (string) preg_replace('/\s*\/?>$/', ' value="' . $escaped . '">', $tag);
		},
		$content,
		1
	);
}

function ksenon_cf7_clear_render_context()
{
	unset($GLOBALS['ksenon_cf7_render_context']);
}

function ksenon_cf7_get_render_context()
{
	return $GLOBALS['ksenon_cf7_render_context'] ?? null;
}

function ksenon_cf7_current_page_url()
{
	if (is_singular()) {
		return get_permalink();
	}

	$host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
	$uri  = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '/';

	if ($host && $uri) {
		return esc_url_raw((is_ssl() ? 'https' : 'http') . '://' . $host . $uri);
	}

	return esc_url(home_url('/'));
}

function ksenon_cf7_page_title_for_url($url)
{
	$post_id = url_to_postid($url);

	if ($post_id) {
		return get_the_title($post_id);
	}

	return get_bloginfo('name');
}

function ksenon_cf7_build_form_page_meta($url = null)
{
	if (null === $url) {
		$url = ksenon_cf7_current_page_url();
	} else {
		$url = esc_url($url);
	}

	return ksenon_cf7_page_title_for_url($url) . ' | ' . $url;
}

function ksenon_cf7_set_hidden_field($content, $name, $value)
{
	$escaped_name  = preg_quote($name, '/');
	$escaped_value = esc_attr($value);

	$pattern = '/(<input(?=[^>]*\bname="' . $escaped_name . '")[^>]*\bvalue=")([^"]*)("[^>]*>)/i';
	if (preg_match($pattern, $content)) {
		return preg_replace($pattern, '${1}' . $escaped_value . '${3}', $content);
	}

	$pattern_no_value = '/(<input(?=[^>]*\bname="' . $escaped_name . '")(?![^>]*\bvalue=)[^>]*)(>)/i';
	if (preg_match($pattern_no_value, $content)) {
		return preg_replace($pattern_no_value, '${1} value="' . $escaped_value . '${2}', $content);
	}

	return $content . sprintf(
		'<input type="hidden" name="%1$s" value="%2$s" class="wpcf7-form-control wpcf7-hidden" />',
		esc_attr($name),
		$escaped_value
	);
}

/**
 * Нормализует пути к SVG-спрайту.
 */
add_filter(
	'wpcf7_form_elements',
	function ($content) {
		$icons_uri = ksenon_assets_uri('img/icons.svg');
		$theme_uri = get_template_directory_uri();

		$content = str_replace(
			array(
				'@img/icons.svg',
				$theme_uri . '/img/icons.svg',
			),
			$icons_uri,
			$content
		);

		$policy_url = ksenon_get_policy_url();
		$opd_url    = ksenon_get_opd_url();

		$content = str_replace('%policy_url%', esc_url($policy_url), $content);
		$content = str_replace('%opd_url%', esc_url($opd_url), $content);

		$content = preg_replace(
			'/<span class="checkbox__box"[^>]*>\s*<svg[^>]*>.*?<\/svg>\s*<\/span>/s',
			'<span class="checkbox__box" aria-hidden="true"></span>',
			$content
		);

		return $content;
	}
);

/**
 * Заполняет скрытые мета-поля CF7 и подменяет текст кнопки при рендере формы.
 */
add_filter(
	'wpcf7_form_elements',
	function ($content) {
		$context = ksenon_cf7_get_render_context();

		if (! $context) {
			return $content;
		}

		foreach ($context as $name => $value) {
			if (str_starts_with((string) $name, '_')) {
				continue;
			}

			$content = ksenon_cf7_set_hidden_field($content, $name, $value);
		}

		$content = ksenon_cf7_replace_submit_label($content, $context['_submit_label'] ?? '');

		return $content;
	},
	20
);

function ksenon_cf7_get_submission_page_meta()
{
	$submission = WPCF7_Submission::get_instance();
	$posted     = $submission ? (array) $submission->get_posted_data() : array();

	if (! empty($posted['form-page'])) {
		$page_meta = (string) $posted['form-page'];

		if (str_contains($page_meta, ' | ')) {
			[$title, $url] = array_pad(explode(' | ', $page_meta, 2), 2, '');
			$title         = trim($title);
			$url           = trim($url);

			if ('' !== $title || '' !== $url) {
				return array(
					'title' => '' !== $title ? $title : ksenon_cf7_page_title_for_url($url),
					'url'   => '' !== $url ? esc_url($url) : '',
				);
			}
		}
	}

	$referer = wp_get_referer();

	if (! $referer && ! empty($_SERVER['HTTP_REFERER'])) {
		$referer = esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER']));
	}

	$url = $referer ? esc_url($referer) : esc_url(home_url('/'));

	return array(
		'title' => ksenon_cf7_page_title_for_url($url),
		'url'   => $url,
	);
}

function ksenon_cf7_field_value($value)
{
	if (is_array($value)) {
		$value = array_filter($value, static fn($item) => '' !== $item && null !== $item);

		return implode(', ', array_map('strval', $value));
	}

	return trim((string) $value);
}

/**
 * Человекочитаемое значение чекбокса согласия (acceptance agree).
 */
function ksenon_cf7_format_agree_value($value)
{
	$value = ksenon_cf7_field_value($value);

	if ('' === $value || '0' === $value) {
		return __('Не дано', 'ksenonspb');
	}

	if ('1' === $value || strcasecmp($value, 'on') === 0 || strcasecmp($value, 'yes') === 0) {
		return __('Одобрено', 'ksenonspb');
	}

	return $value;
}

add_action(
	'wpcf7_mail_failed',
	function ($contact_form) {
		error_log('CF7 mail failed for form #' . $contact_form->id());
	},
	10,
	1
);

/**
 * Логирует причину отказа wp_mail (SMTP / PHP mail).
 */
add_action(
	'wp_mail_failed',
	function ($error) {
		if (! is_wp_error($error)) {
			return;
		}

		error_log('wp_mail failed: ' . $error->get_error_message());

		$data = $error->get_error_data();

		if (is_array($data)) {
			error_log('wp_mail failed data: ' . wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
		}
	}
);

/**
 * Кастомные mail-теги: [_date_msk], [_url], [_post_title], [_post_url].
 */
add_filter(
	'wpcf7_special_mail_tags',
	function ($output, $name) {
		if ('_date_msk' === $name) {
			$tz = new DateTimeZone('Europe/Moscow');

			return (new DateTime('now', $tz))->format('d.m.Y H:i:s');
		}

		if ('_url' === $name) {
			$page_meta = ksenon_cf7_get_submission_page_meta();

			return $page_meta['url'];
		}

		if ('_post_title' === $name) {
			$page_meta = ksenon_cf7_get_submission_page_meta();

			return $page_meta['title'];
		}

		if ('_post_url' === $name) {
			$page_meta = ksenon_cf7_get_submission_page_meta();

			return $page_meta['url'];
		}

		return $output;
	},
	10,
	2
);

/**
 * Имя необязательно: text* → text при разборе тегов CF7.
 *
 * @param array $tag Разобранный тег формы.
 */
add_filter(
	'wpcf7_form_tag',
	static function ($tag) {
		if (! is_array($tag) || ($tag['name'] ?? '') !== 'your-name') {
			return $tag;
		}

		if (isset($tag['type']) && 'text*' === $tag['type']) {
			$tag['type'] = 'text';
		}

		if (! empty($tag['options']) && is_array($tag['options'])) {
			$tag['options'] = array_values(
				array_filter(
					$tag['options'],
					static function ($option) {
						return 'required' !== $option;
					}
				)
			);
		}

		return $tag;
	}
);

/**
 * Нормализует телефон до цифр (RU: 7/8 + 10 цифр).
 */
function ksenon_cf7_normalize_phone_digits($value)
{
	$digits = preg_replace('/\D+/', '', (string) $value);

	return is_string($digits) ? $digits : '';
}

/**
 * Проверяет, что телефон соответствует маске +7 / 8 и полному номеру.
 */
function ksenon_cf7_is_valid_ru_phone($value)
{
	$digits = ksenon_cf7_normalize_phone_digits($value);

	if (11 !== strlen($digits)) {
		return false;
	}

	return in_array($digits[0], array('7', '8'), true);
}

/**
 * Серверная валидация имени (без цифр; поле необязательное).
 *
 * @param WPCF7_Validation $result Результат валидации.
 * @param WPCF7_FormTag    $tag    Тег поля.
 */
function ksenon_cf7_validate_name($result, $tag)
{
	if ('your-name' !== $tag->name) {
		return $result;
	}

	$value = isset($_POST[ $tag->name ]) ? wp_unslash($_POST[ $tag->name ]) : '';
	$value = is_array($value) ? implode('', $value) : (string) $value;
	$value = trim($value);

	if ('' === $value) {
		return $result;
	}

	if (preg_match('/\d/u', $value)) {
		$result->invalidate(
			$tag,
			__('Имя не должно содержать цифры', 'ksenonspb')
		);
	}

	return $result;
}

add_filter('wpcf7_validate_text', 'ksenon_cf7_validate_name', 20, 2);
add_filter('wpcf7_validate_text*', 'ksenon_cf7_validate_name', 20, 2);

/**
 * Серверная валидация телефона (полный RU-номер).
 *
 * @param WPCF7_Validation $result Результат валидации.
 * @param WPCF7_FormTag    $tag    Тег поля.
 */
function ksenon_cf7_validate_tel($result, $tag)
{
	if ('your-phone' !== $tag->name) {
		return $result;
	}

	$value = isset($_POST[ $tag->name ]) ? wp_unslash($_POST[ $tag->name ]) : '';
	$value = is_array($value) ? implode('', $value) : (string) $value;
	$value = trim($value);

	if ('' === $value || ! ksenon_cf7_is_valid_ru_phone($value)) {
		$result->invalidate(
			$tag,
			__('Укажите корректный номер телефона в формате +7 (___) ___-__-__', 'ksenonspb')
		);
	}

	return $result;
}

add_filter('wpcf7_validate_tel', 'ksenon_cf7_validate_tel', 20, 2);
add_filter('wpcf7_validate_tel*', 'ksenon_cf7_validate_tel', 20, 2);

/**
 * Серверная валидация согласия на обработку ПД.
 *
 * @param WPCF7_Validation $result Результат валидации.
 * @param WPCF7_FormTag    $tag    Тег поля.
 */
function ksenon_cf7_validate_acceptance($result, $tag)
{
	if ('agree' !== $tag->name) {
		return $result;
	}

	$value = isset($_POST[ $tag->name ]) ? wp_unslash($_POST[ $tag->name ]) : '';

	if (is_array($value)) {
		$value = reset($value);
	}

	$value = is_string($value) ? strtolower(trim($value)) : '';

	$accepted = in_array($value, array('1', 'on', 'yes', 'true'), true);

	if (! $accepted) {
		$result->invalidate(
			$tag,
			__('Необходимо согласие на обработку персональных данных', 'ksenonspb')
		);
	}

	return $result;
}

add_filter('wpcf7_validate_acceptance', 'ksenon_cf7_validate_acceptance', 20, 2);

/**
 * Серверный fallback для скрытых мета-полей CF7.
 */
add_filter(
	'wpcf7_posted_data',
	function ($posted) {
		$referer = wp_get_referer();

		if (! $referer && ! empty($_SERVER['HTTP_REFERER'])) {
			$referer = esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER']));
		}

		$page_url = $referer ? esc_url($referer) : esc_url(home_url('/'));

		if (empty($posted['form-time'])) {
			$posted['form-time'] = (string) time();
		}

		if (empty($posted['form-page'])) {
			$posted['form-page'] = ksenon_cf7_build_form_page_meta($page_url);
		}

		if (empty($posted['form-source'])) {
			$posted['form-source'] = __('Форма с сайта', 'ksenonspb');
		}

		return $posted;
	}
);

/**
 * Форматирует номинал сертификата для письма.
 */
function ksenon_cf7_format_certificate_amount($value)
{
	$raw = ksenon_cf7_field_value($value);
	$raw = preg_replace('/[^\d.,]/', '', $raw);

	if ('' === $raw) {
		return '';
	}

	$raw    = str_replace(',', '.', $raw);
	$number = (float) $raw;

	if ($number <= 0) {
		return $raw;
	}

	return number_format($number, 0, ',', ' ') . ' ₽';
}

/**
 * Форматирование mail-тегов для писем.
 */
add_filter(
	'wpcf7_mail_tag_replaced',
	function ($replaced, $submitted, $html, $mail_tag) {
		if (! is_object($mail_tag)) {
			return $replaced;
		}

		$field = $mail_tag->field_name();

		if ('agree' === $field) {
			$formatted = ksenon_cf7_format_agree_value($submitted);

			return $html ? esc_html($formatted) : $formatted;
		}

		if ('certificate-amount' === $field) {
			$formatted = ksenon_cf7_format_certificate_amount($submitted);

			if ('' === $formatted) {
				return $html ? '&mdash;' : '—';
			}

			return $html ? esc_html($formatted) : $formatted;
		}

		if (in_array($field, array('your-name', 'your-message', 'your-car'), true)) {
			$value = ksenon_cf7_field_value($submitted);

			if ('' === $value) {
				return $html ? '&mdash;' : '—';
			}

			if ('your-message' === $field) {
				return $html ? nl2br(esc_html($value), false) : $value;
			}

			return $html ? esc_html($value) : $value;
		}

		return $replaced;
	},
	10,
	4
);

/**
 * Map popup forms to CF7 shortcodes (set in ACF Options).
 */
function ksenon_popup_cf7($key, $source = '', $default_shortcode = '', $submit_label = '')
{
	ksenon_cf7_form($key, $source, $default_shortcode, $submit_label);
}
