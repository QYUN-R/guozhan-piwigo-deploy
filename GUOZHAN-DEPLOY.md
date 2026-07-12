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
4. Log in to Piwigo admin and enable plugin `GuozhanClientAdmin`.
5. Enable theme `guozhan-gallery` and set it as the default theme.
6. Open the Guozhan admin page and run/check category contract sync.
7. Check health status: database tables, category contract, upload contract, WebService, frontend sync and active theme should all be normal.
8. Upload test works to normal categories, direct-upload categories, reserved exhibition categories and competition media.
9. Check frontend pages: home, categories, competition, search, detail and contact.

## Existing Piwigo Merge

1. Back up server files and database first.
2. Upload `themes/guozhan-gallery` to the server Piwigo `themes/` directory.
3. Upload `plugins/GuozhanClientAdmin` to the server Piwigo `plugins/` directory.
4. Enable the plugin and theme, then run category contract sync.
5. Do not overwrite existing server `local/config`, `_data`, `upload` or `galleries` unless this is a fresh test site.

## Completed Integration

- 10 top-level frontend sections and 29 child sections.
- 39 frontend editable/upload positions, including all top sections, child sections and 7 reserved unknown exhibition sections.
- Competition page uses `gzca.competition.getMedia` and sends `competition` to `gzca.images.getList`.
- Brand and contact UI uses `gzca.contact.get`.
- List cards prefer `thumbnail_url`; detail page prefers `display_url`.
- Admin upload, category, exhibition, works, competition and contact functions are covered.

## Local Validation

- `node tools/verify-backend.js`: passed.
- `node tools/verify-contract.js`: passed.
- `node tools/test-mock-api.js`: passed.
- Theme JavaScript `node --check`: passed.

The local machine does not have a usable `php` command. Please run PHP syntax checks on the server or another PHP environment.
