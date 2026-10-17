<?php
/**
 * Language flags (فارسی / English / Deutsch) from the GTranslate plugin, as on the live site.
 * Translation happens in the visitor's browser; assets/js/modules/language.js turns the page
 * left-to-right for English and German. Renders nothing when GTranslate is not active.
 *
 * @package IMP
 *
 * @var array $args { class: string }
 */

defined( 'ABSPATH' ) || exit;

if ( ! shortcode_exists( 'gtranslate' ) ) {
	return;
}
?>
<div class="imp-lang notranslate <?php echo esc_attr( $args['class'] ); ?>">
	<?php echo do_shortcode( '[gtranslate]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- GTranslate's own widget markup. ?>
</div>
