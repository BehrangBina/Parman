<?php
/**
 * IMP Mobile — Neve child theme.
 *
 * Desktop (>= 960px) keeps Neve's layout untouched. Below 960px (Neve's own
 * mobile breakpoint) the Neve header/footer are hidden and the markup from
 * inc/ is shown instead, styled by css/mobile.css.
 */

defined( 'ABSPATH' ) || exit;

define( 'IMP_M_VERSION', '0.1.0' );
define( 'IMP_M_DIR', get_stylesheet_directory() );
define( 'IMP_M_URI', get_stylesheet_directory_uri() );

require IMP_M_DIR . '/inc/helpers.php';
require IMP_M_DIR . '/inc/date.php';
require IMP_M_DIR . '/inc/header.php';
require IMP_M_DIR . '/inc/pages.php';
require IMP_M_DIR . '/inc/home.php';
require IMP_M_DIR . '/inc/donate.php';
require IMP_M_DIR . '/inc/statements.php';
require IMP_M_DIR . '/inc/news.php';
require IMP_M_DIR . '/inc/magazine.php';
require IMP_M_DIR . '/inc/footer.php';
require IMP_M_DIR . '/inc/contact.php';
require IMP_M_DIR . '/inc/forms.php';

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'neve-style', get_template_directory_uri() . '/style.css', array(), wp_get_theme( 'neve' )->get( 'Version' ) );

	wp_enqueue_style(
		'imp-m-fonts',
		'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700;800;900&family=Roboto+Slab:wght@400;700&family=Roboto:wght@400;700&display=swap',
		array(),
		null
	);
	// File mtime as version so every edit busts browser/CDN caches.
	$ver = function ( $file ) {
		return IMP_M_VERSION . '.' . filemtime( IMP_M_DIR . $file );
	};
	wp_enqueue_style( 'imp-m-tokens', IMP_M_URI . '/css/tokens.css', array(), $ver( '/css/tokens.css' ) );
	wp_enqueue_style( 'imp-m-mobile', IMP_M_URI . '/css/mobile.css', array( 'imp-m-tokens', 'neve-style' ), $ver( '/css/mobile.css' ) );

	wp_enqueue_script( 'imp-m-mobile', IMP_M_URI . '/js/mobile.js', array(), $ver( '/js/mobile.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
}, 20 );

add_filter( 'wp_resource_hints', function ( $urls, $relation ) {
	if ( 'preconnect' === $relation ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}, 10, 2 );

add_filter( 'body_class', function ( $classes ) {
	$classes[] = 'imp-m-enabled';
	return $classes;
} );
