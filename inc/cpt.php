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
					'slug'       => 'uslugi',
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
				'public'            => true,
				'publicly_queryable' => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => false,
				'show_in_rest'      => true,
				'hierarchical'      => true,
				'rewrite'           => false,
				'query_var'         => false,
			)
		);

		ksenon_seed_service_categories();

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

if (! function_exists('ksenon_get_service_category_definitions')) {
	/**
	 * Fixed service categories (slug => name).
	 *
	 * @return array<string, string>
	 */
	function ksenon_get_service_category_definitions()
	{
		return array(
			'remont'                  => 'Ремонт',
			'tyuning'                 => 'Тюнинг',
			'slozhnaya-elektronika'   => 'Сложная электроника',
			'soputstvuyushchie-uslugi' => 'Сопутствующие услуги',
			'pokupka-far'             => 'Покупка фар',
			'drugoe'                  => 'Другое',
		);
	}
}

if (! function_exists('ksenon_seed_service_categories')) {
	function ksenon_seed_service_categories()
	{
		foreach (ksenon_get_service_category_definitions() as $slug => $name) {
			if (term_exists($slug, 'service_category')) {
				continue;
			}

			wp_insert_term(
				$name,
				'service_category',
				array(
					'slug' => $slug,
				)
			);
		}
	}
}

add_filter(
	'pre_insert_term',
	function ($term, $taxonomy, $args = array()) {
		if ('service_category' !== $taxonomy) {
			return $term;
		}

		$slug = '';
		if (! empty($args['slug'])) {
			$slug = sanitize_title((string) $args['slug']);
		} elseif (is_string($term)) {
			$slug = sanitize_title($term);
		}

		$allowed_slugs = array_keys(ksenon_get_service_category_definitions());
		if ($slug && in_array($slug, $allowed_slugs, true) && ! term_exists($slug, 'service_category')) {
			return $term;
		}

		return new WP_Error(
			'ksenon_service_category_locked',
			__('Категории услуг фиксированы. Новые категории добавляются только через код темы.', 'ksenonspb')
		);
	},
	10,
	3
);

add_action(
	'admin_head-edit-tags.php',
	function () {
		if ('service_category' !== get_current_screen()?->taxonomy) {
			return;
		}

		echo '<style>.taxonomy-service_category .page-title-action { display: none; }</style>';
	}
);

add_action(
	'init',
	function () {
		$version = '20260703-ksenon-cpt-v2';

		if (get_option('ksenon_rewrite_version') === $version) {
			return;
		}

		flush_rewrite_rules(false);
		update_option('ksenon_rewrite_version', $version);
	},
	99
);
