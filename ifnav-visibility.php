<?php
	/*
	 Plugin Name:       Nav Visibility
	 Plugin URI:        https://ilanfirsov.me/wordpress/ifnav-visibility
	 Description:       Adds option to show navigation items only to authenticated users, guests or everyone.
	 Version:           1.0.0
	 Author:            Ilan Firsov
	 Author URI:        https://ilanfirsov.me/
	 License:           GPL-2.0+
	 License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
	 Text Domain:       ifnav-visibility
	 Domain Path:       /languages
	 */

	// If this file is called directly, abort.
	if ( ! defined( 'WPINC' ) ) {
		die;
	}

	/**
	 * Class IFNAV_Visibility
	 */
	final class IFNAV_Visibility {

		/**
		 * @type string
		 */
		protected $version;
		/**
		 * @type string
		 */
		protected $plugin_slug;
		/**
		 * @type string
		 */
		protected $plugin_dir;
		/**
		 * @type string
		 */
		protected $plugin_url;

		/**
		 * IFNAV_Visibility constructor.
		 */
		public function __construct() {
			$this->version     = '1.0.0';
			$this->plugin_slug = 'nav-visibility';
			$this->plugin_dir  = trailingslashit( plugin_dir_path( __FILE__ ) );
			$this->plugin_url  = trailingslashit( plugin_dir_url( __FILE__ ) );

			$this->init();
		}

		/**
		 * Initialize plugin
		 */
		protected function init() {
			$this->load_dependencies();
			$this->load_textdomain();
			$this->register_hooks();
		}

		/**
		 * Load plugin dependencies
		 */
		protected function load_dependencies() {
			require_once $this->plugin_dir . 'class.ifnav-walker-nav-menu-edit.php';
		}

		/**
		 * Load plugin textdomain
		 */
		protected function load_textdomain() {
			$locale    = apply_filters( 'plugin_locale', get_locale(), 'ifnav-visibility' );

			$global_mo = WP_LANG_DIR . '/' . $this->plugin_slug . '/' . 'ifnav-visibility' . '-' . $locale . '.mo';
			$local_mo  = $this->plugin_dir . '/languages/' . $locale . '.mo';

			load_textdomain( 'ifnav-visibility', file_exists( $global_mo ) ? $global_mo : $local_mo );
		}

		/**
		 * Register WordPress hooks
		 */
		protected function register_hooks() {
			add_filter( 'wp_setup_nav_menu_item',  array( $this, 'nav_menu_fields' ), 0 );
			add_action( 'wp_update_nav_menu_item', array( $this, 'nav_menu_fields_update' ), 0, 3 );
			add_filter( 'wp_edit_nav_menu_walker', array( $this, 'nav_menu_fields_edit_walker' ), 0, 2 );
			add_filter( 'wp_get_nav_menu_items',   array( $this, 'nav_menu_wp_get_nav_menu_items' ), 0, 2 );
		}

		/**
		 * Add our visibility field to the returned item
		 *
		 * @param $menu_item
		 *
		 * @return mixed
		 */
		public function nav_menu_fields( $menu_item ) {
			$menu_item->visibility = get_post_meta( $menu_item->ID, '_menu_item_visibility', true );

			return $menu_item;
		}

		/**
		 * Save visibility field to menu meta data
		 *
		 * @param $menu_id
		 * @param $menu_item_db_id
		 * @param $args
		 */
		public function nav_menu_fields_update( $menu_id, $menu_item_db_id, $args ) {
			// Check if element is properly sent
			if ( isset( $_REQUEST['menu-item-visibility'] ) && is_array( $_REQUEST['menu-item-visibility'] ) ) {
				$value = $_REQUEST['menu-item-visibility'][ $menu_item_db_id ];
				update_post_meta( $menu_item_db_id, '_menu_item_visibility', $value );
			}
		}

		/**
		 * Override default Walker_Nav_Menu_Edit and show visibility fields in admin menu
		 *
		 * @param $walker
		 * @param $menu_id
		 *
		 * @return string
		 */
		public function nav_menu_fields_edit_walker( $walker, $menu_id ) {
			return 'IFNAV_Walker_Nav_Menu_Edit';
		}

		/**
		 * Filter menu items based on selected visibility
		 *
		 * @param $items
		 * @param $args
		 *
		 * @return array
		 */
		public function nav_menu_wp_get_nav_menu_items( $items, $args ) {
			// if we are on admin page display all link items
			if ( is_admin() ) {
				return $items;
			}

			// if user wants to handle the visibility allow him to modify it completely
			if ( has_filter( 'ifnv_nav_menu_items' ) ) {
				return apply_filters( 'ifnv_nav_menu_items', $items, $args );
			}

			// filter the menu items list
			return array_filter( $items,
				function ( $item ) {
					return $this->should_display_nav_item( $item->ID );
				} );

			return $items;
		}

		/**
		 * Returns wether the item should be displayed or not
		 *
		 * @param $menu_item_id
		 *
		 * @return bool
		 */
		protected function should_display_nav_item( $menu_item_id ) {
			// get current item's visibility
			$visibility = get_post_meta( $menu_item_id, '_menu_item_visibility', true );

			// allow the user wants to change only one item's visibility
			if ( has_filter( 'ifnv_should_display_nav_item_' . $menu_item_id ) ) {
				return apply_filters( 'ifnv_should_display_nav_item_' . $menu_item_id, $menu_item_id, $visibility );
			}

			// hide item if current user is logged in but the menu item is set to be visible only to guests
			if ( 'guest' === $visibility && is_user_logged_in() ) {
				return apply_filters( 'ifnv_should_display_nav_item', false, $menu_item_id );
			}

			// hide item if current user is not logged in (guest) but the menu item is visible only to authenticated users
			if ( 'authenticated' === $visibility && ! is_user_logged_in() ) {
				return apply_filters( 'ifnv_should_display_nav_item', false, $menu_item_id );
			}

			// now the item on its own should be visible, but we have to check its parents
			// if any of the item's parents are hidden the item should be hidden as well

			// get menu item parent id
			$parent_id = get_post_meta( $menu_item_id, '_menu_item_menu_item_parent', true );

			// if item has a parent return the parent's visibility
			if ( $parent_id > 0 ) {
				$parent_visible = $this->should_display_nav_item( $parent_id );
				return apply_filters( 'ifnv_should_display_nav_item', $parent_visible, $menu_item_id );
			}

			// no parents and the item should be visible
			return apply_filters( 'ifnv_should_display_nav_item', true, $menu_item_id );
		}
	}
	new IFNAV_Visibility();
