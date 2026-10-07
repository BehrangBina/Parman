<?php
/**
 * Page links for list pages.
 *
 * @package IMP
 */

namespace IMP\Controllers;

defined( 'ABSPATH' ) || exit;

/**
 * Same "‹ 1 2 3 ›" links for the news and statements lists.
 */
final class Pagination {

	/**
	 * Links HTML (core paginate_links output), or '' when there is only one page.
	 *
	 * @param int $current Current page.
	 * @param int $total   Total pages.
	 * @return string
	 */
	public static function links( $current, $total ) {
		if ( $total < 2 ) {
			return '';
		}
		return (string) paginate_links(
			array(
				'current'   => $current,
				'total'     => $total,
				'mid_size'  => 1,
				'prev_text' => '‹',
				'next_text' => '›',
			)
		);
	}
}
