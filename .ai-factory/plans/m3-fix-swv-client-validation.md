# M3 — Fix: client-side validation onto CF7 SWV (post-1.6.0 hotfix)

**Created:** 2026-06-01
**Mode:** full · **Type:** bugfix · **Targets:** 1.6.1
**Branch:** main (config `create_branches: false`)
**Executor:** Claude. **Verification:** real browser E2E on `crowdspace.test` stand + PHPUnit.
**Depends on:** 1.6.0 shipped (`m2-functional-features.md`).
**Source:** field bugs reported on the crowdspace.test stand (CF7 6.1.6).

## Settings
- **Testing:** Yes — browser E2E is mandatory this time (the gap that let these bugs ship); PHPUnit for server regressions.
- **Logging:** `WP_DEBUG`-guarded `vmcf7_debug` on the new Reflection path (mirror existing `replace_error`).
- **Docs:** `readme.txt` — correct the "Instant Client-Side Mirroring" claim; changelog/stable tag at release.

## Goal
Eliminate the duplicate / overwritten validation messages on the frontend by making **CF7's own SWV engine the
single source of truth** on the client. Our custom messages and length rules go *into the SWV schema*; our parallel
JS engine is removed. One engine → one tip per field → no duplication and no reset-to-default on re-validation.

## Root cause (confirmed live, CF7 6.1.6)
1. **Two engines.** `assets/js/vmcf7-frontend.js` (ours) and `window.swv` (CF7) each render a `.wpcf7-not-valid-tip`
   for the same field → two tips.
2. **Messages absent from the schema.** REST `/feedback/schema` returns only CF7 defaults ("The field is required."),
   so CF7's live re-validation on any `change` after the first submit (e.g. ticking a checkbox) overwrites our custom
   texts with the defaults. Our custom copy reaches the page only via our JS engine + server AJAX reflection.

## Scope
### Files modified
- `includes/class-vmcf7-loader.php` — add `wpcf7_swv_create_schema` (priority 20) hook; drop `wpcf7_form_elements`
  data-attribute injection (`inject_frontend_rules`); enqueue **CSS only** (no JS) on pages with a CF7 form.
- `admin/css/vmcf7-admin.css` — hide collapsible/conditional elements via CSS classes (not inline style).
- `admin/views/panel.php` — replace every `style="display:none;"` with a state class (CF7 6.x strips inline `style`).
- `admin/js/vmcf7-admin.js` — toggle the state class instead of `.show()/.hide()` (jQuery `.hide()` writes inline style too).
- `readme.txt` — adjust client-side mirroring wording (regex/required-if validate on submit).
### Files removed
- `assets/js/vmcf7-frontend.js` — the parallel client engine (incl. the temporary MutationObserver dedup crutch).
### Files kept untouched
- `includes/class-vmcf7-rules.php`, `class-vmcf7-admin.php`, `class-vmcf7-flavor.php`, `class-vmcf7-i18n-compat.php`,
  `admin/*`, `assets/css/vmcf7-frontend.css` (still styles CF7's native tip).
- Server-side `validate_field()` stays as-is (it already enforces regex/required-if/length + custom messages on submit).
- Version / `VMCF7_VERSION` / `Stable tag` / `== Changelog ==` — at release.

## Work items
### 1. SWV schema injection (`class-vmcf7-loader.php`)
- Register `add_filter( 'wpcf7_swv_create_schema', [ $this, 'add_swv_rules' ], 20, 2 )`.
- Guard: return early if SWV unavailable (`! class_exists('\\Contactable\\SWV\\Rule')`) or form not `_vmcf7_enabled`.
- Build `name → WPCF7_FormTag` map from `scan_form_tags()` (for `{field_label}` expansion).
- **Override** `error` on existing native rules via Reflection on the rule's protected `properties`
  (helper `set_swv_property($rule,$key,$value)`, same philosophy as `replace_error`):
  - `required` / `requiredfile` → custom **required** message.
  - `email`/`url`/`tel`/`number`/`date`/`time` → custom **invalid** message.
  - `maxlength` → custom **length** message + threshold when `max_length` configured.
- **Add** native rules via `wpcf7_swv_create_rule(...)` + `$schema->add_rule(...)`:
  - `minlength` (threshold + length message) when `min_length` configured.
  - `maxlength` when no native maxlength rule exists for the field but `max_length` configured.
- Expand `{field_label}` / `{min}` / `{max}` in every message before setting.
- Pull messages through `get_custom_message()` (keeps Flavor/WPML translation).

### 2. Remove parallel engine (`class-vmcf7-loader.php`)
- Delete the `wpcf7_form_elements` → `inject_frontend_rules` filter + method (no longer needed: client reads the schema).
- Replace `enqueue_assets()` JS+CSS with CSS-only enqueue; keep the `wpcf7_enqueue_scripts` conditional (form present).
- Delete `assets/js/vmcf7-frontend.js`.

### 3. Out of client scope (server-only, by design)
- `regex` and `required-if`: CF7 6.x exposes no SWV error class for custom client rule types, so they stay server-side
  (instant client feedback for the common rules required/email/length is preserved; regex/required-if report on submit).

### 4. Admin panel: stop relying on inline `style` (separate 1.6.0 regression)
- **Root cause (confirmed):** CF7 6.x sanitizes editor-panel HTML and strips inline `style` attributes (served HTML shows
  our elements with `class`/`id`/`data-*` intact but no `style`). Every `style="display:none;"` is therefore ignored, so
  the advanced Regex/Length/Required-If drawers, the hidden file input ("Browse…"), the "Source Form" select, lint
  warnings, and the preview card all render expanded → the panel looks broken.
- **Fix:** introduce state classes in `admin/css/vmcf7-admin.css` (e.g. `.vmcf7-advanced-row` hidden by default;
  `.vmcf7-advanced-row.is-open { display: table-row; }`; a generic `.vmcf7-hidden { display: none; }` for the file
  input / copy-select / sharing-notice / lint-warning / preview-card). Replace `style="display:none;"` in `panel.php`
  with these classes. In `admin/js/vmcf7-admin.js` toggle the class (`toggleClass('is-open')` / `removeClass('vmcf7-hidden')`)
  instead of `.show()/.hide()` (jQuery's `.show()` can't beat a stylesheet `display:none`, and `.hide()` re-introduces
  inline style). Keep `data-*`/`class`-based toggles (CF7 does not strip those).

## Constraints
- No reliance on CF7 internals beyond the documented `wpcf7_swv_create_schema` hook + Reflection on rule `properties`
  (consistent with the plugin's existing Reflection-based SWV message replacement).
- Degrade gracefully on pre-SWV CF7: hook no-ops, server-side `validate_field()` remains authoritative.

## Verification (E2E on crowdspace.test, form 155948 — mandatory)
1. REST `/wp-json/contact-form-7/v1/contact-forms/155948/feedback/schema` now carries the custom messages.
2. Submit empty → **exactly one** `.wpcf7-not-valid-tip` per field, each with the custom text.
3. Submit empty, then tick the consent checkbox → messages stay custom (no reset to "The field is required.").
4. Enter an invalid email → custom invalid-format message (single tip).
5. A field with regex / required-if → still enforced on submit with its custom message (server).
6. `composer`/PHPUnit suite green (server-side regressions).
7. Admin panel (form 155948 editor → Custom Validation): advanced drawers collapsed by default, "Rules" expands/collapses
   one field at a time; toolbar clean (no stray "Browse…"/"Source Form" until their trigger); lint/preview hidden until
   used. Re-check in the live editor (the served HTML strips inline `style`).

## Reference notes for executor (verified against CF7 6.1.6 on the stand)
SWV (server → schema → client):
- Schema is built on `do_action( 'wpcf7_swv_create_schema', $schema, $contact_form )` (`includes/swv/schema-holder.php`,
  cached in `get_schema()`); served to the client via REST `GET /contact-form-7/v1/contact-forms/{id}/feedback/schema`
  (returns `$schema->to_array()`); validated client-side by `window.swv.validate( schema, formData )`
  (`includes/swv/js/index.js`). CF7 re-validates on `change` after the first submit — that path is what overwrote our
  custom texts with schema defaults.
- `$schema` is a `WPCF7_SWV_Schema extends \Contactable\SWV\CompositeRule`.
  - **Add** a rule: `$schema->add_rule( wpcf7_swv_create_rule( 'minlength', array( 'field' => $name, 'threshold' => (string) $min, 'error' => $msg ) ) );`
    Native rule names: `required, requiredfile, email, url, tel, number, date, time, minlength, maxlength, minnumber, maxnumber, …` (`wpcf7_swv_available_rules()`).
  - **Override** an existing rule's message: iterate `foreach ( $schema->rules() as $rule )` (public generator yielding the
    rule objects). Identify with `$rule->to_array()['rule']` (name) + `$rule->get_property('field')`. There is no public
    setter, so set the protected `\Contactable\SWV\Rule::$properties` via Reflection (same approach as `replace_error()`):
    set `['error']` (and `['threshold']` for `maxlength`). Default text on the stand was "The field is required." /
    "The e-mail address entered is invalid." / "The field is too long."
  - Guard the whole hook: `if ( ! class_exists( '\\Contactable\\SWV\\Rule' ) ) return;` (pre-SWV CF7 → server-side only).
- **Why regex/required-if stay server-side:** custom client rule validators added to `window.swv.validators` must `throw`
  CF7's internal SWV error class, which is **not** exported — so a custom client rule cannot signal failure cleanly. A
  custom rule name simply absent from `window.swv.validators` is skipped on the client (no error) and still enforced
  server-side, which is acceptable. Server enforcement already exists in `validate_field()`.

Admin inline-`style` stripping:
- The CF7 6.x editor renders panels through `WPCF7_HTMLFormatter` (`admin/includes/editor.php:73` →
  `includes/html-formatter.php`). The served editor HTML for our panel keeps `class`/`id`/`data-*` but has **all inline
  `style` attributes removed** (verified: 0 `style=` on `vmcf7-*` elements in the response body). So never rely on
  `style="display:none;"` — use CSS classes and toggle them in JS.

## Handoff
- Executor: **Antigravity**, driven by this file. Reviewer: Sonnet. Release/version bump/changelog/SVN sync: Claude (post-review).
- Working tree baseline = released 1.6.0 (exploratory edits reverted); only this plan file is new/uncommitted.

## Follow-up (separate plan, not in scope)
- None outstanding — both reported regressions (frontend duplication, admin layout) are covered above.
