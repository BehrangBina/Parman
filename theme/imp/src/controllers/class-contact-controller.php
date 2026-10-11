<?php
/**
 * Contact page data (Figma Contact: mobile 820:11317, desktop 1225:9182).
 *
 * @package IMP
 */

namespace IMP\Controllers;

use IMP\Core\Config;

defined( 'ABSPATH' ) || exit;

/**
 * Title, social icons and the contact email (obfuscated against address harvesters).
 */
final class Contact_Controller {

	/**
	 * Data for templates/pages/contact.php.
	 *
	 * @return array
	 */
	public static function data() {
		return array(
			'title'   => get_the_title( get_queried_object_id() ),
			'socials' => (array) Config::get( 'socials', array() ),
			'email'   => antispambot( (string) Config::get( 'links.contact_email' ) ),
		);
	}
}
