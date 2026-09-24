<?php
/**
 * Mobile "Hamyari" (donate) page — Figma frame 697:2603. Registered in inc/pages.php.
 *
 * Donorbox links are filterable via `imp_m_links` (keys: donorbox_membership, donorbox_donation).
 * The "about the membership fee" popup shows the content of the page with slug
 * `membership-fee` when it exists, so admins can edit it in WordPress.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'imp_m_links', function ( $links ) {
	return $links + array(
		'membership_form'     => imp_m_page_url( 'membership-form' ), // Fluent Forms page (Figma Form-Hamvandi)
		'donorbox_membership' => 'https://donorbox.org/membership-930550',
		'donorbox_donation'   => 'https://donorbox.org/donation-930545',
	);
}, 5 );

function imp_m_render_donate() {
	$links = imp_m_links();
	?>
	<main class="imp-m imp-m-page imp-m-donate-page">
		<?php imp_m_ornament_title( get_the_title( get_queried_object_id() ) ); ?>

		<section class="imp-m-block">
			<span class="imp-m-block__icon"><?php imp_m_icon( 'hand-coins' ); ?></span>
			<h2 class="imp-m-block__title"><?php echo esc_html_x( 'پرداخت حق هموندی', 'donate', 'imp-mobile' ); ?></h2>
			<p class="imp-m-block__text">
				<?php echo esc_html_x( 'برای ساماندهی بهتر امور تشکیلاتی و برنامه‌ریزی آینده، خواهشمندیم فرم پرداخت حق هموندی را تکمیل فرمایید.', 'donate', 'imp-mobile' ); ?><br>
				<?php echo esc_html_x( 'سپاس از همراهی و احساس مسئولیت شما در مسیر خدمت به ایران', 'donate', 'imp-mobile' ); ?>
			</p>

			<button class="imp-m-info" type="button" data-imp-dialog="imp-m-fee-dialog">
				<span class="imp-m-info__icon"><?php imp_m_icon( 'info' ); ?></span>
				<?php echo esc_html_x( 'درباره هزینه هموندی', 'donate', 'imp-mobile' ); ?>
			</button>

			<a class="imp-m-btn imp-m-btn--gold" href="<?php echo esc_url( $links['membership_form'] ); ?>"><?php echo esc_html_x( 'تکمیل فرم', 'donate', 'imp-mobile' ); ?></a>
		</section>

		<section class="imp-m-block imp-m-block--spaced">
			<span class="imp-m-block__icon"><?php imp_m_icon( 'wallet' ); ?></span>
			<h2 class="imp-m-block__title"><?php echo esc_html_x( 'کمک‌های مالی', 'donate', 'imp-mobile' ); ?></h2>
			<a class="imp-m-textlink" href="<?php echo esc_url( $links['donorbox_donation'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html_x( 'لطفا اینجا کلیک‌کنید', 'donate', 'imp-mobile' ); ?></a>

			<p class="imp-m-block__text imp-m-block__text--gap">
				<?php echo esc_html_x( 'ازکنش‌ها، برنامه‌ها و سازوکارهای پارمان(حزب)، میتوانید از طریق سامانه امن', 'donate', 'imp-mobile' ); ?>
				<span dir="ltr">“donorbox”</span><br>
				<?php echo esc_html_x( 'در خارج از کشور، پارمان را یاری نمایید.', 'donate', 'imp-mobile' ); ?>
			</p>
			<a class="imp-m-textlink" href="<?php echo esc_url( $links['donorbox_donation'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html_x( 'لطفا اینجا کلیک‌کنید', 'donate', 'imp-mobile' ); ?></a>
		</section>

		<?php imp_m_fee_dialog(); ?>
	</main>
	<?php
}

function imp_m_fee_dialog() {
	$page = get_page_by_path( 'membership-fee' );
	?>
	<dialog class="imp-m-dialog" id="imp-m-fee-dialog" aria-labelledby="imp-m-fee-title">
		<div class="imp-m-dialog__inner"><?php // padding lives here so a tap on the dialog element itself = backdrop. ?>
		<button class="imp-m-dialog__close" type="button" data-imp-dialog-close>
			<span class="screen-reader-text"><?php esc_html_e( 'Close', 'imp-mobile' ); ?></span>
			<?php imp_m_icon( 'close' ); ?>
		</button>
		<h2 class="imp-m-dialog__title" id="imp-m-fee-title"><?php echo esc_html_x( 'درباره هزینه هموندی', 'donate', 'imp-mobile' ); ?></h2>
		<div class="imp-m-dialog__body">
			<?php
			if ( $page ) {
				echo apply_filters( 'the_content', $page->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput -- post content.
			} else {
				echo '<p>' . esc_html_x( 'اطلاعات هزینه هموندی به‌زودی در این بخش قرار می‌گیرد.', 'donate', 'imp-mobile' ) . '</p>';
			}
			?>
		</div>
		</div>
	</dialog>
	<?php
}
