# GuozhanClientAdmin 0.5.1

Piwigo 16.4 compatible customer administration plugin for 图物计划国展素材馆。

## Runtime data

- Core images: `IMAGES_TABLE`
- Core albums: `CATEGORIES_TABLE`
- Image/album links: `IMAGE_CATEGORY_TABLE`
- Work metadata: `<prefix>gzca_works`
- Category metadata and stable keys: `<prefix>gzca_categories`
- Competition types: `<prefix>gzca_competition_media`
- Brand/contact config: Piwigo config key `gzca_config`
- Logo and QR files: `_data/guozhan-client-admin/`

The repository does not include installed Piwigo database rows or runtime uploads. On a fresh test site, enable this plugin, sync the category contract, then use the admin setup action to import the 8 bundled sample works into real Piwigo image records.

## Public API

- `gzca.categories.getList`
- `gzca.competition.getMedia`
- `gzca.images.getList`
- `gzca.images.getInfo`
- `gzca.contact.get`

List APIs return public data only. Artwork lists expose only `xsmall`, `thumb` and `square` derivatives, with a hard limit of 20 works per request. The detail API exposes a controlled `xlarge` display image and smaller derivatives. Neither public response contains a dedicated original/download URL.

Frontend replacement should use returned `key` / `system_key`, `id`, `url` and `code_prefix` values instead of hard-coded Piwigo numeric category IDs. Reserved exhibitions keep stable keys such as `caa-unknown-07` even after the customer renames them.

## Access model

- Webmaster: full Piwigo administration.
- Administrator: customer dashboard when restriction is enabled.
- Guest/member: no administration actions.

All writes require administrator access and a valid Piwigo CSRF token. Schema updates are additive and do not delete core Piwigo images, albums or users.
