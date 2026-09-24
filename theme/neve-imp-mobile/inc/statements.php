<?php
/**
 * Mobile "Bayanie" (statements) list — Figma frame 1009:3003.
 * Category archive /category/statements/, registered in inc/pages.php. Uses the main query,
 * so new statements appear automatically when admins publish a post in that category.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Split a statement title into the fixed prefix and the subject shown large on the card.
 * "بیانیه پارمان پادشاهی ایرانیان درباره اعدام …" → "اعدام …"
 */
function imp_m_statement_subject( $title ) {
	$prefixes = apply_filters( 'imp_m_statement_prefixes', array(
		'بیانیه پارمان پادشاهی ایرانیان',
		'بیانیه حزب پادشاهی ایرانیان',
	) );
	foreach ( $prefixes as $prefix ) {
		if ( 0 === mb_strpos( $title, $prefix ) ) {
			$rest = trim( mb_substr( $title, mb_strlen( $prefix ) ) );
			$rest = preg_replace( '/^درباره(ی)?\s*:?\s*/u', '', $rest );
			return '' !== $rest ? $rest : $title;
		}
	}
	return $title;
}

function imp_m_render_statements() {
	global $wp_query;
	?>
	<main class="imp-m imp-m-page imp-m-statements">
		<?php imp_m_ornament_title( imp_m_current_title() ); ?>

		<?php if ( have_posts() ) : ?>
			<ul class="imp-m-slist">
				<?php
				while ( have_posts() ) :
					the_post();
					list( $day_month, $year ) = imp_m_post_date_gregorian_fa();
					?>
					<li>
						<a class="imp-m-scard" href="<?php the_permalink(); ?>">
							<span class="imp-m-scard__date">
								<?php imp_m_icon( 'calendar' ); ?>
								<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
									<span><?php echo esc_html( $day_month ); ?></span>
									<span><?php echo esc_html( $year ); ?></span>
								</time>
							</span>
							<span class="imp-m-scard__body">
								<span class="imp-m-scard__prefix"><?php echo esc_html_x( 'بیانیه پارمان پادشاهی ایرانیان درباره:', 'statements', 'imp-mobile' ); ?></span>
								<span class="imp-m-scard__title"><?php echo esc_html( imp_m_statement_subject( get_the_title() ) ); ?></span>
							</span>
						</a>
					</li>
				<?php endwhile; ?>
			</ul>

			<?php
			the_posts_pagination( array(
				'class'     => 'imp-m-pagination',
				'mid_size'  => 1,
				'prev_text' => '‹',
				'next_text' => '›',
			) );
			?>
		<?php else : ?>
			<p class="imp-m-empty"><?php echo esc_html_x( 'هنوز بیانیه‌ای منتشر نشده است.', 'statements', 'imp-mobile' ); ?></p>
		<?php endif; ?>
	</main>
	<?php
	rewind_posts();
}
