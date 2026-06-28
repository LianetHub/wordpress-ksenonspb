<?php
/**
 * Header
 *
 * @package ksenonspb
 */

$main_class = ksenon_get_main_class();
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
		<?php get_template_part( 'template-parts/header/content' ); ?>
		<main class="main<?php echo $main_class ? ' ' . esc_attr( $main_class ) : ''; ?>">
