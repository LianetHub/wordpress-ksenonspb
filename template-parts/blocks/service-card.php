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

$image   = ksenon_get_post_field( 'card_image', $post->ID );
$price   = (string) ksenon_get_post_field( 'price_from', $post->ID );
$excerpt = (string) ksenon_get_post_field( 'card_excerpt', $post->ID );
$tags    = ksenon_get_post_field( 'card_tags', $post->ID );

if ( ! $image && has_post_thumbnail( $post ) ) {
	$image = get_post_thumbnail_id( $post );
}

if ( ! $excerpt && has_excerpt( $post ) ) {
	$excerpt = get_the_excerpt( $post );
}

$bg_style = '';
if ( $image ) {
	$image_url = is_array( $image ) ? ( $image['url'] ?? '' ) : wp_get_attachment_image_url( $image, 'large' );
	if ( $image_url ) {
		$bg_style = ' style="background-image:url(' . esc_url( $image_url ) . ')"';
	}
}
?>
<article class="service-card"<?php echo $bg_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="service-card__inner">
		<div class="service-card__head">
			<h3 class="service-card__title"><?php echo esc_html( get_the_title( $post ) ); ?></h3>
			<?php if ( $price ) : ?>
				<div class="service-card__price"><?php echo esc_html( $price ); ?></div>
			<?php endif; ?>
		</div>
		<?php if ( $excerpt ) : ?>
			<p class="service-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
		<?php endif; ?>
		<?php if ( is_array( $tags ) && $tags ) : ?>
			<div class="service-card__tags">
				<?php foreach ( $tags as $tag ) : ?>
					<?php if ( ! empty( $tag['label'] ) ) : ?>
						<span class="service-card__tag"><?php echo esc_html( $tag['label'] ); ?></span>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<a class="service-card__link btn btn--arrow btn--arrow-card" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
			<span class="btn__text"><?php esc_html_e( 'Перейти', 'ksenonspb' ); ?></span>
			<span class="btn__arrow" aria-hidden="true">
				<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="20" cy="20" r="20" fill="#FD8011"/>
					<path d="M16 15L24 20L16 25" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</span>
		</a>
	</div>
</article>
