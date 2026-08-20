<?php

/**
 * Yoast SEO breadcrumbs: CPT trails + BEM markup.
 *
 * @package ksenonspb
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * @var array<int, array<string, mixed>>|null
 */
$ksenon_yoast_breadcrumb_links = null;

/**
 * Store / reshape breadcrumb links for CPT hierarchy.
 *
 * @param array<int, array<string, mixed>> $links Links from Yoast.
 * @return array<int, array<string, mixed>>
 */
function ksenon_yoast_breadcrumb_links($links)
{
	global $ksenon_yoast_breadcrumb_links;

	if (! is_array($links) || array() === $links) {
		$ksenon_yoast_breadcrumb_links = $links;

		return $links;
	}

	if (is_post_type_archive('service') || is_tax('service_category') || is_singular('service')) {
		$links = ksenon_yoast_breadcrumb_links_service($links);
	} elseif (is_post_type_archive('portfolio') || is_singular('portfolio')) {
		$links = ksenon_yoast_breadcrumb_links_cpt($links, 'portfolio', __('Портфолио', 'ksenonspb'));
	} elseif (is_post_type_archive('brand') || is_singular('brand')) {
		$links = ksenon_yoast_breadcrumb_links_cpt($links, 'brand', __('Марки', 'ksenonspb'));
	} elseif (is_post_type_archive('promotion') || is_singular('promotion')) {
		$links = ksenon_yoast_breadcrumb_links_cpt($links, 'promotion', __('Акции', 'ksenonspb'));
	}

	$ksenon_yoast_breadcrumb_links = $links;

	return $links;
}
add_filter('wpseo_breadcrumb_links', 'ksenon_yoast_breadcrumb_links', 20);

/**
 * Rebuild breadcrumb HTML as BEM list (schema stays in Yoast JSON-LD).
 *
 * @param string $output Default Yoast HTML.
 * @return string
 */
function ksenon_yoast_breadcrumb_output($output)
{
	global $ksenon_yoast_breadcrumb_links;

	$links = $ksenon_yoast_breadcrumb_links;
	if (! is_array($links) || array() === $links) {
		return $output;
	}

	$count = count($links);
	$html  = '<ul class="breadcrumbs__list">';

	foreach ($links as $index => $link) {
		$text = isset($link['text']) ? (string) $link['text'] : '';
		$url  = isset($link['url']) ? (string) $link['url'] : '';
		$is_last = ($index === $count - 1);

		$html .= '<li class="breadcrumbs__item">';

		if ($url && ! $is_last) {
			$html .= sprintf(
				'<a href="%s" class="breadcrumbs__link"><span>%s</span></a>',
				esc_url($url),
				esc_html($text)
			);
		} else {
			$html .= sprintf(
				'<span class="breadcrumbs__current">%s</span>',
				esc_html($text)
			);
		}

		$html .= '</li>';
	}

	$html .= '</ul>';

	return $html;
}
add_filter('wpseo_breadcrumb_output', 'ksenon_yoast_breadcrumb_output');

/**
 * Hide Yoast default separator (CSS draws chevrons).
 *
 * @return string
 */
function ksenon_yoast_breadcrumb_separator()
{
	return '';
}
add_filter('wpseo_breadcrumb_separator', 'ksenon_yoast_breadcrumb_separator');

/**
 * Service / service_category trail: Home → Услуги → ancestors → term/title.
 *
 * @param array<int, array<string, mixed>> $links Yoast links.
 * @return array<int, array<string, mixed>>
 */
function ksenon_yoast_breadcrumb_links_service($links)
{
	$home = isset($links[0]) ? $links[0] : array(
		'text' => __('Главная', 'ksenonspb'),
		'url'  => home_url('/'),
	);

	$archive_url = get_post_type_archive_link('service');
	$services    = array(
		'text' => __('Услуги', 'ksenonspb'),
		'url'  => $archive_url ? $archive_url : '',
	);

	$built = array($home, $services);

	if (is_tax('service_category')) {
		$term = get_queried_object();
		if ($term instanceof WP_Term) {
			$ancestors = get_ancestors($term->term_id, 'service_category');
			foreach (array_reverse($ancestors) as $ancestor_id) {
				$ancestor = get_term((int) $ancestor_id, 'service_category');
				if ($ancestor instanceof WP_Term && ! is_wp_error($ancestor)) {
					$built[] = array(
						'text' => $ancestor->name,
						'url'  => get_term_link($ancestor),
					);
				}
			}
			$built[] = array(
				'text' => $term->name,
				'url'  => '',
			);
		}
	} elseif (is_singular('service')) {
		$main_term = ksenon_yoast_get_primary_service_term(get_the_ID());
		if ($main_term instanceof WP_Term) {
			$ancestors = get_ancestors($main_term->term_id, 'service_category');
			foreach (array_reverse($ancestors) as $ancestor_id) {
				$ancestor = get_term((int) $ancestor_id, 'service_category');
				if ($ancestor instanceof WP_Term && ! is_wp_error($ancestor)) {
					$built[] = array(
						'text' => $ancestor->name,
						'url'  => get_term_link($ancestor),
					);
				}
			}
			$term_link = get_term_link($main_term);
			$built[]   = array(
				'text' => $main_term->name,
				'url'  => is_wp_error($term_link) ? '' : $term_link,
			);
		}
		$built[] = array(
			'text' => get_the_title(),
			'url'  => '',
		);
	} else {
		// Archive: current crumb without link.
		$built[1]['url'] = '';
	}

	return ksenon_yoast_sanitize_breadcrumb_urls($built);
}

/**
 * Generic CPT archive / singular trail.
 *
 * @param array<int, array<string, mixed>> $links     Yoast links.
 * @param string                           $post_type Post type.
 * @param string                           $label     Archive label.
 * @return array<int, array<string, mixed>>
 */
function ksenon_yoast_breadcrumb_links_cpt($links, $post_type, $label)
{
	$home = isset($links[0]) ? $links[0] : array(
		'text' => __('Главная', 'ksenonspb'),
		'url'  => home_url('/'),
	);

	$archive_url = get_post_type_archive_link($post_type);
	$archive     = array(
		'text' => $label,
		'url'  => $archive_url ? $archive_url : '',
	);

	$built = array($home, $archive);

	if (is_singular($post_type)) {
		$built[] = array(
			'text' => get_the_title(),
			'url'  => '',
		);
	} else {
		$built[1]['url'] = '';
	}

	return ksenon_yoast_sanitize_breadcrumb_urls($built);
}

/**
 * Prefer a child service_category term when present.
 *
 * @param int $post_id Post ID.
 * @return WP_Term|null
 */
function ksenon_yoast_get_primary_service_term($post_id)
{
	$terms = get_the_terms((int) $post_id, 'service_category');
	if (! $terms || is_wp_error($terms)) {
		return null;
	}

	$main_term = $terms[0];
	foreach ($terms as $term) {
		if ($term instanceof WP_Term && (int) $term->parent !== 0) {
			$main_term = $term;
			break;
		}
	}

	return $main_term instanceof WP_Term ? $main_term : null;
}

/**
 * Drop WP_Error URLs from link arrays.
 *
 * @param array<int, array<string, mixed>> $links Links.
 * @return array<int, array<string, mixed>>
 */
function ksenon_yoast_sanitize_breadcrumb_urls($links)
{
	foreach ($links as $i => $link) {
		if (isset($link['url']) && is_wp_error($link['url'])) {
			$links[$i]['url'] = '';
		}
	}

	return $links;
}
