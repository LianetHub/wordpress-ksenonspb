<?php

/**
 * Head cleanup and frontend optimizations
 *
 * @package ksenonspb
 */

// =============================================================================
// ЧИСТКА HEAD
// =============================================================================

remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);

remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');

remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('template_redirect', 'rest_output_link_header', 11);

add_filter('the_generator', '__return_empty_string');

// =============================================================================
// ОТКЛЮЧЕНИЕ RSS-ФИДОВ
// =============================================================================

function ksenon_disable_feeds()
{
	wp_die(esc_html__('No feed available', 'ksenonspb'), '', array('response' => 404));
}

add_action('do_feed', 'ksenon_disable_feeds', 1);
add_action('do_feed_rdf', 'ksenon_disable_feeds', 1);
add_action('do_feed_rss', 'ksenon_disable_feeds', 1);
add_action('do_feed_rss2', 'ksenon_disable_feeds', 1);
add_action('do_feed_atom', 'ksenon_disable_feeds', 1);

// =============================================================================
// DASHICONS (фронт)
// =============================================================================

add_action(
	'wp_enqueue_scripts',
	function () {
		if (! is_user_logged_in()) {
			wp_deregister_style('dashicons');
		}
	}
);

// =============================================================================
// AUTOSIZES INLINE CSS
// =============================================================================

add_filter('wp_img_tag_add_auto_sizes', '__return_false');

// =============================================================================
// EMOJI
// =============================================================================

remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('admin_print_scripts', 'print_emoji_detection_script');

remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_styles', 'print_emoji_styles');

remove_filter('the_content_feed', 'wp_staticize_emoji');
remove_filter('comment_text_rss', 'wp_staticize_emoji');
remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

add_filter(
	'tiny_mce_plugins',
	function ($plugins) {
		if (is_array($plugins)) {
			return array_diff($plugins, array('wpemoji'));
		}

		return array();
	}
);

add_filter(
	'wp_resource_hints',
	function ($urls, $relation_type) {
		if ('dns-prefetch' === $relation_type) {
			$urls = array_diff($urls, array('https://s.w.org/images/core/emoji/'));
		}

		return $urls;
	},
	10,
	2
);

// =============================================================================
// GUTENBERG (тема не использует блоки на фронте)
// =============================================================================

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_dequeue_style('wp-block-library');
		wp_dequeue_style('wp-block-library-theme');
		wp_dequeue_style('global-styles');
		wp_dequeue_style('classic-theme-styles');
	},
	100
);

// =============================================================================
// ЛИШНИЕ РАЗМЕРЫ ИЗОБРАЖЕНИЙ
// =============================================================================

add_action('after_setup_theme', 'ksenon_remove_image_sizes', 999);

function ksenon_remove_image_sizes()
{
	remove_image_size('medium_large');
	remove_image_size('1536x1536');
	remove_image_size('2048x2048');
}
