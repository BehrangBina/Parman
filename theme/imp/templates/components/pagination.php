<?php
/**
 * "‹ 1 2 3 ›" page links under the news and statements lists (not in Figma; site style).
 *
 * @package IMP
 *
 * @var array $args { links: string (core paginate_links() HTML), label: string }
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['links'] ) ) {
	return;
}
?>
<nav class="navigation pagination imp-pagination" aria-label="<?php echo esc_attr( $args['label'] ); ?>">
	<div class="nav-links"><?php echo wp_kses_post( $args['links'] ); ?></div>
</nav>
