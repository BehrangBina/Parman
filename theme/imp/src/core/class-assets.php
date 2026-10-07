<?php
/**
 * Stylesheets, scripts and font preconnect.
 *
 * @package IMP
 */

namespace IMP\Core;

use IMP\Routing\Page_Router;

defined( 'ABSPATH' ) || exit;

/**
 * CSS is one file per component (assets/css/components/) and per designed page
 * (assets/css/pages/); each file holds its mobile rules and its ≥1024px desktop rules.
 * Page files load only on their own page. No build step: on the live site the WP-Optimize
 * plugin already combines and minifies stylesheets.
 */
final class Assets {

	/**
	 * Component stylesheets, in cascade order (later files may build on earlier ones).
	 *
	 * @var string[]
	 */
	const COMPONENTS = array(
		'header',
		'search',
		'mobile-menu',
		'donate-tab',
		'back-link',
		'buttons',
		'ornament-title',
		'news-card',
		'contact-form',
		'statement-card',
		'pagination',
		'dialog',
		'fluent-forms',
		'footer',
	);

	/**
	 * Hook into WordPress.
	 */
	public static function register() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 20 );
		add_filter( 'wp_resource_hints', array( __CLASS__, 'preconnect_fonts' ), 10, 2 );
	}

	/**
	 * Neve's stylesheet first (we build on it), then fonts, tokens, base, components, page.
	 */
	public static function enqueue() {
		wp_enqueue_style( 'neve-style', get_template_directory_uri() . '/style.css', array(), wp_get_theme( 'neve' )->get( 'Version' ) );
		wp_enqueue_style( 'imp-fonts', Config::get( 'fonts_url' ), array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Google Fonts URL is versioned by its query.

		$previous = self::style( 'imp-tokens', 'tokens', array( 'neve-style' ) );
		$previous = self::style( 'imp-base', 'base', array( $previous ) );
		foreach ( self::COMPONENTS as $component ) {
			$previous = self::style( 'imp-' . $component, 'components/' . $component, array( $previous ) );
		}
		$page = Page_Router::current();
		if ( $page ) {
			self::style( 'imp-page-' . $page, 'pages/' . $page, array( $previous ) );
		}

		wp_enqueue_script( 'imp', IMP_URI . '/js/mobile.js', array(), self::version( 'js/mobile.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	}

	/**
	 * Enqueue one stylesheet from assets/css/.
	 *
	 * @param string   $handle Style handle.
	 * @param string   $file   Path inside assets/css/ without ".css".
	 * @param string[] $deps   Dependencies (keeps the cascade order).
	 * @return string The handle, to chain as the next file's dependency.
	 */
	private static function style( $handle, $file, array $deps ) {
		$path = 'assets/css/' . $file . '.css';
		wp_enqueue_style( $handle, IMP_URI . '/' . $path, $deps, self::version( $path ) );
		return $handle;
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
