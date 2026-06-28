<?php
/**
 * Advantages block
 *
 * @package ksenonspb
 *
 * @var array $args { @type string $tag, @type string $title, @type array $items }
 */

$args = wp_parse_args(
	isset( $args ) && is_array( $args ) ? $args : array(),
	array(
		'tag'   => '',
		'title' => '',
		'items' => array(),
	)
);

$items = array_filter(
	(array) $args['items'],
	static function ( $item ) {
		return is_array( $item ) && ( '' !== trim( (string) ( $item['title'] ?? '' ) ) || '' !== trim( (string) ( $item['text'] ?? '' ) ) );
	}
);

if ( ! $items && ! $args['title'] ) {
	return;
}
?>
<section class="advantages">
	<div class="advantages__container _container">
		<?php if ( $args['tag'] ) : ?>
			<span class="advantages__tag tag <?php echo ksenon_anim_class( 'bounce-up' ); ?>"><?php echo esc_html( $args['tag'] ); ?></span>
		<?php endif; ?>
		<?php if ( $args['title'] ) : ?>
			<h2 class="advantages__title title-md <?php echo ksenon_anim_class( 'fade-up' ); ?>"><?php echo esc_html( $args['title'] ); ?></h2>
		<?php endif; ?>
		<?php if ( $items ) : ?>
			<div class="advantages__grid">
				<?php foreach ( $items as $item ) : ?>
					<div class="advantages__item <?php echo ksenon_anim_class( 'fade-up' ); ?>">
						<?php if ( ! empty( $item['title'] ) ) : ?>
							<h3 class="advantages__item-title"><?php echo esc_html( $item['title'] ); ?></h3>
						<?php endif; ?>
						<?php if ( ! empty( $item['text'] ) ) : ?>
							<p class="advantages__item-text"><?php echo nl2br( esc_html( $item['text'] ) ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
