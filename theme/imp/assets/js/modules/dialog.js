/**
 * Popups (Figma Overlay-MembershipFee): [data-imp-dialog="<id>"] opens the <dialog>,
 * [data-imp-dialog-close] or a tap on the backdrop closes it. A trigger that is a link keeps
 * working as a link when its dialog is not shown (no JS dialog support, or hidden container).
 */

export function init() {
	document.querySelectorAll( '[data-imp-dialog]' ).forEach( ( trigger ) => {
		const dialog = document.getElementById( trigger.dataset.impDialog );
		if ( ! dialog || typeof dialog.showModal !== 'function' ) {
			return;
		}
		trigger.addEventListener( 'click', ( event ) => {
			if ( ! dialog.parentElement.getClientRects().length ) {
				return;
			}
			event.preventDefault();
			dialog.showModal();
		} );
		dialog.addEventListener( 'click', ( event ) => {
			// The padding sits on .imp-dialog__inner, so a click on the <dialog> itself is the backdrop.
			if ( event.target === dialog || event.target.closest( '[data-imp-dialog-close]' ) ) {
				dialog.close();
			}
		} );
	} );
}
