<?php
/**
 * Mobile "Akhbaar" (news) page — Figma frame 697:2647. Page slug `news`, registered in
 * inc/pages.php. Lists the party-news category (see imp_news_query_args) with the same
 * card as the home carousel, newest first, 9 per page.
 */

defined( 'ABSPATH' ) || exit;

function imp_render_news() {
	$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	$query = new WP_Query( imp_news_query_args( array(
		'posts_per_page' => 9,
		'paged'          => $paged,
	) ) );
	?>
	<main class="imp-ui imp-page imp-newspage">
		<?php imp_ornament_title( _x( 'آخرین اخبار', 'news page', 'imp' ) ); ?>

		<?php if ( $query->have_posts() ) : ?>
			<div class="imp-newslist">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					imp_news_card();
				endwhile;
				?>
			</div>

			<?php
			$pages = paginate_links( array(
				'current'   => $paged,
				'total'     => $query->max_num_pages,
				'mid_size'  => 1,
				'prev_text' => '‹',
				'next_text' => '›',
			) );
			if ( $pages ) {
				echo '<nav class="navigation pagination imp-pagination" aria-label="' . esc_attr__( 'News pages', 'imp' ) . '"><div class="nav-links">' . $pages . '</div></nav>'; // phpcs:ignore WordPress.Security.EscapeOutput -- core markup.
			}
			?>
		<?php else : ?>
			<p class="imp-empty"><?php echo esc_html_x( 'هنوز خبری منتشر نشده است.', 'news page', 'imp' ); ?></p>
		<?php endif; ?>
	</main>
	<?php
	wp_reset_postdata();
}
