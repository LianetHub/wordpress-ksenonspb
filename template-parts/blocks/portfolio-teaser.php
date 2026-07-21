<?php

/**
 * Portfolio teaser section (shared: home + service)
 *
 * @package ksenonspb
 *
 * @var array $args {
 *     @type WP_Query|null $query       Portfolio query.
 *     @type string        $title       Section title (plain or inline HTML).
 *     @type string        $more_label  Archive CTA label.
 *     @type string        $more_url    Archive CTA URL.
 *     @type bool          $show_more   Show archive CTA. Default true.
 * }
 */

$args = wp_parse_args(
	isset($args) && is_array($args) ? $args : array(),
	array(
		'query'      => null,
		'title'      => '',
		'more_label' => '',
		'more_url'   => '',
		'show_more'  => true,
	)
);

$query = $args['query'];
if (! $query instanceof WP_Query || ! $query->have_posts()) {
	return;
}

$title = (string) $args['title'];
if ('' === $title) {
	$title = __('Наши работы', 'ksenonspb');
}

$title_html = function_exists('ksenon_title_accent_html')
	? ksenon_title_accent_html($title)
	: esc_html(wp_strip_all_tags($title));

$show_more  = (bool) $args['show_more'];
$more_label = (string) $args['more_label'];
$more_url   = (string) $args['more_url'];

if ($show_more) {
	if ('' === $more_label) {
		$more_label = __('Все работы по вашей марке', 'ksenonspb');
	}
	if ('' === $more_url) {
		$more_url = ksenon_portfolio_archive_url();
	}
}

$more_link = $show_more && $more_url
	? array(
		'url'    => $more_url,
		'title'  => $more_label,
		'target' => '',
	)
	: null;
?>
<section class="portfolio-teaser">
	<div class="portfolio-teaser__wrapper">
		<div class="portfolio-teaser__container">
			<div class="section-head section-head--row portfolio-teaser__head">
				<h2 class="section-head__title title-md portfolio-teaser__title">
					<?php echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</h2>
				<?php
				if ($more_link) {
					ksenon_render_btn_arrow($more_link, 'btn btn--primary btn--large portfolio-teaser__more', $more_label);
				}
				?>
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
