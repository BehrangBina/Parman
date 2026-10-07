<?php
/**
 * Round "✕" close button (menu, dialogs, reader). Figma: carbon:close-filled.
 *
 * @package IMP
 *
 * @var array $args {
 *     class: string,          CSS class(es).
 *     label?: string,         Screen-reader label (default "Close").
 *     icon?: string,          Icon file (default "close"; the fee dialog uses "close-thin").
 *     data?: string           One data attribute name, e.g. "data-imp-dialog-close".
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'label' => __( 'Close', 'imp' ),
		'icon'  => 'close',
		'data'  => '',
	)
);
?>
<button class="<?php echo esc_attr( $args['class'] ); ?>" type="button"<?php echo $args['data'] ? ' ' . esc_attr( $args['data'] ) : ''; ?>>
	<span class="screen-reader-text"><?php echo esc_html( $args['label'] ); ?></span>
	<?php imp_icon( $args['icon'] ); ?>
</button>
