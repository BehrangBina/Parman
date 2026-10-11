<?php
/**
 * Gold statement card. Figma: Bayanie card (mobile 1009:3003, desktop "Info" 794×171, radius 18).
 *
 * @package IMP
 *
 * @var array $args { url, subject, day_month, year, date_iso }
 */

defined( 'ABSPATH' ) || exit;
?>
<a class="imp-scard" href="<?php echo esc_url( $args['url'] ); ?>">
	<span class="imp-scard__date">
		<?php imp_icon( 'calendar' ); ?>
		<time datetime="<?php echo esc_attr( $args['date_iso'] ); ?>">
			<span><?php echo esc_html( $args['day_month'] ); ?></span>
			<span><?php echo esc_html( $args['year'] ); ?></span>
		</time>
	</span>
	<span class="imp-scard__body">
		<span class="imp-scard__prefix"><?php echo esc_html_x( 'بیانیه پارمان پادشاهی ایرانیان درباره:', 'statements', 'imp' ); ?></span>
		<span class="imp-scard__title"><?php echo esc_html( $args['subject'] ); ?></span>
	</span>
</a>
