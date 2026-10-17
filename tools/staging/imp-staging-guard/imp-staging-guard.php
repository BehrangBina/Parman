<?php
/**
 * Plugin Name: IMP staging guard
 * Description: For the test copy at imp.iranianmonarchy.info only. Stops all outgoing email
 *              (a copy of the live site would otherwise email applicants and the office),
 *              hides the site from search engines and shows a "TEST SITE" bar. Does nothing
 *              on any other host, so it is harmless if it is ever copied to the live site.
 * Version:     1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( 'imp.iranianmonarchy.info' !== strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) ) {
	return;
}

define( 'IMP_STAGING_GUARD', true ); // the "IMP staging setup" plugin only runs when this is set

// No email leaves the test site; each blocked message is logged (recipient domain only).
add_filter(
	'pre_wp_mail',
	function ( $return, $atts ) {
		$to      = (array) ( is_array( $atts['to'] ) ? $atts['to'] : explode( ',', (string) $atts['to'] ) );
		$domains = array_map(
			function ( $address ) {
				return substr( strrchr( trim( $address ), '@' ), 1 );
			},
			$to
		);
		error_log( '[IMP staging] email blocked to *@' . implode( ', *@', $domains ) . ' — subject length ' . strlen( (string) $atts['subject'] ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		return true; // report "sent" so forms and plugins carry on normally
	},
	1,
	2
);

// Second lock, for mailer plugins (e.g. FluentSMTP) that bypass pre_wp_mail: no recipients left.
add_action(
	'phpmailer_init',
	function ( $mailer ) {
		$mailer->clearAllRecipients();
	},
	PHP_INT_MAX
);

// Never indexed, whatever Settings → Reading says (that setting is overwritten by a copy).
add_filter( 'pre_option_blog_public', '__return_zero' );
add_filter(
	'wp_robots',
	function ( $robots ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
		return $robots;
	}
);

// A visible reminder for anyone looking at the pages.
add_action(
	'wp_body_open',
	function () {
		echo '<div style="position:fixed;inset:auto 0 0 0;z-index:99999;padding:4px;background:#be2b28;color:#fff;font:700 12px/1.4 sans-serif;text-align:center">TEST SITE — not the live iranianmonarchy.info · emails are switched off</div>';
	},
	1
);
