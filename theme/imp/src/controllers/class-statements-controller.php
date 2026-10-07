<?php
/**
 * Bayanie (statements) archive data (Figma Bayanie: mobile 1009:3003, desktop 1215:6745).
 *
 * @package IMP
 */

namespace IMP\Controllers;

use IMP\Services\Jalali_Date;
use IMP\Services\Statement_Title;

defined( 'ABSPATH' ) || exit;

/**
 * Uses WordPress's main query for the category, so new statements appear automatically.
 */
final class Statements_Controller {

	/**
	 * Data for templates/pages/statements.php.
	 *
	 * @return array
	 */
	public static function data() {
		global $wp_query;
		$cards = array_map(
			static function ( \WP_Post $post ) {
				list( $day_month, $year ) = Jalali_Date::post_gregorian_fa( $post );
				return array(
					'url'       => get_permalink( $post ),
					'subject'   => Statement_Title::subject( get_the_title( $post ) ),
					'day_month' => $day_month,
					'year'      => $year,
					'date_iso'  => get_the_date( 'c', $post ),
				);
			},
			$wp_query->posts
		);
		return array(
			'title'      => single_term_title( '', false ),
			'cards'      => $cards,
			'pagination' => Pagination::links( max( 1, (int) get_query_var( 'paged' ) ), (int) $wp_query->max_num_pages ),
		);
	}
}
