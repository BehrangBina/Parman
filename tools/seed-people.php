<?php
/**
 * LOCAL ONLY. Fills the "اعضای حزب" admin section with the people and groups from the
 * Figma About designs (desktop 1153:292), so the About page can be tried on localhost.
 * Photos are left empty (the card shows the placeholder); social links are "#".
 * Also puts the Figma "درباره ما" text on the local about-us page when it has no text yet.
 *
 *   docker cp tools/seed-people.php local-wordpress-703-wordpress-1:/tmp/
 *   docker exec local-wordpress-703-wordpress-1 php /tmp/seed-people.php
 *
 * Safe to run again: existing people (same name) and groups are updated, not duplicated.
 */

require '/var/www/html/wp-load.php';

if ( 'production' === wp_get_environment_type() ) {
	exit( "Refusing to run on production.\n" );
}

use IMP\Admin\Person_Post_Type as People;

// Group title => [order, phone layout, [ [name, role], … ] ]
$groups = array(
	'دبیران حزب'             => array( 10, 'circles', array( array( 'دکتر بابک سینا', 'دبیر نخست' ), array( 'دکتر مسعود زمانی', 'دبیر دوم' ) ) ),
	'دبیرخانه و امور هموندی' => array( 20, 'carousel', array( array( 'میترا سالار', 'دبیرخانه' ) ) ),
	'سخنگویان حزب'           => array( 30, 'carousel', array( array( 'آیسان قاسم پور', 'سخنگو' ), array( 'امید رکنی', 'سخنگو' ) ) ),
	'شورای مدیریت'           => array(
		40,
		'carousel',
		array(
			array( 'کوین کوروش آریامهر', '' ),
			array( 'مهندس مینا چنگیزی', '' ),
			array( 'مهندس رضا پایگانی', '' ),
			array( 'دکتر یحیی نوروزی', '' ),
			array( 'دکتر بهمن شاهنگیان', '' ),
			array( 'مهندس عباس لسانی', '' ),
			array( 'مهندس امین قادری', '' ),
		),
	),
);

foreach ( $groups as $title => $group ) {
	list( $order, $mobile, $people ) = $group;
	$term = term_exists( $title, People::TAXONOMY );
	if ( ! $term ) {
		$term = wp_insert_term( $title, People::TAXONOMY );
	}
	$term_id = (int) $term['term_id'];
	update_term_meta( $term_id, People::TERM_ORDER, $order );
	update_term_meta( $term_id, People::TERM_MOBILE, $mobile );

	foreach ( $people as $index => $person ) {
		list( $name, $role ) = $person;
		$existing = get_posts( array( 'post_type' => People::POST_TYPE, 'title' => $name, 'post_status' => 'any', 'numberposts' => 1 ) );
		$id       = $existing ? $existing[0]->ID : 0;
		$id       = wp_insert_post(
			array(
				'ID'          => $id,
				'post_type'   => People::POST_TYPE,
				'post_title'  => $name,
				'post_status' => 'publish',
				'menu_order'  => $index + 1,
			)
		);
		update_post_meta( $id, People::META_FIELDS['role'], $role );
		update_post_meta( $id, People::META_FIELDS['instagram'], '#' );
		update_post_meta( $id, People::META_FIELDS['x'], '#' );
		wp_set_object_terms( $id, $term_id, People::TAXONOMY, true );
	}
	echo $title . ': ' . count( $people ) . "\n";
}

$about = get_page_by_path( 'about-us' );
if ( $about && '' === trim( wp_strip_all_tags( $about->post_content ) ) ) {
	$text = array(
		'<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">«پارمان(حزب) پادشاهی ایرانیان»</h3><!-- /wp:heading -->',
		'نهادی سیاسی و ملی است که بر پایه باور به اندیشه ایرانشهری، ایران‌گرایی و یگانگی تاریخی و جغرافیایی فلات ایران بنیان گذاشته شده است. این حزب با الهام از میراث سترگ ایران‌زمین و سنت دیرپای شاهنشاهی ایرانی می‌کوشد در راه آزادی، قانون‌مداری، سربلندی و آبادانی ایران گام بردارد و پیوندی استوار میان هویت تاریخی ملت ایران و آینده آن برقرار سازد.',
		'پارمان(حزب) پادشاهی ایرانیان بر پایه مرامنامه خود، پاسداری از یکپارچگی سرزمین ایران (تمامیت ارضی کشور) و نیز یگانگی تاریخی و تمدنی فلات ایران را از بنیادی‌ترین اصول اندیشه سیاسی خویش می‌داند. این حزب بر جایگاه زبان پارسی به‌عنوان زبان ملی و رشته پیونددهنده همه ایرانیان پای می‌فشارد و در عین حال گوناگونی فرهنگی، زبانی و آیینی مردمان ایران را بخشی ارزشمند از میراث و هویت ملی کشور می‌شمارد.',
		'این پارمان باور دارد که پادشاهی پارلمانی در چارچوب مردم‌سالاری و میهن‌سالاری می‌تواند بهترین سامان سیاسی برای پیوند دادن تاریخ و هویت ملی ایران با اراده آزاد ملت باشد؛ نظمی که در آن سرچشمه توان کشورداری از رأی مردم برمی‌خیزد و پادشاه نماد یگانگی، تداوم تاریخی و شکوه ایران است.',
		'پارمان(حزب) پادشاهی ایرانیان بر فرمانروایی قانون، جدایی نهاد دین از دولت و سیاست، پاسداری از آزادی‌های بنیادین شهروندان، برابری حقوقی زنان و مردان و پیشرفت علمی، اقتصادی و اجتماعی ایران پای می‌فشارد و این اصول را از پایه‌های سامان سیاسی آینده ایران می‌داند.',
		'این پارمان خود را در پیوند با میراث ملی و تاریخی دودمان پهلوی می‌داند و از جایگاه ملی رضاشاه دوم در تداوم نهاد پادشاهی و استواری پادشاهی پارلمانی در ایران پشتیبانی می‌کند.',
		'پارمان(حزب) پادشاهی ایرانیان می‌کوشد با سازمان‌دهی نیروهای ایران‌گرا، گسترش آگاهی ملی، آموزش اندیشه سیاسی مبتنی بر آزادی و قانون‌مداری و تقویت همبستگی ایرانیان در راه بازسازی و سربلندی ایران گام بردارد. فعالیت‌های این پارمان در چارچوب کنش‌های مدنی، فرهنگی، آموزشی و رسانه‌ای و با بهره‌گیری از شیوه‌های گوناگون و هماهنگ با مبارزان آزادی‌خواه درون کشور دنبال می‌شود.',
		'اعضای پارمان(حزب) پادشاهی ایرانیان در کشورهای گوناگون در آسیا، اروپا، آمریکا و اقیانوسیه حضور دارند و با همبستگی، میهن‌سالاری و احساس مسئولیت برای آینده‌ای آزاد، نیرومند و سرافراز برای ایران تلاش می‌کنند.',
	);
	$blocks = $text[0];
	foreach ( array_slice( $text, 1 ) as $paragraph ) {
		$blocks .= "\n\n<!-- wp:paragraph --><p>" . $paragraph . '</p><!-- /wp:paragraph -->';
	}
	wp_update_post( array( 'ID' => $about->ID, 'post_content' => $blocks ) );
	echo "about-us: text added\n";
}
