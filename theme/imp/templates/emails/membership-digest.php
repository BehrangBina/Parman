<?php
/**
 * Weekly membership digest email. Inline styles on purpose: email clients ignore
 * stylesheets, so this is the one template that does not use the theme CSS.
 *
 * @package IMP
 *
 * @var array $args {
 *     from: string, to: string,
 *     entries: array<array{id:int,date:string,name:string,fee:string,answers:array,url:string}>
 * }
 */

defined( 'ABSPATH' ) || exit;

$th_style  = 'border:1px solid #ddd;padding:6px 10px;background:#f5f5f5;width:34%;vertical-align:top;';
$td_style  = 'border:1px solid #ddd;padding:6px 10px;vertical-align:top;';
$fee_color = array(
	'بله' => '#1a7f37',
	'خیر' => '#b3261e',
);
?>
<div dir="rtl" lang="fa" style="font-family:Tahoma,Arial,sans-serif;direction:rtl;text-align:right;">
	<h2 style="margin:0 0 4px;">گزارش هفتگی درخواست‌های هموندی</h2>
	<p style="margin:0 0 18px;color:#666;font-size:13px;">
		بازه: از <?php echo esc_html( $args['from'] ); ?> تا <?php echo esc_html( $args['to'] ); ?>
		&nbsp;|&nbsp; تعداد: <?php echo (int) count( $args['entries'] ); ?>
	</p>

	<?php if ( ! $args['entries'] ) : ?>
		<p style="padding:12px;background:#f5f5f5;border:1px solid #ddd;">در این بازه درخواست هموندی جدیدی ثبت نشده است.</p>
	<?php endif; ?>

	<?php foreach ( $args['entries'] as $index => $entry ) : ?>
		<h3 style="margin:0 0 6px;">
			<?php echo (int) $index + 1; ?>. <?php echo esc_html( $entry['name'] ); ?>
			<span style="font-weight:normal;color:#888;font-size:12px;">(<?php echo esc_html( $entry['date'] ); ?>)</span>
			<?php if ( '' !== $entry['fee'] ) : ?>
				<span style="font-size:12px;font-weight:normal;color:<?php echo esc_attr( $fee_color[ $entry['fee'] ] ?? '#8a6d00' ); ?>;">— حق هموندی: <?php echo esc_html( $entry['fee'] ); ?></span>
			<?php endif; ?>
		</h3>
		<table style="border-collapse:collapse;width:100%;margin:0 0 28px;font-size:13px;"><tbody>
			<?php foreach ( $entry['answers'] as $answer ) : ?>
				<tr>
					<th style="<?php echo esc_attr( $th_style ); ?>"><?php echo esc_html( $answer['label'] ); ?></th>
					<td style="<?php echo esc_attr( $td_style ); ?>"><?php echo nl2br( esc_html( $answer['value'] ) ); ?></td>
				</tr>
			<?php endforeach; ?>
			<tr>
				<th style="<?php echo esc_attr( $th_style ); ?>">پرونده در سامانه</th>
				<td style="<?php echo esc_attr( $td_style ); ?>"><a href="<?php echo esc_url( $entry['url'] ); ?>">مشاهده درخواست #<?php echo (int) $entry['id']; ?></a></td>
			</tr>
		</tbody></table>
	<?php endforeach; ?>
</div>
