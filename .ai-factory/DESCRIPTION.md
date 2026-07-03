# Validation Muse for Contact Form 7

## Overview

*Write your own Contact Form 7 validation rules and error messages — per form, per field, no code.*

Validation Muse lets site owners author custom validation **rules and copy** for every Contact Form 7 field directly in the CF7 form editor (a "Custom Validation" panel), stored per form. Its defining feature is **CF7 6.x Schema-based Validation (SWV) compatibility**: most CF7 validation plugins broke when SWV landed, so Validation Muse (a) runs its legacy-hook filters at priority 20 (after CF7 core) and uses Reflection to replace SWV error text on already-invalidated fields, and (b) injects its own rules (required/invalid messages, min/max length) directly into CF7's native SWV schema via `wpcf7_swv_create_schema`, so validation — including custom messages — mirrors instantly on the frontend through CF7's own client-side JS, with no plugin-authored JS validation logic and full accessibility support.

Messages and rules are stored in form **post meta** (prefix `_vmcf7_`), so they travel with the form (compatible with CF7 duplication and import/export plugins). A one-time, self-guarded migration renames any orphaned `_cf7cv_*` rows left over from the plugin's pre-1.2.0 name to the current `_vmcf7_` prefix. When the companion **Flavor** translation plugin is active, per-language tabs and a one-click AI Translate button appear automatically; with zero overhead when Flavor is absent. Independently, WPML and Polylang are supported natively via string registration/translation hooks.

Target user: anyone running Contact Form 7 who wants branded, regex-validated, conditionally-required, and/or localized validation messages without global settings pages or JS hacks. Current version: **1.6.3** (published on wordpress.org, slug `validation-muse-for-contact-form-7`).

## Core Features (implemented)

### Validation rules & messages
- Per-form, per-field custom **required-field** messages for any required tag (text, textarea, select, checkbox, radio, file, acceptance, quiz, …).
- Per-field **invalid-format** messages for `email`, `url`, `tel`, `number`/`range`, `date`, and `time`.
- **Custom regex rules** per field — admin-supplied pattern (delimiter-wrapped and ReDoS-hardened via low `pcre.backtrack_limit`/`pcre.recursion_limit`) with its own error message.
- **Min/max length rules** per field, with a message supporting `{min}`/`{max}` placeholder expansion.
- **Conditional "required-if" rules** — a field becomes required only when a named companion field has a value, with its own message.
- **Placeholder tokens** — `{field_label}` (auto-resolved from the form's `<label>` markup or prettified field name), `{min}`, `{max}` — expanded in any message at validation time.
- HTML allowed in messages, sanitized via `wp_kses_post()`.
- CF7 6.x **SWV-native**: standard rules (required, email/url/tel/number/date/time, min/maxlength) are injected into CF7's own SWV schema with overridden `error`/`threshold` properties (via Reflection on the rule's protected `properties`), so client-side (frontend JS) validation shows the same custom copy — no separate JS validation engine to maintain.
- Self-deactivates with an admin notice if Contact Form 7 is not active.

### Editor UX
- "Custom Validation" panel injected into the CF7 form editor (`wpcf7_editor_panels`).
- Per-field message/rule inputs auto-discovered from the form's tags; saved on `wpcf7_save_contact_form`.
- **Rule sets import/export** — export a form's rules as JSON (`vmcf7_export_rules` AJAX) and import them into another form (`vmcf7_import_rules`).
- **Copy rules between forms** (`vmcf7_copy_rules` AJAX) and **bulk-apply a saved global default template** to any form (`vmcf7_bulk_apply` AJAX; "Save as template" / "Apply template" buttons in the panel).
- Live message preview in the panel, evaluating placeholder tokens as the admin types.
- Admin assets (CSS/JS) enqueued only on CF7 editor screens.

### Multilingual
- **WPML & Polylang** — validation messages are registered as translatable strings on form save (`icl_register_string`/`wpml_register_string`, `pll_register_string`) and resolved through the `vmcf7_translate_message` filter at validation time — no Flavor dependency required.
- **Flavor** (optional) — language tabs + one-click **AI Translate** button appear when Flavor is active; per-language translation storage, retrieval, deletion, export/import; auto-translation flagged. AJAX endpoint `vmcf7_ai_translate` translates a whole form into a target language.
- Ships translated (plugin UI, `.po`/`.mo` + `.pot`) into German, Russian, Spanish, French, Portuguese (BR), Ukrainian.

### Data integrity
- One-time, idempotent migration (`VMCF7_Migrations::maybe_run()`, guarded by an autoloaded option flag) renames orphaned `_cf7cv_*` post meta rows — left over from the plugin's pre-rename days — to the current `_vmcf7_*` prefix, collision-safe (existing current-prefix rows win) and cache-invalidated per post.

## Extensibility (hooks)

- `vmcf7_loaded` — action fired after the plugin initializes.
- `vmcf7_validation_tag_types` — filter to register custom field types for validation.
- `vmcf7_translate_message` — filter to plug in additional translation providers for a custom message (used internally for WPML/Polylang).
- `vmcf7_debug` — action fired on Reflection failures, invalid regex, and migration completion (useful diagnostic hook; consumers can log it).

## Tech Stack

- **PHP** ≥ 8.0, **WordPress** ≥ 6.0 (tested to 7.0), **Contact Form 7** required (hard dependency, self-deactivates otherwise).
- OOP, no framework: `VMCF7_Loader`, `VMCF7_Admin`, `VMCF7_Flavor`, `VMCF7_Rules`, `VMCF7_I18n_Compat`, `VMCF7_Migrations` — classmap-autoloaded via Composer (`includes/`), no runtime Composer dependencies.
- Integrates optionally with the **Flavor** translation plugin for multilingual + AI translation; natively supports **WPML**/**Polylang** without any dependency.
- Dev-only: PHPUnit 9.5 + Brain Monkey for WordPress-free unit tests (`tests/`).

## Non-Functional Requirements

- **Security**: regex evaluation is ReDoS-hardened (temporary low PCRE backtrack/recursion limits, restored after use); all persisted values sanitized on save (`sanitize_rule_value()`), messages allow safe HTML only (`wp_kses_post()`); AJAX handlers are nonce-protected.
- **Compatibility**: legacy SWV-absent CF7 installs and CF7 6.x SWV installs are both supported by the same rule set, without divergent code paths for the admin.
- **i18n**: plugin UI ships pre-translated into 6 languages; validation *messages* (admin-authored content) are translatable independently via WPML/Polylang or Flavor.
- **Backward compatibility**: pre-1.2.0 `_cf7cv_*` meta rows are auto-migrated on first load after upgrade, at most once per site (autoloaded flag avoids repeat cost).
