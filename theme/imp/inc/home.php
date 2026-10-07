<?php
/**
 * Mobile home page (Figma frame "Home" 697:928): hero, document buttons,
 * news carousel and the membership/contact form. Registered in inc/pages.php.
 */

defined( 'ABSPATH' ) || exit;

function imp_render_home() {
	$links = imp_links();
	?>
	<main class="imp-ui imp-home" id="imp-home">
		<section class="imp-hero" style="--imp-hero: url('<?php echo esc_url( imp_asset( 'img/hero.jpg' ) ); ?>')">
			<h1 class="imp-hero__title"><?php echo esc_html_x( 'حزب همگـام با شاهزاده رضا پهلوی', 'home hero', 'imp' ); ?></h1>
			<div class="imp-hero__buttons">
				<a class="imp-btn imp-btn--gold-outline" href="<?php echo esc_url( $links['about'] ); ?>"><?php echo esc_html_x( 'دیدگاه حزب', 'home hero', 'imp' ); ?></a>
				<a class="imp-btn imp-btn--gold-outline" href="<?php echo esc_url( $links['media'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html_x( 'رسانه حزب', 'home hero', 'imp' ); ?></a>
			</div>
		</section>

		<nav class="imp-docs" aria-label="<?php esc_attr_e( 'Party documents', 'imp' ); ?>">
			<a class="imp-btn imp-btn--blue-outline" href="<?php echo esc_url( $links['constitution'] ); ?>"><?php echo esc_html_x( 'اساسنامه', 'doc button', 'imp' ); ?></a>
			<a class="imp-btn imp-btn--blue-outline" href="<?php echo esc_url( $links['affidavit'] ); ?>"><?php echo esc_html_x( 'سوگندنامه', 'doc button', 'imp' ); ?></a>
			<a class="imp-btn imp-btn--blue-outline" href="<?php echo esc_url( $links['motto'] ); ?>"><?php echo esc_html_x( 'مرامنامه', 'doc button', 'imp' ); ?></a>
		</nav>

		<?php imp_news_section(); ?>
		<?php imp_contact_section(); ?>
	</main>
	<?php
}

/**
 * Query args for party news: the "اخبار حزب" category (live slug `news-party`, filter
 * `imp_news_category`), falling back to all posts when that category is missing or empty.
 */
function imp_news_query_args( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'post_type'           => 'post',
		'ignore_sticky_posts' => true,
	) );
	$cat = get_category_by_slug( apply_filters( 'imp_news_category', 'news-party' ) );
	if ( $cat && $cat->count > 0 ) {
		$args['cat'] = $cat->term_id;
	}
	return $args;
}

/** News card (home carousel + news page), for the current post in the loop. */
function imp_news_card() {
	?>
	<a class="imp-card" href="<?php the_permalink(); ?>">
		<span class="imp-card__media">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
			<?php else : ?>
				<img src="<?php echo esc_url( imp_asset( 'img/news-placeholder.png' ) ); ?>" alt="" loading="lazy">
			<?php endif; ?>
		</span>
		<time class="imp-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( implode( ' ', imp_post_date_gregorian_fa() ) ); ?></time>
		<span class="imp-card__title"><?php the_title(); ?></span>
		<span class="imp-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 40, '…' ) ); ?></span>
	</a>
	<?php
}

function imp_news_section() {
	$query = new WP_Query( imp_news_query_args( array(
		'posts_per_page' => 6,
		'no_found_rows'  => true,
	) ) );
	if ( ! $query->have_posts() ) {
		return;
	}
	$links = imp_links();
	?>
	<section class="imp-news" aria-labelledby="imp-news-title">
		<h2 id="imp-news-title" class="imp-section-title"><?php echo esc_html_x( 'اخبار حزب پادشاهی ایرانیان', 'home news', 'imp' ); ?></h2>
		<div class="imp-news__track" tabindex="0">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				imp_news_card();
			endwhile;
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
	<?php
	wp_reset_postdata();
}
