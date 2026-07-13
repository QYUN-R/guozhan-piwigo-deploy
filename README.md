## Guozhan deployment package

This repository contains the current Guozhan gallery deployment package:

- Static customer frontend: `index.html`, `screens/`, and `assets/`
- Piwigo 16.4.0 backend and WebService API
- Custom admin plugin: `plugins/GuozhanClientAdmin` version `0.6.2`
- Sequential artwork upload batches of 20, 30, 40, or 50 images (20 by default)

The production-style container layout serves this checkout from `/config/www`.
Nginx must prefer `index.html` before `index.php` so the static customer frontend
is the public entry point while Piwigo continues to provide the database, admin,
authentication, image storage, and WebService endpoints.

Runtime data is intentionally not stored in Git. Do not commit or overwrite:

- `local/config/database.inc.php` or other server-local configuration
- `_data/` caches and generated derivatives
- `upload/` originals
- `galleries/` synchronized media
- administrator credentials, QR images, API keys, or database dumps

See [GUOZHAN-DEPLOY.md](GUOZHAN-DEPLOY.md) for deployment and validation notes.

### Releases and rollback

The deployed source version is recorded in `VERSION`. Stable GitHub revisions
are tagged as `guozhan-YYYY.MM.DD.N`, and their changes are listed in
`CHANGELOG.md`. To inspect or restore an older source release, check out its tag
instead of guessing from backup folder names.

---

<img src="https://piwigo.org/plugins/piwigo-piwigodotorg/images/piwigo.org.svg" width="200" alt="Piwigo logo">

Manage your photo library. Piwigo is open source photo gallery software for the web. Designed for organisations, teams and individuals.

![screenshot](https://piwigo.org/screenshots/github-screenshot-2.10.jpg)

The [piwigo.org](https://piwigo.org) website introduces you to Piwigo. You'll find a demo, forums, wiki and news.
 
## Requirements

 * A webserver (Apache or nginx recommended)
 * PHP 7.4+. Piwigo can run with PHP 7.0+ but these end-of-life versions are no longer maintained and may expose your site to security vulnerabilities.
 * MySQL 5 or greater or MariaDB equivalent
 * ImageMagick (recommended) or PHP GD

## Quick start install

### NetInstall

 * Download the [NetInstall script](https://piwigo.org/download/dlcounter.php?code=netinstall)
 * Transfer the script to your web space with any FTP client
 * Open the script in you web browser (for example http://example.com/piwigo-netinstall.php) and follow the steps

[More information](https://piwigo.org/guides/install/netinstall)

### Manual

 * Download the [latest stable version](https://piwigo.org/download/dlcounter.php?code=latest) and unzip it
 * Transfer everything to your web space with any FTP client
 * Open your website (for example http://example.com/piwigo) and follow the steps

[More information](https://piwigo.org/guides/install/manual)

If you do not have your own server, consider the [piwigo.com](https://piwigo.com/) hosting solution.

## Contributing

Piwigo is widely driven by its community; if you want to improve the code, fork this repo and submit your changes to the `master` branch. See our [Contribution guide](https://github.com/Piwigo/Piwigo/blob/master/docs/CONTRIBUTING.md).

## License

Piwigo is released under the GPL v2 license. See our [Copying details](https://github.com/Piwigo/Piwigo/blob/master/COPYING.txt).
