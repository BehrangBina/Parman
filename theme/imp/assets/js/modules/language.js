/**
 * Page direction for translations. GTranslate (free) translates in the browser through Google
 * Translate, which marks <html> with "translated-ltr" / "translated-rtl" but leaves dir="rtl".
 * English and German must read left-to-right, so dir follows the translation; other modules
 * (the document reader) listen for "imp:direction" to lay themselves out again.
 *
 * Text that was hidden while the page was translated (e.g. the reader) stays untranslated, so
 * a module that reveals content dispatches "imp:content-shown" and it is translated then.
 */

const ORIGINAL_DIR = document.documentElement.getAttribute( 'dir' ) || 'rtl';

export function init() {
	const html = document.documentElement;
	let current = html.getAttribute( 'dir' ) || ORIGINAL_DIR;

	const apply = () => {
		let dir = ORIGINAL_DIR;
		if ( html.classList.contains( 'translated-ltr' ) ) {
			dir = 'ltr';
		} else if ( html.classList.contains( 'translated-rtl' ) ) {
			dir = 'rtl';
		}
		if ( dir === current ) {
			return;
		}
		current = dir;
		html.setAttribute( 'dir', dir );
		document.body.classList.toggle( 'rtl', dir === 'rtl' ); // WordPress/Neve's own RTL hook
		window.dispatchEvent( new CustomEvent( 'imp:direction', { detail: { dir } } ) );
	};

	new MutationObserver( apply ).observe( html, { attributes: true, attributeFilter: [ 'class' ] } );
	apply();

	window.addEventListener( 'imp:content-shown', translateAgain );
}

/**
 * Translate newly shown text into the language currently chosen (GTranslate's own function).
 */
function translateAgain() {
	if ( typeof window.doGTranslate !== 'function' || ! /translated-(ltr|rtl)/.test( document.documentElement.className ) ) {
		return;
	}
	// GTranslate keeps the choice in localStorage: __GT_TRANSLATE_LANGS = {"srcLang":"fa","tgtLang":"en"}.
	let langs = null;
	try {
		langs = JSON.parse( window.localStorage.getItem( '__GT_TRANSLATE_LANGS' ) || 'null' );
	} catch ( error ) {
		return; // storage blocked: the visitor can pick the flag again
	}
	if ( langs && langs.srcLang && langs.tgtLang && langs.srcLang !== langs.tgtLang ) {
		window.doGTranslate( `${ langs.srcLang }|${ langs.tgtLang }` );
	}
}
