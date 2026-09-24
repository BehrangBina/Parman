<?php
/**
 * Mobile footer: "follow us" icons + copyright. Printed at wp_footer (after Neve's .wrapper).
 * The Contact page already shows the icons at the top, so the footer omits them there.
 */

defined( 'ABSPATH' ) || exit;

function imp_m_social_list( $class = 'imp-m-social' ) {
	?>
	<ul class="<?php echo esc_attr( $class ); ?>">
		<?php foreach ( imp_m_socials() as $key => $social ) : ?>
			<li>
				<a href="<?php echo esc_url( $social[1] ); ?>" target="_blank" rel="noopener">
					<span class="screen-reader-text"><?php echo esc_html( $social[0] ); ?></span>
					<?php imp_m_icon( $key ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

add_action( 'wp_footer', function () {
	$is_contact = 'imp_m_render_contact' === imp_m_current_renderer();
	?>
	<footer class="imp-m imp-m-footer">
		<?php
		if ( ! $is_contact ) {
			imp_m_social_list();
		}
		?>
		<p class="imp-m-footer__copy">© <?php echo esc_html( gmdate( 'Y' ) ); ?> All rights reserved to this website belong to the <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Iranian Monarchy Party</a></p>
	</footer>
	<?php
}, 1 );
