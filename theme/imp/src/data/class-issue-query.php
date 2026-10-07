<?php
/**
 * Magazine issues for the Nashriye page.
 *
 * @package IMP
 */

namespace IMP\Data;

use IMP\Admin\Issue_Post_Type;
use IMP\Services\Jalali_Date;
use IMP\Services\Pdf_Download;
use IMP\Services\Pdf_Locator;

defined( 'ABSPATH' ) || exit;

/**
 * All published issues as plain arrays, ready for the issue-card component.
 */
final class Issue_Query {

	/**
	 * Issues in Figma order (۱ above ۲, i.e. oldest first — filter `imp_issue_order` to flip).
	 *
	 * @return array[] { title, description, year, day_month, date_iso, pdf, download }
	 */
	public static function all() {
		$posts = get_posts(
			array(
				'post_type'        => Issue_Post_Type::POST_TYPE,
				'numberposts'      => -1,
				'orderby'          => 'date',
				'order'            => apply_filters( 'imp_issue_order', 'ASC' ),
				'suppress_filters' => false,
			)
		);
		return array_map( array( __CLASS__, 'to_card' ), $posts );
	}

	/**
	 * Card data for one issue.
	 *
	 * @param \WP_Post $issue The issue.
	 * @return array
	 */
	private static function to_card( \WP_Post $issue ) {
		$date = Jalali_Date::parts( get_post_datetime( $issue ), true );
		$pdf  = Pdf_Locator::for_post( $issue );
		return array(
			'title'       => get_the_title( $issue ),
			'description' => has_excerpt( $issue ) ? get_the_excerpt( $issue ) : '',
			'year'        => $date['year'],
			'day_month'   => $date['day'] . ' ' . $date['month'],
			'date_iso'    => get_the_date( 'c', $issue ),
			'pdf'         => $pdf,
			'download'    => $pdf ? Pdf_Download::url( $issue->ID ) : '',
		);
	}
}
