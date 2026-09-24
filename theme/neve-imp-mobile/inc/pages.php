<?php
/**
 * Pages with a dedicated mobile layout. On these pages Neve's <main> is hidden
 * below 960px and the registered renderer prints the Figma layout instead.
 *
 * Keys are page slugs, "front" for the front page, or "category:<slug>" for a category archive.
 * Filter `imp_m_custom_pages` to add or remove pages.
 */

defined( 'ABSPATH' ) || exit;

function imp_m_custom_pages() {
	return apply_filters( 'imp_m_custom_pages', array(
		'front'               => 'imp_m_render_home',
		'donate'              => 'imp_m_render_donate',
		'bcd31-contact-us'    => 'imp_m_render_contact', // live slug
		'contact-us'          => 'imp_m_render_contact',
		'category:statements' => 'imp_m_render_statements',
		'news'                => 'imp_m_render_news',
		'نشریه-ایرانگرا'      => 'imp_m_render_magazine', // live slug (Persian)
		'irangara'            => 'imp_m_render_magazine',
	) );
}

/** Renderer for the current request, or null. */
function imp_m_current_renderer() {
	$pages = imp_m_custom_pages();
	$key   = null;
	if ( is_front_page() ) {
		$key = 'front';
	} elseif ( is_page() ) {
		$key = urldecode( get_post_field( 'post_name', get_queried_object_id() ) ); // Persian slugs are stored encoded
	} elseif ( is_category() ) {
		$key = 'category:' . get_queried_object()->slug;
	}
	return ( $key && isset( $pages[ $key ] ) ) ? $pages[ $key ] : null;
}

add_filter( 'body_class', function ( $classes ) {
	if ( imp_m_current_renderer() ) {
		$classes[] = 'imp-m-custom';
	}
	return $classes;
} );

/** Title for the current page or archive. */
function imp_m_current_title() {
	if ( is_category() || is_tag() || is_tax() ) {
		return single_term_title( '', false );
	}
	return get_the_title( get_queried_object_id() );
}

add_action( 'wp_body_open', function () {
	$renderer = imp_m_current_renderer();
	if ( $renderer && is_callable( $renderer ) ) {
		call_user_func( $renderer );
	}
}, 6 );

/**
 * Page title with the gold ornaments on both sides (Figma "Text-About").
 */
function imp_m_ornament_title( $title, $tag = 'h1' ) {
	$tag = tag_escape( $tag );
	?>
	<div class="imp-m-otitle">
		<img class="imp-m-otitle__orn" src="<?php echo esc_url( imp_m_asset( 'img/ornament.svg' ) ); ?>" alt="" aria-hidden="true">
		<<?php echo $tag; ?> class="imp-m-otitle__text"><?php echo esc_html( $title ); ?></<?php echo $tag; ?>>
		<img class="imp-m-otitle__orn imp-m-otitle__orn--flip" src="<?php echo esc_url( imp_m_asset( 'img/ornament.svg' ) ); ?>" alt="" aria-hidden="true">
	</div>
	<?php
}
