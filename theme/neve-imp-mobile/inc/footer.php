<?php
/**
 * Mobile footer: "follow us" icons + copyright. Printed at wp_footer (after Neve's .wrapper).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_footer', function () {
	?>
	<footer class="imp-m imp-m-footer">
		<ul class="imp-m-social">
			<?php foreach ( imp_m_socials() as $key => $social ) : ?>
				<li>
					<a href="<?php echo esc_url( $social[1] ); ?>" target="_blank" rel="noopener">
						<span class="screen-reader-text"><?php echo esc_html( $social[0] ); ?></span>
						<?php imp_m_icon( $key ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<p class="imp-m-footer__copy">© <?php echo esc_html( gmdate( 'Y' ) ); ?> All rights reserved to this website belong to the Iranian Monarchy Party</p>
	</footer>
	<?php
}, 1 );
