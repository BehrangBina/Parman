<?php
/**
 * Template tags: the small set of global functions templates use. Everything else lives in
 * namespaced classes under src/; templates only print, they never query.
 *
 * @package IMP
 */

use IMP\Core\Config;

defined( 'ABSPATH' ) || exit;

/**
 * URL of a theme asset, e.g. imp_asset( 'img/logo.svg' ).
 *
 * @param string $path Path inside assets/.
 * @return string
 */
function imp_asset( $path ) {
	return IMP_URI . '/assets/' . ltrim( $path, '/' );
}

/**
 * Print a theme SVG icon inline, so CSS can colour and size it.
 *
 * @param string $name  File name in assets/icons/ without ".svg".
 * @param string $class Extra class.
 */
function imp_icon( $name, $class = '' ) {
	static $cache = array();
	if ( ! isset( $cache[ $name ] ) ) {
		$file = IMP_DIR . '/assets/icons/' . sanitize_file_name( $name ) . '.svg';
		if ( ! is_readable( $file ) ) {
			IMP\Core\Logger::warning( 'imp_icon', 'Icon file missing', array( 'icon' => $name ) );
			$cache[ $name ] = '';
			return;
		}
		$cache[ $name ] = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
	}
	if ( '' === $cache[ $name ] ) {
		return;
	}
	// Mark the icon decorative and add our class; the markup itself is a trusted theme file.
	echo preg_replace( '/<svg\b/', '<svg aria-hidden="true" focusable="false" class="imp-icon ' . esc_attr( $class ) . '"', $cache[ $name ], 1 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Render a reusable component from templates/components/, e.g.
 * imp_component( 'ornament-title', array( 'title' => 'همیاری' ) ).
 *
 * @param string $name Component file name without ".php".
 * @param array  $args Data for the component (available as $args inside it).
 */
function imp_component( $name, array $args = array() ) {
	get_template_part( 'templates/components/' . $name, null, $args );
}

/**
 * Permalink of a configured page (config/site.php "pages").
 *
 * @param string $key Page key, e.g. "donate".
 * @return string
 */
function imp_url( $key ) {
	return Config::page_url( $key );
}

/**
 * Debug helper: print a value in a readable block — only with WP_DEBUG on and only for
 * administrators, so it can never leak on the live site. Also writes it to the debug log.
 *
 * @param mixed  $value Anything.
 * @param string $label Optional label.
 */
function imp_dump( $value, $label = '' ) {
	if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$output = print_r( $value, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r -- debug helper.
	IMP\Core\Logger::debug( 'imp_dump', $label ? $label : 'value', array( 'value' => $output ) );
	printf(
		'<pre class="imp-dump">%s%s</pre>', // styled in the CSS ("Debug" section)
		$label ? '<strong>' . esc_html( $label ) . '</strong>' . "\n" : '',
		esc_html( $output )
	);
}
