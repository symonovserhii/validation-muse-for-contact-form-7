=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact form 7, validation, forms, messages, customization
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Fine-tune the validation copy for every Contact Form 7 field directly inside the form editor.

== Description ==

Validation Muse for Contact Form 7 lets you override the default error copy that Contact Form 7 displays. Enable the plugin per form, then supply custom text for required notices and invalid email, URL, telephone, number, range, and date validation. Messages are stored in post meta so they stay with the form when you export/import.

**Features:**

* Customize "required field" messages per field
* Customize "invalid format" messages for email, URL, phone, number, range, and date fields
* Enable/disable custom validation per form
* Messages stored in post meta for easy export/import
* Fully translatable with POT file included
* Accessible admin interface with ARIA labels

== Installation ==

1. Upload the `validation-muse-for-contact-form-7` folder to `/wp-content/plugins/`, or install it from the WordPress admin under Plugins → Add New.
2. Activate the plugin through the Plugins screen.
3. Open any Contact Form 7 form, switch to the **Custom Validation** panel, enable the feature, and save your messages.

== Frequently Asked Questions ==

= Does this plugin require Contact Form 7? =

Yes. Contact Form 7 must be installed and active. The plugin will display an admin notice and automatically deactivate itself if Contact Form 7 is missing.

= Which field types support custom invalid messages? =

Email, URL, telephone, number (including range), and date tags can display custom invalid-format text. Any required field can have a custom "required" message.

= Where are the messages stored? =

Messages are saved in each form's post meta. They are included in Contact Form 7 exports, so migrating a form will move the messages with it.

= Can I use HTML in validation messages? =

Yes, basic HTML is allowed and sanitized using `wp_kses_post()`.

== Screenshots ==

1. The Custom Validation panel in the Contact Form 7 editor
2. Example of custom validation messages on the frontend

== Changelog ==

= 1.2.1 =
* Fixed variable name mismatch causing "No required fields" error

= 1.2.0 =
* Refactored codebase to follow WordPress Coding Standards
* Reorganized file structure for better maintainability
* Added PHPDoc blocks to all functions and methods
* Improved accessibility with ARIA labels
* Fixed JavaScript prefix inconsistency
* Fixed uninstall script to use correct meta prefix
* Added extensibility hooks (`vmcf7_loaded`, `vmcf7_validation_tag_types`)
* Changed capability check from `manage_options` to `wpcf7_edit_contact_forms`
* Updated POT file name to match text domain

= 1.1.2 =
* Changed plugin name.

= 1.1.1 =
* Added .gitignore file.

= 1.1.0 =
* Added WordPress repository collateral (readme, license, POT file).
* Reworked validation hooks to override required and invalid messages without relying on AJAX filters.
* Hardened sanitization, text domain loading, and uninstall cleanup for release readiness.

= 1.0.1 =
* Initial public iteration bundled with the project.

== Upgrade Notice ==

= 1.2.0 =
This version includes significant code improvements for WordPress Coding Standards compliance and better accessibility. No data migration required.