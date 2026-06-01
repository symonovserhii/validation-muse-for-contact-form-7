# Validation Muse for Contact Form 7

Custom error and required-field messages for Contact Form 7 — per form, per field, no code. CF7 6.x SWV-compatible and multilingual.

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/validation-muse-for-contact-form-7)](https://wordpress.org/plugins/validation-muse-for-contact-form-7/)
[![WordPress Plugin Rating](https://img.shields.io/wordpress/plugin/stars/validation-muse-for-contact-form-7)](https://wordpress.org/plugins/validation-muse-for-contact-form-7/)
[![License: GPL v2](https://img.shields.io/badge/License-GPLv2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Write your own error messages for every Contact Form 7 field, directly in the form editor — no global settings page, no JavaScript hacks. Messages are stored in form post meta, so they travel with the form (compatible with CF7 duplication and import/export).

## Features

- Per-form, per-field **required** and **invalid-format** messages
- **CF7 6.x SWV-compatible** — runs at priority 20 and replaces Schema-based Validation error text via Reflection
- Supported invalid-format types: `email`, `url`, `tel`, `number`/`range`, `date`, `time`
- HTML allowed in messages (sanitized via `wp_kses_post()`)
- **Multilingual via [Flavor](https://wordpress.org/plugins/)** — language tabs + one-click AI Translate when active; zero overhead when not
- Lightweight: no admin bloat, no tracking, no upsells

## Installation

1. Install from [WordPress.org](https://wordpress.org/plugins/validation-muse-for-contact-form-7/) or via wp-admin (Contact Form 7 must be active).
2. Activate the plugin.
3. Edit any CF7 form → open the **Custom Validation** panel → enable it and write your messages.
4. (Optional) Install the Flavor translation plugin for per-language messages with AI assistance.

## Hooks

| Hook | Type | Purpose |
|------|------|---------|
| `vmcf7_loaded` | action | Fired after the plugin initializes |
| `vmcf7_validation_tag_types` | filter | Register custom field types for validation |
| `vmcf7_debug` | action | Diagnostic signal (e.g. SWV Reflection failures) under `WP_DEBUG` |

## Development

No build step — edit PHP/JS/CSS directly. Dev tooling (PHPUnit) is managed via Composer.

### Tests

```bash
composer install
vendor/bin/phpunit
```

### Releasing a new version

The project is split into a GitHub working copy (`github/`) and a WordPress.org SVN working copy (`svn/`).
Releases are pushed to SVN **manually** (no CI auto-deploy).

1. Bump the version in `validation-muse-for-contact-form-7.php` (header + `VMCF7_VERSION`) and `Stable tag` in `readme.txt` (and the translated `readme-*.txt`); add a changelog entry.
2. Commit and push to `main`; tag `vX.Y.Z`.
3. Sync to SVN trunk, tag, and commit:

```bash
../sync-to-svn.sh                 # rsync github/ → svn/trunk honoring .distignore
cd ../svn && svn cp trunk tags/X.Y.Z && svn ci -m "Release X.Y.Z"
```

## License

GPL-2.0-or-later
