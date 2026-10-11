<?php
/**
 * Text of a party-document page (مرامنامه / اساسنامه / سوگندنامه) for the paged reader.
 *
 * @package IMP
 */

namespace IMP\Data;

defined( 'ABSPATH' ) || exit;

/**
 * The page's own content, minus flipbooks/shortcodes and scripts. A short first paragraph
 * ("نگارش دوم") becomes the edition label shown in the reader's corner.
 */
final class Document_Content {

	/** Below this many characters the page is treated as "no text yet" (no reader). */
	const MIN_TEXT_LENGTH = 150;

	/** A first paragraph up to this length is the edition label. */
	const MAX_LABEL_LENGTH = 40;

	/**
	 * Reader content for a page.
	 *
	 * @param \WP_Post $page The document page.
	 * @return array{label:string,html:string,has_text:bool}
	 */
	public static function for_page( \WP_Post $page ) {
		$html = apply_filters( 'the_content', strip_shortcodes( $page->post_content ) );
		$html = preg_replace( '#<script\b[^>]*>.*?</script>#is', '', $html );
		$html = preg_replace( '#<p>(\s|&nbsp;|<br\s*/?>)*</p>#i', '', $html );

		$label = '';
		if ( preg_match( '#^\s*<p[^>]*>(.*?)</p>#is', $html, $first ) && mb_strlen( trim( wp_strip_all_tags( $first[1] ) ) ) <= self::MAX_LABEL_LENGTH ) {
			$label = trim( wp_strip_all_tags( $first[1] ) );
			$html  = substr( $html, strlen( $first[0] ) );
		}

		return array(
			'label'    => $label,
			'html'     => trim( $html ),
			'has_text' => mb_strlen( trim( wp_strip_all_tags( $html ) ) ) > self::MIN_TEXT_LENGTH,
		);
	}
}
