<?php

/**
 * Mobile header drawer
 *
 * @package ksenonspb
 *
 * @var array $args {
 *     @type array|null $logo Logo image array.
 *     @type string     $email Email address.
 *     @type string     $address Address text.
 *     @type array      $phones Phone numbers.
 * }
 */

$args = wp_parse_args(
	isset($args) && is_array($args) ? $args : array(),
	array(
		'logo'    => null,
		'email'   => '',
		'address' => '',
		'phones'  => array(),
	)
);

$logo    = $args['logo'];
$email   = (string) $args['email'];
$address = (string) $args['address'];
$phones  = is_array($args['phones']) ? $args['phones'] : array();
$has_menu = has_nav_menu('primary');
?>
<div class="header-drawer" id="header-drawer" aria-hidden="true">
	<button class="header-drawer__backdrop" type="button" aria-label="<?php esc_attr_e('Закрыть меню', 'ksenonspb'); ?>"></button>

	<div class="header-drawer__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Мобильное меню', 'ksenonspb'); ?>">
		<div class="header-drawer__top">
			<?php if ($logo) : ?>
				<a href="<?php echo esc_url(home_url('/')); ?>" class="header-drawer__logo">
					<?php
					echo ksenon_acf_image(
						$logo,
						'full',
						array(
							'width'  => '50',
							'height' => '51',
						)
					);
					?>
				</a>
			<?php endif; ?>

			<?php
			if ($has_menu) {
				wp_nav_menu(
					array(
						'theme_location'  => 'primary',
						'container'       => false,
						'depth'           => 2,
						'fallback_cb'     => false,
						'walker'          => new Ksenon_Walker_Header_Nav(),
						'ksenon_variant'  => 'drawer',
						'items_wrap'      => '%3$s',
					)
				);
			}
			?>
		</div>

		<div class="header-drawer__bottom">
			<?php if ($address || $email || $phones) : ?>
				<div class="header-drawer__contacts">
					<?php if ($address) : ?>
						<?php
						$address_lines = array_values(
							array_filter(
								array_map('trim', preg_split('/\r\n|\r|\n/', $address) ?: array()),
								static function ($line) {
									return '' !== $line;
								}
							)
						);
						?>
						<div class="header-drawer__contact">
							<span class="header-drawer__contact-icon" aria-hidden="true">
								<svg width="35" height="33" viewBox="0 0 35 33" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M17.5 3.682C24.083 3.682 29.419 9.018 29.419 15.601C29.419 22.184 24.083 27.52 17.5 27.52C10.917 27.52 5.581 22.184 5.581 15.601C5.581 9.018 10.917 3.682 17.5 3.682ZM17.5 8.449C17.184 8.449 16.881 8.575 16.657 8.799C16.434 9.022 16.308 9.325 16.308 9.641V15.601C16.308 15.917 16.434 16.22 16.657 16.443L20.233 20.019C20.458 20.236 20.759 20.356 21.071 20.354C21.384 20.351 21.683 20.226 21.904 20.005C22.125 19.784 22.25 19.485 22.253 19.172C22.256 18.86 22.135 18.559 21.918 18.334L18.692 15.107V9.641C18.692 9.325 18.566 9.022 18.343 8.799C18.119 8.575 17.816 8.449 17.5 8.449Z" fill="currentColor" />
								</svg>
							</span>
							<div class="header-drawer__contact-text">
								<?php if ($address_lines) : ?>
									<?php foreach ($address_lines as $line) : ?>
										<span class="header-drawer__contact-line"><?php echo esc_html($line); ?></span>
									<?php endforeach; ?>
								<?php else : ?>
									<span class="header-drawer__contact-line"><?php echo esc_html($address); ?></span>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ($email || $phones) : ?>
						<div class="header-drawer__contact">
							<span class="header-drawer__contact-icon" aria-hidden="true">
								<svg width="35" height="35" viewBox="0 0 35 35" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M17.5 30C17.5 30 27 21.116 27 14.375C27 11.889 25.999 9.504 24.218 7.746C22.436 5.988 20.02 5 17.5 5C14.98 5 12.564 5.988 10.782 7.746C9.001 9.504 8 11.889 8 14.375C8 21.116 17.5 30 17.5 30ZM17.5 19.062C16.24 19.062 15.032 18.569 14.141 17.69C13.25 16.81 12.75 15.618 12.75 14.375C12.75 13.132 13.25 11.94 14.141 11.06C15.032 10.181 16.24 9.688 17.5 9.688C18.76 9.688 19.968 10.181 20.859 11.06C21.75 11.94 22.25 13.132 22.25 14.375C22.25 15.618 21.75 16.81 20.859 17.69C19.968 18.569 18.76 19.062 17.5 19.062Z" fill="currentColor" />
								</svg>
							</span>
							<div class="header-drawer__contact-text">
								<?php if ($email) : ?>
									<a class="header-drawer__contact-line" href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
								<?php endif; ?>
								<?php foreach ($phones as $index => $phone) : ?>
									<a
										class="header-drawer__contact-line<?php echo $index > 0 ? ' header-drawer__contact-line--muted' : ''; ?>"
										href="tel:+<?php echo esc_attr(ksenon_phone_clean($phone)); ?>"><?php echo esc_html($phone); ?></a>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php ksenon_render_header_social_pills(); ?>
		</div>
	</div>
</div>