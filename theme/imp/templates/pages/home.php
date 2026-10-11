<?php
/**
 * Home. Figma: mobile Home (697:928), desktop Home (1112:5062).
 *
 * @package IMP
 *
 * @var array $args { links: array, news: array[] }  (IMP\Controllers\Home_Controller)
 */

defined( 'ABSPATH' ) || exit;

$links = $args['links'];
?>
<main class="imp-ui imp-home" id="imp-home">
	<?php // Figma: HeroSection (881:13010 / desktop Hero-Photo 1112:5251). ?>
	<section class="imp-hero">
		<h1 class="imp-hero__title"><?php echo esc_html_x( 'حزب همگـام با شاهزاده رضا پهلوی', 'home hero', 'imp' ); ?></h1>
		<div class="imp-hero__buttons">
			<a class="imp-btn imp-btn--gold-outline" href="<?php echo esc_url( $links['about'] ); ?>"><?php echo esc_html_x( 'دیدگاه حزب', 'home hero', 'imp' ); ?></a>
			<a class="imp-btn imp-btn--gold-outline" href="<?php echo esc_url( $links['media'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html_x( 'رسانه حزب', 'home hero', 'imp' ); ?></a>
		</div>
	</section>

	<?php // Figma: Buttons-Home-ASASNAME (881:13008 / desktop 1225:8344). ?>
	<nav class="imp-docs" aria-label="<?php esc_attr_e( 'Party documents', 'imp' ); ?>">
		<a class="imp-btn imp-btn--blue-outline" href="<?php echo esc_url( $links['constitution'] ); ?>"><?php echo esc_html_x( 'اساسنامه', 'doc button', 'imp' ); ?></a>
		<a class="imp-btn imp-btn--blue-outline" href="<?php echo esc_url( $links['affidavit'] ); ?>"><?php echo esc_html_x( 'سوگندنامه', 'doc button', 'imp' ); ?></a>
		<a class="imp-btn imp-btn--blue-outline" href="<?php echo esc_url( $links['motto'] ); ?>"><?php echo esc_html_x( 'مرامنامه', 'doc button', 'imp' ); ?></a>
	</nav>

	<?php if ( $args['news'] ) : ?>
		<?php // Figma: News-Home-Slide (1023:2421 / desktop 1116:3691). ?>
		<section class="imp-news" aria-labelledby="imp-news-title">
			<h2 id="imp-news-title" class="imp-section-title"><?php echo esc_html_x( 'اخبار حزب پادشاهی ایرانیان', 'home news', 'imp' ); ?></h2>
			<div class="imp-news__track" tabindex="0">
				<?php
				foreach ( $args['news'] as $card ) {
					imp_component( 'news-card', $card );
				}
				?>
			</div>
			<div class="imp-news__nav">
				<button class="imp-news__arrow imp-news__arrow--left" type="button" data-dir="-1">
					<span class="screen-reader-text"><?php esc_html_e( 'Scroll left', 'imp' ); ?></span>
					<?php imp_icon( 'arrow-slider' ); ?>
				</button>
				<a class="imp-news__all" href="<?php echo esc_url( $links['news'] ); ?>"><?php echo esc_html_x( 'مشاهده اخبارها', 'home news', 'imp' ); ?></a>
				<button class="imp-news__arrow imp-news__arrow--right" type="button" data-dir="1">
					<span class="screen-reader-text"><?php esc_html_e( 'Scroll right', 'imp' ); ?></span>
					<?php imp_icon( 'arrow-slider' ); ?>
				</button>
			</div>
		</section>
	<?php endif; ?>

	<?php // Figma: ContactUs-Home (881:13015 / desktop 1250:9590). ?>
	<section class="imp-contact" aria-labelledby="imp-contact-title">
		<h2 id="imp-contact-title" class="imp-section-title imp-section-title--slab"><?php echo esc_html_x( 'تماس با ما', 'home contact', 'imp' ); ?></h2>
		<?php imp_component( 'contact-form' ); ?>
	</section>
</main>
