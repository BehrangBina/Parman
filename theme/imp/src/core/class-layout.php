<?php
/**
 * Site-wide layout: the two headers (mobile + desktop), search, mobile menu, Donate tab and
 * the footer. Templates live in templates/layout/.
 *
 * @package IMP
 */

namespace IMP\Core;

use IMP\Data\Menu_Tree;
use IMP\Routing\Page_Router;
use IMP\Services\Jalali_Date;

defined( 'ABSPATH' ) || exit;

/**
 * Prints the layout around the page content and registers the menu locations.
 */
final class Layout {

	/**
	 * Hook into WordPress.
	 */
	public static function register() {
		add_action( 'after_setup_theme', array( __CLASS__, 'register_menus' ), 20 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_action( 'wp_body_open', array( __CLASS__, 'header' ), 5 );
		add_action( 'wp_footer', array( __CLASS__, 'footer' ), 1 );
	}

	/**
	 * "Mobile menu (IMP)" and "Desktop menu (IMP)" in Appearance → Menus.
	 */
	public static function register_menus() {
		register_nav_menus( (array) Config::get( 'menus', array() ) );
	}

	/**
	 * Marks pages rendered by this theme (CSS hides Neve's header/footer).
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public static function body_class( $classes ) {
		$classes[] = 'imp-enabled';
		return $classes;
	}

	/**
	 * Header for both sizes; CSS shows one of them.
	 */
	public static function header() {
		$tree = Menu_Tree::tree( 'imp-desktop' );
		$half = (int) ceil( count( $tree ) / 2 );
		get_template_part(
			'templates/layout/header',
			null,
			array(
				'date'          => Jalali_Date::today_imperial(),
				'nav_start'     => array_slice( $tree, 0, $half ), // right of the logo in Farsi
				'nav_end'       => array_slice( $tree, $half ),
				'mobile_menu'   => self::mobile_menu_html(),
				'donate_url'    => Config::page_url( 'donate' ),
				'show_back'     => ! is_front_page(),
				'back_url'      => wp_get_referer() ? wp_get_referer() : home_url( '/' ),
				'search_query'  => get_search_query(),
			)
		);
	}

	/**
	 * Footer (the contact page shows the social icons at the top, so not again here).
	 */
	public static function footer() {
		get_template_part(
			'templates/layout/footer',
			null,
			array(
				'show_socials' => 'contact' !== Page_Router::current(),
				'socials'      => (array) Config::get( 'socials', array() ),
				'year'         => gmdate( 'Y' ),
			)
		);
	}

	/**
	 * The mobile menu as WordPress renders it (submenu chevrons are added by JS).
	 *
	 * @return string
	 */
	private static function mobile_menu_html() {
		$menu = Menu_Tree::menu_for( 'imp-mobile' );
		if ( ! $menu ) {
			return (string) wp_page_menu(
				array(
					'echo'       => false,
					'menu_class' => 'imp-menu__list',
					'depth'      => 2,
				)
			);
		}
		return (string) wp_nav_menu(
			array(
				'menu'        => $menu,
				'container'   => false,
				'menu_class'  => 'imp-menu__list',
				'fallback_cb' => false,
				'depth'       => 2,
				'echo'        => false,
			)
		);
	}
}
