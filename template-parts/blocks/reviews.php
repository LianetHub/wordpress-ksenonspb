<?php
/**
 * Reviews block (global from theme settings)
 *
 * @package ksenonspb
 *
 * @var array $args { @type string $tag, @type string $title }
 */

$args = wp_parse_args(
	isset( $args ) && is_array( $args ) ? $args : array(),
	array(
		'tag'   => '',
		'title' => __( 'Отзывы клиентов', 'ksenonspb' ),
	)
);

$reviews = ksenon_get_reviews();
if ( ! $reviews ) {
	return;
}
?>
<section class="reviews">
	<div class="reviews__container _container">
		<?php if ( $args['tag'] ) : ?>
			<span class="reviews__tag tag <?php echo ksenon_anim_class( 'bounce-up' ); ?>"><?php echo esc_html( $args['tag'] ); ?></span>
		<?php endif; ?>
		<?php if ( $args['title'] ) : ?>
			<h2 class="reviews__title title-md <?php echo ksenon_anim_class( 'fade-up' ); ?>"><?php echo esc_html( $args['title'] ); ?></h2>
		<?php endif; ?>
		<div class="reviews__grid">
			<?php foreach ( $reviews as $review ) : ?>
				<blockquote class="reviews__item <?php echo ksenon_anim_class( 'fade-up' ); ?>">
					<?php if ( ! empty( $review['photo'] ) ) : ?>
						<div class="reviews__photo">
							<?php echo ksenon_acf_image( $review['photo'], 'thumbnail', array( 'class' => 'reviews__photo-img' ) ); ?>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $review['text'] ) ) : ?>
						<p class="reviews__text"><?php echo nl2br( esc_html( $review['text'] ) ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $review['name'] ) ) : ?>
						<cite class="reviews__name"><?php echo esc_html( $review['name'] ); ?></cite>
					<?php endif; ?>
				</blockquote>
			<?php endforeach; ?>
		</div>
	</div>
</section>
