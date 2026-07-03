<?php

/**
 * Home: Services teaser (service categories)
 *
 * @package ksenonspb
 */

$categories = function_exists('ksenon_get_all_service_categories_flat')
	? ksenon_get_all_service_categories_flat()
	: array();

if (! $categories) {
	return;
}

$services_count = ksenon_count_cpt('service');
$title          = (string) ksenon_home_get('title', __('Наши услуги', 'ksenonspb'));
$title_html     = function_exists('ksenon_title_accent_html')
	? ksenon_title_accent_html($title)
	: esc_html($title);
$more_label     = ksenon_services_count_label($services_count);
$more_link      = array(
	'url'    => ksenon_services_archive_url(),
	'title'  => $more_label,
	'target' => '',
);
?>
<section class="services-teaser">
	<div class="services-teaser__container container">
		<div class="section-head section-head--row services-teaser__head">
			<h2 class="section-head__title title-md services-teaser__title">
				<?php echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
				?>
			</h2>
			<?php ksenon_render_btn_arrow($more_link, 'btn btn--primary btn--large services-teaser__more', $more_label); ?>
		</div>
		<div class="services-teaser__slider">
			<div class="swiper">
				<ul class="services-teaser__grid swiper-wrapper">
					<?php foreach ($categories as $term) : ?>
						<?php
						if (! $term instanceof WP_Term) {
							continue;
						}

						get_template_part(
							'template-parts/blocks/service-card',
							null,
							array(
								'term'  => $term,
								'class' => 'service-card--grey services-teaser__slide swiper-slide',
							)
						);
						?>
					<?php endforeach; ?>
				</ul>
			</div>
			<button class="services-teaser__prev swiper-button-prev" type="button" aria-label="<?php esc_attr_e('Предыдущие категории', 'ksenonspb'); ?>"></button>
			<button class="services-teaser__next swiper-button-next" type="button" aria-label="<?php esc_attr_e('Следующие категории', 'ksenonspb'); ?>"></button>
		</div>
	</div>
</section>