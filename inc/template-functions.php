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
