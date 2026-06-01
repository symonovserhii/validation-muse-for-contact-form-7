# Roadmap — Validation Muse for Contact Form 7

> **Status:** all items below are scheduled in `plans/m2-functional-features.md` (release 1.6.0), to run
> after `plans/m1-php8-wp7-hardening.md` (release 1.5.0) ships. This file is the rationale/source for each.

Functional improvement backlog (what to add/improve), grounded in current capabilities:
per-form/per-field custom **required** + **invalid-format** messages, CF7 6.x **SWV-compatible**
(priority-20 + Reflection), messages stored in **post meta**, **Flavor** multilingual + one-click AI translate,
HTML allowed (`wp_kses_post`). Extensible via `vmcf7_loaded` / `vmcf7_validation_tag_types`.

Priority: **H** high value · **M** solid · **L** nice-to-have.
"(in harden plan)" = already scheduled in `plans/harden-php8-wp7-functional.md`.

## Validation coverage — the core product axis
- **H — Custom pattern / regex rule per field.** Today messages only override CF7's built-in checks (`class-vmcf7-loader.php:118`). Let an admin attach a regex (or min/max length) **and** its message to any text field — real new validation, not just re-wording. Wire through the same panel + `vmcf7_validation_tag_types`.
- **H — More field types:** `time` (in harden plan), plus `file` (size/type message), `acceptance`, `quiz`, and min/max-length for `text`/`textarea`. Each is a `case` in the loader switch + a panel row.
- **M — Conditional "required-if" messages** — message shown only when another field has a value (pairs well with CF7 conditional-fields setups).

## Editor UX
- **M — Live message preview** in the panel (`admin/views/panel.php`) — show how the message renders as the admin types; reduces guesswork.
- **M — Placeholder tokens** in messages: `{field_label}`, `{min}`, `{max}` expanded at render. Small parser in `class-vmcf7-loader.php` message output.
- **M — Copy messages between forms / bulk apply.** Import-export a form's message set, or apply a default template to all forms. Builds on the post-meta storage (so it's just meta copy).
- **L — Editor lint:** warn on empty / overly long / disallowed-HTML messages before save (`class-vmcf7-admin.php:save_messages`).

## Frontend & accessibility
- **H — Client-side (inline) messages mirroring the server messages.** Currently validation is server-side only; emitting the same custom copy via CF7's JS validation gives instant feedback. New small `admin/js` → front `assets/js`.
- **M — A11y wiring:** ensure custom error text is associated via `aria-describedby` / announced in CF7's response region.

## Multilingual / integrations
- **M — WPML / Polylang compatibility** for message strings, alongside the existing Flavor bridge (`class-vmcf7-flavor.php`), for users not on Flavor.
- **M — Translation import/export** (per-language message sets) — extends Flavor save/get (`flavor.php:190/126`).
- **L — Better AI-translate feedback** (in harden plan covers error states; future: per-field re-translate, glossary/terms lock).

## Power users / quality
- **M — PHPUnit suite** (in harden plan) — then extend to cover new validation rules as they land.
- **L — `vmcf7_debug` action** (in harden plan adds it for Reflection) reused as a general diagnostic hook for the validation path.

## Already covered by the harden plan
`time` field type, AI-translate error handling, Reflection debug logging, PENDING-translation handling, escaping/str_* modernization, PHP 8.0 / WP 6.0 floor, unit tests.
