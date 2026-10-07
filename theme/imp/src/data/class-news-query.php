<?php
/**
 * Party news posts (home carousel + news page).
 *
 * @package IMP
 */

namespace IMP\Data;

use IMP\Core\Config;
use IMP\Core\Logger;

defined( 'ABSPATH' ) || exit;

/**
 * Posts in the configured news category ("اخبار حزب"), newest first. Falls back to all posts
 * when the category is missing or empty, so a fresh site still shows something.
 */
final class News_Query {

	/**
	 * Latest news for the home carousel.
	 *
	 * @param int $count Number of posts.
	 * @return \WP_Query
	 */
	public static function latest( $count ) {
		return new \WP_Query(
			self::args(
				array(
					'posts_per_page' => $count,
					'no_found_rows'  => true,
				)
			)
		);
	}

	/**
	 * One page of the news list.
	 *
	 * @param int $page     Page number (1-based).
	 * @param int $per_page Posts per page.
	 * @return \WP_Query
	 */
	public static function page( $page, $per_page ) {
		return new \WP_Query(
			self::args(
				array(
					'posts_per_page' => $per_page,
					'paged'          => max( 1, (int) $page ),
				)
			)
		);
	}

	/**
	 * Card data for every post in a query (templates/components/news-card.php).
	 *
	 * @param \WP_Query $query Query to read.
	 * @return array[] { url, image, date_iso, date_text, title, excerpt }
	 */
	public static function cards( \WP_Query $query ) {
		return array_map(
			static function ( \WP_Post $post ) {
				return array(
					'url'       => get_permalink( $post ),
					'image'     => has_post_thumbnail( $post )
						? get_the_post_thumbnail( $post, 'medium_large', array( 'loading' => 'lazy', 'alt' => '' ) )
						: '',
					'date_iso'  => get_the_date( 'c', $post ),
					'date_text' => implode( ' ', \IMP\Services\Jalali_Date::post_gregorian_fa( $post ) ),
					'title'     => get_the_title( $post ),
					'excerpt'   => wp_trim_words( get_the_excerpt( $post ), 40, '…' ),
				);
			},
			$query->posts
		);
	}

	/**
	 * Base query args.
	 *
	 * @param array $args Overrides.
	 * @return array
	 */
	private static function args( array $args ) {
		$args     = wp_parse_args(
			$args,
			array(
				'post_type'           => 'post',
				'ignore_sticky_posts' => true,
			)
		);
		$slug     = apply_filters( 'imp_news_category', Config::get( 'categories.news' ) );
		$category = get_category_by_slug( $slug );
		if ( $category && $category->count > 0 ) {
			$args['cat'] = $category->term_id;
		} else {
			Logger::warning( 'News_Query::args', 'News category missing or empty, showing all posts', array( 'category' => $slug ) );
		}
		return $args;
	}
}
