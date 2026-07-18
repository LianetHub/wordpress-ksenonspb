<?php

/**
 * Header content
 *
 * @package ksenonspb
 */

$logo     = ksenon_get_logo('dark');
$cta_text = ksenon_get_option('header_cta_text', __('Связаться с нами', 'ksenonspb'));
$email    = ksenon_get_option('email');
$address  = ksenon_get_option('address');
$phones   = ksenon_get_phones();
?>
<header class="header">
	<div class="header__container container container--medium">
		<div class="header__bar">
			<?php if ($logo) : ?>
				<a href="<?php echo esc_url(home_url('/')); ?>" class="header__logo">
					<?php
					echo ksenon_acf_image(
						$logo,
						'full',
						array(
							'class'         => 'header__logo-img',
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'width'         => '50',
							'height'        => '51',
						)
					);
					?>
				</a>
			<?php endif; ?>

			<?php get_template_part('template-parts/header/nav'); ?>

			<div class="header__actions">
				<?php if ($cta_text) : ?>
					<button
						class="header__cta"
						type="button"
						data-fancybox
						data-src="#popup-consultation"><?php echo esc_html($cta_text); ?></button>
				<?php endif; ?>

				<button
					class="header__toggle"
					type="button"
					aria-label="<?php esc_attr_e('Открыть меню', 'ksenonspb'); ?>"
					aria-expanded="false"
					aria-controls="header-drawer">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<path d="M4 7H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
						<path d="M4 12H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
						<path d="M4 17H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
					</svg>
				</button>
			</div>
		</div>
	</div>

	<?php
	get_template_part(
		'template-parts/header/mobile-menu',
		null,
		array(
			'logo'    => $logo,
			'email'   => $email,
			'address' => $address,
			'phones'  => $phones,
		)
	);
	?>
</header>