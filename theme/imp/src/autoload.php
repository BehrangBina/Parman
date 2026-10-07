<?php
/**
 * Class autoloader for the IMP namespace.
 *
 * Maps IMP\Services\Pdf_Download to src/services/class-pdf-download.php
 * (WordPress coding standards file naming: lowercase, hyphens, "class-" prefix).
 *
 * @package IMP
 */

defined( 'ABSPATH' ) || exit;

spl_autoload_register(
	static function ( $class_name ) {
		if ( 0 !== strpos( $class_name, 'IMP\\' ) ) {
			return;
		}
		$parts = explode( '\\', substr( $class_name, 4 ) );
		$class = array_pop( $parts );
		$dir   = strtolower( implode( '/', $parts ) );
		$file  = IMP_DIR . '/src/' . ( $dir ? $dir . '/' : '' ) . 'class-' . strtolower( str_replace( '_', '-', $class ) ) . '.php';
		if ( is_readable( $file ) ) {
			require $file;
		}
	}
);
