<?php
/**
 * Plugin Name: IMP staging setup
 * Description: Test site only. Recreates the localhost test content (pages, news with images,
 *              magazine issues, party members, menus, membership form #3) with one button under
 *              Tools → IMP staging setup. Runs only while "IMP staging guard" is active.
 * Version:     1.0.0
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	function () {
		add_management_page( 'IMP staging setup', 'IMP staging setup', 'manage_options', 'imp-staging-setup', 'imp_staging_setup_page' );
	}
);
add_action( 'admin_post_imp_staging_setup', 'imp_staging_setup_run' );

/**
 * Things that must be in place before the content can be created.
 *
 * @return array<string,array{0:bool,1:string}>
 */
function imp_staging_setup_checks() {
	$theme = wp_get_theme();
	return array(
		'guard'  => array( defined( 'IMP_STAGING_GUARD' ), 'افزونه «IMP staging guard» فعال است (ایمیل خاموش، پنهان از گوگل)' ),
		'neve'   => array( wp_get_theme( 'neve' )->exists(), 'پوسته Neve نصب شده است' ),
		'imp'    => array( 'imp' === $theme->get_stylesheet(), 'پوسته IMP فعال است' ),
		'fluent' => array( defined( 'FLUENTFORM_VERSION' ), 'افزونه Fluent Forms فعال است' ),
	);
}

/**
 * Tools → IMP staging setup.
 */
function imp_staging_setup_page() {
	$checks = imp_staging_setup_checks();
	$ready  = ! in_array( false, array_column( $checks, 0 ), true );
	$log    = get_transient( 'imp_staging_setup_log' );
	echo '<div class="wrap" dir="rtl"><h1>IMP staging setup</h1><ul>';
	foreach ( $checks as $check ) {
		echo '<li>' . ( $check[0] ? '✅ ' : '❌ ' ) . esc_html( $check[1] ) . '</li>';
	}
	echo '</ul>';
	if ( $log ) {
		echo '<div class="notice notice-success"><pre style="white-space:pre-wrap">' . esc_html( implode( "\n", (array) $log ) ) . '</pre></div>';
		delete_transient( 'imp_staging_setup_log' );
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="imp_staging_setup">';
	wp_nonce_field( 'imp_staging_setup' );
	submit_button( 'ساخت محتوای آزمایشی', 'primary', 'submit', false, $ready ? array() : array( 'disabled' => 'disabled' ) );
	echo '<p class="description">بارها قابل اجراست: موارد موجود به‌روز می‌شوند، تکراری ساخته نمی‌شوند.</p></form></div>';
}

/**
 * Button handler.
 */
function imp_staging_setup_run() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	check_admin_referer( 'imp_staging_setup' );
	if ( in_array( false, array_column( imp_staging_setup_checks(), 0 ), true ) ) {
		wp_die( 'Requirements not met — see Tools → IMP staging setup.' );
	}
	$data = json_decode( (string) file_get_contents( __DIR__ . '/content/content.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$home = untrailingslashit( home_url() );
	$fix  = function ( $text ) use ( $home ) {
		return str_replace( '{{home}}', $home, (string) $text );
	};
	$log  = array();

	// Settings: pretty links (the theme routes by page slug), hidden from search engines.
	update_option( 'permalink_structure', '/%postname%/' );
	update_option( 'blog_public', '0' );

	foreach ( $data['categories'] as $cat ) {
		if ( ! term_exists( $cat['slug'], 'category' ) ) {
			wp_insert_term( $cat['name'], 'category', array( 'slug' => $cat['slug'] ) );
		}
	}

	$page_ids = array();
	foreach ( $data['pages'] as $page ) {
		$existing                  = get_page_by_path( $page['slug'] );
		$page_ids[ $page['slug'] ] = wp_insert_post(
			array(
				'ID'           => $existing ? $existing->ID : 0,
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_name'    => $page['slug'],
				'post_title'   => $page['title'],
				'post_content' => wp_slash( $fix( $page['content'] ) ),
				'menu_order'   => (int) $page['order'],
			)
		);
		if ( '' !== $page['pdf'] ) {
			update_post_meta( $page_ids[ $page['slug'] ], '_imp_pdf', esc_url_raw( $page['pdf'] ) );
		}
	}
	if ( isset( $page_ids[ $data['front_page'] ] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_ids[ $data['front_page'] ] );
	}
	$log[] = 'برگه‌ها: ' . count( $page_ids );

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$images = array();
	foreach ( $data['posts'] as $post ) {
		$existing = get_posts( array( 'post_type' => 'post', 'title' => $post['title'], 'numberposts' => 1, 'post_status' => 'any' ) );
		$id       = wp_insert_post(
			array(
				'ID'           => $existing ? $existing[0]->ID : 0,
				'post_status'  => 'publish',
				'post_title'   => $post['title'],
				'post_content' => wp_slash( $fix( $post['content'] ) ),
				'post_excerpt' => $post['excerpt'],
				'post_date'    => $post['date'],
			)
		);
		wp_set_post_categories(
			$id,
			array_filter(
				array_map(
					function ( $slug ) {
						$term = get_category_by_slug( $slug );
						return $term ? $term->term_id : 0;
					},
					$post['categories']
				)
			)
		);
		if ( $post['image'] && ! get_post_thumbnail_id( $id ) ) {
			if ( ! isset( $images[ $post['image'] ] ) ) {
				$upload = wp_upload_bits( preg_replace( '/^\d+-/', '', $post['image'] ), null, (string) file_get_contents( __DIR__ . '/content/images/' . $post['image'] ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				if ( empty( $upload['error'] ) ) {
					$attachment = wp_insert_attachment( array( 'post_mime_type' => wp_check_filetype( $upload['file'] )['type'], 'post_title' => $post['title'], 'post_status' => 'inherit' ), $upload['file'], $id );
					wp_update_attachment_metadata( $attachment, wp_generate_attachment_metadata( $attachment, $upload['file'] ) );
					$images[ $post['image'] ] = $attachment;
				}
			}
			if ( isset( $images[ $post['image'] ] ) ) {
				set_post_thumbnail( $id, $images[ $post['image'] ] );
			}
		}
	}
	$log[] = 'خبرها و بیانیه‌ها: ' . count( $data['posts'] ) . ' (عکس: ' . count( $images ) . ')';

	foreach ( $data['issues'] as $issue ) {
		$existing = get_posts( array( 'post_type' => 'imp_issue', 'title' => $issue['title'], 'numberposts' => 1, 'post_status' => 'any' ) );
		$id       = wp_insert_post(
			array(
				'ID'           => $existing ? $existing[0]->ID : 0,
				'post_type'    => 'imp_issue',
				'post_status'  => 'publish',
				'post_title'   => $issue['title'],
				'post_excerpt' => $issue['excerpt'],
				'post_date'    => $issue['date'],
			)
		);
		update_post_meta( $id, '_imp_pdf', esc_url_raw( $issue['pdf'] ) );
	}
	$log[] = 'شماره‌های نشریه: ' . count( $data['issues'] );

	$taxonomy = IMP\Admin\Person_Post_Type::TAXONOMY;
	foreach ( $data['groups'] as $group ) {
		$term = term_exists( $group['name'], $taxonomy );
		$term = $term ? $term : wp_insert_term( $group['name'], $taxonomy );
		update_term_meta( (int) $term['term_id'], IMP\Admin\Person_Post_Type::TERM_ORDER, (int) $group['order'] );
		update_term_meta( (int) $term['term_id'], IMP\Admin\Person_Post_Type::TERM_MOBILE, sanitize_key( $group['mobile'] ) );
	}
	foreach ( $data['people'] as $person ) {
		$existing = get_posts( array( 'post_type' => IMP\Admin\Person_Post_Type::POST_TYPE, 'title' => $person['name'], 'numberposts' => 1, 'post_status' => 'any' ) );
		$id       = wp_insert_post(
			array(
				'ID'          => $existing ? $existing[0]->ID : 0,
				'post_type'   => IMP\Admin\Person_Post_Type::POST_TYPE,
				'post_status' => 'publish',
				'post_title'  => $person['name'],
				'menu_order'  => (int) $person['order'],
			)
		);
		foreach ( IMP\Admin\Person_Post_Type::META_FIELDS as $key => $meta_key ) {
			update_post_meta( $id, $meta_key, sanitize_text_field( $person['meta'][ $key ] ?? '' ) );
		}
		wp_set_object_terms( $id, $person['groups'], $taxonomy );
	}
	$log[] = 'اعضای حزب: ' . count( $data['people'] );

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	foreach ( $data['menus'] as $menu ) {
		$existing = wp_get_nav_menu_object( $menu['name'] );
		$menu_id  = $existing ? $existing->term_id : wp_create_nav_menu( $menu['name'] );
		foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $old ) {
			wp_delete_post( $old->ID, true );
		}
		$ids = array();
		foreach ( $menu['items'] as $i => $item ) {
			$args = array(
				'menu-item-title'     => $item['title'],
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $item['parent'] >= 0 && isset( $ids[ $item['parent'] ] ) ? $ids[ $item['parent'] ] : 0,
				'menu-item-position'  => $i + 1,
			);
			if ( 'post_type' === $item['type'] && isset( $page_ids[ $item['ref'] ] ) ) {
				$args += array( 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids[ $item['ref'] ] );
			} elseif ( 'taxonomy' === $item['type'] && get_category_by_slug( $item['ref'] ) ) {
				$args += array( 'menu-item-type' => 'taxonomy', 'menu-item-object' => 'category', 'menu-item-object-id' => get_category_by_slug( $item['ref'] )->term_id );
			} else {
				$args += array( 'menu-item-type' => 'custom', 'menu-item-url' => $fix( $item['url'] ? $item['url'] : '#' ) );
			}
			$ids[ $i ] = wp_update_nav_menu_item( $menu_id, 0, $args );
		}
		if ( $menu['location'] ) {
			$locations[ $menu['location'] ] = $menu_id;
		}
		if ( 'primary' === $menu['location'] ) {
			$locations['imp-mobile'] = $menu_id; // the phone menu uses the same items
		}
	}
	set_theme_mod( 'nav_menu_locations', $locations );
	$log[] = 'منوها: ' . count( $data['menus'] );

	if ( $data['form'] ) {
		global $wpdb;
		$forms = $wpdb->prefix . 'fluentform_forms';
		$metas = $wpdb->prefix . 'fluentform_form_meta';
		$id    = (int) $data['form']['id'];
		$now   = current_time( 'mysql' );
		$row   = array( 'title' => $data['form']['title'], 'status' => 'published', 'form_fields' => $data['form']['fields'], 'has_payment' => 0, 'type' => 'form', 'updated_at' => $now );
		if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$forms} WHERE id = %d", $id ) ) ) { // phpcs:ignore WordPress.DB
			$wpdb->update( $forms, $row, array( 'id' => $id ) ); // phpcs:ignore WordPress.DB
		} else {
			$wpdb->insert( $forms, $row + array( 'id' => $id, 'created_by' => get_current_user_id(), 'created_at' => $now ) ); // phpcs:ignore WordPress.DB
		}
		$wpdb->delete( $metas, array( 'form_id' => $id ) ); // phpcs:ignore WordPress.DB
		foreach ( $data['form']['metas'] as $meta ) {
			$wpdb->insert( $metas, array( 'form_id' => $id, 'meta_key' => $meta['key'], 'value' => $fix( $meta['value'] ) ) ); // phpcs:ignore WordPress.DB
		}
		$log[] = 'فرم هموندی: #' . $id;
	}

	// Writes the .htaccess rules too (needs misc.php, which admin-post.php does not load).
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	flush_rewrite_rules( true );
	set_transient( 'imp_staging_setup_log', $log, 300 );
	wp_safe_redirect( admin_url( 'tools.php?page=imp-staging-setup' ) );
	exit;
}
