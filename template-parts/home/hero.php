<?php
/**
 * Home: Hero
 *
 * @package ksenonspb
 */

$title = (string) ksenon_home_get( 'title' );
$text  = (string) ksenon_home_get( 'text' );
$image = ksenon_home_get( 'image' );
$btn   = ksenon_home_get( 'btn' );
?>
<section class="hero">
	<div class="hero__container _container">
		<div class="hero__content">
			<?php if ( $title ) : ?>
				<h1 class="hero__title title-lg"><?php echo nl2br( esc_html( $title ) ); ?></h1>
			<?php endif; ?>
			<?php if ( $text ) : ?>
				<p class="hero__text"><?php echo nl2br( esc_html( $text ) ); ?></p>
			<?php endif; ?>
			<?php if ( is_array( $btn ) && ! empty( $btn['url'] ) ) : ?>
				<a class="btn hero__btn" href="<?php echo esc_url( ksenon_acf_link_url( $btn ) ); ?>"<?php echo ksenon_acf_link_target( $btn ) ? ' target="' . esc_attr( ksenon_acf_link_target( $btn ) ) . '"' : ''; ?>>
					<?php echo esc_html( ksenon_acf_link_title( $btn, __( 'Подробнее', 'ksenonspb' ) ) ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php if ( $image ) : ?>
			<div class="hero__media"><?php echo ksenon_acf_image( $image, 'large', array( 'class' => 'hero__img' ) ); ?></div>
		<?php endif; ?>
	</div>
</section>
