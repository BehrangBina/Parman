<?php
/**
 * Mobile "Hamyari" (donate) page — Figma frame 697:2603. Registered in inc/pages.php.
 *
 * Donorbox links are filterable via `imp_links` (keys: donorbox_membership, donorbox_donation).
 * The "about the membership fee" popup shows the content of the page with slug
 * `membership-fee` when it exists, so admins can edit it in WordPress.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'imp_links', function ( $links ) {
	return $links + array(
		'membership_form'     => imp_page_url( 'membership-form' ), // Fluent Forms page (Figma Form-Hamvandi)
		'donorbox_membership' => 'https://donorbox.org/membership-930550',
		'donorbox_donation'   => 'https://donorbox.org/donation-930545',
	);
}, 5 );

function imp_render_donate() {
	$links = imp_links();
	?>
	<main class="imp-ui imp-page imp-donate-page">
		<?php imp_ornament_title( get_the_title( get_queried_object_id() ) ); ?>

		<section class="imp-block">
			<span class="imp-block__icon"><?php imp_icon( 'hand-coins' ); ?></span>
			<h2 class="imp-block__title"><?php echo esc_html_x( 'پرداخت حق هموندی', 'donate', 'imp' ); ?></h2>
			<p class="imp-block__text">
				<?php echo esc_html_x( 'برای ساماندهی بهتر امور تشکیلاتی و برنامه‌ریزی آینده، خواهشمندیم فرم پرداخت حق هموندی را تکمیل فرمایید.', 'donate', 'imp' ); ?><br>
				<?php echo esc_html_x( 'سپاس از همراهی و احساس مسئولیت شما در مسیر خدمت به ایران', 'donate', 'imp' ); ?>
			</p>

			<button class="imp-info" type="button" data-imp-dialog="imp-fee-dialog">
				<span class="imp-info__icon"><?php imp_icon( 'info' ); ?></span>
				<?php echo esc_html_x( 'درباره هزینه هموندی', 'donate', 'imp' ); ?>
			</button>

			<a class="imp-btn imp-btn--gold" href="<?php echo esc_url( $links['membership_form'] ); ?>"><?php echo esc_html_x( 'تکمیل فرم', 'donate', 'imp' ); ?></a>
		</section>

		<section class="imp-block imp-block--spaced">
			<span class="imp-block__icon"><?php imp_icon( 'wallet' ); ?></span>
			<h2 class="imp-block__title"><?php echo esc_html_x( 'کمک‌های مالی', 'donate', 'imp' ); ?></h2>
			<a class="imp-textlink" href="<?php echo esc_url( $links['donorbox_membership'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html_x( 'لطفا اینجا کلیک‌کنید', 'donate', 'imp' ); ?></a>

			<p class="imp-block__text imp-block__text--gap">
				<?php echo esc_html_x( 'ازکنش‌ها، برنامه‌ها و سازوکارهای پارمان(حزب)، میتوانید از طریق سامانه امن', 'donate', 'imp' ); ?>
				<span dir="ltr">“donorbox”</span><br>
				<?php echo esc_html_x( 'در خارج از کشور، پارمان را یاری نمایید.', 'donate', 'imp' ); ?>
			</p>
			<a class="imp-textlink" href="<?php echo esc_url( $links['donorbox_donation'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html_x( 'لطفا اینجا کلیک‌کنید', 'donate', 'imp' ); ?></a>
		</section>

		<?php imp_fee_dialog(); ?>
	</main>
	<?php
}

function imp_fee_dialog() {
	$page = get_page_by_path( 'membership-fee' );
	?>
	<dialog class="imp-dialog" id="imp-fee-dialog" aria-labelledby="imp-fee-title">
		<div class="imp-dialog__inner"><?php // padding lives here so a tap on the dialog element itself = backdrop. ?>
		<button class="imp-dialog__close" type="button" data-imp-dialog-close>
			<span class="screen-reader-text"><?php esc_html_e( 'Close', 'imp' ); ?></span>
			<?php imp_icon( 'close-thin' ); ?>
		</button>
		<h2 class="screen-reader-text" id="imp-fee-title"><?php echo esc_html_x( 'درباره هزینه هموندی', 'donate', 'imp' ); ?></h2>
		<div class="imp-dialog__body">
			<?php
			if ( $page ) {
				echo apply_filters( 'the_content', $page->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput -- post content.
			} else {
				echo '<p>' . esc_html_x( 'اطلاعات هزینه هموندی به‌زودی در این بخش قرار می‌گیرد.', 'donate', 'imp' ) . '</p>';
			}
			?>
		</div>
		</div>
	</dialog>
	<?php
}
