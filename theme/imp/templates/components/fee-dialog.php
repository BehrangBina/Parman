<?php
/**
 * "درباره هزینه هموندی" popup. Figma: Overlay-MembershipFee (966:3055, desktop 1181:6254).
 * The text comes from the membership-fee page (IMP\Data\Fee_Content).
 *
 * @package IMP
 *
 * @var array $args { html: string }
 */

defined( 'ABSPATH' ) || exit;
?>
<dialog class="imp-dialog" id="imp-fee-dialog" aria-labelledby="imp-fee-title">
	<?php // Padding lives on the inner box, so a tap on the <dialog> itself means "backdrop" (closes). ?>
	<div class="imp-dialog__inner">
		<?php
		imp_component(
			'close-button',
			array(
				'class' => 'imp-dialog__close',
				'icon'  => 'close-thin',
				'data'  => 'data-imp-dialog-close',
			)
		);
		?>
		<h2 class="screen-reader-text" id="imp-fee-title"><?php echo esc_html_x( 'درباره هزینه هموندی', 'donate', 'imp' ); ?></h2>
		<div class="imp-dialog__body">
			<?php if ( $args['html'] ) : ?>
				<?php echo wp_kses_post( $args['html'] ); ?>
			<?php else : ?>
				<p><?php echo esc_html_x( 'اطلاعات هزینه هموندی به‌زودی در این بخش قرار می‌گیرد.', 'donate', 'imp' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</dialog>
