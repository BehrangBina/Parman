<?php
/**
 * Extras for the Fluent Forms membership form (Figma Form-Hamvandi 740:2198, desktop 1181:5631).
 * The form is the live Fluent form #3 (config "membership"); tools/build-membership-form.php
 * rebuilds its local copy.
 *
 * @package IMP
 */

namespace IMP\Integrations;

use IMP\Core\Config;
use IMP\Data\Fee_Content;

defined( 'ABSPATH' ) || exit;

/**
 * Adds the "your information is safe" line under the form and the fee popup on its page.
 */
final class Fluent_Forms {

	/** Matched by title, so it works whatever ID the form gets when imported on the live site. */
	const FORM_TITLE_MARK = 'فرم هموندی';

	/**
	 * Hook into WordPress (harmless when Fluent Forms is not active: the hook never fires).
	 */
	public static function register() {
		add_action( 'fluentform/after_form_render', array( __CLASS__, 'safe_note' ) );
		add_action( 'wp_footer', array( __CLASS__, 'fee_dialog' ), 5 );
	}

	/**
	 * "اطلاعات شما نزد ما محفوظ است" under the submit button.
	 *
	 * @param object $form Fluent Forms form object.
	 */
	public static function safe_note( $form ) {
		if ( ! isset( $form->title ) || false === mb_strpos( $form->title, self::FORM_TITLE_MARK ) ) {
			return;
		}
		get_template_part( 'templates/components/form-safe-note' );
	}

	/**
	 * The form's "درباره هزینه هموندی" link opens the same popup as the Hamyari page.
	 */
	public static function fee_dialog() {
		if ( ! is_page( Config::get( 'pages.membership_form' ) ) ) {
			return;
		}
		echo '<div class="imp-ui">';
		imp_component( 'fee-dialog', array( 'html' => Fee_Content::html() ) );
		echo '</div>';
	}
}
