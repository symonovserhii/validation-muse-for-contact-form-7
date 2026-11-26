=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact-form-7, cf7, validation, error-message, multilingual
Requires at least: 6.0
Tested up to: 7.2
Requires PHP: 8.0
Stable tag: 1.8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Eigene Validierungsregeln & -meldungen für Contact Form 7 — Regex, Länge, Required-If, pro Feld, kompatibel mit CF7 6.x SWV, mehrsprachig.

== Description ==

**Validation Muse** ermöglicht Ihnen, eigene Validierungsregeln und Fehlermeldungen für jedes Contact-Form-7-Feld zu schreiben — direkt im Formular-Editor, pro Formular und pro Feld. Kein Code, keine globale Einstellungsseite, keine JavaScript-Hacks.

Die meisten CF7-Validierungs-Plugins funktionierten nicht mehr, als Contact Form 7 6.x **Schema-based Validation (SWV)** einführte. Validation Muse hängt seine Filter mit Priorität 20 ein (nach dem CF7-Core) und ersetzt SWV-Fehlertexte für bereits invalidierte Felder mittels Reflection — deine eigenen Texte erscheinen also auch mit der neuen Validierungs-Engine.

= Warum Validation Muse =

* **CF7-6.x-SWV-kompatibel** — funktioniert mit der neuen Schema-based Validation, nicht nur mit Legacy-Hooks.
* **Eigene Regex- und Längenregeln** — eigene reguläre Ausdrücke, Mindest- und Maximallänge pro Feld definieren, jeweils mit eigener Fehlermeldung.
* **Bedingte "Pflicht-wenn"-Regeln** — ein Feld wird nur dann pflicht, wenn ein Begleitfeld ausgefüllt/angehakt ist.
* **Client-seitige SWV-Integration** — Standard-Validierungsregeln (required, email, length) werden in die native SWV-Engine von CF7 eingespeist, für sofortiges Frontend-Feedback mit voller Barrierefreiheit (A11y).
* **Regelsätze Import/Export & Vorlagen** — Validierungsregeln als JSON exportieren/importieren, von anderen Formularen kopieren und globale Vorlagen bulk-anwenden.
* **Platzhalter-Tokens** — verwende `{field_label}`, `{min}` und `{max}` in Validierungsmeldungen, um dynamische Texte zu erzeugen.
* **Pro Formular, pro Feld** — jedes Formular hat seine eigenen Meldungen; keine globale Überschreibung.
* **In Post-Meta gespeichert** — Meldungen leben mit dem Formular, kompatibel mit CF7-Formular-Duplikation und Drittanbieter-Import/Export-Plugins.
* **Mehrsprachig via WPML, Polylang & Flavor** — übersetzt Regeln über die Standard-WPML/Polylang-Hooks, und zeigt Sprach-Tabs + One-Click-AI-Translate-Button, wenn Flavor aktiv ist.
* **Entwicklerfreundlich** — die Erweiterungs-Hooks `vmcf7_loaded` und `vmcf7_validation_tag_types` ermöglichen eigene Feldtypen; mit den Filtern `vmcf7_message`, `vmcf7_default_message`, `vmcf7_placeholders` und `vmcf7_field_label` kannst du Meldungen auslesen, websiteweite Standardmeldungen festlegen und eigene `{tokens}` hinzufügen.
* **Schlank** — kein Admin-Bloat, kein Tracking, kein Upselling.

= Unterstützte Feldtypen =

* Pflichtfeld-Meldungen: jeder required-Tag (`text`, `textarea`, `select`, `checkbox`, `radio`, `file` usw.).
* Format-Meldungen: `email`, `url`, `tel`, `number` (inkl. `range`), `date` und `time`.
* Eigene Fehlermeldungen bei Validierungsfehlern: `file` (Größen-/Typ-Prüfung), `acceptance` (nicht akzeptierter Zustand) und `quiz` (falsche Antwort).
* HTML in Meldungen ist erlaubt und wird über `wp_kses_post()` bereinigt.
* Eigene Regex- und Min-/Max-Längenregeln sind für jeden Feldtyp außer `file` verfügbar.

= Übersetzungen =

Das Plugin liefert eine `.pot`-Datei und ist bereits ins Deutsche, Spanische, Französische, Italienische, Japanische, Polnische, brasilianische Portugiesisch, Russische und Ukrainische übersetzt. [Hilf mit, es in deine Sprache zu übersetzen.](https://translate.wordpress.org/projects/wp-plugins/validation-muse-for-contact-form-7)

== Installation ==

1. Über **Plugins → Installieren** nach *Validation Muse for Contact Form 7* suchen oder den Ordner `validation-muse-for-contact-form-7` nach `/wp-content/plugins/` hochladen.
2. Plugin aktivieren. Contact Form 7 muss bereits aktiv sein — Validation Muse deaktiviert sich selbst mit einer Admin-Notiz, wenn CF7 fehlt.
3. Beliebiges Contact-Form-7-Formular bearbeiten, den Tab **Benutzerdefinierte Validierung** öffnen, die Validierung aktivieren und Meldungen schreiben.
4. (Optional) Flavor-Übersetzungs-Plugin installieren, um Meldungen pro Sprache mit AI-Unterstützung zu übersetzen.

== Frequently Asked Questions ==

= Funktioniert das mit Contact Form 7 6.x und Schema-based Validation (SWV)? =

Ja. Seit Version 1.3.0 hängt sich Validation Muse mit Priorität 20 ein (nach dem CF7-Core) und ersetzt SWV-Fehlertexte für bereits invalidierte Felder via Reflection. Seit 1.6.1 werden Standard-Regeln (required, email/url/tel/number/date/time, Min-/Max-Länge) zusätzlich direkt in CF7s eigenes SWV-Schema eingespeist, sodass die clientseitige (Frontend-JS) Validierung deine eigenen Meldungen sofort zeigt — über CF7s native Validierungs-Engine, ohne zusätzliches Skript dieses Plugins.

= Wie unterscheidet sich das von anderen CF7-Validierungs-Plugins? =

Validation Muse ist das einzige CF7-Validierungs-Plugin, das (1) ab Werk mit CF7 6.x SWV kompatibel ist — sowohl serverseitig als auch in CF7s eigener clientseitiger Validierung, (2) Meldungen im Post-Meta des Formulars speichert, sodass sie mit dem Formular leben (kompatibel mit CF7-Duplikation und Import/Export-Plugins), und (3) Meldungen pro Sprache über WPML, Polylang oder das Flavor-Übersetzungs-Plugin (mit One-Click-AI-Übersetzung) unterstützt.

= Kann ich Validierungsmeldungen pro Sprache übersetzen? =

Ja. WPML (String Translation) und Polylang werden nativ unterstützt: Deine Meldungen werden bei jedem Speichern des Formulars zur Übersetzung registriert, und der Tab **Benutzerdefinierte Validierung** verlinkt auf die Seite, auf der du sie übersetzt. Mit dem Flavor-Übersetzungs-Plugin zeigt Validation Muse außerdem Sprach-Tabs im Formular-Editor sowie einen *AI-Translate*-Button; diese Übersetzungen werden in der Flavor-Datenbank gespeichert, und die Deinstallation von Validation Muse räumt sie auf.

= Welche Feldtypen unterstützen eigene Fehlermeldungen bei ungültigem Format oder Validierungsfehler? =

* `email`, `url`, `tel`, `number` (inkl. `range`), `date` und `time` unterstützen eigene Format-Fehlermeldungen.
* `file` unterstützt eigene Fehlermeldungen zur Datei-Größen-/Typ-Validierung.
* `acceptance` unterstützt eigene Fehlermeldungen, wenn die Checkbox nicht akzeptiert wird.
* `quiz` unterstützt eigene Fehlermeldungen, wenn die Antwort falsch ist.
* Jedes Pflichtfeld jedes Typs kann eine eigene Pflichtfeld-Meldung haben.

= Wo werden die Meldungen gespeichert? =

Im Post-Meta des jeweiligen Formulars. Sie leben mit dem Formular, deshalb behält das Duplizieren eines Formulars (CF7-Standardfunktion) die Meldungen. CF7 hat keinen nativen Export, aber Drittanbieter-Import/Export-Plugins lesen Post-Meta — Migrationen zwischen Sites funktionieren also ohne separaten Importschritt.

= Kann ich HTML in Validierungsmeldungen verwenden? =

Ja, einfaches HTML ist erlaubt und wird über `wp_kses_post()` bereinigt.

= Benötigt das Plugin Contact Form 7? =

Ja. CF7 muss installiert und aktiv sein. Das Plugin zeigt eine Admin-Notiz und deaktiviert sich selbst, wenn CF7 fehlt.

= Gibt es eine Einstellungsseite? =

Nein. Die Konfiguration lebt in jedem Formular im Tab **Benutzerdefinierte Validierung**. Es gibt absichtlich keine globale Einstellungsseite — jedes Formular behält seine eigenen Meldungen.

= Trackt oder sendet das Plugin Daten? =

Nein. Validation Muse stellt keine externen Anfragen. Der optionale AI-Translate-Button (Flavor-Integration) nutzt den von Flavor selbst konfigurierten Provider.

== Screenshots ==

1. Der Tab **Benutzerdefinierte Validierung** im Contact-Form-7-Editor — eine Karte pro Feld mit Meldungen, zusätzlichen Regeln (Länge, Muster mit Live-Test, nur manchmal erforderlich) und einer Vorschau dessen, was Besucher sehen werden.
2. **Kopieren, sichern und wiederverwenden** — Regeln aus einem anderen Formular kopieren, als Datei herunterladen/laden, eine Vorlage aufbewahren oder für alle Formulare verwenden.
3. Pflichtfeld-Meldung im Frontend gerendert.
4. Format-Fehlermeldung für ein E-Mail-Feld im Frontend gerendert.

== Changelog ==

= 1.8.0 =
* Neu: Der Tab „Benutzerdefinierte Validierung“ wurde neu gestaltet. Jedes Feld ist eine Karte mit Beschriftung, Typ und dem Hinweis, ob es angepasst ist; zusätzliche Regeln (Länge, Muster, nur manchmal erforderlich) liegen hinter einem einzigen Schalter „Zusätzliche Regeln“; jede Karte zeigt, was Besucher sehen werden. Suche, „Nur angepasste Felder anzeigen“ und „Alle öffnen“/„Alle schließen“ helfen bei langen Formularen.
* Neu: fertige Musterbeispiele und ein Feld „Ausprobieren: Gib eine Beispielantwort ein“, das ein Muster genauso prüft wie die Website.
* Neu: Die Werkzeuge zum Kopieren, Sichern, für die Vorlage und „Für alle Formulare verwenden“ sind gruppiert und erklärt, fragen direkt an Ort und Stelle nach einer Bestätigung und zeigen das Ergebnis im Panel statt in Browser-Pop-ups.
* Neu: Wenn WPML String Translation oder Polylang aktiv ist, verlinkt der Tab direkt auf die Seite, auf der die Meldungen übersetzt werden.
* Neu: eigene Meldung „falsche Antwort“ für Quiz-Felder und „falsche Datei“ für Datei-Felder.
* Neu: `{min}` und `{max}` funktionieren in jeder Meldung; Entwickler-Filter `vmcf7_default_message`, `vmcf7_placeholders`, `vmcf7_field_label`, `vmcf7_supports_text_rules` und `vmcf7_swv_invalid_rules`.
* Neu: Das Aussehen der Fehlermeldungen im Frontend lässt sich über CSS Custom Properties ändern (`--vmcf7-error-color`, `--vmcf7-error-font-size` und weitere).
* Neu: Übersetzungen ins Japanische, Italienische und Polnische.
* Fix: Der Contact-Form-7-Editor fragt nicht mehr „Website verlassen? Deine Änderungen werden eventuell nicht gespeichert“, wenn nichts geändert wurde.
* Fix: Eigene Meldungen und Längenbegrenzungen erreichen wieder die clientseitige Validierung von Contact Form 7 6.2+ (der Schema-Hook tat unter 6.2 stillschweigend nichts).
* Fix: Backslashes wurden aus gespeicherten Mustern und Meldungen entfernt (`^\d+$` wurde als `^d+$` gespeichert), auch bei Import, Kopieren und „Für alle Formulare verwenden“.
* Fix: Ein ungültiges Muster lehnt nicht mehr jede Antwort ab; eine Muster- oder Längenregel auf Checkboxen oder Mehrfachauswahlen verursacht beim Absenden keinen Fehler mehr.
* Fix: Das Kopieren der Regeln eines Formulars auf dasselbe Formular löscht sie nicht mehr; die Regelsatz-Werkzeuge akzeptieren nur Contact-Form-7-Formulare, die der Benutzer bearbeiten darf, und Importdateien werden geprüft und in der Größe begrenzt.
* Fix: WPML/Polylang erhielten jede Meldung einen Speichervorgang zu spät, Polylang listete die Meldungen auf seiner Übersetzungen-Seite zunächst gar nicht und dann doppelt auf.
* Fix: Die eigene Pflichtfeld-Meldung gilt jetzt auch für Acceptance-Checkboxen; „Pflicht-wenn“ funktioniert auch, wenn der Name des anderen Feldes Großbuchstaben enthält.
* Fix: AI Translate sendet keine Muster und Zahlen mehr an den Übersetzer.
* Fix: Die Deinstallation entfernt alle Plugin-Optionen; Frontend-Styles werden auch für Formulare geladen, die in Widgets und Templates platziert sind.

= 1.7.2 =
* Fix: Das Öffnen von Formulare → Erstellen bei aktivem Flavor bricht nicht mehr mit einem fatalen Fehler ab. Ein noch nicht gespeichertes Formular hat keine ID, und Validation Muse überspringt dessen Übersetzungen nun, bis das Formular gespeichert ist.

= 1.7.1 =
* Fix: Das Archiv, das GitHub und Composer aus einem Tag bauen, enthält keine Tests, Entwicklungswerkzeuge und Dokumentation mehr, sodass ein Projekt, das das Plugin aus Git installiert, nur das Plugin erhält. Keine Änderung am Verhalten des Plugins.

= 1.7.0 =
* Neu: Der Filter `vmcf7_message` gibt eine hier geschriebene Meldung an anderen Code weiter: `apply_filters( 'vmcf7_message', $default, $form_id, $field_name, $type )` liefert die eigene Meldung oder `$default`, wenn keine geschrieben wurde oder die benutzerdefinierte Validierung für das Formular ausgeschaltet ist. Für Besucher ändert sich nichts, solange kein anderer Code danach fragt.

= 1.6.5 =
* Neu: eine einmalige, ausblendbare Bitte um eine Bewertung auf WordPress.org. Sie erscheint erst eine Woche, nachdem du zum ersten Mal eine eigene Validierungsmeldung gespeichert hast, nur für Benutzer, die Contact-Form-7-Formulare bearbeiten dürfen, und nur auf den Contact-Form-7-Seiten. „Vielleicht später“ pausiert sie für 30 Tage, „Nicht mehr fragen“ blendet sie dauerhaft aus. Es wird nichts irgendwohin gesendet.

= 1.6.4 =
* Getestet mit WordPress 7.1 (verifiziert auf 7.1.1 mit Contact Form 7 6.1).
* Fix: In PHP 8.5 als veraltet markierte Aufrufe entfernt (`finfo_close()` sowie `ReflectionProperty::setAccessible()` ab PHP 8.1), sodass das Plugin unter PHP 8.5 keine Deprecation-Hinweise mehr auslöst.

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
* Neu: Validierungsmeldungen für `file`-, `acceptance`- und `quiz`-Felder; bedingte "Pflicht-wenn"-Meldungen.
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
* Null Overhead, wenn Flavor nicht installiert ist — alle Aufrufe hinter class_exists()-Prüfungen
* Flavor-Übersetzungen werden bei Plugin-Deinstallation aufgeräumt

= 1.3.0 =
* Kompatibilität mit Contact Form 7 6.x SWV (Schema-based Validation) behoben
* Validierungsfilter laufen nun mit Priorität 20 (nach dem CF7-Core), um SWV-Fehlermeldungen zu ersetzen
* Reflection-basierter Fehlerersatz für bereits invalidierte Felder hinzugefügt
* Eigene Meldungen überschreiben nun korrekt den Standardtext "The field is required." von CF7

= 1.2.1 =
* Variablennamen-Fehler behoben, der den Fehler "No required fields" verursachte

= 1.2.0 =
* Codebasis refaktoriert, um den WordPress Coding Standards zu folgen
* Dateistruktur für bessere Wartbarkeit reorganisiert
* PHPDoc-Blöcke zu allen Funktionen und Methoden hinzugefügt
* Barrierefreiheit mit ARIA-Labels verbessert
* JavaScript-Präfix-Inkonsistenz behoben
* Deinstallationsskript korrigiert, um das richtige Meta-Präfix zu verwenden
* Erweiterungs-Hooks hinzugefügt (`vmcf7_loaded`, `vmcf7_validation_tag_types`)
* Berechtigungsprüfung von `manage_options` auf `wpcf7_edit_contact_forms` geändert
* POT-Dateiname an Text-Domain angepasst

= 1.1.2 =
* Plugin-Name geändert.

= 1.1.1 =
* Datei .gitignore hinzugefügt.

= 1.1.0 =
* WordPress-Repository-Begleitmaterial hinzugefügt (Readme, Lizenz, POT-Datei).
* Validierungs-Hooks überarbeitet, um Pflicht- und Ungültig-Meldungen ohne AJAX-Filter zu überschreiben.
* Sanitisierung, Text-Domain-Ladung und Deinstallations-Bereinigung für Release-Reife gehärtet.

= 1.0.1 =
* Erste öffentliche Iteration, im Projekt gebündelt.

== Upgrade Notice ==

= 1.8.0 =
Neu gestalteter Tab „Benutzerdefinierte Validierung“, clientseitige Meldungen funktionieren wieder mit Contact Form 7 6.2+, dazu mehrere Validierungs-Fixes. Bestehende Regeln und Meldungen bleiben unverändert erhalten.

= 1.7.2 =
Behebt einen fatalen Fehler unter Formulare → Erstellen bei aktivem Flavor. Keine Änderungen am Validierungsverhalten.

= 1.7.1 =
Nur Paketierung: Das Git-Archiv enthält keine Tests und Werkzeuge mehr. Keine Änderungen am Validierungsverhalten.

= 1.7.0 =
Fügt den Filter `vmcf7_message` hinzu, über den anderer Code die hier geschriebenen Meldungen lesen kann. Keine Änderungen am Validierungsverhalten.

= 1.6.5 =
Fügt eine einmalige, ausblendbare Bewertungsanfrage hinzu (erscheint eine Woche, nachdem du zum ersten Mal eine eigene Meldung gespeichert hast). Keine Änderungen am Validierungsverhalten.

= 1.6.4 =
Kompatibilitäts-Release: getestet mit WordPress 7.1 und sauber unter PHP 8.5. Keine Verhaltensänderungen.

= 1.6.3 =
Dokumentations- und Übersetzungs-Release — keine funktionalen Änderungen oder Verhaltensänderungen. Aktualisiert vollständig die übersetzten Readmes und den .pot/.po/.mo-Übersetzungskatalog.

= 1.6.2 =
Stellt Validierungsmeldungen wieder her, die von Versionen vor 1.2.0 gespeichert wurden (altes `_cf7cv_`-Meta-Präfix), über eine einmalige, automatische Migration. Sicher, keine Aktion nötig.

= 1.6.1 =
Behebt doppelte/überschriebene Validierungsmeldungen im Frontend sowie Layout-Regressionen im Admin-Panel aus 1.6.0. Empfohlen für alle Nutzer von 1.6.0.

= 1.6.0 =
Fügt eigene Regex-/Längenregeln, weitere Feldtypen, „Pflicht-wenn"-Meldungen, clientseitige Spiegelung, Live-Vorschau, Regel-Import/Export/Vorlagen und WPML/Polylang-Unterstützung hinzu. Keine Datenmigration nötig.

= 1.5.0 =
Hebt das Mindest-PHP auf 8.0 und das Mindest-WordPress auf 6.0 an. Prüfe deine Hosting-Umgebung vor dem Update.

= 1.4.2 =
Plugin URI zeigt nun auf die dedizierte Landing-Page auf plugins.symonov.com. Keine Code-Änderungen.

= 1.4.1 =
Dokumentations-Release. Readme aufgefrischt für klarere Feature-Erkennbarkeit.

= 1.4.0 =
Fügt mehrsprachige Unterstützung via Flavor-Plugin und One-Click-AI-Übersetzung hinzu. Keine Datenmigration nötig.

= 1.3.0 =
Stellt Kompatibilität mit Contact Form 7 6.x Schema-based Validation (SWV) wieder her. Empfohlen für alle CF7-6.x-Nutzer.
