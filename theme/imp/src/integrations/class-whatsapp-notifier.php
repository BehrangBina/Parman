<?php
/**
 * WhatsApp messages for new membership entries: an alert to the office numbers and,
 * when the applicant ticked the opt-in box, a welcome message to the applicant.
 * Sent through the WhatsApp Business Cloud API with Meta-approved templates.
 *
 * @package IMP
 */

namespace IMP\Integrations;

use IMP\Core\Config;
use IMP\Core\Logger;

defined( 'ABSPATH' ) || exit;

/**
 * Hooks Fluent Forms' submission; a failed message is logged and never blocks the form.
 * Test mode (no IMP_WHATSAPP_TOKEN): nothing is sent, messages are only listed in the
 * admin page, so it can be tried locally.
 */
final class Whatsapp_Notifier {

	const LOG_OPTION = 'imp_whatsapp_log';
	const LOG_SIZE   = 50;
	const PAGE       = 'imp-whatsapp';
	const ACTION     = 'imp_whatsapp_test';

	/**
	 * Hook into WordPress.
	 */
	public static function register() {
		add_action( 'fluentform/submission_inserted', array( __CLASS__, 'on_submission' ), 20, 3 );
		add_action( 'admin_menu', array( __CLASS__, 'admin_page' ) );
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'send_test' ) );
	}

	/**
	 * True when real sending is configured in wp-config.php.
	 *
	 * @return bool
	 */
	public static function is_live() {
		return defined( 'IMP_WHATSAPP_TOKEN' ) && IMP_WHATSAPP_TOKEN && defined( 'IMP_WHATSAPP_PHONE_ID' ) && IMP_WHATSAPP_PHONE_ID;
	}

	/**
	 * Office numbers from wp-config.php (comma separated).
	 *
	 * @return string[]
	 */
	private static function office_numbers() {
		if ( ! defined( 'IMP_WHATSAPP_OFFICE' ) ) {
			return array();
		}
		return array_values( array_filter( array_map( array( __CLASS__, 'normalize' ), explode( ',', (string) IMP_WHATSAPP_OFFICE ) ) ) );
	}

	/**
	 * A new entry was stored.
	 *
	 * @param int    $entry_id  Submission ID.
	 * @param array  $form_data Submitted values by field name.
	 * @param object $form      Fluent form.
	 */
	public static function on_submission( $entry_id, $form_data, $form ) {
		if ( ! isset( $form->id ) || (int) $form->id !== (int) Config::get( 'membership.form_id' ) ) {
			return;
		}
		try {
			$names     = (array) ( $form_data['names'] ?? array() );
			$first     = trim( (string) ( $names['first_name'] ?? '' ) );
			$full      = trim( $first . ' ' . (string) ( $names['last_name'] ?? '' ) );
			$country   = (string) ( $form_data['country-list'] ?? '' );
			$entry_url = admin_url( 'admin.php?page=fluent_forms&route=entries&form_id=' . (int) $form->id . '#/entries/' . (int) $entry_id );

			foreach ( self::office_numbers() as $number ) {
				self::send( 'office', $number, array( $full, $country, $entry_url ) );
			}

			if ( ! empty( $form_data[ Config::get( 'whatsapp.consent_field' ) ] ) ) {
				$phone = self::normalize( (string) ( $form_data['phone'] ?? '' ) );
				if ( $phone ) {
					self::send( 'applicant', $phone, array( '' !== $first ? $first : $full ) );
				} else {
					self::record( 'applicant', '', 'skipped', 'phone not in international format' );
				}
			}
		} catch ( \Throwable $error ) {
			Logger::error( 'Whatsapp_Notifier::on_submission', 'Unexpected error', array( 'error' => $error->getMessage() ) );
		}
	}

	/**
	 * "+49 170 123-45" / "0049…" → "49170…" (digits only, as the API wants); '' if invalid.
	 * Numbers without a country code cannot be routed, so they are rejected.
	 *
	 * @param string $number Raw number.
	 * @return string
	 */
	private static function normalize( $number ) {
		$number = trim( $number );
		if ( 0 === strpos( $number, '00' ) ) {
			$number = '+' . substr( $number, 2 );
		}
		if ( 0 !== strpos( $number, '+' ) ) {
			return '';
		}
		$digits = preg_replace( '/\D+/', '', $number );
		return ( strlen( $digits ) >= 8 && strlen( $digits ) <= 15 ) ? $digits : '';
	}

	/**
	 * Send one template message (or only record it in test mode).
	 *
	 * @param string   $kind   "office" or "applicant" (selects the template).
	 * @param string   $to     Digits with country code.
	 * @param string[] $params Template body parameters {{1}}, {{2}}, ….
	 * @return bool
	 */
	private static function send( $kind, $to, array $params ) {
		$template = (string) Config::get( 'whatsapp.templates.' . $kind );
		if ( ! self::is_live() ) {
			self::record( $kind, $to, 'test', $template );
			return true;
		}

		$response = wp_remote_post(
			sprintf( 'https://graph.facebook.com/%s/%s/messages', rawurlencode( Config::get( 'whatsapp.api_version' ) ), rawurlencode( IMP_WHATSAPP_PHONE_ID ) ),
			array(
				'timeout' => 8, // the visitor is waiting for the form to finish
				'headers' => array(
					'Authorization' => 'Bearer ' . IMP_WHATSAPP_TOKEN,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'messaging_product' => 'whatsapp',
						'to'                => $to,
						'type'              => 'template',
						'template'          => array(
							'name'       => $template,
							'language'   => array( 'code' => Config::get( 'whatsapp.language' ) ),
							'components' => array(
								array(
									'type'       => 'body',
									'parameters' => array_map(
										function ( $text ) {
											return array( 'type' => 'text', 'text' => (string) $text );
										},
										$params
									),
								),
							),
						),
					)
				),
			)
		);

		$code = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
		if ( 200 === $code ) {
			self::record( $kind, $to, 'sent', $template );
			return true;
		}
		$body   = is_wp_error( $response ) ? array() : json_decode( wp_remote_retrieve_body( $response ), true );
		$reason = is_wp_error( $response ) ? $response->get_error_message() : (string) ( $body['error']['message'] ?? 'HTTP ' . $code );
		self::record( $kind, $to, 'failed', $reason );
		Logger::error( 'Whatsapp_Notifier::send', 'WhatsApp API call failed', array( 'kind' => $kind, 'http' => $code, 'reason' => $reason ) );
		return false;
	}

	/**
	 * Keep the last messages for the admin page. Numbers are masked; no names or answers.
	 *
	 * @param string $kind   office / applicant.
	 * @param string $to     Number (digits).
	 * @param string $status test / sent / failed / skipped.
	 * @param string $note   Template name or error.
	 */
	private static function record( $kind, $to, $status, $note ) {
		$log = (array) get_option( self::LOG_OPTION, array() );
		array_unshift(
			$log,
			array(
				'time'   => current_time( 'mysql' ),
				'kind'   => $kind,
				'to'     => $to ? '+' . substr( $to, 0, 2 ) . str_repeat( '•', max( 0, strlen( $to ) - 5 ) ) . substr( $to, -3 ) : '—',
				'status' => $status,
				'note'   => mb_substr( $note, 0, 200 ),
			)
		);
		update_option( self::LOG_OPTION, array_slice( $log, 0, self::LOG_SIZE ), false );
	}

	/**
	 * Tools → IMP WhatsApp: mode, numbers, templates, recent messages and a test button.
	 */
	public static function admin_page() {
		add_management_page(
			'پیام‌های واتساپ هموندی',
			'IMP WhatsApp',
			'manage_options',
			self::PAGE,
			function () {
				get_template_part(
					'templates/admin/whatsapp',
					null,
					array(
						'live'      => self::is_live(),
						'office'    => count( self::office_numbers() ),
						'templates' => (array) Config::get( 'whatsapp.templates' ),
						'log'       => (array) get_option( self::LOG_OPTION, array() ),
						'action'    => self::ACTION,
						'result'    => isset( $_GET['imp_wa'] ) ? sanitize_key( $_GET['imp_wa'] ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
					)
				);
			}
		);
	}

	/**
	 * "Send test alert": the office template to the office numbers, with sample values.
	 */
	public static function send_test() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'imp' ), 403 );
		}
		check_admin_referer( self::ACTION );
		$numbers = self::office_numbers();
		$ok      = (bool) $numbers;
		foreach ( $numbers as $number ) {
			$ok = self::send( 'office', $number, array( 'پیام آزمایشی', '—', admin_url() ) ) && $ok;
		}
		wp_safe_redirect( add_query_arg( array( 'page' => self::PAGE, 'imp_wa' => $numbers ? ( $ok ? 'sent' : 'failed' ) : 'nonumbers' ), admin_url( 'tools.php' ) ) );
		exit;
	}
}
