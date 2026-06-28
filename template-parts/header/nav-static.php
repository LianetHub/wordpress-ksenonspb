<?php
/**
 * Static header navigation (desktop)
 *
 * @package ksenonspb
 */

$items = ksenon_get_header_static_menu();
?>
<nav class="header__nav" aria-label="<?php esc_attr_e( 'Основная навигация', 'ksenonspb' ); ?>">
	<ul class="header__menu">
		<?php foreach ( $items as $item ) : ?>
			<li class="header__menu-item<?php echo ! empty( $item['has_sub'] ) ? ' header__menu-item--has-sub' : ''; ?>">
				<a class="header__link" href="<?php echo esc_url( $item['url'] ); ?>">
					<?php echo esc_html( $item['label'] ); ?>
					<?php if ( ! empty( $item['has_sub'] ) ) : ?>
						<svg class="header__link-chevron" width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					<?php endif; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
