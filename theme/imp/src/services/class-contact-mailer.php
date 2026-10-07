<?php
/**
 * Handles the "علاقه‌مند به هموندی هستید؟" form on the home and contact pages.
 *
 * @package IMP
 */

namespace IMP\Services;

use IMP\Core\Config;
use IMP\Core\Logger;

defined( 'ABSPATH' ) || exit;

/**
 * Validates the submission (nonce, email, honeypot), emails it, and redirects back with
 * ?imp_contact=sent|error so the form can show the result.
 */
final class Contact_Mailer {

	const ACTION     = 'imp_contact';
	const NONCE      = 'imp_contact_nonce';
	const RESULT_VAR = 'imp_contact';
	const ANCHOR     = '#imp-contact';

	/**
	 * Hook into WordPress (logged-in and anonymous visitors).
	 */
	public static function register() {
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle' ) );
		add_action( 'admin_post_nopriv_' . self::ACTION, array( __CLASS__, 'handle' ) );
	}

	/**
	 * Result of the last submission for the form ("sent", "error" or "").
	 *
	 * @return string
	 */
	public static function result() {
		return isset( $_GET[ self::RESULT_VAR ] ) ? sanitize_key( $_GET[ self::RESULT_VAR ] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	}

	/**
	 * Process a submission.
	 */
	public static function handle() {
		$back = remove_query_arg( self::RESULT_VAR, wp_get_referer() ? wp_get_referer() : home_url( '/' ) );

		$nonce = isset( $_POST[ self::NONCE ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::NONCE ] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::ACTION ) ) {
			Logger::warning( 'Contact_Mailer::handle', 'Invalid or expired nonce' );
			self::redirect( $back, 'error' );
		}

		$fields = self::fields();
		if ( ! is_email( $fields['email'] ) ) {
			self::redirect( $back, 'error' );
		}
		if ( ! empty( $_POST['website'] ) ) { // Honeypot filled in: a bot. Pretend success.
			Logger::debug( 'Contact_Mailer::handle', 'Honeypot triggered' );
			self::redirect( $back, 'sent' );
		}

		$sent = self::send( $fields );
		self::redirect( $back, $sent ? 'sent' : 'error' );
	}

	/**
	 * Sanitised form values.
	 *
	 * @return array{first_name:string,last_name:string,email:string,country:string,message:string}
	 */
	private static function fields() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified in handle().
		$text = static function ( $key ) {
			return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		};
		$fields = array(
			'first_name' => $text( 'first_name' ),
			'last_name'  => $text( 'last_name' ),
			'email'      => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
			'country'    => $text( 'country' ),
			'message'    => isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '',
		);
		// phpcs:enable
		return $fields;
	}

	/**
	 * Email the enquiry to the configured recipient.
	 *
	 * @param array $fields Sanitised values.
	 * @return bool
	 */
	private static function send( array $fields ) {
		$name      = trim( $fields['first_name'] . ' ' . $fields['last_name'] );
		$recipient = Config::get( 'contact_recipient' );
		$recipient = apply_filters( 'imp_contact_recipient', $recipient ? $recipient : get_option( 'admin_email' ) );

		$failure = null;
		$catch   = static function ( $error ) use ( &$failure ) {
			$failure = $error;
		};
		add_action( 'wp_mail_failed', $catch );
		$sent = wp_mail(
			$recipient,
			sprintf( '[%s] Membership enquiry: %s', wp_specialchars_decode( get_bloginfo( 'name' ) ), $name ),
			sprintf( "Name: %s\nEmail: %s\nCountry: %s\n\n%s", $name, $fields['email'], $fields['country'], $fields['message'] ),
			array( 'Reply-To: ' . $name . ' <' . $fields['email'] . '>' )
		);
		remove_action( 'wp_mail_failed', $catch );

		if ( ! $sent ) {
			// No personal data in the log — only the mailer's own error.
			Logger::error( 'Contact_Mailer::send', 'wp_mail failed', array( 'reason' => $failure instanceof \WP_Error ? $failure->get_error_message() : 'unknown' ) );
		}
		return $sent;
	}

	/**
	 * Redirect back to the form with the result and stop.
	 *
	 * @param string $back   URL to return to.
	 * @param string $result "sent" or "error".
	 */
	private static function redirect( $back, $result ) {
		wp_safe_redirect( add_query_arg( self::RESULT_VAR, $result, $back ) . self::ANCHOR );
		exit;
	}
}
