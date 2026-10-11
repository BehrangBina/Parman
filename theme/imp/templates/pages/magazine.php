<?php
/**
 * Nashriye (magazine). Figma: mobile Nashriye (820:9504), desktop Nashriyte (1215:4401).
 *
 * @package IMP
 *
 * @var array $args { title, intro_html, issues: array[] }  (IMP\Controllers\Magazine_Controller)
 */

defined( 'ABSPATH' ) || exit;
?>
<main class="imp-ui imp-page imp-magazine">
	<?php imp_component( 'ornament-title', array( 'title' => $args['title'] ) ); ?>

	<div class="imp-magazine__intro">
		<?php echo wp_kses_post( $args['intro_html'] ); ?>
	</div>

	<?php if ( $args['issues'] ) : ?>
		<ul class="imp-issues">
			<?php
			foreach ( $args['issues'] as $issue ) {
				imp_component( 'issue-card', $issue );
			}
			?>
		</ul>
	<?php endif; ?>
</main>
