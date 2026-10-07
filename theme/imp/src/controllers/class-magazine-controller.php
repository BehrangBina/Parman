<?php
/**
 * Nashriye (magazine) page data (Figma Nashriye: mobile 820:9504, desktop 1215:4401).
 *
 * @package IMP
 */

namespace IMP\Controllers;

use IMP\Data\Issue_Query;

defined( 'ABSPATH' ) || exit;

/**
 * The page's own intro text (editable as usual) and the issue cards.
 */
final class Magazine_Controller {

	/**
	 * Data for templates/pages/magazine.php.
	 *
	 * @return array
	 */
	public static function data() {
		$page_id = get_queried_object_id();
		return array(
			'title'      => get_the_title( $page_id ),
			'intro_html' => (string) apply_filters( 'the_content', (string) get_post_field( 'post_content', $page_id ) ),
			'issues'     => Issue_Query::all(),
		);
	}
}
