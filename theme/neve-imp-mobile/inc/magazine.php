<?php
/**
 * "Nashriye" (Irangara magazine) — Figma frame 820:9504.
 *
 * Issues are a small admin section ("نشریه ایرانگرا" in wp-admin): title (e.g. "شماره ۱"),
 * excerpt (short description), publish date (shown as the issue date) and a PDF file.
 * The page intro text is the page's own content, so admins edit it as before.
 */

defined( 'ABSPATH' ) || exit;

const IMP_M_ISSUE_PDF = '_imp_issue_pdf';

add_action( 'init', function () {
	register_post_type( 'imp_issue', array(
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
	) );
} );

/* ---------- Admin: PDF field ---------- */

add_action( 'add_meta_boxes_imp_issue', function () {
	add_meta_box( 'imp-issue-pdf', 'فایل PDF نشریه', 'imp_m_issue_pdf_box', 'imp_issue', 'normal', 'high' );
} );

function imp_m_issue_pdf_box( $post ) {
	wp_enqueue_media();
	wp_nonce_field( 'imp_issue_pdf', 'imp_issue_pdf_nonce' );
	$url = get_post_meta( $post->ID, IMP_M_ISSUE_PDF, true );
	?>
	<p>
		<input type="url" class="widefat" id="imp-issue-pdf" name="imp_issue_pdf" value="<?php echo esc_attr( $url ); ?>" placeholder="https://…/IranGara-No1.pdf" dir="ltr">
	</p>
	<p><button type="button" class="button" id="imp-issue-pdf-pick">انتخاب از کتابخانه رسانه</button></p>
	<p class="description">عنوان = «شماره ۱»، چکیده = توضیح کوتاه، تاریخ انتشار = تاریخ نشریه روی کارت.</p>
	<script>
	document.getElementById('imp-issue-pdf-pick').addEventListener('click', function () {
		var frame = wp.media({ title: 'انتخاب PDF', library: { type: 'application/pdf' }, multiple: false });
		frame.on('select', function () {
			document.getElementById('imp-issue-pdf').value = frame.state().get('selection').first().get('url');
		});
		frame.open();
	});
	</script>
	<?php
}

add_action( 'save_post_imp_issue', function ( $post_id ) {
	if ( ! isset( $_POST['imp_issue_pdf_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['imp_issue_pdf_nonce'] ) ), 'imp_issue_pdf' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$url = isset( $_POST['imp_issue_pdf'] ) ? esc_url_raw( wp_unslash( $_POST['imp_issue_pdf'] ) ) : '';
	update_post_meta( $post_id, IMP_M_ISSUE_PDF, $url );
} );

/* ---------- Front end ---------- */

function imp_m_render_magazine() {
	$page_id = get_queried_object_id();
	$issues  = new WP_Query( array(
		'post_type'      => 'imp_issue',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => apply_filters( 'imp_m_issue_order', 'ASC' ), // Figma lists ۱ then ۲
		'no_found_rows'  => true,
	) );
	?>
	<main class="imp-m imp-m-page imp-m-magazine">
		<?php imp_m_ornament_title( get_the_title( $page_id ) ); ?>

		<div class="imp-m-magazine__intro">
			<?php echo apply_filters( 'the_content', get_post_field( 'post_content', $page_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- post content. ?>
		</div>

		<?php if ( $issues->have_posts() ) : ?>
			<ul class="imp-m-issues">
				<?php
				while ( $issues->have_posts() ) :
					$issues->the_post();
					$date = imp_m_jalali_parts( get_post_datetime(), true );
					$pdf  = get_post_meta( get_the_ID(), IMP_M_ISSUE_PDF, true );
					?>
					<li class="imp-m-issue">
						<div class="imp-m-issue__top">
							<h2 class="imp-m-issue__title"><?php the_title(); ?></h2>
							<span class="imp-m-issue__year"><?php echo esc_html( $date['year'] ); ?></span>
						</div>
						<?php if ( has_excerpt() ) : ?>
							<p class="imp-m-issue__desc"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>
						<div class="imp-m-issue__bar">
							<time class="imp-m-issue__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( $date['day'] . ' ' . $date['month'] ); ?></time>
							<?php if ( $pdf ) : ?>
								<a class="imp-m-issue__download" href="<?php echo esc_url( $pdf ); ?>" download target="_blank" rel="noopener">
									<?php echo esc_html_x( 'دانلود', 'magazine', 'imp-mobile' ); ?>
									<?php imp_m_icon( 'download' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</li>
				<?php endwhile; ?>
			</ul>
		<?php endif; ?>
	</main>
	<?php
	wp_reset_postdata();
}
