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
	isset( $args ) && is_array( $args ) ? $args : array(),
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
$phones  = is_array( $args['phones'] ) ? $args['phones'] : array();
$items   = ksenon_get_header_static_menu();
$primary = null;
$links   = array();

foreach ( $items as $item ) {
	if ( ! empty( $item['primary'] ) ) {
		$primary = $item;
		continue;
	}
	$links[] = $item;
}
?>
<div class="header-drawer" id="header-drawer" aria-hidden="true">
	<button class="header-drawer__backdrop" type="button" aria-label="<?php esc_attr_e( 'Закрыть меню', 'ksenonspb' ); ?>"></button>

	<div class="header-drawer__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Мобильное меню', 'ksenonspb' ); ?>">
		<div class="header-drawer__top">
			<?php if ( $logo ) : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="header-drawer__logo">
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

			<?php if ( $primary ) : ?>
				<a class="header-drawer__primary" href="<?php echo esc_url( $primary['url'] ); ?>">
					<span><?php echo esc_html( $primary['label'] ); ?></span>
					<span class="header-drawer__primary-arrow" aria-hidden="true">
						<svg width="31" height="32" viewBox="0 0 31 32" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="15.5" cy="16" r="15.5" fill="white"/>
							<path d="M14 11L19 16L14 21" stroke="#FD8011" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</span>
				</a>
			<?php endif; ?>

			<?php if ( $links ) : ?>
				<ul class="header-drawer__menu">
					<?php foreach ( $links as $item ) : ?>
						<li class="header-drawer__menu-item">
							<a class="header-drawer__link" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="header-drawer__bottom">
			<?php if ( $address || $email || $phones ) : ?>
				<div class="header-drawer__contacts">
					<?php if ( $address ) : ?>
						<div class="header-drawer__contact">
							<span class="header-drawer__contact-icon" aria-hidden="true">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M12 13.5C13.933 13.5 15.5 11.933 15.5 10C15.5 8.067 13.933 6.5 12 6.5C10.067 6.5 8.5 8.067 8.5 10C8.5 11.933 10.067 13.5 12 13.5Z" stroke="currentColor" stroke-width="1.2"/>
									<path d="M12 21.5C15.5 17.5 18.5 14.5147 18.5 10.5C18.5 7.18629 15.8137 4.5 12.5 4.5H11.5C8.18629 4.5 5.5 7.18629 5.5 10.5C5.5 14.5147 8.5 17.5 12 21.5Z" stroke="currentColor" stroke-width="1.2"/>
								</svg>
							</span>
							<p class="header-drawer__contact-text"><?php echo nl2br( esc_html( $address ) ); ?></p>
						</div>
					<?php endif; ?>

					<?php if ( $email || $phones ) : ?>
						<div class="header-drawer__contact">
							<span class="header-drawer__contact-icon" aria-hidden="true">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M7 4H17C18.1046 4 19 4.89543 19 6V18C19 19.1046 18.1046 20 17 20H7C5.89543 20 5 19.1046 5 18V6C5 4.89543 5.89543 4 7 4Z" stroke="currentColor" stroke-width="1.2"/>
									<path d="M9 8H15" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
									<path d="M9 12H15" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
								</svg>
							</span>
							<div class="header-drawer__contact-text">
								<?php if ( $email ) : ?>
									<a class="header-drawer__contact-line" href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
								<?php endif; ?>
								<?php foreach ( $phones as $index => $phone ) : ?>
									<a
										class="header-drawer__contact-line<?php echo $index > 0 ? ' header-drawer__contact-line--muted' : ''; ?>"
										href="tel:+<?php echo esc_attr( ksenon_phone_clean( $phone ) ); ?>"
									><?php echo esc_html( $phone ); ?></a>
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
