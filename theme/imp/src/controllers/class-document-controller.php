<?php
/**
 * Party-document page data — مرامنامه / اساسنامه / سوگندنامه
 * (Figma Overlay-Maramname 697:1066, Read-Maramname 805:6354, Maram-01/02 805:7201 / 813:7420;
 * desktop Maramname 1181:6630 and *-Content frames).
 *
 * @package IMP
 */

namespace IMP\Controllers;

use IMP\Data\Document_Content;
use IMP\Services\Pdf_Download;
use IMP\Services\Pdf_Locator;

defined( 'ABSPATH' ) || exit;

/**
 * The intro screen (download, PDF card, بخوانید) and the paged reader built from the page text.
 */
final class Document_Controller {

	/**
	 * Data for templates/pages/document.php.
	 *
	 * @return array
	 * @throws \UnexpectedValueException When the route has no page (logged by the router).
	 */
	public static function data() {
		$page = get_queried_object();
		if ( ! $page instanceof \WP_Post ) {
			throw new \UnexpectedValueException( 'Document route without a page object' );
		}
		$pdf = Pdf_Locator::for_post( $page );
		return array(
			'title'     => get_the_title( $page ),
			'pdf'       => $pdf,
			'download'  => $pdf ? Pdf_Download::url( $page->ID ) : '',
			'thumbnail' => has_post_thumbnail( $page ) ? get_the_post_thumbnail( $page, 'medium', array( 'alt' => '' ) ) : '',
			'text'      => Document_Content::for_page( $page ),
			'back_url'  => wp_get_referer() ? wp_get_referer() : home_url( '/' ),
			'slogan'    => apply_filters( 'imp_document_slogan', 'شکوه دیروز، اقتدار فردا', $page ),
		);
	}
}
