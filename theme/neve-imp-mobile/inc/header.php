<?php
/**
 * Mobile header, full-screen menu, search and the fixed Donate tab.
 * Printed at wp_body_open; hidden on desktop by css/mobile.css.
 */

defined( 'ABSPATH' ) || exit;

function imp_m_menu() {
	$args = array(
		'container'   => false,
		'menu_class'  => 'imp-m-menu__list',
		'fallback_cb' => false,
		'depth'       => 2,
		'echo'        => false,
	);
	if ( has_nav_menu( 'primary' ) ) {
		$args['theme_location'] = 'primary';
	} else {
		$menus = wp_get_nav_menus();
		if ( empty( $menus ) ) {
			return wp_page_menu( array( 'echo' => false, 'menu_class' => 'imp-m-menu__list', 'depth' => 2 ) );
		}
		$args['menu'] = $menus[0]->term_id;
	}
	return wp_nav_menu( $args );
}

add_action( 'wp_body_open', function () {
	$links = imp_m_links();
	$date  = imp_m_jalali_parts( null, true );
	?>
	<div class="imp-m imp-m-top">
		<header class="imp-m-header" role="banner">
			<div class="imp-m-header__date" aria-label="<?php esc_attr_e( 'Today', 'imp-mobile' ); ?>">
				<span><?php echo esc_html( $date['day'] . ' ' . $date['month'] ); ?></span>
				<span><?php echo esc_html( $date['year'] ); ?></span>
			</div>

			<a class="imp-m-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<img src="<?php echo esc_url( imp_m_asset( 'img/logo.svg' ) ); ?>" width="152" height="110" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			</a>

			<button class="imp-m-header__burger" type="button" aria-controls="imp-m-menu" aria-expanded="false">
				<span class="screen-reader-text"><?php esc_html_e( 'Open menu', 'imp-mobile' ); ?></span>
				<?php imp_m_icon( 'burger' ); ?>
			</button>
		</header>

		<div class="imp-m-search" data-state="closed">
				<form class="imp-m-search__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<label class="screen-reader-text" for="imp-m-search-input"><?php esc_html_e( 'Search', 'imp-mobile' ); ?></label>
					<input id="imp-m-search-input" class="imp-m-search__input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'جستجو…', 'imp-mobile' ); ?>" tabindex="-1">
				</form>
				<button class="imp-m-search__toggle" type="button" aria-controls="imp-m-search-input" aria-expanded="false">
					<span class="screen-reader-text"><?php esc_html_e( 'Search', 'imp-mobile' ); ?></span>
					<?php imp_m_icon( 'search' ); ?>
				</button>
		</div>

		<?php if ( ! is_front_page() ) : ?>
			<a class="imp-m-back" href="<?php echo esc_url( wp_get_referer() ?: home_url( '/' ) ); ?>" data-imp-back>
				<?php imp_m_icon( 'caret-back' ); ?>
				<span><?php esc_html_e( 'Back', 'imp-mobile' ); ?></span>
			</a>
		<?php endif; ?>

		<nav id="imp-m-menu" class="imp-m-menu" aria-label="<?php esc_attr_e( 'Main menu', 'imp-mobile' ); ?>" hidden>
			<button class="imp-m-menu__close" type="button">
				<span class="screen-reader-text"><?php esc_html_e( 'Close menu', 'imp-mobile' ); ?></span>
				<?php imp_m_icon( 'close' ); ?>
			</button>
			<img class="imp-m-menu__lion" src="<?php echo esc_url( imp_m_asset( 'img/lion.svg' ) ); ?>" alt="" aria-hidden="true">
			<?php echo imp_m_menu(); // phpcs:ignore WordPress.Security.EscapeOutput -- core menu output. ?>
		</nav>

		<a class="imp-m-donate" href="<?php echo esc_url( $links['donate'] ); ?>"><?php echo esc_html_x( 'همیاری', 'donate tab', 'imp-mobile' ); ?></a>
	</div>
	<?php
}, 5 );
