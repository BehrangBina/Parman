<?php
/**
 * News card (home carousel, news page). Figma: CarddNews / Card (715:4482, desktop 1116:3695).
 *
 * @package IMP
 *
 * @var array $args { url, image (HTML or ''), date_iso, date_text, title, excerpt }
 */

defined( 'ABSPATH' ) || exit;
?>
<a class="imp-card" href="<?php echo esc_url( $args['url'] ); ?>">
	<span class="imp-card__media">
		<?php if ( $args['image'] ) : ?>
			<?php echo $args['image']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core get_the_post_thumbnail() output. ?>
		<?php else : ?>
			<img src="<?php echo esc_url( imp_asset( 'img/news-placeholder.png' ) ); ?>" alt="" loading="lazy">
		<?php endif; ?>
	</span>
	<time class="imp-card__date" datetime="<?php echo esc_attr( $args['date_iso'] ); ?>"><?php echo esc_html( $args['date_text'] ); ?></time>
	<span class="imp-card__title"><?php echo esc_html( $args['title'] ); ?></span>
	<span class="imp-card__excerpt"><?php echo esc_html( $args['excerpt'] ); ?></span>
</a>
