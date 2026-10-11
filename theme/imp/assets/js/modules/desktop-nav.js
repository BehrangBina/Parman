/**
 * Desktop dropdowns (Figma Components 1181:6348). Hover is pure CSS; this adds click and
 * keyboard support (touch laptops, keyboard users) via the chevron or a "#" parent label.
 */

export function init() {
	const toggles = document.querySelectorAll( '.imp-d-nav__toggle' );
	if ( ! toggles.length ) {
		return;
	}

	const closeAll = ( except ) => {
		toggles.forEach( ( toggle ) => {
			if ( toggle === except ) {
				return;
			}
			toggle.setAttribute( 'aria-expanded', 'false' );
			toggle.closest( '.imp-d-nav__item' ).classList.remove( 'is-open' );
		} );
	};

	toggles.forEach( ( toggle ) => {
		toggle.addEventListener( 'click', ( event ) => {
			event.stopPropagation();
			const item = toggle.closest( '.imp-d-nav__item' );
			const isOpen = ! item.classList.contains( 'is-open' );
			closeAll( toggle );
			item.classList.toggle( 'is-open', isOpen );
			item.querySelectorAll( '.imp-d-nav__toggle' ).forEach( ( other ) => other.setAttribute( 'aria-expanded', String( isOpen ) ) );
		} );
	} );

	document.addEventListener( 'click', ( event ) => {
		if ( ! event.target.closest( '.imp-d-nav__item' ) ) {
			closeAll();
		}
	} );
	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' ) {
			closeAll();
		}
	} );
}
