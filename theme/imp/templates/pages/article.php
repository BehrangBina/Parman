<?php
/**
 * One news story or statement. Figma: desktop Full-News (1215:6609, overview only): section title,
 * picture beside the headline, the text below. Phones: picture, date, headline, text.
 *
 * @package IMP
 *
 * @var array $args { section, title, date_iso, date_text, image, html }  (IMP\Controllers\Article_Controller)
 */

defined( 'ABSPATH' ) || exit;
?>
<main class="imp-ui imp-page imp-article">
	<?php imp_component( 'ornament-title', array( 'title' => $args['section'], 'tag' => 'p' ) ); ?>

	<article class="imp-article__body">
		<header class="imp-article__head<?php echo $args['image'] ? '' : ' imp-article__head--text'; ?>">
			<?php if ( $args['image'] ) : ?>
				<figure class="imp-article__media"><?php echo $args['image']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core get_the_post_thumbnail() output. ?></figure>
			<?php endif; ?>
			<div class="imp-article__meta">
				<time class="imp-article__date" datetime="<?php echo esc_attr( $args['date_iso'] ); ?>"><?php echo esc_html( $args['date_text'] ); ?></time>
				<h1 class="imp-article__title"><?php echo esc_html( $args['title'] ); ?></h1>
			</div>
		</header>
		<div class="imp-article__text">
			<?php echo wp_kses_post( $args['html'] ); ?>
		</div>
	</article>
</main>
