# M2 — Release 1.6.0: functional feature expansion

**Created:** 2026-05-31
**Mode:** full · **Milestone:** M2 (after M1 ships)
**Branch:** feature/m2-features
**Executor:** Antigravity. **Reviewer:** Sonnet subagent. **Release/deploy:** Claude (post-review).
**Depends on:** `m1-php8-wp7-hardening.md` merged/released first (PHP 8.0 baseline + test harness in place).
**Source:** every item here is from `../ROADMAP.md`.

## Settings
- **Testing:** Yes — extend the M1 suite per new rule/type.
- **Logging:** `WP_DEBUG`-guarded for new validation paths.
- **Docs:** `readme.txt` new rules/types/integrations + FAQ. Stable tag/changelog = Claude at release.

## Goal
Grow Validation Muse from "custom messages" into a per-field validation toolkit: custom regex & length rules
with their own messages, many more field types, client-side mirroring, live preview, message tokens,
cross-form reuse, broader multilingual. Preserve CF7 6.x SWV compatibility and the Flavor bridge.

## Scope
### Files modified
- `includes/class-vmcf7-loader.php` — new field types (`file`/`acceptance`/`quiz`); custom regex + min/max-length rules; placeholder-token expansion in output.
- `includes/class-vmcf7-admin.php` — panel rows for rules/types; save/load regex+length rules; copy/import-export message sets; editor lint.
- `includes/class-vmcf7-flavor.php` — translation import/export hooks.
- `admin/views/panel.php` — live preview; new rule/type inputs.
- `admin/js/vmcf7-admin.js` — live-preview logic; copy-between-forms UI.
- `admin/css/vmcf7-admin.css` — styles for preview/new rows.
- `readme.txt` — document new rules/types/integrations.
### New files
- `includes/class-vmcf7-rules.php` — regex/length rule evaluation (shared server + client).
- `assets/js/vmcf7-frontend.js` — client-side inline messages mirroring server rules.
- `includes/class-vmcf7-i18n-compat.php` — WPML/Polylang string registration.
### Files NOT touched (Antigravity)
- Version number / `VMCF7_VERSION` / `Stable tag` / `== Changelog ==` — **Claude at release.**

## Work items
### 1. Validation engine (core)
- **Custom regex / min-max-length rule per field** (`class-vmcf7-rules.php` + loader `class-vmcf7-loader.php:118`): admin defines a regex (or min/max length) + its message; evaluated server-side after CF7 core, SWV-safe; persisted in post meta; panel inputs in admin/panel.
- **More field types** (`class-vmcf7-loader.php:118–135`): `file` (size/type), `acceptance`, `quiz`. Correct `wpcf7_is_*` helpers; keep `vmcf7_validation_tag_types` authoritative.
- **Conditional "required-if" message** — shown only when a named companion field has a value.
- **Placeholder tokens** `{field_label}`/`{min}`/`{max}` expanded at message render.
### 2. Frontend & a11y
- **Client-side inline messages** (`assets/js/vmcf7-frontend.js`): mirror server rules for instant feedback; enqueue only on pages with CF7 forms.
- **A11y**: associate error text via `aria-describedby` / CF7 response region.
### 3. Editor UX
- **Live preview** in panel (`panel.php` + `admin/js`).
- **Copy / import-export message+rule sets** (`class-vmcf7-admin.php`): export as JSON, import into another form, apply default template to all forms (post-meta copy).
- **Editor lint**: warn on empty / overly long / disallowed-HTML messages before `save_messages`.
### 4. Multilingual / integrations
- **WPML / Polylang compat** (`class-vmcf7-i18n-compat.php`): register message strings; no-op when absent.
- **Translation import/export** for Flavor language sets (`class-vmcf7-flavor.php:126/190`).
### 5. Tests
- Extend: regex/length rules, new types, token expansion, conditional required-if, import/export round-trip, i18n-compat no-op, client-side rule serialization.

## Constraints
- CF7 hard dep; Flavor + WPML/Polylang soft (feature-detected, zero overhead when absent). Messages stay post-meta.
- SWV priority-20 + Reflection path must keep working. Escaping/nonce/cap on every new admin surface (import/export, copy). 8.0 floor: no 8.1+-only syntax.

## Verification
- On CF7 6.x (SWV): custom required + every invalid type (incl. `file`/`acceptance`) + a custom regex rule override correctly server-side; client-side shows the same messages inline.
- Tokens expand; live preview renders; copy/import-export round-trips between two forms.
- WPML/Polylang registers strings when active, no-ops when not.
- `php -l` clean; `vendor/bin/phpunit` green.
