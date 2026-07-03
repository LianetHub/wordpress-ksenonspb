<?php

/**
 * Home: Services teaser
 *
 * @package ksenonspb
 */

$featured = ksenon_home_get('featured_services', array());
$limit    = (int) ksenon_home_get('limit', 3);
$args     = array('posts_per_page' => $limit > 0 ? $limit : 3);

if (is_array($featured) && $featured) {
	$ids = array();
	foreach ($featured as $item) {
		if ($item instanceof WP_Post) {
			$ids[] = (int) $item->ID;
		} elseif (is_numeric($item)) {
			$ids[] = (int) $item;
		}
	}
	if ($ids) {
		$args['post__in'] = $ids;
		$args['orderby']  = 'post__in';
	}
}

$query = ksenon_query_services($args);
if (! $query->have_posts()) {
	return;
}

$services_count = ksenon_count_cpt('service');
$title          = (string) ksenon_home_get('title', __('Наши услуги', 'ksenonspb'));
$more_link      = array(
	'url'    => ksenon_services_archive_url(),
	'title'  => sprintf(
		/* translators: %d: services count */
		__('Все %d услуги', 'ksenonspb'),
		$services_count
	),
	'target' => '',
);
?>
<section class="services-teaser">
	<div class="services-teaser__container container">
		<div class="section-head section-head--row services-teaser__head">
			<h2 class="section-head__title title-md services-teaser__title">
				<?php echo ksenon_title_accent_html($title); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</h2>
			<?php ksenon_render_btn_arrow($more_link, 'btn btn--arrow services-teaser__more', sprintf(__('Все %d услуги', 'ksenonspb'), $services_count)); ?>
		</div>
		<div class="services-teaser__grid">
			<?php
			while ($query->have_posts()) :
				$query->the_post();
				get_template_part('template-parts/blocks/service-card', null, array('post' => get_post()));
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>