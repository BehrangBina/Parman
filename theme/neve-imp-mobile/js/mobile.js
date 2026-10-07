/* IMP mobile: menu overlay, submenu toggles, search, news carousel. No dependencies. */
(function () {
	'use strict';

	var burger = document.querySelector('.imp-m-header__burger');
	var menu = document.getElementById('imp-m-menu');

	var hideTimer;

	function openMenu() {
		clearTimeout(hideTimer);
		menu.hidden = false;
		void menu.offsetWidth; // apply the off-screen position first so the slide animates
		menu.classList.add('is-open');
		document.body.classList.add('imp-m-menu-open');
		burger.setAttribute('aria-expanded', 'true');
		var close = menu.querySelector('.imp-m-menu__close');
		if (close) close.focus({ preventScroll: true });
	}

	function closeMenu(restoreFocus) {
		menu.classList.remove('is-open');
		document.body.classList.remove('imp-m-menu-open');
		burger.setAttribute('aria-expanded', 'false');
		hideTimer = setTimeout(function () { menu.hidden = true; }, 400); // after the slide-out
		if (restoreFocus !== false) burger.focus({ preventScroll: true });
	}

	if (burger && menu) {
		burger.addEventListener('click', openMenu);
		menu.querySelector('.imp-m-menu__close').addEventListener('click', closeMenu);
		// Tapping a menu link closes the panel. A parent whose link is just "#"
		// (e.g. مدیا) only opens/closes its submenu instead.
		menu.addEventListener('click', function (e) {
			var link = e.target.closest('a[href]');
			if (!link) return;
			var toggle = link.getAttribute('href') === '#' && link.parentElement.querySelector(':scope > .imp-m-sub-toggle');
			if (toggle) {
				e.preventDefault();
				toggle.click();
				return;
			}
			closeMenu(false);
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !menu.hidden) closeMenu();
		});

		// Add a chevron toggle to every item with a submenu.
		menu.querySelectorAll('.menu-item-has-children, .page_item_has_children').forEach(function (item, i) {
			var sub = item.querySelector(':scope > ul');
			if (!sub) return;
			sub.id = sub.id || 'imp-m-sub-' + i;
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'imp-m-sub-toggle';
			btn.setAttribute('aria-expanded', 'false');
			btn.setAttribute('aria-controls', sub.id);
			btn.innerHTML = '<svg aria-hidden="true" viewBox="0 0 30 30" width="30" height="30" fill="none"><path d="M10 12L15 17L20 12" stroke="#243F88" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
				'<span class="screen-reader-text">' + (item.querySelector('a') || {}).textContent + '</span>';
			item.insertBefore(btn, sub);
			btn.addEventListener('click', function () {
				var open = btn.getAttribute('aria-expanded') !== 'true';
				btn.setAttribute('aria-expanded', String(open));
				sub.classList.toggle('is-open', open);
			});
		});
	}

	// Search: the round button opens the bar and turns into ✕ (closes it);
	// the magnifier at the far end (or Enter) searches.
	var search = document.querySelector('.imp-m-search');
	if (search) {
		var toggle = search.querySelector('.imp-m-search__toggle');
		var input = search.querySelector('.imp-m-search__input');
		var submit = search.querySelector('.imp-m-search__submit');
		var setSearch = function (open) {
			search.dataset.state = open ? 'open' : 'closed';
			toggle.setAttribute('aria-expanded', String(open));
			input.tabIndex = submit.tabIndex = open ? 0 : -1;
			if (open) input.focus(); else input.blur();
		};
		toggle.addEventListener('click', function () { setSearch(search.dataset.state !== 'open'); });
		search.querySelector('form').addEventListener('submit', function (e) {
			if (!input.value.trim()) { e.preventDefault(); input.focus(); }
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && search.dataset.state === 'open') setSearch(false);
		});
	}

	// Desktop dropdowns: open on hover (CSS) and on click/keyboard via the chevron or a "#" label.
	var navToggles = document.querySelectorAll('.imp-d-nav__toggle');
	var closeAllNav = function (except) {
		navToggles.forEach(function (t) {
			if (t === except) return;
			t.setAttribute('aria-expanded', 'false');
			t.closest('.imp-d-nav__item').classList.remove('is-open');
		});
	};
	navToggles.forEach(function (t) {
		t.addEventListener('click', function (e) {
			e.stopPropagation();
			var item = t.closest('.imp-d-nav__item');
			var open = !item.classList.contains('is-open');
			closeAllNav(t);
			item.classList.toggle('is-open', open);
			item.querySelectorAll('.imp-d-nav__toggle').forEach(function (x) { x.setAttribute('aria-expanded', String(open)); });
		});
	});
	document.addEventListener('click', function (e) { if (!e.target.closest('.imp-d-nav__item')) closeAllNav(); });
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeAllNav(); });

	// Footer "back to top" (desktop design).
	document.querySelectorAll('[data-imp-top]').forEach(function (b) {
		b.addEventListener('click', function () {
			var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
		});
	});

	// Back: go to the previous page in history. If the browser can't go back (new tab,
	// first entry, history.back() is a no-op), fall back to the link's href
	// (the server-side referrer, or the home page).
	document.querySelectorAll('[data-imp-back]').forEach(function (back) {
		back.addEventListener('click', function (e) {
			if (history.length <= 1) return; // nothing to go back to: follow href
			e.preventDefault();
			var left = false;
			window.addEventListener('pagehide', function () { left = true; }, { once: true });
			history.back();
			setTimeout(function () {
				if (!left) location.href = back.href;
			}, 500);
		});
	});

	// Document reader (inc/documents.php): cover, then the page text split into screen-sized
	// pages with CSS columns. RTL: next page lies to the left, swipe right to advance.
	document.querySelectorAll('[data-imp-reader]').forEach(function (reader) {
		var cover = reader.querySelector('[data-imp-reader-cover]');
		var pagesEl = reader.querySelector('[data-imp-reader-pages]');
		var viewport = reader.querySelector('[data-imp-reader-viewport]');
		var flow = reader.querySelector('[data-imp-reader-flow]');
		var count = reader.querySelector('[data-imp-reader-count]');
		var rtl = getComputedStyle(reader).direction === 'rtl';
		var page = 0, total = 1, step = 0;
		var fa = function (n) { return String(n).replace(/\d/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[d]; }); };

		function layout() {
			var width = viewport.clientWidth;
			var gap = parseFloat(getComputedStyle(flow).columnGap) || 0;
			flow.style.columnWidth = width + 'px';
			step = width + gap;
			total = Math.max(1, Math.round((flow.scrollWidth + gap) / step));
			page = Math.min(page, total - 1);
			render();
		}
		function render() {
			flow.style.transform = 'translateX(' + (rtl ? 1 : -1) * page * step + 'px)';
			// Figma Maram-01: "ورق بزن" (turn the page) on the first page, then the page count.
			count.textContent = total < 2 ? '' : page === 0 ? 'ورق بزن' : 'صفحه ' + fa(page + 1) + ' از ' + fa(total);
			reader.querySelector('[data-imp-reader-pages] [data-imp-reader-next]').disabled = page >= total - 1;
		}
		function showCover() { pagesEl.hidden = true; cover.hidden = false; }
		function showPages() { cover.hidden = true; pagesEl.hidden = false; layout(); }
		function go(delta) {
			if (pagesEl.hidden) { if (delta > 0) showPages(); return; }
			if (page + delta < 0) { showCover(); return; }
			page = Math.max(0, Math.min(total - 1, page + delta));
			render();
		}
		function open(e) {
			if (e) e.preventDefault();
			reader.hidden = false;
			document.body.classList.add('imp-m-menu-open'); // reuse the scroll lock
			page = 0; showCover();
			var close = cover.querySelector('[data-imp-reader-close]');
			if (close) close.focus({ preventScroll: true });
		}
		function close() {
			reader.hidden = true;
			document.body.classList.remove('imp-m-menu-open');
		}

		document.querySelectorAll('[data-imp-reader-open]').forEach(function (b) { b.addEventListener('click', open); });
		reader.querySelectorAll('[data-imp-reader-close]').forEach(function (b) { b.addEventListener('click', close); });
		reader.querySelectorAll('[data-imp-reader-next]').forEach(function (b) { b.addEventListener('click', function () { go(1); }); });
		reader.querySelectorAll('[data-imp-reader-prev]').forEach(function (b) { b.addEventListener('click', function () { go(-1); }); });

		document.addEventListener('keydown', function (e) {
			if (reader.hidden) return;
			if (e.key === 'Escape') close();
			if (e.key === 'ArrowLeft') go(rtl ? 1 : -1);
			if (e.key === 'ArrowRight') go(rtl ? -1 : 1);
		});

		var x0 = null;
		reader.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
		reader.addEventListener('touchend', function (e) {
			if (x0 === null) return;
			var dx = e.changedTouches[0].clientX - x0;
			x0 = null;
			if (Math.abs(dx) < 40) return;
			go((dx > 0) === rtl ? 1 : -1);
		});
		window.addEventListener('resize', function () { if (!pagesEl.hidden) layout(); });
		if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { if (!pagesEl.hidden) layout(); });
	});

	// Dialogs: [data-imp-dialog="id"] opens, [data-imp-dialog-close] or a backdrop tap closes.
	// Links fall back to their href when the dialog isn't shown (e.g. desktop, where .imp-m is hidden).
	document.querySelectorAll('[data-imp-dialog]').forEach(function (btn) {
		var dialog = document.getElementById(btn.dataset.impDialog);
		if (!dialog || typeof dialog.showModal !== 'function') return;
		btn.addEventListener('click', function (e) {
			if (!dialog.parentElement.getClientRects().length) return;
			e.preventDefault();
			dialog.showModal();
		});
		dialog.addEventListener('click', function (e) {
			if (e.target === dialog || e.target.closest('[data-imp-dialog-close]')) dialog.close();
		});
	});

	// News carousel arrows scroll one card in the physical direction.
	document.querySelectorAll('.imp-m-news').forEach(function (news) {
		var track = news.querySelector('.imp-m-news__track');
		news.querySelectorAll('.imp-m-news__arrow').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var card = track.querySelector('.imp-m-card');
				var step = card ? card.getBoundingClientRect().width + 16 : track.clientWidth;
				track.scrollBy({ left: step * Number(btn.dataset.dir), behavior: 'smooth' });
			});
		});
	});
})();
