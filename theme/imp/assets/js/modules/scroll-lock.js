/**
 * Stops the page behind an overlay (mobile menu, document reader) from scrolling.
 * Counts owners, so closing one overlay does not unlock while another is still open.
 */

let owners = 0;

/**
 * @param {boolean} on True to lock, false to release one lock.
 */
export function setScrollLock( on ) {
	owners = Math.max( 0, owners + ( on ? 1 : -1 ) );
	document.body.classList.toggle( 'imp-scroll-lock', owners > 0 );
}
