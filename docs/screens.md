# Figma → WordPress screen map

Figma: `IMP-Website-2026`, page **Mobile-HiFi-Farsi** (node `697:927`). Frames are 393px wide and include a 48px iOS status bar, which is dropped in CSS.

| Figma frame | Node | WordPress target | Status |
|---|---|---|---|
| Home | 697:928 | Front page → `inc/home.php` | ✅ done |
| Burger Menu | 697:2702 | `inc/header.php` overlay (Appearance → Menus, "primary") | ✅ done |
| NavBar | 881:13007 | `inc/header.php` | ✅ done |
| Style guide (Colors/Effects) | 1035:4601 | `css/tokens.css` | ✅ done |
| About (long) | 966:3542 | page `about-us` | todo |
| Overlay-About | 975:4405 | about-us intro | todo |
| Maram-01/02, Read-Maramname, Overlay-Maramname | 805:7201, 813:7420, 805:6354, 697:1066 | page `partys-motto` → `inc/documents.php` | ✅ done — needs the real text pasted into the page |
| Asas-01/02, Read-Asas, Overlay-Asasname | 813:9167, 813:9243, 813:9134, 697:1553 | page `party-constitution` (same template) | ✅ template done — needs text (until then بخوانید opens the PDF) |
| Sogand01, Read-Sogand, Overlay-Sogandname | 813:8901, 813:8862, 813:8316 | page `affidavit` (same template) | ✅ done (live text) |
| Dabir (secretaries) | 871:12520 | secretaries page | todo |
| Sokhangoo (spokespersons) | 865:12156 | spokespersons page | todo |
| BonyanGozaran-Overlay | 1006:1920 | founders section | todo |
| Akhbaar (news list) | 697:2647 | page `news` → `inc/news.php` (category `news-party`) | ✅ done (from screenshot) |
| Full Post, -02, -03 | 697:2681, 1023:2445, 1023:2602 | single post | todo |
| Bayanie (list) | 1009:3003 | category archive `statements` → `inc/statements.php` | ✅ done (from screenshot) |
| Bayanie dated statements (single) | 1011:3758, 1014:4757, 1014:4874, 1014:4958 | single post in `statements` | todo |
| Hamyari (donate) | 697:2603, 1023:2782 | page `donate` → `inc/donate.php` | ✅ done (fee popup uses page `membership-fee`; design 966:3055 not yet read) |
| Form-Hamvandi | 740:2198 | NEW Fluent form built by `tools/build-membership-form.php`; import file `docs/membership-form.fluentform.json`; styles in `css/mobile.css`, extras in `inc/forms.php` | ✅ done (+ ID upload kept, Pro-only) |
| Overlay-MembershipFee | 966:3055 | popup on donate + membership pages; text = page `membership-fee` | ✅ styled — needs the real text |
| Contact | 820:11317 | page `bcd31-contact-us` → `imp_m_render_contact` in `inc/contact.php` | ✅ done (built from screenshot; Figma MCP limit) |
| Nashriye (magazine) | 820:9504 | page `نشریه-ایرانگرا` → `inc/magazine.php`; issues = admin "نشریه ایرانگرا" (post type `imp_issue`) | ✅ done (from screenshot) |
| Prince | 790:5679 | TBD — ask designer | todo |

Components: Search-Bar 526:1324, News-Home-Slide 1023:2421, Arrows-Slider 1023:2422, Card 715:4482, FIND-US 780:4882, Language/Dropdown 746:2755 (phase 2).
