<?php
/**
 * Akhbaar (news) page data (Figma Akhbaar: mobile 697:2647, desktop 1215:5585).
 *
 * @package IMP
 */

namespace IMP\Controllers;

use IMP\Data\News_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Party news with page links. Each page: 3 large stories, then a grid of 8 (Figma desktop
 * News 1215:5585); the phone shows them as one list.
 */
final class News_Controller {

	const PER_PAGE = 11;
	const FEATURED = 3;

	/**
	 * Data for templates/pages/news.php.
	 *
	 * @return array
	 */
	public static function data() {
		$page  = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		$query = News_Query::page( $page, self::PER_PAGE );
		$cards = News_Query::cards( $query );
		return array(
			'featured'   => array_slice( $cards, 0, self::FEATURED ),
			'more'       => array_slice( $cards, self::FEATURED ),
			'next_url'   => $page < (int) $query->max_num_pages ? get_pagenum_link( $page + 1 ) : '',
			'pagination' => Pagination::links( $page, (int) $query->max_num_pages ),
		);
	}
}
