# Guozhan Piwigo Test Deploy Notes

Updated: 2026-07-13

This is a complete Piwigo 16.4.0 test project with:

- Static customer frontend: `index.html`, `screens/` and `assets/`
- Frontend theme: `themes/guozhan-gallery`
- Admin plugin: `plugins/GuozhanClientAdmin` (`0.6.2`)
- Piwigo core files, default themes and default plugins

## Active Web Root Layout

The validated deployment serves the repository checkout from `/config/www`.
The web server index order must prefer `index.html` before `index.php`.

- `index.html` and `screens/*.html` are the public customer pages.
- `assets/guozhan.js` connects those pages to the same-origin Piwigo WebService.
- `admin.php`, `identification.php`, `ws.php` and the remaining PHP files keep
  Piwigo authentication, administration, database and media functions active.
- The static frontend files are deployment files, not screenshots or mockups.

Do not publish a PHP configuration override as a normal web asset. Copy
`deployment/php/php-local.ini.example` outside the web root to the container's
`/config/php/php-local.ini` when the upload limit override is required.

## Fresh Server Test

1. Place the repository contents in the server web root (the validated container
   path is `/config/www`).
2. Make sure Piwigo writable directories have proper permissions, especially `_data/`, `upload/`, `galleries/` and `local/`.
3. Open the site URL and finish the Piwigo installer with your database credentials.
4. Log in to the original Piwigo admin once, enable theme `guozhan-gallery`, and set it as the default theme.
5. Enable plugin `GuozhanClientAdmin`. After the plugin is enabled, opening `admin.php` only shows the Guozhan customer dashboard and native Piwigo admin pages are hidden.
6. Open the Guozhan admin page and run/check category contract sync.
7. Check the dashboard health status: database tables, category contract, upload contract, WebService, frontend sync and active theme should all be normal.
8. Use `Import sample works` to write the 8 bundled locally generated sample images into Piwigo as real gallery works, or upload your own test works.
9. Check frontend pages: home, categories, category list, search, detail and contact.

## Existing Piwigo Merge

1. Back up server files and database first.
2. Upload `themes/guozhan-gallery` to the server Piwigo `themes/` directory.
3. Upload `plugins/GuozhanClientAdmin` to the server Piwigo `plugins/` directory.
4. Enable theme `guozhan-gallery` and set it as the default theme first, then enable plugin `GuozhanClientAdmin`.
5. Once the plugin is enabled, `admin.php` only shows the Guozhan customer dashboard. Run category contract sync from that dashboard.
6. Do not overwrite existing server `local/config`, `_data`, `upload` or `galleries` unless this is a fresh test site.

The GitHub repository intentionally excludes server runtime data such as the installed database, uploaded originals and generated derivatives. For a fresh test site, finish the installer first, then import the bundled sample works from the Guozhan admin page.

## Completed Integration

- 10 top-level frontend sections and 29 child sections.
- 39 frontend editable/upload positions, including all top sections, child sections and 7 reserved unknown exhibition sections.
- Brand and contact UI uses `gzca.contact.get`.
- List cards prefer `thumbnail_url`; detail page prefers `display_url`.
- Admin upload, category, exhibition, works, homepage carousel and two-contact management are covered.
- Uploads are sent sequentially in selectable batches of 20, 30, 40 or 50
  images, defaulting to 20 and capped at 72 MB per request.
- The backend accepts at most 50 files per request and stores oversized artwork
  near the 1-3 MB target when supported image libraries are available.
- Admin can import 8 bundled sample works into Piwigo storage/database for first server testing.

## Local Validation

- `node --check assets/guozhan.js`
- `node --check plugins/GuozhanClientAdmin/assets/admin.js`
- `git diff --check`
- Run `php -l` on changed plugin PHP files when a PHP CLI is available.

Before publishing a repository revision, also verify that no database dump,
administrator credential, API key, `_data` cache, `upload` original, or
`galleries` media file is staged.

The local machine does not have a usable `php` command. Please run PHP syntax checks on the server or another PHP environment.
