=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact-form-7, cf7, validation, error-message, multilingual
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.6.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Eigene Fehler- und Pflichtfeldmeldungen für Contact Form 7. Per Formular, per Feld, kompatibel mit CF7 6.x SWV, mehrsprachig.

== Description ==

**Validation Muse** ermöglicht eigene Fehlermeldungen für jedes Contact-Form-7-Feld — direkt im Formular-Editor, pro Formular und pro Feld. Kein Code, keine globale Einstellungsseite, keine JavaScript-Hacks.

Die meisten CF7-Validierungs-Plugins funktionierten nicht mehr, als Contact Form 7 6.x **Schema-based Validation (SWV)** einführte. Validation Muse hängt seine Filter mit Priorität 20 ein (nach dem CF7-Core) und ersetzt SWV-Fehlertexte für bereits invalidierte Felder mittels Reflection — Ihre eigenen Texte erscheinen also auch mit der neuen Validierungs-Engine.

= Warum Validation Muse =

* **CF7-6.x-SWV-kompatibel** — funktioniert mit der neuen Schema-based Validation, nicht nur mit Legacy-Hooks.
* **Eigene Regex- und Längenregeln** — eigene reguläre Ausdrücke, Mindest- und Maximallänge pro Feld definieren, jeweils mit eigener Fehlermeldung.
* **Bedingte „Pflicht-wenn"-Regeln** — ein Feld wird nur dann pflicht, wenn ein Begleitfeld ausgefüllt/angehakt ist.
* **Client-seitige SWV-Integration** — Standard-Validierungsregeln (required, email, length) werden in die native SWV-Engine von CF7 eingespeist, für sofortiges Frontend-Feedback mit voller Barrierefreiheit (A11y).
* **Regelsätze Import/Export & Vorlagen** — Validierungsregeln als JSON exportieren/importieren, von anderen Formularen kopieren und globale Vorlagen bulk-anwenden.
* **Platzhalter-Tokens** — `{field_label}`, `{min}` und `{max}` in Validierungsmeldungen verwenden, um dynamische Texte zu erzeugen.
* **Pro Formular, pro Feld** — jedes Formular hat seine eigenen Meldungen; keine globale Überschreibung.
* **In Post-Meta gespeichert** — Meldungen leben mit dem Formular, kompatibel mit CF7-Formular-Duplikation und Drittanbieter-Import/Export-Plugins.
* **Mehrsprachig via WPML, Polylang & Flavor** — übersetzt Regeln über die Standard-WPML/Polylang-Hooks, und zeigt Sprach-Tabs + One-Click-AI-Translate-Button, wenn Flavor aktiv ist.
* **Entwicklerfreundlich** — Erweiterungs-Hooks `vmcf7_loaded` und `vmcf7_validation_tag_types` ermöglichen eigene Feldtypen.
* **Schlank** — kein Admin-Bloat, kein Tracking, kein Upselling.

= Unterstützte Feldtypen =

* Pflichtfeld-Meldungen: jeder required-Tag (`text`, `textarea`, `select`, `checkbox`, `radio`, `file` usw.).
* Format-Meldungen: `email`, `url`, `tel`, `number` (inkl. `range`), `date` und `time`.
* Eigene Fehlermeldungen bei Validierungsfehlern: `file` (Größen-/Typ-Prüfung), `acceptance` (nicht akzeptierter Zustand) und `quiz` (falsche Antwort).
* HTML in Meldungen ist erlaubt und wird über `wp_kses_post()` bereinigt.
* Eigene Regex- und Min-/Max-Längenregeln funktionieren für alle Eingabefelder (außer acceptance und quiz).

= Übersetzungen =

Das Plugin liefert eine `.pot`-Datei und ist bereits ins Deutsche, Spanische, Französische, brasilianische Portugiesisch, Russische und Ukrainische übersetzt. [Übersetzen Sie es in Ihre Sprache.](https://translate.wordpress.org/projects/wp-plugins/validation-muse-for-contact-form-7)

== Installation ==

1. Über **Plugins → Installieren** nach *Validation Muse for Contact Form 7* suchen oder den Ordner `validation-muse-for-contact-form-7` nach `/wp-content/plugins/` hochladen.
2. Plugin aktivieren. Contact Form 7 muss bereits aktiv sein — Validation Muse deaktiviert sich selbst mit einer Admin-Notiz, wenn CF7 fehlt.
3. Beliebiges Contact-Form-7-Formular bearbeiten, das Panel **Custom Validation** öffnen, aktivieren und Meldungen schreiben.
4. (Optional) Flavor-Übersetzungs-Plugin installieren, um Meldungen pro Sprache mit AI-Unterstützung zu übersetzen.

== Frequently Asked Questions ==

= Funktioniert das mit Contact Form 7 6.x und Schema-based Validation (SWV)? =

Ja. Seit Version 1.3.0 hängt sich Validation Muse mit Priorität 20 ein (nach dem CF7-Core) und ersetzt SWV-Fehlertexte für bereits invalidierte Felder via Reflection. Seit 1.6.1 werden Standard-Regeln (required, email/url/tel/number/date/time, Min-/Max-Länge) zusätzlich direkt in CF7s eigenes SWV-Schema eingespeist, sodass die clientseitige (Frontend-JS) Validierung Ihre eigenen Meldungen sofort zeigt — über CF7s native Validierungs-Engine, ohne zusätzliches Skript dieses Plugins.

= Wie unterscheidet sich das von anderen CF7-Validierungs-Plugins? =

Validation Muse ist das einzige CF7-Validierungs-Plugin, das (1) ab Werk mit CF7 6.x SWV kompatibel ist — sowohl serverseitig als auch in CF7s eigener clientseitiger Validierung, (2) Meldungen im Post-Meta des Formulars speichert, sodass sie mit dem Formular leben (kompatibel mit CF7-Duplikation und Import/Export-Plugins), und (3) sich mit dem Flavor-Übersetzungs-Plugin für Meldungen pro Sprache mit One-Click-AI-Übersetzung integriert.

= Kann ich Validierungsmeldungen pro Sprache übersetzen? =

Ja — Flavor-Übersetzungs-Plugin installieren, und Validation Muse zeigt Sprach-Tabs im Formular-Editor sowie einen *AI-Translate*-Button. Übersetzungen werden in der Flavor-Datenbank gespeichert; Deinstallation von Validation Muse räumt sie auf.

= Welche Feldtypen unterstützen eigene Format-Fehlermeldungen? =

`email`, `url`, `tel`, `number` (inkl. `range`) und `date`. Jedes Pflichtfeld jedes Typs kann eine eigene Pflichtfeld-Meldung haben.

= Wo werden die Meldungen gespeichert? =

Im Post-Meta des jeweiligen Formulars. Sie leben mit dem Formular, deshalb behält das Duplizieren eines Formulars (CF7-Standardfunktion) die Meldungen. CF7 hat keinen nativen Export, aber Drittanbieter-Import/Export-Plugins lesen Post-Meta — Migrationen zwischen Sites funktionieren also ohne separaten Importschritt.

= Kann ich HTML in Validierungsmeldungen verwenden? =

Ja, einfaches HTML ist erlaubt und wird über `wp_kses_post()` bereinigt.

= Benötigt das Plugin Contact Form 7? =

Ja. CF7 muss installiert und aktiv sein. Das Plugin zeigt eine Admin-Notiz und deaktiviert sich selbst, wenn CF7 fehlt.

= Gibt es eine Einstellungsseite? =

Nein. Die Konfiguration lebt in jedem Formular im Panel **Custom Validation**. Es gibt absichtlich keine globale Einstellungsseite — jedes Formular behält seine eigenen Meldungen.

= Trackt oder sendet das Plugin Daten? =

Nein. Validation Muse stellt keine externen Anfragen. Der optionale AI-Translate-Button (Flavor-Integration) nutzt den von Flavor selbst konfigurierten Provider.

== Screenshots ==

1. Das Panel **Custom Validation** im Contact-Form-7-Editor — pro Formular aktivieren, pro Feld Meldungen schreiben.
2. Sprach-Tabs und der **AI-Translate**-Button (sichtbar, wenn das Flavor-Übersetzungs-Plugin aktiv ist).
3. Pflichtfeld-Meldung im Frontend gerendert.
4. Format-Fehlermeldung für ein E-Mail-Feld im Frontend gerendert.

== Changelog ==

= 1.6.3 =
* i18n: Die `.pot`-Übersetzungsvorlage vollständig neu generiert — der Katalog war seit 1.2.0 eingefroren und deckte nur etwa 20 % der übersetzbaren Strings des Plugins ab (Regex-/Längenregeln, Regelvorlagen, AI-Translate-Fehler und mehr waren zuvor nicht extrahierbar). Alle sechs ausgelieferten Sprachen (Deutsch, Spanisch, Französisch, Portugiesisch, Russisch, Ukrainisch) sind jetzt vollständig gegen die aktuelle Oberfläche übersetzt.
* Doku: alle übersetzten Readme-Dateien synchronisiert — Feature-Liste, unterstützte Feldtypen und FAQ spiegeln jetzt die Funktionalität von 1.6.0–1.6.2 wider (eigene Regex-/Längenregeln, Pflicht-wenn, native SWV-Client-Validierung, Regelvorlagen); ihre Changelog-/Upgrade-Notice-Abschnitte, die bisher bei 1.4.2 hängen geblieben waren, decken nun jede Version bis 1.6.2 ab.
* Fix: einen fehlenden `translators:`-Kommentar für einen Platzhalter-String hinzugefügt (i18n-Codequalität, keine Verhaltensänderung).

= 1.6.2 =
* Fix: Meldungen, die von Versionen vor der Plugin-Umbenennung gespeichert wurden (unter dem alten `_cf7cv_`-Meta-Präfix), waren für den aktuellen Code unsichtbar. Eine einmalige Migration benennt sie jetzt automatisch in das `_vmcf7_`-Präfix um und stellt verlorene Meldungen wieder her. Kollisionssicher, läuft nur einmal.

= 1.6.1 =
* Fix: doppelte/überschriebene Validierungsmeldungen im Frontend durch Integration mit der nativen SWV-Engine von CF7 behoben.
* Fix: Admin-Panel-Drawer und Vorschaukarten wurden aufgeklappt gerendert, weil der CF7-Editor Inline-Styles entfernt.

= 1.6.0 =
* Neu: eigene Regex- und Min-/Max-Längen-Validierungsregeln pro Feld, jeweils mit eigener Meldung.
* Neu: Validierungsmeldungen für `file`-, `acceptance`- und `quiz`-Felder; bedingte „Pflicht-wenn"-Meldungen.
* Neu: Platzhalter-Tokens `{field_label}`, `{min}`, `{max}` in Meldungen.
* Neu: clientseitige Inline-Validierung, die die Server-Meldungen spiegelt, mit `aria-describedby`-Barrierefreiheit.
* Neu: Live-Vorschau der Meldungen, Meldungen zwischen Formularen kopieren, JSON-Import/Export von Meldungssätzen und Editor-Lint.
* Neu: WPML/Polylang-Stringregistrierung neben der Flavor-Anbindung; Übersetzungs-Import/Export.

= 1.5.0 =
* Mindest-PHP auf 8.0 angehoben; Mindest-WordPress auf 6.0 angehoben; getestet bis WordPress 7.0.
* Neu: eigene Validierungsmeldungen für `time`-Felder.
* Klareres Fehlerfeedback bei AI Translate im Formular-Editor.
* Reflection-basierter SWV-Meldungsersatz protokolliert Fehler nun unter WP_DEBUG für einfachere Diagnose.
* Verbesserte Behandlung ausstehender Übersetzungen (kein stiller Fallback mehr) mit Per-Request-Caching.
* Code auf PHP-8-Idiome modernisiert; Output-Escaping gehärtet; erste Testsuite hinzugefügt.

= 1.4.2 =
* Plugin URI: zeigt nun auf die dedizierte Landing-Page unter https://plugins.symonov.com/validation-muse-for-cf7/
* Keine Code- oder Verhaltensänderungen

= 1.4.1 =
* Readme: USP-first-Neuschrift für SEO-Auffindbarkeit
* Tags: generische `messages`/`forms`/`customization` durch zielgerichtete `contact-form-7`, `cf7`, `validation`, `error-message`, `multilingual` ersetzt
* FAQ: Einträge zu CF7-6.x-SWV-Kompatibilität, Vergleich mit anderen CF7-Validierungs-Plugins und Mehrsprachigkeit via Flavor hinzugefügt

= 1.4.0 =
* Mehrsprachige Unterstützung über Flavor-Übersetzungs-Plugin-Integration hinzugefügt
* Validierungsmeldungen können nun pro Sprache im Formular-Editor übersetzt werden
* Sprach-Tabs erscheinen automatisch, wenn Flavor aktiv ist
* AI-Translate-Button für One-Click-Maschinenübersetzung aller Meldungen
* Übersetzungen in Flavor-Datenbank gespeichert — Plugin-Daten bleiben portabel
* Null Overhead, wenn Flavor nicht installiert ist — alle Aufrufe hinter `class_exists()`-Prüfungen
* Flavor-Übersetzungen werden bei Plugin-Deinstallation aufgeräumt

= 1.3.0 =
* Fixed compatibility with Contact Form 7 6.x SWV (Schema-based Validation)
* Validation filters now run at priority 20 (after CF7 core) to replace SWV error messages
* Added Reflection-based error replacement for already-invalidated fields
* Custom messages now correctly override default CF7 "The field is required." text

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

= 1.6.3 =
Dokumentations- und Übersetzungs-Release — keine funktionalen Änderungen oder Verhaltensänderungen. Aktualisiert vollständig die übersetzten Readmes und den .pot/.po/.mo-Übersetzungskatalog.

= 1.6.2 =
Stellt Validierungsmeldungen wieder her, die von Versionen vor 1.2.0 gespeichert wurden (altes `_cf7cv_`-Meta-Präfix), über eine einmalige, automatische Migration. Sicher, keine Aktion nötig.

= 1.6.1 =
Behebt doppelte/überschriebene Validierungsmeldungen im Frontend sowie Layout-Regressionen im Admin-Panel aus 1.6.0. Empfohlen für alle Nutzer von 1.6.0.

= 1.6.0 =
Fügt eigene Regex-/Längenregeln, weitere Feldtypen, „Pflicht-wenn"-Meldungen, clientseitige Spiegelung, Live-Vorschau, Regel-Import/Export/Vorlagen und WPML/Polylang-Unterstützung hinzu. Keine Datenmigration nötig.

= 1.5.0 =
Hebt das Mindest-PHP auf 8.0 und das Mindest-WordPress auf 6.0 an. Prüfen Sie Ihre Hosting-Umgebung vor dem Update.

= 1.4.2 =
Plugin URI zeigt nun auf die dedizierte Landing-Page auf plugins.symonov.com. Keine Code-Änderungen.

= 1.4.1 =
Dokumentations-Release. Readme aufgefrischt für klarere Feature-Erkennbarkeit.

= 1.4.0 =
Fügt mehrsprachige Unterstützung via Flavor-Plugin und One-Click-AI-Übersetzung hinzu. Keine Datenmigration nötig.

= 1.3.0 =
Stellt Kompatibilität mit Contact Form 7 6.x Schema-based Validation (SWV) wieder her. Empfohlen für alle CF7-6.x-Nutzer.
