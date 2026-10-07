<?php
/**
 * Social icons (footer and contact page). Figma: Follow-Us / Icons SocialMedia.
 *
 * @package IMP
 *
 * @var array $args { socials: array<string, array{0:string,1:string}>, class?: string }
 */

defined( 'ABSPATH' ) || exit;

$class = isset( $args['class'] ) ? $args['class'] : 'imp-social';
?>
<ul class="<?php echo esc_attr( $class ); ?>">
	<?php foreach ( $args['socials'] as $icon => $social ) : ?>
		<li>
			<a href="<?php echo esc_url( $social[1] ); ?>" target="_blank" rel="noopener">
				<span class="screen-reader-text"><?php echo esc_html( $social[0] ); ?></span>
				<?php imp_icon( $icon ); ?>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
