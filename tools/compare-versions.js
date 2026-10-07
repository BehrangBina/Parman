/*
 * Visual equivalence check between two theme versions on the same local site:
 * the current theme vs the pre-refactor theme served with ?imp_compare=old
 * (local-only mu-plugin). For every rendered element it compares position/size and ~30
 * computed style properties. Class names are normalised (imp-m- → imp-) so renames match.
 *
 *   await impCompareVersions()   → { pages, elementsCompared, differences: [...] }
 */
window.impCompareVersions = async function () {
	var PAGES = [ '/', '/donate/', '/bcd31-contact-us/', '/category/statements/', '/news/', '/نشریه-ایرانگرا/', '/partys-motto/', '/party-constitution/', '/affidavit/', '/membership-form/', '/about-us/' ];
	var WIDTHS = [ 393, 1440 ];
	var PROPS = [ 'display', 'position', 'visibility', 'opacity', 'z-index', 'color', 'background-color', 'background-image', 'background-size',
		'background-position', 'border-top', 'border-right', 'border-bottom', 'border-left', 'border-radius', 'box-shadow', 'font-family',
		'font-size', 'font-weight', 'line-height', 'letter-spacing', 'text-align', 'text-decoration-line', 'margin', 'padding', 'gap',
		'transform', 'filter', 'overflow', 'direction', 'writing-mode', 'rotate', 'translate', 'scale' ];

	function normClass( c ) {
		return ( c || '' ).toString().split( /\s+/ ).filter( Boolean )
			.map( function ( x ) { return x === 'imp-m' ? 'imp-ui' : x.replace( /^imp-m-/, 'imp-' ); } )
			.filter( function ( x ) { return ! /^(menu-item-\d+|page-item-\d+|post-\d+|page-id-\d+|wp-image-\d+|attachment-|size-)/.test( x ); } )
			.sort().join( '.' );
	}
	function normValue( v ) {
		return String( v )
			.replace( /url\("?[^")]*\/([^/")]+)"?\)/g, 'url($1)' )   // asset path moved; compare file names
			.replace( /imp-m-/g, 'imp-' );
	}
	async function snapshot( url, width ) {
		var f = document.createElement( 'iframe' );
		f.style.cssText = 'position:absolute;left:-99999px;top:0;width:' + width + 'px;max-width:none!important;height:900px;border:0';
		f.src = url;
		document.body.appendChild( f );
		await new Promise( function ( r ) { f.onload = r; } );
		await f.contentWindow.document.fonts.ready;
		await new Promise( function ( r ) { setTimeout( r, 700 ); } );
		var w = f.contentWindow, d = f.contentDocument, out = {}, seen = {};
		d.body.querySelectorAll( '*' ).forEach( function ( el ) {
			var tag = el.tagName.toLowerCase();
			if ( /^(script|style|link|noscript|template|meta)$/.test( tag ) || el.closest( '#wpadminbar, svg :not(svg)' ) ) { return; }
			if ( el.closest( 'svg' ) && tag !== 'svg' ) { return; }
			var path = [], node = el, depth = 0;
			while ( node && node !== d.body && depth < 4 ) {
				path.unshift( node.tagName.toLowerCase() + ( normClass( node.className && node.className.baseVal !== undefined ? node.className.baseVal : node.className ) ? '.' + normClass( node.className && node.className.baseVal !== undefined ? node.className.baseVal : node.className ) : '' ) );
				node = node.parentElement; depth++;
			}
			var key = path.join( '>' );
			seen[ key ] = ( seen[ key ] || 0 ) + 1;
			key += '#' + seen[ key ];
			var cs = w.getComputedStyle( el );
			if ( cs.display === 'none' ) { out[ key ] = { hidden: true }; return; }
			var r = el.getBoundingClientRect();
			var rec = { box: [ r.left, r.top + w.scrollY, r.width, r.height ].map( Math.round ).join( ',' ) };
			PROPS.forEach( function ( p ) { rec[ p ] = normValue( cs.getPropertyValue( p ) ); } );
			out[ key ] = rec;
		} );
		f.remove();
		return out;
	}

	var differences = [], compared = 0;
	for ( var wi = 0; wi < WIDTHS.length; wi++ ) {
		for ( var pi = 0; pi < PAGES.length; pi++ ) {
			var page = PAGES[ pi ], width = WIDTHS[ wi ];
			var now = await snapshot( page, width );
			var old = await snapshot( page + ( page.indexOf( '?' ) === -1 ? '?' : '&' ) + 'imp_compare=old', width );
			Object.keys( old ).forEach( function ( key ) {
				if ( ! now[ key ] ) { differences.push( page + '@' + width + ' only in OLD: ' + key ); return; }
				compared++;
				var a = old[ key ], b = now[ key ];
				if ( a.hidden || b.hidden ) {
					if ( !! a.hidden !== !! b.hidden ) { differences.push( page + '@' + width + ' visibility ' + key + ': old ' + ( a.hidden ? 'hidden' : 'shown' ) + ', new ' + ( b.hidden ? 'hidden' : 'shown' ) ); }
					return;
				}
				Object.keys( a ).forEach( function ( prop ) {
					if ( a[ prop ] !== b[ prop ] ) { differences.push( page + '@' + width + ' ' + key + ' {' + prop + '}: ' + a[ prop ] + '  →  ' + b[ prop ] ); }
				} );
			} );
			Object.keys( now ).forEach( function ( key ) {
				if ( ! old[ key ] ) { differences.push( page + '@' + width + ' only in NEW: ' + key ); }
			} );
		}
	}
	return { pages: PAGES.length * WIDTHS.length, elementsCompared: compared, differences: differences };
};
