<?php
/**
 * Akhbaar (news list). Figma: mobile Akhbaar (697:2647), desktop News (1215:5585):
 * 3 large stories, "مشاهده اخبارها" over a rule, then a 4-column grid. Phones: one list.
 *
 * @package IMP
 *
 * @var array $args { featured: array[], more: array[], next_url: string, pagination: string }  (IMP\Controllers\News_Controller)
 */

defined( 'ABSPATH' ) || exit;
?>
<main class="imp-ui imp-page imp-newspage">
	<?php imp_component( 'ornament-title', array( 'title' => _x( 'آخرین اخبار', 'news page', 'imp' ) ) ); ?>

	<?php if ( $args['featured'] ) : ?>
		<div class="imp-newslist imp-newslist--featured">
			<?php
			foreach ( $args['featured'] as $card ) {
				imp_component( 'news-card', $card );
			}
			?>
		</div>

		<?php if ( $args['more'] ) : ?>
			<div class="imp-newspage__divider">
				<?php if ( $args['next_url'] ) : ?>
					<a class="imp-newspage__more" href="<?php echo esc_url( $args['next_url'] ); ?>"><?php echo esc_html_x( 'مشاهده اخبارها', 'news page', 'imp' ); ?></a>
				<?php endif; ?>
			</div>
			<div class="imp-newslist imp-newslist--grid">
				<?php
				foreach ( $args['more'] as $card ) {
					imp_component( 'news-card', $card );
				}
				?>
			</div>
		<?php endif; ?>

		<?php
		imp_component(
			'pagination',
			array(
				'links' => $args['pagination'],
				'label' => __( 'News pages', 'imp' ),
			)
		);
		?>
	<?php else : ?>
		<p class="imp-empty"><?php echo esc_html_x( 'هنوز خبری منتشر نشده است.', 'news page', 'imp' ); ?></p>
	<?php endif; ?>
</main>
