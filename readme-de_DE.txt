=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact form 7, validation, forms, messages, customization
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Passen Sie die Validierungsmeldungen für jedes Contact Form 7 Feld direkt im Formular-Editor an.

== Description ==

Validation Muse for Contact Form 7 ermöglicht es Ihnen, die Standard-Fehlermeldungen von Contact Form 7 zu überschreiben. Aktivieren Sie das Plugin pro Formular und geben Sie benutzerdefinierte Texte für Pflichtfeld-Hinweise sowie E-Mail-, URL-, Telefon-, Zahlen-, Bereichs- und Datumsvalidierung an. Die Meldungen werden in Post-Meta gespeichert, sodass sie beim Export/Import mit dem Formular erhalten bleiben.

**Funktionen:**

* Anpassung von "Pflichtfeld"-Meldungen pro Feld
* Anpassung von "Ungültiges Format"-Meldungen für E-Mail-, URL-, Telefon-, Zahlen-, Bereichs- und Datumsfelder
* Aktivierung/Deaktivierung der benutzerdefinierten Validierung pro Formular
* Meldungen werden in Post-Meta für einfachen Export/Import gespeichert
* Vollständig übersetzbar mit enthaltener POT-Datei
* Barrierefreie Admin-Oberfläche mit ARIA-Labels

== Installation ==

1. Laden Sie den Ordner `validation-muse-for-contact-form-7` nach `/wp-content/plugins/` hoch oder installieren Sie es über den WordPress-Admin unter Plugins → Neu hinzufügen.
2. Aktivieren Sie das Plugin über die Plugins-Seite.
3. Öffnen Sie ein beliebiges Contact Form 7 Formular, wechseln Sie zum Panel **Benutzerdefinierte Validierung**, aktivieren Sie die Funktion und speichern Sie Ihre Meldungen.

== Frequently Asked Questions ==

= Benötigt dieses Plugin Contact Form 7? =

Ja. Contact Form 7 muss installiert und aktiv sein. Das Plugin zeigt einen Admin-Hinweis an und deaktiviert sich automatisch, wenn Contact Form 7 fehlt.

= Welche Feldtypen unterstützen benutzerdefinierte Ungültig-Meldungen? =

E-Mail-, URL-, Telefon-, Zahlen- (einschließlich Bereich) und Datumsfelder können benutzerdefinierten Text für ungültiges Format anzeigen. Jedes Pflichtfeld kann eine benutzerdefinierte "Pflichtfeld"-Meldung haben.

= Wo werden die Meldungen gespeichert? =

Meldungen werden im Post-Meta jedes Formulars gespeichert. Sie sind in Contact Form 7 Exporten enthalten, sodass beim Migrieren eines Formulars die Meldungen mitgenommen werden.

= Kann ich HTML in Validierungsmeldungen verwenden? =

Ja, einfaches HTML ist erlaubt und wird mit `wp_kses_post()` bereinigt.

== Screenshots ==

1. Das Panel für benutzerdefinierte Validierung im Contact Form 7 Editor
2. Beispiel für benutzerdefinierte Validierungsmeldungen im Frontend

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
