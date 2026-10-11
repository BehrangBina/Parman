<?php
/**
 * Hamyari (donate) page data (Figma Hamyari: mobile 697:2603, desktop 1181:5330).
 *
 * @package IMP
 */

namespace IMP\Controllers;

use IMP\Core\Config;
use IMP\Data\Fee_Content;

defined( 'ABSPATH' ) || exit;

/**
 * Links for the two sections and the membership-fee popup text.
 */
final class Donate_Controller {

	/**
	 * Data for templates/pages/donate.php.
	 *
	 * @return array
	 */
	public static function data() {
		return array(
			'title'               => get_the_title( get_queried_object_id() ),
			'membership_form_url' => Config::page_url( 'membership_form' ),
			'donorbox_membership' => Config::get( 'links.donorbox_membership' ),
			'donorbox_donation'   => Config::get( 'links.donorbox_donation' ),
			'fee_html'            => Fee_Content::html(),
		);
	}
}
