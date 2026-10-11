<?php
/**
 * Party members for the About page, grouped and ordered as set in the admin.
 *
 * @package IMP
 */

namespace IMP\Data;

use IMP\Admin\Person_Post_Type;

defined( 'ABSPATH' ) || exit;

/**
 * Groups (by their "order") with their people (by "Order", then name). Empty groups are left out.
 */
final class People_Query {

	/**
	 * All groups with people.
	 *
	 * @return array[] Each array{id,slug,title,mobile,people:array[]}.
	 */
	public static function groups() {
		$terms = get_terms(
			array(
				'taxonomy'   => Person_Post_Type::TAXONOMY,
				'hide_empty' => true,
			)
		);
		if ( is_wp_error( $terms ) ) {
			\IMP\Core\Logger::error( 'People_Query::groups', 'get_terms failed', array( 'error' => $terms->get_error_message() ) );
			return array();
		}
		usort(
			$terms,
			function ( $a, $b ) {
				return (int) get_term_meta( $a->term_id, Person_Post_Type::TERM_ORDER, true ) <=> (int) get_term_meta( $b->term_id, Person_Post_Type::TERM_ORDER, true );
			}
		);

		$groups = array();
		foreach ( $terms as $term ) {
			$people = self::people( $term->term_id );
			if ( ! $people ) {
				continue;
			}
			$mobile   = (string) get_term_meta( $term->term_id, Person_Post_Type::TERM_MOBILE, true );
			$groups[] = array(
				'id'     => (int) $term->term_id,
				'slug'   => 'imp-group-' . (int) $term->term_id,
				'title'  => $term->name,
				'mobile' => isset( Person_Post_Type::MOBILE_STYLES[ $mobile ] ) ? $mobile : 'carousel',
				'people' => $people,
			);
		}
		return $groups;
	}

	/**
	 * Published people of one group.
	 *
	 * @param int $term_id Group ID.
	 * @return array[] Each array{name,role,photo,photo_alt,instagram,x}.
	 */
	private static function people( $term_id ) {
		$posts = get_posts(
			array(
				'post_type'      => Person_Post_Type::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- small admin-managed list.
					array(
						'taxonomy' => Person_Post_Type::TAXONOMY,
						'terms'    => $term_id,
					),
				),
			)
		);
		return array_map(
			function ( $post ) {
				$photo = get_post_thumbnail_id( $post );
				return array(
					'name'      => get_the_title( $post ),
					'role'      => (string) get_post_meta( $post->ID, Person_Post_Type::META_FIELDS['role'], true ),
					'photo'     => $photo ? (string) wp_get_attachment_image_url( $photo, 'medium' ) : '',
					'instagram' => (string) get_post_meta( $post->ID, Person_Post_Type::META_FIELDS['instagram'], true ),
					'x'         => (string) get_post_meta( $post->ID, Person_Post_Type::META_FIELDS['x'], true ),
				);
			},
			$posts
		);
	}
}
