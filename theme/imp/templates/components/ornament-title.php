<?php
/**
 * Page title between two gold ornaments. Figma: Text-About (740:2162).
 *
 * @package IMP
 *
 * @var array $args { title: string, tag?: string (default h1), id?: string }
 */

defined( 'ABSPATH' ) || exit;

$tag      = tag_escape( isset( $args['tag'] ) ? $args['tag'] : 'h1' );
$ornament = imp_asset( 'img/ornament.svg' );
?>
<div class="imp-otitle"<?php echo ! empty( $args['id'] ) ? ' id="' . esc_attr( $args['id'] ) . '"' : ''; ?>>
	<img class="imp-otitle__orn" src="<?php echo esc_url( $ornament ); ?>" alt="" aria-hidden="true">
	<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- tag_escape(). ?> class="imp-otitle__text"><?php echo esc_html( $args['title'] ); ?></<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<img class="imp-otitle__orn imp-otitle__orn--flip" src="<?php echo esc_url( $ornament ); ?>" alt="" aria-hidden="true">
</div>
