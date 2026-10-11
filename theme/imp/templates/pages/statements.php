<?php
/**
 * Bayanie (statements list). Figma: mobile Bayanie (1009:3003), desktop Bayanie (1215:6745).
 *
 * @package IMP
 *
 * @var array $args { title, cards: array[], pagination: string }  (IMP\Controllers\Statements_Controller)
 */

defined( 'ABSPATH' ) || exit;
?>
<main class="imp-ui imp-page imp-statements">
	<?php imp_component( 'ornament-title', array( 'title' => $args['title'] ) ); ?>

	<?php if ( $args['cards'] ) : ?>
		<ul class="imp-slist">
			<?php foreach ( $args['cards'] as $card ) : ?>
				<li><?php imp_component( 'statement-card', $card ); ?></li>
			<?php endforeach; ?>
		</ul>
		<?php
		imp_component(
			'pagination',
			array(
				'links' => $args['pagination'],
				'label' => __( 'Statement pages', 'imp' ),
			)
		);
		?>
	<?php else : ?>
		<p class="imp-empty"><?php echo esc_html_x( 'هنوز بیانیه‌ای منتشر نشده است.', 'statements', 'imp' ); ?></p>
	<?php endif; ?>
</main>
