<?php

/**
 * Custom nav walker for header (desktop + mobile drawer).
 *
 * @package ksenonspb
 */

if (! class_exists('Ksenon_Walker_Header_Nav')) {
	class Ksenon_Walker_Header_Nav extends Walker_Nav_Menu
	{
		/**
		 * Whether drawer elements were reordered (primary first).
		 *
		 * @var bool
		 */
		protected $drawer_reordered = false;

		/**
		 * Whether the drawer menu <ul> has been opened.
		 *
		 * @var bool
		 */
		protected $drawer_menu_open = false;

		/**
		 * @param string   $output Used to append additional content (passed by reference).
		 * @param int      $depth  Depth of menu item. Used for padding.
		 * @param stdClass $args   An object of wp_nav_menu() arguments.
		 */
		public function start_lvl(&$output, $depth = 0, $args = null)
		{
			if ($this->is_drawer($args)) {
				$output .= '<ul class="header-drawer__submenu">';
				return;
			}

			$output .= '<ul class="header__submenu">';
		}

		/**
		 * @param string   $output Used to append additional content (passed by reference).
		 * @param int      $depth  Depth of menu item. Used for padding.
		 * @param stdClass $args   An object of wp_nav_menu() arguments.
		 */
		public function end_lvl(&$output, $depth = 0, $args = null)
		{
			$output .= '</ul>';
		}

		/**
		 * @param string   $output Used to append additional content (passed by reference).
		 * @param WP_Post  $item   Menu item data object.
		 * @param int      $depth  Depth of menu item. Used for padding.
		 * @param stdClass $args   An object of wp_nav_menu() arguments.
		 * @param int      $id     Current item ID.
		 */
		public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
		{
			$title = apply_filters('the_title', $item->title, $item->ID);
			$title = apply_filters('nav_menu_item_title', $title, $item, $args, $depth);
			$url   = ! empty($item->url) ? $item->url : '';
			$current = ! empty($item->current) || ! empty($item->current_item_ancestor) || ! empty($item->current_item_parent);
			$has_children = ! empty($this->has_children);

			if ($this->is_drawer($args)) {
				$this->start_el_drawer($output, $item, $depth, $title, $url, $current, $has_children);
				return;
			}

			$this->start_el_desktop($output, $item, $depth, $title, $url, $current, $has_children);
		}

		/**
		 * @param string   $output Used to append additional content (passed by reference).
		 * @param WP_Post  $item   Page data object. Not used.
		 * @param int      $depth  Depth of page. Not Used.
		 * @param stdClass $args   An object of wp_nav_menu() arguments.
		 */
		public function end_el(&$output, $item, $depth = 0, $args = null)
		{
			if ($this->is_drawer($args) && 0 === (int) $depth && $this->item_is_primary($item)) {
				return;
			}

			$output .= '</li>';
		}

		/**
		 * @param array $elements  Array of elements to continue walking.
		 * @param int   $max_depth Max depth to traverse.
		 * @param mixed ...$args   Optional additional arguments.
		 * @return string
		 */
		public function walk($elements, $max_depth, ...$args)
		{
			$menu_args = isset($args[0]) ? $args[0] : null;

			if ($this->is_drawer($menu_args) && ! $this->drawer_reordered) {
				$this->drawer_reordered = true;
				$elements = $this->reorder_drawer_elements($elements);
			}

			$output = parent::walk($elements, $max_depth, ...$args);

			if ($this->is_drawer($menu_args) && $this->drawer_menu_open) {
				$output .= '</ul>';
				$this->drawer_menu_open = false;
			}

			return $output;
		}

		/**
		 * Put primary top-level items first so the orange CTA stays outside the menu list.
		 *
		 * @param array $elements Menu elements.
		 * @return array
		 */
		protected function reorder_drawer_elements($elements)
		{
			$primary_ids = array();

			foreach ($elements as $el) {
				if (0 === (int) $el->menu_item_parent && $this->item_is_primary($el)) {
					$primary_ids[$el->ID] = true;
				}
			}

			if (! $primary_ids) {
				return $elements;
			}

			$changed = true;
			while ($changed) {
				$changed = false;
				foreach ($elements as $el) {
					$parent_id = (int) $el->menu_item_parent;
					if ($parent_id && isset($primary_ids[$parent_id]) && ! isset($primary_ids[$el->ID])) {
						$primary_ids[$el->ID] = true;
						$changed = true;
					}
				}
			}

			$primary = array();
			$rest    = array();

			foreach ($elements as $el) {
				if (isset($primary_ids[$el->ID])) {
					$primary[] = $el;
				} else {
					$rest[] = $el;
				}
			}

			return array_merge($primary, $rest);
		}

		/**
		 * Skip children of primary items in the drawer (orange CTA only).
		 *
		 * @param object $element           Data object.
		 * @param array  $children_elements List of elements to continue searching.
		 * @param int    $max_depth         Max depth to effectively display.
		 * @param int    $depth             Depth of current element.
		 * @param array  $args              An array of arguments.
		 * @param string $output            Used to append additional content.
		 */
		public function display_element($element, &$children_elements, $max_depth, $depth, $args, &$output)
		{
			$menu_args = isset($args[0]) ? $args[0] : null;

			if (
				$this->is_drawer($menu_args)
				&& 0 === (int) $depth
				&& $this->item_is_primary($element)
			) {
				$id_field = $this->db_fields['id'];
				$this->has_children = ! empty($children_elements[$element->$id_field]);
				$this->start_el($output, $element, $depth, $menu_args);
				$this->end_el($output, $element, $depth, $menu_args);
				return;
			}

			parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
		}

		/**
		 * @param stdClass|null $args Menu args.
		 */
		protected function is_drawer($args)
		{
			return is_object($args) && ! empty($args->ksenon_variant) && 'drawer' === $args->ksenon_variant;
		}

		/**
		 * @param WP_Post|object $item Menu item.
		 */
		protected function item_is_primary($item)
		{
			$classes = empty($item->classes) ? array() : (array) $item->classes;

			return in_array('is-primary', $classes, true);
		}

		/**
		 * @param bool $current Whether the item is current.
		 */
		protected function aria_current_attr($current)
		{
			return $current ? ' aria-current="page"' : '';
		}

		/**
		 * Chevron SVG for desktop parent links.
		 */
		protected function chevron_svg()
		{
			return '<svg class="header__link-chevron" width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		}

		/**
		 * Primary CTA arrow SVG for drawer.
		 */
		protected function primary_arrow_svg()
		{
			return '<span class="header-drawer__primary-arrow" aria-hidden="true"><svg width="31" height="32" viewBox="0 0 31 32" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="15.5" cy="16" r="15.5" fill="white"/><path d="M14 11L19 16L14 21" stroke="#FD8011" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
		}

		/**
		 * Desktop start_el.
		 *
		 * @param string  $output       Output buffer.
		 * @param WP_Post $item         Menu item.
		 * @param int     $depth        Depth.
		 * @param string  $title        Item title.
		 * @param string  $url          Item URL.
		 * @param bool    $current      Is current.
		 * @param bool    $has_children Has children.
		 */
		protected function start_el_desktop(&$output, $item, $depth, $title, $url, $current, $has_children)
		{
			if (0 === (int) $depth) {
				$li_class = 'header__menu-item';
				if ($has_children) {
					$li_class .= ' header__menu-item--has-sub';
				}

				$output .= '<li class="' . esc_attr($li_class) . '">';
				$output .= '<a class="header__link" href="' . esc_url($url) . '"' . $this->aria_current_attr($current);

				if ($has_children) {
					$output .= ' aria-haspopup="true" aria-expanded="false"';
				}

				$output .= '>' . esc_html($title);

				if ($has_children) {
					$output .= $this->chevron_svg();
				}

				$output .= '</a>';
				return;
			}

			$output .= '<li class="header__submenu-item">';
			$output .= '<a class="header__submenu-link" href="' . esc_url($url) . '"' . $this->aria_current_attr($current) . '>';
			$output .= esc_html($title);
			$output .= '</a>';
		}

		/**
		 * Drawer start_el.
		 *
		 * @param string  $output       Output buffer.
		 * @param WP_Post $item         Menu item.
		 * @param int     $depth        Depth.
		 * @param string  $title        Item title.
		 * @param string  $url          Item URL.
		 * @param bool    $current      Is current.
		 * @param bool    $has_children Has children.
		 */
		protected function start_el_drawer(&$output, $item, $depth, $title, $url, $current, $has_children)
		{
			if (0 === (int) $depth && $this->item_is_primary($item)) {
				$output .= '<a class="header-drawer__primary" href="' . esc_url($url) . '"' . $this->aria_current_attr($current) . '>';
				$output .= '<span>' . esc_html($title) . '</span>';
				$output .= $this->primary_arrow_svg();
				$output .= '</a>';
				return;
			}

			if (0 === (int) $depth) {
				if (! $this->drawer_menu_open) {
					$output .= '<ul class="header-drawer__menu">';
					$this->drawer_menu_open = true;
				}

				$li_class = 'header-drawer__menu-item';
				if ($has_children) {
					$li_class .= ' header-drawer__menu-item--has-sub';
				}

				$output .= '<li class="' . esc_attr($li_class) . '">';

				if ($has_children) {
					$output .= '<div class="header-drawer__item-row">';
					$output .= '<a class="header-drawer__link" href="' . esc_url($url) . '"' . $this->aria_current_attr($current) . '>';
					$output .= esc_html($title);
					$output .= '</a>';
					$output .= '<button class="header-drawer__sub-toggle" type="button" aria-expanded="false" aria-label="' . esc_attr__('Открыть подменю', 'ksenonspb') . '">';
					$output .= '<svg class="header-drawer__sub-chevron" width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
					$output .= '</button>';
					$output .= '</div>';
					return;
				}

				$output .= '<a class="header-drawer__link" href="' . esc_url($url) . '"' . $this->aria_current_attr($current) . '>';
				$output .= esc_html($title);
				$output .= '</a>';
				return;
			}

			$output .= '<li class="header-drawer__submenu-item">';
			$output .= '<a class="header-drawer__submenu-link" href="' . esc_url($url) . '"' . $this->aria_current_attr($current) . '>';
			$output .= esc_html($title);
			$output .= '</a>';
		}
	}
}
