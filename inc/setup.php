<?php

/**
 * Theme setup
 *
 * @package ksenonspb
 */

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain('ksenonspb', KSENON_DIR . '/languages');

		add_theme_support('title-tag');
		add_theme_support('post-thumbnails');
		add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));

		register_nav_menus(
			array(
				'footer_menu' => esc_html__('Footer Menu (fallback)', 'ksenonspb'),
			)
		);
	}
);

if (! function_exists('ksenon_is_web_manifest_request')) {
	function ksenon_is_web_manifest_request()
	{
		if (empty($_SERVER['REQUEST_URI'])) {
			return false;
		}

		$request_path  = (string) wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH);
		$manifest_path = (string) wp_parse_url(home_url('/site.webmanifest'), PHP_URL_PATH);

		return untrailingslashit($request_path) === untrailingslashit($manifest_path);
	}
}

if (! function_exists('ksenon_serve_web_manifest')) {
	function ksenon_serve_web_manifest()
	{
		if (! function_exists('ksenon_get_web_manifest_data')) {
			status_header(404);
			exit;
		}

		header('Content-Type: application/manifest+json; charset=utf-8');
		echo wp_json_encode(
			ksenon_get_web_manifest_data(),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		);
		exit;
	}
}

add_action(
	'init',
	function () {
		add_rewrite_rule('^site\.webmanifest$', 'index.php?ksenon_webmanifest=1', 'top');

		// Fallback when rewrite rules were not flushed yet.
		if (ksenon_is_web_manifest_request()) {
			ksenon_serve_web_manifest();
		}
	}
);

add_filter(
	'query_vars',
	function ($vars) {
		$vars[] = 'ksenon_webmanifest';

		return $vars;
	}
);

add_action(
	'after_switch_theme',
	function () {
		flush_rewrite_rules();
	}
);

add_action(
	'template_redirect',
	function () {
		if (! get_query_var('ksenon_webmanifest')) {
			return;
		}

		ksenon_serve_web_manifest();
	}
);

add_filter(
	'upload_mimes',
	function ($mimes) {
		$mimes['svg']         = 'image/svg+xml';
		$mimes['ico']         = 'image/x-icon';
		$mimes['webmanifest'] = 'application/manifest+json';

		return $mimes;
	}
);

add_filter(
	'wp_check_filetype_and_ext',
	function ($data, $file, $filename, $mimes) {
		if (! empty($data['ext']) && ! empty($data['type'])) {
			return $data;
		}

		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		if ('ico' === $extension) {
			$data['ext']  = 'ico';
			$data['type'] = 'image/x-icon';
		}

		if ('webmanifest' === $extension) {
			$data['ext']  = 'webmanifest';
			$data['type'] = 'application/manifest+json';
		}

		return $data;
	},
	10,
	4
);
