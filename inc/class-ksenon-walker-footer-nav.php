<?php

/**
 * Custom nav walker for footer columns.
 *
 * @package ksenonspb
 */

if (! class_exists('Ksenon_Walker_Footer_Nav')) {
	class Ksenon_Walker_Footer_Nav extends Walker_Nav_Menu
	{
		/**
		 * @param string   $output Used to append additional content (passed by reference).
		 * @param WP_Post  $item   Menu item data object.
		 * @param int      $depth  Depth of menu item.
		 * @param stdClass $args   An object of wp_nav_menu() arguments.
		 * @param int      $id     Current item ID.
		 */
		public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
		{
			$title = apply_filters('the_title', $item->title, $item->ID);
			$title = apply_filters('nav_menu_item_title', $title, $item, $args, $depth);
			$url   = ! empty($item->url) ? $item->url : '';

			$output .= '<li class="footer__menu-item">';
			$output .= '<a class="footer__menu-link" href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
		}

		/**
		 * @param string   $output Used to append additional content (passed by reference).
		 * @param WP_Post  $item   Menu item data object.
		 * @param int      $depth  Depth of menu item.
		 * @param stdClass $args   An object of wp_nav_menu() arguments.
		 */
		public function end_el(&$output, $item, $depth = 0, $args = null)
		{
			$output .= '</li>';
		}
	}
}
