# Architecture — Validation Muse for Contact Form 7

## Directory Layout

```
validation-muse-for-contact-form-7.php  # Bootstrap: activation guard, CF7 check, init, missing-CF7 notice
includes/
  class-vmcf7-loader.php   # Core: registers validation filters, replaces error messages (SWV via Reflection)
  class-vmcf7-admin.php    # CF7 editor panel, message save, Flavor translation save, AI-translate AJAX
  class-vmcf7-flavor.php   # Static bridge to the Flavor translation plugin (detect, store, AI translate)
admin/
  views/                   # Editor panel markup
  css/ js/                 # Editor assets
languages/                 # .pot + de/es/fr/pt_BR/ru/uk .po/.mo
readme.txt + readme-*.txt  # wp.org listing (multilingual readmes)
uninstall.php
```

OOP, three single-responsibility classes wired together by the loader.

## Key Components

**`VMCF7_Loader`** (validation engine)
- `init()` — registers `wpcf7_validate_{tag}` / `wpcf7_validate_{tag}*` filters at **priority 20**, plus admin hooks (panel, save, AJAX) when in admin; fires `do_action('vmcf7_loaded')`.
- `validate_field($result, $tag)` — the filter callback; looks up the custom message and applies it.
- `replace_error()` — uses Reflection to overwrite SWV error text on an already-invalidated field (the CF7 6.x compatibility trick).
- Helpers: `get_validation_tag_types()` (filterable via `vmcf7_validation_tag_types`), `get_posted_value`, `value_is_empty`, `is_enabled($form_id)`, `get_custom_message($form_id,$field,$type)`, `normalize_field_name`.

**`VMCF7_Admin`** (editor UI + persistence)
- `add_panel()` (`wpcf7_editor_panels`) + `display_panel()` render the Custom Validation panel.
- `get_form_fields()` introspects the form's tags to build the message inputs.
- `save_messages()` (`wpcf7_save_contact_form`) persists messages to post meta; `save_flavor_translations()` persists per-language values.
- `enqueue_scripts()` loads assets on CF7 editor screens only; `ajax_ai_translate()` handles `wp_ajax_vmcf7_ai_translate`.
- `get_default_invalid_message($type)` supplies CF7-style defaults.

**`VMCF7_Flavor`** (optional multilingual bridge, all static)
- `is_active()`, `needs_translation()`, `get_current_language()`, `get_default_language()`, `get_target_languages()`.
- `field_key()`, `get_translation()`, `get_all_translations()`, `save_translation()`, `delete_translation()`.
- `ai_translate_form($form_id,$lang)`, `is_ai_available()` — drive one-click AI translation through Flavor.

## Data Flow

**Front-end validation:**
1. Visitor submits a CF7 form → CF7 runs its validators (legacy + SWV).
2. At priority 20, `VMCF7_Loader::validate_field()` runs after CF7 core; if the field is invalid and a custom message exists, `replace_error()` swaps the message text (Reflection for SWV).
3. The custom (and, with Flavor, language-specific) message is what the visitor sees.

**Editor:**
1. Admin opens a CF7 form → `add_panel()` injects "Custom Validation"; `display_panel()` + `get_form_fields()` render inputs (with Flavor language tabs if active).
2. On save, `save_messages()` writes messages to form post meta; `save_flavor_translations()` writes per-language values.
3. "AI Translate" → `ajax_ai_translate()` → `VMCF7_Flavor::ai_translate_form()` fills target-language messages.

## Hooks & Extension Points

- **Consumes:** `wpcf7_validate_{tag}` / `{tag}*` (prio 20), `wpcf7_editor_panels`, `wpcf7_save_contact_form`, `admin_enqueue_scripts`, `wp_ajax_vmcf7_ai_translate`, activation hook.
- **Provides:** `vmcf7_loaded` (action), `vmcf7_validation_tag_types` (filter).

## Notes

- Hard dependency on Contact Form 7 (self-deactivates if missing). Soft dependency on Flavor (feature-detected at runtime, no overhead when absent).
- Messages stored in post meta → survive form duplication and CF7 import/export.
- No runtime Composer deps; release excludes dev/VCS/`.ai-factory*` via `.distignore`.
