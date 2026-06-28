<?php
/**
 * Bottom CTA block
 *
 * @package ksenonspb
 *
 * @var array $args { @type string $title, @type string $text }
 */

$args = wp_parse_args(
	isset( $args ) && is_array( $args ) ? $args : array(),
	array(
		'title' => __( 'Опишите проблему — оценим быстро и по делу', 'ksenonspb' ),
		'text'  => '',
	)
);
?>
<section class="cta-bottom">
	<div class="cta-bottom__container _container">
		<h2 class="cta-bottom__title title-md"><?php echo esc_html( $args['title'] ); ?></h2>
		<?php if ( $args['text'] ) : ?>
			<p class="cta-bottom__text"><?php echo nl2br( esc_html( $args['text'] ) ); ?></p>
		<?php endif; ?>
		<div class="cta-bottom__form">
			<?php ksenon_cf7_form( 'cf7_zakaz', __( 'Опишите проблему', 'ksenonspb' ) ); ?>
		</div>
	</div>
</section>
