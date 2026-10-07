/*
 * Behaviour smoke test — complements the layout check by exercising the interactive parts
 * (menus, search, dropdowns, dialog, reader, carousel) in off-screen iframes.
 * In the browser console of any local page:   await impRunSmokeCheck()   → { ok, results }
 */
window.impRunSmokeCheck = async function () {
	async function open(path, width) {
		var frame = document.createElement('iframe');
		frame.style.cssText = 'position:absolute;left:-99999px;top:0;width:' + width + 'px;max-width:none!important;height:900px;border:0';
		frame.src = path;
		document.body.appendChild(frame);
		await new Promise(function (resolve) { frame.onload = resolve; });
		await new Promise(function (resolve) { setTimeout(resolve, 400); });
		return frame;
	}
	var wait = function (ms) { return new Promise(function (r) { setTimeout(r, ms); }); };
	var results = {};
	var check = function (name, pass) { results[name] = pass ? 'ok' : 'FAIL'; };

	// Mobile: burger menu, submenu toggle, close; search open/close.
	var f = await open('/', 393), d = f.contentDocument;
	d.querySelector('.imp-header__burger').click(); await wait(450);
	var menu = d.getElementById('imp-menu');
	check('mobile menu opens', !menu.hidden && menu.classList.contains('is-open'));
	var sub = d.querySelector('.imp-sub-toggle');
	if (sub) { sub.click(); check('mobile submenu toggles', sub.getAttribute('aria-expanded') === 'true'); }
	d.querySelector('.imp-menu__close').click(); await wait(450);
	check('mobile menu closes', menu.hidden);
	var search = d.querySelector('.imp-search');
	search.querySelector('.imp-search__toggle').click(); await wait(300);
	check('search opens', search.dataset.state === 'open');
	search.querySelector('.imp-search__toggle').click(); await wait(300);
	check('search closes', search.dataset.state === 'closed');
	var track = d.querySelector('.imp-news__track');
	check('news carousel present', !!track && track.children.length > 0);
	f.remove();

	// Desktop: dropdown opens on click and closes on outside click.
	f = await open('/', 1440); d = f.contentDocument;
	var toggle = d.querySelector('.imp-d-nav__toggle');
	toggle.click();
	var item = toggle.closest('.imp-d-nav__item');
	check('desktop dropdown opens', item.classList.contains('is-open'));
	d.body.click();
	check('desktop dropdown closes', !item.classList.contains('is-open'));
	f.remove();

	// Donate page: fee dialog.
	f = await open('/donate/', 393); d = f.contentDocument;
	d.querySelector('[data-imp-dialog]').click(); await wait(200);
	var dialog = d.getElementById('imp-fee-dialog');
	check('fee dialog opens', dialog && dialog.open);
	if (dialog) { dialog.querySelector('[data-imp-dialog-close]').click(); check('fee dialog closes', !dialog.open); }
	f.remove();

	// Document reader: cover → pages → next.
	f = await open('/partys-motto/', 393); d = f.contentDocument;
	var read = d.querySelector('[data-imp-reader-open]');
	if (read) {
		read.click(); await wait(200);
		d.querySelector('.imp-reader__start').click(); await wait(400);
		var count = d.querySelector('[data-imp-reader-count]');
		var first = count.textContent;
		d.querySelector('.imp-reader__arrow--next').click(); await wait(100);
		check('reader opens and turns pages', first !== count.textContent && count.textContent.indexOf('۲') !== -1);
	} else {
		check('reader opens and turns pages', false);
	}
	f.remove();

	var ok = Object.keys(results).every(function (k) { return results[k] === 'ok'; });
	return { ok: ok, results: results };
};
