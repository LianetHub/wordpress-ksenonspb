<?php

/**
 * Template helpers
 *
 * @package ksenonspb
 */

if (! function_exists('ksenon_anim_class')) {
	function ksenon_anim_class(string $type = 'fade-up', string $extra = ''): string
	{
		return '';
	}
}

if (! function_exists('ksenon_home_get')) {
	function ksenon_home_get($key, $default = '')
	{
		if (function_exists('get_sub_field')) {
			$value = get_sub_field($key);
			if (null !== $value && '' !== $value && false !== $value) {
				return $value;
			}
		}

		return $default;
	}
}

if (! function_exists('ksenon_home_rows')) {
	function ksenon_home_rows($key)
	{
		static $cache = array();

		if (! function_exists('get_sub_field')) {
			return array();
		}

		$context = 'default';
		if (function_exists('get_row_layout') && function_exists('get_row_index')) {
			$context = get_row_layout() . ':' . get_row_index();
		}

		$cache_key = $context . ':' . $key;
		if (isset($cache[$cache_key])) {
			return $cache[$cache_key];
		}

		$value = get_sub_field($key);
		if (! is_array($value) || array() === $value) {
			$cache[$cache_key] = array();
		} else {
			$cache[$cache_key] = array_values($value);
		}

		return $cache[$cache_key];
	}
}

if (! function_exists('ksenon_acf_link_url')) {
	function ksenon_acf_link_url($link, $fallback = '#')
	{
		if (! is_array($link) || empty($link['url'])) {
			return $fallback;
		}

		return ksenon_resolve_link($link['url']);
	}
}

if (! function_exists('ksenon_acf_link_title')) {
	function ksenon_acf_link_title($link, $fallback = '')
	{
		if (! is_array($link)) {
			return $fallback;
		}

		return (string) ($link['title'] ?? $fallback);
	}
}

if (! function_exists('ksenon_acf_link_target')) {
	function ksenon_acf_link_target($link)
	{
		if (! is_array($link) || empty($link['target'])) {
			return '';
		}

		return (string) $link['target'];
	}
}

if (! function_exists('ksenon_normalize_link')) {
	function ksenon_normalize_link($link)
	{
		if (is_array($link)) {
			return trim((string) ($link['url'] ?? ''));
		}

		return trim((string) $link);
	}
}

if (! function_exists('ksenon_resolve_link')) {
	function ksenon_resolve_link($link)
	{
		$link = ksenon_normalize_link($link);
		if ('' === $link || '#' === $link) {
			return $link;
		}
		if (str_starts_with($link, '#') || str_starts_with($link, 'mailto:') || str_starts_with($link, 'tel:')) {
			return $link;
		}
		if (preg_match('#^https?://#i', $link)) {
			return $link;
		}

		return home_url('/' . ltrim($link, '/'));
	}
}

if (! function_exists('ksenon_esc_link')) {
	function ksenon_esc_link($link)
	{
		$resolved = ksenon_resolve_link($link);

		if (str_starts_with($resolved, '#')) {
			return esc_attr($resolved);
		}

		return esc_url($resolved);
	}
}

if (! function_exists('ksenon_menu_link_to_path')) {
	function ksenon_menu_link_to_path($link)
	{
		$normalized = ksenon_normalize_link($link);
		if ('' === $normalized || '#' === $normalized) {
			return '';
		}
		if (str_starts_with($normalized, '#') || str_starts_with($normalized, 'mailto:') || str_starts_with($normalized, 'tel:')) {
			return '';
		}

		$resolved  = ksenon_resolve_link($normalized);
		$home_host = wp_parse_url(home_url(), PHP_URL_HOST);
		$link_host = wp_parse_url($resolved, PHP_URL_HOST);

		if ($link_host && $home_host && strtolower((string) $link_host) !== strtolower((string) $home_host)) {
			return untrailingslashit($resolved);
		}

		$path = wp_parse_url($resolved, PHP_URL_PATH);
		if (! $path || '/' === $path) {
			return '/';
		}

		return user_trailingslashit($path);
	}
}

if (! function_exists('ksenon_get_current_request_path')) {
	function ksenon_get_current_request_path()
	{
		if (is_front_page()) {
			return '/';
		}

		global $wp;
		$request = isset($wp->request) ? trim((string) $wp->request, '/') : '';
		if ('' === $request) {
			return '/';
		}

		return user_trailingslashit('/' . $request);
	}
}

if (! function_exists('ksenon_service_matches_menu_root')) {
	function ksenon_service_matches_menu_root($root_slug)
	{
		$root_slug = sanitize_title((string) $root_slug);
		if (! $root_slug) {
			return false;
		}

		if (is_tax('service_category')) {
			$term = get_queried_object();
			if (! $term instanceof WP_Term) {
				return false;
			}

			if ($term->slug === $root_slug) {
				return true;
			}

			return ksenon_get_service_category_top_parent_slug($term) === $root_slug;
		}

		if (is_singular('service')) {
			$post = get_queried_object();
			if (! $post instanceof WP_Post) {
				return false;
			}

			$term = ksenon_get_deepest_service_category_term((int) $post->ID);
			if (! $term) {
				return false;
			}

			return ksenon_get_service_category_top_parent_slug($term) === $root_slug;
		}

		return false;
	}
}

if (! function_exists('ksenon_menu_section_is_active')) {
	function ksenon_menu_section_is_active($menu_path)
	{
		$slug = trim((string) $menu_path, '/');
		$root = '' === $slug ? '' : strtok($slug, '/');

		switch ($root) {
			case '':
				return is_front_page();
			case 'uslugi':
				return is_post_type_archive('service') || is_page_template('page-uslugi.php');
			case 'tyuning-far':
			case 'remont-far':
			case 'regulirovka-diagnostika':
			case 'ptf':
			case 'dop-uslugi':
				return ksenon_service_matches_menu_root($root);
			case 'portfolio':
				return is_post_type_archive('portfolio') || is_singular('portfolio');
			case 'marki':
				return is_post_type_archive('brand') || is_singular('brand');
			case 'akcii':
				return is_post_type_archive('promotion') || is_singular('promotion');
			case 'o-kompanii':
				return is_page_template('page-o-kompanii.php') || is_page('o-kompanii');
			case 'stoimost':
				return is_page_template('page-stoimost.php') || is_page('stoimost');
			case 'privacy-policy':
				return is_page_template('page-policy.php') || is_page('privacy-policy');
		}

		return false;
	}
}

if (! function_exists('ksenon_menu_page_is_active')) {
	function ksenon_menu_page_is_active($menu_path)
	{
		if (! is_page()) {
			return false;
		}

		$slug = trim((string) $menu_path, '/');
		if ('' === $slug) {
			return is_front_page();
		}

		$page = get_page_by_path($slug);
		if (! $page) {
			return false;
		}

		$current_id = (int) get_queried_object_id();

		return $current_id === (int) $page->ID
			|| in_array((int) $page->ID, get_post_ancestors($current_id), true);
	}
}

if (! function_exists('ksenon_is_menu_link_active')) {
	function ksenon_is_menu_link_active($link)
	{
		$menu_path = ksenon_menu_link_to_path($link);
		if ('' === $menu_path) {
			return false;
		}

		if (preg_match('#^https?://#i', $menu_path)) {
			$current = (is_ssl() ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');
			$current = untrailingslashit(strtok($current, '?'));

			return $current === $menu_path;
		}

		$current_path = ksenon_get_current_request_path();
		if (untrailingslashit($current_path) === untrailingslashit($menu_path)) {
			return true;
		}

		if (ksenon_menu_section_is_active($menu_path)) {
			return true;
		}

		return ksenon_menu_page_is_active($menu_path);
	}
}

if (! function_exists('ksenon_get_post_field')) {
	function ksenon_get_post_field($key, $post_id = 0)
	{
		static $cache = array();

		$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
		if (! $post_id || ! function_exists('get_field')) {
			return false;
		}

		if (! isset($cache[$post_id])) {
			$cache[$post_id] = function_exists('get_fields') ? (get_fields($post_id) ?: array()) : array();
		}

		if (array_key_exists($key, $cache[$post_id])) {
			return $cache[$post_id][$key];
		}

		$value                     = get_field($key, $post_id);
		$cache[$post_id][$key] = $value;

		return $value;
	}
}

if (! function_exists('ksenon_get_term_field')) {
	/**
	 * @param string $key     ACF field name.
	 * @param int    $term_id Term ID.
	 */
	function ksenon_get_term_field($key, $term_id = 0)
	{
		static $cache = array();

		$term_id = (int) $term_id;
		if (! $term_id || ! function_exists('get_field')) {
			return false;
		}

		$context = 'service_category_' . $term_id;

		if (! isset($cache[$context])) {
			$cache[$context] = function_exists('get_fields') ? (get_fields($context) ?: array()) : array();
		}

		if (array_key_exists($key, $cache[$context])) {
			return $cache[$context][$key];
		}

		$value                    = get_field($key, $context);
		$cache[$context][$key] = $value;

		return $value;
	}
}

if (! function_exists('ksenon_normalize_card_labels')) {
	/**
	 * @param mixed $labels_raw ACF repeater rows with `text` subfield.
	 * @return string[]
	 */
	function ksenon_normalize_card_labels($labels_raw)
	{
		if (! is_array($labels_raw)) {
			return array();
		}

		$labels = array();
		foreach ($labels_raw as $row) {
			if (! is_array($row)) {
				continue;
			}
			$label_text = trim((string) ($row['text'] ?? ''));
			if ('' !== $label_text) {
				$labels[] = $label_text;
			}
		}

		return $labels;
	}
}

if (! function_exists('ksenon_normalize_faq_items')) {
	function ksenon_normalize_faq_items($items)
	{
		if (! is_array($items)) {
			return array();
		}

		$normalized = array();
		foreach ($items as $item) {
			if (! is_array($item)) {
				continue;
			}
			$question = trim((string) ($item['question'] ?? ''));
			if ('' === $question) {
				continue;
			}

			$prices = array();
			if (! empty($item['prices']) && is_array($item['prices'])) {
				foreach ($item['prices'] as $row) {
					if (! is_array($row)) {
						continue;
					}
					$label = trim((string) ($row['label'] ?? ''));
					$value = trim((string) ($row['value'] ?? ''));
					if ('' === $label && '' === $value) {
						continue;
					}
					$prices[] = array(
						'label' => $label,
						'value' => $value,
					);
				}
			}

			$normalized[] = array(
				'question'     => $question,
				'answer'       => (string) ($item['answer'] ?? ''),
				'answer_after' => (string) ($item['answer_after'] ?? ''),
				'prices'       => $prices,
				'is_open'      => ! empty($item['is_open']),
			);
		}

		return $normalized;
	}
}

if (! function_exists('ksenon_get_reviews_source_urls')) {
	/**
	 * External “all reviews” URLs by source tab.
	 *
	 * @return array{yandex: string, drive2: string}
	 */
	function ksenon_get_reviews_source_urls()
	{
		$yandex = trim((string) ksenon_get_option('reviews_url_yandex', ''));
		$drive2 = trim((string) ksenon_get_option('reviews_url_drive2', ''));

		return array(
			'yandex' => $yandex ?: 'https://yandex.ru/maps/user/kezdbvu6mzrqd3kzyurb1n08rc',
			'drive2' => $drive2 ?: 'https://www.drive2.ru/o/kbauto/reviews',
		);
	}
}

if (! function_exists('ksenon_format_review_date_label')) {
	/**
	 * Relative Russian date label for a review date (Y-m-d or timestamp).
	 *
	 * @param string|int $date Date string or unix timestamp.
	 * @return string
	 */
	function ksenon_format_review_date_label($date)
	{
		if (is_numeric($date)) {
			$ts = (int) $date;
		} else {
			$date = trim((string) $date);
			if ('' === $date) {
				return '';
			}
			$ts = strtotime($date);
		}

		if (! $ts) {
			return '';
		}

		$now  = time();
		$diff = max(0, $now - $ts);

		if ($diff < DAY_IN_SECONDS) {
			return __('сегодня', 'ksenonspb');
		}

		$days = (int) floor($diff / DAY_IN_SECONDS);
		if ($days < 7) {
			return sprintf(
				/* translators: %s: day count with plural word */
				__('%s назад', 'ksenonspb'),
				$days . ' ' . ksenon_plural_ru($days, 'день', 'дня', 'дней')
			);
		}

		$weeks = (int) floor($days / 7);
		if ($weeks < 5) {
			return sprintf(
				__('%s назад', 'ksenonspb'),
				$weeks . ' ' . ksenon_plural_ru($weeks, 'неделю', 'недели', 'недель')
			);
		}

		$months = (int) floor($days / 30);
		if ($months < 12) {
			$months = max(1, $months);
			return sprintf(
				__('%s назад', 'ksenonspb'),
				$months . ' ' . ksenon_plural_ru($months, 'месяц', 'месяца', 'месяцев')
			);
		}

		$years = (int) floor($days / 365);
		$years = max(1, $years);

		return sprintf(
			__('%s назад', 'ksenonspb'),
			$years . ' ' . ksenon_plural_ru($years, 'год', 'года', 'лет')
		);
	}
}

if (! function_exists('ksenon_get_reviews')) {
	/**
	 * Reviews from CPT `review`.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	function ksenon_get_reviews()
	{
		if (! function_exists('get_field')) {
			return array();
		}

		$query = new WP_Query(
			array(
				'post_type'              => 'review',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'orderby'                => array(
					'menu_order' => 'ASC',
					'date'       => 'DESC',
				),
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			)
		);

		$reviews = array();
		foreach ($query->posts as $post) {
			if (! $post instanceof WP_Post) {
				continue;
			}

			$post_id = (int) $post->ID;
			$name    = trim(get_the_title($post));
			$text    = trim((string) get_field('text', $post_id));
			if ('' === $text && '' === $name) {
				continue;
			}

			$source = sanitize_key((string) get_field('source', $post_id));
			if (! in_array($source, array('yandex', 'drive2'), true)) {
				$source = 'yandex';
			}

			$review_date = (string) (get_field('review_date', $post_id) ?: '');
			$date_label  = $review_date
				? ksenon_format_review_date_label($review_date)
				: '';

			$reviews[] = array(
				'name'        => $name,
				'text'        => $text,
				'photo'       => get_field('photo', $post_id) ?: null,
				'rating'      => (int) (get_field('rating', $post_id) ?: 5),
				'source'      => $source,
				'review_date' => $review_date,
				'date_label'  => $date_label,
				'car_model'   => (string) (get_field('car_model', $post_id) ?: ''),
				'story_title' => (string) (get_field('story_title', $post_id) ?: ''),
				'story_url'   => (string) (get_field('story_url', $post_id) ?: ''),
				'story_image' => get_field('story_image', $post_id) ?: null,
				'verified'    => (bool) get_field('verified', $post_id),
			);
		}

		wp_reset_postdata();

		return $reviews;
	}
}

if (! function_exists('ksenon_cta_form_config')) {
	function ksenon_cta_form_config($variant = 'service_not_found')
	{
		$variants = array(
			'service_not_found' => array(
				'title'       => (string) ksenon_get_option('cf7_title_service_not_found', __('Не нашли свою услугу?', 'ksenonspb')),
				'cf7_option'  => 'cf7_zakaz',
				'form_source' => __('Не нашли услугу', 'ksenonspb'),
			),
			'same_result'       => array(
				'title'       => (string) ksenon_get_option('cf7_title_same_result', __('Хотите такой же результат?', 'ksenonspb')),
				'cf7_option'  => 'cf7_zakaz',
				'form_source' => __('Хотите такой же результат', 'ksenonspb'),
			),
			'free_inspection'   => array(
				'title'       => (string) ksenon_get_option('cf7_title_free_inspection', __('Убедились? Запишитесь на бесплатный осмотр', 'ksenonspb')),
				'cf7_option'  => 'cf7_zakaz',
				'form_source' => __('Бесплатный осмотр', 'ksenonspb'),
			),
			'appointment'       => array(
				'title'       => (string) ksenon_get_option('cf7_title_appointment', __('Запишитесь на установку', 'ksenonspb')),
				'cf7_option'  => 'cf7_zakaz',
				'form_source' => __('Запись на установку', 'ksenonspb'),
			),
		);

		return $variants[$variant] ?? $variants['service_not_found'];
	}
}

if (! function_exists('ksenon_query_cpt')) {
	function ksenon_query_cpt($post_type, $args = array())
	{
		$defaults = array(
			'post_type'              => $post_type,
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'menu_order title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		return new WP_Query(wp_parse_args($args, $defaults));
	}
}

if (! function_exists('ksenon_query_services')) {
	function ksenon_query_services($args = array())
	{
		return ksenon_query_cpt('service', $args);
	}
}

if (! function_exists('ksenon_query_portfolio')) {
	function ksenon_query_portfolio($args = array())
	{
		return ksenon_query_cpt('portfolio', $args);
	}
}

if (! function_exists('ksenon_query_brands')) {
	function ksenon_query_brands($args = array())
	{
		return ksenon_query_cpt('brand', $args);
	}
}

if (! function_exists('ksenon_get_popular_brand_ids')) {
	/**
	 * Brand IDs sorted by references in portfolio and services.
	 *
	 * @param int $limit Max items.
	 * @return int[]
	 */
	function ksenon_get_popular_brand_ids($limit = 9)
	{
		$limit = max(1, (int) $limit);

		$brand_ids = get_posts(
			array(
				'post_type'              => 'brand',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		if (! $brand_ids) {
			return array();
		}

		$scores = array_fill_keys(array_map('intval', $brand_ids), 0);

		$referencing_posts = get_posts(
			array(
				'post_type'              => array('portfolio', 'service'),
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			)
		);

		foreach ($referencing_posts as $post_id) {
			foreach (ksenon_get_related_ids('related_brands', (int) $post_id) as $brand_id) {
				if (isset($scores[$brand_id])) {
					++$scores[$brand_id];
				}
			}
		}

		if (max($scores) <= 0) {
			return array_slice(array_map('intval', $brand_ids), 0, $limit);
		}

		arsort($scores, SORT_NUMERIC);

		return array_slice(array_map('intval', array_keys($scores)), 0, $limit);
	}
}

if (! function_exists('ksenon_query_promotions')) {
	function ksenon_query_promotions($args = array())
	{
		return ksenon_query_cpt('promotion', $args);
	}
}

if (! function_exists('ksenon_get_related_ids')) {
	function ksenon_get_related_ids($field, $post_id = 0)
	{
		$value = ksenon_get_post_field($field, $post_id);
		if (! is_array($value)) {
			return array();
		}

		$ids = array();
		foreach ($value as $item) {
			if ($item instanceof WP_Post) {
				$ids[] = (int) $item->ID;
			} elseif (is_numeric($item)) {
				$ids[] = (int) $item;
			}
		}

		return array_values(array_filter(array_unique($ids)));
	}
}

if (! function_exists('ksenon_get_related_portfolio')) {
	function ksenon_get_related_portfolio($post_id = 0, $limit = 4)
	{
		$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
		$service_ids = ksenon_get_related_ids('related_services', $post_id);
		$brand_ids   = ksenon_get_related_ids('related_brands', $post_id);

		$meta_query = array('relation' => 'OR');
		if ($service_ids) {
			$meta_query[] = array(
				'key'     => 'related_services',
				'value'   => '"' . implode('"|"', array_map('strval', $service_ids)) . '"',
				'compare' => 'LIKE',
			);
		}
		if ($brand_ids) {
			$meta_query[] = array(
				'key'     => 'related_brands',
				'value'   => '"' . implode('"|"', array_map('strval', $brand_ids)) . '"',
				'compare' => 'LIKE',
			);
		}

		$args = array(
			'posts_per_page' => $limit,
			'post__not_in'   => array($post_id),
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if (count($meta_query) > 1) {
			$args['meta_query'] = $meta_query;
		}

		return ksenon_query_portfolio($args);
	}
}

if (! function_exists('ksenon_get_service_categories')) {
	/**
	 * Top-level service_category terms from WordPress.
	 *
	 * @return WP_Term[]
	 */
	function ksenon_get_service_categories()
	{
		$terms = get_terms(
			array(
				'taxonomy'   => 'service_category',
				'parent'     => 0,
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		if (is_wp_error($terms) || ! is_array($terms)) {
			return array();
		}

		$terms = array_values(
			array_filter($terms, static fn($term) => $term instanceof WP_Term)
		);

		$order = array('tyuning-far', 'remont-far', 'regulirovka-diagnostika', 'ptf', 'dop-uslugi');

		usort(
			$terms,
			static function ($left, $right) use ($order) {
				$left_pos  = array_search($left->slug, $order, true);
				$right_pos = array_search($right->slug, $order, true);
				$left_pos  = false === $left_pos ? PHP_INT_MAX : $left_pos;
				$right_pos = false === $right_pos ? PHP_INT_MAX : $right_pos;

				return $left_pos <=> $right_pos;
			}
		);

		return $terms;
	}
}

if (! function_exists('ksenon_get_service_subcategories')) {
	/**
	 * @return WP_Term[]
	 */
	function ksenon_get_service_subcategories($parent_slug)
	{
		$parent = get_term_by('slug', sanitize_title((string) $parent_slug), 'service_category');
		if (! $parent instanceof WP_Term) {
			return array();
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'service_category',
				'parent'     => (int) $parent->term_id,
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		if (is_wp_error($terms) || ! is_array($terms)) {
			return array();
		}

		$terms = array_values(
			array_filter($terms, static fn($term) => $term instanceof WP_Term)
		);

		$order = array('optika-i-korpus', 'elektrika', 'mehanika');

		usort(
			$terms,
			static function ($left, $right) use ($order) {
				$left_pos  = array_search($left->slug, $order, true);
				$right_pos = array_search($right->slug, $order, true);
				$left_pos  = false === $left_pos ? PHP_INT_MAX : $left_pos;
				$right_pos = false === $right_pos ? PHP_INT_MAX : $right_pos;

				return $left_pos <=> $right_pos;
			}
		);

		return $terms;
	}
}

if (! function_exists('ksenon_get_all_service_categories_flat')) {
	/**
	 * All service_category terms in site display order (top-level + nested children).
	 *
	 * @return WP_Term[]
	 */
	function ksenon_get_all_service_categories_flat()
	{
		$result = array();

		foreach (ksenon_get_service_categories() as $term) {
			$result[] = $term;

			$subcategories = ksenon_get_service_subcategories($term->slug);
			if ($subcategories) {
				foreach ($subcategories as $subcategory) {
					$result[] = $subcategory;
				}
			}
		}

		return $result;
	}
}

if (! function_exists('ksenon_get_service_category_min_price')) {
	/**
	 * Minimum price_from among services assigned directly to the category term.
	 *
	 * @param int $term_id service_category term ID.
	 */
	function ksenon_get_service_category_min_price($term_id)
	{
		$term_id = (int) $term_id;
		if ($term_id <= 0) {
			return 0;
		}

		$query = new WP_Query(
			array(
				'post_type'              => 'service',
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'orderby'                => 'meta_value_num',
				'order'                  => 'ASC',
				'meta_key'               => 'price_from',
				'meta_query'             => array(
					array(
						'key'     => 'price_from',
						'value'   => 0,
						'compare' => '>',
						'type'    => 'NUMERIC',
					),
				),
				'tax_query'              => array(
					array(
						'taxonomy'         => 'service_category',
						'field'            => 'term_id',
						'terms'            => $term_id,
						'include_children' => false,
					),
				),
			)
		);

		if (! $query->have_posts()) {
			return 0;
		}

		$post_id = (int) $query->posts[0];
		wp_reset_postdata();

		$price = ksenon_get_post_field('price_from', $post_id);

		return is_numeric($price) ? (int) $price : 0;
	}
}

if (! function_exists('ksenon_service_category_url')) {
	function ksenon_service_category_url($category_slug)
	{
		$category_slug = sanitize_title((string) $category_slug);
		if (! $category_slug) {
			return '';
		}

		$term = get_term_by('slug', $category_slug, 'service_category');
		if (! $term instanceof WP_Term) {
			return '';
		}

		$link = get_term_link($term);

		return is_wp_error($link) ? '' : $link;
	}
}

if (! function_exists('ksenon_get_current_service_category_slug')) {
	function ksenon_get_current_service_category_slug()
	{
		if (! is_tax('service_category')) {
			return '';
		}

		$term = get_queried_object();

		return $term instanceof WP_Term ? $term->slug : '';
	}
}

if (! function_exists('ksenon_get_active_top_level_service_category_slug')) {
	function ksenon_get_active_top_level_service_category_slug()
	{
		if (! is_tax('service_category')) {
			return '';
		}

		$term = get_queried_object();
		if (! $term instanceof WP_Term) {
			return '';
		}

		return ksenon_get_service_category_top_parent_slug($term);
	}
}

if (! function_exists('ksenon_services_archive_url')) {
	function ksenon_services_archive_url($category_slug = '')
	{
		if ($category_slug) {
			return ksenon_service_category_url($category_slug);
		}

		$url = get_post_type_archive_link('service');

		return $url ?: home_url('/uslugi/');
	}
}

if (! function_exists('ksenon_services_pagination_url')) {
	function ksenon_services_pagination_url($page, $category_slug = '')
	{
		$page = max(1, (int) $page);
		$url  = ksenon_services_archive_url($category_slug);

		if ($page <= 1) {
			return $url;
		}

		if (get_option('permalink_structure')) {
			return user_trailingslashit(trailingslashit($url) . 'page/' . $page);
		}

		return add_query_arg('paged', $page, $url);
	}
}

if (! function_exists('ksenon_get_pagination_items')) {
	function ksenon_get_pagination_items($current, $total)
	{
		$current = max(1, (int) $current);
		$total   = max(1, (int) $total);

		if ($total <= 6) {
			$items = array();
			for ($i = 1; $i <= $total; $i++) {
				$items[] = array(
					'type' => 'page',
					'num'  => $i,
				);
			}

			return $items;
		}

		if ($current <= 4) {
			$items = array();
			for ($i = 1; $i <= 4; $i++) {
				$items[] = array(
					'type' => 'page',
					'num'  => $i,
				);
			}
			$items[] = array('type' => 'dots');
			$items[] = array(
				'type' => 'page',
				'num'  => $total,
			);

			return $items;
		}

		if ($current >= $total - 3) {
			$items   = array(
				array(
					'type' => 'page',
					'num'  => 1,
				),
				array('type' => 'dots'),
			);
			for ($i = $total - 3; $i <= $total; $i++) {
				$items[] = array(
					'type' => 'page',
					'num'  => $i,
				);
			}

			return $items;
		}

		return array(
			array(
				'type' => 'page',
				'num'  => 1,
			),
			array('type' => 'dots'),
			array(
				'type' => 'page',
				'num'  => $current - 1,
			),
			array(
				'type' => 'page',
				'num'  => $current,
			),
			array(
				'type' => 'page',
				'num'  => $current + 1,
			),
			array('type' => 'dots'),
			array(
				'type' => 'page',
				'num'  => $total,
			),
		);
	}
}

if (! function_exists('ksenon_render_pagination')) {
	/**
	 * @param WP_Query       $query
	 * @param string         $category_slug
	 * @param callable|null  $url_callback  function (int $page): string
	 */
	function ksenon_render_pagination(WP_Query $query, $category_slug = '', $url_callback = null)
	{
		$total = (int) $query->max_num_pages;
		if ($total <= 1) {
			return;
		}

		$current = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
		$items   = ksenon_get_pagination_items($current, $total);

		if (! is_callable($url_callback)) {
			$url_callback = static function ($page) use ($category_slug) {
				return ksenon_services_pagination_url($page, $category_slug);
			};
		}

		$prev_url = $current > 1 ? (string) $url_callback($current - 1) : '';
		$next_url = $current < $total ? (string) $url_callback($current + 1) : '';
?>
		<nav class="cpt-pagination" aria-label="<?php esc_attr_e('Навигация по страницам', 'ksenonspb'); ?>">
			<div class="cpt-pagination__inner">
				<?php if ($prev_url) : ?>
					<a class="cpt-pagination__arrow cpt-pagination__arrow--prev" href="<?php echo esc_url($prev_url); ?>" aria-label="<?php esc_attr_e('Предыдущая страница', 'ksenonspb'); ?>">
						<?php ksenon_icon('icon-chevron-left', 8, 18, 'cpt-pagination__icon'); ?>
					</a>
				<?php else : ?>
					<span class="cpt-pagination__arrow cpt-pagination__arrow--prev cpt-pagination__arrow--disabled" aria-hidden="true">
						<?php ksenon_icon('icon-chevron-left', 8, 18, 'cpt-pagination__icon'); ?>
					</span>
				<?php endif; ?>

				<div class="cpt-pagination__pages">
					<?php foreach ($items as $item) : ?>
						<?php if ('dots' === $item['type']) : ?>
							<span class="cpt-pagination__page cpt-pagination__page--dots" aria-hidden="true">&hellip;</span>
						<?php else : ?>
							<?php
							$page_num   = (int) $item['num'];
							$is_current = $page_num === $current;
							$page_url   = (string) $url_callback($page_num);
							?>
							<?php if ($is_current) : ?>
								<span class="cpt-pagination__page _active" aria-current="page"><?php echo esc_html((string) $page_num); ?></span>
							<?php else : ?>
								<a class="cpt-pagination__page" href="<?php echo esc_url($page_url); ?>"><?php echo esc_html((string) $page_num); ?></a>
							<?php endif; ?>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>

				<?php if ($next_url) : ?>
					<a class="cpt-pagination__arrow cpt-pagination__arrow--next" href="<?php echo esc_url($next_url); ?>" aria-label="<?php esc_attr_e('Следующая страница', 'ksenonspb'); ?>">
						<?php ksenon_icon('icon-chevron-right', 8, 18, 'cpt-pagination__icon'); ?>
					</a>
				<?php else : ?>
					<span class="cpt-pagination__arrow cpt-pagination__arrow--next cpt-pagination__arrow--disabled" aria-hidden="true">
						<?php ksenon_icon('icon-chevron-right', 8, 18, 'cpt-pagination__icon'); ?>
					</span>
				<?php endif; ?>
			</div>
		</nav>
	<?php
	}
}

if (! function_exists('ksenon_portfolio_archive_url')) {
	function ksenon_portfolio_archive_url($category_slug = '', $brand = '')
	{
		$url = get_post_type_archive_link('portfolio');
		$url = $url ?: home_url('/portfolio/');

		$args = array();
		$category_slug = sanitize_title((string) $category_slug);
		$brand         = trim((string) $brand);

		if ($category_slug) {
			$args['category'] = $category_slug;
		}
		if ($brand !== '') {
			$args['brand'] = $brand;
		}

		return $args ? add_query_arg($args, $url) : $url;
	}
}

if (! function_exists('ksenon_portfolio_pagination_url')) {
	function ksenon_portfolio_pagination_url($page, $category_slug = '', $brand = '')
	{
		$page = max(1, (int) $page);
		$url  = ksenon_portfolio_archive_url($category_slug, $brand);

		if ($page <= 1) {
			return $url;
		}

		if (get_option('permalink_structure')) {
			$base = get_post_type_archive_link('portfolio') ?: home_url('/portfolio/');
			$paged_url = user_trailingslashit(trailingslashit($base) . 'page/' . $page);
			$query = array();
			$category_slug = sanitize_title((string) $category_slug);
			$brand         = trim((string) $brand);
			if ($category_slug) {
				$query['category'] = $category_slug;
			}
			if ($brand !== '') {
				$query['brand'] = $brand;
			}

			return $query ? add_query_arg($query, $paged_url) : $paged_url;
		}

		return add_query_arg('paged', $page, $url);
	}
}

if (! function_exists('ksenon_get_portfolio_filter_category_slug')) {
	function ksenon_get_portfolio_filter_category_slug()
	{
		if (! isset($_GET['category'])) {
			return '';
		}

		return sanitize_title(wp_unslash((string) $_GET['category']));
	}
}

if (! function_exists('ksenon_get_portfolio_filter_brand_query')) {
	function ksenon_get_portfolio_filter_brand_query()
	{
		if (! isset($_GET['brand'])) {
			return '';
		}

		return sanitize_text_field(wp_unslash((string) $_GET['brand']));
	}
}

if (! function_exists('ksenon_resolve_portfolio_brand')) {
	/**
	 * @param string $query Brand title or slug.
	 * @return WP_Post|null
	 */
	function ksenon_resolve_portfolio_brand($query)
	{
		$query = trim((string) $query);
		if ($query === '') {
			return null;
		}

		$by_slug = get_posts(
			array(
				'post_type'              => 'brand',
				'name'                   => sanitize_title($query),
				'posts_per_page'         => 1,
				'post_status'            => 'publish',
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		if ($by_slug) {
			return $by_slug[0];
		}

		$by_title = get_posts(
			array(
				'post_type'              => 'brand',
				'title'                  => $query,
				'posts_per_page'         => 1,
				'post_status'            => 'publish',
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		return $by_title ? $by_title[0] : null;
	}
}

if (! function_exists('ksenon_get_service_ids_for_category')) {
	/**
	 * @param int $term_id
	 * @return int[]
	 */
	function ksenon_get_service_ids_for_category($term_id)
	{
		$term_id = (int) $term_id;
		if ($term_id <= 0) {
			return array();
		}

		$include_children = (bool) get_term_children($term_id, 'service_category');
		$ids = get_posts(
			array(
				'post_type'              => 'service',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'post_status'            => 'publish',
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'tax_query'              => array(
					array(
						'taxonomy'         => 'service_category',
						'field'            => 'term_id',
						'terms'            => array($term_id),
						'include_children' => $include_children,
					),
				),
			)
		);

		return array_map('intval', $ids);
	}
}

if (! function_exists('ksenon_brands_archive_url')) {
	function ksenon_brands_archive_url()
	{
		$url = get_post_type_archive_link('brand');
		return $url ?: home_url('/marki/');
	}
}

if (! function_exists('ksenon_promotions_archive_url')) {
	function ksenon_promotions_archive_url()
	{
		$url = get_post_type_archive_link('promotion');
		return $url ?: home_url('/akcii/');
	}
}

if (! function_exists('ksenon_get_phones')) {
	function ksenon_get_phones()
	{
		$phones = array();

		if (! function_exists('have_rows') || ! have_rows('telefony', 'option')) {
			return $phones;
		}

		while (have_rows('telefony', 'option')) {
			the_row();
			$number = get_sub_field('nomer');
			if (! $number) {
				continue;
			}
			$phones[] = $number;
		}

		return $phones;
	}
}

if (! function_exists('ksenon_get_main_class')) {
	function ksenon_get_main_class()
	{
		if (is_front_page()) {
			return 'main--home';
		}
		if (is_404()) {
			return 'main--not-found';
		}
		if (is_search()) {
			return 'main--search';
		}
		if (is_singular('service')) {
			return 'main--service';
		}
		if (is_post_type_archive('service') || is_page_template('page-uslugi.php') || is_tax('service_category')) {
			return 'main--services';
		}
		if (is_singular('portfolio')) {
			return 'main--case';
		}
		if (is_post_type_archive('portfolio')) {
			return 'main--portfolio';
		}
		if (is_singular('brand')) {
			return 'main--brand';
		}
		if (is_post_type_archive('brand')) {
			return 'main--brands';
		}
		if (is_singular('promotion')) {
			return 'main--promotion';
		}
		if (is_post_type_archive('promotion')) {
			return 'main--promotions';
		}
		if (is_page_template('page-o-kompanii.php')) {
			return 'main--about';
		}
		if (is_page_template('page-stoimost.php')) {
			return 'main--pricing';
		}
		if (is_page_template('page-policy.php') || is_page('privacy-policy')) {
			return 'main--policy';
		}

		return '';
	}
}

if (! function_exists('ksenon_get_option_raw')) {
	function ksenon_get_option_raw($key)
	{
		static $cache = null;

		if (! function_exists('get_field')) {
			return null;
		}

		if (null === $cache) {
			$cache = function_exists('get_fields') ? (get_fields('option') ?: array()) : array();
		}

		if (array_key_exists($key, $cache)) {
			return $cache[$key];
		}

		$value         = get_field($key, 'option');
		$cache[$key] = $value;

		return $value;
	}
}

if (! function_exists('ksenon_get_option')) {
	function ksenon_get_option($key, $default = '')
	{
		if (! function_exists('get_field')) {
			return $default;
		}

		$value = ksenon_get_option_raw($key);
		if (null !== $value && '' !== $value && false !== $value) {
			return $value;
		}

		return $default;
	}
}

if (! function_exists('ksenon_get_policy_url')) {
	function ksenon_get_policy_url()
	{
		return home_url('/politika-konfidentsialnosti/');
	}
}

if (! function_exists('ksenon_get_opd_url')) {
	function ksenon_get_opd_url()
	{
		return home_url('/soglasie-na-obrabotku-pd/');
	}
}

if (! function_exists('ksenon_get_logo')) {
	function ksenon_get_logo($variant = 'dark')
	{
		$key  = 'light' === $variant ? 'logo_light' : 'logo_dark';
		$logo = ksenon_get_option_raw($key);

		if ($logo) {
			return $logo;
		}

		return ksenon_get_option_raw('logotip');
	}
}

if (! function_exists('ksenon_render_favicons')) {
	function ksenon_render_favicons()
	{
		$png_url      = ksenon_assets_uri('favicon-96x96.png');
		$svg_url      = ksenon_assets_uri('favicon.svg');
		$ico_url      = ksenon_assets_uri('favicon.ico');
		$apple_url    = ksenon_assets_uri('apple-touch-icon.png');
		$manifest_url = ksenon_web_manifest_url();

		printf('<link rel="icon" type="image/png" href="%s" sizes="96x96">' . "\n", esc_url($png_url));
		printf('<link rel="icon" type="image/svg+xml" href="%s">' . "\n", esc_url($svg_url));
		printf('<link rel="shortcut icon" href="%s">' . "\n", esc_url($ico_url));
		printf('<link rel="apple-touch-icon" sizes="180x180" href="%s">' . "\n", esc_url($apple_url));
		printf('<meta name="apple-mobile-web-app-title" content="%s">' . "\n", esc_attr('КБ АВТО'));
		printf('<link rel="manifest" href="%s">' . "\n", esc_url($manifest_url));
	}
}

if (! function_exists('ksenon_web_manifest_url')) {
	function ksenon_web_manifest_url()
	{
		return home_url('/site.webmanifest');
	}
}

if (! function_exists('ksenon_get_web_manifest_data')) {
	function ksenon_get_web_manifest_data()
	{
		return array(
			'name'             => 'КБ АВТО',
			'short_name'       => 'КБ АВТО',
			'icons'            => array(
				array(
					'src'     => ksenon_assets_uri('web-app-manifest-192x192.png'),
					'sizes'   => '192x192',
					'type'    => 'image/png',
					'purpose' => 'maskable',
				),
				array(
					'src'     => ksenon_assets_uri('web-app-manifest-512x512.png'),
					'sizes'   => '512x512',
					'type'    => 'image/png',
					'purpose' => 'maskable',
				),
			),
			'theme_color'      => '#ffffff',
			'background_color' => '#ffffff',
			'display'          => 'standalone',
		);
	}
}

if (! function_exists('ksenon_get_footer_domain')) {
	function ksenon_get_footer_domain()
	{
		$domain = ksenon_get_option('site_domain', '');
		if ($domain) {
			return $domain;
		}

		$host = wp_parse_url(home_url(), PHP_URL_HOST);

		return $host ? $host : 'ksenonspb.ru';
	}
}

if (! function_exists('ksenon_get_social_links')) {
	function ksenon_get_social_links()
	{
		$networks = array(
			'telegram' => 'social_telegram',
			'whatsapp' => 'social_whatsapp',
			'vk'       => 'social_vk',
			'youtube'  => 'social_youtube',
			'max'      => 'social_max',
		);

		$links = array();

		foreach ($networks as $network => $field) {
			$url = trim((string) ksenon_get_option($field, ''));
			if ($url) {
				$links[] = array(
					'network' => $network,
					'url'     => $url,
				);
			}
		}

		if ($links) {
			return $links;
		}

		$rows = ksenon_get_option('social_links', array());
		if (! is_array($rows)) {
			return array();
		}

		foreach ($rows as $row) {
			if (! is_array($row)) {
				continue;
			}

			$url = isset($row['url']) ? trim((string) $row['url']) : '';
			if (! $url) {
				continue;
			}

			$network = isset($row['network']) ? sanitize_key($row['network']) : '';
			if (! in_array($network, array('telegram', 'whatsapp', 'vk', 'youtube', 'max'), true)) {
				continue;
			}

			$links[] = array(
				'network' => $network,
				'url'     => $url,
			);
		}

		return $links;
	}
}

if (! function_exists('ksenon_get_footer_social_label')) {
	function ksenon_get_footer_social_label($network)
	{
		$labels = array(
			'telegram' => 'Telegram',
			'whatsapp' => 'WhatsApp',
			'vk'       => 'VK',
			'youtube'  => 'YouTube',
			'max'      => 'MAX',
		);

		return $labels[$network] ?? $network;
	}
}

if (! function_exists('ksenon_render_footer_socials')) {
	function ksenon_render_footer_socials()
	{
		$links = ksenon_get_social_links();
		if (! $links) {
			return;
		}

		$icons = array(
			'telegram' => array('icon-social-telegram', 29, 29),
			'whatsapp' => array('icon-social-whatsapp', 29, 29),
			'vk'       => array('icon-vk', 29, 29),
			'youtube'  => array('icon-youtube', 34, 24),
			'max'      => array('icon-max', 29, 29),
		);
	?>
		<ul class="footer__socials">
			<?php foreach ($links as $link) : ?>
				<?php
				if (empty($icons[$link['network']])) {
					continue;
				}
				list($icon_id, $icon_w, $icon_h) = $icons[$link['network']];
				?>
				<li class="footer__socials-item">
					<a
						class="footer__socials-link footer__socials-link--<?php echo esc_attr($link['network']); ?>"
						href="<?php echo esc_url($link['url']); ?>"
						target="_blank"
						rel="noopener noreferrer"
						aria-label="<?php echo esc_attr(ksenon_get_footer_social_label($link['network'])); ?>">
						<?php ksenon_icon($icon_id, $icon_w, $icon_h, 'footer__socials-icon'); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php
	}
}

if (! function_exists('ksenon_get_footer_requisites')) {
	function ksenon_get_footer_requisites()
	{
		$inn  = trim((string) ksenon_get_option('inn', ''));
		$ogrn = trim((string) ksenon_get_option('ogrn', ''));

		if (! $inn && ! $ogrn) {
			return '';
		}

		$parts = array();
		if ($inn) {
			$parts[] = sprintf('ИНН %s', $inn);
		}
		if ($ogrn) {
			$parts[] = sprintf('ОГРН %s', $ogrn);
		}

		return implode('. ', $parts);
	}
}

if (! function_exists('ksenon_get_header_social_pills')) {
	function ksenon_get_header_social_pills()
	{
		$allowed = array('telegram', 'whatsapp');
		$links   = ksenon_get_social_links();

		return array_values(
			array_filter(
				$links,
				static function ($link) use ($allowed) {
					return in_array($link['network'], $allowed, true);
				}
			)
		);
	}
}

if (! function_exists('ksenon_render_header_social_pills')) {
	function ksenon_render_header_social_pills()
	{
		$links = ksenon_get_header_social_pills();
		if (! $links) {
			return;
		}
	?>
		<div class="header-drawer__socials">
			<?php foreach ($links as $link) : ?>
				<a
					class="header-drawer__social header-drawer__social--<?php echo esc_attr($link['network']); ?>"
					href="<?php echo esc_url($link['url']); ?>"
					target="_blank"
					rel="noopener noreferrer">
					<?php echo ksenon_get_header_social_icon($link['network']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
					?>
					<span><?php echo esc_html(ksenon_get_footer_social_label($link['network'])); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php
	}
}

if (! function_exists('ksenon_get_header_social_icon')) {
	function ksenon_get_header_social_icon($network)
	{
		$icons = array(
			'telegram' => '<svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M13.08 1.2 11.5 12.1c-.12.55-.44.68-.9.42l-2.5-1.84-1.2 1.16c-.13.13-.24.24-.5.24l.18-2.55 4.62-4.17c.2-.18-.04-.28-.31-.1L4.2 8.2l-2.46-.77c-.53-.17-.54-.53.11-.78l9.6-3.7c.44-.16.83.1.63.25Z" fill="currentColor"/></svg>',
			'whatsapp' => '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M7 0C3.13 0 0 3.13 0 7c0 1.23.32 2.39.88 3.4L0 14l3.82-.9A6.96 6.96 0 0 0 7 14c3.87 0 7-3.13 7-7S10.87 0 7 0Zm3.79 9.73c-.16.45-.93.86-1.28.92-.33.05-.76.08-1.22-.08-.28-.1-.64-.23-1.1-.45-1.92-.83-3.18-2.79-3.27-2.92-.1-.13-.78-1.04-.78-2 0-.94.5-1.4.67-1.6.17-.2.37-.25.5-.25.12 0 .25.002.36.006.12.006.28-.05.43.33.16.38.55 1.31.6 1.41.05.1.08.21.02.34-.06.13-.09.21-.18.32-.09.11-.19.24-.27.33-.09.09-.19.2-.08.39.11.19.49 1.01 1.05 1.64.72.8 1.33 1.05 1.52 1.17.19.1.3.08.41-.05.11-.13.48-.56.61-.75.13-.2.26-.17.44-.1.19.06 1.09.51 1.27.6.19.1.31.15.36.23.05.08.05.47-.11.92Z" fill="currentColor"/></svg>',
		);

		return $icons[$network] ?? '';
	}
}

if (! function_exists('ksenon_get_footer_static_menus')) {
	function ksenon_get_footer_static_menus()
	{
		$service_items = array();
		$featured = array(
			'ustanovka-biled',
			'remont-posle-povrezhdeniy',
			'polirovka-shlifovka',
			'angelskie-dyavolskie-glazki',
			'remont-drayverov',
			'ustanovka-ksenona',
			'remont-afs',
			'regulirovka-sveta',
		);

		foreach ($featured as $slug) {
			$post = get_page_by_path($slug, OBJECT, 'service');
			if (! $post instanceof WP_Post) {
				continue;
			}

			$service_items[] = array(
				'label' => get_the_title($post),
				'url'   => get_permalink($post),
			);
		}

		return array(
			'services' => array(
				'title' => __('Услуги', 'ksenonspb'),
				'items' => $service_items,
			),
			'info'     => array(
				'title' => __('Информация', 'ksenonspb'),
				'items' => array(
					array(
						'label' => __('О компании', 'ksenonspb'),
						'url'   => home_url('/o-kompanii/'),
					),
					array(
						'label' => __('Команда', 'ksenonspb'),
						'url'   => home_url('/komanda/'),
					),
					array(
						'label' => __('Гарантия', 'ksenonspb'),
						'url'   => home_url('/garantiya/'),
					),
					array(
						'label' => __('Сертификаты', 'ksenonspb'),
						'url'   => home_url('/sertifikaty/'),
					),
					array(
						'label' => __('Цены', 'ksenonspb'),
						'url'   => home_url('/stoimost/'),
					),
					array(
						'label' => __('Рассрочка', 'ksenonspb'),
						'url'   => home_url('/rassrochka/'),
					),
					array(
						'label' => __('Подарочные сертификаты', 'ksenonspb'),
						'url'   => home_url('/podarochnye-sertifikaty/'),
					),
					array(
						'label' => __('Приём фар почтой', 'ksenonspb'),
						'url'   => home_url('/priem-far-pochtoj/'),
					),
					array(
						'label' => __('Отзывы', 'ksenonspb'),
						'url'   => home_url('/otzyvy/'),
					),
					array(
						'label' => __('Блог', 'ksenonspb'),
						'url'   => home_url('/blog/'),
					),
				),
			),
		);
	}
}

if (! function_exists('ksenon_cf7_form')) {
	function ksenon_cf7_form($option_key, $source = '', $fallback_shortcode = '')
	{
		$shortcode = ksenon_get_option($option_key, $fallback_shortcode);
		if ($shortcode && function_exists('wpcf7_contact_form')) {
			ksenon_cf7_set_render_context($source);
			echo do_shortcode($shortcode);
			ksenon_cf7_clear_render_context();
		}
	}
}

if (! function_exists('ksenon_render_section')) {
	function ksenon_render_section($slug, $args = array())
	{
		get_template_part('template-parts/blocks/' . $slug, null, $args);
	}
}

if (! function_exists('ksenon_render_faq')) {
	function ksenon_render_faq($args = array())
	{
		get_template_part('template-parts/section/faq', null, $args);
	}
}

if (! function_exists('ksenon_get_partners')) {
	function ksenon_get_partners()
	{
		return array();
	}
}

if (! function_exists('ksenon_get_partner_link')) {
	function ksenon_get_partner_link($post_id)
	{
		unset($post_id);

		return '';
	}
}

if (! function_exists('ksenon_count_cpt')) {
	function ksenon_count_cpt($post_type)
	{
		$counts = wp_count_posts($post_type);

		return isset($counts->publish) ? (int) $counts->publish : 0;
	}
}

if (! function_exists('ksenon_kses_inline')) {
	function ksenon_kses_inline($content)
	{
		return wp_kses(
			(string) $content,
			array(
				'span'   => array(
					'class' => array('color-grey', 'color-accent', 'title--brand'),
				),
				'br'     => array(),
				'strong' => array(),
				'em'     => array(),
			)
		);
	}
}

if (! function_exists('ksenon_render_home_arrow')) {
	function ksenon_render_home_arrow()
	{
	?>
		<span class="home-arrow" aria-hidden="true">
			<?php ksenon_icon('icon-btn-arrow', 58, 58, 'home-arrow__icon'); ?>
		</span>
	<?php
	}
}

if (! function_exists('ksenon_btn_arrow_icon')) {
	function ksenon_btn_arrow_icon()
	{
	?>
		<span class="btn__arrow" aria-hidden="true">
			<?php ksenon_icon('icon-arrow-right', 15, 10, 'btn__arrow-icon'); ?>
		</span>
	<?php
	}
}

if (! function_exists('ksenon_render_btn_arrow')) {
	function ksenon_render_btn_arrow($link, $class = 'btn', $fallback_title = '')
	{
		if (! is_array($link) || empty($link['url'])) {
			return;
		}

		$target = ksenon_acf_link_target($link);
	?>
		<a
			class="<?php echo esc_attr($class); ?>"
			href="<?php echo esc_url(ksenon_acf_link_url($link)); ?>"
			<?php echo $target ? ' target="' . esc_attr($target) . '"' : ''; ?>>
			<span class="btn__text"><?php echo esc_html(ksenon_acf_link_title($link, $fallback_title)); ?></span>
			<?php ksenon_btn_arrow_icon(); ?>
		</a>
		<?php
	}
}

if (! function_exists('ksenon_get_cta_bottom_popup_target')) {
	function ksenon_get_cta_bottom_popup_target($action)
	{
		$targets = array(
			'popup_order'         => '#popup-order',
			'popup_consultation'  => '#popup-consultation',
			'anchor_contacts'     => '#contacts',
		);

		return $targets[$action] ?? '';
	}
}

if (! function_exists('ksenon_render_cta_bottom_button')) {
	function ksenon_render_cta_bottom_button($link, $action, $class, $fallback_title = '', $with_arrow = false)
	{
		$title = ksenon_acf_link_title(is_array($link) ? $link : array(), $fallback_title);
		if (! $title) {
			return;
		}

		$popup_target = ksenon_get_cta_bottom_popup_target($action);

		if ($popup_target && in_array($action, array('popup_order', 'popup_consultation'), true)) {
		?>
			<button
				class="<?php echo esc_attr($class); ?>"
				type="button"
				data-fancybox
				data-src="<?php echo esc_attr($popup_target); ?>">
				<span class="btn__text"><?php echo esc_html($title); ?></span>
				<?php if ($with_arrow) : ?>
					<?php ksenon_btn_arrow_icon(); ?>
				<?php endif; ?>
			</button>
		<?php
			return;
		}

		if ('anchor_contacts' === $action) {
			$link = array(
				'url'    => '#contacts',
				'title'  => $title,
				'target' => '',
			);
		}

		if (! is_array($link) || empty($link['url'])) {
			return;
		}

		if ($with_arrow) {
			ksenon_render_btn_arrow($link, $class, $fallback_title);
			return;
		}

		$target = ksenon_acf_link_target($link);
		?>
		<a
			class="<?php echo esc_attr($class); ?>"
			href="<?php echo esc_url(ksenon_acf_link_url($link)); ?>"
			<?php echo $target ? ' target="' . esc_attr($target) . '"' : ''; ?>>
			<?php echo esc_html($title); ?>
		</a>
	<?php
	}
}

if (! function_exists('ksenon_get_messenger_links')) {
	function ksenon_get_messenger_links()
	{
		$links = ksenon_get_social_links();
		if (! $links) {
			return array();
		}

		return array_values(
			array_filter(
				$links,
				static function ($link) {
					return in_array($link['network'], array('telegram', 'whatsapp'), true);
				}
			)
		);
	}
}

if (! function_exists('ksenon_faq_title_html')) {
	function ksenon_faq_title_html($title)
	{
		$title = trim((string) $title);
		if ('' === $title) {
			return '';
		}

		if (false !== strpos($title, '<')) {
			return nl2br(ksenon_kses_inline($title)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		$parts = preg_split('/\s+/u', $title);
		if (count($parts) < 2) {
			return esc_html($title);
		}

		$brand = array_pop($parts);

		return esc_html(implode(' ', $parts)) . ' <span class="title--brand">' . esc_html($brand) . '</span>';
	}
}

if (! function_exists('ksenon_render_messenger_links')) {
	function ksenon_render_messenger_links($class = 'messenger-links', $always_show = false)
	{
		$networks        = array('telegram', 'whatsapp');
		$links_by_network = array();

		foreach (ksenon_get_messenger_links() as $link) {
			$links_by_network[$link['network']] = $link;
		}

		if (! $always_show && ! $links_by_network) {
			return;
		}
	?>
		<div class="<?php echo esc_attr($class); ?>">
			<?php foreach ($networks as $network) : ?>
				<?php
				$link    = $links_by_network[$network] ?? null;
				$url     = is_array($link) && ! empty($link['url']) ? $link['url'] : '';
				$has_url = '' !== $url;
				$icon_id = 'icon-' . $network;
				$icon_w  = 'telegram' === $network ? 16 : 20;
				$icon_h  = 'telegram' === $network ? 14 : 20;
				?>
				<a
					class="messenger-links__item messenger-links__item--<?php echo esc_attr($network); ?><?php echo $has_url ? '' : ' is-disabled'; ?>"
					href="<?php echo esc_url($has_url ? $url : '#'); ?>"
					<?php if ($has_url) : ?>
					target="_blank"
					rel="noopener noreferrer"
					<?php else : ?>
					aria-disabled="true"
					tabindex="-1"
					<?php endif; ?>>
					<?php ksenon_icon($icon_id, $icon_w, $icon_h, 'messenger-links__icon'); ?>
					<span><?php echo esc_html(ksenon_get_footer_social_label($network)); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
<?php
	}
}

if (! function_exists('ksenon_youtube_id')) {
	/**
	 * Extract YouTube video ID from common URL formats.
	 *
	 * @param string $url YouTube URL.
	 * @return string Video ID or empty string.
	 */
	function ksenon_youtube_id($url)
	{
		$url = trim((string) $url);
		if ('' === $url) {
			return '';
		}

		if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{11})~', $url, $matches)) {
			return $matches[1];
		}

		return '';
	}
}
