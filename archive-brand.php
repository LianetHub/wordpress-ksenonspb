<?php

/**
 * Brand archive
 *
 * @package ksenonspb
 */

get_header();

$query = ksenon_query_brands();
if (! $query->have_posts()) {
	get_footer();
	return;
}

$title      = __('Работаем <span class="color-accent">со всеми</span> марками', 'ksenonspb');
$title_html = function_exists('ksenon_title_accent_html')
	? ksenon_title_accent_html($title)
	: esc_html(wp_strip_all_tags($title));
?>
<section class="brands-section brands-section--archive">
	<div class="brands-section__container container">
		<div class="section-head section-head--row brands-section__head">
			<h1 class="section-head__title title-md brands-section__title">
				<?php echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
				?>
			</h1>
		</div>
		<ul class="brands-section__grid">
			<?php
			while ($query->have_posts()) :
				$query->the_post();
				get_template_part('template-parts/blocks/brand-card', null, array('post' => get_post()));
			endwhile;
			wp_reset_postdata();
			?>
		</ul>
	</div>
</section>

<?php get_template_part('template-parts/blocks/cta-contacts'); ?>

<?php
get_template_part(
	'template-parts/blocks/cta-bottom',
	null,
	array(
		'title' => __('Опишите проблему — оценим быстро и по делу', 'ksenonspb'),
	)
);

get_footer();
