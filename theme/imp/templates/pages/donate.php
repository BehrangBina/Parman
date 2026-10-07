<?php
/**
 * Hamyari (donate). Figma: mobile Hamyari (697:2603), desktop Hamyari (1181:5330).
 *
 * @package IMP
 *
 * @var array $args { title, membership_form_url, donorbox_membership, donorbox_donation, fee_html }
 */

defined( 'ABSPATH' ) || exit;
?>
<main class="imp-ui imp-page imp-donate-page">
	<?php imp_component( 'ornament-title', array( 'title' => $args['title'] ) ); ?>

	<?php // Figma: "پرداخت حق هموندی" block (Icon/hand-coins 1023:3984 … Button 1023:3973). ?>
	<section class="imp-block">
		<span class="imp-block__icon"><?php imp_icon( 'hand-coins' ); ?></span>
		<h2 class="imp-block__title"><?php echo esc_html_x( 'پرداخت حق هموندی', 'donate', 'imp' ); ?></h2>
		<p class="imp-block__text">
			<?php echo esc_html_x( 'برای ساماندهی بهتر امور تشکیلاتی و برنامه‌ریزی آینده، خواهشمندیم فرم پرداخت حق هموندی را تکمیل فرمایید.', 'donate', 'imp' ); ?><br>
			<?php echo esc_html_x( 'سپاس از همراهی و احساس مسئولیت شما در مسیر خدمت به ایران', 'donate', 'imp' ); ?>
		</p>

		<?php // Figma: Fee-Info (1023:3996) — opens the Overlay-MembershipFee popup. ?>
		<button class="imp-info" type="button" data-imp-dialog="imp-fee-dialog">
			<span class="imp-info__icon"><?php imp_icon( 'info' ); ?></span>
			<?php echo esc_html_x( 'درباره هزینه هموندی', 'donate', 'imp' ); ?>
		</button>

		<a class="imp-btn imp-btn--gold" href="<?php echo esc_url( $args['membership_form_url'] ); ?>"><?php echo esc_html_x( 'تکمیل فرم', 'donate', 'imp' ); ?></a>
	</section>

	<?php // Figma: "کمک‌های مالی" block (Icon-wallet 1023:3966 …). ?>
	<section class="imp-block imp-block--spaced">
		<span class="imp-block__icon"><?php imp_icon( 'wallet' ); ?></span>
		<h2 class="imp-block__title"><?php echo esc_html_x( 'کمک‌های مالی', 'donate', 'imp' ); ?></h2>
		<a class="imp-textlink" href="<?php echo esc_url( $args['donorbox_membership'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html_x( 'لطفا اینجا کلیک‌کنید', 'donate', 'imp' ); ?></a>

		<p class="imp-block__text imp-block__text--gap">
			<?php echo esc_html_x( 'ازکنش‌ها، برنامه‌ها و سازوکارهای پارمان(حزب)، میتوانید از طریق سامانه امن', 'donate', 'imp' ); ?>
			<span dir="ltr">“donorbox”</span><br>
			<?php echo esc_html_x( 'در خارج از کشور، پارمان را یاری نمایید.', 'donate', 'imp' ); ?>
		</p>
		<a class="imp-textlink" href="<?php echo esc_url( $args['donorbox_donation'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html_x( 'لطفا اینجا کلیک‌کنید', 'donate', 'imp' ); ?></a>
	</section>

	<?php imp_component( 'fee-dialog', array( 'html' => $args['fee_html'] ) ); ?>
</main>
