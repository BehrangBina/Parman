# Figma → WordPress screen map

Where each screen lives: the page template in `theme/imp/templates/pages/`, its data in `theme/imp/src/controllers/`, its styles in `theme/imp/assets/css/pages/`. See `docs/ARCHITECTURE.md`.

Figma: `IMP-Website-2026`, page **Mobile-HiFi-Farsi** (node `697:927`). Frames are 393px wide and include a 48px iOS status bar, which is dropped in CSS.

| Figma frame | Node | WordPress target | Status |
|---|---|---|---|
| Home | 697:928 | Front page → `templates/pages/home.php` | ✅ done |
| Burger Menu | 697:2702 | `templates/layout/header.php` (menu: Appearance → Menus, "Mobile menu (IMP)") | ✅ done |
| NavBar | 881:13007 | `templates/layout/header.php` | ✅ done |
| Style guide (Colors/Effects) | 1035:4601 | `css/tokens.css` | ✅ done |
| About (long) | 966:3542 | page `about-us` | todo |
| Overlay-About | 975:4405 | about-us intro | todo |
| Maram-01/02, Read-Maramname, Overlay-Maramname | 805:7201, 813:7420, 805:6354, 697:1066 | page `partys-motto` → `templates/pages/document.php` | ✅ done — needs the real text pasted into the page |
| Asas-01/02, Read-Asas, Overlay-Asasname | 813:9167, 813:9243, 813:9134, 697:1553 | page `party-constitution` (same template) | ✅ done — text extracted from the PDF by `tools/pdf-to-reader.py` → `docs/content/asasname.html` (paste into the page's Code editor on live) |
| Sogand01, Read-Sogand, Overlay-Sogandname | 813:8901, 813:8862, 813:8316 | page `affidavit` (same template) | ✅ done (live text) |
| Dabir (secretaries) | 871:12520 | secretaries page | todo |
| Sokhangoo (spokespersons) | 865:12156 | spokespersons page | todo |
| BonyanGozaran-Overlay | 1006:1920 | founders section | todo |
| Akhbaar (news list) | 697:2647 | page `news` → `templates/pages/news.php` (category `news-party`) | ✅ done (from screenshot) |
| Full Post, -02, -03 | 697:2681, 1023:2445, 1023:2602 | single post | todo |
| Bayanie (list) | 1009:3003 | category archive `statements` → `templates/pages/statements.php` | ✅ done (from screenshot) |
| Bayanie dated statements (single) | 1011:3758, 1014:4757, 1014:4874, 1014:4958 | single post in `statements` | todo |
| Hamyari (donate) | 697:2603, 1023:2782 | page `donate` → `templates/pages/donate.php` | ✅ done (fee popup uses page `membership-fee`; design 966:3055 not yet read) |
| Form-Hamvandi | 740:2198 | live Fluent form **#3** restyled (live field names kept), local copy built by `tools/build-membership-form.php`; styles in `assets/css/components/fluent-forms.css`, extras in `src/integrations/class-fluent-forms.php` | ✅ done (+ ID upload kept, Pro-only) |
| Overlay-MembershipFee | 966:3055 | popup on donate + membership pages; text = page `membership-fee` | ✅ styled — needs the real text |
| Contact | 820:11317 | page `bcd31-contact-us` → `templates/pages/contact.php` | ✅ done (built from screenshot; Figma MCP limit) |
| Nashriye (magazine) | 820:9504 | page `نشریه-ایرانگرا` → `templates/pages/magazine.php`; issues = admin "نشریه ایرانگرا" (post type `imp_issue`) | ✅ done (from screenshot) |
| Prince | 790:5679 | TBD — ask designer | todo |

Components: Search-Bar 526:1324, News-Home-Slide 1023:2421, Arrows-Slider 1023:2422, Card 715:4482, FIND-US 780:4882, Language/Dropdown 746:2755 (phase 2).

## Desktop (≥ 1024px) — Figma page **Desktop-HiFi-Farsi** (node `1074:5061`, 1440px frames)

Mobile layout below 1024px; every stylesheet in `assets/css/` holds a component's mobile rules followed by its `@media (min-width: 1024px)` rules.
Menus: "Desktop menu (IMP)" and "Mobile menu (IMP)" locations (both fall back to Neve's `primary`).
References saved in `docs/figma/` (overview, Home, Components with hover/dropdown states).

| Figma frame | Node | Status |
|---|---|---|
| Header + dropdowns + search + footer + Donate (Components 1181:6348) | 1171:5178 | ✅ done |
| Home | 1112:5062 | ✅ done |
| About (people sections) | 1153:292 | todo (+ admin "people" section, + mobile About) |
| Hamyari / Overlay-MembershipFee | 1181:5330 / 1181:6254 | todo |
| Hamvandi (form) | 1181:5631 | todo |
| Maramname / Asasname / Sogandname + *-Content | 1181:6630, 1181:10104, 1215:994, 1181:9241, 1181:10634, 1215:1524 | todo |
| Nashriye | 1215:4401 | todo |
| News | 1215:5585 | todo |
| Full-News (single post, also mobile) | 1215:6609 | todo |
| Bayanie | 1215:6745 | todo |
| Contact | 1225:9182 | todo |
| Generic page style (pages without a design) | — | todo |
