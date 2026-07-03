<?php

/**
 * Custom post types
 *
 * @package ksenonspb
 */

add_action(
	'init',
	function () {
		register_post_type(
			'service',
			array(
				'labels'              => array(
					'name'                     => 'Услуги',
					'singular_name'            => 'Услуга',
					'menu_name'                => 'Услуги',
					'name_admin_bar'           => 'Услуга',
					'archives'                 => 'Архив услуг',
					'attributes'               => 'Атрибуты услуги',
					'parent_item_colon'        => 'Родительская услуга:',
					'all_items'                => 'Все услуги',
					'add_new'                  => 'Добавить',
					'add_new_item'             => 'Добавить услугу',
					'new_item'                 => 'Новая услуга',
					'edit_item'                => 'Редактировать услугу',
					'update_item'              => 'Обновить услугу',
					'view_item'                => 'Просмотреть услугу',
					'view_items'               => 'Просмотреть услуги',
					'search_items'             => 'Искать услуги',
					'not_found'                => 'Услуги не найдены',
					'not_found_in_trash'       => 'В корзине услуг не найдено',
					'featured_image'           => 'Изображение услуги',
					'set_featured_image'       => 'Установить изображение услуги',
					'remove_featured_image'    => 'Удалить изображение услуги',
					'use_featured_image'       => 'Использовать как изображение услуги',
					'insert_into_item'         => 'Вставить в услугу',
					'uploaded_to_this_item'    => 'Загружено для этой услуги',
					'items_list'               => 'Список услуг',
					'items_list_navigation'    => 'Навигация по списку услуг',
					'filter_items_list'        => 'Фильтровать список услуг',
					'filter_by_date'           => 'Фильтровать по дате',
					'item_published'           => 'Услуга опубликована',
					'item_published_privately' => 'Услуга опубликована приватно',
					'item_reverted_to_draft'   => 'Услуга возвращена в черновики',
					'item_scheduled'           => 'Публикация услуги запланирована',
					'item_updated'             => 'Услуга обновлена',
					'item_link'                => 'Ссылка на услугу',
					'item_link_description'    => 'Ссылка на отдельную услугу',
				),
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'menu_position'       => 5,
				'menu_icon'           => 'dashicons-hammer',
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'supports'            => array('title', 'editor', 'thumbnail', 'excerpt'),
				'has_archive'         => 'uslugi',
				'rewrite'             => array(
					'slug'       => '%service_category%',
					'with_front' => false,
				),
				'show_in_rest'        => true,
			)
		);

		remove_post_type_support('service', 'comments');
		remove_post_type_support('service', 'trackbacks');

		register_taxonomy(
			'service_category',
			'service',
			array(
				'labels'            => array(
					'name'                       => 'Категории услуг',
					'singular_name'              => 'Категория услуги',
					'menu_name'                  => 'Категории',
					'name_admin_bar'             => 'Категория услуги',
					'search_items'               => 'Искать категории',
					'popular_items'              => 'Популярные категории',
					'all_items'                  => 'Все категории',
					'parent_item'                => 'Родительская категория',
					'parent_item_colon'          => 'Родительская категория:',
					'edit_item'                  => 'Редактировать категорию',
					'view_item'                  => 'Просмотреть категорию',
					'update_item'                => 'Обновить категорию',
					'add_new_item'               => 'Добавить категорию',
					'new_item_name'              => 'Название категории',
					'separate_items_with_commas' => 'Разделяйте категории запятыми',
					'add_or_remove_items'        => 'Добавить или удалить категории',
					'choose_from_most_used'      => 'Выбрать из часто используемых',
					'not_found'                  => 'Категории не найдены',
					'no_terms'                   => 'Нет категорий',
					'items_list_navigation'      => 'Навигация по списку категорий',
					'items_list'                 => 'Список категорий',
					'back_to_items'              => '← К категориям',
					'item_link'                  => 'Ссылка на категорию',
					'item_link_description'      => 'Ссылка на отдельную категорию',
				),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_admin_column'  => true,
				'show_in_nav_menus'  => true,
				'show_in_rest'       => true,
				'hierarchical'       => true,
				'rewrite'            => array(
					'slug'         => '.',
					'with_front'   => false,
					'hierarchical' => true,
				),
				'query_var'          => 'service_category',
			)
		);

		register_post_type(
			'portfolio',
			array(
				'labels'              => array(
					'name'                     => 'Портфолио',
					'singular_name'            => 'Кейс',
					'menu_name'                => 'Портфолио',
					'name_admin_bar'           => 'Кейс',
					'archives'                 => 'Архив портфолио',
					'attributes'               => 'Атрибуты кейса',
					'parent_item_colon'        => 'Родительский кейс:',
					'all_items'                => 'Все кейсы',
					'add_new'                  => 'Добавить',
					'add_new_item'             => 'Добавить кейс',
					'new_item'                 => 'Новый кейс',
					'edit_item'                => 'Редактировать кейс',
					'update_item'              => 'Обновить кейс',
					'view_item'                => 'Просмотреть кейс',
					'view_items'               => 'Просмотреть кейсы',
					'search_items'             => 'Искать кейсы',
					'not_found'                => 'Кейсы не найдены',
					'not_found_in_trash'       => 'В корзине кейсов не найдено',
					'featured_image'           => 'Изображение кейса',
					'set_featured_image'       => 'Установить изображение кейса',
					'remove_featured_image'    => 'Удалить изображение кейса',
					'use_featured_image'       => 'Использовать как изображение кейса',
					'insert_into_item'         => 'Вставить в кейс',
					'uploaded_to_this_item'    => 'Загружено для этого кейса',
					'items_list'               => 'Список кейсов',
					'items_list_navigation'    => 'Навигация по списку кейсов',
					'filter_items_list'        => 'Фильтровать список кейсов',
					'filter_by_date'           => 'Фильтровать по дате',
					'item_published'           => 'Кейс опубликован',
					'item_published_privately' => 'Кейс опубликован приватно',
					'item_reverted_to_draft'   => 'Кейс возвращён в черновики',
					'item_scheduled'           => 'Публикация кейса запланирована',
					'item_updated'             => 'Кейс обновлён',
					'item_link'                => 'Ссылка на кейс',
					'item_link_description'    => 'Ссылка на отдельный кейс',
				),
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'menu_position'       => 6,
				'menu_icon'           => 'dashicons-portfolio',
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'supports'            => array('title', 'editor', 'thumbnail', 'excerpt'),
				'has_archive'         => 'portfolio',
				'rewrite'             => array(
					'slug'       => 'portfolio',
					'with_front' => false,
				),
				'show_in_rest'        => true,
			)
		);

		register_post_type(
			'brand',
			array(
				'labels'              => array(
					'name'                     => 'Марки',
					'singular_name'            => 'Марка',
					'menu_name'                => 'Марки',
					'name_admin_bar'           => 'Марка',
					'archives'                 => 'Архив марок',
					'attributes'               => 'Атрибуты марки',
					'parent_item_colon'        => 'Родительская марка:',
					'all_items'                => 'Все марки',
					'add_new'                  => 'Добавить',
					'add_new_item'             => 'Добавить марку',
					'new_item'                 => 'Новая марка',
					'edit_item'                => 'Редактировать марку',
					'update_item'              => 'Обновить марку',
					'view_item'                => 'Просмотреть марку',
					'view_items'               => 'Просмотреть марки',
					'search_items'             => 'Искать марки',
					'not_found'                => 'Марки не найдены',
					'not_found_in_trash'       => 'В корзине марок не найдено',
					'featured_image'           => 'Изображение марки',
					'set_featured_image'       => 'Установить изображение марки',
					'remove_featured_image'    => 'Удалить изображение марки',
					'use_featured_image'       => 'Использовать как изображение марки',
					'insert_into_item'         => 'Вставить в марку',
					'uploaded_to_this_item'    => 'Загружено для этой марки',
					'items_list'               => 'Список марок',
					'items_list_navigation'    => 'Навигация по списку марок',
					'filter_items_list'        => 'Фильтровать список марок',
					'filter_by_date'           => 'Фильтровать по дате',
					'item_published'           => 'Марка опубликована',
					'item_published_privately' => 'Марка опубликована приватно',
					'item_reverted_to_draft'   => 'Марка возвращена в черновики',
					'item_scheduled'           => 'Публикация марки запланирована',
					'item_updated'             => 'Марка обновлена',
					'item_link'                => 'Ссылка на марку',
					'item_link_description'    => 'Ссылка на отдельную марку',
				),
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'menu_position'       => 7,
				'menu_icon'           => 'dashicons-car',
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'supports'            => array('title', 'editor', 'thumbnail', 'excerpt'),
				'has_archive'         => 'marki',
				'rewrite'             => array(
					'slug'       => 'marki',
					'with_front' => false,
				),
				'show_in_rest'        => true,
			)
		);

		register_post_type(
			'promotion',
			array(
				'labels'              => array(
					'name'                     => 'Акции',
					'singular_name'            => 'Акция',
					'menu_name'                => 'Акции',
					'name_admin_bar'           => 'Акция',
					'archives'                 => 'Архив акций',
					'attributes'               => 'Атрибуты акции',
					'parent_item_colon'        => 'Родительская акция:',
					'all_items'                => 'Все акции',
					'add_new'                  => 'Добавить',
					'add_new_item'             => 'Добавить акцию',
					'new_item'                 => 'Новая акция',
					'edit_item'                => 'Редактировать акцию',
					'update_item'              => 'Обновить акцию',
					'view_item'                => 'Просмотреть акцию',
					'view_items'               => 'Просмотреть акции',
					'search_items'             => 'Искать акции',
					'not_found'                => 'Акции не найдены',
					'not_found_in_trash'       => 'В корзине акций не найдено',
					'featured_image'           => 'Изображение акции',
					'set_featured_image'       => 'Установить изображение акции',
					'remove_featured_image'    => 'Удалить изображение акции',
					'use_featured_image'       => 'Использовать как изображение акции',
					'insert_into_item'         => 'Вставить в акцию',
					'uploaded_to_this_item'    => 'Загружено для этой акции',
					'items_list'               => 'Список акций',
					'items_list_navigation'    => 'Навигация по списку акций',
					'filter_items_list'        => 'Фильтровать список акций',
					'filter_by_date'           => 'Фильтровать по дате',
					'item_published'           => 'Акция опубликована',
					'item_published_privately' => 'Акция опубликована приватно',
					'item_reverted_to_draft'   => 'Акция возвращена в черновики',
					'item_scheduled'           => 'Публикация акции запланирована',
					'item_updated'             => 'Акция обновлена',
					'item_link'                => 'Ссылка на акцию',
					'item_link_description'    => 'Ссылка на отдельную акцию',
				),
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'menu_position'       => 8,
				'menu_icon'           => 'dashicons-megaphone',
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'supports'            => array('title', 'editor', 'thumbnail', 'excerpt'),
				'has_archive'         => 'akcii',
				'rewrite'             => array(
					'slug'       => 'akcii',
					'with_front' => false,
				),
				'show_in_rest'        => true,
			)
		);
	}
);

if (! function_exists('ksenon_get_deepest_service_category_term')) {
	/**
	 * @param int $post_id
	 * @return WP_Term|null
	 */
	function ksenon_get_deepest_service_category_term($post_id)
	{
		$terms = get_the_terms((int) $post_id, 'service_category');
		if (! $terms || is_wp_error($terms)) {
			return null;
		}

		$deepest   = null;
		$max_depth = -1;

		foreach ($terms as $term) {
			if (! $term instanceof WP_Term) {
				continue;
			}

			$depth  = 0;
			$parent = (int) $term->parent;
			while ($parent) {
				$depth++;
				$parent_term = get_term($parent, 'service_category');
				$parent      = ($parent_term instanceof WP_Term && ! is_wp_error($parent_term))
					? (int) $parent_term->parent
					: 0;
			}

			if ($depth > $max_depth) {
				$max_depth = $depth;
				$deepest   = $term;
			}
		}

		return $deepest;
	}
}

if (! function_exists('ksenon_get_service_category_path')) {
	/**
	 * @return string Category path without leading/trailing slashes.
	 */
	function ksenon_get_service_category_path(WP_Term $term)
	{
		$segments  = array();
		$ancestors = array_reverse(get_ancestors((int) $term->term_id, 'service_category', 'taxonomy'));

		foreach ($ancestors as $ancestor_id) {
			$ancestor = get_term((int) $ancestor_id, 'service_category');
			if ($ancestor instanceof WP_Term && ! is_wp_error($ancestor)) {
				$segments[] = $ancestor->slug;
			}
		}

		$segments[] = $term->slug;

		return implode('/', $segments);
	}
}

if (! function_exists('ksenon_get_service_url_slug')) {
	/**
	 * Public URL segment for a service (meta service_slug from import, fallback post_name).
	 */
	function ksenon_get_service_url_slug($post_id)
	{
		$post_id = (int) $post_id;
		if ($post_id <= 0) {
			return '';
		}

		$service_slug = get_post_meta($post_id, 'service_slug', true);
		if (is_string($service_slug) && '' !== trim($service_slug)) {
			return sanitize_title($service_slug);
		}

		$post = get_post($post_id);

		return ($post instanceof WP_Post && $post->post_name)
			? sanitize_title($post->post_name)
			: '';
	}
}

if (! function_exists('ksenon_get_service_category_top_parent_slug')) {
	/**
	 * Root category slug for a hierarchical service_category term.
	 */
	function ksenon_get_service_category_top_parent_slug(WP_Term $term)
	{
		while ($term->parent) {
			$parent = get_term((int) $term->parent, 'service_category');
			if (! $parent instanceof WP_Term || is_wp_error($parent)) {
				break;
			}
			$term = $parent;
		}

		return $term->slug;
	}
}

if (! function_exists('ksenon_find_service_category_by_url_path')) {
	/**
	 * Resolve hierarchical service_category term from URL path segments.
	 *
	 * @param string[] $segments
	 * @return WP_Term|null
	 */
	function ksenon_find_service_category_by_url_path(array $segments)
	{
		$segments = array_values(array_filter(array_map('strval', $segments)));
		if (empty($segments)) {
			return null;
		}

		$parent_id = 0;
		$term      = null;

		foreach ($segments as $slug) {
			$matches = get_terms(
				array(
					'taxonomy'   => 'service_category',
					'slug'       => sanitize_title($slug),
					'parent'     => $parent_id,
					'hide_empty' => false,
					'number'     => 1,
				)
			);

			if (is_wp_error($matches) || empty($matches) || ! $matches[0] instanceof WP_Term) {
				return null;
			}

			$term      = $matches[0];
			$parent_id = (int) $term->term_id;
		}

		return $term;
	}
}

if (! function_exists('ksenon_find_service_by_url_path')) {
	/**
	 * Resolve service post ID from public URL path segments.
	 *
	 * @param string[] $segments
	 */
	function ksenon_find_service_by_url_path(array $segments)
	{
		$segments = array_values(array_filter(array_map('strval', $segments)));
		$count    = count($segments);

		if ($count < 2 || $count > 3) {
			return 0;
		}

		$url_slug = $segments[$count - 1];
		if ('' === $url_slug) {
			return 0;
		}

		if (2 === $count) {
			$maybe_term = get_term_by('slug', $url_slug, 'service_category');
			if ($maybe_term instanceof WP_Term && ! is_wp_error($maybe_term)) {
				return 0;
			}
		}

		$category_path = implode('/', array_slice($segments, 0, -1));
		$candidates = get_posts(
			array(
				'post_type'              => 'service',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => true,
				'meta_query'             => array(
					array(
						'key'   => 'service_slug',
						'value' => $url_slug,
					),
				),
			)
		);

		if (empty($candidates)) {
			$candidates = get_posts(
				array(
					'post_type'              => 'service',
					'post_status'            => 'publish',
					'posts_per_page'         => -1,
					'fields'                 => 'ids',
					'name'                   => $url_slug,
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => true,
				)
			);
		}

		foreach ($candidates as $post_id) {
			$term = ksenon_get_deepest_service_category_term((int) $post_id);
			if (! $term) {
				continue;
			}

			if (ksenon_get_service_category_path($term) === $category_path) {
				return (int) $post_id;
			}
		}

		return 0;
	}
}

add_filter(
	'request',
	function ($query_vars) {
		if (is_admin() || ! empty($query_vars['p'])) {
			return $query_vars;
		}

		global $wp;
		$request  = isset($wp->request) ? trim((string) $wp->request, '/') : '';
		$segments = '' === $request ? array() : explode('/', $request);

		if ('' === $request) {
			return $query_vars;
		}

		$segment_count = count($segments);

		if ($segment_count >= 2 && $segment_count <= 3) {
			$post_id = ksenon_find_service_by_url_path($segments);
			if ($post_id > 0) {
				return array(
					'p'         => $post_id,
					'post_type' => 'service',
				);
			}
		}

		if ($segment_count >= 1 && $segment_count <= 2) {
			$category_term = ksenon_find_service_category_by_url_path($segments);
			if ($category_term instanceof WP_Term) {
				return array(
					'taxonomy'         => 'service_category',
					'service_category' => $category_term->slug,
					'term'             => $category_term->slug,
				);
			}
		}

		return $query_vars;
	},
	1
);

add_filter(
	'post_type_link',
	function ($permalink, $post) {
		if (! $post instanceof WP_Post || 'service' !== $post->post_type) {
			return $permalink;
		}

		$term = ksenon_get_deepest_service_category_term((int) $post->ID);
		if (! $term) {
			return $permalink;
		}

		$path = ksenon_get_service_category_path($term);
		$url_slug = ksenon_get_service_url_slug((int) $post->ID);
		if (! $path || ! $url_slug) {
			return $permalink;
		}

		return home_url(user_trailingslashit($path . '/' . $url_slug));
	},
	10,
	2
);

add_action(
	'pre_get_posts',
	function ($query) {
		if (is_admin() || ! $query->is_main_query() || ! $query->is_tax('service_category')) {
			return;
		}

		$term = get_queried_object();
		if (! $term instanceof WP_Term) {
			return;
		}

		$children = get_terms(
			array(
				'taxonomy'   => 'service_category',
				'parent'     => (int) $term->term_id,
				'hide_empty' => false,
				'fields'     => 'ids',
			)
		);

		$query->set('post_type', 'service');
		$query->set(
			'tax_query',
			array(
				array(
					'taxonomy'         => 'service_category',
					'field'            => 'term_id',
					'terms'            => array((int) $term->term_id),
					'include_children' => ! is_wp_error($children) && ! empty($children),
				),
			)
		);
	}
);

add_action(
	'init',
	function () {
		$version = '20260703-ksenon-cpt-v8';

		if (get_option('ksenon_rewrite_version') === $version) {
			return;
		}

		flush_rewrite_rules(false);
		update_option('ksenon_rewrite_version', $version);
	},
	99
);
