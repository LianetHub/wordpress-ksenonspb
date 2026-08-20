<?php

/**
 * Yoast breadcrumbs
 *
 * @package ksenonspb
 */

if (! function_exists('yoast_breadcrumb') || is_front_page()) {
	return;
}

ob_start();
yoast_breadcrumb('', '');
$breadcrumbs = trim((string) ob_get_clean());

if ('' === $breadcrumbs) {
	return;
}
?>
<nav aria-label="<?php echo esc_attr__('Хлебные крошки', 'ksenonspb'); ?>" class="breadcrumbs">
	<div class="container">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built in ksenon_yoast_breadcrumb_output().
		echo $breadcrumbs;
		?>
	</div>
</nav>
