# Deployment plan — IMP theme to iranianmonarchy.info

Status: **plan only, nothing applied to production yet** (agreed 2026-10-09).
Production today: WordPress 7.1.3 on IONOS, plain Neve 4.2.3, membership form = Fluent Forms #3.

## 1. Code → GitHub (source of truth)

- Repository: `BehrangBina/Parman`. Work happens on `feature/desktop`.
- When ready: merge into **`master`** by pull request. `master` = what runs on the live site.
- Tag every release (`v1.0.0`, `v1.0.1`, …) so it is always clear which version is live and the
  previous one can be restored.

## 2. GitHub → website (how the theme reaches IONOS)

| Option | How | When |
|---|---|---|
| **A. Manual zip** (recommended first) | Build `imp-vX.Y.Z.zip` from the tag (`git archive` of `theme/imp`); an admin uploads it in Appearance → Themes → Upload | First release; no server access needed |
| **B. Automatic** (later) | GitHub Action uploads `theme/imp` over SFTP to IONOS when a release is tagged; credentials stored as GitHub secrets | When releases become frequent |

Only `theme/imp` is deployed. `tools/`, `docs/`, Docker files and `.env` never go to the server.

## 3. Rehearsal on staging

- Use IONOS staging if the plan includes it; otherwise clone the site with UpdraftPlus to a
  password-protected, no-index subdomain (e.g. `staging.iranianmonarchy.info`).
- Run the complete release (section 4) there first, including a test submission, emails and digest.

## 4. Release day on production (≈1 hour, quiet time)

1. **Backup** — full UpdraftPlus backup (database + files) and an export of form #3
   (Fluent Forms → Tools → Export) as a safety copy.
2. **Upload** the `imp` theme without activating; check it with Appearance → Customize → Live Preview.
3. **Menus & content** — assign "Desktop menu (IMP)" and "Mobile menu (IMP)"; fill the
   "PDF file" box (`_imp_pdf`) on the document pages and magazine issues.
4. **Activate** the theme.
5. **Form #3** — apply the Figma changes by hand in the Fluent editor (checklist to be written):
   reorder/relabel to Figma, remove `file-upload`, `input_radio_2`, `description`,
   `description_2`, `description_3`, `net_promoter_score`; add `whatsapp_consent`.
   **Do not import** a form: an import creates a new ID and separates it from the existing entries,
   emails and digest. Field names must stay as they are (see `config/site.php` → `membership`).
6. **Digest** — deactivate Code Snippet #6 ("IMP - Weekly Membership Digest"). The theme's digest
   (Tools → IMP membership digest) turns itself on only when the snippet is off — never both.
7. **Cron** — add a real server cron at IONOS calling `wp-cron.php` every 5–15 min, and
   `define( 'DISABLE_WP_CRON', true );` in `wp-config.php`. Check the digest page shows a future
   "next check" date and Site Health shows no failed scheduled events.
8. **Purge caches** — Super Page Cache / Cloudflare, WP-Optimize, Optimole.
9. **Smoke test** — pages on phone and desktop, GTranslate, one test submission (office email +
   welcome email arrive), then delete the test entry.

## 5. Rollback

- Theme: reactivate Neve (about a minute; content and entries are not affected).
- Digest: reactivate snippet #6.
- Form: restore #3's fields from the export taken in step 1.
- Everything: UpdraftPlus restore.

## 6. Later releases

- **WhatsApp** — Meta Business account + approved templates (`imp_new_member`, `imp_welcome`), then
  `IMP_WHATSAPP_TOKEN`, `IMP_WHATSAPP_PHONE_ID`, `IMP_WHATSAPP_OFFICE` in the server's
  `wp-config.php` (never in git). Until then the module stays in test mode.
- **Housekeeping** — plugin/Neve updates, remove duplicate plugins (two statistics, two snippet
  plugins, Tumblr Importer, Use Any Font), remove the IONOS CVE mu-plugin after updating WordPress,
  personal admin accounts instead of one shared admin, decide on forms #4/#5/#6.
- **Automatic deploys** (option B).

## Open questions

- Does the IONOS plan include staging?
- Who has IONOS hosting access (SFTP/SSH, cron settings)?
- Manual zip releases first, or automatic deploys from the start?
