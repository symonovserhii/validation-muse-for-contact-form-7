# Validation Muse for Contact Form 7

[![Version](https://img.shields.io/wordpress/plugin/v/validation-muse-for-contact-form-7)](https://wordpress.org/plugins/validation-muse-for-contact-form-7/) [![Rating](https://img.shields.io/wordpress/plugin/stars/validation-muse-for-contact-form-7)](https://wordpress.org/plugins/validation-muse-for-contact-form-7/) [![Active installs](https://img.shields.io/wordpress/plugin/installs/validation-muse-for-contact-form-7)](https://wordpress.org/plugins/validation-muse-for-contact-form-7/) [![License: GPL v2](https://img.shields.io/badge/License-GPLv2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Requires at least: **6.0** · Tested up to: **7.0** · Requires PHP: **8.0** · Stable tag: **1.6.3**

Custom validation rules & messages for Contact Form 7 — regex, length, required-if, per field, CF7 6.x SWV-compatible, multilingual.

## Description
**Validation Muse** lets you write your own validation rules and error messages for every Contact Form 7 field — directly in the form editor, per form, per field. No code, no global settings page, no JavaScript hacks.

Most CF7 validation plugins broke when Contact Form 7 6.x introduced **Schema-based Validation (SWV)**. Validation Muse runs its filters at priority 20 (after CF7 core) and uses Reflection to replace SWV error text on already-invalidated fields, so your custom copy actually shows up — even on the new validation engine.

### Why Validation Muse
* **CF7 6.x SWV compatible** — works with the new Schema-based Validation engine, not just legacy hooks.
* **Custom Regex & Length Rules** — define custom regular expression patterns, min length, and max length rules per field with their own error messages.
* **Conditional "Required-If" Rules** — make fields required only when a companion field is filled/checked.
* **Client-Side SWV Integration** — standard validation rules (required, email, length) are injected into CF7's native SWV engine for instant frontend feedback with full accessibility (A11y) support.
* **Rule Sets Import/Export & Templates** — export/import validation rules as JSON, copy them from other forms, and bulk apply global templates.
* **Placeholder Tokens** — use `{field_label}`, `{min}`, and `{max}` in validation messages to generate dynamic texts.
* **Per-form, per-field** — each form keeps its own messages; no global override.
* **Stored in post meta** — messages live with the form, compatible with CF7 form duplication and third-party CF7 import/export plugins.
* **Multilingual via WPML, Polylang & Flavor** — translates rules using standard WPML/Polylang hooks, and features language tabs + one-click AI Translate when Flavor is active.
* **Developer-friendly** — extensibility hooks `vmcf7_loaded` and `vmcf7_validation_tag_types` let you add custom field types.
* **Lightweight** — no admin bloat, no tracking, no upsells.

### Supported field types
* Required-field messages: any required tag (`text`, `textarea`, `select`, `checkbox`, `radio`, `file`, etc.).
* Invalid-format messages: `email`, `url`, `tel`, `number` (including `range`), `date`, and `time` fields.
* Custom error messages on validation failure: `file` (size/type checks), `acceptance` (unchecked/unaccepted state), and `quiz` (incorrect answer).
* HTML inside messages is allowed and sanitized through `wp_kses_post()`.
* Custom Regex and Min/Max Length rules support all input fields (excluding acceptance and quiz).

### Translations
The plugin ships with a `.pot` file and is already translated into German, Spanish, French, Portuguese (Brazil), Russian, and Ukrainian. [Help translate it into your language.](https://translate.wordpress.org/projects/wp-plugins/validation-muse-for-contact-form-7)

## Installation
1. Install from **Plugins → Add New** and search for *Validation Muse for Contact Form 7*, or upload the `validation-muse-for-contact-form-7` folder to `/wp-content/plugins/`.
2. Activate the plugin. Contact Form 7 must already be active — Validation Muse will deactivate itself with an admin notice if it is not.
3. Edit any Contact Form 7 form, open the **Custom Validation** panel, enable it, and write your messages.
4. (Optional) Install the Flavor translation plugin to translate messages per language with AI assistance.

## Frequently Asked Questions
### Does this work with Contact Form 7 6.x and Schema-based Validation (SWV)?
Yes. Since version 1.3.0, Validation Muse hooks at priority 20 (after CF7 core) and uses Reflection to replace SWV error text on already-invalidated fields. Since 1.6.1, standard rules (required, email/url/tel/number/date/time, min/max length) are also injected directly into CF7's own SWV schema, so client-side (frontend JS) validation shows your custom messages instantly — through CF7's native validation engine, with no extra script from this plugin.

### How is this different from other CF7 validation plugins?
Validation Muse is the only CF7 validation plugin that (1) is compatible with CF7 6.x SWV out of the box — both server-side and in CF7's own client-side validation, (2) stores messages in form post meta so they live with the form (compatible with CF7 form duplication and import/export plugins), and (3) integrates with the Flavor translation plugin for per-language messages with one-click AI translation.

### Can I translate validation messages per language?
Yes — install the Flavor translation plugin and Validation Muse will show language tabs in the form editor plus an *AI Translate* button. Translations are stored in Flavor's database; uninstalling Validation Muse cleans them up. WPML and Polylang are also supported natively, without Flavor.

### Which field types support custom invalid or validation failure messages?
* `email`, `url`, `tel`, `number` (including `range`), `date`, and `time` support custom invalid-format messages.
* `file` supports custom file size/type validation error messages.
* `acceptance` supports custom error messages when the checkbox is not accepted.
* `quiz` supports custom error messages when the answer is incorrect.
* Any required field of any type can have a custom required-field message.

### Where are the messages stored?
In each form's post meta. They live with the form, so duplicating a form (built into CF7) keeps the messages. CF7 has no native export, but third-party CF7 import/export plugins read post meta — so migrations across sites work without a separate import step.

### Can I use HTML in validation messages?
Yes, basic HTML is allowed and sanitized via `wp_kses_post()`.

### Does this plugin require Contact Form 7?
Yes. CF7 must be installed and active. The plugin shows an admin notice and self-deactivates if CF7 is missing.

### Is there a settings page?
No. Configuration lives inside each form, in the **Custom Validation** panel. There is no global settings page by design — every form keeps its own messages.

### Does the plugin track or send any data?
No. Validation Muse makes no external requests. The optional AI Translate button (Flavor integration) routes through Flavor's own configured provider.

## Screenshots
1. The **Custom Validation** panel inside the Contact Form 7 editor — enable per form, write messages per field.
2. Per-language tabs and the **AI Translate** button (visible when the Flavor translation plugin is active).
3. Required-field message rendered on the frontend.
4. Invalid-format message for an email field rendered on the frontend.

## Changelog
### 1.6.3
* i18n: fully regenerated the `.pot` translation template — the catalog was frozen since 1.2.0 and covered only about 20% of the plugin's translatable strings (regex/length rules, rule templates, AI Translate errors, and more were never extractable before). All six shipped languages (German, Spanish, French, Portuguese, Russian, Ukrainian) are now fully translated against the current UI.
* Docs: synced all translated readme files — feature list, supported field types, and FAQ now reflect 1.6.0–1.6.2 functionality (custom regex/length rules, required-if, native SWV client-side validation, rule templates); their Changelog/Upgrade Notice sections, previously stuck at 1.4.2, now cover every release up to 1.6.2.
* Fix: added a missing `translators:` comment for a placeholder string (i18n code quality, no behavior change).

### 1.6.2
* Fix: messages saved by versions prior to the plugin rename (stored under the legacy `_cf7cv_` meta prefix) were invisible to the current code. A one-time migration now renames them to the `_vmcf7_` prefix automatically, restoring lost messages. Collision-safe and runs once.

### 1.6.1
* Fix: duplicate/overwritten validation messages on frontend by integrating with CF7's native SWV engine.
* Fix: admin panel drawers and preview cards rendered expanded due to CF7 editor panel stripping inline styles.

### 1.6.0
* New: custom regex and min/max-length validation rules per field, each with its own message.
* New: validation messages for `file`, `acceptance`, and `quiz` fields; conditional "required-if" messages.
* New: message placeholder tokens `{field_label}`, `{min}`, `{max}`.
* New: client-side inline validation mirroring the server messages, with `aria-describedby` accessibility.
* New: live message preview, copy messages between forms, JSON import/export of message sets, and editor lint.
* New: WPML/Polylang string registration alongside the Flavor bridge; translation import/export.

### 1.5.0
* Minimum PHP raised to 8.0; minimum WordPress raised to 6.0; tested up to WordPress 7.0.
* New: custom validation messages for `time` fields.
* Clearer AI Translate error feedback in the form editor.
* Reflection-based SWV message replacement now logs failures under WP_DEBUG for easier diagnosis.
* Improved handling of pending translations (no more silent fallback) with per-request caching.
* Code modernized to PHP 8 idioms; output escaping hardened; first unit-test suite added.

### 1.4.2
* Plugin URI: now points to the dedicated landing page at https://plugins.symonov.com/validation-muse-for-cf7/
* No code or behavior changes

### 1.4.1
* Readme: USP-first rewrite for SEO discoverability
* Tags: replaced generic `messages`/`forms`/`customization` with targeted `contact-form-7`, `cf7`, `validation`, `error-message`, `multilingual`
* FAQ: added entries for CF7 6.x SWV compatibility, comparison with other CF7 validation plugins, and multilingual via Flavor

### 1.4.0
* Added multilingual support via Flavor translation plugin integration
* Validation messages can now be translated per language in the form editor
* Language tabs appear automatically when Flavor plugin is active
* AI Translate button for one-click machine translation of all messages
* Translations stored in Flavor's database, keeping plugin data portable
* Zero overhead when Flavor is not installed — all calls behind class_exists() checks
* Flavor translations cleaned up on plugin uninstall

### 1.3.0
* Fixed compatibility with Contact Form 7 6.x SWV (Schema-based Validation)
* Validation filters now run at priority 20 (after CF7 core) to replace SWV error messages
* Added Reflection-based error replacement for already-invalidated fields
* Custom messages now correctly override default CF7 "The field is required." text

### 1.2.1
* Fixed variable name mismatch causing "No required fields" error

### 1.2.0
* Refactored codebase to follow WordPress Coding Standards
* Reorganized file structure for better maintainability
* Added PHPDoc blocks to all functions and methods
* Improved accessibility with ARIA labels
* Fixed JavaScript prefix inconsistency
* Fixed uninstall script to use correct meta prefix
* Added extensibility hooks (`vmcf7_loaded`, `vmcf7_validation_tag_types`)
* Changed capability check from `manage_options` to `wpcf7_edit_contact_forms`
* Updated POT file name to match text domain

### 1.1.2
* Changed plugin name.

### 1.1.1
* Added .gitignore file.

### 1.1.0
* Added WordPress repository collateral (readme, license, POT file).
* Reworked validation hooks to override required and invalid messages without relying on AJAX filters.
* Hardened sanitization, text domain loading, and uninstall cleanup for release readiness.

### 1.0.1
* Initial public iteration bundled with the project.

## Upgrade Notice
### 1.6.3
Documentation and translation release — no functional or behavior changes. Fully refreshes translated readmes and the .pot/.po/.mo translation catalog.

### 1.6.2
Recovers validation messages saved by pre-1.2.0 versions (legacy `_cf7cv_` meta prefix) via a one-time, automatic migration. Safe, no action needed.

### 1.6.1
Fixes duplicate/overwritten validation messages on the frontend and admin panel layout regressions from 1.6.0. Recommended for all 1.6.0 users.

### 1.6.0
Adds custom regex/length rules, more field types, required-if messages, client-side mirroring, live preview, rule import/export/templates, and WPML/Polylang support. No data migration required.

### 1.5.0
Raises the minimum PHP to 8.0 and minimum WordPress to 6.0. Check your hosting environment before upgrading.

### 1.4.2
Plugin URI now points to the dedicated landing page on plugins.symonov.com. No code changes.

### 1.4.1
Documentation-only release. Refreshed readme for clearer feature discovery.

### 1.4.0
Adds multilingual support via the Flavor translation plugin and one-click AI translation of validation messages. No data migration required.

### 1.3.0
Restores compatibility with Contact Form 7 6.x Schema-based Validation (SWV). Recommended for all CF7 6.x users.
