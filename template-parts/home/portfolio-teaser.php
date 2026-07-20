<?php

/**
 * Home: Portfolio teaser
 *
 * @package ksenonspb
 */

$limit = (int) ksenon_home_get('limit', 4);
$query = ksenon_query_portfolio(
	array(
		'posts_per_page' => $limit > 0 ? $limit : 4,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

if (! $query->have_posts()) {
	return;
}

$title      = (string) ksenon_home_get('title', __('Наши работы', 'ksenonspb'));
$title_html = function_exists('ksenon_title_accent_html')
	? ksenon_title_accent_html($title)
	: esc_html($title);
$more_label = __('Все работы по вашей марке', 'ksenonspb');
$more_link  = array(
	'url'    => ksenon_portfolio_archive_url(),
	'title'  => $more_label,
	'target' => '',
);
?>
<section class="portfolio-teaser">
	<div class="portfolio-teaser__wrapper">
		<div class="portfolio-teaser__container">
			<div class="section-head section-head--row portfolio-teaser__head">
				<h2 class="section-head__title title-md portfolio-teaser__title">
					<?php echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</h2>
				<?php ksenon_render_btn_arrow($more_link, 'btn btn--primary btn--large portfolio-teaser__more', $more_label); ?>
			</div>
			<div class="portfolio-teaser__grid">
				<?php
				while ($query->have_posts()) :
					$query->the_post();
					get_template_part('template-parts/blocks/portfolio-card', null, array('post' => get_post()));
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</div>
</section>
