<?php
/**
 * Small "your information is safe" line under the membership form's submit button.
 * Figma: Form-Hamvandi footer note (740:2198).
 *
 * @package IMP
 */

defined( 'ABSPATH' ) || exit;
?>
<p class="imp-form-safe">
	<?php imp_icon( 'lock' ); ?>
	<?php echo esc_html_x( 'اطلاعات شما نزد ما محفوظ است', 'membership form', 'imp' ); ?>
</p>
