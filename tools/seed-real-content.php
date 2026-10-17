<?php
/**
 * LOCAL ONLY. Puts real texts (copied from the live site's public pages) into local pages that
 * still had sample text:
 *  - membership-fee ("درباره هزینه هموندی" popup): the fee section of the live
 *    /membership-form-guide/ page.
 *
 *   docker cp tools/seed-real-content.php local-wordpress-703-wordpress-1:/tmp/
 *   docker exec local-wordpress-703-wordpress-1 php /tmp/seed-real-content.php
 *
 * On the live site the same text must go into a page with the slug "membership-fee"
 * (see docs/DEPLOYMENT.md) — today it exists there only inside the membership guide.
 */

require '/var/www/html/wp-load.php';

if ( 'production' === wp_get_environment_type() ) {
	exit( "Refusing to run on production.\n" );
}

$fee = <<<'HTML'
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">حق هموندی</h3><!-- /wp:heading -->

<!-- wp:paragraph --><p>برای تسهیل مشارکت همه هم‌میهنان، گونه‌های مختلف هموندی به شرح زیر در نظر گرفته شده است:</p><!-- /wp:paragraph -->

<!-- wp:list --><ul class="wp-block-list"><li>حق هموندی ماهانه: ۹٫۹۹ دلار آمریکا</li><li>حق هموندی سالانه: ۹۹ دلار آمریکا</li></ul><!-- /wp:list -->

<!-- wp:paragraph --><p>پرداخت حق هموندی، جهت پشتیبانی از فعالیت‌ها، سازماندهی و پیشبرد اهداف پارمان پادشاهی ایرانیان، برای همه هموندان در نظر گرفته شده است.</p><!-- /wp:paragraph -->

<!-- wp:paragraph --><p><strong>تبصره:</strong> دانشجویان از پرداخت حق هموندی معاف می‌باشند.</p><!-- /wp:paragraph -->
HTML;

$page = get_page_by_path( 'membership-fee' );
$id   = wp_insert_post(
	array(
		'ID'           => $page ? $page->ID : 0,
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_name'    => 'membership-fee',
		'post_title'   => 'هزینه هموندی',
		'post_content' => wp_slash( $fee ),
	)
);
echo 'membership-fee: ' . ( $id ? 'updated' : 'failed' ) . "\n";
