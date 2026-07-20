<?php

/**
 * Contacts page
 *
 * @package ksenonspb
 */

$address = (string) ksenon_get_option('address');
$email   = (string) ksenon_get_option('email');
$phones  = ksenon_get_phones();
$hours   = (string) ksenon_get_option('hours');

$hours_lines = array_values(
	array_filter(
		array_map('trim', preg_split('/\R/u', $hours) ?: array()),
		static function ($line) {
			return '' !== $line;
		}
	)
);

$map_coords = trim((string) ksenon_get_option('map_coords', '60.006714,30.359320'));
$map_zoom   = (int) ksenon_get_option('map_zoom', 16);
$map_api    = trim((string) ksenon_get_option('map_api_key', ''));
$map_icon   = ksenon_acf_image_url(
	ksenon_get_option('map_placemark'),
	'full',
	ksenon_assets_uri('img/placemark.svg')
);

if ($map_zoom < 1) {
	$map_zoom = 16;
}

$site_name = get_bloginfo('name');
$site_url  = home_url('/');
$logo_url  = ksenon_acf_image_url(ksenon_get_logo('dark'), 'full');
?>
<section class="contacts" itemscope itemtype="https://schema.org/Organization">
	<?php if ($logo_url) : ?>
		<link itemprop="logo" href="<?php echo esc_url($logo_url); ?>">
	<?php endif; ?>
	<meta itemprop="name" content="<?php echo esc_attr($site_name); ?>">
	<meta itemprop="url" content="<?php echo esc_url($site_url); ?>">

	<div class="contacts__container container">
		<h1 class="contacts__title title-lg"><?php the_title(); ?></h1>

		<ul class="contacts__list">
			<?php if ($address) : ?>
				<li class="contacts__item" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
					<span class="contacts__item-icon" aria-hidden="true"><?php ksenon_icon('icon-location', 24, 24, 'contacts__item-icon-svg'); ?></span>
					<div class="contacts__item-caption"><?php esc_html_e('Адрес', 'ksenonspb'); ?></div>
					<address class="contacts__item-address">
						<span itemprop="streetAddress"><?php echo nl2br(esc_html($address)); ?></span>
					</address>
				</li>
			<?php endif; ?>

			<?php if ($email) : ?>
				<li class="contacts__item">
					<span class="contacts__item-icon" aria-hidden="true"><?php ksenon_icon('icon-envelope', 24, 24, 'contacts__item-icon-svg'); ?></span>
					<div class="contacts__item-caption"><?php esc_html_e('Email', 'ksenonspb'); ?></div>
					<a class="contacts__item-link" href="mailto:<?php echo esc_attr($email); ?>" itemprop="email"><?php echo esc_html($email); ?></a>
				</li>
			<?php endif; ?>

			<?php if ($phones) : ?>
				<li class="contacts__item">
					<span class="contacts__item-icon" aria-hidden="true"><?php ksenon_icon('icon-phone-outline', 24, 24, 'contacts__item-icon-svg'); ?></span>
					<div class="contacts__item-caption"><?php esc_html_e('Телефон', 'ksenonspb'); ?></div>
					<div class="contacts__item-phones">
						<?php foreach ($phones as $phone) : ?>
							<a
								class="contacts__item-link"
								href="tel:+<?php echo esc_attr(ksenon_phone_clean($phone)); ?>"
								itemprop="telephone">
								<?php echo esc_html($phone); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</li>
			<?php endif; ?>

			<?php if ($hours_lines) : ?>
				<li class="contacts__item">
					<span class="contacts__item-icon" aria-hidden="true"><?php ksenon_icon('icon-clock', 24, 24, 'contacts__item-icon-svg'); ?></span>
					<div class="contacts__item-caption"><?php esc_html_e('Часы работы', 'ksenonspb'); ?></div>
					<div class="contacts__item-hours">
						<?php foreach ($hours_lines as $hours_line) : ?>
							<p class="contacts__item-text"><?php echo esc_html($hours_line); ?></p>
						<?php endforeach; ?>
					</div>
				</li>
			<?php endif; ?>
		</ul>

		<?php if ($map_coords) : ?>
			<div
				class="contacts__map"
				id="map"
				data-coords="<?php echo esc_attr($map_coords); ?>"
				data-zoom="<?php echo esc_attr((string) $map_zoom); ?>"
				data-icon="<?php echo esc_url($map_icon); ?>"
				<?php echo $map_api ? ' data-apikey="' . esc_attr($map_api) . '"' : ''; ?>
				role="region"
				aria-label="<?php esc_attr_e('Карта проезда', 'ksenonspb'); ?>"></div>
		<?php endif; ?>
	</div>
</section>

<?php
get_template_part(
	'template-parts/blocks/cta-bottom',
	null,
	array(
		'title' => __('Опишите проблему — оценим быстро и по делу', 'ksenonspb'),
	)
);
