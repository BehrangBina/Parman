<?php
/**
 * "علاقه‌مند به هموندی هستید؟" form (home + contact page). Figma: Join-Us (715:4821),
 * desktop contact card (1250:9590). Submitted to IMP\Services\Contact_Mailer.
 *
 * @package IMP
 *
 * @var array $args { note_inside?: bool (contact page puts the note inside the card) }
 */

use IMP\Services\Contact_Mailer;

defined( 'ABSPATH' ) || exit;

$note_inside = ! empty( $args['note_inside'] );
$result      = Contact_Mailer::result();
$note        = _x( 'پس از بررسی اولیه، یکی از اعضای تیم با شما تماس خواهد گرفت', 'home contact', 'imp' );

// One labelled input with its icon on the start side.
$field = static function ( $name, $label, $icon, $type = 'text', $placeholder = '' ) {
	?>
	<label class="imp-field">
		<span class="imp-field__label"><?php echo esc_html( $label ); ?></span>
		<span class="imp-field__control">
			<input type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $name ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" required<?php echo 'email' === $type ? ' dir="ltr"' : ''; ?>>
			<?php imp_icon( $icon ); ?>
		</span>
	</label>
	<?php
};
?>
<form class="imp-contact__card" id="imp-contact" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<p class="imp-contact__heading"><?php echo esc_html_x( 'علاقه‌مند به هموندی هستید؟', 'home contact', 'imp' ); ?></p>
	<p class="imp-contact__sub"><?php echo esc_html_x( 'لطفاً اطلاعات زیر را وارد کنید', 'home contact', 'imp' ); ?></p>

	<?php if ( 'sent' === $result ) : ?>
		<p class="imp-contact__notice" role="status"><?php echo esc_html_x( 'پیام شما ارسال شد. سپاس!', 'home contact', 'imp' ); ?></p>
	<?php elseif ( 'error' === $result ) : ?>
		<p class="imp-contact__notice imp-contact__notice--error" role="alert"><?php echo esc_html_x( 'ارسال پیام ناموفق بود. لطفاً دوباره تلاش کنید.', 'home contact', 'imp' ); ?></p>
	<?php endif; ?>

	<div class="imp-contact__row">
		<?php $field( 'first_name', _x( 'نام', 'home contact', 'imp' ), 'user' ); ?>
		<?php $field( 'last_name', _x( 'نام خانوادگی', 'home contact', 'imp' ), 'user' ); ?>
	</div>
	<?php $field( 'email', _x( 'آدرس ایمیل', 'home contact', 'imp' ), 'mail', 'email', 'name@example.com' ); ?>
	<?php $field( 'country', _x( 'کشور', 'home contact', 'imp' ), 'map-pin' ); ?>
	<label class="imp-field">
		<span class="imp-field__label"><?php echo esc_html_x( 'پیام', 'home contact', 'imp' ); ?></span>
		<span class="imp-field__control imp-field__control--area">
			<textarea name="message" rows="4" placeholder="<?php echo esc_attr_x( 'پیام خود را بنویسید...', 'home contact', 'imp' ); ?>"></textarea>
		</span>
	</label>

	<?php // Honeypot: hidden from people; bots that fill it in are ignored. ?>
	<p class="imp-hp" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></p>
	<input type="hidden" name="action" value="<?php echo esc_attr( Contact_Mailer::ACTION ); ?>">
	<?php wp_nonce_field( Contact_Mailer::ACTION, Contact_Mailer::NONCE ); ?>

	<button class="imp-btn imp-btn--solid" type="submit"><?php echo esc_html_x( 'ارسال', 'home contact', 'imp' ); ?></button>
	<?php if ( $note_inside ) : ?>
		<p class="imp-contact__note"><?php echo esc_html( $note ); ?></p>
	<?php endif; ?>
</form>
<?php if ( ! $note_inside ) : ?>
	<p class="imp-contact__note"><?php echo esc_html( $note ); ?></p>
<?php endif; ?>
