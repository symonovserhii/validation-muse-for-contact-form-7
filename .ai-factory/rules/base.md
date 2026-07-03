# Project Base Rules

> Auto-detected conventions from codebase analysis. Edit as needed.

## Naming Conventions

- Files: WordPress style — `class-vmcf7-{name}.php` for classes under `includes/`; kebab-case assets (`vmcf7-admin.css`, `vmcf7-admin.js`, `vmcf7-frontend.css`); views under `admin/views/{name}.php`.
- Variables & functions/methods: `snake_case`.
- Classes: `VMCF7_Upper_Snake_Case` (e.g. `VMCF7_Loader`, `VMCF7_I18n_Compat`, `VMCF7_Migrations`).
- Constants: plugin-level constants prefixed `VMCF7_` (`VMCF7_VERSION`, `VMCF7_PATH`, `VMCF7_URL`, `VMCF7_BASENAME`); class constants `UPPER_SNAKE` (`VMCF7_Migrations::LEGACY_PREFIX`).
- Prefixes everywhere: post meta `_vmcf7_{field}_{type}` (current) / `_cf7cv_*` (legacy, auto-migrated), hooks/actions `vmcf7_` (`vmcf7_loaded`, `vmcf7_debug`, `vmcf7_validation_tag_types`, `vmcf7_translate_message`), AJAX actions `vmcf7_` (`vmcf7_export_rules`, `vmcf7_ai_translate`, …), options `vmcf7_` (`vmcf7_version`, `vmcf7_migrated_cf7cv_prefix`), text domain `validation-muse-for-contact-form-7`.

## Module Structure

- All classes live flat under `includes/` (no subdirectories) and are Composer classmap-autoloaded; the plugin bootstrap (`validation-muse-for-contact-form-7.php`) only requires `class-vmcf7-loader.php` — `VMCF7_Loader::init()` is the composition root that requires the other five classes and wires them together.
- Load order inside `VMCF7_Loader::init()` matters: Flavor → Admin → Rules → I18n_Compat → Migrations are required first, migrations run once, then filters/hooks are registered.
- Extension points are plain WordPress filters/actions, not a registry object: `vmcf7_validation_tag_types` (add field types), `vmcf7_translate_message` (add a translation source), `vmcf7_debug` (observe internal diagnostics).
- Static, stateless bridges (`VMCF7_Flavor`) use `public static function` throughout — no instantiation; stateful collaborators (`VMCF7_Loader`, `VMCF7_Admin`, `VMCF7_I18n_Compat`) are instantiated once in `init()`.
- Pure evaluation logic with no WordPress state (`VMCF7_Rules`) is kept separate from the filter-registration/orchestration class (`VMCF7_Loader`) so it can be unit-tested without WordPress.

## Error Handling

- Reflection failures (both `WPCF7_Validation::invalid_fields` and an SWV rule's `properties`, since neither exposes a public setter) are caught narrowly (`catch ( \ReflectionException $e )`), fired as `vmcf7_debug`, and additionally `error_log`'d only under `WP_DEBUG` — never fatal.
- Guard clauses with early `return $result;` / `return $schema;` for precondition failures (form not enabled, field not found, dependency class missing) rather than nested conditionals.
- Regex evaluation treats a `false` `preg_match` result as "invalid" and reports via `vmcf7_debug`, never throws.
- Hard dependency (Contact Form 7 missing) is handled at plugin level via `deactivate_plugins()` + `wp_die()` on activation, and an `admin_notices` callback + early `return` in `vmcf7_init()` on every subsequent load — not an exception.

## Control Flow

- Prefer flat, readable control flow over deeply nested conditionals. Use guard clauses, early `return`/`continue`, small named helper methods, or explicit classification logic when they make the code easier to follow. Handle edge cases and irrelevant branches early so the main path stays visible.

## Logging

- `do_action( 'vmcf7_debug', $message )` is the single diagnostic hook — fired on Reflection failures, invalid/failing regex, and migration completion; the plugin itself has no logging sink, consumers hook `vmcf7_debug` to log/observe.
- Direct `error_log()` is used only as a `WP_DEBUG`-gated fallback alongside `vmcf7_debug`, never on its own.

## Security

- Every PHP file starts with `if ( ! defined( 'ABSPATH' ) ) { exit; }`.
- Regex rules are ReDoS-hardened: `pcre.backtrack_limit` and `pcre.recursion_limit` are lowered before `preg_match` and restored immediately after, in both `is_valid_regex()` and `evaluate_regex()`.
- Input is sanitized on save (`VMCF7_Admin::sanitize_rule_value()`, `sanitize_key()` for field/meta-key components); messages allow safe HTML only via `wp_kses_post()`; output is escaped (`esc_attr()`, `esc_html()`, `wp_kses()` with an explicit allowed-tags array).
- Legacy meta-key migration uses `$wpdb->prepare()` for every query, including the identifier-adjacent `LIKE`/`CONCAT`/`SUBSTRING` parameters, and is collision-safe (never overwrites an existing current-prefix row).
- AJAX handlers (`ajax_export_rules`, `ajax_import_rules`, `ajax_copy_rules`, `ajax_bulk_apply`, `ajax_ai_translate`) are nonce-protected and scoped to `is_admin()` registration.

## Testing

- PHPUnit 9.5 + Brain Monkey (`require-dev` only, no runtime dependency); CF7 core classes (`WPCF7_ContactForm`, `WPCF7_FormTag`, `WPCF7_Validation`) are hand-stubbed at the top of the test file rather than pulled from a real CF7 install, so tests run WordPress-free.
- `tests/bootstrap.php` defines the `VMCF7_*` plugin constants before requiring the Composer autoloader — keep it in sync when new plugin-level constants are added.
- Run: `composer` is configured with `phpunit/phpunit` and `brain/monkey` in `require-dev`; invoke via `vendor/bin/phpunit` (config: `phpunit.xml.dist`, single suite over `tests/`).
- No PHPCS/WPCS config is committed; formatting still follows WordPress conventions (tabs, docblocks, Yoda-adjacent style) by convention, not enforcement — verify manually or add `phpcs.xml.dist` if enforcement is desired.

## PHP Style

- PHP 8.0+ floor (declared in the plugin header); no constructor property promotion or typed properties in use yet — plain property declarations and method-level type checks (`is_array()`, `is_string()`) instead.
- WordPress-style formatting: tabs for indentation, space inside parentheses, full docblocks with `@since`, `@param`, `@return` on every method.
- Docblocks record the introducing version (`@since 1.6.0`, `@since 1.6.1`, `@since 1.6.2`) — preserve this when adding methods so the changelog/version history stays traceable from the code.
