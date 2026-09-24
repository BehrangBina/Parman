<?php
/**
 * Shared helpers: asset URLs, site links, social profiles.
 */

defined( 'ABSPATH' ) || exit;

function imp_m_asset( $path ) {
	return IMP_M_URI . '/assets/' . ltrim( $path, '/' );
}

/**
 * Print an SVG icon inline so it can inherit colour. Falls back to <img>.
 */
function imp_m_icon( $name, $class = '' ) {
	$file = IMP_M_DIR . '/assets/icons/' . $name . '.svg';
	if ( ! is_readable( $file ) ) {
		return;
	}
	$svg = file_get_contents( $file );
	$svg = preg_replace( '/<svg\b/', '<svg aria-hidden="true" focusable="false" class="imp-m-icon ' . esc_attr( $class ) . '"', $svg, 1 );
	echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput -- file shipped with the theme.
}

/**
 * URL of a page by slug, falling back to home_url( slug ) when the page is missing locally.
 */
function imp_m_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' . trim( $slug, '/' ) . '/' );
}

/**
 * Links used by the mobile layout. Filter `imp_m_links` to change them without editing the theme.
 */
function imp_m_links() {
	return apply_filters( 'imp_m_links', array(
		'donate'       => imp_m_page_url( 'donate' ),
		'about'        => imp_m_page_url( 'about-us' ),
		'constitution' => imp_m_page_url( 'party-constitution' ),
		'affidavit'    => imp_m_page_url( 'affidavit' ),
		'motto'        => imp_m_page_url( 'partys-motto' ),
		'news'         => imp_m_page_url( 'news' ),
		'media'        => 'https://www.youtube.com/@IranianMonarchyParty',
	) );
}

function imp_m_socials() {
	return apply_filters( 'imp_m_socials', array(
		'instagram' => array( 'Instagram', 'https://www.instagram.com/iranianmonarchyparty' ),
		'facebook'  => array( 'Facebook', 'https://www.facebook.com/people/Iranian-Monarchy-Party/61582238103605/' ),
		'tiktok'    => array( 'TikTok', 'https://www.tiktok.com/@imp2584' ),
		'youtube'   => array( 'YouTube', 'https://www.youtube.com/@IranianMonarchyParty' ),
		'x'         => array( 'X', 'https://x.com/IMP2584' ),
		'telegram'  => array( 'Telegram', 'https://t.me/imp2584' ),
	) );
}
