/**
 * "Back" links and the document screen's ✕ ([data-imp-back]): go to the previous page in
 * history. When the browser cannot go back (new tab, first entry — history.back() is then a
 * silent no-op), fall back to the link's href (the server-side referrer, or the home page).
 */

const FALLBACK_AFTER_MS = 500;

export function init() {
	document.querySelectorAll( '[data-imp-back]' ).forEach( ( link ) => {
		link.addEventListener( 'click', ( event ) => {
			if ( history.length <= 1 ) {
				return; // nothing to go back to: follow the href
			}
			event.preventDefault();
			let left = false;
			window.addEventListener( 'pagehide', () => {
				left = true;
			}, { once: true } );
			history.back();
			setTimeout( () => {
				if ( ! left ) {
					location.href = link.href;
				}
			}, FALLBACK_AFTER_MS );
		} );
	} );
}
