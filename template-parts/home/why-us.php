<?php
/**
 * Home: Why us
 *
 * @package ksenonspb
 */

$items = ksenon_home_rows( 'items' );
if ( ! $items && ! ksenon_home_get( 'title' ) ) {
	return;
}
?>
<section class="why-us">
	<div class="why-us__container _container">
		<?php if ( ksenon_home_get( 'tag' ) ) : ?>
			<span class="why-us__tag tag"><?php echo esc_html( (string) ksenon_home_get( 'tag' ) ); ?></span>
		<?php endif; ?>
		<h2 class="why-us__title title-md"><?php echo esc_html( (string) ksenon_home_get( 'title', __( 'Почему мы?', 'ksenonspb' ) ) ); ?></h2>
		<?php if ( $items ) : ?>
			<ul class="why-us__list">
				<?php foreach ( $items as $item ) : ?>
					<?php if ( ! empty( $item['text'] ) ) : ?>
						<li class="why-us__item"><?php echo esc_html( $item['text'] ); ?></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
