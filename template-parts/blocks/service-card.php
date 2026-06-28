<?php
/**
 * Service card
 *
 * @package ksenonspb
 *
 * @var array $args { @type WP_Post $post }
 */

$post = $args['post'] ?? null;
if ( ! $post instanceof WP_Post ) {
	return;
}

$image = ksenon_get_post_field( 'card_image', $post->ID );
if ( ! $image && has_post_thumbnail( $post ) ) {
	$image = get_post_thumbnail_id( $post );
}
?>
<article class="service-card">
	<a class="service-card__link" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
		<?php if ( $image ) : ?>
			<div class="service-card__media">
				<?php echo ksenon_acf_image( $image, 'medium_large', array( 'class' => 'service-card__img' ) ); ?>
			</div>
		<?php endif; ?>
		<h3 class="service-card__title"><?php echo esc_html( get_the_title( $post ) ); ?></h3>
		<?php if ( has_excerpt( $post ) ) : ?>
			<p class="service-card__excerpt"><?php echo esc_html( get_the_excerpt( $post ) ); ?></p>
		<?php endif; ?>
	</a>
</article>
