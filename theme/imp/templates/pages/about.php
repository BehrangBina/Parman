<?php
/**
 * About page. Figma: mobile About (text card + "اعضای حزب": round photos, card carousels and
 * the Dabir / Sokhangoo "مشاهده همه" overlays), desktop About 1153:292 (cards per group).
 * People and groups are managed in the admin under "اعضای حزب".
 *
 * @package IMP
 *
 * @var array $args { title, intro_html, groups: array[], contact_url, socials }  (IMP\Controllers\About_Controller)
 */

defined( 'ABSPATH' ) || exit;
?>
<main class="imp-ui imp-about">
	<section class="imp-page imp-about__intro">
		<?php imp_component( 'ornament-title', array( 'title' => $args['title'] ) ); ?>
		<?php if ( '' !== trim( wp_strip_all_tags( $args['intro_html'] ) ) ) : ?>
			<div class="imp-about__card">
				<div class="imp-about__text" tabindex="0">
					<?php echo wp_kses_post( $args['intro_html'] ); ?>
				</div>
			</div>
		<?php endif; ?>
	</section>

	<?php if ( $args['groups'] ) : ?>
		<section class="imp-about__members" aria-labelledby="imp-members-title">
			<?php
			imp_component(
				'ornament-title',
				array(
					'title' => _x( 'اعضای حزب', 'about', 'imp' ),
					'tag'   => 'h2',
					'id'    => 'imp-members-title',
				)
			);
			?>
			<div class="imp-about__band">
				<?php foreach ( $args['groups'] as $group ) : ?>
					<section class="imp-group imp-group--<?php echo esc_attr( $group['mobile'] ); ?>">
						<?php imp_component( 'ornament-title', array( 'title' => $group['title'], 'tag' => 'h3' ) ); ?>

						<?php // Desktop (and the phone "circles" layout for small groups). ?>
						<ul class="imp-group__cards">
							<?php
							foreach ( $group['people'] as $person ) {
								imp_component( 'person-card', $person );
							}
							?>
						</ul>

						<?php if ( 'circles' === $group['mobile'] ) : ?>
							<ul class="imp-group__circles">
								<?php
								foreach ( $group['people'] as $person ) {
									imp_component( 'person-card', array_merge( $person, array( 'variant' => 'circle' ) ) );
								}
								?>
							</ul>
						<?php else : ?>
							<div class="imp-group__carousel" data-imp-carousel>
								<button class="imp-group__arrow imp-group__arrow--left" type="button" data-imp-carousel-dir="-1">
									<span class="screen-reader-text"><?php esc_html_e( 'Previous', 'imp' ); ?></span>
								</button>
								<ul class="imp-group__track" data-imp-carousel-track>
									<?php
									foreach ( $group['people'] as $person ) {
										imp_component( 'person-card', array_merge( $person, array( 'variant' => 'mini' ) ) );
									}
									?>
								</ul>
								<button class="imp-group__arrow imp-group__arrow--right" type="button" data-imp-carousel-dir="1">
									<span class="screen-reader-text"><?php esc_html_e( 'Next', 'imp' ); ?></span>
								</button>
							</div>
							<a class="imp-group__all" href="#<?php echo esc_attr( $group['slug'] ); ?>" data-imp-dialog="<?php echo esc_attr( $group['slug'] ); ?>"><?php echo esc_html_x( 'مشاهده همه', 'about', 'imp' ); ?></a>
						<?php endif; ?>
					</section>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php // Figma: closing verse with ornaments (1008:1941), exported artwork Title-Ending. ?>
	<div class="imp-about__verse notranslate">
		<img src="<?php echo esc_url( imp_asset( 'img/verse.svg' ) ); ?>" width="395" height="76" alt="به مردی و فرهنگ و فرزانگی، ز گیتی برآریم آوازگی" loading="lazy">
	</div>

	<?php
	// Phone "مشاهده همه" overlays (Figma Dabir / Sokhangoo): the whole group as full cards.
	foreach ( $args['groups'] as $group ) {
		if ( 'carousel' !== $group['mobile'] ) {
			continue;
		}
		imp_component(
			'people-dialog',
			array(
				'group'       => $group,
				'contact_url' => $args['contact_url'],
				'socials'     => $args['socials'],
			)
		);
	}
	?>
</main>
