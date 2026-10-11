/**
 * Small card carousels (Figma mobile About: دبیرخانه / سخنگویان): the track scrolls with
 * touch (CSS scroll-snap); the arrows move one card in the physical direction they point.
 *
 *   <div data-imp-carousel> <button data-imp-carousel-dir="-1"> <ul data-imp-carousel-track> …
 */

export function init() {
	document.querySelectorAll( '[data-imp-carousel]' ).forEach( ( carousel ) => {
		const track = carousel.querySelector( '[data-imp-carousel-track]' );
		if ( ! track ) {
			return;
		}
		const step = () => {
			const item = track.firstElementChild;
			const gap = parseFloat( getComputedStyle( track ).columnGap ) || 0;
			return item ? item.getBoundingClientRect().width + gap : track.clientWidth;
		};
		const arrows = carousel.querySelectorAll( '[data-imp-carousel-dir]' );
		arrows.forEach( ( arrow ) => {
			arrow.addEventListener( 'click', () => {
				track.scrollBy( { left: step() * Number( arrow.dataset.impCarouselDir ), behavior: 'smooth' } );
			} );
		} );
		// Nothing to scroll (e.g. one or two people): keep the arrows' space, hide the arrows.
		const update = () => arrows.forEach( ( arrow ) => {
			arrow.hidden = track.scrollWidth <= track.clientWidth + 1;
		} );
		update();
		window.addEventListener( 'resize', update );
	} );
}
