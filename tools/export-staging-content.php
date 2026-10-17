<?php
/**
 * LOCAL ONLY. Packs the localhost content (pages, posts + images, magazine issues, people,
 * menus, membership form #3) into tools/staging/imp-staging-setup/content/, so the
 * "IMP staging setup" plugin can recreate it on the test site with one button.
 *
 *   docker cp tools/export-staging-content.php local-wordpress-703-wordpress-1:/tmp/
 *   docker exec local-wordpress-703-wordpress-1 php /tmp/export-staging-content.php
 *   docker cp local-wordpress-703-wordpress-1:/tmp/imp-content/. tools/staging/imp-staging-setup/content/
 */

require '/var/www/html/wp-load.php';

if ( 'local' !== wp_get_environment_type() ) {
	exit( "Local only.\n" );
}

use IMP\Admin\Person_Post_Type as People;

global $wpdb;
$out  = '/tmp/imp-content';
$home = untrailingslashit( home_url() );
wp_mkdir_p( $out . '/images' );
array_map( 'unlink', glob( $out . '/images/*' ) );

$relative = function ( $text ) use ( $home ) {
	// Also the JSON-escaped form ("http:\/\/…") used inside Fluent Forms settings.
	return str_replace( array( $home, str_replace( '/', '\/', $home ) ), array( '{{home}}', '{{home}}' ), (string) $text );
};

$content = array(
	'pages'      => array(),
	'categories' => array(),
	'posts'      => array(),
	'issues'     => array(),
	'groups'     => array(),
	'people'     => array(),
	'menus'      => array(),
	'form'       => null,
	'front_page' => urldecode( get_post_field( 'post_name', (int) get_option( 'page_on_front' ) ) ),
);

foreach ( get_pages( array( 'post_status' => 'publish' ) ) as $page ) {
	if ( 'sample-page' === $page->post_name ) {
		continue;
	}
	$content['pages'][] = array(
		'slug'    => urldecode( $page->post_name ),
		'title'   => $page->post_title,
		'content' => $relative( $page->post_content ),
		'order'   => $page->menu_order,
		'pdf'     => (string) get_post_meta( $page->ID, '_imp_pdf', true ),
	);
}

foreach ( get_categories( array( 'hide_empty' => false ) ) as $cat ) {
	if ( 'uncategorized' !== $cat->slug ) {
		$content['categories'][] = array( 'slug' => urldecode( $cat->slug ), 'name' => $cat->name );
	}
}

foreach ( get_posts( array( 'numberposts' => -1, 'post_status' => 'publish' ) ) as $post ) {
	$image = '';
	$thumb = get_post_thumbnail_id( $post );
	if ( $thumb ) {
		$file  = get_attached_file( $thumb );
		$image = $thumb . '-' . basename( $file );
		copy( $file, $out . '/images/' . $image );
	}
	$content['posts'][] = array(
		'title'      => $post->post_title,
		'content'    => $relative( $post->post_content ),
		'excerpt'    => $post->post_excerpt,
		'date'       => $post->post_date,
		'categories' => wp_get_post_categories( $post->ID, array( 'fields' => 'slugs' ) ),
		'image'      => $image,
	);
}

foreach ( get_posts( array( 'post_type' => 'imp_issue', 'numberposts' => -1 ) ) as $issue ) {
	$pdf                 = (string) get_post_meta( $issue->ID, '_imp_pdf', true );
	$content['issues'][] = array(
		'title'   => $issue->post_title,
		'excerpt' => $issue->post_excerpt,
		'date'    => $issue->post_date,
		'pdf'     => false === strpos( $pdf, $home ) ? $pdf : '', // local test files do not exist there
	);
}

foreach ( get_terms( array( 'taxonomy' => People::TAXONOMY, 'hide_empty' => false ) ) as $term ) {
	$content['groups'][] = array(
		'name'   => $term->name,
		'order'  => (int) get_term_meta( $term->term_id, People::TERM_ORDER, true ),
		'mobile' => (string) get_term_meta( $term->term_id, People::TERM_MOBILE, true ),
	);
}
foreach ( get_posts( array( 'post_type' => People::POST_TYPE, 'numberposts' => -1 ) ) as $person ) {
	$meta = array();
	foreach ( People::META_FIELDS as $key => $meta_key ) {
		$meta[ $key ] = (string) get_post_meta( $person->ID, $meta_key, true );
	}
	$content['people'][] = array(
		'name'   => $person->post_title,
		'order'  => $person->menu_order,
		'meta'   => $meta,
		'groups' => wp_get_object_terms( $person->ID, People::TAXONOMY, array( 'fields' => 'names' ) ),
	);
}

$locations = array_flip( get_nav_menu_locations() );
foreach ( wp_get_nav_menus() as $menu ) {
	$items = array();
	$index = array();
	foreach ( wp_get_nav_menu_items( $menu ) as $i => $item ) {
		$index[ $item->ID ] = $i;
		$ref                = '';
		if ( 'post_type' === $item->type ) {
			$ref = urldecode( get_post_field( 'post_name', $item->object_id ) );
		} elseif ( 'taxonomy' === $item->type ) {
			$term = get_term( (int) $item->object_id, $item->object );
			$ref  = $term && ! is_wp_error( $term ) ? urldecode( $term->slug ) : '';
		}
		$items[] = array(
			'title'  => $item->title,
			'type'   => $item->type,
			'object' => $item->object,
			'ref'    => $ref,
			'url'    => 'custom' === $item->type ? $relative( $item->url ) : '',
			'parent' => (int) $item->menu_item_parent,
		);
	}
	foreach ( $items as $i => $item ) {
		$items[ $i ]['parent'] = $item['parent'] ? $index[ $item['parent'] ] : -1;
	}
	$content['menus'][] = array(
		'name'     => $menu->name,
		'location' => isset( $locations[ $menu->term_id ] ) ? $locations[ $menu->term_id ] : '',
		'items'    => $items,
	);
}

$form = $wpdb->get_row( "SELECT title, form_fields FROM {$wpdb->prefix}fluentform_forms WHERE id = 3" );
if ( $form ) {
	$content['form'] = array(
		'id'     => 3,
		'title'  => $form->title,
		'fields' => $form->form_fields,
		'metas'  => array_map(
			function ( $row ) use ( $relative ) {
				return array( 'key' => $row->meta_key, 'value' => $relative( $row->value ) );
			},
			$wpdb->get_results( "SELECT meta_key, value FROM {$wpdb->prefix}fluentform_form_meta WHERE form_id = 3" )
		),
	);
}

file_put_contents( $out . '/content.json', wp_json_encode( $content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) );
printf( "pages %d, posts %d, issues %d, people %d, menus %d, form %s, images %d\n", count( $content['pages'] ), count( $content['posts'] ), count( $content['issues'] ), count( $content['people'] ), count( $content['menus'] ), $content['form'] ? 'yes' : 'no', count( glob( $out . '/images/*' ) ) );
