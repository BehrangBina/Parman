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

/* ---------- Download endpoint: /?imp_pdf=<post id> ----------
 * Works for magazine issues and document pages (see imp_m_post_pdf()). The HTML `download`
 * attribute is ignored by some mobile browsers, so files in this site's uploads are sent with
 * Content-Disposition: attachment. PDFs hosted elsewhere are redirected to.
 */

function imp_m_pdf_download_url( $post_id ) {
	return add_query_arg( 'imp_pdf', (int) $post_id, home_url( '/' ) );
}

/** Back-compat name used by the magazine template. */
function imp_m_issue_download_url( $issue_id ) {
	return imp_m_pdf_download_url( $issue_id );
}

/** PDF URL for an issue (its PDF field) or a document page (see inc/documents.php). */
function imp_m_post_pdf( $post ) {
	if ( ! $post || 'publish' !== $post->post_status ) {
		return '';
	}
	if ( 'imp_issue' === $post->post_type ) {
		return get_post_meta( $post->ID, IMP_M_ISSUE_PDF, true );
	}
	return function_exists( 'imp_m_document_pdf' ) ? imp_m_document_pdf( $post ) : '';
}

add_action( 'template_redirect', function () {
	$key = isset( $_GET['imp_pdf'] ) ? 'imp_pdf' : ( isset( $_GET['imp_issue_download'] ) ? 'imp_issue_download' : '' ); // phpcs:ignore WordPress.Security.NonceVerification -- public download link.
	if ( ! $key ) {
		return;
	}
	$url = imp_m_post_pdf( get_post( absint( $_GET[ $key ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
	if ( ! $url ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	$uploads = wp_get_upload_dir();
	$base    = set_url_scheme( $uploads['baseurl'], 'https' );
	$rel     = 0 === strpos( set_url_scheme( $url, 'https' ), $base ) ? substr( set_url_scheme( $url, 'https' ), strlen( $base ) ) : '';
	$path    = $rel ? realpath( $uploads['basedir'] . rawurldecode( $rel ) ) : false;
	$root    = realpath( $uploads['basedir'] );

	if ( $path && $root && 0 === strpos( $path, $root ) && is_file( $path ) && 'pdf' === strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) ) {
		nocache_headers();
		header( 'Content-Type: application/pdf' );
		$name = basename( $path ); // may be Persian: send an ASCII fallback + the UTF-8 name (RFC 5987)
		$ascii = preg_replace( '/[^A-Za-z0-9._-]/', '', $name );
		$ascii = preg_match( '/[A-Za-z0-9]/', pathinfo( $ascii, PATHINFO_FILENAME ) ) ? $ascii : 'document.pdf';
		header( 'Content-Disposition: attachment; filename="' . $ascii . '"; filename*=UTF-8\'\'' . rawurlencode( $name ) );
		header( 'Content-Length: ' . filesize( $path ) );
		header( 'X-Content-Type-Options: nosniff' );
		readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}

	wp_redirect( esc_url_raw( $url ) ); // phpcs:ignore WordPress.Security.SafeRedirect -- admin-entered PDF URL.
	exit;
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
						<?php
						// Tapping the blue part views the PDF; "دانلود" downloads it.
						$view_tag = $pdf ? 'a' : 'div';
						printf(
							'<%1$s class="imp-m-issue__view"%2$s>',
							$view_tag, // phpcs:ignore WordPress.Security.EscapeOutput -- fixed tag.
							$pdf ? ' href="' . esc_url( $pdf ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( sprintf( 'مشاهده %s', get_the_title() ) ) . '"' : '' // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
						);
						?>
							<span class="imp-m-issue__top">
								<span class="imp-m-issue__title"><?php the_title(); ?></span>
								<span class="imp-m-issue__year"><?php echo esc_html( $date['year'] ); ?></span>
							</span>
							<?php if ( has_excerpt() ) : ?>
								<span class="imp-m-issue__desc"><?php echo esc_html( get_the_excerpt() ); ?></span>
							<?php endif; ?>
						<?php echo '</' . $view_tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput -- fixed tag. ?>
						<div class="imp-m-issue__bar">
							<time class="imp-m-issue__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( $date['day'] . ' ' . $date['month'] ); ?></time>
							<?php if ( $pdf ) : ?>
								<a class="imp-m-issue__download" href="<?php echo esc_url( imp_m_issue_download_url( get_the_ID() ) ); ?>" download>
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
