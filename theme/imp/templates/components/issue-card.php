<?php
/**
 * Magazine issue card: tapping the blue part views the PDF, "دانلود" downloads it.
 * Figma: Nashriye issue card (820:9504), desktop Card-Box.
 *
 * @package IMP
 *
 * @var array $args { title, description, year, day_month, date_iso, pdf, download }
 */

defined( 'ABSPATH' ) || exit;

$has_pdf = '' !== $args['pdf'];
?>
<li class="imp-issue">
	<?php if ( $has_pdf ) : ?>
		<a class="imp-issue__view" href="<?php echo esc_url( $args['pdf'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( sprintf( 'مشاهده %s', $args['title'] ) ); ?>">
	<?php else : ?>
		<div class="imp-issue__view">
	<?php endif; ?>
		<span class="imp-issue__top">
			<span class="imp-issue__title"><?php echo esc_html( $args['title'] ); ?></span>
			<span class="imp-issue__year"><?php echo esc_html( $args['year'] ); ?></span>
		</span>
		<?php if ( $args['description'] ) : ?>
			<span class="imp-issue__desc"><?php echo esc_html( $args['description'] ); ?></span>
		<?php endif; ?>
	<?php echo $has_pdf ? '</a>' : '</div>'; ?>
	<div class="imp-issue__bar">
		<time class="imp-issue__date" datetime="<?php echo esc_attr( $args['date_iso'] ); ?>"><?php echo esc_html( $args['day_month'] ); ?></time>
		<?php if ( $has_pdf ) : ?>
			<a class="imp-issue__download" href="<?php echo esc_url( $args['download'] ); ?>" download>
				<?php echo esc_html_x( 'دانلود', 'magazine', 'imp' ); ?>
				<?php imp_icon( 'download' ); ?>
			</a>
		<?php endif; ?>
	</div>
</li>
