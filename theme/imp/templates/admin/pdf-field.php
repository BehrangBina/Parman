<?php
/**
 * Admin: "PDF file" box (see IMP\Admin\Pdf_Meta_Field).
 *
 * @package IMP
 *
 * @var array $args { url: string, is_issue: bool }
 */

defined( 'ABSPATH' ) || exit;
?>
<p>
	<input type="url" class="widefat" id="imp-pdf-url" name="imp_pdf" value="<?php echo esc_attr( $args['url'] ); ?>" placeholder="https://…/file.pdf" dir="ltr">
</p>
<p><button type="button" class="button" id="imp-pdf-pick">انتخاب از کتابخانه رسانه</button></p>
<p class="description">
	<?php if ( $args['is_issue'] ) : ?>
		عنوان = «شماره ۱»، چکیده = توضیح کوتاه، تاریخ انتشار = تاریخ نشریه روی کارت.
	<?php else : ?>
		خالی بگذارید تا PDF داخل صفحه (مثلاً فلیپ‌بوک) به‌طور خودکار استفاده شود.
	<?php endif; ?>
</p>
