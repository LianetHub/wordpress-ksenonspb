<?php

/**
 * Services archive page
 *
 * @package ksenonspb
 */

$archive_term = isset($args['term']) && $args['term'] instanceof WP_Term ? $args['term'] : null;

$categories       = ksenon_get_service_categories();
$active_slug      = $archive_term ? $archive_term->slug : ksenon_get_current_service_category_slug();
$active_top_slug  = $archive_term ? ksenon_get_active_top_level_service_category_slug() : $active_slug;
$subcategories    = $active_top_slug ? ksenon_get_service_subcategories($active_top_slug) : array();
$paged            = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
$query_args       = array(
	'posts_per_page' => 9,
	'paged'          => $paged,
	'no_found_rows'  => false,
);

if ($archive_term) {
	$include_children = (bool) get_term_children((int) $archive_term->term_id, 'service_category');
	$query_args['tax_query'] = array(
		array(
			'taxonomy'         => 'service_category',
			'field'            => 'term_id',
			'terms'            => array((int) $archive_term->term_id),
			'include_children' => $include_children,
		),
	);
}

$query = ksenon_query_services($query_args);

$page_title = __('Наши услуги', 'ksenonspb');
if ($archive_term instanceof WP_Term) {
	$page_title = $archive_term->name;
}
?>
<section class="services-archive">
	<div class="services-archive__container container">
		<h1 class="services-archive__title title-lg"><?php echo esc_html($page_title); ?></h1>
	</div>
	<div class="services-archive__container container container--large">
		<div class="services-archive__panel">
			<?php if ($categories) : ?>
				<nav class="services-archive__filters swiper" aria-label="<?php esc_attr_e('Фильтр услуг по категориям', 'ksenonspb'); ?>">
					<div class="swiper-wrapper">
						<a
							class="services-archive__tab swiper-slide<?php echo $active_slug ? '' : ' _active'; ?>"
							href="<?php echo esc_url(ksenon_services_archive_url()); ?>"
							<?php echo $active_slug ? '' : ' aria-current="page"'; ?>>
							<?php esc_html_e('Все', 'ksenonspb'); ?>
						</a>
						<?php foreach ($categories as $category) : ?>
							<?php
							$is_active = $active_top_slug === $category->slug;
							?>
							<a
								class="services-archive__tab swiper-slide<?php echo $is_active ? ' _active' : ''; ?>"
								href="<?php echo esc_url(ksenon_services_archive_url($category->slug)); ?>"
								<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
								<?php echo esc_html($category->name); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</nav>
			<?php endif; ?>

			<?php if ($subcategories) : ?>
				<nav class="services-archive__subfilters swiper" aria-label="<?php esc_attr_e('Подкатегории услуг', 'ksenonspb'); ?>">
					<div class="swiper-wrapper">
						<a
							class="services-archive__tab swiper-slide<?php echo ($archive_term && $archive_term->slug === $active_top_slug) ? ' _active' : ''; ?>"
							href="<?php echo esc_url(ksenon_services_archive_url($active_top_slug)); ?>"
							<?php echo ($archive_term && $archive_term->slug === $active_top_slug) ? ' aria-current="page"' : ''; ?>>
							<?php esc_html_e('Все', 'ksenonspb'); ?>
						</a>
						<?php foreach ($subcategories as $subcategory) : ?>
							<?php $is_sub_active = $active_slug === $subcategory->slug; ?>
							<a
								class="services-archive__tab swiper-slide<?php echo $is_sub_active ? ' _active' : ''; ?>"
								href="<?php echo esc_url(ksenon_services_archive_url($subcategory->slug)); ?>"
								<?php echo $is_sub_active ? ' aria-current="page"' : ''; ?>>
								<?php echo esc_html($subcategory->name); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</nav>
			<?php endif; ?>

			<?php if ($query->have_posts()) : ?>
				<ul class="services-archive__grid">
					<?php
					while ($query->have_posts()) :
						$query->the_post();
						get_template_part('template-parts/blocks/service-card', null, array('post' => get_post()));
					endwhile;
					wp_reset_postdata();
					?>
				</ul>
				<?php ksenon_render_pagination($query, $active_slug); ?>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php get_template_part('template-parts/blocks/cta-form', null, array('variant' => 'service_not_found')); ?>