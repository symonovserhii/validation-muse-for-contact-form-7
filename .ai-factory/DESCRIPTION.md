# Validation Muse for Contact Form 7

## Overview

*Write your own Contact Form 7 error and required-field messages — per form, per field, no code.*

Validation Muse lets site owners author custom validation copy for every Contact Form 7 field directly in the CF7 form editor (a "Custom Validation" panel), stored per form. Its defining feature is **CF7 6.x Schema-based Validation (SWV) compatibility**: most CF7 validation plugins broke when SWV landed, so Validation Muse runs its filters at priority 20 (after CF7 core) and uses Reflection to replace SWV error text on already-invalidated fields — so custom messages show up on both the legacy and new validation engines.

Messages are stored in form **post meta**, so they travel with the form (compatible with CF7 duplication and import/export plugins). When the companion **Flavor** translation plugin is active, per-language tabs and a one-click AI Translate button appear automatically; with zero overhead when Flavor is absent.

Target user: anyone running Contact Form 7 who wants branded/localized validation messages without global settings pages or JS hacks. Current version: **1.4.3** (published on wordpress.org, slug `validation-muse-for-contact-form-7`).

## Core Features (implemented)

### Validation messages
- Per-form, per-field custom **required-field** messages for any required tag (text, textarea, select, checkbox, radio, file, …).
- Per-field **invalid-format** messages for `email`, `url`, `tel`, `number`/`range`, and `date`.
- HTML allowed in messages, sanitized via `wp_kses_post()`.
- CF7 6.x **SWV-compatible** via priority-20 filters + Reflection-based error replacement.
- Self-deactivates with an admin notice if Contact Form 7 is not active.

### Editor UX
- "Custom Validation" panel injected into the CF7 form editor (`wpcf7_editor_panels`).
- Per-field message fields auto-discovered from the form's tags; saved on `wpcf7_save_contact_form`.
- Admin assets (CSS/JS) enqueued only on CF7 editor screens.

### Multilingual (via Flavor)
- Language tabs + one-click **AI Translate** button when Flavor is active.
- Per-language translation storage, retrieval, and deletion; auto-translation flagged.
- AJAX endpoint `vmcf7_ai_translate` to translate a whole form into a target language.
- Ships translated into German, Russian, Spanish, French, Portuguese (BR), Ukrainian (`.po/.mo` + `.pot`).

## Extensibility (hooks)

- `vmcf7_loaded` — fired after the plugin initializes.
- `vmcf7_validation_tag_types` — filter to register custom field types for validation.

## Tech Stack

- **PHP** ≥ 7.4, **WordPress** ≥ 5.8 (tested to 7.0), **Contact Form 7** required.
- OOP: `VMCF7_Loader`, `VMCF7_Admin`, `VMCF7_Flavor` (no Composer runtime deps).
- Integrates optionally with the **Flavor** translation plugin for multilingual + AI translation.
