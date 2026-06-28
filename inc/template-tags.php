<?php
/**
 * Template tags
 *
 * @package ksenonspb
 */

if ( ! function_exists( 'str_starts_with' ) ) {
	function str_starts_with( $haystack, $needle ) {
		$haystack = (string) $haystack;
		$needle   = (string) $needle;

		if ( '' === $needle ) {
			return true;
		}

		return strncmp( $haystack, $needle, strlen( $needle ) ) === 0;
	}
}

if ( ! function_exists( 'ksenon_assets_uri' ) ) {
	function ksenon_assets_uri( $path = '' ) {
		return trailingslashit( KSENON_ASSETS_URI ) . ltrim( $path, '/' );
	}
}

if ( ! function_exists( 'ksenon_phone_clean' ) ) {
	function ksenon_phone_clean( $phone ) {
		return preg_replace( '![^0-9]+!', '', (string) $phone );
	}
}

if ( ! function_exists( 'ksenon_icon' ) ) {
	function ksenon_icon( $id, $width = 24, $height = 24, $class = 'icon' ) {
		printf(
			'<svg%s width="%d" height="%d" aria-hidden="true"><use href="%s#%s"></use></svg>',
			$class ? ' class="' . esc_attr( $class ) . '"' : '',
			(int) $width,
			(int) $height,
			esc_url( ksenon_assets_uri( 'img/icons.svg' ) ),
			esc_attr( $id )
		);
	}
}

if ( ! function_exists( 'ksenon_acf_image_url' ) ) {
	function ksenon_acf_image_url( $image, $size = 'full', $fallback = '' ) {
		if ( empty( $image ) ) {
			return $fallback;
		}

		if ( is_numeric( $image ) ) {
			$url = wp_get_attachment_image_url( (int) $image, $size );
			return $url ?: $fallback;
		}

		if ( is_string( $image ) ) {
			if ( preg_match( '#^https?://#i', $image ) || str_starts_with( $image, '/' ) ) {
				return $image;
			}

			return ksenon_assets_uri( ltrim( $image, '/' ) );
		}

		if ( ! is_array( $image ) ) {
			return $fallback;
		}

		$url = $image['sizes'][ $size ] ?? $image['url'] ?? '';
		return $url ?: $fallback;
	}
}

if ( ! function_exists( 'ksenon_acf_image' ) ) {
	function ksenon_acf_image( $image, $size = 'full', $attrs = array() ) {
		$url = ksenon_acf_image_url( $image, $size );
		if ( ! $url ) {
			return '';
		}

		if ( is_numeric( $image ) ) {
			$attachment_id = (int) $image;
			$meta          = wp_get_attachment_metadata( $attachment_id ) ?: array();
			$image         = array(
				'url'    => $url,
				'alt'    => (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
				'width'  => $meta['width'] ?? '',
				'height' => $meta['height'] ?? '',
			);
		} elseif ( is_string( $image ) ) {
			$image = array( 'url' => $url );
		} elseif ( ! is_array( $image ) ) {
			return '';
		}

		$url = $image['sizes'][ $size ] ?? $image['url'] ?? $url;
		if ( ! $url ) {
			return '';
		}

		$alt              = $attrs['alt'] ?? ( $image['alt'] ?? '' );
		$title            = $attrs['title'] ?? ( $image['title'] ?? '' );
		$w                = $attrs['width'] ?? ( $image['width'] ?? '' );
		$h                = $attrs['height'] ?? ( $image['height'] ?? '' );
		$loading          = $attrs['loading'] ?? 'lazy';
		$fetchpriority    = $attrs['fetchpriority'] ?? '';
		$class            = $attrs['class'] ?? '';
		$class_attr       = $class ? sprintf( ' class="%s"', esc_attr( $class ) ) : '';
		$aria_hidden_attr = ! empty( $attrs['aria-hidden'] )
			? sprintf( ' aria-hidden="%s"', esc_attr( $attrs['aria-hidden'] ) )
			: '';
		$dim_attrs        = '';
		if ( '' !== $w && null !== $w ) {
			$dim_attrs .= sprintf( ' width="%s"', esc_attr( (string) $w ) );
		}
		if ( '' !== $h && null !== $h ) {
			$dim_attrs .= sprintf( ' height="%s"', esc_attr( (string) $h ) );
		}
		$fetchpriority_attr = $fetchpriority
			? sprintf( ' fetchpriority="%s"', esc_attr( $fetchpriority ) )
			: '';

		return sprintf(
			'<img src="%s" alt="%s" title="%s"%s loading="%s"%s%s%s>',
			esc_url( $url ),
			esc_attr( $alt ),
			esc_attr( $title ),
			$dim_attrs,
			esc_attr( $loading ),
			$fetchpriority_attr,
			$aria_hidden_attr,
			$class_attr
		);
	}
}
