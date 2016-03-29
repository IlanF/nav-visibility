=== Nav Visibility ===
Contributors: ilanfir
Tags: menu, navigation, access control
Requires at least: 3.0.0
Tested up to: 4.4.2
Stable tag: trunk
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Adds option to show navigation items only to authenticated users, guests or everyone.

== Description ==

Adds option to control navigation item visibility.

Hide links from guests or even authenticated (logged-in) users.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/ifnav-visibility` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Appearance->Menus screen to create your menu
1. Expand menu item and change visibility as necessary

== Frequently Asked Questions ==

= Can I change item visibility via a hook? =

Yes!
Nav Visibility exposes 3 Filter hooks which allow you to modify the way the plugin decides which menu items to show.

*ifnv_nav_menu_items* allows you to change the visibility logic completely.

2 argument are passed: array $items, array $args (same as *wp_get_nav_menu_items* filter hook)

*ifnv_should_display_nav_item* filter is called for each item in the menu.

2 argument are passed to this filter: bool $is_visible, int $menu_item_id

For example to allow admin user to view all menu items in the front end:
`
add_filter( 'ifnv_should_display_nav_item', 'my_theme_should_display_nav_item', 10, 2 );
function my_theme_should_display_nav_item( $is_visible, $menu_item_id ) {
    if( current_user_can( 'Administrator' ) ) {
        return true;
    }

    // default visibility
    return $is_visible;
}
`

*ifnv_should_display_nav_item_**####*** allows to change a specific menu item by id (denoted by the **####**) visibility.

2 argument are passed to this filter: string $menu_item_visibility, int $menu_item_id

Both *ifnv_should_display_nav_item* and *ifnv_should_display_nav_item_**####*** filters should return a boolean value (true/false)

*ifnv_nav_menu_items* returns items array after filtering

= What happens to nested items when the parent is hidden? =

If the parent is hidden all nested items in it are hidden.

Please note that **when overriding** the visibility of a nested item, if the item is set to be visible while its parent is hidden, the item will move up to the root of the menu:
`
1. item
2. item (hidden)
    2a. item (visible)
3. item
    3a. item (hidden)
        3aa. item (visible)
    3b. item
4. item (hidden)
    4a. item (hidden)
        4aa. item (visible)
`
will show as:
`
1. item
2a. item (visible)
3. item
    3b. item
3aa. item (visible)
4aa. item (visible)
`

== Screenshots ==

1. Appearance->Menus item edit view
2. Example menu - admin view
3. Front end logged in view
4. Front end guest view

== Changelog ==

= 1.0 =
* Initial release.
