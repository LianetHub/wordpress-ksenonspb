<?php

/**
 * Footer
 *
 * @package ksenonspb
 */

$logo_footer  = ksenon_get_option('logotip');
$phones       = ksenon_get_phones();
$footer_phone = $phones[0] ?? '';
$copyright    = ksenon_get_option('kopirajt');
$policy_link  = ksenon_get_option('ssylka_na_politiku');
$link_opd     = ksenon_get_option('ssylka_opd');
$link_cookies = ksenon_get_option('ssylka_cookies');
?>
</main>
<footer class="footer">
	<div class="footer__container _container">
		<?php if ($logo_footer || $footer_phone) : ?>
			<div class="footer__brand-row">
				<?php if ($logo_footer) : ?>
					<a href="<?php echo esc_url(home_url('/')); ?>" class="footer__logo">
						<?php
						echo ksenon_acf_image(
							$logo_footer,
							'full',
							array(
								'width'  => '141',
								'height' => '39',
							)
						);
						?>
					</a>
				<?php endif; ?>
				<?php if ($footer_phone) : ?>
					<a class="footer__phone" href="tel:+<?php echo esc_attr(ksenon_phone_clean($footer_phone)); ?>"><?php echo esc_html($footer_phone); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if (function_exists('have_rows') && have_rows('glavnoe_menyu', 'option')) : ?>
			<nav class="footer__nav" aria-label="<?php esc_attr_e('Навигация в подвале', 'ksenonspb'); ?>">
				<?php ksenon_render_main_menu('footer__menu', 'footer__item', 'footer__link'); ?>
			</nav>
		<?php endif; ?>

		<div class="footer__divider" aria-hidden="true"></div>
		<?php if ($policy_link || $link_opd || $link_cookies) : ?>

			<div class="footer__docs">
				<?php if ($policy_link) : ?>
					<a class="footer__doc" href="<?php echo ksenon_esc_link($policy_link); ?>"><?php esc_html_e('Политика конфиденциальности', 'ksenonspb'); ?></a>
				<?php endif; ?>
				<?php if ($link_opd) : ?>
					<a class="footer__doc" href="<?php echo ksenon_esc_link($link_opd); ?>"><?php esc_html_e('Согласие ОПД', 'ksenonspb'); ?></a>
				<?php endif; ?>
				<?php if ($link_cookies) : ?>
					<a class="footer__doc" href="<?php echo ksenon_esc_link($link_cookies); ?>"><?php esc_html_e('Согласие Cookies', 'ksenonspb'); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="footer__meta">
			<?php if ($copyright) : ?>
				<p class="footer__copy"><?php echo esc_html($copyright); ?></p>
			<?php endif; ?>
			<a href="https://ds-art.ru/" target="_blank" rel="noopener noreferrer" aria-label="Сайт разработан компанией DS-ART" class="footer__dev">
				<img class="footer__dev-logo" src="<?php echo esc_url(ksenon_assets_uri('img/ds-art-logo.svg')); ?>" alt="" width="26" height="22" loading="lazy" aria-hidden="true">
				<span class="footer__dev-text"><?php esc_html_e('Сайт разработан компанией DS-ART', 'ksenonspb'); ?></span>
			</a>
		</div>
	</div>
</footer>
</div>
<?php get_template_part('template-parts/popups'); ?>
<?php wp_footer(); ?>
</body>

</html>