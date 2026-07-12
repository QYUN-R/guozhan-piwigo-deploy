# Guozhan Piwigo Test Deploy Notes

Updated: 2026-07-12

This is a complete Piwigo 16.4.0 test project with:

- Frontend theme: `themes/guozhan-gallery`
- Admin plugin: `plugins/GuozhanClientAdmin`
- Piwigo core files, default themes and default plugins

## Fresh Server Test

1. Upload the contents of the `piwigo` directory to the server web root.
2. Make sure Piwigo writable directories have proper permissions, especially `_data/`, `upload/`, `galleries/` and `local/`.
3. Open the site URL and finish the Piwigo installer with your database credentials.
4. Log in to the original Piwigo admin once, enable theme `guozhan-gallery`, and set it as the default theme.
5. Enable plugin `GuozhanClientAdmin`. After the plugin is enabled, opening `admin.php` only shows the Guozhan customer dashboard and native Piwigo admin pages are hidden.
6. Open the Guozhan admin page and run/check category contract sync.
7. Check the dashboard health status: database tables, category contract, upload contract, WebService, frontend sync and active theme should all be normal.
8. Use `Import sample works` to write the 8 bundled locally generated sample images into Piwigo as real gallery works, or upload your own test works.
9. Check frontend pages: home, categories, competition, search, detail and contact.

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
- Competition page uses `gzca.competition.getMedia` and sends `competition` to `gzca.images.getList`.
- Brand and contact UI uses `gzca.contact.get`.
- List cards prefer `thumbnail_url`; detail page prefers `display_url`.
- Admin upload, category, exhibition, works, competition and contact functions are covered.
- Admin can import 8 bundled sample works into Piwigo storage/database for first server testing.

## Local Validation

- `node tools/verify-backend.js`: passed.
- `node tools/verify-contract.js`: passed.
- `node tools/test-mock-api.js`: passed.
- Theme JavaScript `node --check`: passed.

The local machine does not have a usable `php` command. Please run PHP syntax checks on the server or another PHP environment.
