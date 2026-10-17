<?php
/**
 * Party member card: photo, name, role, Instagram / X.
 * Figma: profile card (mobile About 975:4217, overlays Dabir/Sokhangoo, desktop About 1153:292).
 *
 * @package IMP
 *
 * @var array $args { name, role, photo, instagram, x, variant?: "card"|"mini"|"circle" }
 */

defined( 'ABSPATH' ) || exit;

$variant = isset( $args['variant'] ) ? $args['variant'] : 'card';
$links   = array_filter(
	array(
		// Figma card: outline icons (Lucide "instagram", ISC; X mark, Simple Icons, CC0).
		'instagram-line' => array( 'Instagram', $args['instagram'] ),
		'x-line'         => array( 'X', $args['x'] ),
	),
	function ( $link ) {
		return '' !== $link[1];
	}
);
?>
<li class="imp-person imp-person--<?php echo esc_attr( $variant ); ?>">
	<div class="imp-person__photo<?php echo $args['photo'] ? '' : ' imp-person__photo--empty'; ?>">
		<?php if ( $args['photo'] ) : ?>
			<img src="<?php echo esc_url( $args['photo'] ); ?>" alt="" loading="lazy" decoding="async">
		<?php else : ?>
			<?php imp_icon( 'user' ); ?>
		<?php endif; ?>
	</div>
	<div class="imp-person__text">
		<p class="imp-person__name notranslate"><?php echo esc_html( $args['name'] ); ?></p>
		<?php if ( 'circle' !== $variant && '' !== $args['role'] ) : ?>
			<p class="imp-person__role"><?php echo esc_html( $args['role'] ); ?></p>
		<?php endif; ?>
		<?php if ( 'card' === $variant && $links ) : ?>
			<ul class="imp-person__social">
				<?php foreach ( $links as $icon => $link ) : ?>
					<li>
						<a href="<?php echo esc_url( $link[1] ); ?>"<?php echo '#' === $link[1] ? '' : ' target="_blank" rel="noopener"'; ?>>
							<span class="screen-reader-text"><?php echo esc_html( $link[0] . ' — ' . $args['name'] ); ?></span>
							<?php imp_icon( $icon ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</li>
