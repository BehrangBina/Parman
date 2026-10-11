/**
 * Home news carousel arrows (Figma News-Home-Slide 1023:2421): scroll one card in the
 * physical direction of the arrow (the row is RTL, the arrows are not).
 */

const CARD_GAP = 16; // px, same as the CSS gap

export function init() {
	document.querySelectorAll( '.imp-news' ).forEach( ( news ) => {
		const track = news.querySelector( '.imp-news__track' );
		if ( ! track ) {
			return;
		}
		news.querySelectorAll( '.imp-news__arrow' ).forEach( ( arrow ) => {
			arrow.addEventListener( 'click', () => {
				const card = track.querySelector( '.imp-card' );
				const step = card ? card.getBoundingClientRect().width + CARD_GAP : track.clientWidth;
				track.scrollBy( { left: step * Number( arrow.dataset.dir ), behavior: 'smooth' } );
			} );
		} );
	} );
}
