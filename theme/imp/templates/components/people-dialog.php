<?php
/**
 * "مشاهده همه" overlay: one group as full cards, on the blue lion background.
 * Figma: Dabir / Sokhangoo (mobile About overlays).
 *
 * @package IMP
 *
 * @var array $args { group: array{slug,title,people}, contact_url: string, socials: array }
 */

defined( 'ABSPATH' ) || exit;

$group = $args['group'];
?>
<dialog class="imp-people-dialog" id="<?php echo esc_attr( $group['slug'] ); ?>" aria-labelledby="<?php echo esc_attr( $group['slug'] ); ?>-title">
	<div class="imp-people-dialog__inner">
		<?php
		imp_component(
			'close-button',
			array(
				'class' => 'imp-people-dialog__close',
				'data'  => 'data-imp-dialog-close',
			)
		);
		?>
		<div class="imp-otitle">
			<img class="imp-otitle__orn" src="<?php echo esc_url( imp_asset( 'img/ornament.svg' ) ); ?>" alt="" aria-hidden="true">
			<h2 class="imp-otitle__text" id="<?php echo esc_attr( $group['slug'] ); ?>-title"><?php echo esc_html( $group['title'] ); ?></h2>
			<img class="imp-otitle__orn imp-otitle__orn--flip" src="<?php echo esc_url( imp_asset( 'img/ornament.svg' ) ); ?>" alt="" aria-hidden="true">
		</div>
		<ul class="imp-people-dialog__cards">
			<?php
			foreach ( $group['people'] as $person ) {
				imp_component( 'person-card', $person );
			}
			?>
		</ul>
		<a class="imp-people-dialog__contact" href="<?php echo esc_url( $args['contact_url'] ); ?>"><?php echo esc_html_x( 'راه ارتباط با حزب', 'about', 'imp' ); ?></a>
		<div class="imp-people-dialog__follow">
			<p>FOLLOW US</p>
			<?php imp_component( 'social-list', array( 'socials' => $args['socials'] ) ); ?>
			<p class="imp-people-dialog__copy">© <?php echo esc_html( gmdate( 'Y' ) ); ?> All rights reserved to this website belong to the Iranian Monarchy Party</p>
		</div>
	</div>
</dialog>
