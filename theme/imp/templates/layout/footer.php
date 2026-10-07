<?php
/**
 * Footer: social icons, copyright and (desktop) the "back to top" arrow.
 * Figma: Footer-Follow (mobile 881:13013), desktop Icons SocialMedia + Go-Up (1225:8568, 1124:3722).
 *
 * @package IMP
 *
 * @var array $args { show_socials: bool, socials: array, year: string }
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="imp-ui imp-footer">
	<?php
	if ( $args['show_socials'] ) {
		imp_component( 'social-list', array( 'socials' => $args['socials'] ) );
	}
	?>
	<p class="imp-footer__copy">© <?php echo esc_html( $args['year'] ); ?> All rights reserved to this website belong to the <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Iranian Monarchy Party</a></p>
	<button class="imp-footer__top" type="button" data-imp-top>
		<span class="screen-reader-text"><?php esc_html_e( 'Back to top', 'imp' ); ?></span>
		<?php imp_icon( 'go-up' ); ?>
	</button>
</footer>
