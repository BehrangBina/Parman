<?php
/**
 * Tools → IMP membership digest: schedule status and a "send now" button.
 *
 * @package IMP
 *
 * @var array $args {
 *     recipient: string, next: string, last: string, overdue: bool, action: string, result: string
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1>گزارش هفتگی هموندی</h1>

	<?php if ( 'sent' === $args['result'] ) : ?>
		<div class="notice notice-success"><p>گزارش ارسال شد به <?php echo esc_html( $args['recipient'] ); ?>.</p></div>
	<?php elseif ( 'failed' === $args['result'] ) : ?>
		<div class="notice notice-error"><p>ارسال ناموفق بود. گزارش FluentSMTP را بررسی کنید.</p></div>
	<?php endif; ?>

	<?php if ( $args['overdue'] ) : ?>
		<div class="notice notice-warning"><p>زمان‌بندی وردپرس (WP-Cron) اجرا نمی‌شود: نوبت بعدی گذشته است. یک cron واقعی در سرور تنظیم کنید.</p></div>
	<?php endif; ?>

	<table class="form-table" role="presentation">
		<tr><th scope="row">گیرنده</th><td><?php echo esc_html( $args['recipient'] ); ?></td></tr>
		<tr><th scope="row">آخرین ارسال هفتگی</th><td><?php echo esc_html( $args['last'] ? $args['last'] : '—' ); ?></td></tr>
		<tr><th scope="row">بررسی بعدی</th><td><?php echo esc_html( $args['next'] ? $args['next'] : 'زمان‌بندی نشده' ); ?></td></tr>
	</table>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="<?php echo esc_attr( $args['action'] ); ?>">
		<?php wp_nonce_field( $args['action'] ); ?>
		<?php submit_button( 'ارسال گزارش همین حالا', 'secondary', 'submit', false ); ?>
	</form>
</div>
