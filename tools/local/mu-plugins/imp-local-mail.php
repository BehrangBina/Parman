<?php
/**
 * Plugin Name: IMP local mail catcher
 * Description: LOCAL ONLY (mounted by docker-compose.wordpress.yml). Sends every email to
 * Mailpit instead of the internet; read them at http://localhost:8025.
 */

defined( 'ABSPATH' ) || exit;

if ( 'local' !== wp_get_environment_type() ) {
	return;
}

// WordPress's default sender "wordpress@localhost" is rejected as an invalid address.
add_filter(
	'wp_mail_from',
	function ( $from ) {
		return false === strpos( $from, '@localhost' ) ? $from : 'wordpress@imp.test';
	}
);

add_action(
	'phpmailer_init',
	function ( $mailer ) {
		$mailer->isSMTP();
		$mailer->Host     = 'mailpit';
		$mailer->Port     = 1025;
		$mailer->SMTPAuth = false;
		$mailer->SMTPAutoTLS = false;
	}
);
