<?php

/**
 * Footer navigation columns (WP menus + static fallback)
 *
 * Locations:
 * - footer_services  → «Подвал: Услуги»
 * - footer_info      → «Подвал: Информация»
 *
 * @package ksenonspb
 */

$columns      = ksenon_get_footer_nav_columns();
$static_menus = ksenon_get_footer_static_menus();
?>
<div class="footer__navs">
	<?php foreach ($columns as $slug => $column) : ?>
		<?php
		$location = $column['location'];
		$title    = ksenon_get_footer_nav_title($location, $column['title']);
		$has_menu = has_nav_menu($location);
		$fallback = (! $has_menu && ! empty($static_menus[$slug]['items'])) ? $static_menus[$slug] : null;

		if (! $has_menu && ! $fallback) {
			continue;
		}
		?>
		<nav class="footer__nav footer__nav--<?php echo esc_attr($slug); ?>" aria-label="<?php echo esc_attr($title); ?>">
			<p class="footer__nav-title"><?php echo esc_html($title); ?></p>

			<?php if ($has_menu) : ?>
				<?php ksenon_render_footer_nav_menu($location); ?>
			<?php else : ?>
				<ul class="footer__menu">
					<?php foreach ($fallback['items'] as $item) : ?>
						<li class="footer__menu-item">
							<a class="footer__menu-link" href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</nav>
	<?php endforeach; ?>
</div>