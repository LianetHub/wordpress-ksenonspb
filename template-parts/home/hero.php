<?php
/**
 * Home: Hero
 *
 * @package ksenonspb
 */

$title          = (string) ksenon_home_get( 'title' );
$text           = (string) ksenon_home_get( 'text' );
$btn            = ksenon_home_get( 'btn' );
$btn_secondary  = ksenon_home_get( 'btn_secondary' );
$slides         = ksenon_home_rows( 'slides' );

if ( ! $btn_secondary || ! is_array( $btn_secondary ) || empty( $btn_secondary['url'] ) ) {
	$portfolio_count = ksenon_count_cpt( 'portfolio' );
	$btn_secondary   = array(
		'url'    => ksenon_portfolio_archive_url(),
		'title'  => sprintf(
			/* translators: %d: portfolio items count */
			__( 'Смотреть %d работ', 'ksenonspb' ),
			$portfolio_count
		),
		'target' => '',
	);
}
?>
<section class="hero">
	<?php if ( $slides ) : ?>
		<div class="hero__promo">
			<div class="hero__promo-slider swiper">
				<div class="swiper-wrapper">
					<?php foreach ( $slides as $slide ) : ?>
						<?php
						$slide_title = trim( (string) ( $slide['title'] ?? '' ) );
						$slide_btn   = $slide['btn'] ?? null;
						$slide_image = $slide['image'] ?? null;
						$slide_style = '';
						if ( $slide_image ) {
							$slide_url = is_array( $slide_image ) ? ( $slide_image['url'] ?? '' ) : '';
							if ( $slide_url ) {
								$slide_style = ' style="background-image:url(' . esc_url( $slide_url ) . ')"';
							}
						}
						?>
						<div class="hero__promo-slide swiper-slide"<?php echo $slide_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<div class="hero__promo-container _container">
								<?php if ( $slide_title ) : ?>
									<div class="hero__promo-title"><?php echo nl2br( esc_html( $slide_title ) ); ?></div>
								<?php endif; ?>
								<?php if ( is_array( $slide_btn ) && ! empty( $slide_btn['url'] ) ) : ?>
									<?php ksenon_render_btn_arrow( $slide_btn, 'btn btn--arrow btn--arrow-sm hero__promo-btn', __( 'Подробнее', 'ksenonspb' ) ); ?>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="hero__promo-nav">
					<button class="hero__promo-prev" type="button" aria-label="<?php esc_attr_e( 'Предыдущий слайд', 'ksenonspb' ); ?>"></button>
					<button class="hero__promo-next" type="button" aria-label="<?php esc_attr_e( 'Следующий слайд', 'ksenonspb' ); ?>"></button>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="hero__main">
		<div class="hero__container _container">
			<?php if ( $title ) : ?>
				<h1 class="hero__title title-lg"><?php echo nl2br( esc_html( $title ) ); ?></h1>
			<?php endif; ?>
			<?php if ( $text ) : ?>
				<p class="hero__text"><?php echo nl2br( esc_html( $text ) ); ?></p>
			<?php endif; ?>
			<div class="hero__actions">
				<?php if ( is_array( $btn ) && ! empty( $btn['url'] ) ) : ?>
					<?php ksenon_render_btn_arrow( $btn, 'btn btn--arrow btn--accent hero__btn', __( 'Оценить ремонт', 'ksenonspb' ) ); ?>
				<?php endif; ?>
				<?php if ( is_array( $btn_secondary ) && ! empty( $btn_secondary['url'] ) ) : ?>
					<a
						class="btn btn--plain hero__btn-secondary"
						href="<?php echo esc_url( ksenon_acf_link_url( $btn_secondary ) ); ?>"
						<?php echo ksenon_acf_link_target( $btn_secondary ) ? ' target="' . esc_attr( ksenon_acf_link_target( $btn_secondary ) ) . '"' : ''; ?>
					>
						<?php echo esc_html( ksenon_acf_link_title( $btn_secondary ) ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
