<?php
/**
 * Site header for both layouts, printed at wp_body_open:
 *  - mobile (< 1024px): blue bar with burger + full-screen menu (Figma Mobile-HiFi-Farsi)
 *  - desktop (≥ 1024px): blue bar with the menu split around the logo + dropdowns (Figma Desktop-HiFi-Farsi)
 * Plus the shared search, Back link and the fixed Donate tab. CSS decides which header shows.
 *
 * Menus (Appearance → Menus): "Mobile menu" and "Desktop menu" locations. Until they are
 * assigned, both fall back to Neve's "primary" menu, then to the first menu that exists.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	register_nav_menus( array(
		'imp-mobile'  => 'Mobile menu (IMP)',
		'imp-desktop' => 'Desktop menu (IMP)',
	) );
}, 20 );

/** Menu term for a location, falling back to "primary", then the first menu. */
function imp_m_menu_for( $location ) {
	$locations = get_nav_menu_locations();
	foreach ( array( $location, 'primary' ) as $loc ) {
		if ( ! empty( $locations[ $loc ] ) && wp_get_nav_menu_object( $locations[ $loc ] ) ) {
			return (int) $locations[ $loc ];
		}
	}
	$menus = wp_get_nav_menus();
	return $menus ? (int) $menus[0]->term_id : 0;
}

function imp_m_menu() {
	$menu = imp_m_menu_for( 'imp-mobile' );
	if ( ! $menu ) {
		return wp_page_menu( array( 'echo' => false, 'menu_class' => 'imp-m-menu__list', 'depth' => 2 ) );
	}
	return wp_nav_menu( array(
		'menu'        => $menu,
		'container'   => false,
		'menu_class'  => 'imp-m-menu__list',
		'fallback_cb' => false,
		'depth'       => 2,
		'echo'        => false,
	) );
}

/**
 * Desktop menu as a two-level tree: [ [ 'item' => WP_Post, 'children' => [WP_Post…] ], … ].
 */
function imp_m_desktop_menu_tree() {
	$menu  = imp_m_menu_for( 'imp-desktop' );
	$items = $menu ? wp_get_nav_menu_items( $menu ) : array();
	if ( ! $items ) {
		return array();
	}
	_wp_menu_item_classes_by_context( $items ); // adds current-menu-item classes
	$tree = array();
	foreach ( $items as $item ) {
		if ( ! $item->menu_item_parent ) {
			$tree[ $item->ID ] = array( 'item' => $item, 'children' => array() );
		}
	}
	foreach ( $items as $item ) {
		if ( $item->menu_item_parent && isset( $tree[ $item->menu_item_parent ] ) ) {
			$tree[ $item->menu_item_parent ]['children'][] = $item;
		}
	}
	return array_values( $tree );
}

function imp_m_desktop_nav_group( $entries, $side ) {
	if ( ! $entries ) {
		return;
	}
	?>
	<ul class="imp-d-nav imp-d-nav--<?php echo esc_attr( $side ); ?>">
		<?php
		foreach ( $entries as $i => $entry ) :
			$item    = $entry['item'];
			$current = array_intersect( (array) $item->classes, array( 'current-menu-item', 'current-menu-ancestor', 'current-menu-parent' ) );
			$sub_id  = 'imp-d-sub-' . $item->ID;
			?>
			<li class="imp-d-nav__item<?php echo $entry['children'] ? ' has-sub' : ''; ?><?php echo $current ? ' is-current' : ''; ?>">
				<?php if ( $entry['children'] && ( '#' === $item->url || '' === $item->url ) ) : ?>
					<button class="imp-d-nav__link imp-d-nav__toggle" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $sub_id ); ?>">
						<?php echo esc_html( $item->title ); ?>
						<?php imp_m_icon( 'dropdown-white' ); ?>
					</button>
				<?php else : ?>
					<a class="imp-d-nav__link" href="<?php echo esc_url( $item->url ); ?>"<?php echo $item->target ? ' target="' . esc_attr( $item->target ) . '" rel="noopener"' : ''; ?>>
						<?php echo esc_html( $item->title ); ?>
					</a>
					<?php if ( $entry['children'] ) : ?>
						<button class="imp-d-nav__toggle imp-d-nav__toggle--icon" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $sub_id ); ?>">
							<span class="screen-reader-text"><?php echo esc_html( $item->title ); ?></span>
							<?php imp_m_icon( 'dropdown-white' ); ?>
						</button>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( $entry['children'] ) : ?>
					<ul class="imp-d-nav__sub" id="<?php echo esc_attr( $sub_id ); ?>">
						<?php foreach ( $entry['children'] as $child ) : ?>
							<li><a href="<?php echo esc_url( $child->url ); ?>"<?php echo $child->target ? ' target="' . esc_attr( $child->target ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $child->title ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

add_action( 'wp_body_open', function () {
	$links = imp_m_links();
	$date  = imp_m_jalali_parts( null, true );
	$tree  = imp_m_desktop_menu_tree();
	$half  = (int) ceil( count( $tree ) / 2 );
	?>
	<div class="imp-m imp-m-top">
		<?php /* ---------- Mobile header ---------- */ ?>
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

		<?php /* ---------- Desktop header ---------- */ ?>
		<header class="imp-d-header" aria-label="<?php esc_attr_e( 'Site header', 'imp-mobile' ); ?>">
			<span class="imp-d-header__date"><?php echo esc_html( $date['day'] . ' ' . $date['month'] . ' ' . $date['year'] ); ?></span>
			<nav class="imp-d-header__nav" aria-label="<?php esc_attr_e( 'Main menu', 'imp-mobile' ); ?>">
				<?php imp_m_desktop_nav_group( array_slice( $tree, 0, $half ), 'start' ); ?>
				<a class="imp-d-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<img src="<?php echo esc_url( imp_m_asset( 'img/logo.svg' ) ); ?>" width="182" height="131" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				</a>
				<?php imp_m_desktop_nav_group( array_slice( $tree, $half ), 'end' ); ?>
			</nav>
		</header>

		<?php /* ---------- Shared: search (closed = round button, open = long bar) ---------- */ ?>
		<div class="imp-m-search" data-state="closed">
			<button class="imp-m-search__toggle" type="button" aria-controls="imp-m-search-input" aria-expanded="false">
				<span class="screen-reader-text"><?php esc_html_e( 'Search', 'imp-mobile' ); ?></span>
				<span class="imp-m-search__icon-open"><?php imp_m_icon( 'search' ); ?></span>
				<span class="imp-m-search__icon-close"><?php imp_m_icon( 'close' ); ?></span>
			</button>
			<form class="imp-m-search__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="imp-m-search-input"><?php esc_html_e( 'Search', 'imp-mobile' ); ?></label>
				<input id="imp-m-search-input" class="imp-m-search__input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'جستجو…', 'imp-mobile' ); ?>" tabindex="-1">
				<button class="imp-m-search__submit" type="submit" tabindex="-1">
					<span class="screen-reader-text"><?php esc_html_e( 'Search', 'imp-mobile' ); ?></span>
					<?php imp_m_icon( 'search' ); ?>
				</button>
			</form>
		</div>

		<?php if ( ! is_front_page() ) : ?>
			<a class="imp-m-back" href="<?php echo esc_url( wp_get_referer() ?: home_url( '/' ) ); ?>" data-imp-back>
				<?php imp_m_icon( 'caret-back' ); ?>
				<span><?php esc_html_e( 'Back', 'imp-mobile' ); ?></span>
			</a>
		<?php endif; ?>

		<?php /* ---------- Mobile full-screen menu ---------- */ ?>
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
