# IMP theme — architecture & developer guide

WordPress child theme of **Neve** for iranianmonarchy.info, built from the Figma file
*IMP Website 2026* (pages **Mobile-HiFi-Farsi** and **Desktop-HiFi-Farsi**).
Below **1024px** the mobile design is shown, from 1024px the desktop design.

## Layout of `theme/imp/`

| Folder | What lives there | Rule |
|---|---|---|
| `functions.php` | Bootstrap only (constants, autoloader, `IMP\Core\Theme::boot()`) | no logic |
| `config/site.php` | All URLs, page slugs, social links, categories, menus, **page routes** | change settings here, not in code |
| `src/` | PHP logic, namespace `IMP`, one class per file (`class-*.php`, WordPress standards) | no HTML |
| `src/core/` | `Theme` (module list), `Assets`, `Layout` (header/footer), `Config`, `Logger` | |
| `src/routing/` | `Page_Router` — which URL gets which designed page | |
| `src/controllers/` | One per designed page: gathers the page's data (`data()`), returns plain arrays | no HTML, no `echo` |
| `src/data/` | Queries: news, issues, document text, menu tree, fee text | |
| `src/services/` | Jalali dates, PDF locator + download endpoint, contact mailer, statement titles | |
| `src/admin/` | Issue post type, the reusable **PDF file** box (issues + document pages) | |
| `src/integrations/` | Fluent Forms extras | |
| `templates/` | **HTML only**, everything escaped, data comes in `$args` | no queries |
| `templates/layout/` | header (mobile + desktop), footer | |
| `templates/components/` | reusable parts: ornament title, cards, search, close button, contact form, dialog… | |
| `templates/pages/` | one per designed page (`home`, `donate`, `contact`, `statements`, `news`, `magazine`, `document`) | |
| `assets/css/` | `tokens.css` (design values), `base.css`, `components/*.css`, `pages/*.css` | each file = mobile rules, then `@media (min-width: 1024px)` |
| `assets/js/` | `main.js` + `modules/*.js` (ES modules, no build step); `admin/` | one feature per module |
| `assets/img`, `assets/icons` | images and SVG icons (`imp_icon( 'name' )`) | `pattern-blue.png` is reserved for the desktop document screens |

Every template and stylesheet starts with a **`Figma: <frame> (<node id>)`** reference, so you can
jump from code to design (`https://www.figma.com/design/2b1gD9YEpqv5EYpirFNrhu/?node-id=<id>`).

## How a designed page is rendered

```
request → Page_Router (src/routing) ── route in config/site.php? ──no──▶ normal Neve page
                                         │yes
                                         ▼
                     <Name>_Controller::data()   (src/controllers, uses src/data + src/services)
                                         │ array
                                         ▼
                     templates/pages/<name>.php  (+ templates/components/*)
```

If a controller throws, the router **logs it and falls back** to the normal page instead of
showing a broken layout. Neve's own `<main>` is hidden on designed pages (`imp-custom` body class).

### Add a new designed page

1. Add the route in `config/site.php` → `'routes' => array( 'my-slug' => 'my-page' )`.
2. Create `src/controllers/class-my-page-controller.php` with `public static function data()`.
3. Create `templates/pages/my-page.php` (start with the Figma reference comment).
4. Optional styles: `assets/css/pages/my-page.css` (loaded only on that page).

### Add a component

`templates/components/my-thing.php`, render with `imp_component( 'my-thing', array( … ) )`;
styles in `assets/css/components/my-thing.css` and add `'my-thing'` to `Assets::COMPONENTS`.

## Coding standards

* **WordPress Coding Standards** (PHP: `snake_case`, `Class_Name`, tabs; JS: WordPress style).
  `.editorconfig` sets tabs/LF; `.gitattributes` keeps LF in git.
* Escape at output with the WordPress functions: `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`.
  Each remaining `phpcs:ignore` says why (core output, theme SVG files, the logger).
* No inline styles or scripts — CSS/JS files only.
* Comments explain **why**, not what.

## Debugging

| Tool | Use |
|---|---|
| `IMP\Core\Logger::error/warning/debug( 'Class::method', 'message', array( … ) )` | Writes `[IMP] <time> LEVEL Class::method - message {context}` via `error_log()` |
| `wp-content/debug.log` | Locally all PHP errors and theme logs go here (not into the page) |
| `imp_dump( $value, 'label' )` | Prints a readable block — only with `WP_DEBUG` and only for administrators |
| Query Monitor plugin (recommended, free) | Queries, hooks, template parts and PHP errors per page |

Levels: **error** always logged; **warning** with `WP_DEBUG`; **debug** only in a non-production
environment. **Never log personal data** (names, emails, form answers) — GDPR.

The local Docker site sets `WP_ENVIRONMENT_TYPE=local`, `WP_DEBUG_LOG` on, `WP_DEBUG_DISPLAY` off
(`docker-compose.wordpress.yml`). The live site keeps WordPress defaults.

## Local environment

```bash
cp .env.example .env            # local DB credentials (git-ignored)
docker compose -f docker-compose.wordpress.yml up -d
```

Site: http://localhost:8080 — WordPress core lives in a Docker volume (fast); only `theme/imp`
is mounted from the repo.

## Regression checks (run before every commit that touches markup, CSS or JS)

Copy the tools into the local site once (and after editing them):

```bash
for f in check-layout.js run-layout-check.js layout-baseline.json run-smoke-check.js; do
  docker cp tools/$f local-wordpress-703-wordpress-1:/var/www/html/wp-content/uploads/$f
done
```

Then in the browser console on any local page:

```js
eval( await ( await fetch( '/wp-content/uploads/run-layout-check.js' ) ).text() );
eval( await ( await fetch( '/wp-content/uploads/run-smoke-check.js' ) ).text() );
await impRunLayoutCheck( 'imp-' );   // positions of ~50 elements, 10 pages × 393/1440px vs baseline
await impRunSmokeCheck();            // menu, search, dropdowns, dialog, reader, carousel
```

When a change is **intended** to move things, capture a new baseline with
`await impRunLayoutCheck( 'imp-', true )` and save it to `tools/layout-baseline.json`.

## Other tools

* `tools/build-membership-form.php` — builds the Fluent Forms membership form and writes
  `docs/membership-form.fluentform.json` for import on the live site.
* `tools/pdf-to-reader.py` — turns a Persian Google-Docs PDF into reader text
  (`docs/content/*.html`), fixing direction marks, half-spaces and line breaks. Proofread the result.
