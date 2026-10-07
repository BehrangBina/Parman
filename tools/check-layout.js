/*
 * Layout regression check — paste into the browser console (or run via DevTools) on a page
 * of the local site. Returns { url, width, boxes } where every box is [left, top, width, height]
 * (page coordinates, rounded) or "hidden" / null (missing).
 *
 * Usage while refactoring:
 *   1. Before: run on each page/width, save the JSON (tools/layout-baseline.json).
 *   2. After each step: run again and diff — any moved element shows up immediately.
 *
 * Selectors use the current class prefix; PREFIX lets the same list run before/after a rename.
 */
window.impCheckLayout = function (prefix) {
	var p = prefix || 'imp-';
	var selectors = [
		// Layout chrome
		'.' + p + 'header', '.' + p + 'header__logo', '.' + p + 'header__burger', '.' + p + 'header__date',
		'.imp-d-header', '.imp-d-header__logo', '.imp-d-header__date', '.imp-d-nav--start', '.imp-d-nav--end',
		'.' + p + 'search', '.' + p + 'donate', '.' + p + 'back', '.' + p + 'footer', '.' + p + 'social', '.' + p + 'footer__top',
		// Home
		'.' + p + 'hero', '.' + p + 'hero__title', '.' + p + 'hero__buttons', '.' + p + 'docs', '.' + p + 'news__track',
		'.' + p + 'card', '.' + p + 'news__all', '.' + p + 'contact__card', '.' + p + 'contact__note',
		// Inner pages
		'.' + p + 'otitle', '.' + p + 'block', '.' + p + 'block--spaced', '.' + p + 'info', '.' + p + 'btn--gold',
		'.' + p + 'textlink', '.' + p + 'slist', '.' + p + 'scard', '.' + p + 'newslist', '.' + p + 'issues', '.' + p + 'issue',
		'.' + p + 'contact-page__social', '.' + p + 'contact-page__email', '.' + p + 'magazine__intro',
		'.' + p + 'doc__top', '.' + p + 'doc__stage', '.' + p + 'doc__download', '.' + p + 'doc__thumb', '.' + p + 'doc__read',
		'.fluentform', '.ff_submit_btn_wrapper'
	];
	var boxes = {};
	selectors.forEach(function (s) {
		var e = document.querySelector(s);
		if (!e) { boxes[s.replace(p, '*-')] = null; return; }
		if (getComputedStyle(e).display === 'none' || !e.getClientRects().length) { boxes[s.replace(p, '*-')] = 'hidden'; return; }
		var r = e.getBoundingClientRect();
		boxes[s.replace(p, '*-')] = [r.left, r.top + scrollY, r.width, r.height].map(Math.round);
	});
	return { url: decodeURI(location.pathname), width: innerWidth, boxes: boxes };
};
