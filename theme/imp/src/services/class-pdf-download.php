<?php
/**
 * Reliable PDF downloads: /?imp_pdf=<post id>.
 *
 * @package IMP
 */

namespace IMP\Services;

use IMP\Core\Logger;

defined( 'ABSPATH' ) || exit;

/**
 * Some mobile browsers ignore the HTML "download" attribute and open (or, on Android, still
 * download without a name) — so PDFs stored in this site's uploads are streamed with
 * Content-Disposition: attachment. PDFs hosted elsewhere can only be redirected to.
 */
final class Pdf_Download {

	const QUERY_VAR = 'imp_pdf';

	/**
	 * Hook into WordPress.
	 */
	public static function register() {
		add_action( 'template_redirect', array( __CLASS__, 'maybe_send' ) );
	}

	/**
	 * Download link for a post's PDF.
	 *
	 * @param int $post_id Issue or document page ID.
	 * @return string
	 */
	public static function url( $post_id ) {
		return add_query_arg( self::QUERY_VAR, (int) $post_id, home_url( '/' ) );
	}

	/**
	 * Handle /?imp_pdf=<id> (public link: no nonce, read-only, published posts only).
	 */
	public static function maybe_send() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- public, read-only download link.
		$requested = isset( $_GET[ self::QUERY_VAR ] ) ? absint( $_GET[ self::QUERY_VAR ] ) : ( isset( $_GET['imp_issue_download'] ) ? absint( $_GET['imp_issue_download'] ) : 0 );
		// phpcs:enable
		if ( ! $requested ) {
			return;
		}
		$url = Pdf_Locator::for_post( get_post( $requested ) );
		if ( ! $url ) {
			Logger::warning( 'Pdf_Download::maybe_send', 'No PDF for requested post', array( 'post_id' => $requested ) );
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		$path = self::local_path( $url );
		if ( $path ) {
			self::stream( $path );
		}
		Logger::debug( 'Pdf_Download::maybe_send', 'PDF is not in local uploads, redirecting', array( 'post_id' => $requested ) );
		wp_redirect( esc_url_raw( $url ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- admin-entered PDF URL, may be another host.
		exit;
	}

	/**
	 * File path for a URL inside this site's uploads, or '' (also blocks ../ tricks and non-PDFs).
	 *
	 * @param string $url PDF URL.
	 * @return string
	 */
	private static function local_path( $url ) {
		$uploads = wp_get_upload_dir();
		$base    = set_url_scheme( $uploads['baseurl'], 'https' );
		$url     = set_url_scheme( $url, 'https' );
		if ( 0 !== strpos( $url, $base ) ) {
			return '';
		}
		$path = realpath( $uploads['basedir'] . rawurldecode( substr( $url, strlen( $base ) ) ) );
		$root = realpath( $uploads['basedir'] );
		if ( ! $path || ! $root || 0 !== strpos( $path, $root ) || ! is_file( $path ) || 'pdf' !== strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) ) {
			return '';
		}
		return $path;
	}

	/**
	 * Send the file as an attachment and stop.
	 *
	 * @param string $path Verified local PDF path.
	 */
	private static function stream( $path ) {
		$name  = basename( $path );
		// File names may be Persian: ASCII fallback + the UTF-8 name (RFC 5987).
		$ascii = preg_replace( '/[^A-Za-z0-9._-]/', '', $name );
		$ascii = preg_match( '/[A-Za-z0-9]/', pathinfo( $ascii, PATHINFO_FILENAME ) ) ? $ascii : 'document.pdf';

		nocache_headers();
		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: attachment; filename="' . $ascii . '"; filename*=UTF-8\'\'' . rawurlencode( $name ) );
		header( 'Content-Length: ' . filesize( $path ) );
		header( 'X-Content-Type-Options: nosniff' );
		readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- streaming a verified local file.
		exit;
	}
}
