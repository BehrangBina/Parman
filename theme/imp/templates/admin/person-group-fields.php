<?php
/**
 * Group (اعضای حزب → گروه‌ها) fields: display order and phone layout.
 * Rendered in core's add form (divs) or edit form (table rows).
 *
 * @package IMP
 *
 * @var array $args { order: int, mobile: string, styles: array<string,string>, table: bool }
 */

defined( 'ABSPATH' ) || exit;

$order_input  = '<input type="number" id="imp-group-order" name="imp_group_order" value="' . esc_attr( (string) $args['order'] ) . '" class="small-text">';
$mobile_input = '<select id="imp-group-mobile" name="imp_group_mobile">';
foreach ( $args['styles'] as $value => $label ) {
	$mobile_input .= '<option value="' . esc_attr( $value ) . '"' . selected( $args['mobile'], $value, false ) . '>' . esc_html( $label ) . '</option>';
}
$mobile_input .= '</select>';
$allowed       = array(
	'input'  => array( 'type' => true, 'id' => true, 'name' => true, 'value' => true, 'class' => true ),
	'select' => array( 'id' => true, 'name' => true ),
	'option' => array( 'value' => true, 'selected' => true ),
);
?>
<?php if ( $args['table'] ) : ?>
	<tr class="form-field">
		<th scope="row"><label for="imp-group-order">ترتیب</label></th>
		<td><?php echo wp_kses( $order_input, $allowed ); ?><p class="description">گروه با عدد کوچک‌تر بالاتر نمایش داده می‌شود.</p></td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="imp-group-mobile">نمایش در موبایل</label></th>
		<td><?php echo wp_kses( $mobile_input, $allowed ); ?></td>
	</tr>
<?php else : ?>
	<div class="form-field">
		<label for="imp-group-order">ترتیب</label>
		<?php echo wp_kses( $order_input, $allowed ); ?>
		<p>گروه با عدد کوچک‌تر بالاتر نمایش داده می‌شود.</p>
	</div>
	<div class="form-field">
		<label for="imp-group-mobile">نمایش در موبایل</label>
		<?php echo wp_kses( $mobile_input, $allowed ); ?>
	</div>
<?php endif; ?>
