<?php

/**
 * Services archive page
 *
 * @package ksenonspb
 */

$categories   = ksenon_get_service_categories();
$active_slug  = ksenon_get_current_service_category_slug();
$query_args   = array();

if ($active_slug) {
	$query_args['tax_query'] = array(
		array(
			'taxonomy' => 'service_category',
			'field'    => 'slug',
			'terms'    => $active_slug,
		),
	);
}

$query = ksenon_query_services($query_args);
?>
<section class="services-archive">
	<div class="services-archive__container container">
		<h1 class="services-archive__title title-lg"><?php echo esc_html(get_the_title() ?: __('Наши услуги', 'ksenonspb')); ?></h1>

		<?php if ($categories) : ?>
			<nav class="services-archive__filters blog-tabs" aria-label="<?php esc_attr_e('Фильтр услуг по категориям', 'ksenonspb'); ?>">
				<a
					class="blog-tabs__btn<?php echo $active_slug ? '' : ' _active'; ?>"
					href="<?php echo esc_url(ksenon_services_archive_url()); ?>"
					<?php echo $active_slug ? '' : ' aria-current="page"'; ?>>
					<?php esc_html_e('Все', 'ksenonspb'); ?>
				</a>
				<?php foreach ($categories as $category) : ?>
					<?php
					$is_active = $active_slug === $category->slug;
					?>
					<a
						class="blog-tabs__btn<?php echo $is_active ? ' _active' : ''; ?>"
						href="<?php echo esc_url(ksenon_services_archive_url($category->slug)); ?>"
						<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
						<?php echo esc_html($category->name); ?>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<ul class="services-archive__grid">
			<?php
			if ($query->have_posts()) :
				while ($query->have_posts()) :
					$query->the_post();
					get_template_part('template-parts/blocks/service-card', null, array('post' => get_post()));
				endwhile;
				wp_reset_postdata();
			endif;
			?>
		</ul>
	</div>
</section>
<?php get_template_part('template-parts/blocks/cta-form', null, array('variant' => 'service_not_found')); ?>