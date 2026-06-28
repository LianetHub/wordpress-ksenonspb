<?php
/**
 * Portfolio card
 *
 * @package ksenonspb
 *
 * @var array $args { @type WP_Post $post }
 */

$post = $args['post'] ?? null;
if ( ! $post instanceof WP_Post ) {
	return;
}

$image = ksenon_get_post_field( 'hero_image', $post->ID );
if ( ! $image && has_post_thumbnail( $post ) ) {
	$image = get_post_thumbnail_id( $post );
}
?>
<article class="portfolio-card">
	<a class="portfolio-card__link" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
		<?php if ( $image ) : ?>
			<div class="portfolio-card__media">
				<?php echo ksenon_acf_image( $image, 'medium_large', array( 'class' => 'portfolio-card__img' ) ); ?>
			</div>
		<?php endif; ?>
		<h3 class="portfolio-card__title"><?php echo esc_html( get_the_title( $post ) ); ?></h3>
		<?php if ( has_excerpt( $post ) ) : ?>
			<p class="portfolio-card__excerpt"><?php echo esc_html( get_the_excerpt( $post ) ); ?></p>
		<?php endif; ?>
	</a>
</article>
