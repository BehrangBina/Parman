<?php
/**
 * Party documents — مرامنامه / اساسنامه / سوگندنامه (Figma Overlay-Maramname 697:1066,
 * Read-Maramname 805:6354, Maram-01/02 805:7201 / 813:7420, and the Asas/Sogand equivalents).
 *
 * The page shows the "overlay" screen (download, PDF card, بخوانید). بخوانید opens a paged
 * reader built from the page's own text: cover, then the text split into screen-sized pages.
 * If the page has no text yet (only a PDF/flipbook), بخوانید opens the PDF instead.
 *
 * PDF: the page's "فایل PDF سند" field, or else the first PDF found in the page content
 * (e.g. the dFlip flipbook the live pages already use).
 */

defined( 'ABSPATH' ) || exit;

const IMP_DOC_PDF = '_imp_doc_pdf';

add_filter( 'imp_custom_pages', function ( $pages ) {
	foreach ( imp_document_slugs() as $slug ) {
		$pages[ $slug ] = 'imp_render_document';
	}
	return $pages;
} );

function imp_document_slugs() {
	return apply_filters( 'imp_document_slugs', array( 'partys-motto', 'party-constitution', 'affidavit' ) );
}

/* ---------- Admin: PDF field on pages ---------- */

add_action( 'add_meta_boxes_page', function ( $post ) {
	if ( in_array( $post->post_name, imp_document_slugs(), true ) ) {
		add_meta_box( 'imp-doc-pdf', 'فایل PDF سند', 'imp_doc_pdf_box', 'page', 'side' );
	}
} );

function imp_doc_pdf_box( $post ) {
	wp_nonce_field( 'imp_doc_pdf', 'imp_doc_pdf_nonce' );
	?>
	<p><input type="url" class="widefat" name="imp_doc_pdf" value="<?php echo esc_attr( get_post_meta( $post->ID, IMP_DOC_PDF, true ) ); ?>" dir="ltr" placeholder="https://…/file.pdf"></p>
	<p class="description">خالی بگذارید تا PDF داخل صفحه (مثلاً فلیپ‌بوک) به‌طور خودکار استفاده شود.</p>
	<?php
}

add_action( 'save_post_page', function ( $post_id ) {
	if ( ! isset( $_POST['imp_doc_pdf_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['imp_doc_pdf_nonce'] ) ), 'imp_doc_pdf' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, IMP_DOC_PDF, isset( $_POST['imp_doc_pdf'] ) ? esc_url_raw( wp_unslash( $_POST['imp_doc_pdf'] ) ) : '' );
} );

/* ---------- Data ---------- */

/** PDF URL for a document page: explicit field, else the first PDF in the rendered content. */
function imp_document_pdf( $post ) {
	static $cache = array();
	if ( isset( $cache[ $post->ID ] ) ) {
		return $cache[ $post->ID ];
	}
	$url = get_post_meta( $post->ID, IMP_DOC_PDF, true );
	if ( ! $url ) {
		$html = apply_filters( 'the_content', $post->post_content );
		if ( preg_match( '#"source"\s*:\s*"([^"]+?\.pdf)"#i', $html, $m ) ) {
			$url = json_decode( '"' . $m[1] . '"' ); // dFlip stores it JSON-escaped
		} elseif ( preg_match( '#https?://[^\s"\'<>]+?\.pdf#i', $html, $m ) ) {
			$url = $m[0];
		}
	}
	return $cache[ $post->ID ] = $url ? esc_url_raw( $url ) : '';
}

/**
 * The page text for the reader, without shortcodes (flipbooks) or scripts.
 * A short first paragraph (e.g. "نگارش دوم") becomes the edition label.
 *
 * @return array{label:string,html:string,has_text:bool}
 */
function imp_document_text( $post ) {
	$html  = apply_filters( 'the_content', strip_shortcodes( $post->post_content ) );
	$html  = preg_replace( '#<script\b[^>]*>.*?</script>#is', '', $html );
	$html  = preg_replace( '#<p>(\s|&nbsp;|<br\s*/?>)*</p>#i', '', $html );
	$label = '';
	if ( preg_match( '#^\s*<p[^>]*>(.*?)</p>#is', $html, $m ) && mb_strlen( trim( wp_strip_all_tags( $m[1] ) ) ) <= 40 ) {
		$label = trim( wp_strip_all_tags( $m[1] ) );
		$html  = substr( $html, strlen( $m[0] ) );
	}
	return array(
		'label'    => $label,
		'html'     => trim( $html ),
		'has_text' => mb_strlen( trim( wp_strip_all_tags( $html ) ) ) > 150,
	);
}

/* ---------- Front end ---------- */

function imp_render_document() {
	$post  = get_queried_object();
	$title = get_the_title( $post );
	$pdf   = imp_document_pdf( $post );
	$text  = imp_document_text( $post );
	$back  = wp_get_referer() ?: home_url( '/' );
	$slogan = apply_filters( 'imp_document_slogan', 'شکوه دیروز، اقتدار فردا', $post );
	?>
	<main class="imp-ui imp-doc" aria-labelledby="imp-doc-title">
		<div class="imp-doc__top">
			<a class="imp-doc__close" href="<?php echo esc_url( $back ); ?>" data-imp-back>
				<span class="screen-reader-text"><?php esc_html_e( 'Close', 'imp' ); ?></span>
				<?php imp_icon( 'close' ); ?>
			</a>
			<div class="imp-otitle" id="imp-doc-title">
				<img class="imp-otitle__orn" src="<?php echo esc_url( imp_asset( 'img/ornament.svg' ) ); ?>" alt="" aria-hidden="true">
				<h1 class="imp-otitle__text"><?php echo esc_html( $title ); ?></h1>
				<img class="imp-otitle__orn imp-otitle__orn--flip" src="<?php echo esc_url( imp_asset( 'img/ornament.svg' ) ); ?>" alt="" aria-hidden="true">
			</div>
		</div>

		<div class="imp-doc__stage" style="--imp-lion: url('<?php echo esc_url( imp_asset( 'img/lion.svg' ) ); ?>')">
			<?php if ( $pdf ) : ?>
				<a class="imp-doc__download" href="<?php echo esc_url( imp_pdf_download_url( $post->ID ) ); ?>" download>
					<?php imp_icon( 'download' ); ?>
					<?php echo esc_html_x( 'دانلود', 'document', 'imp' ); ?>
				</a>
			<?php endif; ?>

			<a class="imp-doc__thumb" <?php echo $pdf ? 'href="' . esc_url( $pdf ) . '" target="_blank" rel="noopener"' : 'href="#" data-imp-reader-open'; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped. ?> aria-label="<?php echo esc_attr( $title ); ?>">
				<?php if ( has_post_thumbnail( $post ) ) : ?>
					<?php echo get_the_post_thumbnail( $post, 'medium', array( 'alt' => '' ) ); ?>
				<?php else : ?>
					<span class="imp-doc__sheet" aria-hidden="true">
						<img src="<?php echo esc_url( imp_asset( 'img/logo.svg' ) ); ?>" alt="">
						<i></i><i></i><i></i><i></i><i></i><i></i>
					</span>
				<?php endif; ?>
				<?php if ( $pdf ) : ?><span class="imp-doc__pdf">PDF</span><?php endif; ?>
			</a>

			<?php
			// بخوانید only when the page has text for the reader. (A plain PDF link here made
			// Android phones download the file, so without text only دانلود is offered.)
			if ( $text['has_text'] ) :
				?>
				<button class="imp-btn imp-btn--gold imp-doc__read" type="button" data-imp-reader-open><?php echo esc_html_x( 'بخوانید', 'document', 'imp' ); ?></button>
			<?php endif; ?>
		</div>

		<?php if ( $text['has_text'] ) : ?>
			<div class="imp-reader" data-imp-reader hidden>
				<section class="imp-reader__cover" data-imp-reader-cover>
					<button class="imp-reader__close imp-reader__close--light" type="button" data-imp-reader-close>
						<span class="screen-reader-text"><?php esc_html_e( 'Close', 'imp' ); ?></span>
						<?php imp_icon( 'close' ); ?>
					</button>
					<img class="imp-reader__logo" src="<?php echo esc_url( imp_asset( 'img/logo.svg' ) ); ?>" alt="">
					<p class="imp-reader__title">«<?php echo esc_html( $title ); ?>»</p>
					<p class="imp-reader__slogan">
						<img src="<?php echo esc_url( imp_asset( 'img/ornament.svg' ) ); ?>" alt="">
						<span><?php echo esc_html( $slogan ); ?></span>
						<img class="imp-otitle__orn--flip" src="<?php echo esc_url( imp_asset( 'img/ornament.svg' ) ); ?>" alt="">
					</p>
					<button class="imp-reader__arrow imp-reader__start" type="button" data-imp-reader-next>
						<span class="screen-reader-text"><?php esc_html_e( 'Start reading', 'imp' ); ?></span>
						<?php imp_icon( 'arrow-slider' ); ?>
					</button>
				</section>

				<section class="imp-reader__pages" data-imp-reader-pages hidden>
					<button class="imp-reader__close" type="button" data-imp-reader-close>
						<span class="screen-reader-text"><?php esc_html_e( 'Close', 'imp' ); ?></span>
						<?php imp_icon( 'close' ); ?>
					</button>
					<?php if ( $text['label'] ) : ?>
						<span class="imp-reader__label"><?php echo esc_html( $text['label'] ); ?></span>
					<?php endif; ?>
					<div class="imp-reader__viewport" data-imp-reader-viewport>
						<div class="imp-reader__flow" data-imp-reader-flow>
							<?php echo wp_kses_post( $text['html'] ); ?>
						</div>
					</div>
					<nav class="imp-reader__nav" aria-label="<?php esc_attr_e( 'Pages', 'imp' ); ?>">
						<button class="imp-reader__arrow imp-reader__arrow--prev" type="button" data-imp-reader-prev>
							<span class="screen-reader-text"><?php esc_html_e( 'Previous page', 'imp' ); ?></span>
							<?php imp_icon( 'arrow-slider' ); ?>
						</button>
						<span class="imp-reader__count" data-imp-reader-count aria-live="polite"></span>
						<button class="imp-reader__arrow imp-reader__arrow--next" type="button" data-imp-reader-next>
							<span class="screen-reader-text"><?php esc_html_e( 'Next page', 'imp' ); ?></span>
							<?php imp_icon( 'arrow-slider' ); ?>
						</button>
					</nav>
				</section>
			</div>
		<?php endif; ?>
	</main>
	<?php
}

add_filter( 'body_class', function ( $classes ) {
	if ( 'imp_render_document' === imp_current_renderer() ) {
		$classes[] = 'imp-docpage';
	}
	return $classes;
} );
