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
		// Tapping any menu link closes the panel (also covers same-page #anchors).
		menu.addEventListener('click', function (e) {
			if (e.target.closest('a[href]')) closeMenu(false);
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

	// Search: first tap opens the bar, second tap (with text) submits.
	var search = document.querySelector('.imp-m-search');
	if (search) {
		var toggle = search.querySelector('.imp-m-search__toggle');
		var input = search.querySelector('.imp-m-search__input');
		toggle.addEventListener('click', function () {
			if (search.dataset.state === 'open' && input.value.trim()) {
				search.querySelector('form').submit();
				return;
			}
			var open = search.dataset.state !== 'open';
			search.dataset.state = open ? 'open' : 'closed';
			toggle.setAttribute('aria-expanded', String(open));
			input.tabIndex = open ? 0 : -1;
			if (open) input.focus();
		});
	}

	// Back: go to the previous page in history. If the browser can't go back (new tab,
	// first entry, history.back() is a no-op), fall back to the link's href
	// (the server-side referrer, or the home page).
	var back = document.querySelector('[data-imp-back]');
	if (back) {
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
	}

	// Dialogs: [data-imp-dialog="id"] opens, [data-imp-dialog-close] or a backdrop tap closes.
	document.querySelectorAll('[data-imp-dialog]').forEach(function (btn) {
		var dialog = document.getElementById(btn.dataset.impDialog);
		if (!dialog || typeof dialog.showModal !== 'function') return;
		btn.addEventListener('click', function () { dialog.showModal(); });
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
