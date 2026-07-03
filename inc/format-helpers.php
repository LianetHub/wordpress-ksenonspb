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
