<?php

/**
 * Header top contacts row (address, phones, socials).
 *
 * @package ksenonspb
 *
 * @var array $args {
 *     @type string $address Address text.
 *     @type array  $phones  Phone numbers.
 * }
 */

$args = wp_parse_args(
	isset($args) && is_array($args) ? $args : array(),
	array(
		'address' => '',
		'phones'  => array(),
	)
);

$address       = (string) $args['address'];
$phones        = is_array($args['phones']) ? $args['phones'] : array();
$address_lines = ksenon_get_address_lines($address);
$has_socials   = (bool) ksenon_get_social_links();

if (! $address_lines && ! $phones && ! $has_socials) {
	return;
}
?>
<div class="header__top">
	<?php if ($address_lines) : ?>
		<div class="header__address" aria-label="<?php esc_attr_e('Адрес', 'ksenonspb'); ?>">
			<?php foreach ($address_lines as $line) : ?>
				<span class="header__address-line"><?php echo esc_html($line); ?></span>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ($phones) : ?>
		<div class="header__phones">
			<?php foreach ($phones as $phone) : ?>
				<a
					class="header__phone"
					href="tel:+<?php echo esc_attr(ksenon_phone_clean($phone)); ?>"><?php echo esc_html($phone); ?></a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php ksenon_render_header_social_icons(); ?>
</div>
