/**
 * Party-document reader (Figma Read-Maramname 805:6354 cover, Maram-01/02 text pages):
 * the page text is laid out with CSS columns, one screen-sized column per page, and moved
 * with translateX. RTL: the next page lies to the left, so swiping right advances.
 */

import { setScrollLock } from '@imp/scroll-lock';

const SWIPE_MIN_PX = 40;
const PERSIAN_DIGITS = '۰۱۲۳۴۵۶۷۸۹';
const toPersianDigits = ( number ) => String( number ).replace( /\d/g, ( digit ) => PERSIAN_DIGITS[ digit ] );

export function init() {
	document.querySelectorAll( '[data-imp-reader]' ).forEach( setUpReader );
}

/**
 * @param {HTMLElement} reader The reader overlay.
 */
function setUpReader( reader ) {
	const cover = reader.querySelector( '[data-imp-reader-cover]' );
	const pages = reader.querySelector( '[data-imp-reader-pages]' );
	const viewport = reader.querySelector( '[data-imp-reader-viewport]' );
	const flow = reader.querySelector( '[data-imp-reader-flow]' );
	const count = reader.querySelector( '[data-imp-reader-count]' );
	const nextOnPages = pages.querySelector( '[data-imp-reader-next]' );
	const isRtl = getComputedStyle( reader ).direction === 'rtl';
	let page = 0;
	let total = 1;
	let step = 0;

	// Measure how many screen-wide columns the text needs.
	const layout = () => {
		const width = viewport.clientWidth;
		const gap = parseFloat( getComputedStyle( flow ).columnGap ) || 0;
		flow.style.columnWidth = `${ width }px`;
		step = width + gap;
		total = Math.max( 1, Math.round( ( flow.scrollWidth + gap ) / step ) );
		page = Math.min( page, total - 1 );
		render();
	};

	const render = () => {
		flow.style.transform = `translateX(${ ( isRtl ? 1 : -1 ) * page * step }px)`;
		// Figma Maram-01: "ورق بزن" (turn the page) on the first page, then "صفحه n از N".
		if ( total < 2 ) {
			count.textContent = '';
		} else if ( page === 0 ) {
			count.textContent = 'ورق بزن';
		} else {
			count.textContent = `صفحه ${ toPersianDigits( page + 1 ) } از ${ toPersianDigits( total ) }`;
		}
		nextOnPages.disabled = page >= total - 1;
	};

	const showCover = () => {
		pages.hidden = true;
		cover.hidden = false;
	};
	const showPages = () => {
		cover.hidden = true;
		pages.hidden = false;
		layout();
	};

	// +1 = forward, -1 = back; from the cover forward opens the pages, from page 1 back returns to it.
	const go = ( delta ) => {
		if ( pages.hidden ) {
			if ( delta > 0 ) {
				showPages();
			}
			return;
		}
		if ( page + delta < 0 ) {
			showCover();
			return;
		}
		page = Math.max( 0, Math.min( total - 1, page + delta ) );
		render();
	};

	const open = ( event ) => {
		event?.preventDefault();
		reader.hidden = false;
		setScrollLock( true );
		page = 0;
		showCover();
		cover.querySelector( '[data-imp-reader-close]' )?.focus( { preventScroll: true } );
	};
	const close = () => {
		if ( reader.hidden ) {
			return;
		}
		reader.hidden = true;
		setScrollLock( false );
	};

	document.querySelectorAll( '[data-imp-reader-open]' ).forEach( ( button ) => button.addEventListener( 'click', open ) );
	reader.querySelectorAll( '[data-imp-reader-close]' ).forEach( ( button ) => button.addEventListener( 'click', close ) );
	reader.querySelectorAll( '[data-imp-reader-next]' ).forEach( ( button ) => button.addEventListener( 'click', () => go( 1 ) ) );
	reader.querySelectorAll( '[data-imp-reader-prev]' ).forEach( ( button ) => button.addEventListener( 'click', () => go( -1 ) ) );

	document.addEventListener( 'keydown', ( event ) => {
		if ( reader.hidden ) {
			return;
		}
		if ( event.key === 'Escape' ) {
			close();
		} else if ( event.key === 'ArrowLeft' ) {
			go( isRtl ? 1 : -1 );
		} else if ( event.key === 'ArrowRight' ) {
			go( isRtl ? -1 : 1 );
		}
	} );

	let touchStartX = null;
	reader.addEventListener( 'touchstart', ( event ) => {
		touchStartX = event.touches[ 0 ].clientX;
	}, { passive: true } );
	reader.addEventListener( 'touchend', ( event ) => {
		if ( touchStartX === null ) {
			return;
		}
		const distance = event.changedTouches[ 0 ].clientX - touchStartX;
		touchStartX = null;
		if ( Math.abs( distance ) >= SWIPE_MIN_PX ) {
			go( ( distance > 0 ) === isRtl ? 1 : -1 );
		}
	} );

	window.addEventListener( 'resize', () => {
		if ( ! pages.hidden ) {
			layout();
		}
	} );
	document.fonts?.ready.then( () => {
		if ( ! pages.hidden ) {
			layout();
		}
	} );
}
