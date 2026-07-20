<?php

/**
 * Format helpers (titles, prices)
 *
 * @package ksenonspb
 */

if (! function_exists('ksenon_title_accent_html')) {
	function ksenon_title_accent_html($title, $accent_class = 'color-accent')
	{
		$title = trim((string) $title);
		if ('' === $title) {
			return '';
		}

		if (false !== strpos($title, '<')) {
			if (function_exists('ksenon_kses_inline')) {
				return nl2br(ksenon_kses_inline($title)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			return esc_html(wp_strip_all_tags($title));
		}

		$parts = preg_split('/\s+/u', $title);
		if (count($parts) < 2) {
			return esc_html($title);
		}

		$accent = array_pop($parts);

		return esc_html(implode(' ', $parts)) . ' <span class="' . esc_attr($accent_class) . '">' . esc_html($accent) . '</span>';
	}
}

if (! function_exists('ksenon_plural_ru')) {
	/**
	 * Russian plural form by count.
	 *
	 * @param int    $count Number.
	 * @param string $one   1, 21, 31…
	 * @param string $few   2–4, 22–24…
	 * @param string $many  0, 5–20, 25–30…
	 */
	function ksenon_plural_ru($count, $one, $few, $many)
	{
		$count = abs((int) $count) % 100;
		$mod10 = $count % 10;

		if ($count >= 11 && $count <= 19) {
			return $many;
		}
		if (1 === $mod10) {
			return $one;
		}
		if ($mod10 >= 2 && $mod10 <= 4) {
			return $few;
		}

		return $many;
	}
}

if (! function_exists('ksenon_services_count_label')) {
	function ksenon_services_count_label($count)
	{
		return sprintf(
			/* translators: 1: count, 2: услуга/услуги/услуг */
			__('Все %1$d %2$s', 'ksenonspb'),
			(int) $count,
			ksenon_plural_ru(
				$count,
				__('услуга', 'ksenonspb'),
				__('услуги', 'ksenonspb'),
				__('услуг', 'ksenonspb')
			)
		);
	}
}

if (! function_exists('ksenon_brands_count_label')) {
	function ksenon_brands_count_label($count)
	{
		$count = (int) $count;
		if ($count <= 0) {
			return __('Все марки', 'ksenonspb');
		}

		return sprintf(
			/* translators: 1: count, 2: марка/марки/марок */
			__('%1$d+ %2$s', 'ksenonspb'),
			$count,
			ksenon_plural_ru(
				$count,
				__('марка', 'ksenonspb'),
				__('марки', 'ksenonspb'),
				__('марок', 'ksenonspb')
			)
		);
	}
}

if (! function_exists('ksenon_portfolio_works_label')) {
	function ksenon_portfolio_works_label($count)
	{
		$count = (int) $count;

		return sprintf(
			/* translators: 1: count, 2: работа/работы/работ */
			__('Смотреть %1$d %2$s', 'ksenonspb'),
			$count,
			ksenon_plural_ru(
				$count,
				__('работу', 'ksenonspb'),
				__('работы', 'ksenonspb'),
				__('работ', 'ksenonspb')
			)
		);
	}
}

if (! function_exists('ksenon_format_price_from')) {
	function ksenon_format_price_from($value)
	{
		if (is_numeric($value)) {
			$amount = (int) $value;
		} else {
			$digits = preg_replace('/\D/u', '', (string) $value);
			if ('' === $digits) {
				return '';
			}
			$amount = (int) $digits;
		}

		if ($amount <= 0) {
			return '';
		}

		$nbsp = "\xc2\xa0";

		return wp_kses(
			sprintf(
				'<small>%1$s</small> <span>%2$s</span> <small>%3$s</small>',
				esc_html__('от', 'ksenonspb'),
				esc_html(number_format($amount, 0, '', $nbsp)),
				'₽'
			),
			array(
				'small' => array(),
				'span'  => array(),
			)
		);
	}
}
