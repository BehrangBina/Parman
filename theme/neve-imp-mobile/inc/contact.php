<?php
/**
 * "Interested in membership?" form, used on the home page and the Contact page
 * (Figma 820:11317). Posts to admin-post.php and emails the recipient
 * (default: site admin email; filter `imp_m_contact_recipient`).
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'imp_m_links', function ( $links ) {
	return $links + array( 'contact_email' => 'padeshahiparty@gmail.com' );
}, 5 );

/** Home page section: slab title, form card, note below the card. */
function imp_m_contact_section() {
	?>
	<section class="imp-m-contact" aria-labelledby="imp-m-contact-title">
		<h2 id="imp-m-contact-title" class="imp-m-section-title imp-m-section-title--slab"><?php echo esc_html_x( 'تماس با ما', 'home contact', 'imp-mobile' ); ?></h2>
		<?php imp_m_contact_form(); ?>
		<p class="imp-m-contact__note"><?php echo esc_html_x( 'پس از بررسی اولیه، یکی از اعضای تیم با شما تماس خواهد گرفت', 'home contact', 'imp-mobile' ); ?></p>
	</section>
	<?php
}

/** Contact page (slug bcd31-contact-us): ornament title, socials, form with the note inside, email. */
function imp_m_render_contact() {
	$email = imp_m_links()['contact_email'];
	?>
	<main class="imp-m imp-m-page imp-m-contact-page">
		<?php imp_m_ornament_title( get_the_title( get_queried_object_id() ) ); ?>
		<?php imp_m_social_list( 'imp-m-social imp-m-contact-page__social' ); ?>
		<?php imp_m_contact_form( true ); ?>
		<a class="imp-m-contact-page__email" href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>" dir="ltr">
			<?php imp_m_icon( 'mail-lg' ); ?>
			<span><?php echo esc_html( antispambot( $email ) ); ?></span>
		</a>
	</main>
	<?php
}

/**
 * @param bool $note_inside Put the "we'll contact you" note inside the card (Contact page).
 */
function imp_m_contact_form( $note_inside = false ) {
	$status = isset( $_GET['imp_contact'] ) ? sanitize_key( $_GET['imp_contact'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$field  = function ( $name, $label, $icon, $type = 'text', $placeholder = '' ) {
		?>
		<label class="imp-m-field">
			<span class="imp-m-field__label"><?php echo esc_html( $label ); ?></span>
			<span class="imp-m-field__control">
				<input type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $name ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" required<?php echo 'email' === $type ? ' dir="ltr"' : ''; ?>>
				<?php imp_m_icon( $icon ); ?>
			</span>
		</label>
		<?php
	};
	?>
		<form class="imp-m-contact__card" id="imp-contact" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<p class="imp-m-contact__heading"><?php echo esc_html_x( 'علاقه‌مند به هموندی هستید؟', 'home contact', 'imp-mobile' ); ?></p>
			<p class="imp-m-contact__sub"><?php echo esc_html_x( 'لطفاً اطلاعات زیر را وارد کنید', 'home contact', 'imp-mobile' ); ?></p>

			<?php if ( 'sent' === $status ) : ?>
				<p class="imp-m-contact__notice" role="status"><?php echo esc_html_x( 'پیام شما ارسال شد. سپاس!', 'home contact', 'imp-mobile' ); ?></p>
			<?php elseif ( 'error' === $status ) : ?>
				<p class="imp-m-contact__notice imp-m-contact__notice--error" role="alert"><?php echo esc_html_x( 'ارسال پیام ناموفق بود. لطفاً دوباره تلاش کنید.', 'home contact', 'imp-mobile' ); ?></p>
			<?php endif; ?>

			<div class="imp-m-contact__row">
				<?php $field( 'first_name', 'نام', 'user' ); ?>
				<?php $field( 'last_name', 'نام خانوادگی', 'user' ); ?>
			</div>
			<?php $field( 'email', 'آدرس ایمیل', 'mail', 'email', 'name@example.com' ); ?>
			<?php $field( 'country', 'کشور', 'map-pin' ); ?>
			<label class="imp-m-field">
				<span class="imp-m-field__label"><?php echo esc_html_x( 'پیام', 'home contact', 'imp-mobile' ); ?></span>
				<span class="imp-m-field__control imp-m-field__control--area">
					<textarea name="message" rows="4" placeholder="<?php echo esc_attr_x( 'پیام خود را بنویسید...', 'home contact', 'imp-mobile' ); ?>"></textarea>
				</span>
			</label>

			<p class="imp-m-hp" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></p>
			<input type="hidden" name="action" value="imp_contact">
			<?php wp_nonce_field( 'imp_contact', 'imp_contact_nonce' ); ?>

			<button class="imp-m-btn imp-m-btn--solid" type="submit"><?php echo esc_html_x( 'ارسال', 'home contact', 'imp-mobile' ); ?></button>
			<?php if ( $note_inside ) : ?>
				<p class="imp-m-contact__note"><?php echo esc_html_x( 'پس از بررسی اولیه، یکی از اعضای تیم با شما تماس خواهد گرفت', 'home contact', 'imp-mobile' ); ?></p>
			<?php endif; ?>
		</form>
	<?php
}

function imp_m_handle_contact() {
	$back = wp_get_referer() ?: home_url( '/' );
	$back = remove_query_arg( 'imp_contact', $back );

	$valid = isset( $_POST['imp_contact_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['imp_contact_nonce'] ) ), 'imp_contact' );
	$bot   = ! empty( $_POST['website'] );
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	if ( ! $valid || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'imp_contact', 'error', $back ) . '#imp-contact' );
		exit;
	}
	if ( $bot ) { // Pretend success for bots.
		wp_safe_redirect( add_query_arg( 'imp_contact', 'sent', $back ) . '#imp-contact' );
		exit;
	}

	$get  = function ( $key ) {
		return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	};
	$name = trim( $get( 'first_name' ) . ' ' . $get( 'last_name' ) );
	$body = sprintf(
		"Name: %s\nEmail: %s\nCountry: %s\n\n%s",
		$name,
		$email,
		$get( 'country' ),
		isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : ''
	);

	$sent = wp_mail(
		apply_filters( 'imp_m_contact_recipient', get_option( 'admin_email' ) ),
		sprintf( '[%s] %s', wp_specialchars_decode( get_bloginfo( 'name' ) ), 'Membership enquiry: ' . $name ),
		$body,
		array( 'Reply-To: ' . $name . ' <' . $email . '>' )
	);

	wp_safe_redirect( add_query_arg( 'imp_contact', $sent ? 'sent' : 'error', $back ) . '#imp-contact' );
	exit;
}
add_action( 'admin_post_imp_contact', 'imp_m_handle_contact' );
add_action( 'admin_post_nopriv_imp_contact', 'imp_m_handle_contact' );
