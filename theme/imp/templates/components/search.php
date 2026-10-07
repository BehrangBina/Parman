<?php
/**
 * Search: round button that opens into a long bar (✕ where the button was, magnifier at the end).
 * Figma: Search-Bar (mobile 526:1323 / Search-Bar-Open), desktop Search-Bar (1171:5050).
 *
 * @package IMP
 *
 * @var array $args { query: string }
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="imp-search" data-state="closed">
	<button class="imp-search__toggle" type="button" aria-controls="imp-search-input" aria-expanded="false">
		<span class="screen-reader-text"><?php esc_html_e( 'Search', 'imp' ); ?></span>
		<span class="imp-search__icon-open"><?php imp_icon( 'search' ); ?></span>
		<span class="imp-search__icon-close"><?php imp_icon( 'close' ); ?></span>
	</button>
	<form class="imp-search__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="screen-reader-text" for="imp-search-input"><?php esc_html_e( 'Search', 'imp' ); ?></label>
		<input id="imp-search-input" class="imp-search__input" type="search" name="s" value="<?php echo esc_attr( $args['query'] ); ?>" placeholder="<?php esc_attr_e( 'جستجو…', 'imp' ); ?>" tabindex="-1">
		<button class="imp-search__submit" type="submit" tabindex="-1">
			<span class="screen-reader-text"><?php esc_html_e( 'Search', 'imp' ); ?></span>
			<?php imp_icon( 'search' ); ?>
		</button>
	</form>
</div>
