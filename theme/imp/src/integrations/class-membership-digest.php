<?php
/**
 * Weekly membership digest: every week the secretariat gets one email listing the new
 * membership-form entries. Ported from the live "IMP - Weekly Membership Digest (Form 3)"
 * Code Snippet (#6) so it is versioned with the theme.
 *
 * @package IMP
 */

namespace IMP\Integrations;

use IMP\Core\Config;
use IMP\Core\Logger;

defined( 'ABSPATH' ) || exit;

/**
 * Scheduled daily through WP-Cron; sends only on the configured weekday (or when a week
 * was missed). Admins can send it by hand from Tools → IMP membership digest.
 */
final class Membership_Digest {

	/** Same hook and option names as the old snippet, so its schedule and history carry over. */
	const HOOK        = 'imp_membership_digest';
	const LAST_OPTION = 'imp_membership_digest_last_run';
	const PAGE        = 'imp-membership-digest';
	const ACTION      = 'imp_send_digest';

	/**
	 * Hook into WordPress. Stays off while the old snippet is still active on the site,
	 * otherwise both would answer the same cron hook and send the digest twice.
	 */
	public static function register() {
		add_action(
			'init',
			function () {
				if ( function_exists( 'imp_digest_run' ) ) {
					Logger::warning( 'Membership_Digest::register', 'Old digest snippet is active; theme digest disabled. Deactivate Code Snippet #6.' );
					return;
				}
				self::schedule();
				add_action( self::HOOK, array( __CLASS__, 'run' ) );
				add_action( 'admin_menu', array( __CLASS__, 'admin_page' ) );
				add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'send_now' ) );
			},
			20 // after Code Snippets has loaded the snippets
		);
	}

	/**
	 * Daily check; run() decides whether today is the day.
	 */
	private static function schedule() {
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time() + 300, 'daily', self::HOOK );
		}
	}

	/**
	 * Send the digest if it is due (or always, when $force).
	 *
	 * @param bool $force Send now, regardless of the day; does not move the weekly window.
	 * @return bool|null True sent, false failed, null not due.
	 */
	public static function run( $force = false ) {
		$now  = current_time( 'timestamp' );
		$last = (int) get_option( self::LAST_OPTION, 0 );

		if ( true !== $force && ! self::is_due( $now, $last ) ) {
			return null;
		}

		$rows    = self::rows();
		$subject = sprintf( 'گزارش هفتگی هموندی — %d درخواست', count( $rows ) );
		$html    = self::render( $rows );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$sent    = wp_mail( Config::get( 'membership.digest.recipient' ), $subject, $html, $headers );

		if ( ! $sent ) {
			Logger::error( 'Membership_Digest::run', 'wp_mail failed (see the FluentSMTP log)', array( 'entries' => count( $rows ) ) );
			return false;
		}
		if ( true !== $force ) {
			update_option( self::LAST_OPTION, $now, false );
		}
		Logger::debug( 'Membership_Digest::run', 'Digest sent', array( 'entries' => count( $rows ), 'forced' => (bool) $force ) );
		return true;
	}

	/**
	 * Due on the configured weekday (at most once in 6 days), or whenever a week was missed.
	 *
	 * @param int $now  Local timestamp.
	 * @param int $last Local timestamp of the last scheduled send (0 = never).
	 * @return bool
	 */
	private static function is_due( $now, $last ) {
		$is_send_day = (int) wp_date( 'N' ) === (int) Config::get( 'membership.digest.weekday', 6 );
		if ( ! $last ) {
			return $is_send_day;
		}
		$since = $now - $last;
		return ( $is_send_day && $since >= 6 * DAY_IN_SECONDS ) || $since >= 7 * DAY_IN_SECONDS;
	}

	/**
	 * Start of the reporting window: a week back, or the last send if that is earlier
	 * (so entries are not lost when a week was skipped).
	 *
	 * @return int Local timestamp.
	 */
	private static function window_start() {
		$default = current_time( 'timestamp' ) - (int) Config::get( 'membership.digest.days_back', 7 ) * DAY_IN_SECONDS;
		$last    = (int) get_option( self::LAST_OPTION, 0 );
		return $last ? min( $last, $default ) : $default;
	}

	/**
	 * Entries of the membership form in the window (Fluent stores created_at in site time).
	 *
	 * @return object[] Rows with id, created_at, response (JSON).
	 */
	private static function rows() {
		global $wpdb;
		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Fluent Forms' own table, read once a week.
			$wpdb->prepare(
				"SELECT id, created_at, response FROM {$wpdb->prefix}fluentform_submissions WHERE form_id = %d AND created_at >= %s ORDER BY created_at ASC",
				(int) Config::get( 'membership.form_id' ),
				gmdate( 'Y-m-d H:i:s', self::window_start() )
			)
		);
		if ( null === $rows || $wpdb->last_error ) {
			Logger::error( 'Membership_Digest::rows', 'Query failed', array( 'db_error' => $wpdb->last_error ) );
			return array();
		}
		return $rows;
	}

	/**
	 * Field name → label, from the form definition (the admin label when one is set,
	 * because some questions are long).
	 *
	 * @return array<string,string>
	 */
	private static function labels() {
		global $wpdb;
		$json = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Fluent Forms' own table.
			$wpdb->prepare( "SELECT form_fields FROM {$wpdb->prefix}fluentform_forms WHERE id = %d", (int) Config::get( 'membership.form_id' ) )
		);
		$data   = json_decode( (string) $json, true );
		$labels = array();
		if ( is_array( $data ) ) {
			self::collect_labels( isset( $data['fields'] ) ? $data['fields'] : $data, $labels );
		}
		return $labels;
	}

	/**
	 * Walk fields, including fields inside column containers.
	 *
	 * @param array $fields Fluent field definitions.
	 * @param array $labels Collected labels (by reference).
	 */
	private static function collect_labels( $fields, array &$labels ) {
		foreach ( (array) $fields as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}
			$name = $field['attributes']['name'] ?? '';
			if ( '' !== $name ) {
				$admin           = trim( (string) ( $field['settings']['admin_field_label'] ?? '' ) );
				$labels[ $name ] = '' !== $admin ? $admin : (string) ( $field['settings']['label'] ?? $name );
			}
			foreach ( (array) ( $field['columns'] ?? array() ) as $column ) {
				self::collect_labels( $column['fields'] ?? array(), $labels );
			}
			if ( ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
				self::collect_labels( $field['fields'], $labels );
			}
		}
	}

	/**
	 * One entry's answers as label/value rows for the email (skips internal and consent fields).
	 *
	 * @param array $data   Decoded entry response.
	 * @param array $labels Field labels.
	 * @return array[] Each array{label,value}.
	 */
	private static function answers( array $data, array $labels ) {
		$rows = array();
		foreach ( $data as $key => $value ) {
			if ( '' === $key || '_' === $key[0] || in_array( $key, array( 'terms-n-condition', 'confirm_documents' ), true ) ) {
				continue;
			}
			$flat = 'names' === $key ? self::person_name( $data ) : self::flatten( $value );
			if ( '' === $flat ) {
				continue;
			}
			$rows[] = array(
				'label' => 'names' === $key ? 'نام و نام خانوادگی' : ( $labels[ $key ] ?? $key ),
				'value' => $flat,
			);
		}
		return $rows;
	}

	/**
	 * Nested answers (address, name parts, checkboxes) as one line.
	 *
	 * @param mixed $value Answer.
	 * @return string
	 */
	private static function flatten( $value ) {
		if ( is_scalar( $value ) && ! is_bool( $value ) ) {
			return trim( (string) $value );
		}
		if ( ! is_array( $value ) ) {
			return '';
		}
		$parts = array();
		foreach ( $value as $key => $item ) {
			$flat = self::flatten( $item );
			if ( '' !== $flat ) {
				$parts[] = ( is_string( $key ) && ! is_numeric( $key ) ) ? $key . ': ' . $flat : $flat;
			}
		}
		return implode( '، ', $parts );
	}

	/**
	 * "First Middle Last" from the Fluent name field.
	 *
	 * @param array $data Entry response.
	 * @return string
	 */
	private static function person_name( array $data ) {
		$names = $data['names'] ?? '';
		if ( is_array( $names ) ) {
			$parts = array_filter( array( $names['first_name'] ?? '', $names['middle_name'] ?? '', $names['last_name'] ?? '' ) );
			if ( $parts ) {
				return implode( ' ', $parts );
			}
		}
		return self::flatten( $names );
	}

	/**
	 * Email body.
	 *
	 * @param object[] $rows Entries.
	 * @return string HTML.
	 */
	private static function render( array $rows ) {
		$labels    = self::labels();
		$fee_field = Config::get( 'membership.fee_field' );
		$entries   = array();
		foreach ( $rows as $row ) {
			$data      = json_decode( (string) $row->response, true );
			$data      = is_array( $data ) ? $data : array();
			$entries[] = array(
				'id'      => (int) $row->id,
				'date'    => $row->created_at,
				'name'    => self::person_name( $data ),
				'fee'     => isset( $data[ $fee_field ] ) ? self::flatten( $data[ $fee_field ] ) : '',
				'answers' => self::answers( $data, $labels ),
				'url'     => admin_url( 'admin.php?page=fluent_forms&route=entries&form_id=' . (int) Config::get( 'membership.form_id' ) . '#/entries/' . (int) $row->id ),
			);
		}
		ob_start();
		get_template_part(
			'templates/emails/membership-digest',
			null,
			array(
				'from'    => gmdate( 'Y-m-d', self::window_start() ),
				'to'      => gmdate( 'Y-m-d', current_time( 'timestamp' ) ),
				'entries' => $entries,
			)
		);
		return (string) ob_get_clean();
	}

	/**
	 * Tools → IMP membership digest: status and a "send now" button.
	 */
	public static function admin_page() {
		add_management_page(
			'گزارش هفتگی هموندی',
			'IMP membership digest',
			'manage_options',
			self::PAGE,
			function () {
				$next = wp_next_scheduled( self::HOOK );
				$last = (int) get_option( self::LAST_OPTION, 0 );
				get_template_part(
					'templates/admin/membership-digest',
					null,
					array(
						'recipient' => Config::get( 'membership.digest.recipient' ),
						'next'      => $next ? wp_date( 'Y-m-d H:i', $next ) : '',
						'last'      => $last ? gmdate( 'Y-m-d H:i', $last ) : '',
						'overdue'   => $next && $next < time() - HOUR_IN_SECONDS,
						'action'    => self::ACTION,
						'result'    => isset( $_GET['imp_digest'] ) ? sanitize_key( $_GET['imp_digest'] ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
					)
				);
			}
		);
	}

	/**
	 * The "send now" button (admin-post.php).
	 */
	public static function send_now() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'imp' ), 403 );
		}
		check_admin_referer( self::ACTION );
		$sent = self::run( true );
		wp_safe_redirect( add_query_arg( array( 'page' => self::PAGE, 'imp_digest' => $sent ? 'sent' : 'failed' ), admin_url( 'tools.php' ) ) );
		exit;
	}
}
