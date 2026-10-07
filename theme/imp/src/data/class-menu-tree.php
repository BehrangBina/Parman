<?php
/**
 * Menus for the two headers (Appearance → Menus).
 *
 * @package IMP
 */

namespace IMP\Data;

use IMP\Core\Logger;

defined( 'ABSPATH' ) || exit;

/**
 * Resolves the menu for a location with fallbacks, and builds the two-level tree the desktop
 * header needs (it splits top-level items around the logo).
 */
final class Menu_Tree {

	/**
	 * Menu term ID for a location: the location itself, then Neve's "primary", then the
	 * first menu that exists. 0 when the site has no menus at all.
	 *
	 * @param string $location Menu location.
	 * @return int
	 */
	public static function menu_for( $location ) {
		$locations = get_nav_menu_locations();
		foreach ( array( $location, 'primary' ) as $candidate ) {
			if ( ! empty( $locations[ $candidate ] ) && wp_get_nav_menu_object( $locations[ $candidate ] ) ) {
				return (int) $locations[ $candidate ];
			}
		}
		$menus = wp_get_nav_menus();
		Logger::warning( 'Menu_Tree::menu_for', 'No menu assigned to location, using fallback', array( 'location' => $location ) );
		return $menus ? (int) $menus[0]->term_id : 0;
	}

	/**
	 * Two-level tree: [ [ 'item' => WP_Post, 'children' => WP_Post[], 'current' => bool ], … ].
	 *
	 * @param string $location Menu location.
	 * @return array[]
	 */
	public static function tree( $location ) {
		$menu  = self::menu_for( $location );
		$items = $menu ? wp_get_nav_menu_items( $menu ) : array();
		if ( ! $items ) {
			return array();
		}
		_wp_menu_item_classes_by_context( $items ); // Adds current-menu-item / -ancestor classes.

		$tree = array();
		foreach ( $items as $item ) {
			if ( ! $item->menu_item_parent ) {
				$tree[ $item->ID ] = array(
					'item'     => $item,
					'children' => array(),
					'current'  => (bool) array_intersect( (array) $item->classes, array( 'current-menu-item', 'current-menu-ancestor', 'current-menu-parent' ) ),
				);
			}
		}
		foreach ( $items as $item ) {
			if ( $item->menu_item_parent && isset( $tree[ $item->menu_item_parent ] ) ) {
				$tree[ $item->menu_item_parent ]['children'][] = $item;
			}
		}
		return array_values( $tree );
	}
}
