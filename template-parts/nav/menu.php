<?php
/**
 * Main navigation menu
 *
 * @package ksenonspb
 *
 * @var array $args {
 *     @type string $menu_class Menu list class.
 *     @type string $item_class Menu item class.
 *     @type string $link_class Menu link class.
 * }
 */

$args = wp_parse_args(
	isset( $args ) && is_array( $args ) ? $args : array(),
	array(
		'menu_class' => 'header__menu',
		'item_class' => 'header__item',
		'link_class' => 'header__link',
	)
);

if ( ! function_exists( 'have_rows' ) || ! have_rows( 'glavnoe_menyu', 'option' ) ) {
	return;
}

$menu_class = (string) $args['menu_class'];
$item_class = (string) $args['item_class'];
$link_class = (string) $args['link_class'];
?>
<ul class="<?php echo esc_attr( $menu_class ); ?>">
	<?php
	while ( have_rows( 'glavnoe_menyu', 'option' ) ) :
		the_row();
		$title    = (string) get_sub_field( 'nazvanie' );
		$link     = get_sub_field( 'ssylka' );
		$has_sub  = (bool) get_sub_field( 'est_podmenyu' );
		$link_url = ksenon_resolve_link( $link );
		$active   = ksenon_is_menu_link_active( $link );
		?>
		<li class="<?php echo esc_attr( trim( $item_class . ( $active ? ' _active' : '' ) . ( $has_sub ? ' _has-sub' : '' ) ) ); ?>">
			<a class="<?php echo esc_attr( $link_class ); ?>" href="<?php echo ksenon_esc_link( $link ); ?>"<?php echo $active ? ' aria-current="page"' : ''; ?>>
				<?php echo esc_html( $title ); ?>
			</a>
			<?php if ( $has_sub && have_rows( 'podmenyu' ) ) : ?>
				<ul class="<?php echo esc_attr( $menu_class ); ?>__sub">
					<?php
					while ( have_rows( 'podmenyu' ) ) :
						the_row();
						$sub_title = (string) get_sub_field( 'nazvanie' );
						$sub_link  = get_sub_field( 'ssylka' );
						$sub_active = ksenon_is_menu_link_active( $sub_link );
						?>
						<li class="<?php echo esc_attr( trim( $item_class . ( $sub_active ? ' _active' : '' ) ) ); ?>">
							<a class="<?php echo esc_attr( $link_class ); ?>" href="<?php echo ksenon_esc_link( $sub_link ); ?>"<?php echo $sub_active ? ' aria-current="page"' : ''; ?>>
								<?php echo esc_html( $sub_title ); ?>
							</a>
						</li>
					<?php endwhile; ?>
				</ul>
			<?php endif; ?>
		</li>
	<?php endwhile; ?>
</ul>
