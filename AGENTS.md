# AGENTS.md

> Structural map for AI agents and new developers. Keep this in sync when the project layout changes.

## Project Overview

Validation Muse for Contact Form 7 — a WordPress plugin that lets site owners author custom validation rules (required, regex, min/max length, required-if) and error messages for every Contact Form 7 field, per form, per field, directly in the CF7 form editor. Its defining trait is native CF7 6.x Schema-based Validation (SWV) compatibility: custom messages are injected into CF7's own SWV schema so they render both server-side and in CF7's native client-side JS validation.

## Tech Stack

- **Programming language:** PHP 8.0+
- **Platform:** WordPress 6.0+ plugin; hard dependency on Contact Form 7 (self-deactivates if missing)
- **Multilingual:** native WPML/Polylang string registration + translation; optional deeper integration (language tabs, one-click AI translate) via the companion **Flavor** plugin, feature-detected at runtime
- **Storage:** WordPress post meta only (`_vmcf7_{field}_{type}` prefix on the CF7 form post; legacy `_cf7cv_*` auto-migrated once)
- **Build / dependencies:** Composer classmap-autoload only (`includes/`), no runtime dependencies; `require-dev`: PHPUnit 9.5 + Brain Monkey

## Project Structure

```
validation-muse-for-contact-form-7/
├── validation-muse-for-contact-form-7.php  # Bootstrap: constants, activation guard, CF7 check, plugins_loaded init
├── uninstall.php                           # Uninstall cleanup
├── includes/
│   ├── class-vmcf7-loader.php              # Composition root: registers legacy + SWV-native validation filters
│   ├── class-vmcf7-admin.php               # CF7 editor panel, save, rule-set import/export/copy/bulk-apply, AI translate AJAX
│   ├── class-vmcf7-rules.php               # Pure regex (ReDoS-hardened) + length evaluators, no WP state
│   ├── class-vmcf7-i18n-compat.php         # WPML/Polylang string registration + translation for messages
│   ├── class-vmcf7-migrations.php          # One-time _cf7cv_ → _vmcf7_ post-meta prefix migration
│   └── class-vmcf7-flavor.php              # Static bridge to the optional Flavor translation plugin
├── admin/
│   ├── views/panel.php                     # "Custom Validation" editor panel markup
│   ├── css/vmcf7-admin.css
│   └── js/vmcf7-admin.js                   # Toggles, rule-set AJAX, live preview, Flavor tabs, AI translate
├── assets/
│   └── css/vmcf7-frontend.css              # Enqueued only on pages/blocks containing a CF7 form
├── languages/                              # .pot + de/es/fr/pt_BR/ru/uk .po/.mo
├── tests/
│   ├── bootstrap.php                       # Defines VMCF7_* constants, requires Composer autoload
│   └── VMCF7Test.php                       # PHPUnit + Brain Monkey; CF7 core classes hand-stubbed
├── docs/
│   └── translation-import-guide.md
├── readme.txt + readme-*.txt               # wp.org listing (multilingual readmes)
├── README.md                               # GitHub landing page (mirrors wp.org listing)
├── AGENTS.md                               # This file
└── .ai-factory/
    ├── config.yaml
    ├── DESCRIPTION.md
    ├── ARCHITECTURE.md
    ├── ROADMAP.md
    ├── plans/
    └── rules/base.md
```

## Key Entry Points

| File | Purpose |
|------|---------|
| `validation-muse-for-contact-form-7.php` | Plugin header + constants (`VMCF7_VERSION`, `VMCF7_PATH`, `VMCF7_URL`, `VMCF7_BASENAME`); activation dependency check; `plugins_loaded` → requires and inits `VMCF7_Loader`; missing-CF7 admin notice. |
| `includes/class-vmcf7-loader.php` | Requires the other five classes, runs migrations, registers `wpcf7_validate_{tag}` filters (priority 20) and the `wpcf7_swv_create_schema` SWV-native injection, wires admin hooks. |
| `includes/class-vmcf7-admin.php` | Editor panel render/save; rule-set import/export/copy/bulk-apply AJAX handlers; AI-translate AJAX handler. |
| `includes/class-vmcf7-rules.php` | `evaluate_regex()` / `evaluate_length()` — the only pure, WordPress-free validation logic. |
| `includes/class-vmcf7-migrations.php` | `VMCF7_Migrations::maybe_run()` — self-guarded, one-time legacy meta-key migration. |
| `admin/views/panel.php` | Source of truth for which rule/message fields exist per type — cross-reference when adding a new field type or rule. |

## Documentation

| Document | Path | Description |
|----------|------|-------------|
| README | `README.md` | GitHub landing page, mirrors the wp.org listing |
| wp.org listing | `readme.txt` (+ `readme-*.txt`) | Canonical plugin description, changelog, FAQ — multilingual |
| Translation import guide | `docs/translation-import-guide.md` | How to import/export per-language message sets |

## AI Context Files

| File | Purpose |
|------|---------|
| `AGENTS.md` | Project map and entry points for AI agents. |
| `.ai-factory/DESCRIPTION.md` | Detailed project specification (features, stack, NFRs). |
| `.ai-factory/ARCHITECTURE.md` | Class-by-class architecture, data flow (legacy hook path + SWV-native path), hooks. |
| `.ai-factory/ROADMAP.md` | Functional backlog rationale; cross-reference against `.ai-factory/plans/` for what has already shipped. |
| `.ai-factory/rules/base.md` | Detected naming, structure, error-handling, and security conventions. |
| `.ai-factory/config.yaml` | AI Factory configuration (language, git workflow, paths). |

## Agent Rules

- Decompose shell commands instead of chaining with `&&` so each step can be reviewed independently.
  - Incorrect: `git checkout main && git pull`
  - Correct: first `git checkout main`, then `git pull origin main`.
- Every PHP file starts with `if ( ! defined( 'ABSPATH' ) ) { exit; }`.
- Sanitize input on save, escape on output, `wp_kses_post()` for message HTML, nonce every admin AJAX action.
- Prefix new post meta keys, hooks, and AJAX actions with `vmcf7_` / `_vmcf7_` to stay consistent with existing code; never reintroduce the legacy `_cf7cv_` prefix.
- New validation logic that doesn't need WordPress state belongs in `VMCF7_Rules` (pure, unit-testable) — not inline in `VMCF7_Loader`.
- Any new standard-rule type surfaced in the legacy hook path (`VMCF7_Loader::validate_field()`) should also be mirrored into the SWV-native path (`VMCF7_Loader::add_swv_rules()`) so client-side and server-side validation stay in sync.
- Reflection is the sanctioned way to override CF7's private `WPCF7_Validation::invalid_fields` and an SWV rule's protected `properties` — wrap in `try`/`catch ( \ReflectionException $e )`, fire `vmcf7_debug`, never let it fatal.
- Unit tests are WordPress-free (Brain Monkey); stub any new CF7 core class usage at the top of `tests/VMCF7Test.php` rather than requiring a real CF7 install.
