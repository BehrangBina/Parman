<?php
/**
 * LOCAL ONLY. Sets the people's photos ("اعضای حزب") from Titles/photos/ (cut out of the
 * Figma cards by tools/crop-person-photos.py). Matches file → person by the map below.
 *
 *   docker cp Titles/photos local-wordpress-703-wordpress-1:/tmp/person-photos
 *   docker cp tools/import-person-photos.php local-wordpress-703-wordpress-1:/tmp/
 *   docker exec local-wordpress-703-wordpress-1 php /tmp/import-person-photos.php [file-filter]
 */

require '/var/www/html/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

if ( 'local' !== wp_get_environment_type() ) {
	exit( "Local only.\n" );
}

// Figma export file → person (post title in the admin).
const IMP_PHOTO_MAP = array(
	'MitraSalar.png'     => 'میترا سالار',
	'Dr-SinaBabak.png'   => 'دکتر بابک سینا',
	'Masood-Zamani.png'  => 'دکتر مسعود زمانی',
	'Amin.png'           => 'مهندس امین قادری',
	'Bahman.png'         => 'دکتر بهمن شاهنگیان',
	'Chengizi.png'       => 'مهندس مینا چنگیزی',
	'Kourosh.png'        => 'کوین کوروش آریامهر',
	'Lesani.png'         => 'مهندس عباس لسانی',
	'Paygani.png'        => 'مهندس رضا پایگانی',
	'Yahya.png'          => 'دکتر یحیی نوروزی',
	'Aysan.png'          => 'آیسان قاسم پور',
	'OmidRokni.png'      => 'امید رکنی',
);

$filter = isset( $argv[1] ) ? strtolower( $argv[1] ) : '';
foreach ( IMP_PHOTO_MAP as $file => $name ) {
	if ( $filter && false === strpos( strtolower( $file ), $filter ) ) {
		continue;
	}
	$path = '/tmp/person-photos/' . $file;
	$post = get_posts( array( 'post_type' => IMP\Admin\Person_Post_Type::POST_TYPE, 'title' => $name, 'numberposts' => 1, 'post_status' => 'any' ) );
	if ( ! $post || ! is_readable( $path ) ) {
		echo "skip {$file}: " . ( $post ? 'no file' : 'no person' ) . "\n";
		continue;
	}
	$upload = wp_upload_bits( sanitize_file_name( $file ), null, (string) file_get_contents( $path ) );
	if ( ! empty( $upload['error'] ) ) {
		echo "fail {$file}: {$upload['error']}\n";
		continue;
	}
	$id = wp_insert_attachment( array( 'post_mime_type' => 'image/png', 'post_title' => $name, 'post_status' => 'inherit' ), $upload['file'], $post[0]->ID );
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	set_post_thumbnail( $post[0]->ID, $id );
	echo "ok {$file} → {$name}\n";
}
