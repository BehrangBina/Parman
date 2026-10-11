<?php
/**
 * Person editor box: role and social links (IMP\Admin\Person_Post_Type).
 *
 * @package IMP
 *
 * @var array $args { values: array{role,instagram,x}, nonce: string }
 */

defined( 'ABSPATH' ) || exit;

$fields = array(
	'role'      => array( 'سمت', 'مثلاً دبیر نخست، سخنگو (خالی بگذارید اگر سمتی ندارد)', 'text' ),
	'instagram' => array( 'اینستاگرام', 'نشانی کامل صفحه، یا # تا بعداً', 'text' ),
	'x'         => array( 'X (توییتر)', 'نشانی کامل صفحه، یا # تا بعداً', 'text' ),
);
wp_nonce_field( $args['nonce'], $args['nonce'] );
?>
<table class="form-table" role="presentation">
	<?php foreach ( $fields as $key => $field ) : ?>
		<tr>
			<th scope="row"><label for="imp-person-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field[0] ); ?></label></th>
			<td>
				<input class="regular-text" type="<?php echo esc_attr( $field[2] ); ?>" id="imp-person-<?php echo esc_attr( $key ); ?>" name="imp_person_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $args['values'][ $key ] ); ?>"<?php echo 'role' === $key ? '' : ' dir="ltr"'; ?>>
				<p class="description"><?php echo esc_html( $field[1] ); ?></p>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
<p class="description">عکس را از «عکس» در ستون کنار انتخاب کنید؛ ترتیب نمایش با «ترتیب» (Order) تعیین می‌شود. بدون عکس، تصویر جایگزین نمایش داده می‌شود.</p>
