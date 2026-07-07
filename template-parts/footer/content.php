<?php

/**
 * Footer content
 *
 * @package ksenonspb
 */

$logo         = ksenon_get_logo('light');
$tagline      = ksenon_get_option('footer_tagline', __('Лаборатория автосвета с 2001', 'ksenonspb'));
$description  = ksenon_get_option('footer_description', __('Ремонтируем фары, которые другие меняют целиком. Гарантия до 2 лет письменно', 'ksenonspb'));
$phones       = ksenon_get_phones();
$footer_phone = $phones[0] ?? '';
$email        = ksenon_get_option('email');
$address      = ksenon_get_option('address');
$hours        = ksenon_get_option('hours');
$copyright    = ksenon_get_option('kopirajt', '© 2001–2026 КБ АВТО');
$requisites   = ksenon_get_footer_requisites();
$domain       = ksenon_get_footer_domain();
$policy_link  = ksenon_get_policy_url();
$link_opd     = ksenon_get_opd_url();
?>
<footer class="footer">
	<div class="footer__container container">
		<div class="footer__top">
			<div class="footer__brand">
				<div class="footer__brand-head">
					<?php if ($logo) : ?>
						<a href="<?php echo esc_url(home_url('/')); ?>" class="footer__logo">
							<?php
							echo ksenon_acf_image(
								$logo,
								'full',
								array(
									'width'  => '141',
									'height' => '39',
								)
							);
							?>
						</a>
					<?php endif; ?>

					<?php if ($tagline) : ?>
						<p class="footer__tagline"><?php echo esc_html($tagline); ?></p>
					<?php endif; ?>

					<div class="footer__socials-wrap footer__socials-wrap--mobile">
						<?php ksenon_render_footer_socials(); ?>
					</div>
				</div>

				<?php if ($description) : ?>
					<p class="footer__description"><?php echo esc_html($description); ?></p>
				<?php endif; ?>

				<div class="footer__contacts footer__contacts--mobile">
					<?php if ($address) : ?>
						<p class="footer__contact footer__contact--address"><?php echo esc_html($address); ?></p>
					<?php endif; ?>
					<?php if ($footer_phone) : ?>
						<a class="footer__contact footer__contact--phone" href="tel:+<?php echo esc_attr(ksenon_phone_clean($footer_phone)); ?>"><?php echo esc_html($footer_phone); ?></a>
					<?php endif; ?>
					<?php if ($email) : ?>
						<a class="footer__contact footer__contact--email" href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
					<?php endif; ?>
					<?php if ($hours) : ?>
						<p class="footer__contact footer__contact--hours"><?php echo nl2br(esc_html($hours)); ?></p>
					<?php endif; ?>
				</div>

				<div class="footer__socials-wrap footer__socials-wrap--desktop">
					<?php ksenon_render_footer_socials(); ?>
				</div>
			</div>

			<?php get_template_part('template-parts/footer/nav-static'); ?>

			<div class="footer__contacts footer__contacts--desktop">
				<p class="footer__contacts-title"><?php esc_html_e('Контакты', 'ksenonspb'); ?></p>

				<?php if ($address) : ?>
					<div class="footer__contact-group">
						<p class="footer__contact-label"><?php esc_html_e('Адрес', 'ksenonspb'); ?></p>
						<p class="footer__contact footer__contact--address"><?php echo esc_html($address); ?></p>
					</div>
				<?php endif; ?>

				<?php if ($footer_phone) : ?>
					<a class="footer__contact footer__contact--phone" href="tel:+<?php echo esc_attr(ksenon_phone_clean($footer_phone)); ?>"><?php echo esc_html($footer_phone); ?></a>
				<?php endif; ?>

				<?php if ($email) : ?>
					<div class="footer__contact-group">
						<p class="footer__contact-label"><?php esc_html_e('Почта', 'ksenonspb'); ?></p>
						<a class="footer__contact footer__contact--email" href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
					</div>
				<?php endif; ?>

				<?php if ($hours) : ?>
					<div class="footer__contact-group">
						<p class="footer__contact-label"><?php esc_html_e('Часы работы', 'ksenonspb'); ?></p>
						<p class="footer__contact footer__contact--hours"><?php echo nl2br(esc_html($hours)); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="footer__bottom">
			<div class="footer__bottom-main">
				<?php if ($copyright) : ?>
					<p class="footer__copy"><?php echo esc_html($copyright); ?></p>
				<?php endif; ?>

				<?php if ($requisites) : ?>
					<p class="footer__requisites"><?php echo esc_html($requisites); ?></p>
				<?php endif; ?>

				<?php if ($domain) : ?>
					<p class="footer__domain"><?php echo esc_html($domain); ?></p>
				<?php endif; ?>
			</div>

			<div class="footer__bottom-links">
				<a class="footer__legal" href="<?php echo esc_url($policy_link); ?>"><?php esc_html_e('Политика конфиденциальности', 'ksenonspb'); ?></a>
				<a class="footer__legal" href="<?php echo esc_url($link_opd); ?>"><?php esc_html_e('Согласие на обработку ПД', 'ksenonspb'); ?></a>
			</div>
		</div>
	</div>
</footer>