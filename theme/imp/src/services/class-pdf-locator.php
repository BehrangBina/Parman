<?php
/**
 * Finds the PDF that belongs to a post (magazine issue or party-document page).
 *
 * @package IMP
 */

namespace IMP\Services;

use IMP\Admin\Pdf_Meta_Field;

defined( 'ABSPATH' ) || exit;

/**
 * Order of preference: the admin "PDF" field; for document pages, the first PDF in the page
 * content (e.g. the dFlip flipbook the live pages already use, so nothing has to be set up).
 */
final class Pdf_Locator {

	/**
	 * PDF URL for a published post, or '' if there is none.
	 *
	 * @param \WP_Post|null $post The post.
	 * @return string
	 */
	public static function for_post( $post ) {
		static $cache = array();
		if ( ! $post instanceof \WP_Post || 'publish' !== $post->post_status ) {
			return '';
		}
		if ( isset( $cache[ $post->ID ] ) ) {
			return $cache[ $post->ID ];
		}

		$url = (string) get_post_meta( $post->ID, Pdf_Meta_Field::META_KEY, true );
		if ( ! $url && 'page' === $post->post_type ) {
			$url = self::find_in_content( $post );
		}
		$cache[ $post->ID ] = $url ? esc_url_raw( $url ) : '';
		return $cache[ $post->ID ];
	}

	/**
	 * First PDF referenced by the rendered page content.
	 *
	 * @param \WP_Post $post The page.
	 * @return string
	 */
	private static function find_in_content( \WP_Post $post ) {
		$html = apply_filters( 'the_content', $post->post_content );
		// dFlip prints its options as JSON: "source":"https:\/\/…\/file.pdf" (escaped).
		if ( preg_match( '#"source"\s*:\s*"([^"]+?\.pdf)"#i', $html, $match ) ) {
			return (string) json_decode( '"' . $match[1] . '"' );
		}
		if ( preg_match( '#https?://[^\s"\'<>]+?\.pdf#i', $html, $match ) ) {
			return $match[0];
		}
		return '';
	}
}
