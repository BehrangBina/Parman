/**
 * Party-document reader (Figma Read-Maramname 805:6354 cover, Maram-01/02 text pages):
 * the page text is laid out with CSS columns, one screen-sized column per page, and moved
 * with translateX. RTL: the next page lies to the left, so swiping right advances.
 *
 * Page turn: a copy of the page being turned ("leaf") is laid over the text and folds
 * around the spine — the right edge in Persian, the left edge in LTR languages — while the
 * real text underneath has already jumped to its new page.
 */

import { setScrollLock } from '@imp/scroll-lock';

const SWIPE_MIN_PX = 40;
const TURN_MS = 650;
const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
const PERSIAN_DIGITS = '۰۱۲۳۴۵۶۷۸۹';
const toPersianDigits = ( number ) => String( number ).replace( /\d/g, ( digit ) => PERSIAN_DIGITS[ digit ] );

// The counter is written after a translation has run, so it brings its own words (see language.js).
const COUNTER = {
	fa: { turn: 'ورق بزن', page: ( n, total ) => `صفحه ${ toPersianDigits( n ) } از ${ toPersianDigits( total ) }` },
	en: { turn: 'Turn the page', page: ( n, total ) => `Page ${ n } of ${ total }` },
	de: { turn: 'Umblättern', page: ( n, total ) => `Seite ${ n } von ${ total }` },
};
const counterWords = () => COUNTER[ ( document.documentElement.lang || 'fa' ).slice( 0, 2 ) ] || COUNTER.fa;

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
	// Direction can change while the page is open (translation to English/German, see language.js).
	let isRtl = getComputedStyle( reader ).direction === 'rtl';
	let page = 0;
	let total = 1;
	let step = 0;
	let finishTurn = null; // completes the running page turn at once (a new turn may start first)

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

	const offset = ( index ) => `translateX(${ ( isRtl ? 1 : -1 ) * index * step }px)`;

	const render = () => {
		flow.style.transform = offset( page );
		// Figma Maram-01: "ورق بزن" (turn the page) on the first page, then "صفحه n از N".
		const words = counterWords();
		if ( total < 2 ) {
			count.textContent = '';
		} else if ( page === 0 ) {
			count.textContent = words.turn;
		} else {
			count.textContent = words.page( page + 1, total );
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
		window.dispatchEvent( new CustomEvent( 'imp:content-shown' ) ); // translate it if the page is translated
	};

	// +1 = forward, -1 = back; from the cover forward opens the pages, from page 1 back returns to it.
	const go = ( delta ) => {
		if ( pages.hidden ) {
			if ( delta > 0 ) {
				showPages();
			}
			return;
		}
		finishTurn?.(); // settle a running turn first, so "page" is where the reader really is
		if ( page + delta < 0 ) {
			showCover();
			return;
		}
		const target = Math.max( 0, Math.min( total - 1, page + delta ) );
		if ( target === page ) {
			return;
		}
		turn( page, target );
	};

	// Forward: the old page folds away over the new one. Back: the previous page folds back in
	// over the current one, and the text underneath switches when it lands.
	const turn = ( from, to ) => {
		finishTurn?.();
		const forward = to > from;
		if ( reducedMotion.matches || ! viewport.animate ) {
			page = to;
			render();
			return;
		}

		const leaf = document.createElement( 'div' );
		leaf.className = 'imp-reader__leaf';
		leaf.setAttribute( 'aria-hidden', 'true' );
		const copy = flow.cloneNode( true );
		copy.removeAttribute( 'data-imp-reader-flow' );
		copy.style.transform = offset( forward ? from : to );
		const shade = document.createElement( 'span' );
		shade.className = 'imp-reader__leaf-shade';
		leaf.append( copy, shade );
		viewport.append( leaf );

		if ( forward ) {
			page = to;
			render();
		}
		const folded = `rotateY(${ isRtl ? 180 : -180 }deg)`;
		const frames = forward ? [ { transform: 'rotateY(0deg)' }, { transform: folded } ] : [ { transform: folded }, { transform: 'rotateY(0deg)' } ];
		const timing = { duration: TURN_MS, easing: 'cubic-bezier(.45, .05, .35, 1)' };
		shade.animate( forward ? [ { opacity: 0 }, { opacity: 1 } ] : [ { opacity: 1 }, { opacity: 0 } ], timing );
		const animation = leaf.animate( frames, timing );
		finishTurn = () => {
			finishTurn = null;
			animation.onfinish = null;
			animation.cancel();
			leaf.remove();
			if ( ! forward ) {
				page = to;
				render();
			}
		};
		animation.onfinish = finishTurn;
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

	// A translation changes the text length (new page breaks) and maybe the direction.
	let relayoutTimer = 0;
	const relayoutSoon = () => {
		clearTimeout( relayoutTimer );
		relayoutTimer = setTimeout( () => {
			isRtl = getComputedStyle( reader ).direction === 'rtl';
			if ( ! pages.hidden ) {
				layout();
			}
		}, 250 );
	};
	window.addEventListener( 'imp:direction', relayoutSoon );
	new MutationObserver( relayoutSoon ).observe( flow, { childList: true, subtree: true, characterData: true } );
	document.fonts?.ready.then( () => {
		if ( ! pages.hidden ) {
			layout();
		}
	} );
}
