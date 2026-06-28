<?php
/**
 * Enqueue styles and scripts
 *
 * @package ksenonspb
 */

add_action(
	'wp_enqueue_scripts',
	function () {
		$uri = KSENON_ASSETS_URI;
		$ver = KSENON_VERSION;

		wp_enqueue_style( 'ksenonspb-swiper', $uri . '/css/libs/swiper-bundle.min.css', array(), $ver );
		wp_enqueue_style( 'ksenonspb-fancybox', $uri . '/css/libs/fancybox.min.css', array(), $ver );
		wp_enqueue_style( 'ksenonspb-reset', $uri . '/css/reset.min.css', array(), $ver );
		wp_enqueue_style( 'ksenonspb-global', $uri . '/css/style-global.min.css', array( 'ksenonspb-reset' ), $ver );
		wp_enqueue_style( 'ksenonspb-header', $uri . '/css/header.min.css', array( 'ksenonspb-global' ), $ver );
		wp_enqueue_style( 'ksenonspb-footer', $uri . '/css/footer.min.css', array( 'ksenonspb-global' ), $ver );

		if ( file_exists( KSENON_DIR . '/assets/css/style.min.css' ) ) {
			wp_enqueue_style( 'ksenonspb-style-bundle', $uri . '/css/style.min.css', array( 'ksenonspb-reset' ), $ver );
		}

		ksenon_enqueue_conditional_styles( $uri, $ver );

		wp_enqueue_script( 'ksenonspb-swiper', $uri . '/js/libs/swiper-bundle.min.js', array(), $ver, true );
		wp_enqueue_script( 'ksenonspb-fancybox', $uri . '/js/libs/fancybox.umd.js', array( 'ksenonspb-swiper' ), $ver, true );
		wp_enqueue_script( 'ksenonspb-app', $uri . '/js/app.min.js', array( 'ksenonspb-fancybox' ), $ver, true );

		$map = function_exists( 'ksenon_get_map_settings' ) ? ksenon_get_map_settings() : array();

		wp_localize_script(
			'ksenonspb-app',
			'theme_ajax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ksenon_nonce' ),
				'home_url' => home_url( '/' ),
			)
		);

		if ( ! empty( $map['apiKey'] ) ) {
			wp_localize_script(
				'ksenonspb-app',
				'theme_map',
				array(
					'apiKey' => $map['apiKey'],
					'lang'   => 'ru_RU',
				)
			);
		}
	}
);

function ksenon_enqueue_conditional_styles( $uri, $ver ) {
	$deps = array( 'ksenonspb-global', 'ksenonspb-header', 'ksenonspb-footer' );

	if ( is_front_page() ) {
		wp_enqueue_style( 'ksenonspb-home', $uri . '/css/home.min.css', $deps, $ver );
	}

	if ( is_post_type_archive( 'service' ) || is_page_template( 'page-uslugi.php' ) ) {
		wp_enqueue_style( 'ksenonspb-services', $uri . '/css/services.min.css', $deps, $ver );
	}

	if ( is_singular( 'service' ) ) {
		wp_enqueue_style( 'ksenonspb-service', $uri . '/css/service.min.css', $deps, $ver );
	}

	if ( is_post_type_archive( 'portfolio' ) ) {
		wp_enqueue_style( 'ksenonspb-portfolio', $uri . '/css/portfolio.min.css', $deps, $ver );
	}

	if ( is_singular( 'portfolio' ) ) {
		wp_enqueue_style( 'ksenonspb-case', $uri . '/css/case.min.css', $deps, $ver );
	}

	if ( is_post_type_archive( 'brand' ) || is_singular( 'brand' ) ) {
		wp_enqueue_style( 'ksenonspb-brand', $uri . '/css/brand.min.css', $deps, $ver );
	}

	if ( is_post_type_archive( 'promotion' ) || is_singular( 'promotion' ) ) {
		wp_enqueue_style( 'ksenonspb-promotion', $uri . '/css/promotion.min.css', $deps, $ver );
	}

	if ( is_page_template( 'page-o-kompanii.php' ) ) {
		wp_enqueue_style( 'ksenonspb-about', $uri . '/css/about.min.css', $deps, $ver );
	}

	if ( is_page_template( 'page-stoimost.php' ) ) {
		wp_enqueue_style( 'ksenonspb-pricing', $uri . '/css/pricing.min.css', $deps, $ver );
	}

	if ( is_page_template( 'page-policy.php' ) || is_page( 'privacy-policy' ) || is_page( 'politika-konfidentsialnosti' ) ) {
		wp_enqueue_style( 'ksenonspb-policy', $uri . '/css/policy.min.css', $deps, $ver );
	}

	if ( is_404() ) {
		wp_enqueue_style( 'ksenonspb-not-found', $uri . '/css/not-found.min.css', $deps, $ver );
	}
}

add_action( 'wp_head', 'ksenon_preload_fonts', 1 );

function ksenon_preload_fonts() {
	$fonts = array(
		'Manrope-VariableFont_wght.woff2',
		'Inter-VariableFont_opsz,wght.woff2',
	);
	$base  = KSENON_ASSETS_URI . '/fonts/';

	foreach ( $fonts as $file ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( $base . $file )
		);
	}
}
