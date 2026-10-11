<?php
/**
 * "نشریه ایرانگرا" magazine issues in the admin (Figma Nashriye 820:9504).
 *
 * @package IMP
 */

namespace IMP\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Each issue: title ("شماره ۱"), excerpt (short description), publish date (shown on the
 * card) and a PDF (Pdf_Meta_Field). Not public on its own — it is listed on the magazine page.
 */
final class Issue_Post_Type {

	const POST_TYPE = 'imp_issue';

	/**
	 * Hook into WordPress.
	 */
	public static function register() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
	}

	/**
	 * Register the post type.
	 */
	public static function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'        => array(
					'name'          => 'نشریه ایرانگرا',
					'singular_name' => 'شماره نشریه',
					'add_new'       => 'افزودن شماره',
					'add_new_item'  => 'افزودن شماره جدید',
					'edit_item'     => 'ویرایش شماره',
					'all_items'     => 'همه شماره‌ها',
				),
				'public'        => false,
				'show_ui'       => true,
				'show_in_rest'  => true,
				'menu_icon'     => 'dashicons-book-alt',
				'menu_position' => 21,
				'supports'      => array( 'title', 'excerpt', 'thumbnail' ),
			)
		);
	}
}
