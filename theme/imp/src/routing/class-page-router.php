<?php
/**
 * Decides which pages get a Figma-designed template (config/site.php "routes").
 *
 * @package IMP
 */

namespace IMP\Routing;

use IMP\Core\Config;
use IMP\Core\Logger;

defined( 'ABSPATH' ) || exit;

/**
 * Flow per request:
 *  1. template_redirect: find the route; its controller (src/controllers/) gathers the data.
 *  2. body_class: mark the page so CSS hides Neve's own <main> (and adds per-page classes).
 *  3. wp_body_open: render templates/pages/<route>.php with that data.
 * If a controller fails, the error is logged and the page falls back to the normal
 * WordPress/Neve content instead of showing a broken layout.
 */
final class Page_Router {

	/**
	 * Matched route for this request: array{template:string,data:array}, or null.
	 *
	 * @var array|null
	 */
	private static $match = null;

	/**
	 * Hook into WordPress.
	 */
	public static function register() {
		add_action( 'template_redirect', array( __CLASS__, 'resolve' ), 20 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_action( 'wp_body_open', array( __CLASS__, 'render' ), 6 );
	}

	/**
	 * Template name of the current designed page ("home", "contact", …) or ''.
	 *
	 * @return string
	 */
	public static function current() {
		return self::$match ? self::$match['template'] : '';
	}

	/**
	 * Match the request against the routes and run the controller.
	 */
	public static function resolve() {
		$routes = (array) Config::get( 'routes', array() );
		$key    = self::route_key();
		if ( ! $key || empty( $routes[ $key ] ) ) {
			return;
		}
		$template   = $routes[ $key ];
		$controller = 'IMP\\Controllers\\' . str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $template ) ) ) . '_Controller';
		if ( ! class_exists( $controller ) ) {
			Logger::error( 'Page_Router::resolve', 'Controller missing for route', array( 'route' => $key, 'controller' => $controller ) );
			return;
		}
		try {
			self::$match = array(
				'template' => $template,
				'data'     => (array) $controller::data(),
			);
		} catch ( \Throwable $error ) {
			Logger::error(
				'Page_Router::resolve',
				'Controller failed, falling back to the default page',
				array(
					'route' => $key,
					'error' => $error->getMessage(),
					'at'    => basename( $error->getFile() ) . ':' . $error->getLine(),
				)
			);
			self::$match = null;
		}
	}

	/**
	 * Body classes for designed pages.
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public static function body_class( $classes ) {
		if ( self::$match ) {
			$classes[] = 'imp-custom';
			if ( 'document' === self::$match['template'] ) {
				$classes[] = 'imp-docpage';
			}
		}
		return $classes;
	}

	/**
	 * Print the page template.
	 */
	public static function render() {
		if ( self::$match ) {
			get_template_part( 'templates/pages/' . self::$match['template'], null, self::$match['data'] );
		}
	}

	/**
	 * Route key for the request: "front", a (decoded) page slug, or "category:<slug>".
	 *
	 * @return string
	 */
	private static function route_key() {
		if ( is_front_page() ) {
			return 'front';
		}
		if ( is_page() ) {
			return urldecode( (string) get_post_field( 'post_name', get_queried_object_id() ) ); // Persian slugs are stored URL-encoded.
		}
		if ( is_category() ) {
			return 'category:' . get_queried_object()->slug;
		}
		return '';
	}
}
