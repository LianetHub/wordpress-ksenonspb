<?php

/**
 * Header navigation (desktop)
 *
 * @package ksenonspb
 */

if (! has_nav_menu('primary')) {
	return;
}
?>
<nav class="header__nav" aria-label="<?php esc_attr_e('Основная навигация', 'ksenonspb'); ?>">
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'header__menu',
			'depth'          => 2,
			'fallback_cb'    => false,
			'walker'         => new Ksenon_Walker_Header_Nav(),
			'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
		)
	);
	?>
</nav>