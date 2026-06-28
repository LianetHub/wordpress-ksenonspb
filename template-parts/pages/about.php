<?php
/**
 * About page
 *
 * @package ksenonspb
 */

$cards = function_exists( 'get_field' ) ? (array) get_field( 'company_cards' ) : array();
?>
<section class="about-page">
	<div class="about-page__container _container">
		<h1 class="about-page__title title-lg"><?php the_title(); ?></h1>
		<?php if ( $cards ) : ?>
			<div class="about-page__cards">
				<?php foreach ( $cards as $card ) : ?>
					<div class="about-page__card">
						<?php if ( ! empty( $card['image'] ) ) : ?>
							<?php echo ksenon_acf_image( $card['image'], 'medium', array( 'class' => 'about-page__card-img' ) ); ?>
						<?php endif; ?>
						<?php if ( ! empty( $card['title'] ) ) : ?>
							<h2><?php echo esc_html( $card['title'] ); ?></h2>
						<?php endif; ?>
						<?php if ( ! empty( $card['text'] ) ) : ?>
							<p><?php echo nl2br( esc_html( $card['text'] ) ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
$advantages = function_exists( 'get_field' ) ? (array) get_field( 'advantages' ) : array();
if ( $advantages ) {
	get_template_part(
		'template-parts/blocks/advantages',
		null,
		array(
			'title' => __( 'Преимущества', 'ksenonspb' ),
			'items' => $advantages,
		)
	);
}

$video_url = function_exists( 'get_field' ) ? (string) get_field( 'video_url' ) : '';
$poster    = function_exists( 'get_field' ) ? get_field( 'video_poster' ) : null;
if ( $video_url ) :
	?>
	<section class="about-video">
		<div class="about-video__container _container">
			<h2 class="about-video__title title-md"><?php esc_html_e( 'Видео о студии', 'ksenonspb' ); ?></h2>
			<div class="about-video__player">
				<?php if ( $poster ) : ?>
					<?php echo ksenon_acf_image( $poster, 'large', array( 'class' => 'about-video__poster' ) ); ?>
				<?php endif; ?>
				<a class="about-video__link" href="<?php echo esc_url( $video_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Смотреть видео', 'ksenonspb' ); ?></a>
			</div>
		</div>
	</section>
	<?php
endif;

$certificates = function_exists( 'get_field' ) ? (array) get_field( 'certificates' ) : array();
if ( $certificates ) :
	?>
	<section class="about-certificates">
		<div class="about-certificates__container _container">
			<h2 class="about-certificates__title title-md"><?php esc_html_e( 'Сертификаты', 'ksenonspb' ); ?></h2>
			<div class="about-certificates__grid">
				<?php foreach ( $certificates as $image ) : ?>
					<?php echo ksenon_acf_image( $image, 'medium', array( 'class' => 'about-certificates__img' ) ); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
endif;

get_template_part( 'template-parts/blocks/cta-form', null, array( 'variant' => 'free_inspection' ) );
