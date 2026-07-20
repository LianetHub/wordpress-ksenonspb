<?php

/**
 * Legal pages: privacy policy & personal data consent.
 *
 * @package ksenonspb
 */

if (! defined('KSENON_PAGE_PRIVACY_POLICY')) {
	define('KSENON_PAGE_PRIVACY_POLICY', 3);
}

if (! defined('KSENON_PAGE_PERSONAL_DATA_CONSENT')) {
	define('KSENON_PAGE_PERSONAL_DATA_CONSENT', 3555);
}

/** Bump when legal HTML content files change to re-seed posts. */
if (! defined('KSENON_LEGAL_CONTENT_VERSION')) {
	define('KSENON_LEGAL_CONTENT_VERSION', 1);
}

if (! function_exists('ksenon_get_legal_page_ids')) {
	/**
	 * @return int[]
	 */
	function ksenon_get_legal_page_ids()
	{
		return array(
			(int) KSENON_PAGE_PRIVACY_POLICY,
			(int) KSENON_PAGE_PERSONAL_DATA_CONSENT,
		);
	}
}

if (! function_exists('ksenon_is_legal_page')) {
	/**
	 * @param int|null $post_id Optional post ID. Defaults to queried object.
	 */
	function ksenon_is_legal_page($post_id = null)
	{
		if (null === $post_id) {
			if (is_page_template('page-policy.php')) {
				return true;
			}
			if (function_exists('is_privacy_policy') && is_privacy_policy()) {
				return true;
			}
			$post_id = get_queried_object_id();
		}

		return in_array((int) $post_id, ksenon_get_legal_page_ids(), true);
	}
}

if (! function_exists('ksenon_get_legal_content_path')) {
	/**
	 * @param int $page_id Page ID.
	 * @return string Absolute path or empty string.
	 */
	function ksenon_get_legal_content_path($page_id)
	{
		$map = array(
			(int) KSENON_PAGE_PRIVACY_POLICY        => 'privacy-policy.html',
			(int) KSENON_PAGE_PERSONAL_DATA_CONSENT => 'consent-personal-data.html',
		);

		$page_id = (int) $page_id;
		if (! isset($map[$page_id])) {
			return '';
		}

		$path = KSENON_DIR . '/content/legal/' . $map[$page_id];

		return is_readable($path) ? $path : '';
	}
}

if (! function_exists('ksenon_get_legal_content_html')) {
	/**
	 * @param int $page_id Page ID.
	 * @return string
	 */
	function ksenon_get_legal_content_html($page_id)
	{
		$path = ksenon_get_legal_content_path($page_id);
		if (! $path) {
			return '';
		}

		$html = file_get_contents($path);
		if (false === $html) {
			return '';
		}

		$html = str_replace(
			array(
				'{{policy_url}}',
				'{{opd_url}}',
			),
			array(
				esc_url(ksenon_get_policy_url()),
				esc_url(ksenon_get_opd_url()),
			),
			$html
		);

		return trim($html);
	}
}

if (! function_exists('ksenon_seed_legal_pages')) {
	/**
	 * Assign policy template and sync post content from theme HTML files.
	 *
	 * @param bool $force Update even if version option already matches.
	 */
	function ksenon_seed_legal_pages($force = false)
	{
		$option_key = 'ksenon_legal_content_version';
		$current    = (int) get_option($option_key, 0);

		if (! $force && $current === (int) KSENON_LEGAL_CONTENT_VERSION) {
			return;
		}

		foreach (ksenon_get_legal_page_ids() as $page_id) {
			$post = get_post($page_id);
			if (! $post || 'page' !== $post->post_type) {
				continue;
			}

			update_post_meta($page_id, '_wp_page_template', 'page-policy.php');

			$content = ksenon_get_legal_content_html($page_id);
			if ('' === $content) {
				continue;
			}

			wp_update_post(
				array(
					'ID'           => $page_id,
					'post_content' => $content,
				)
			);
		}

		if ((int) KSENON_PAGE_PRIVACY_POLICY > 0 && get_post(KSENON_PAGE_PRIVACY_POLICY)) {
			update_option('wp_page_for_privacy_policy', (int) KSENON_PAGE_PRIVACY_POLICY);
		}

		update_option($option_key, (int) KSENON_LEGAL_CONTENT_VERSION);
	}
}

add_action(
	'init',
	static function () {
		if (wp_installing() || wp_doing_ajax() || wp_doing_cron()) {
			return;
		}
		ksenon_seed_legal_pages();
	},
	30
);

add_filter(
	'template_include',
	static function ($template) {
		if (! ksenon_is_legal_page()) {
			return $template;
		}

		$custom = locate_template('page-policy.php');

		return $custom ? $custom : $template;
	},
	20
);
