<?php

/**
 * Brands section (shared: home + service)
 *
 * @package ksenonspb
 *
 * @var array $args {
 *     @type WP_Query|null $query      Brands query.
 *     @type string        $title      Section title (plain or inline HTML).
 *     @type bool          $show_more  Show archive CTA. Default true.
 *     @type string        $class      Extra section class.
 * }
 */

$args = wp_parse_args(
	isset($args) && is_array($args) ? $args : array(),
	array(
		'query'     => null,
		'title'     => '',
		'show_more' => true,
		'class'     => '',
	)
);

$query = $args['query'];
if (! $query instanceof WP_Query || ! $query->have_posts()) {
	return;
}

$title = (string) $args['title'];
if ('' === $title) {
	$title = __('Работаем со всеми марками', 'ksenonspb');
}

$title_html = function_exists('ksenon_title_accent_html')
	? ksenon_title_accent_html($title)
	: esc_html(wp_strip_all_tags($title));

$show_more  = (bool) $args['show_more'];
$more_label = '';
$more_link  = null;

if ($show_more) {
	$brands_count = ksenon_count_cpt('brand');
	$more_label   = function_exists('ksenon_brands_count_label')
		? ksenon_brands_count_label($brands_count)
		: __('Все марки', 'ksenonspb');
	$more_link    = array(
		'url'    => ksenon_brands_archive_url(),
		'title'  => $more_label,
		'target' => '',
	);
}

$section_class = trim('brands-section ' . (string) $args['class']);
?>
<section class="<?php echo esc_attr($section_class); ?>">
	<div class="brands-section__container container">
		<div class="section-head section-head--row brands-section__head">
			<h2 class="section-head__title title-md brands-section__title">
				<?php echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</h2>
			<?php
			if ($show_more && $more_link) {
				ksenon_render_btn_arrow($more_link, 'btn btn--primary btn--large brands-section__more', $more_label);
			}
			?>
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
