<?php
/**
 * ACF configuration
 *
 * Группы полей в админке WordPress (ACF).
 *
 * @package ksenonspb
 */

define( 'KSENON_ACF_SETTINGS_SLUG', 'theme-settings' );
define( 'KSENON_ACF_JSON_DIR', KSENON_DIR . '/acf-json' );

add_filter(
	'acf/settings/save_json',
	function () {
		return KSENON_ACF_JSON_DIR;
	}
);

add_filter(
	'acf/settings/load_json',
	function ( $paths ) {
		$paths[] = KSENON_ACF_JSON_DIR;

		return $paths;
	}
);

add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		acf_add_options_page(
			array(
				'page_title' => 'Настройки сайта',
				'menu_title' => 'Настройки сайта',
				'menu_slug'  => KSENON_ACF_SETTINGS_SLUG,
				'capability' => 'edit_posts',
				'redirect'   => false,
				'icon_url'   => 'dashicons-admin-generic',
			)
		);
	}
);

add_action(
	'wp_head',
	function () {
		if ( function_exists( 'ksenon_render_favicons' ) ) {
			ksenon_render_favicons();
		}
	},
	1
);

add_action(
	'acf/input/admin_head',
	function () {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, KSENON_ACF_SETTINGS_SLUG ) === false ) {
			return;
		}
		?>
	<style type="text/css">
		h2.hndle.ui-sortable-handle {
			background: #1a5f4a;
			color: #fff !important;
			transition: all 0.25s;
		}

		.acf-field.acf-accordion .acf-label.acf-accordion-title {
			background: #e8f4ef;
			transition: all 0.25s;
		}

		.acf-accordion .acf-accordion-title label {
			text-transform: uppercase;
			color: #000;
		}

		.acf-field p.description {
			color: #c47a00;
		}

		.acf-field-group {
			border: 1px solid #1a5f4a !important;
		}
	</style>
		<?php
	}
);

add_action(
	'admin_head',
	function () {
		?>
	<style>
		#toplevel_page_<?php echo esc_attr( KSENON_ACF_SETTINGS_SLUG ); ?>>a {
			background-color: #26668c !important;
			color: #fff !important;
		}

		#toplevel_page_<?php echo esc_attr( KSENON_ACF_SETTINGS_SLUG ); ?>>a:hover,
		#toplevel_page_<?php echo esc_attr( KSENON_ACF_SETTINGS_SLUG ); ?>>a:focus {
			background-color: #1e5270 !important;
			color: #fff !important;
		}

		#toplevel_page_<?php echo esc_attr( KSENON_ACF_SETTINGS_SLUG ); ?>.wp-has-current-submenu>a,
		#toplevel_page_<?php echo esc_attr( KSENON_ACF_SETTINGS_SLUG ); ?>.current>a {
			background-color: #26668c !important;
			color: #fff !important;
		}
	</style>
		<?php
	}
);
