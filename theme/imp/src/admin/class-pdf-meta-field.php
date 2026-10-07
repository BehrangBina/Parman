<?php
/**
 * One reusable "PDF file" box in the editor, for magazine issues and party-document pages.
 *
 * @package IMP
 */

namespace IMP\Admin;

use IMP\Core\Config;

defined( 'ABSPATH' ) || exit;

/**
 * Adds a URL field with a "pick from Media Library" button and saves it as post meta.
 */
final class Pdf_Meta_Field {

	const META_KEY = '_imp_pdf';
	const NONCE    = 'imp_pdf_nonce';

	/**
	 * Hook into WordPress.
	 */
	public static function register() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_box' ), 10, 2 );
		add_action( 'save_post', array( __CLASS__, 'save' ) );
	}

	/**
	 * Show the box on magazine issues and on the configured document pages only.
	 *
	 * @param string   $post_type Post type of the edited post.
	 * @param \WP_Post $post      The post.
	 */
	public static function add_box( $post_type, $post ) {
		$is_issue    = 'imp_issue' === $post_type;
		$is_document = 'page' === $post_type && 'document' === ( Config::get( 'routes' )[ urldecode( $post->post_name ) ] ?? '' );
		if ( ! $is_issue && ! $is_document ) {
			return;
		}
		add_meta_box(
			'imp-pdf',
			$is_issue ? 'فایل PDF نشریه' : 'فایل PDF سند',
			array( __CLASS__, 'render' ),
			$post_type,
			$is_issue ? 'normal' : 'side',
			'high',
			array( 'is_issue' => $is_issue )
		);
	}

	/**
	 * Box markup.
	 *
	 * @param \WP_Post $post The post.
	 * @param array    $box  Meta box data (args.is_issue).
	 */
	public static function render( $post, $box ) {
		wp_enqueue_media();
		wp_enqueue_script( 'imp-admin-pdf-field', IMP_URI . '/assets/js/admin/pdf-field.js', array( 'media-editor' ), IMP_VERSION, true );
		wp_nonce_field( self::NONCE, self::NONCE );
		get_template_part(
			'templates/admin/pdf-field',
			null,
			array(
				'url'      => (string) get_post_meta( $post->ID, self::META_KEY, true ),
				'is_issue' => ! empty( $box['args']['is_issue'] ),
			)
		);
	}

	/**
	 * Save the field (nonce + capability checked; only http(s) URLs are stored).
	 *
	 * @param int $post_id Post ID.
	 */
	public static function save( $post_id ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE ] ) ), self::NONCE ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		$url = isset( $_POST['imp_pdf'] ) ? esc_url_raw( wp_unslash( $_POST['imp_pdf'] ), array( 'http', 'https' ) ) : '';
		update_post_meta( $post_id, self::META_KEY, $url );
	}
}
