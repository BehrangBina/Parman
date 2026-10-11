<?php
/**
 * Home page data (Figma Home: mobile 697:928, desktop 1112:5062).
 *
 * @package IMP
 */

namespace IMP\Controllers;

use IMP\Core\Config;
use IMP\Data\News_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Hero/document links and the latest news for the carousel (desktop shows the first 3).
 */
final class Home_Controller {

	const NEWS_COUNT = 6;

	/**
	 * Data for templates/pages/home.php.
	 *
	 * @return array
	 */
	public static function data() {
		return array(
			'links' => array(
				'about'        => Config::page_url( 'about' ),
				'media'        => Config::get( 'links.media' ),
				'constitution' => Config::page_url( 'constitution' ),
				'affidavit'    => Config::page_url( 'affidavit' ),
				'motto'        => Config::page_url( 'motto' ),
				'news'         => Config::page_url( 'news' ),
			),
			'news'  => News_Query::cards( News_Query::latest( self::NEWS_COUNT ) ),
		);
	}
}
