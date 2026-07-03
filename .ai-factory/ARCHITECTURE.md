# Architecture — Validation Muse for Contact Form 7

## Directory Layout

```
validation-muse-for-contact-form-7.php  # Bootstrap: activation guard, CF7 check, init, missing-CF7 notice
includes/
  class-vmcf7-loader.php      # Core: registers validation filters + SWV schema injection, replaces error messages
  class-vmcf7-admin.php       # CF7 editor panel, message/rule save, rule sets import/export/copy/bulk-apply, Flavor AJAX
  class-vmcf7-flavor.php      # Static bridge to the Flavor translation plugin (detect, store, AI translate)
  class-vmcf7-rules.php       # Regex (ReDoS-hardened) and min/max length evaluators
  class-vmcf7-i18n-compat.php # WPML/Polylang string registration + translation for validation messages
  class-vmcf7-migrations.php  # One-time, self-guarded data migrations (legacy _cf7cv_ → _vmcf7_ meta prefix)
admin/
  views/panel.php             # Editor panel markup (rules, messages, templates, live preview, Flavor tabs)
  css/ js/                    # Editor assets (vmcf7-admin.css/js — toggles, AJAX, live preview, Flavor tabs)
assets/
  css/vmcf7-frontend.css      # Frontend stylesheet, enqueued only on pages/blocks containing a CF7 form
languages/                    # .pot + de/es/fr/pt_BR/ru/uk .po/.mo
tests/                        # PHPUnit + Brain Monkey, CF7 classes stubbed in bootstrap
docs/                         # translation-import-guide.md
readme.txt + readme-*.txt     # wp.org listing (multilingual readmes)
uninstall.php
```

OOP, six single-responsibility classes wired together by the loader. No framework, no runtime Composer dependencies (classmap autoload only).

## Key Components

**`VMCF7_Loader`** (validation engine — composition root for `includes/`)
- `init()` — requires the other five classes, runs `VMCF7_Migrations::maybe_run()`, initializes `VMCF7_I18n_Compat`, registers `wpcf7_validate_{tag}` / `{tag}*` filters at **priority 20** (after CF7 core and legacy SWV), registers `add_swv_rules` on `wpcf7_swv_create_schema` (priority 20), wires admin hooks (panel, save, rule-set AJAX endpoints) when `is_admin()`, else enqueues the frontend stylesheet conditionally; fires `do_action('vmcf7_loaded')`.
- `validate_field($result, $tag)` — the legacy-hook filter callback. Order: required-if → required → (non-empty value) regex rule → length rule → replace-already-invalid message → evaluate built-in formats when SWV didn't already flag the field. Expands placeholder tokens on every message via `expand_placeholders()`.
- `add_swv_rules($schema, $contact_form)` — **SWV-native path** (since 1.6.1): walks the SWV schema's existing rules and overrides their `error` (and `maxlength`'s `threshold`) via `set_swv_property()` (Reflection on the rule's protected `properties`), then adds new `minlength`/`maxlength` SWV rules for fields that have a custom length constraint but no matching SWV rule yet. This is what makes custom messages appear in CF7's own client-side JS validation with zero plugin-authored frontend validation logic.
- `replace_error()` / `set_swv_property()` — Reflection-based message/threshold overrides, since both `WPCF7_Validation::invalid_fields` and an SWV rule's `properties` are private/protected with no public setter.
- Helpers: `get_validation_tag_types()` (filterable via `vmcf7_validation_tag_types`), `get_posted_value()`, `get_field_value_by_name()` (for required-if companion lookups), `value_is_empty()`, `is_enabled($form_id)`, `get_custom_message($form_id,$field,$type)` (also runs `vmcf7_translate_message` and Flavor lookup), `normalize_field_name()`, `expand_placeholders()`, `get_field_label()` (parses the form template's `<label>` markup for `{field_label}`).

**`VMCF7_Rules`** (pure evaluators, no WordPress state)
- `is_valid_regex($pattern)` / `evaluate_regex($value, $pattern)` — wraps the admin-supplied pattern with `~…~u` delimiters; ReDoS-hardened by temporarily lowering `pcre.backtrack_limit`/`pcre.recursion_limit` around `preg_match`, restoring them afterward; failures fire `vmcf7_debug`.
- `evaluate_length($value, $min, $max)` — `mb_strlen`-based min/max check, returns `{valid, type}`.

**`VMCF7_Admin`** (editor UI + persistence)
- `add_panel()` (`wpcf7_editor_panels`) + `display_panel()` render the Custom Validation panel; `get_form_fields()` introspects the form's tags to build rule/message inputs.
- `save_messages()` (`wpcf7_save_contact_form`) persists messages and rules (`sanitize_rule_value()` per field type) to post meta; `save_flavor_translations()` persists per-language values.
- `enqueue_scripts()` loads assets on CF7 editor screens only; `ajax_ai_translate()` handles `wp_ajax_vmcf7_ai_translate` (gated on `VMCF7_Flavor::is_active()`).
- `ajax_export_rules()` / `ajax_import_rules()` — export a form's rule set as JSON / import a JSON rule set into a form.
- `ajax_copy_rules()` — copy one form's rule set onto another form.
- `ajax_bulk_apply()` — save the current form's rules as a reusable global default template, or apply the saved template to the current form (panel buttons: "Save as template" / "Apply template").
- `get_default_invalid_message($type)` supplies CF7-style default placeholder copy for the panel inputs.

**`VMCF7_I18n_Compat`** (WPML/Polylang bridge, independent of Flavor)
- `init()` — hooks `register_strings_on_save()` on `wpcf7_save_contact_form` and `translate_message()` on the `vmcf7_translate_message` filter.
- `register_strings_on_save()` — for every field/message-type combination on an enabled form, registers the string with WPML (`icl_register_string()` or the `wpml_register_string` action) and/or Polylang (`pll_register_string()`).
- `translate_message()` — resolves the active-language translation via `icl_t()` / `wpml_translate_string` or `pll__()`, falling back to the untranslated value.

**`VMCF7_Migrations`** (one-time upgrade housekeeping)
- `maybe_run()` — no-op once the autoloaded `vmcf7_migrated_cf7cv_prefix` option is set, so steady-state page loads pay no extra query.
- `migrate_legacy_prefix()` — bulk-renames `_cf7cv_*` post meta keys (used before the 1.2.0 rename) to `_vmcf7_*` via a single `UPDATE … LEFT JOIN` (skips posts that already have a current-prefix row for the same key), deletes any leftover legacy rows, invalidates the post-meta cache for affected posts, and fires `vmcf7_debug` with a summary count.

**`VMCF7_Flavor`** (optional multilingual bridge, all static)
- `is_active()`, `needs_translation()`, `get_current_language()`, `get_default_language()`, `get_target_languages()`.
- `field_key()`, `get_translation()`, `get_all_translations()`, `export_language_set()`, `import_language_set()`, `save_translation()`, `delete_translation()`.
- `ai_translate_form($form_id,$lang)`, `is_ai_available()` — drive one-click AI translation through Flavor.

## Data Flow

**Front-end validation (legacy hook path — always active):**
1. Visitor submits a CF7 form → CF7 runs its validators (legacy hooks, and SWV if the schema declares rules for the field).
2. At priority 20, `VMCF7_Loader::validate_field()` runs after CF7 core; evaluates required-if → required → regex → length → format checks; if invalid and a custom message exists, `replace_error()` swaps the message text (Reflection) or `$result->invalidate()` sets it fresh.
3. The custom (and, with Flavor/WPML/Polylang, language-specific) message is what the visitor sees server-side.

**Front-end validation (SWV-native path — since 1.6.1):**
1. On `wpcf7_swv_create_schema`, `VMCF7_Loader::add_swv_rules()` rewrites the schema's `required`/format/`maxlength` rule messages to the custom copy and adds `minlength`/`maxlength` rules for constraints without a matching SWV rule.
2. CF7's own frontend JS renders and evaluates this schema client-side — so the visitor sees the custom message instantly, before submission, with CF7's native accessibility wiring, and without any plugin-authored validation JS.

**Editor:**
1. Admin opens a CF7 form → `add_panel()` injects "Custom Validation"; `display_panel()` + `get_form_fields()` render rule/message inputs (with Flavor language tabs if active) and a live preview.
2. On save, `save_messages()` writes messages and rules to form post meta (`_vmcf7_{field}_{type}`); `save_flavor_translations()` writes per-language values; `VMCF7_I18n_Compat::register_strings_on_save()` registers the messages with WPML/Polylang.
3. Panel actions: "AI Translate" → `ajax_ai_translate()` → `VMCF7_Flavor::ai_translate_form()`; export/import/copy/bulk-apply → the corresponding `VMCF7_Admin::ajax_*` handler reads/writes the same post-meta rule set as JSON.

## Hooks & Extension Points

- **Consumes:** `wpcf7_validate_{tag}` / `{tag}*` (prio 20), `wpcf7_swv_create_schema` (prio 20), `wpcf7_editor_panels`, `wpcf7_save_contact_form`, `admin_enqueue_scripts`, `wpcf7_enqueue_scripts`, `wp_ajax_vmcf7_ai_translate` / `_export_rules` / `_import_rules` / `_copy_rules` / `_bulk_apply`, activation hook, `plugins_loaded`.
- **Provides:** `vmcf7_loaded` (action), `vmcf7_validation_tag_types` (filter), `vmcf7_translate_message` (filter), `vmcf7_debug` (action).

## Notes

- Hard dependency on Contact Form 7 (self-deactivates if missing). Soft dependency on Flavor (feature-detected at runtime, no overhead when absent); WPML/Polylang support is native and dependency-free (feature-detected via `function_exists`/`has_action`/`has_filter`).
- Messages and rules stored in post meta (`_vmcf7_*` prefix) → survive form duplication and CF7 import/export. Legacy `_cf7cv_*` rows are auto-migrated once, self-guarded.
- Reflection is used at two points — `WPCF7_Validation::invalid_fields` (legacy path) and an SWV rule's `properties` (native path) — because CF7 exposes no public setter for either; failures degrade gracefully (`vmcf7_debug` + optional `error_log` under `WP_DEBUG`), never fatal.
- No runtime Composer deps; release excludes dev/VCS/`.ai-factory*` via `.distignore`.
