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
 * Party news, 9 per page, with page links.
 */
final class News_Controller {

	const PER_PAGE = 9;

	/**
	 * Data for templates/pages/news.php.
	 *
	 * @return array
	 */
	public static function data() {
		$page  = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		$query = News_Query::page( $page, self::PER_PAGE );
		return array(
			'cards'      => News_Query::cards( $query ),
			'pagination' => Pagination::links( $page, (int) $query->max_num_pages ),
		);
	}
}
