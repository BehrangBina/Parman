<?php
/**
 * Extras for the Fluent Forms membership form (Figma Form-Hamvandi 740:2198).
 * The form itself is built by tools/build-membership-form.php and managed in Fluent Forms.
 */

defined( 'ABSPATH' ) || exit;

/** Is this the membership form? Matched by title so it works whatever ID the import gets. */
function imp_m_is_membership_form( $form ) {
	return $form && isset( $form->title ) && false !== mb_strpos( $form->title, 'فرم هموندی' );
}

// "Your information is safe" line under the submit button.
add_action( 'fluentform/after_form_render', function ( $form ) {
	if ( ! imp_m_is_membership_form( $form ) ) {
		return;
	}
	?>
	<p class="imp-m-form-safe">
		<?php imp_m_icon( 'lock' ); ?>
		<?php echo esc_html_x( 'اطلاعات شما نزد ما محفوظ است', 'membership form', 'imp-mobile' ); ?>
	</p>
	<?php
} );

// The form's "درباره هزینه هموندی" link opens the same fee popup as the Hamyari page.
add_action( 'wp_footer', function () {
	if ( is_page( 'membership-form' ) && function_exists( 'imp_m_fee_dialog' ) ) {
		echo '<div class="imp-m">';
		imp_m_fee_dialog();
		echo '</div>';
	}
}, 5 );
