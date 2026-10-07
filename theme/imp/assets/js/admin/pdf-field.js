/* Admin: "PDF file" box — opens the Media Library filtered to PDFs (IMP\Admin\Pdf_Meta_Field). */
( function () {
	'use strict';
	var button = document.getElementById( 'imp-pdf-pick' );
	var input = document.getElementById( 'imp-pdf-url' );
	if ( ! button || ! input || ! window.wp || ! wp.media ) {
		return;
	}
	button.addEventListener( 'click', function () {
		var frame = wp.media( { title: 'انتخاب PDF', library: { type: 'application/pdf' }, multiple: false } );
		frame.on( 'select', function () {
			input.value = frame.state().get( 'selection' ).first().get( 'url' );
		} );
		frame.open();
	} );
}() );
