<?php
/**
 * One news story or statement (Figma desktop Full-News 1215:6609 — built from the overview,
 * no detailed frame yet).
 *
 * @package IMP
 */

namespace IMP\Controllers;

use IMP\Core\Config;
use IMP\Services\Jalali_Date;

defined( 'ABSPATH' ) || exit;

/**
 * Picture, date, headline and the post text; the section title follows the post's category.
 */
final class Article_Controller {

	/**
	 * Data for templates/pages/article.php.
	 *
	 * @return array
	 * @throws \RuntimeException When there is no post (the router then shows Neve's own page).
	 */
	public static function data() {
		$post = get_queried_object();
		if ( ! $post instanceof \WP_Post ) {
			throw new \RuntimeException( 'No post' );
		}
		$is_statement = has_category( Config::get( 'categories.statements' ), $post );
		return array(
			'section'   => $is_statement ? _x( 'بیانیه‌ها', 'article', 'imp' ) : _x( 'آخرین اخبار', 'article', 'imp' ),
			'title'     => get_the_title( $post ),
			'date_iso'  => get_the_date( 'c', $post ),
			'date_text' => implode( ' ', Jalali_Date::post_gregorian_fa( $post ) ),
			'image'     => has_post_thumbnail( $post ) ? get_the_post_thumbnail( $post, 'large', array( 'alt' => '' ) ) : '',
			'html'      => (string) apply_filters( 'the_content', (string) $post->post_content ),
		);
	}
}
