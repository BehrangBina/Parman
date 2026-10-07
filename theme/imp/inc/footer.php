<?php
/**
 * Mobile footer: "follow us" icons + copyright. Printed at wp_footer (after Neve's .wrapper).
 * The Contact page already shows the icons at the top, so the footer omits them there.
 */

defined( 'ABSPATH' ) || exit;

function imp_social_list( $class = 'imp-social' ) {
	?>
	<ul class="<?php echo esc_attr( $class ); ?>">
		<?php foreach ( imp_socials() as $key => $social ) : ?>
			<li>
				<a href="<?php echo esc_url( $social[1] ); ?>" target="_blank" rel="noopener">
					<span class="screen-reader-text"><?php echo esc_html( $social[0] ); ?></span>
					<?php imp_icon( $key ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

add_action( 'wp_footer', function () {
	$is_contact = 'imp_render_contact' === imp_current_renderer();
	?>
	<footer class="imp-ui imp-footer">
		<?php
		if ( ! $is_contact ) {
			imp_social_list();
		}
		?>
		<p class="imp-footer__copy">© <?php echo esc_html( gmdate( 'Y' ) ); ?> All rights reserved to this website belong to the <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Iranian Monarchy Party</a></p>
		<?php // Desktop design: "back to top" arrow beside the social icons (hidden on mobile). ?>
		<button class="imp-footer__top" type="button" data-imp-top>
			<span class="screen-reader-text"><?php esc_html_e( 'Back to top', 'imp' ); ?></span>
			<?php imp_icon( 'go-up' ); ?>
		</button>
	</footer>
	<?php
}, 1 );
