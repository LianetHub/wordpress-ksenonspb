<?php
/**
 * Template helpers
 *
 * @package ksenonspb
 */

if ( ! function_exists( 'ksenon_anim_class' ) ) {
	function ksenon_anim_class( string $type = 'fade-up', string $extra = '' ): string {
		return '';
	}
}

if ( ! function_exists( 'ksenon_home_get' ) ) {
	function ksenon_home_get( $key, $default = '' ) {
		if ( function_exists( 'get_sub_field' ) ) {
			$value = get_sub_field( $key );
			if ( null !== $value && '' !== $value && false !== $value ) {
				return $value;
			}
		}

		return $default;
	}
}

if ( ! function_exists( 'ksenon_home_rows' ) ) {
	function ksenon_home_rows( $key ) {
		static $cache = array();

		if ( ! function_exists( 'get_sub_field' ) ) {
			return array();
		}

		$context = 'default';
		if ( function_exists( 'get_row_layout' ) && function_exists( 'get_row_index' ) ) {
			$context = get_row_layout() . ':' . get_row_index();
		}

		$cache_key = $context . ':' . $key;
		if ( isset( $cache[ $cache_key ] ) ) {
			return $cache[ $cache_key ];
		}

		$value = get_sub_field( $key );
		if ( ! is_array( $value ) || array() === $value ) {
			$cache[ $cache_key ] = array();
		} else {
			$cache[ $cache_key ] = array_values( $value );
		}

		return $cache[ $cache_key ];
	}
}

if ( ! function_exists( 'ksenon_acf_link_url' ) ) {
	function ksenon_acf_link_url( $link, $fallback = '#' ) {
		if ( ! is_array( $link ) || empty( $link['url'] ) ) {
			return $fallback;
		}

		return ksenon_resolve_link( $link['url'] );
	}
}

if ( ! function_exists( 'ksenon_acf_link_title' ) ) {
	function ksenon_acf_link_title( $link, $fallback = '' ) {
		if ( ! is_array( $link ) ) {
			return $fallback;
		}

		return (string) ( $link['title'] ?? $fallback );
	}
}

if ( ! function_exists( 'ksenon_acf_link_target' ) ) {
	function ksenon_acf_link_target( $link ) {
		if ( ! is_array( $link ) || empty( $link['target'] ) ) {
			return '';
		}

		return (string) $link['target'];
	}
}

if ( ! function_exists( 'ksenon_normalize_link' ) ) {
	function ksenon_normalize_link( $link ) {
		if ( is_array( $link ) ) {
			return trim( (string) ( $link['url'] ?? '' ) );
		}

		return trim( (string) $link );
	}
}

if ( ! function_exists( 'ksenon_resolve_link' ) ) {
	function ksenon_resolve_link( $link ) {
		$link = ksenon_normalize_link( $link );
		if ( '' === $link || '#' === $link ) {
			return $link;
		}
		if ( str_starts_with( $link, '#' ) || str_starts_with( $link, 'mailto:' ) || str_starts_with( $link, 'tel:' ) ) {
			return $link;
		}
		if ( preg_match( '#^https?://#i', $link ) ) {
			return $link;
		}

		return home_url( '/' . ltrim( $link, '/' ) );
	}
}

if ( ! function_exists( 'ksenon_esc_link' ) ) {
	function ksenon_esc_link( $link ) {
		$resolved = ksenon_resolve_link( $link );

		if ( str_starts_with( $resolved, '#' ) ) {
			return esc_attr( $resolved );
		}

		return esc_url( $resolved );
	}
}

if ( ! function_exists( 'ksenon_menu_link_to_path' ) ) {
	function ksenon_menu_link_to_path( $link ) {
		$normalized = ksenon_normalize_link( $link );
		if ( '' === $normalized || '#' === $normalized ) {
			return '';
		}
		if ( str_starts_with( $normalized, '#' ) || str_starts_with( $normalized, 'mailto:' ) || str_starts_with( $normalized, 'tel:' ) ) {
			return '';
		}

		$resolved  = ksenon_resolve_link( $normalized );
		$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$link_host = wp_parse_url( $resolved, PHP_URL_HOST );

		if ( $link_host && $home_host && strtolower( (string) $link_host ) !== strtolower( (string) $home_host ) ) {
			return untrailingslashit( $resolved );
		}

		$path = wp_parse_url( $resolved, PHP_URL_PATH );
		if ( ! $path || '/' === $path ) {
			return '/';
		}

		return user_trailingslashit( $path );
	}
}

if ( ! function_exists( 'ksenon_get_current_request_path' ) ) {
	function ksenon_get_current_request_path() {
		if ( is_front_page() ) {
			return '/';
		}

		global $wp;
		$request = isset( $wp->request ) ? trim( (string) $wp->request, '/' ) : '';
		if ( '' === $request ) {
			return '/';
		}

		return user_trailingslashit( '/' . $request );
	}
}

if ( ! function_exists( 'ksenon_menu_section_is_active' ) ) {
	function ksenon_menu_section_is_active( $menu_path ) {
		$slug = trim( (string) $menu_path, '/' );
		$root = '' === $slug ? '' : strtok( $slug, '/' );

		switch ( $root ) {
			case '':
				return is_front_page();
			case 'uslugi':
				return is_post_type_archive( 'service' ) || is_singular( 'service' );
			case 'portfolio':
				return is_post_type_archive( 'portfolio' ) || is_singular( 'portfolio' );
			case 'marki':
				return is_post_type_archive( 'brand' ) || is_singular( 'brand' );
			case 'akcii':
				return is_post_type_archive( 'promotion' ) || is_singular( 'promotion' );
			case 'o-kompanii':
				return is_page_template( 'page-o-kompanii.php' ) || is_page( 'o-kompanii' );
			case 'stoimost':
				return is_page_template( 'page-stoimost.php' ) || is_page( 'stoimost' );
			case 'privacy-policy':
				return is_page_template( 'page-policy.php' ) || is_page( 'privacy-policy' );
		}

		return false;
	}
}

if ( ! function_exists( 'ksenon_menu_page_is_active' ) ) {
	function ksenon_menu_page_is_active( $menu_path ) {
		if ( ! is_page() ) {
			return false;
		}

		$slug = trim( (string) $menu_path, '/' );
		if ( '' === $slug ) {
			return is_front_page();
		}

		$page = get_page_by_path( $slug );
		if ( ! $page ) {
			return false;
		}

		$current_id = (int) get_queried_object_id();

		return $current_id === (int) $page->ID
			|| in_array( (int) $page->ID, get_post_ancestors( $current_id ), true );
	}
}

if ( ! function_exists( 'ksenon_is_menu_link_active' ) ) {
	function ksenon_is_menu_link_active( $link ) {
		$menu_path = ksenon_menu_link_to_path( $link );
		if ( '' === $menu_path ) {
			return false;
		}

		if ( preg_match( '#^https?://#i', $menu_path ) ) {
			$current = ( is_ssl() ? 'https://' : 'http://' ) . ( $_SERVER['HTTP_HOST'] ?? '' ) . ( $_SERVER['REQUEST_URI'] ?? '' );
			$current = untrailingslashit( strtok( $current, '?' ) );

			return $current === $menu_path;
		}

		$current_path = ksenon_get_current_request_path();
		if ( untrailingslashit( $current_path ) === untrailingslashit( $menu_path ) ) {
			return true;
		}

		if ( ksenon_menu_section_is_active( $menu_path ) ) {
			return true;
		}

		return ksenon_menu_page_is_active( $menu_path );
	}
}

if ( ! function_exists( 'ksenon_get_post_field' ) ) {
	function ksenon_get_post_field( $key, $post_id = 0 ) {
		static $cache = array();

		$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
		if ( ! $post_id || ! function_exists( 'get_field' ) ) {
			return false;
		}

		if ( ! isset( $cache[ $post_id ] ) ) {
			$cache[ $post_id ] = function_exists( 'get_fields' ) ? ( get_fields( $post_id ) ?: array() ) : array();
		}

		if ( array_key_exists( $key, $cache[ $post_id ] ) ) {
			return $cache[ $post_id ][ $key ];
		}

		$value                     = get_field( $key, $post_id );
		$cache[ $post_id ][ $key ] = $value;

		return $value;
	}
}

if ( ! function_exists( 'ksenon_normalize_faq_items' ) ) {
	function ksenon_normalize_faq_items( $items ) {
		if ( ! is_array( $items ) ) {
			return array();
		}

		$normalized = array();
		foreach ( $items as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$question = trim( (string) ( $item['question'] ?? '' ) );
			if ( '' === $question ) {
				continue;
			}
			$normalized[] = array(
				'question' => $question,
				'answer'   => (string) ( $item['answer'] ?? '' ),
				'is_open'  => ! empty( $item['is_open'] ),
			);
		}

		return $normalized;
	}
}

if ( ! function_exists( 'ksenon_get_reviews' ) ) {
	function ksenon_get_reviews() {
		$rows = ksenon_get_option( 'reviews', array() );
		if ( ! is_array( $rows ) ) {
			return array();
		}

		$reviews = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$text = trim( (string) ( $row['text'] ?? '' ) );
			$name = trim( (string) ( $row['name'] ?? '' ) );
			if ( '' === $text && '' === $name ) {
				continue;
			}
			$reviews[] = array(
				'name'   => $name,
				'text'   => $text,
				'photo'  => $row['photo'] ?? null,
				'rating' => (int) ( $row['rating'] ?? 5 ),
			);
		}

		return $reviews;
	}
}

if ( ! function_exists( 'ksenon_cta_form_config' ) ) {
	function ksenon_cta_form_config( $variant = 'service_not_found' ) {
		$variants = array(
			'service_not_found' => array(
				'title'       => __( 'Не нашли свою услугу?', 'ksenonspb' ),
				'cf7_option'  => 'cf7_zakaz',
				'form_source' => __( 'Не нашли услугу', 'ksenonspb' ),
			),
			'same_result'       => array(
				'title'       => __( 'Хотите такой же результат?', 'ksenonspb' ),
				'cf7_option'  => 'cf7_zakaz',
				'form_source' => __( 'Хотите такой же результат', 'ksenonspb' ),
			),
			'free_inspection'   => array(
				'title'       => __( 'Убедились? Запишитесь на бесплатный осмотр', 'ksenonspb' ),
				'cf7_option'  => 'cf7_konsultaciya',
				'form_source' => __( 'Бесплатный осмотр', 'ksenonspb' ),
			),
			'appointment'       => array(
				'title'       => __( 'Запишитесь на установку', 'ksenonspb' ),
				'cf7_option'  => 'cf7_zakaz',
				'form_source' => __( 'Запись на установку', 'ksenonspb' ),
			),
		);

		return $variants[ $variant ] ?? $variants['service_not_found'];
	}
}

if ( ! function_exists( 'ksenon_query_cpt' ) ) {
	function ksenon_query_cpt( $post_type, $args = array() ) {
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

		return new WP_Query( wp_parse_args( $args, $defaults ) );
	}
}

if ( ! function_exists( 'ksenon_query_services' ) ) {
	function ksenon_query_services( $args = array() ) {
		return ksenon_query_cpt( 'service', $args );
	}
}

if ( ! function_exists( 'ksenon_query_portfolio' ) ) {
	function ksenon_query_portfolio( $args = array() ) {
		return ksenon_query_cpt( 'portfolio', $args );
	}
}

if ( ! function_exists( 'ksenon_query_brands' ) ) {
	function ksenon_query_brands( $args = array() ) {
		return ksenon_query_cpt( 'brand', $args );
	}
}

if ( ! function_exists( 'ksenon_query_promotions' ) ) {
	function ksenon_query_promotions( $args = array() ) {
		return ksenon_query_cpt( 'promotion', $args );
	}
}

if ( ! function_exists( 'ksenon_get_related_ids' ) ) {
	function ksenon_get_related_ids( $field, $post_id = 0 ) {
		$value = ksenon_get_post_field( $field, $post_id );
		if ( ! is_array( $value ) ) {
			return array();
		}

		$ids = array();
		foreach ( $value as $item ) {
			if ( $item instanceof WP_Post ) {
				$ids[] = (int) $item->ID;
			} elseif ( is_numeric( $item ) ) {
				$ids[] = (int) $item;
			}
		}

		return array_values( array_filter( array_unique( $ids ) ) );
	}
}

if ( ! function_exists( 'ksenon_get_related_portfolio' ) ) {
	function ksenon_get_related_portfolio( $post_id = 0, $limit = 4 ) {
		$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
		$service_ids = ksenon_get_related_ids( 'related_services', $post_id );
		$brand_ids   = ksenon_get_related_ids( 'related_brands', $post_id );

		$meta_query = array( 'relation' => 'OR' );
		if ( $service_ids ) {
			$meta_query[] = array(
				'key'     => 'related_services',
				'value'   => '"' . implode( '"|"', array_map( 'strval', $service_ids ) ) . '"',
				'compare' => 'LIKE',
			);
		}
		if ( $brand_ids ) {
			$meta_query[] = array(
				'key'     => 'related_brands',
				'value'   => '"' . implode( '"|"', array_map( 'strval', $brand_ids ) ) . '"',
				'compare' => 'LIKE',
			);
		}

		$args = array(
			'posts_per_page' => $limit,
			'post__not_in'   => array( $post_id ),
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( count( $meta_query ) > 1 ) {
			$args['meta_query'] = $meta_query;
		}

		return ksenon_query_portfolio( $args );
	}
}

if ( ! function_exists( 'ksenon_services_archive_url' ) ) {
	function ksenon_services_archive_url() {
		$url = get_post_type_archive_link( 'service' );
		return $url ?: home_url( '/uslugi/' );
	}
}

if ( ! function_exists( 'ksenon_portfolio_archive_url' ) ) {
	function ksenon_portfolio_archive_url() {
		$url = get_post_type_archive_link( 'portfolio' );
		return $url ?: home_url( '/portfolio/' );
	}
}

if ( ! function_exists( 'ksenon_brands_archive_url' ) ) {
	function ksenon_brands_archive_url() {
		$url = get_post_type_archive_link( 'brand' );
		return $url ?: home_url( '/marki/' );
	}
}

if ( ! function_exists( 'ksenon_promotions_archive_url' ) ) {
	function ksenon_promotions_archive_url() {
		$url = get_post_type_archive_link( 'promotion' );
		return $url ?: home_url( '/akcii/' );
	}
}

if ( ! function_exists( 'ksenon_get_phones' ) ) {
	function ksenon_get_phones( $header_only = false ) {
		$phones = array();

		if ( ! function_exists( 'have_rows' ) || ! have_rows( 'telefony', 'option' ) ) {
			return $phones;
		}

		while ( have_rows( 'telefony', 'option' ) ) {
			the_row();
			$number = get_sub_field( 'nomer' );
			if ( ! $number ) {
				continue;
			}
			if ( $header_only && ! get_sub_field( 'v_shapke' ) ) {
				continue;
			}
			$phones[] = $number;
		}

		return $phones;
	}
}

if ( ! function_exists( 'ksenon_get_map_settings' ) ) {
	function ksenon_get_map_settings() {
		$icon_default = 'img/placemark.svg';
		$icon         = ksenon_get_option( 'karta_ikonka', $icon_default );

		return array(
			'show'   => (bool) ksenon_get_option( 'karta_pokazyvat', 1 ),
			'coords' => ksenon_get_option( 'karta_koordinaty', '59.985,30.315' ),
			'zoom'   => (int) ksenon_get_option( 'karta_zoom', 16 ),
			'label'  => ksenon_get_option( 'karta_podpis', 'Карта' ),
			'apiKey' => ksenon_get_option( 'karta_api_klyuch', '' ),
			'icon'   => ksenon_acf_image_url( $icon, 'full', ksenon_assets_uri( $icon_default ) ),
		);
	}
}

if ( ! function_exists( 'ksenon_get_map_html' ) ) {
	function ksenon_get_map_html( $id = 'contacts-map', $class = 'contacts-order__map' ) {
		$map = ksenon_get_map_settings();
		if ( ! $map['show'] ) {
			return '';
		}

		return sprintf(
			'<div class="%1$s" id="%2$s" data-map data-coords="%3$s" data-zoom="%4$d" data-icon="%5$s" role="region" aria-label="%6$s" aria-busy="true"></div>',
			esc_attr( $class ),
			esc_attr( $id ),
			esc_attr( $map['coords'] ),
			(int) $map['zoom'],
			esc_url( $map['icon'] ),
			esc_attr( $map['label'] )
		);
	}
}

if ( ! function_exists( 'ksenon_get_main_class' ) ) {
	function ksenon_get_main_class() {
		if ( is_front_page() ) {
			return 'main--home';
		}
		if ( is_404() ) {
			return 'main--not-found';
		}
		if ( is_search() ) {
			return 'main--search';
		}
		if ( is_singular( 'service' ) ) {
			return 'main--service';
		}
		if ( is_post_type_archive( 'service' ) || is_page_template( 'page-uslugi.php' ) ) {
			return 'main--services';
		}
		if ( is_singular( 'portfolio' ) ) {
			return 'main--case';
		}
		if ( is_post_type_archive( 'portfolio' ) ) {
			return 'main--portfolio';
		}
		if ( is_singular( 'brand' ) ) {
			return 'main--brand';
		}
		if ( is_post_type_archive( 'brand' ) ) {
			return 'main--brands';
		}
		if ( is_singular( 'promotion' ) ) {
			return 'main--promotion';
		}
		if ( is_post_type_archive( 'promotion' ) ) {
			return 'main--promotions';
		}
		if ( is_page_template( 'page-o-kompanii.php' ) ) {
			return 'main--about';
		}
		if ( is_page_template( 'page-stoimost.php' ) ) {
			return 'main--pricing';
		}
		if ( is_page_template( 'page-policy.php' ) || is_page( 'privacy-policy' ) ) {
			return 'main--policy';
		}

		return '';
	}
}

if ( ! function_exists( 'ksenon_get_option_raw' ) ) {
	function ksenon_get_option_raw( $key ) {
		static $cache = null;

		if ( ! function_exists( 'get_field' ) ) {
			return null;
		}

		if ( null === $cache ) {
			$cache = function_exists( 'get_fields' ) ? ( get_fields( 'option' ) ?: array() ) : array();
		}

		if ( array_key_exists( $key, $cache ) ) {
			return $cache[ $key ];
		}

		$value         = get_field( $key, 'option' );
		$cache[ $key ] = $value;

		return $value;
	}
}

if ( ! function_exists( 'ksenon_get_option' ) ) {
	function ksenon_get_option( $key, $default = '' ) {
		if ( ! function_exists( 'get_field' ) ) {
			return $default;
		}

		$value = ksenon_get_option_raw( $key );
		if ( null !== $value && '' !== $value && false !== $value ) {
			return $value;
		}

		return $default;
	}
}

if ( ! function_exists( 'ksenon_render_favicons' ) ) {
	function ksenon_render_favicons() {
		$png_url      = ksenon_acf_image_url( ksenon_get_option( 'favicon_png' ), 'full', ksenon_assets_uri( 'favicon-96x96.png' ) );
		$svg_url      = ksenon_acf_image_url( ksenon_get_option( 'favicon_svg' ), 'full', ksenon_assets_uri( 'favicon.svg' ) );
		$ico_url      = ksenon_acf_image_url( ksenon_get_option( 'favicon_ico' ), 'full', ksenon_assets_uri( 'favicon.ico' ) );
		$apple_url    = ksenon_acf_image_url( ksenon_get_option( 'favicon_apple' ), 'full', ksenon_assets_uri( 'apple-touch-icon.png' ) );
		$manifest_url = ksenon_web_manifest_url();

		printf( '<link rel="icon" type="image/png" href="%s" sizes="96x96">' . "\n", esc_url( $png_url ) );
		printf( '<link rel="icon" type="image/svg+xml" href="%s">' . "\n", esc_url( $svg_url ) );
		printf( '<link rel="shortcut icon" href="%s">' . "\n", esc_url( $ico_url ) );
		printf( '<link rel="apple-touch-icon" sizes="180x180" href="%s">' . "\n", esc_url( $apple_url ) );
		printf( '<meta name="apple-mobile-web-app-title" content="%s">' . "\n", esc_attr( 'КБ АВТО' ) );
		printf( '<link rel="manifest" href="%s">' . "\n", esc_url( $manifest_url ) );
	}
}

if ( ! function_exists( 'ksenon_web_manifest_url' ) ) {
	function ksenon_web_manifest_url() {
		return home_url( '/site.webmanifest' );
	}
}

if ( ! function_exists( 'ksenon_get_web_manifest_data' ) ) {
	function ksenon_get_web_manifest_data() {
		$icon_192 = ksenon_acf_image_url( ksenon_get_option( 'favicon_manifest_192' ), 'full', ksenon_assets_uri( 'web-app-manifest-192x192.png' ) );
		$icon_512 = ksenon_acf_image_url( ksenon_get_option( 'favicon_manifest_512' ), 'full', ksenon_assets_uri( 'web-app-manifest-512x512.png' ) );

		return array(
			'name'             => 'КБ АВТО',
			'short_name'       => 'КБ АВТО',
			'icons'            => array(
				array(
					'src'     => $icon_192,
					'sizes'   => '192x192',
					'type'    => 'image/png',
					'purpose' => 'maskable',
				),
				array(
					'src'     => $icon_512,
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

if ( ! function_exists( 'ksenon_render_main_menu' ) ) {
	function ksenon_render_main_menu( $menu_class = 'header__menu', $item_class = 'header__item', $link_class = 'header__link' ) {
		get_template_part(
			'template-parts/nav/menu',
			null,
			array(
				'menu_class' => $menu_class,
				'item_class' => $item_class,
				'link_class' => $link_class,
			)
		);
	}
}

if ( ! function_exists( 'ksenon_get_footer_domain' ) ) {
	function ksenon_get_footer_domain() {
		$domain = ksenon_get_option( 'site_domain', '' );
		if ( $domain ) {
			return $domain;
		}

		$host = wp_parse_url( home_url(), PHP_URL_HOST );

		return $host ? $host : 'ksenonspb.ru';
	}
}

if ( ! function_exists( 'ksenon_get_social_links' ) ) {
	function ksenon_get_social_links() {
		$rows = ksenon_get_option( 'social_links', array() );
		if ( ! is_array( $rows ) ) {
			return array();
		}

		$links = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$url = isset( $row['url'] ) ? trim( (string) $row['url'] ) : '';
			if ( ! $url ) {
				continue;
			}

			$network = isset( $row['network'] ) ? sanitize_key( $row['network'] ) : '';
			if ( ! in_array( $network, array( 'telegram', 'whatsapp', 'vk', 'youtube' ), true ) ) {
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

if ( ! function_exists( 'ksenon_get_footer_social_label' ) ) {
	function ksenon_get_footer_social_label( $network ) {
		$labels = array(
			'telegram' => 'Telegram',
			'whatsapp' => 'WhatsApp',
			'vk'       => 'VK',
			'youtube'  => 'YouTube',
		);

		return $labels[ $network ] ?? $network;
	}
}

if ( ! function_exists( 'ksenon_get_footer_social_icon' ) ) {
	function ksenon_get_footer_social_icon( $network ) {
		$icons = array(
			'telegram' => '<svg width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M14.5 0C6.492 0 0 6.492 0 14.5S6.492 29 14.5 29 29 22.508 29 14.5 22.508 0 14.5 0Zm6.676 9.838-2.32 10.936c-.175.787-.64.98-1.295.61l-3.58-2.64-1.728 1.662c-.19.19-.35.35-.717.35l.257-3.64 6.62-5.98c.288-.256-.063-.398-.446-.142l-8.18 5.15-3.53-1.1c-.766-.24-.78-.766.16-1.132l13.78-5.31c.637-.23 1.194.147.98 1.056Z" fill="#2AABEE"/></svg>',
			'whatsapp' => '<svg width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M14.5 0C6.492 0 0 6.492 0 14.5c0 2.553.667 4.953 1.835 7.035L0 29l7.678-1.802A14.43 14.43 0 0 0 14.5 29C22.508 29 29 22.508 29 14.5S22.508 0 14.5 0Zm7.865 20.135c-.328.925-1.92 1.768-2.65 1.88-.675.102-1.545.145-2.495-.15-.575-.188-1.315-.44-2.265-.86-3.985-1.725-6.585-5.785-6.785-6.055-.195-.27-1.62-2.155-1.62-4.11 0-1.955 1.03-2.92 1.395-3.32.365-.4.795-.5 1.06-.5.265 0 .53.002.76.013.245.012.573-.093.895.685.328.795 1.115 2.725 1.213 2.923.098.198.163.428.033.688-.13.26-.195.425-.39.655-.195.23-.41.515-.585.69-.195.195-.398.405-.17.795.228.39 1.013 1.67 2.175 2.705 1.495 1.328 2.755 1.74 3.145 1.935.39.195.618.163.845-.098.228-.26.975-1.138 1.235-1.528.26-.39.52-.325.875-.195.355.13 2.255 1.063 2.64 1.255.385.193.64.288.735.445.095.158.095.915-.233 1.84Z" fill="#25D366"/></svg>',
			'vk'       => '<svg width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M14.5 0C6.492 0 0 6.492 0 14.5S6.492 29 14.5 29 29 22.508 29 14.5 22.508 0 14.5 0Zm8.015 15.468c.885-.93 1.74-1.83 2.385-2.655.42-.54.735-1.005.735-1.455 0-.405-.285-.615-.855-.615h-2.25c-.675 0-.99.315-1.17.795-.405 1.08-.945 2.07-1.575 2.97-.225.315-.465.585-.72.585-.165 0-.24-.12-.24-.375v-2.85c0-.675-.195-.96-.765-.96-1.215 0-2.415.315-3.375 1.815-.975 1.53-1.455 3.375-1.455 3.375s-.075.465-.42.465H8.44c-.405 0-.495-.21-.375-.615.165-.555 1.95-4.455 4.05-6.675.78-.855 1.695-1.275 2.295-1.275.525 0 .675.285.675.93v2.175c0 .525.225.705.375.705.165 0 .3-.09.585-.375 1.005-1.065 1.725-2.715 1.725-2.715.09-.195.24-.375.615-.375h2.25c.675 0 .825.345.675.825-.225.765-1.185 2.385-1.185 2.385-.195.33-.27.495 0 .855.195.27.825.81 1.245 1.305.765.885 1.35 1.635 1.5 2.145.15.525-.075.795-.6.795h-2.25c-.48 0-.69-.225-.975-.615-.705-.945-1.47-1.845-1.845-2.325-.165-.21-.33-.255-.495-.075-.375.42-.855 1.065-1.275 1.545-.24.27-.495.285-.825.105-.615-.33-1.455-.975-2.025-1.755 2.55-3.795 4.755-8.07 4.755-8.07.12-.27.015-.495-.33-.495h-2.25c-.405 0-.585.195-.765.495 0 0-2.85 4.335-6.6 7.14-.375.285-.585.42-.795.42-.195 0-.285-.12-.285-.375v-2.85c0-.675-.24-.96-.855-.96-.675 0-1.365.165-1.365.675 0 .345.525.645.525 2.385 0 .72-.135 1.71-.405 2.385-.285.705-.795 1.305-1.185 1.305-.33 0-.585-.27-.585-.855V9.84c0-.675-.195-.96-.765-.96H4.89c-.405 0-.615.195-.615.495 0 .585.885 3.495 4.155 7.365 2.145 2.535 4.875 3.75 7.365 3.75.465 0 .705-.21.705-.675v-1.605c0-.585.255-.705.555-.585.345.135 1.365.855 1.905 1.545.345.435.615.63 1.005.63h2.25c.675 0 .825-.315.615-.795-.195-.435-1.425-2.385-1.425-2.385-.195-.27-.165-.495.075-.765.195-.225.735-.705 1.125-1.125Z" fill="#2787F5"/></svg>',
			'youtube'  => '<svg width="34" height="24" viewBox="0 0 34 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M33.12 3.78A4.24 4.24 0 0 0 30.15.82C27.52 0 17 0 17 0S6.48 0 3.85.82A4.24 4.24 0 0 0 .88 3.78 29.5 29.5 0 0 0 0 12a29.5 29.5 0 0 0 .88 8.22 4.24 4.24 0 0 0 2.97 2.96C6.48 24 17 24 17 24s10.52 0 13.15-.82a4.24 4.24 0 0 0 2.97-2.96A29.5 29.5 0 0 0 34 12a29.5 29.5 0 0 0-.88-8.22ZM13.6 17.14V6.86L22.4 12l-8.8 5.14Z" fill="#FF0000"/></svg>',
		);

		return $icons[ $network ] ?? '';
	}
}

if ( ! function_exists( 'ksenon_render_footer_socials' ) ) {
	function ksenon_render_footer_socials() {
		$links = ksenon_get_social_links();
		if ( ! $links ) {
			return;
		}
		?>
		<ul class="footer__socials">
			<?php foreach ( $links as $link ) : ?>
				<li class="footer__socials-item">
					<a
						class="footer__socials-link footer__socials-link--<?php echo esc_attr( $link['network'] ); ?>"
						href="<?php echo esc_url( $link['url'] ); ?>"
						target="_blank"
						rel="noopener noreferrer"
						aria-label="<?php echo esc_attr( ksenon_get_footer_social_label( $link['network'] ) ); ?>"
					>
						<?php echo ksenon_get_footer_social_icon( $link['network'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}
}

if ( ! function_exists( 'ksenon_get_footer_requisites' ) ) {
	function ksenon_get_footer_requisites() {
		$inn  = trim( (string) ksenon_get_option( 'inn', '' ) );
		$ogrn = trim( (string) ksenon_get_option( 'ogrn', '' ) );

		if ( ! $inn && ! $ogrn ) {
			return '';
		}

		$parts = array();
		if ( $inn ) {
			$parts[] = sprintf( 'ИНН %s', $inn );
		}
		if ( $ogrn ) {
			$parts[] = sprintf( 'ОГРН %s', $ogrn );
		}

		return implode( '. ', $parts );
	}
}

if ( ! function_exists( 'ksenon_get_header_static_menu' ) ) {
	function ksenon_get_header_static_menu() {
		return array(
			array(
				'label'   => __( 'Услуги', 'ksenonspb' ),
				'url'     => home_url( '/uslugi/' ),
				'has_sub' => true,
				'primary' => true,
			),
			array(
				'label' => __( 'Работы', 'ksenonspb' ),
				'url'   => home_url( '/portfolio/' ),
			),
			array(
				'label' => __( 'Цены', 'ksenonspb' ),
				'url'   => home_url( '/stoimost/' ),
			),
			array(
				'label' => __( 'О компании', 'ksenonspb' ),
				'url'   => home_url( '/o-kompanii/' ),
			),
			array(
				'label' => __( 'Контакты', 'ksenonspb' ),
				'url'   => home_url( '/#contacts' ),
			),
			array(
				'label' => __( 'Акции', 'ksenonspb' ),
				'url'   => home_url( '/akcii/' ),
			),
		);
	}
}

if ( ! function_exists( 'ksenon_get_header_social_pills' ) ) {
	function ksenon_get_header_social_pills() {
		$allowed = array( 'telegram', 'whatsapp' );
		$links   = ksenon_get_social_links();

		return array_values(
			array_filter(
				$links,
				static function ( $link ) use ( $allowed ) {
					return in_array( $link['network'], $allowed, true );
				}
			)
		);
	}
}

if ( ! function_exists( 'ksenon_render_header_social_pills' ) ) {
	function ksenon_render_header_social_pills() {
		$links = ksenon_get_header_social_pills();
		if ( ! $links ) {
			return;
		}
		?>
		<div class="header-drawer__socials">
			<?php foreach ( $links as $link ) : ?>
				<a
					class="header-drawer__social header-drawer__social--<?php echo esc_attr( $link['network'] ); ?>"
					href="<?php echo esc_url( $link['url'] ); ?>"
					target="_blank"
					rel="noopener noreferrer"
				>
					<?php echo ksenon_get_header_social_icon( $link['network'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo esc_html( ksenon_get_footer_social_label( $link['network'] ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'ksenon_get_header_social_icon' ) ) {
	function ksenon_get_header_social_icon( $network ) {
		$icons = array(
			'telegram' => '<svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M13.08 1.2 11.5 12.1c-.12.55-.44.68-.9.42l-2.5-1.84-1.2 1.16c-.13.13-.24.24-.5.24l.18-2.55 4.62-4.17c.2-.18-.04-.28-.31-.1L4.2 8.2l-2.46-.77c-.53-.17-.54-.53.11-.78l9.6-3.7c.44-.16.83.1.63.25Z" fill="currentColor"/></svg>',
			'whatsapp' => '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M7 0C3.13 0 0 3.13 0 7c0 1.23.32 2.39.88 3.4L0 14l3.82-.9A6.96 6.96 0 0 0 7 14c3.87 0 7-3.13 7-7S10.87 0 7 0Zm3.79 9.73c-.16.45-.93.86-1.28.92-.33.05-.76.08-1.22-.08-.28-.1-.64-.23-1.1-.45-1.92-.83-3.18-2.79-3.27-2.92-.1-.13-.78-1.04-.78-2 0-.94.5-1.4.67-1.6.17-.2.37-.25.5-.25.12 0 .25.002.36.006.12.006.28-.05.43.33.16.38.55 1.31.6 1.41.05.1.08.21.02.34-.06.13-.09.21-.18.32-.09.11-.19.24-.27.33-.09.09-.19.2-.08.39.11.19.49 1.01 1.05 1.64.72.8 1.33 1.05 1.52 1.17.19.1.3.08.41-.05.11-.13.48-.56.61-.75.13-.2.26-.17.44-.1.19.06 1.09.51 1.27.6.19.1.31.15.36.23.05.08.05.47-.11.92Z" fill="currentColor"/></svg>',
		);

		return $icons[ $network ] ?? '';
	}
}

if ( ! function_exists( 'ksenon_get_footer_static_menus' ) ) {
	function ksenon_get_footer_static_menus() {
		return array(
			'services' => array(
				'title' => __( 'Услуги', 'ksenonspb' ),
				'items' => array(
					array(
						'label' => __( 'Ретрофит Bi-LED', 'ksenonspb' ),
						'url'   => home_url( '/uslugi/retrofit-bi-led/' ),
					),
					array(
						'label' => __( 'Ремонт оптики после ДТП', 'ksenonspb' ),
						'url'   => home_url( '/uslugi/remont-optiki-posle-dtp/' ),
					),
					array(
						'label' => __( 'Полировка стекол фар', 'ksenonspb' ),
						'url'   => home_url( '/uslugi/polirovka-stekol-far/' ),
					),
					array(
						'label' => __( 'Тюнинг — ангельские глазки', 'ksenonspb' ),
						'url'   => home_url( '/uslugi/tyuning-angel-skie-glazki/' ),
					),
					array(
						'label' => __( 'Ремонт LED-драйверов', 'ksenonspb' ),
						'url'   => home_url( '/uslugi/remont-led-draiverov/' ),
					),
					array(
						'label' => __( 'Замена ксеновых ламп', 'ksenonspb' ),
						'url'   => home_url( '/uslugi/zamena-ksenovyh-lamp/' ),
					),
					array(
						'label' => __( 'Восстановление AFS', 'ksenonspb' ),
						'url'   => home_url( '/uslugi/vosstanovlenie-afs/' ),
					),
					array(
						'label' => __( 'Регулировка цвета', 'ksenonspb' ),
						'url'   => home_url( '/uslugi/regulirovka-cveta/' ),
					),
				),
			),
			'info'     => array(
				'title' => __( 'Информация', 'ksenonspb' ),
				'items' => array(
					array(
						'label' => __( 'О компании', 'ksenonspb' ),
						'url'   => home_url( '/o-kompanii/' ),
					),
					array(
						'label' => __( 'Команда', 'ksenonspb' ),
						'url'   => home_url( '/komanda/' ),
					),
					array(
						'label' => __( 'Гарантия', 'ksenonspb' ),
						'url'   => home_url( '/garantiya/' ),
					),
					array(
						'label' => __( 'Сертификаты', 'ksenonspb' ),
						'url'   => home_url( '/sertifikaty/' ),
					),
					array(
						'label' => __( 'Цены', 'ksenonspb' ),
						'url'   => home_url( '/stoimost/' ),
					),
					array(
						'label' => __( 'Рассрочка', 'ksenonspb' ),
						'url'   => home_url( '/rassrochka/' ),
					),
					array(
						'label' => __( 'Подарочные сертификаты', 'ksenonspb' ),
						'url'   => home_url( '/podarochnye-sertifikaty/' ),
					),
					array(
						'label' => __( 'Приём фар почтой', 'ksenonspb' ),
						'url'   => home_url( '/priem-far-pochtoj/' ),
					),
					array(
						'label' => __( 'Отзывы', 'ksenonspb' ),
						'url'   => home_url( '/otzyvy/' ),
					),
					array(
						'label' => __( 'Блог', 'ksenonspb' ),
						'url'   => home_url( '/blog/' ),
					),
				),
			),
		);
	}
}

if ( ! function_exists( 'ksenon_cf7_form' ) ) {
	function ksenon_cf7_form( $option_key, $source = '', $fallback_shortcode = '' ) {
		$shortcode = ksenon_get_option( $option_key, $fallback_shortcode );
		if ( $shortcode && function_exists( 'wpcf7_contact_form' ) ) {
			ksenon_cf7_set_render_context( $source );
			echo do_shortcode( $shortcode );
			ksenon_cf7_clear_render_context();
		}
	}
}

if ( ! function_exists( 'ksenon_render_section' ) ) {
	function ksenon_render_section( $slug, $args = array() ) {
		get_template_part( 'template-parts/blocks/' . $slug, null, $args );
	}
}

if ( ! function_exists( 'ksenon_render_faq' ) ) {
	function ksenon_render_faq( $args = array() ) {
		get_template_part( 'template-parts/section/faq', null, $args );
	}
}

if ( ! function_exists( 'ksenon_get_partners' ) ) {
	function ksenon_get_partners() {
		return array();
	}
}

if ( ! function_exists( 'ksenon_get_partner_link' ) ) {
	function ksenon_get_partner_link( $post_id ) {
		unset( $post_id );

		return '';
	}
}

if ( ! function_exists( 'ksenon_count_cpt' ) ) {
	function ksenon_count_cpt( $post_type ) {
		$counts = wp_count_posts( $post_type );

		return isset( $counts->publish ) ? (int) $counts->publish : 0;
	}
}

if ( ! function_exists( 'ksenon_render_home_arrow' ) ) {
	function ksenon_render_home_arrow() {
		?>
		<span class="home-arrow" aria-hidden="true">
			<svg width="58" height="58" viewBox="0 0 58 58" fill="none" xmlns="http://www.w3.org/2000/svg">
				<circle cx="29" cy="29" r="29" fill="#FD8011"/>
				<path d="M24 22L34 29L24 36" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</span>
		<?php
	}
}

if ( ! function_exists( 'ksenon_render_btn_arrow' ) ) {
	function ksenon_render_btn_arrow( $link, $class = 'btn btn--arrow', $fallback_title = '' ) {
		if ( ! is_array( $link ) || empty( $link['url'] ) ) {
			return;
		}

		$target = ksenon_acf_link_target( $link );
		?>
		<a
			class="<?php echo esc_attr( $class ); ?>"
			href="<?php echo esc_url( ksenon_acf_link_url( $link ) ); ?>"
			<?php echo $target ? ' target="' . esc_attr( $target ) . '"' : ''; ?>
		>
			<span class="btn__text"><?php echo esc_html( ksenon_acf_link_title( $link, $fallback_title ) ); ?></span>
			<span class="btn__arrow" aria-hidden="true">
				<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="20" cy="20" r="20" fill="#FD8011"/>
					<path d="M16 15L24 20L16 25" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</span>
		</a>
		<?php
	}
}

if ( ! function_exists( 'ksenon_get_messenger_links' ) ) {
	function ksenon_get_messenger_links() {
		$links = ksenon_get_social_links();
		if ( ! $links ) {
			return array();
		}

		return array_values(
			array_filter(
				$links,
				static function ( $link ) {
					return in_array( $link['network'], array( 'telegram', 'whatsapp' ), true );
				}
			)
		);
	}
}

if ( ! function_exists( 'ksenon_render_messenger_links' ) ) {
	function ksenon_render_messenger_links( $class = 'messenger-links' ) {
		$links = ksenon_get_messenger_links();
		if ( ! $links ) {
			return;
		}
		?>
		<div class="<?php echo esc_attr( $class ); ?>">
			<?php foreach ( $links as $link ) : ?>
				<a
					class="messenger-links__item messenger-links__item--<?php echo esc_attr( $link['network'] ); ?>"
					href="<?php echo esc_url( $link['url'] ); ?>"
					target="_blank"
					rel="noopener noreferrer"
				>
					<?php echo ksenon_get_footer_social_icon( $link['network'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo esc_html( ksenon_get_footer_social_label( $link['network'] ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
