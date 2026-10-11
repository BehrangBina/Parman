/**
 * Footer "back to top" arrow (Figma desktop Go-Up 1124:3722).
 */

export function init() {
	document.querySelectorAll( '[data-imp-top]' ).forEach( ( button ) => {
		button.addEventListener( 'click', () => {
			const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
			window.scrollTo( { top: 0, behavior: reduceMotion ? 'auto' : 'smooth' } );
		} );
	} );
}
