<?php
/**
 * CTA + contacts block
 *
 * @package ksenonspb
 *
 * @var array $args { @type string $tag, @type string $title }
 */

$args = wp_parse_args(
	isset( $args ) && is_array( $args ) ? $args : array(),
	array(
		'tag'   => '',
		'title' => __( 'Запишитесь на бесплатный осмотр', 'ksenonspb' ),
	)
);

$phones = ksenon_get_phones();
$email  = ksenon_get_option( 'email' );
$addr   = ksenon_get_option( 'address' );
$hours  = ksenon_get_option( 'hours' );
?>
<section class="cta-contacts" id="contacts">
	<div class="cta-contacts__container _container">
		<div class="cta-contacts__head">
			<?php if ( $args['tag'] ) : ?>
				<span class="cta-contacts__tag tag"><?php echo esc_html( $args['tag'] ); ?></span>
			<?php endif; ?>
			<?php if ( $args['title'] ) : ?>
				<h2 class="cta-contacts__title title-md"><?php echo esc_html( $args['title'] ); ?></h2>
			<?php endif; ?>
		</div>
		<div class="cta-contacts__grid">
			<div class="cta-contacts__form">
				<?php ksenon_cf7_form( 'cf7_konsultaciya', __( 'CTA + контакты', 'ksenonspb' ) ); ?>
			</div>
			<div class="cta-contacts__info">
				<?php if ( $phones ) : ?>
					<div class="cta-contacts__phones">
						<?php foreach ( $phones as $phone ) : ?>
							<a href="tel:+<?php echo esc_attr( ksenon_phone_clean( $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<a class="cta-contacts__email" href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
				<?php endif; ?>
				<?php if ( $addr ) : ?>
					<p class="cta-contacts__address"><?php echo nl2br( esc_html( $addr ) ); ?></p>
				<?php endif; ?>
				<?php if ( $hours ) : ?>
					<p class="cta-contacts__hours"><?php echo nl2br( esc_html( $hours ) ); ?></p>
				<?php endif; ?>
				<?php echo ksenon_get_map_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</div>
</section>
