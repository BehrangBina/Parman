<?php
/**
 * Mobile home page (Figma frame "Home" 697:928): hero, document buttons,
 * news carousel and the membership/contact form. Registered in inc/pages.php.
 */

defined( 'ABSPATH' ) || exit;

function imp_m_render_home() {
	$links = imp_m_links();
	?>
	<main class="imp-m imp-m-home" id="imp-m-home">
		<section class="imp-m-hero" style="--imp-hero: url('<?php echo esc_url( imp_m_asset( 'img/hero.jpg' ) ); ?>')">
			<h1 class="imp-m-hero__title"><?php echo esc_html_x( 'حزب همگـام با شاهزاده رضا پهلوی', 'home hero', 'imp-mobile' ); ?></h1>
			<div class="imp-m-hero__buttons">
				<a class="imp-m-btn imp-m-btn--gold-outline" href="<?php echo esc_url( $links['about'] ); ?>"><?php echo esc_html_x( 'دیدگاه حزب', 'home hero', 'imp-mobile' ); ?></a>
				<a class="imp-m-btn imp-m-btn--gold-outline" href="<?php echo esc_url( $links['media'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html_x( 'رسانه حزب', 'home hero', 'imp-mobile' ); ?></a>
			</div>
		</section>

		<nav class="imp-m-docs" aria-label="<?php esc_attr_e( 'Party documents', 'imp-mobile' ); ?>">
			<a class="imp-m-btn imp-m-btn--blue-outline" href="<?php echo esc_url( $links['constitution'] ); ?>"><?php echo esc_html_x( 'اساسنامه', 'doc button', 'imp-mobile' ); ?></a>
			<a class="imp-m-btn imp-m-btn--blue-outline" href="<?php echo esc_url( $links['affidavit'] ); ?>"><?php echo esc_html_x( 'سوگندنامه', 'doc button', 'imp-mobile' ); ?></a>
			<a class="imp-m-btn imp-m-btn--blue-outline" href="<?php echo esc_url( $links['motto'] ); ?>"><?php echo esc_html_x( 'مرامنامه', 'doc button', 'imp-mobile' ); ?></a>
		</nav>

		<?php imp_m_news_section(); ?>
		<?php imp_m_contact_section(); ?>
	</main>
	<?php
}

function imp_m_news_section() {
	$query = new WP_Query( array(
		'post_type'           => 'post',
		'posts_per_page'      => 6,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );
	if ( ! $query->have_posts() ) {
		return;
	}
	$links = imp_m_links();
	?>
	<section class="imp-m-news" aria-labelledby="imp-m-news-title">
		<h2 id="imp-m-news-title" class="imp-m-section-title"><?php echo esc_html_x( 'اخبار حزب پادشاهی ایرانیان', 'home news', 'imp-mobile' ); ?></h2>
		<div class="imp-m-news__track" tabindex="0">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				?>
				<a class="imp-m-card" href="<?php the_permalink(); ?>">
					<span class="imp-m-card__media">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
						<?php else : ?>
							<img src="<?php echo esc_url( imp_m_asset( 'img/news-placeholder.png' ) ); ?>" alt="" loading="lazy">
						<?php endif; ?>
					</span>
					<time class="imp-m-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( imp_m_post_date() ); ?></time>
					<span class="imp-m-card__title"><?php the_title(); ?></span>
					<span class="imp-m-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 40, '…' ) ); ?></span>
				</a>
			<?php endwhile; ?>
		</div>
		<div class="imp-m-news__nav">
			<button class="imp-m-news__arrow imp-m-news__arrow--left" type="button" data-dir="-1">
				<span class="screen-reader-text"><?php esc_html_e( 'Scroll left', 'imp-mobile' ); ?></span>
				<?php imp_m_icon( 'arrow-slider' ); ?>
			</button>
			<a class="imp-m-news__all" href="<?php echo esc_url( $links['news'] ); ?>"><?php echo esc_html_x( 'مشاهده اخبارها', 'home news', 'imp-mobile' ); ?></a>
			<button class="imp-m-news__arrow imp-m-news__arrow--right" type="button" data-dir="1">
				<span class="screen-reader-text"><?php esc_html_e( 'Scroll right', 'imp-mobile' ); ?></span>
				<?php imp_m_icon( 'arrow-slider' ); ?>
			</button>
		</div>
	</section>
	<?php
	wp_reset_postdata();
}
