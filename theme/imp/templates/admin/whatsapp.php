<?php
/**
 * Tools → IMP WhatsApp: mode, configuration status and the last messages.
 *
 * @package IMP
 *
 * @var array $args {
 *     live: bool, office: int, templates: array, log: array[], action: string, result: string
 * }
 */

defined( 'ABSPATH' ) || exit;

$statuses = array(
	'test'    => 'آزمایشی (ارسال نشد)',
	'sent'    => 'ارسال شد',
	'failed'  => 'ناموفق',
	'skipped' => 'رد شد',
);
$kinds    = array(
	'office'    => 'دبیرخانه',
	'applicant' => 'متقاضی',
);
?>
<div class="wrap">
	<h1>پیام‌های واتساپ هموندی</h1>

	<?php if ( 'sent' === $args['result'] ) : ?>
		<div class="notice notice-success"><p>پیام آزمایشی ثبت شد.</p></div>
	<?php elseif ( 'failed' === $args['result'] ) : ?>
		<div class="notice notice-error"><p>ارسال ناموفق بود؛ جزئیات در جدول زیر.</p></div>
	<?php elseif ( 'nonumbers' === $args['result'] ) : ?>
		<div class="notice notice-warning"><p>هیچ شماره‌ای برای دبیرخانه تعریف نشده است (IMP_WHATSAPP_OFFICE).</p></div>
	<?php endif; ?>

	<?php if ( ! $args['live'] ) : ?>
		<div class="notice notice-info"><p><strong>حالت آزمایشی:</strong> پیامی ارسال نمی‌شود و فقط در جدول زیر نمایش داده می‌شود. برای ارسال واقعی، IMP_WHATSAPP_TOKEN و IMP_WHATSAPP_PHONE_ID را در wp-config.php تعریف کنید.</p></div>
	<?php endif; ?>

	<table class="form-table" role="presentation">
		<tr><th scope="row">حالت</th><td><?php echo esc_html( $args['live'] ? 'فعال (ارسال واقعی)' : 'آزمایشی' ); ?></td></tr>
		<tr><th scope="row">شماره‌های دبیرخانه</th><td><?php echo (int) $args['office']; ?></td></tr>
		<?php foreach ( $args['templates'] as $kind => $template ) : ?>
			<tr><th scope="row">قالب پیام — <?php echo esc_html( $kinds[ $kind ] ?? $kind ); ?></th><td><code><?php echo esc_html( $template ); ?></code></td></tr>
		<?php endforeach; ?>
	</table>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="<?php echo esc_attr( $args['action'] ); ?>">
		<?php wp_nonce_field( $args['action'] ); ?>
		<?php submit_button( 'ارسال پیام آزمایشی به دبیرخانه', 'secondary', 'submit', false ); ?>
	</form>

	<h2>آخرین پیام‌ها</h2>
	<table class="widefat striped">
		<thead><tr><th>زمان</th><th>گیرنده</th><th>شماره</th><th>وضعیت</th><th>قالب / خطا</th></tr></thead>
		<tbody>
			<?php if ( ! $args['log'] ) : ?>
				<tr><td colspan="5">هنوز پیامی ثبت نشده است.</td></tr>
			<?php endif; ?>
			<?php foreach ( $args['log'] as $item ) : ?>
				<tr>
					<td><?php echo esc_html( $item['time'] ?? '' ); ?></td>
					<td><?php echo esc_html( $kinds[ $item['kind'] ?? '' ] ?? '' ); ?></td>
					<td dir="ltr"><?php echo esc_html( $item['to'] ?? '' ); ?></td>
					<td><?php echo esc_html( $statuses[ $item['status'] ?? '' ] ?? '' ); ?></td>
					<td><?php echo esc_html( $item['note'] ?? '' ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
