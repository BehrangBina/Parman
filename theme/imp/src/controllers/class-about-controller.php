<?php
/**
 * About page data (Figma: mobile About + Dabir/Sokhangoo overlays, desktop About 1153:292).
 *
 * @package IMP
 */

namespace IMP\Controllers;

use IMP\Core\Config;
use IMP\Data\People_Query;

defined( 'ABSPATH' ) || exit;

/**
 * The page's own text (edited as usual) and the party members managed under "اعضای حزب".
 */
final class About_Controller {

	/**
	 * Data for templates/pages/about.php.
	 *
	 * @return array
	 */
	public static function data() {
		$page_id = get_queried_object_id();
		return array(
			'title'       => get_the_title( $page_id ),
			'intro_html'  => (string) apply_filters( 'the_content', (string) get_post_field( 'post_content', $page_id ) ),
			'groups'      => People_Query::groups(),
			'contact_url' => Config::page_url( 'contact' ),
			'socials'     => Config::get( 'socials' ),
		);
	}
}
