/**
 * Front-end entry point. Each module looks for its own elements and does nothing when they
 * are not on the page. Modules are ES modules registered in IMP\Core\Assets (WordPress prints
 * the import map, with a version per file for cache busting) — no build step.
 */

import { init as mobileMenu } from '@imp/mobile-menu';
import { init as search } from '@imp/search';
import { init as desktopNav } from '@imp/desktop-nav';
import { init as backLink } from '@imp/back-link';
import { init as backToTop } from '@imp/back-to-top';
import { init as dialog } from '@imp/dialog';
import { init as reader } from '@imp/reader';
import { init as newsCarousel } from '@imp/news-carousel';

const features = { mobileMenu, search, desktopNav, backLink, backToTop, dialog, reader, newsCarousel };

Object.entries( features ).forEach( ( [ name, init ] ) => {
	// One failing feature must not stop the others.
	try {
		init();
	} catch ( error ) {
		// eslint-disable-next-line no-console
		console.error( `[IMP] ${ name } failed to start`, error );
	}
} );
