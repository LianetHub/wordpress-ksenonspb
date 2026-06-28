<?php
/**
 * Header
 *
 * @package ksenonspb
 */

$logo          = ksenon_get_option( 'logotip' );
$header_phones = ksenon_get_phones( true );
$main_class    = ksenon_get_main_class();
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="format-detection" content="telephone=no">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php
	$keywords = '';
	if ( function_exists( 'get_field' ) && is_singular( 'page' ) ) {
		$keywords = (string) get_field( 'keywords', get_queried_object_id() );
	}
	if ( $keywords ) :
		?>
	<meta name="keywords" content="<?php echo esc_attr( wp_strip_all_tags( $keywords ) ); ?>">
	<?php endif; ?>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<div class="wrapper">
		<header class="header">
			<div class="header__container _container _container--small">
				<div class="header__bar">
					<?php if ( $logo ) : ?>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="header__logo">
							<?php
							echo ksenon_acf_image(
								$logo,
								'full',
								array(
									'class'         => 'header__logo-img',
									'loading'       => 'eager',
									'fetchpriority' => 'high',
									'width'         => '111',
									'height'        => '31',
								)
							);
							?>
						</a>
					<?php endif; ?>

					<nav class="header__nav" id="header-nav" aria-label="<?php esc_attr_e( 'Основная навигация', 'ksenonspb' ); ?>">
						<?php if ( function_exists( 'have_rows' ) && have_rows( 'glavnoe_menyu', 'option' ) ) : ?>
							<?php ksenon_render_main_menu(); ?>
						<?php endif; ?>
					</nav>

					<?php if ( $header_phones ) : ?>
						<div class="header__phones" role="group" aria-label="<?php esc_attr_e( 'Телефоны', 'ksenonspb' ); ?>">
							<?php foreach ( $header_phones as $index => $phone ) : ?>
								<?php if ( $index > 0 ) : ?>
									<span class="header__sep" aria-hidden="true"></span>
								<?php endif; ?>
								<a class="header__phone" href="tel:+<?php echo esc_attr( ksenon_phone_clean( $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<button class="icon-menu header__toggle" type="button" aria-label="<?php esc_attr_e( 'Открыть меню', 'ksenonspb' ); ?>" aria-expanded="false" aria-controls="header-nav">
						<span aria-hidden="true"></span>
						<span aria-hidden="true"></span>
						<span aria-hidden="true"></span>
					</button>
				</div>
			</div>
		</header>
		<main class="main<?php echo $main_class ? ' ' . esc_attr( $main_class ) : ''; ?>">
