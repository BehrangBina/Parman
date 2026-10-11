<?php
/**
 * Small logger on top of PHP's error_log(), so it follows WordPress's own debug settings
 * (WP_DEBUG / WP_DEBUG_LOG write to wp-content/debug.log).
 *
 * Deliberately NOT a global error handler: a theme that took over PHP's handler would also
 * swallow every plugin's errors. WordPress already handles fatals (recovery mode).
 *
 * Privacy: never pass personal data (names, emails, form answers) in $context — the forms on
 * this site collect GDPR-protected data.
 *
 * @package IMP
 */

namespace IMP\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Logger::error( 'Contact_Mailer::send', 'wp_mail failed', array( 'reason' => … ) )
 * writes: [IMP] 2026-10-07 21:14:03 ERROR Contact_Mailer::send - wp_mail failed {"reason":"…"}
 */
final class Logger {

	/**
	 * Something failed and a visitor may have noticed. Always logged.
	 *
	 * @param string $where   Class::method (or a short location).
	 * @param string $message What happened.
	 * @param array  $context Non-personal details.
	 */
	public static function error( $where, $message, array $context = array() ) {
		self::write( 'ERROR', $where, $message, $context );
	}

	/**
	 * Unexpected but handled (a fallback was used). Logged when WP_DEBUG is on.
	 *
	 * @param string $where   Class::method.
	 * @param string $message What happened.
	 * @param array  $context Non-personal details.
	 */
	public static function warning( $where, $message, array $context = array() ) {
		if ( self::verbose() ) {
			self::write( 'WARNING', $where, $message, $context );
		}
	}

	/**
	 * Trace for development only (local environment + WP_DEBUG).
	 *
	 * @param string $where   Class::method.
	 * @param string $message What happened.
	 * @param array  $context Non-personal details.
	 */
	public static function debug( $where, $message, array $context = array() ) {
		if ( self::verbose() && 'production' !== wp_get_environment_type() ) {
			self::write( 'DEBUG', $where, $message, $context );
		}
	}

	/**
	 * Whether extra logging is on.
	 *
	 * @return bool
	 */
	private static function verbose() {
		return defined( 'WP_DEBUG' ) && WP_DEBUG;
	}

	/**
	 * Format and hand the line to error_log().
	 *
	 * @param string $level   ERROR / WARNING / DEBUG.
	 * @param string $where   Class::method.
	 * @param string $message What happened.
	 * @param array  $context Non-personal details.
	 */
	private static function write( $level, $where, $message, array $context ) {
		$line = sprintf( '[IMP] %s %s %s - %s', gmdate( 'Y-m-d H:i:s' ), $level, $where, $message );
		if ( $context ) {
			$line .= ' ' . wp_json_encode( $context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		}
		error_log( $line ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- this IS the logger.
	}
}
