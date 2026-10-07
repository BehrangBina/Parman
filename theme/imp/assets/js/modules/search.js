/**
 * Search (Figma Search-Bar): the round button opens the bar and turns into ✕ (closes it);
 * the magnifier at the far end, or Enter, searches.
 */

export function init() {
	const search = document.querySelector( '.imp-search' );
	if ( ! search ) {
		return;
	}
	const toggle = search.querySelector( '.imp-search__toggle' );
	const input = search.querySelector( '.imp-search__input' );
	const submit = search.querySelector( '.imp-search__submit' );

	const setOpen = ( isOpen ) => {
		search.dataset.state = isOpen ? 'open' : 'closed';
		toggle.setAttribute( 'aria-expanded', String( isOpen ) );
		input.tabIndex = submit.tabIndex = isOpen ? 0 : -1;
		if ( isOpen ) {
			input.focus();
		} else {
			input.blur();
		}
	};

	toggle.addEventListener( 'click', () => setOpen( search.dataset.state !== 'open' ) );
	search.querySelector( 'form' ).addEventListener( 'submit', ( event ) => {
		if ( ! input.value.trim() ) {
			event.preventDefault();
			input.focus();
		}
	} );
	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' && search.dataset.state === 'open' ) {
			setOpen( false );
		}
	} );
}
