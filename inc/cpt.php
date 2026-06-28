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
					'name'          => 'Услуги',
					'singular_name' => 'Услуга',
					'menu_name'     => 'Услуги',
					'all_items'     => 'Все услуги',
					'add_new_item'  => 'Добавить услугу',
					'edit_item'     => 'Редактировать услугу',
					'view_item'     => 'Просмотреть услугу',
					'search_items'  => 'Искать услуги',
					'not_found'     => 'Услуги не найдены',
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
				'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'has_archive'         => 'uslugi',
				'rewrite'             => array(
					'slug'       => 'uslugi',
					'with_front' => false,
				),
				'show_in_rest'        => true,
			)
		);

		register_post_type(
			'portfolio',
			array(
				'labels'              => array(
					'name'          => 'Портфолио',
					'singular_name' => 'Кейс',
					'menu_name'     => 'Портфолио',
					'all_items'     => 'Все кейсы',
					'add_new_item'  => 'Добавить кейс',
					'edit_item'     => 'Редактировать кейс',
					'view_item'     => 'Просмотреть кейс',
					'search_items'  => 'Искать кейсы',
					'not_found'     => 'Кейсы не найдены',
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
				'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
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
					'name'          => 'Марки',
					'singular_name' => 'Марка',
					'menu_name'     => 'Марки',
					'all_items'     => 'Все марки',
					'add_new_item'  => 'Добавить марку',
					'edit_item'     => 'Редактировать марку',
					'view_item'     => 'Просмотреть марку',
					'search_items'  => 'Искать марки',
					'not_found'     => 'Марки не найдены',
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
				'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
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
					'name'          => 'Акции',
					'singular_name' => 'Акция',
					'menu_name'     => 'Акции',
					'all_items'     => 'Все акции',
					'add_new_item'  => 'Добавить акцию',
					'edit_item'     => 'Редактировать акцию',
					'view_item'     => 'Просмотреть акцию',
					'search_items'  => 'Искать акции',
					'not_found'     => 'Акции не найдены',
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
				'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
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

add_action(
	'init',
	function () {
		$version = '20260626-ksenon-cpt-v1';

		if ( get_option( 'ksenon_rewrite_version' ) === $version ) {
			return;
		}

		flush_rewrite_rules( false );
		update_option( 'ksenon_rewrite_version', $version );
	},
	99
);
