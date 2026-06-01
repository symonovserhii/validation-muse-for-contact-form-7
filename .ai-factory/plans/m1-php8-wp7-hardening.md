# M1 — Release 1.5.0: PHP 8.0 floor, WP 7.0, code-style & hardening

**Created:** 2026-05-31
**Mode:** full · **Milestone:** M1 (ship first; low risk)
**Branch:** feature/m1-php8-wp7-harden
**Executor:** Antigravity. **Reviewer:** Sonnet subagent. **Release/deploy:** Claude (post-review).
**Sibling:** M2 features → `m2-functional-features.md` (later release 1.6.0).

## Settings
- **Testing:** Yes — stand up PHPUnit + Brain Monkey with `WPCF7_*` stubs; M2 extends it.
- **Logging:** `WP_DEBUG`-guarded for Reflection/translation failures.
- **Docs:** `readme.txt` header lines + accurate supported-types list. Stable tag/changelog = Claude at release.
- **Roadmap linkage:** hardening + small fixes; feature ROADMAP is M2.

## Goal
Raise the floor to **PHP 8.0** (from 7.4) and **WP 6.0** (from 5.8), make "Tested up to: 7.0" truthful and
consistent, fix the two unescaped attributes and pre-8.0 `strpos` idioms, and land the small code-review
fixes (one new field type, AI-translate error feedback, Reflection diagnostics, PENDING handling) + a test
suite. Low-risk compatibility release. Bigger features are M2.

## Scope
### Files modified
- `validation-muse-for-contact-form-7.php` — header floors + add `Tested up to: 7.0`.
- `admin/views/panel.php` — `esc_attr()` on the two class-attribute ternaries.
- `includes/class-vmcf7-loader.php` — add `time` type; log caught `ReflectionException` under `WP_DEBUG`.
- `includes/class-vmcf7-admin.php` — `str_contains`; structured AI-translate AJAX errors.
- `includes/class-vmcf7-flavor.php` — `str_starts_with`; PENDING-translation handling/caching.
- `admin/js/vmcf7-admin.js` — differentiate AJAX error states.
- `uninstall.php` — `str_starts_with`.
- `readme.txt` — header lines + supported-types list.
- **New:** `composer.json` (dev), `phpunit.xml.dist`, `tests/bootstrap.php`, `tests/**`.
### Files NOT touched (Antigravity)
- Version number / `VMCF7_VERSION` / `Stable tag` / `== Changelog ==` — **Claude at release.**
- `.distignore` (verify excludes `tests`/`composer.*`/`vendor`), `svn/`.
- Anything in M2 (regex/length rules, file/acceptance/quiz types, client-side, preview, copy/import-export, WPML/Polylang).

## Work items
### 1. Versions & headers
- Docblock (`validation-muse-for-contact-form-7.php:1–16`): `Requires PHP 7.4→8.0`; `Requires at least 5.8→6.0`; **add** `Tested up to: 7.0`.
- `readme.txt`: `Requires PHP: 8.0`, `Requires at least: 6.0`, keep `Tested up to: 7.0`; remove stale `6.9.4`.
### 2. Code style / PHP 8.0
- **Escaping** `admin/views/panel.php:106,128` — wrap ternaries in `esc_attr()`.
- **str_\*** — `class-vmcf7-admin.php:42` → `! str_contains`; `class-vmcf7-flavor.php:169,246` → `str_starts_with`; `uninstall.php:33` → `! str_starts_with`. 8.0-safe; `php -l` clean.
### 3. Small functional / diagnostics (from code review)
- **`time` field type** `class-vmcf7-loader.php:118–135` — add to the invalid switch (verify the correct `wpcf7_is_*` helper exists). Update readme supported list to match.
- **AI-translate error handling** `class-vmcf7-admin.php:342–372` + `admin/js/vmcf7-admin.js:107–109` — structured empty/provider/network errors in the notice.
- **Reflection debug logging** `class-vmcf7-loader.php:159–180` — log swallowed `ReflectionException` under `WP_DEBUG` (or `do_action('vmcf7_debug', …)`).
- **PENDING translations** `class-vmcf7-flavor.php:142–146` — debug signal + per-request cache instead of silent English fallback.
### 4. Tests
- Unit: `validate_field` (required + each invalid type incl. new `time`), `replace_error` SWV path (mock Reflection), `get_custom_message`/`is_enabled`, `save_messages` sanitization, `VMCF7_Flavor` get/save/delete + `field_key`. Brain Monkey + `WPCF7_*` stubs.

## Constraints
- CF7 hard dep (self-deactivate if absent); Flavor soft (zero overhead when absent). Messages stay post-meta.
- SWV priority-20 + Reflection path must keep working. 8.0 floor: no 8.1+-only syntax.

## Verification
- `php -l` clean; `vendor/bin/phpunit` green.
- Grep: floors updated (PHP 8.0 / WP 6.0); `Tested up to: 7.0` in BOTH docblock and readme; no `strpos(` in the four flagged spots; no `6.9.4`.
- Manual: custom required + invalid (incl. `time`) on CF7 6.x (SWV) → custom copy overrides; AI Translate (Flavor active) → improved error feedback.
