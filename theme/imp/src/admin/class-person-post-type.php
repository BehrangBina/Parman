<?php
/**
 * "اعضای حزب" (party members) in the admin, shown on the About page
 * (Figma: mobile About + Dabir/Sokhangoo overlays, desktop About 1153:292).
 *
 * @package IMP
 */

namespace IMP\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * A person: title = name, featured image = photo, plus role and Instagram / X links.
 * Order with "Order" (page attributes). Each person is in one or more groups
 * ("دبیران حزب", "سخنگویان حزب", …); a group has its own order and phone layout.
 */
final class Person_Post_Type {

	const POST_TYPE   = 'imp_person';
	const TAXONOMY    = 'imp_person_group';
	const NONCE       = 'imp_person_nonce';
	const META_FIELDS = array(
		'role'      => '_imp_role',
		'instagram' => '_imp_instagram',
		'x'         => '_imp_x',
	);
	const TERM_ORDER  = 'imp_order';
	const TERM_MOBILE = 'imp_mobile_style';
	const MOBILE_STYLES = array(
		'carousel' => 'کارت‌های کوچک کشویی (با «مشاهده همه»)',
		'circles'  => 'عکس‌های گرد بزرگ',
	);

	/**
	 * Hook into WordPress.
	 */
	public static function register() {
		add_action( 'init', array( __CLASS__, 'register_types' ) );
		add_action( 'add_meta_boxes_' . self::POST_TYPE, array( __CLASS__, 'add_box' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column' ), 10, 2 );
		add_action( self::TAXONOMY . '_add_form_fields', array( __CLASS__, 'term_fields_add' ) );
		add_action( self::TAXONOMY . '_edit_form_fields', array( __CLASS__, 'term_fields_edit' ) );
		add_action( 'created_' . self::TAXONOMY, array( __CLASS__, 'save_term' ) );
		add_action( 'edited_' . self::TAXONOMY, array( __CLASS__, 'save_term' ) );
	}

	/**
	 * Post type and group taxonomy (both admin-only: they are listed on the About page).
	 */
	public static function register_types() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'        => array(
					'name'               => 'اعضای حزب',
					'singular_name'      => 'عضو',
					'add_new'            => 'افزودن عضو',
					'add_new_item'       => 'افزودن عضو جدید',
					'edit_item'          => 'ویرایش عضو',
					'all_items'          => 'همه اعضا',
					'featured_image'     => 'عکس',
					'set_featured_image' => 'انتخاب عکس',
					'enter_title_here'   => 'نام و عنوان (مثلاً دکتر بابک سینا)',
				),
				'public'        => false,
				'show_ui'       => true,
				'show_in_rest'  => true,
				'menu_icon'     => 'dashicons-groups',
				'menu_position' => 22,
				'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
			)
		);
		register_taxonomy(
			self::TAXONOMY,
			self::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => 'گروه‌ها',
					'singular_name' => 'گروه',
					'add_new_item'  => 'افزودن گروه',
					'edit_item'     => 'ویرایش گروه',
				),
				'public'            => false,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'hierarchical'      => true, // checkboxes in the editor
			)
		);
	}

	/**
	 * Role and social links box.
	 */
	public static function add_box() {
		add_meta_box( 'imp-person', 'مشخصات', array( __CLASS__, 'render' ), self::POST_TYPE, 'normal', 'high' );
	}

	/**
	 * Box markup.
	 *
	 * @param \WP_Post $post The person.
	 */
	public static function render( $post ) {
		$values = array();
		foreach ( self::META_FIELDS as $key => $meta ) {
			$values[ $key ] = (string) get_post_meta( $post->ID, $meta, true );
		}
		get_template_part( 'templates/admin/person-fields', null, array( 'values' => $values, 'nonce' => self::NONCE ) );
	}

	/**
	 * Save role and links. Links may be "#" (placeholder) or a full URL.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function save( $post_id ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_key( $_POST[ self::NONCE ] ), self::NONCE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( self::META_FIELDS as $key => $meta ) {
			$raw   = isset( $_POST[ 'imp_person_' . $key ] ) ? trim( wp_unslash( $_POST[ 'imp_person_' . $key ] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised below.
			$value = 'role' === $key ? sanitize_text_field( $raw ) : self::sanitize_link( $raw );
			if ( '' === $value ) {
				delete_post_meta( $post_id, $meta );
			} else {
				update_post_meta( $post_id, $meta, $value );
			}
		}
	}

	/**
	 * "#" stays "#" (the design's placeholder); anything else must be a valid URL.
	 *
	 * @param string $value Raw input.
	 * @return string
	 */
	public static function sanitize_link( $value ) {
		return '#' === $value ? '#' : esc_url_raw( $value );
	}

	/**
	 * Admin list: photo and role columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function columns( $columns ) {
		return array_merge(
			array_slice( $columns, 0, 1 ),
			array( 'imp_photo' => 'عکس' ),
			array_slice( $columns, 1, 1 ),
			array( 'imp_role' => 'سمت' ),
			array_slice( $columns, 2 )
		);
	}

	/**
	 * Column values.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	public static function column( $column, $post_id ) {
		if ( 'imp_photo' === $column ) {
			$photo = get_the_post_thumbnail( $post_id, array( 48, 48 ) );
			echo $photo ? $photo : '—'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core markup.
		} elseif ( 'imp_role' === $column ) {
			echo esc_html( (string) get_post_meta( $post_id, self::META_FIELDS['role'], true ) );
		}
	}

	/**
	 * Group fields on "Add group".
	 */
	public static function term_fields_add() {
		get_template_part( 'templates/admin/person-group-fields', null, array( 'order' => 0, 'mobile' => 'carousel', 'styles' => self::MOBILE_STYLES, 'table' => false ) );
	}

	/**
	 * Group fields on "Edit group".
	 *
	 * @param \WP_Term $term The group.
	 */
	public static function term_fields_edit( $term ) {
		get_template_part(
			'templates/admin/person-group-fields',
			null,
			array(
				'order'  => (int) get_term_meta( $term->term_id, self::TERM_ORDER, true ),
				'mobile' => get_term_meta( $term->term_id, self::TERM_MOBILE, true ) ? get_term_meta( $term->term_id, self::TERM_MOBILE, true ) : 'carousel',
				'styles' => self::MOBILE_STYLES,
				'table'  => true,
			)
		);
	}

	/**
	 * Save group order and phone layout (core already checked the term-form nonce).
	 *
	 * @param int $term_id Term ID.
	 */
	public static function save_term( $term_id ) {
		if ( ! current_user_can( 'manage_categories' ) ) {
			return;
		}
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified by core's term form.
		if ( isset( $_POST['imp_group_order'] ) ) {
			update_term_meta( $term_id, self::TERM_ORDER, (int) $_POST['imp_group_order'] );
		}
		if ( isset( $_POST['imp_group_mobile'] ) && isset( self::MOBILE_STYLES[ sanitize_key( $_POST['imp_group_mobile'] ) ] ) ) {
			update_term_meta( $term_id, self::TERM_MOBILE, sanitize_key( $_POST['imp_group_mobile'] ) );
		}
		// phpcs:enable
	}
}
