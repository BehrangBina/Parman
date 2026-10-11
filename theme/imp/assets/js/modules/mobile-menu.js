/**
 * Mobile full-screen menu (Figma Burger Menu 697:2702): slides in from the burger's side,
 * submenus open with a chevron, tapping a link closes the panel.
 */

import { setScrollLock } from '@imp/scroll-lock';

const HIDE_AFTER_MS = 400; // matches the CSS slide-out transition

export function init() {
	const burger = document.querySelector( '.imp-header__burger' );
	const menu = document.getElementById( 'imp-menu' );
	if ( ! burger || ! menu ) {
		return;
	}
	let hideTimer;

	const open = () => {
		clearTimeout( hideTimer );
		menu.hidden = false;
		void menu.offsetWidth; // apply the off-screen position first, so the slide animates
		menu.classList.add( 'is-open' );
		setScrollLock( true );
		burger.setAttribute( 'aria-expanded', 'true' );
		menu.querySelector( '.imp-menu__close' )?.focus( { preventScroll: true } );
	};

	const close = ( restoreFocus = true ) => {
		if ( menu.hidden ) {
			return;
		}
		menu.classList.remove( 'is-open' );
		setScrollLock( false );
		burger.setAttribute( 'aria-expanded', 'false' );
		hideTimer = setTimeout( () => {
			menu.hidden = true;
		}, HIDE_AFTER_MS );
		if ( restoreFocus ) {
			burger.focus( { preventScroll: true } );
		}
	};

	burger.addEventListener( 'click', open );
	menu.querySelector( '.imp-menu__close' )?.addEventListener( 'click', () => close() );
	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' ) {
			close();
		}
	} );

	// A link closes the panel; a parent whose link is just "#" (e.g. مدیا) toggles its submenu.
	menu.addEventListener( 'click', ( event ) => {
		const link = event.target.closest( 'a[href]' );
		if ( ! link ) {
			return;
		}
		const toggle = link.getAttribute( 'href' ) === '#' && link.parentElement.querySelector( ':scope > .imp-sub-toggle' );
		if ( toggle ) {
			event.preventDefault();
			toggle.click();
			return;
		}
		close( false );
	} );

	addSubmenuToggles( menu );
}

/**
 * WordPress prints nested <ul>s; add a chevron button before each. The icon is the theme's
 * chevron.svg, printed once in a <template> by templates/layout/header.php.
 *
 * @param {HTMLElement} menu Menu panel.
 */
function addSubmenuToggles( menu ) {
	const icon = document.getElementById( 'imp-tpl-chevron' );
	menu.querySelectorAll( '.menu-item-has-children, .page_item_has_children' ).forEach( ( item, index ) => {
		const sub = item.querySelector( ':scope > ul' );
		if ( ! sub ) {
			return;
		}
		sub.id = sub.id || `imp-sub-${ index }`;

		const button = document.createElement( 'button' );
		button.type = 'button';
		button.className = 'imp-sub-toggle';
		button.setAttribute( 'aria-expanded', 'false' );
		button.setAttribute( 'aria-controls', sub.id );
		if ( icon ) {
			button.append( icon.content.cloneNode( true ) );
		}
		const label = document.createElement( 'span' );
		label.className = 'screen-reader-text';
		label.textContent = item.querySelector( 'a' )?.textContent ?? '';
		button.append( label );

		item.insertBefore( button, sub );
		button.addEventListener( 'click', () => {
			const isOpen = button.getAttribute( 'aria-expanded' ) !== 'true';
			button.setAttribute( 'aria-expanded', String( isOpen ) );
			sub.classList.toggle( 'is-open', isOpen );
		} );
	} );
}
