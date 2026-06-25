# Cloudari BioEnergy Essentials

WordPress plugin for EERA Bioenergy member access control.

Current live plugin observed on `eerabioenergy.eu`:

- Plugin name: `Cloudari BioEnergy Essentials`
- Slug: `cloudari-bioenergy-essentials`
- Main file: `cloudari-bioenergy-essentials/cloudari-bioenergy-essentials.php`
- Current version: `1.4.8`
- Description: `Adds isolated bcrypt login and members-area access control for the EERA Bioenergy Elementor kit.`

## GitHub Updates

This repository includes [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) vendored in `plugin-update-checker/`.

Load the updater from the plugin main file:

```php
require_once __DIR__ . '/includes/updater.php';
```

The updater points to:

```text
https://github.com/daviidxestrada/cloudari-bioenergy-essentials
```

## Release Flow

1. Update the plugin header version in `cloudari-bioenergy-essentials.php`.
2. Commit and push to `main`.
3. Create a GitHub release/tag, for example `v1.4.9`.
4. WordPress will detect the update through Plugin Update Checker.

Do not create a public release from this repository until the full live plugin code has been added here.
