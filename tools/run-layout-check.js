/*
 * Runs tools/check-layout.js on every page at phone and desktop width (inside off-screen
 * iframes, so one browser tab is enough) and compares with tools/layout-baseline.json.
 *
 * Both files must be reachable from the site (copied into wp-content/uploads by the refactor
 * workflow), then in the browser console of any local page:
 *     await impRunLayoutCheck('imp-')        // returns { ok, diffs }
 *     await impRunLayoutCheck('imp-', true)  // returns fresh measurements (new baseline)
 *
 * TOLERANCE allows sub-pixel/font rounding; anything larger is reported as a diff.
 */
window.impRunLayoutCheck = async function (prefix, capture) {
	var TOLERANCE = 1;
	var PAGES = ['/', '/donate/', '/bcd31-contact-us/', '/category/statements/', '/news/', '/نشریه-ایرانگرا/', '/partys-motto/', '/membership-form/', '/about-us/', '/affidavit/'];
	var WIDTHS = [393, 1440];
	var code = await (await fetch('/wp-content/uploads/check-layout.js?' + Date.now())).text();

	async function measure(path, width) {
		var frame = document.createElement('iframe');
		frame.style.cssText = 'position:absolute;left:-99999px;top:0;width:' + width + 'px;max-width:none!important;height:900px;border:0';
		frame.src = path;
		document.body.appendChild(frame);
		await new Promise(function (resolve) { frame.onload = resolve; });
		await frame.contentWindow.document.fonts.ready;
		await new Promise(function (resolve) { setTimeout(resolve, 400); });
		frame.contentWindow.eval(code);
		var result = frame.contentWindow.impCheckLayout(prefix);
		frame.remove();
		var boxes = {};
		Object.keys(result.boxes).forEach(function (k) {
			var v = result.boxes[k];
			if (v !== null && v !== 'hidden') boxes[k] = v;
		});
		return { url: result.url, width: result.width, boxes: boxes };
	}

	var runs = [];
	for (var w = 0; w < WIDTHS.length; w++) {
		for (var p = 0; p < PAGES.length; p++) runs.push(await measure(PAGES[p], WIDTHS[w]));
	}
	if (capture) return runs;

	var baseline = await (await fetch('/wp-content/uploads/layout-baseline.json?' + Date.now())).json();
	var diffs = [];
	baseline.forEach(function (base) {
		var now = runs.find(function (r) { return r.url === base.url && r.width === base.width; });
		if (!now) { diffs.push(base.url + '@' + base.width + ': page not measured'); return; }
		Object.keys(base.boxes).forEach(function (sel) {
			var a = base.boxes[sel], b = now.boxes[sel];
			if (!b) { diffs.push(base.url + '@' + base.width + ' ' + sel + ': missing'); return; }
			if (a.some(function (v, i) { return Math.abs(v - b[i]) > TOLERANCE; })) {
				diffs.push(base.url + '@' + base.width + ' ' + sel + ': ' + JSON.stringify(a) + ' → ' + JSON.stringify(b));
			}
		});
		Object.keys(now.boxes).forEach(function (sel) {
			if (!base.boxes[sel]) diffs.push(base.url + '@' + base.width + ' ' + sel + ': new element');
		});
	});
	return { ok: diffs.length === 0, diffs: diffs };
};
