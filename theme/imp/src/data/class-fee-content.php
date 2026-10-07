<?php
/**
 * Text of the "درباره هزینه هموندی" popup (Figma Overlay-MembershipFee 966:3055 / 1181:6254).
 *
 * @package IMP
 */

namespace IMP\Data;

use IMP\Core\Config;

defined( 'ABSPATH' ) || exit;

/**
 * The popup shows the content of the configured "membership_fee" page, so admins edit the fee
 * text in WordPress like any page. Empty string when that page does not exist yet.
 */
final class Fee_Content {

	/**
	 * Rendered page content, or ''.
	 *
	 * @return string
	 */
	public static function html() {
		$page = get_page_by_path( (string) Config::get( 'pages.membership_fee' ) );
		return $page ? (string) apply_filters( 'the_content', $page->post_content ) : '';
	}
}
