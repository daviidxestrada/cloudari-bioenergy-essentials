# Cloudari BioEnergy Essentials

WordPress plugin for EERA Bioenergy member access control and recovered publication redirects.

## Plugin

- Plugin name: `Cloudari BioEnergy Essentials`
- Slug: `cloudari-bioenergy-essentials`
- Main file: `cloudari-bioenergy-essentials.php`
- Current version: `1.4.12`
- Update source: `https://github.com/daviidxestrada/cloudari-bioenergy-essentials`

## GitHub Updates

This repository includes [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) in `plugin-update-checker/`.

The main plugin file loads:

```php
require_once CLOUDARI_BIOENERGY_ESSENTIALS_DIR . 'includes/updater.php';
```

`includes/updater.php` points Plugin Update Checker at this public GitHub repository and the `main` branch.

## Release Flow

1. Update the plugin header version in `cloudari-bioenergy-essentials.php`.
2. Update `CLOUDARI_BIOENERGY_ESSENTIALS_VERSION` to the same value.
3. Update `readme.txt` stable tag and changelog.
4. Commit and push to `main`.
5. Create a GitHub release/tag with a ZIP asset named for the version, for example `cloudari-bioenergy-essentials-1.4.12.zip`.

WordPress will detect the update through Plugin Update Checker.
