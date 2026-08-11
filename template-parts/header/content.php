<?php

/**
 * Header content
 *
 * @package ksenonspb
 */

$logo    = ksenon_get_logo('dark');
$email   = ksenon_get_option('email');
$address = ksenon_get_option('address');
$phones  = ksenon_get_phones();
?>
<header class="header">
	<div class="header__container container container--medium">
		<div class="header__panel">
			<?php
			get_template_part(
				'template-parts/header/contacts',
				null,
				array(
					'address' => $address,
					'phones'  => $phones,
				)
			);
			?>

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
								'width'         => '80',
								'height'        => '82',
							)
						);
						?>
					</a>
				<?php endif; ?>

				<?php get_template_part('template-parts/header/nav'); ?>

				<div class="header__actions">
					<button
						class="header__cta"
						type="button"
						data-fancybox
						data-src="#popup-order"><?php esc_html_e('Оценить ремонт', 'ksenonspb'); ?></button>
				</div>
			</div>

			<button
				class="header__toggle icon-menu"
				type="button"
				aria-label="<?php esc_attr_e('Открыть меню', 'ksenonspb'); ?>"
				aria-expanded="false"
				aria-controls="header-drawer">
				<span></span>
				<span></span>
				<span></span>
			</button>
		</div>
	</div>
</header>

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
