<?php
/**
 * Contact. Figma: mobile Contact (820:11317), desktop Contact (1225:9182).
 *
 * @package IMP
 *
 * @var array $args { title, socials, email }  (IMP\Controllers\Contact_Controller)
 */

defined( 'ABSPATH' ) || exit;
?>
<main class="imp-ui imp-page imp-contact-page">
	<?php imp_component( 'ornament-title', array( 'title' => $args['title'] ) ); ?>
	<?php
	imp_component(
		'social-list',
		array(
			'socials' => $args['socials'],
			'class'   => 'imp-social imp-contact-page__social',
		)
	);
	?>
	<?php imp_component( 'contact-form', array( 'note_inside' => true ) ); ?>
	<a class="imp-contact-page__email" href="mailto:<?php echo esc_attr( $args['email'] ); ?>" dir="ltr">
		<?php imp_icon( 'mail-lg' ); ?>
		<span><?php echo esc_html( $args['email'] ); ?></span>
	</a>
</main>
