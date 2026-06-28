<?php
/**
 * Pricing page
 *
 * @package ksenonspb
 */
?>
<section class="pricing-page">
	<div class="pricing-page__container _container">
		<h1 class="pricing-page__title title-lg"><?php echo esc_html( function_exists( 'get_field' ) && get_field( 'hero_title' ) ? (string) get_field( 'hero_title' ) : get_the_title() ); ?></h1>
		<?php if ( function_exists( 'get_field' ) && get_field( 'hero_text' ) ) : ?>
			<p class="pricing-page__lead"><?php echo nl2br( esc_html( (string) get_field( 'hero_text' ) ) ); ?></p>
		<?php endif; ?>
		<?php
		if ( function_exists( 'get_field' ) && get_field( 'hero_image' ) ) {
			echo ksenon_acf_image( get_field( 'hero_image' ), 'large', array( 'class' => 'pricing-page__hero-img' ) );
		}
		?>

		<?php
		$table = function_exists( 'get_field' ) ? (array) get_field( 'price_table' ) : array();
		if ( $table ) :
			?>
			<div class="pricing-page__table-wrap">
				<table class="pricing-page__table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Услуга', 'ksenonspb' ); ?></th>
							<th><?php esc_html_e( 'Цена от', 'ksenonspb' ); ?></th>
							<th><?php esc_html_e( 'Примечание', 'ksenonspb' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $table as $row ) : ?>
							<tr>
								<td><?php echo esc_html( (string) ( $row['service_name'] ?? '' ) ); ?></td>
								<td><?php echo esc_html( (string) ( $row['price_from'] ?? '' ) ); ?></td>
								<td><?php echo esc_html( (string) ( $row['note'] ?? '' ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
$why_cards = function_exists( 'get_field' ) ? (array) get_field( 'why_prices_cards' ) : array();
if ( $why_cards ) {
	get_template_part(
		'template-parts/blocks/advantages',
		null,
		array(
			'title' => function_exists( 'get_field' ) ? (string) get_field( 'why_prices_title' ) : __( 'Почему цены разные?', 'ksenonspb' ),
			'items' => $why_cards,
		)
	);
}
?>

<section class="pricing-extra">
	<div class="pricing-extra__container _container">
		<?php if ( function_exists( 'get_field' ) && get_field( 'installment_title' ) ) : ?>
			<div class="pricing-extra__block">
				<h2 class="title-md"><?php echo esc_html( (string) get_field( 'installment_title' ) ); ?></h2>
				<?php echo wp_kses_post( (string) get_field( 'installment_text' ) ); ?>
			</div>
		<?php endif; ?>
		<?php if ( function_exists( 'get_field' ) && get_field( 'gift_title' ) ) : ?>
			<div class="pricing-extra__block">
				<h2 class="title-md"><?php echo esc_html( (string) get_field( 'gift_title' ) ); ?></h2>
				<?php echo wp_kses_post( (string) get_field( 'gift_text' ) ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>
