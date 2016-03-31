<?php
	/**
	 * Navigation Menu API: Walker_Nav_Menu_Edit class
	 *
	 * @package    WordPress
	 * @subpackage Administration
	 * @since      4.4.0
	 */

	/**
	 * Create HTML list of nav menu input items.
	 *
	 * @package WordPress
	 * @since   3.0.0
	 * @uses    Walker_Nav_Menu
	 */
	class IFNAV_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {
		/**
		 * Start the element output.
		 *
		 * @see   Walker_Nav_Menu::start_el()
		 * @since 3.0.0
		 *
		 * @global int   $_wp_nav_menu_max_depth
		 *
		 * @param string $output Passed by reference. Used to append additional content.
		 * @param object $item   Menu item data object.
		 * @param int    $depth  Depth of menu item. Used for padding.
		 * @param array  $args   Not used.
		 * @param int    $id     Not used.
		 */
		public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
			$item_output = '';
		    parent::start_el( $item_output, $item, $depth, $args, $id );

		    $new_fields = apply_filters( 'ifnv_menu_edit_add_fields', '', $item_output, $item, $depth, $args, $id );

		    if( ! empty( $new_fields ) ) {
		        $item_output = preg_replace( '/(?=<div[^>]+class="[^"]*submitbox)/', $new_fields, $item_output );
		    }

		    $output .= $item_output;
		}
	} // Walker_Nav_Menu_Edit
