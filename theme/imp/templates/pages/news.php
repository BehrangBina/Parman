<?php
/**
 * Akhbaar (news list). Figma: mobile Akhbaar (697:2647), desktop News (1215:5585).
 *
 * @package IMP
 *
 * @var array $args { cards: array[], pagination: string }  (IMP\Controllers\News_Controller)
 */

defined( 'ABSPATH' ) || exit;
?>
<main class="imp-ui imp-page imp-newspage">
	<?php imp_component( 'ornament-title', array( 'title' => _x( 'آخرین اخبار', 'news page', 'imp' ) ) ); ?>

	<?php if ( $args['cards'] ) : ?>
		<div class="imp-newslist">
			<?php
			foreach ( $args['cards'] as $card ) {
				imp_component( 'news-card', $card );
			}
			?>
		</div>
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
