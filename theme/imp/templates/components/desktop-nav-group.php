<?php
/**
 * One half of the desktop menu (items on one side of the logo) with dropdowns.
 * Figma: desktop nav items + dropdown states (Components 1181:6348).
 *
 * Parent items whose link is "#" are pure toggles (e.g. "رسانه"); others are a link plus a
 * chevron button. Dropdowns open on hover (CSS) and on click/keyboard (assets JS).
 *
 * @package IMP
 *
 * @var array $args { entries: array[] from Menu_Tree::tree(), side: 'start'|'end' }
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['entries'] ) ) {
	return;
}

$link_attrs = static function ( WP_Post $item ) {
	return $item->target ? ' target="' . esc_attr( $item->target ) . '" rel="noopener"' : '';
};
?>
<ul class="imp-d-nav imp-d-nav--<?php echo esc_attr( $args['side'] ); ?>">
	<?php
	foreach ( $args['entries'] as $entry ) :
		$item        = $entry['item'];
		$has_sub     = ! empty( $entry['children'] );
		$sub_id      = 'imp-d-sub-' . $item->ID;
		$toggle_only = $has_sub && in_array( $item->url, array( '#', '' ), true );
		$classes     = 'imp-d-nav__item' . ( $has_sub ? ' has-sub' : '' ) . ( $entry['current'] ? ' is-current' : '' );
		?>
		<li class="<?php echo esc_attr( $classes ); ?>">
			<?php if ( $toggle_only ) : ?>
				<button class="imp-d-nav__link imp-d-nav__toggle" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $sub_id ); ?>">
					<?php echo esc_html( $item->title ); ?>
					<?php imp_icon( 'dropdown-white' ); ?>
				</button>
			<?php else : ?>
				<a class="imp-d-nav__link" href="<?php echo esc_url( $item->url ); ?>"<?php echo $link_attrs( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in $link_attrs. ?>>
					<?php echo esc_html( $item->title ); ?>
				</a>
				<?php if ( $has_sub ) : ?>
					<button class="imp-d-nav__toggle imp-d-nav__toggle--icon" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $sub_id ); ?>">
						<span class="screen-reader-text"><?php echo esc_html( $item->title ); ?></span>
						<?php imp_icon( 'dropdown-white' ); ?>
					</button>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( $has_sub ) : ?>
				<ul class="imp-d-nav__sub" id="<?php echo esc_attr( $sub_id ); ?>">
					<?php foreach ( $entry['children'] as $child ) : ?>
						<li><a href="<?php echo esc_url( $child->url ); ?>"<?php echo $link_attrs( $child ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in $link_attrs. ?>><?php echo esc_html( $child->title ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
