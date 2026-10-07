<?php
/**
 * Site header for both layouts (CSS shows one): the mobile bar with burger + full-screen menu
 * (Figma Mobile NavBar 881:13007, Burger Menu 697:2702) and the desktop bar with the menu split
 * around the logo (Figma Desktop NAVBAR-FINAL 1171:5178, dropdowns in Components 1181:6348).
 * Plus the shared search, Back link and Donate tab.
 *
 * @package IMP
 *
 * @var array $args {
 *     date: array{day,month,year}, nav_start: array[], nav_end: array[], mobile_menu: string,
 *     donate_url: string, show_back: bool, back_url: string, search_query: string
 * }
 */

defined( 'ABSPATH' ) || exit;

$site_name = get_bloginfo( 'name' );
$home_url  = home_url( '/' );
?>
<div class="imp-ui imp-top">
	<?php // Figma: Mobile NavBar (881:13007). ?>
	<header class="imp-header" role="banner">
		<div class="imp-header__date" aria-label="<?php esc_attr_e( 'Today', 'imp' ); ?>">
			<span><?php echo esc_html( $args['date']['day'] . ' ' . $args['date']['month'] ); ?></span>
			<span><?php echo esc_html( $args['date']['year'] ); ?></span>
		</div>

		<a class="imp-header__logo" href="<?php echo esc_url( $home_url ); ?>" rel="home">
			<img src="<?php echo esc_url( imp_asset( 'img/logo.svg' ) ); ?>" width="152" height="110" alt="<?php echo esc_attr( $site_name ); ?>">
		</a>

		<button class="imp-header__burger" type="button" aria-controls="imp-menu" aria-expanded="false">
			<span class="screen-reader-text"><?php esc_html_e( 'Open menu', 'imp' ); ?></span>
			<?php imp_icon( 'burger' ); ?>
		</button>
	</header>

	<?php // Figma: Desktop NAVBAR-FINAL (1171:5178). ?>
	<header class="imp-d-header" aria-label="<?php esc_attr_e( 'Site header', 'imp' ); ?>">
		<span class="imp-d-header__date"><?php echo esc_html( $args['date']['day'] . ' ' . $args['date']['month'] . ' ' . $args['date']['year'] ); ?></span>
		<nav class="imp-d-header__nav" aria-label="<?php esc_attr_e( 'Main menu', 'imp' ); ?>">
			<?php
			imp_component(
				'desktop-nav-group',
				array(
					'entries' => $args['nav_start'],
					'side'    => 'start',
				)
			);
			?>
			<a class="imp-d-header__logo" href="<?php echo esc_url( $home_url ); ?>" rel="home">
				<img src="<?php echo esc_url( imp_asset( 'img/logo.svg' ) ); ?>" width="182" height="131" alt="<?php echo esc_attr( $site_name ); ?>">
			</a>
			<?php
			imp_component(
				'desktop-nav-group',
				array(
					'entries' => $args['nav_end'],
					'side'    => 'end',
				)
			);
			?>
		</nav>
	</header>

	<?php imp_component( 'search', array( 'query' => $args['search_query'] ) ); ?>

	<?php if ( $args['show_back'] ) : ?>
		<?php // Figma: Icon-Back (881:13552). ?>
		<a class="imp-back" href="<?php echo esc_url( $args['back_url'] ); ?>" data-imp-back>
			<?php imp_icon( 'caret-back' ); ?>
			<span><?php esc_html_e( 'Back', 'imp' ); ?></span>
		</a>
	<?php endif; ?>

	<?php // Figma: Burger Menu (697:2702). ?>
	<nav id="imp-menu" class="imp-menu" aria-label="<?php esc_attr_e( 'Main menu', 'imp' ); ?>" hidden>
		<?php
		imp_component(
			'close-button',
			array(
				'class' => 'imp-menu__close',
				'label' => __( 'Close menu', 'imp' ),
			)
		);
		?>
		<img class="imp-menu__lion" src="<?php echo esc_url( imp_asset( 'img/lion.svg' ) ); ?>" alt="" aria-hidden="true">
		<?php echo $args['mobile_menu']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core wp_nav_menu() output. ?>
	</nav>

	<?php // Figma: Donate tab (Components 1181:6348). ?>
	<a class="imp-donate" href="<?php echo esc_url( $args['donate_url'] ); ?>"><?php echo esc_html_x( 'همیاری', 'donate tab', 'imp' ); ?></a>
</div>
