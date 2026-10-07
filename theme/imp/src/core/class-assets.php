<?php
/**
 * Stylesheets, scripts and font preconnect.
 *
 * @package IMP
 */

namespace IMP\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues the theme's CSS and JS.
 */
final class Assets {

	/**
	 * Hook into WordPress.
	 */
	public static function register() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 20 );
		add_filter( 'wp_resource_hints', array( __CLASS__, 'preconnect_fonts' ), 10, 2 );
	}

	/**
	 * Neve's stylesheet first (we build on it), then fonts, tokens, mobile base, desktop layer.
	 */
	public static function enqueue() {
		wp_enqueue_style( 'neve-style', get_template_directory_uri() . '/style.css', array(), wp_get_theme( 'neve' )->get( 'Version' ) );
		wp_enqueue_style( 'imp-fonts', Config::get( 'fonts_url' ), array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Google Fonts URL is versioned by its query.

		wp_enqueue_style( 'imp-tokens', IMP_URI . '/css/tokens.css', array(), self::version( 'css/tokens.css' ) );
		wp_enqueue_style( 'imp', IMP_URI . '/css/mobile.css', array( 'imp-tokens', 'neve-style' ), self::version( 'css/mobile.css' ) );
		wp_enqueue_style( 'imp-desktop', IMP_URI . '/css/desktop.css', array( 'imp' ), self::version( 'css/desktop.css' ) );

		wp_enqueue_script( 'imp', IMP_URI . '/js/mobile.js', array(), self::version( 'js/mobile.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	}

	/**
	 * Start the font connection early.
	 *
	 * @param array  $urls     Resource hints.
	 * @param string $relation Hint type.
	 * @return array
	 */
	public static function preconnect_fonts( $urls, $relation ) {
		if ( 'preconnect' === $relation ) {
			$urls[] = array(
				'href' => 'https://fonts.gstatic.com',
				'crossorigin',
			);
		}
		return $urls;
	}

	/**
	 * Theme version + file modification time, so every edit busts browser and CDN caches.
	 *
	 * @param string $file Path inside the theme.
	 * @return string
	 */
	private static function version( $file ) {
		$path = IMP_DIR . '/' . $file;
		if ( ! is_readable( $path ) ) {
			Logger::error( 'Assets::version', 'Asset missing', array( 'file' => $file ) );
			return IMP_VERSION;
		}
		return IMP_VERSION . '.' . filemtime( $path );
	}
}
