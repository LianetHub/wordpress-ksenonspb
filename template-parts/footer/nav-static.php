<?php
/**
 * Static footer navigation columns
 *
 * @package ksenonspb
 */

$menus = ksenon_get_footer_static_menus();
?>
<div class="footer__navs">
	<?php foreach ( $menus as $slug => $menu ) : ?>
		<nav class="footer__nav footer__nav--<?php echo esc_attr( $slug ); ?>" aria-label="<?php echo esc_attr( $menu['title'] ); ?>">
			<p class="footer__nav-title"><?php echo esc_html( $menu['title'] ); ?></p>
			<ul class="footer__menu">
				<?php foreach ( $menu['items'] as $item ) : ?>
					<li class="footer__menu-item">
						<a class="footer__menu-link" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endforeach; ?>
</div>
