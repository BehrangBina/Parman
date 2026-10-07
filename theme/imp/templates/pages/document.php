<?php
/**
 * Party document (مرامنامه / اساسنامه / سوگندنامه): intro screen + paged reader.
 * Figma: Overlay-Maramname (697:1066) → Read-Maramname cover (805:6354) → Maram-01/02 text pages
 * (805:7201, 813:7420); same layout for Asas* and Sogand* frames. Desktop: Maramname (1181:6630).
 *
 * @package IMP
 *
 * @var array $args { title, pdf, download, thumbnail, text: {label,html,has_text}, back_url, slogan }
 */

defined( 'ABSPATH' ) || exit;

$text = $args['text'];
?>
<main class="imp-ui imp-doc" aria-labelledby="imp-doc-title">
	<?php // Figma: Overlay-* top area (close + ornament title). ?>
	<div class="imp-doc__top">
		<a class="imp-doc__close" href="<?php echo esc_url( $args['back_url'] ); ?>" data-imp-back>
			<span class="screen-reader-text"><?php esc_html_e( 'Close', 'imp' ); ?></span>
			<?php imp_icon( 'close' ); ?>
		</a>
		<?php
		imp_component(
			'ornament-title',
			array(
				'title' => $args['title'],
				'id'    => 'imp-doc-title',
			)
		);
		?>
	</div>

	<?php // Figma: Overlay-* blue stage (download, PDF card, بخوانید). ?>
	<div class="imp-doc__stage">
		<?php if ( $args['download'] ) : ?>
			<a class="imp-doc__download" href="<?php echo esc_url( $args['download'] ); ?>" download>
				<?php imp_icon( 'download' ); ?>
				<?php echo esc_html_x( 'دانلود', 'document', 'imp' ); ?>
			</a>
		<?php endif; ?>

		<?php if ( $args['pdf'] ) : ?>
			<a class="imp-doc__thumb" href="<?php echo esc_url( $args['pdf'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $args['title'] ); ?>">
		<?php else : ?>
			<a class="imp-doc__thumb" href="#" data-imp-reader-open aria-label="<?php echo esc_attr( $args['title'] ); ?>">
		<?php endif; ?>
			<?php if ( $args['thumbnail'] ) : ?>
				<?php echo $args['thumbnail']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core get_the_post_thumbnail() output. ?>
			<?php else : ?>
				<?php // Stand-in "page" when the document has no cover image (Featured image). ?>
				<span class="imp-doc__sheet" aria-hidden="true">
					<img src="<?php echo esc_url( imp_asset( 'img/logo.svg' ) ); ?>" alt="">
					<i></i><i></i><i></i><i></i><i></i><i></i>
				</span>
			<?php endif; ?>
			<?php if ( $args['pdf'] ) : ?>
				<span class="imp-doc__pdf">PDF</span>
			<?php endif; ?>
		</a>

		<?php // بخوانید only when the page has text for the reader (a bare PDF link made Android download). ?>
		<?php if ( $text['has_text'] ) : ?>
			<button class="imp-btn imp-btn--gold imp-doc__read" type="button" data-imp-reader-open><?php echo esc_html_x( 'بخوانید', 'document', 'imp' ); ?></button>
		<?php endif; ?>
	</div>

	<?php if ( $text['has_text'] ) : ?>
		<div class="imp-reader" data-imp-reader hidden>
			<?php // Figma: Read-* cover. ?>
			<section class="imp-reader__cover" data-imp-reader-cover>
				<?php
				imp_component(
					'close-button',
					array(
						'class' => 'imp-reader__close imp-reader__close--light',
						'data'  => 'data-imp-reader-close',
					)
				);
				?>
				<img class="imp-reader__logo" src="<?php echo esc_url( imp_asset( 'img/logo.svg' ) ); ?>" alt="">
				<p class="imp-reader__title">«<?php echo esc_html( $args['title'] ); ?>»</p>
				<p class="imp-reader__slogan">
					<img src="<?php echo esc_url( imp_asset( 'img/ornament.svg' ) ); ?>" alt="">
					<span><?php echo esc_html( $args['slogan'] ); ?></span>
					<img class="imp-otitle__orn--flip" src="<?php echo esc_url( imp_asset( 'img/ornament.svg' ) ); ?>" alt="">
				</p>
				<button class="imp-reader__arrow imp-reader__start" type="button" data-imp-reader-next>
					<span class="screen-reader-text"><?php esc_html_e( 'Start reading', 'imp' ); ?></span>
					<?php imp_icon( 'arrow-slider' ); ?>
				</button>
			</section>

			<?php // Figma: *-01 / *-02 text pages. ?>
			<section class="imp-reader__pages" data-imp-reader-pages hidden>
				<?php
				imp_component(
					'close-button',
					array(
						'class' => 'imp-reader__close',
						'data'  => 'data-imp-reader-close',
					)
				);
				?>
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
