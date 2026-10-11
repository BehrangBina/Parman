<?php
/**
 * LOCAL ONLY. Adds sample news stories (title starts with "[نمونه]", meta _imp_sample) so the
 * news page layout can be previewed; reuses the existing stories' images.
 *
 *   docker cp tools/seed-sample-news.php local-wordpress-703-wordpress-1:/tmp/
 *   docker exec local-wordpress-703-wordpress-1 php /tmp/seed-sample-news.php [count]
 *   docker exec local-wordpress-703-wordpress-1 php /tmp/seed-sample-news.php delete
 */

require '/var/www/html/wp-load.php';

if ( 'production' === wp_get_environment_type() ) {
	exit( "Refusing to run on production.\n" );
}

$samples = get_posts( array( 'post_type' => 'post', 'meta_key' => '_imp_sample', 'numberposts' => -1, 'post_status' => 'any', 'fields' => 'ids' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
if ( isset( $argv[1] ) && 'delete' === $argv[1] ) {
	foreach ( $samples as $id ) {
		wp_delete_post( $id, true );
	}
	exit( 'deleted ' . count( $samples ) . "\n" );
}

$category = get_category_by_slug( IMP\Core\Config::get( 'categories.news' ) );
if ( ! $category ) {
	exit( "News category not found.\n" );
}
$images = array_values( array_filter( array_map( 'get_post_thumbnail_id', get_posts( array( 'category' => $category->term_id, 'numberposts' => 10, 'fields' => 'ids' ) ) ) ) );
$count  = isset( $argv[1] ) ? max( 1, (int) $argv[1] ) : 10;

for ( $i = 1; $i <= $count; $i++ ) {
	$id = wp_insert_post(
		array(
			'post_title'    => '[نمونه] طرح جدید توسعه زیرساخت‌های حمل‌ونقل تهران رونمایی شد ' . $i,
			'post_content'  => '<!-- wp:paragraph --><p>پادشاهی ایرانیان چه می‌خواهد؟ از شکوه دیروز تا افق آینده ایران. این برنامه در رسانه میهن TV، فراتر از یک گفتگوی سیاسی است.</p><!-- /wp:paragraph -->',
			'post_status'   => 'publish',
			'post_category' => array( $category->term_id ),
			'post_date'     => gmdate( 'Y-m-d H:i:s', strtotime( '-' . ( 30 + $i ) . ' days' ) ),
		)
	);
	update_post_meta( $id, '_imp_sample', 1 );
	if ( $images ) {
		set_post_thumbnail( $id, $images[ $i % count( $images ) ] );
	}
}
echo 'added ' . $count . ' (already had ' . count( $samples ) . ")\n";
