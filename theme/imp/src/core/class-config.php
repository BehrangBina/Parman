<?php
/**
 * Read access to config/site.php.
 *
 * @package IMP
 */

namespace IMP\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Config values by dot path, e.g. Config::get( 'links.media' ).
 */
final class Config {

	/**
	 * Loaded (and filtered) configuration.
	 *
	 * @var array|null
	 */
	private static $values = null;

	/**
	 * Value at a dot path, or $default when it is missing.
	 *
	 * @param string $path    Dot-separated key, e.g. "pages.donate".
	 * @param mixed  $default Fallback value.
	 * @return mixed
	 */
	public static function get( $path, $default = null ) {
		if ( null === self::$values ) {
			self::$values = (array) apply_filters( 'imp_config', require IMP_DIR . '/config/site.php' );
		}
		$value = self::$values;
		foreach ( explode( '.', $path ) as $key ) {
			if ( ! is_array( $value ) || ! array_key_exists( $key, $value ) ) {
				return $default;
			}
			$value = $value[ $key ];
		}
		return $value;
	}

	/**
	 * Permalink of a configured page (config "pages"), falling back to /slug/ so links still
	 * work on a site where the page has not been created yet.
	 *
	 * @param string $key Key in config "pages".
	 * @return string
	 */
	public static function page_url( $key ) {
		$slug = self::get( 'pages.' . $key, $key );
		$page = get_page_by_path( $slug );
		if ( ! $page ) {
			Logger::debug( 'Config::page_url', 'Page not found, using fallback URL', array( 'slug' => $slug ) );
			return home_url( '/' . trim( $slug, '/' ) . '/' );
		}
		return get_permalink( $page );
	}
}
