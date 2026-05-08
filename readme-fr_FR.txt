=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact-form-7, cf7, validation, error-message, multilingual
Requires at least: 5.8
Tested up to: 6.9.4
Requires PHP: 7.4
Stable tag: 1.4.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Messages d'erreur et de champ requis personnalisés pour Contact Form 7. Par formulaire, par champ, compatible CF7 6.x SWV, multilingue.

== Description ==

**Validation Muse** vous permet d'écrire vos propres messages d'erreur pour chaque champ de Contact Form 7 — directement dans l'éditeur de formulaire, par formulaire et par champ. Sans code, sans page de réglages globale, sans bidouilles JavaScript.

La plupart des plugins de validation CF7 ont cessé de fonctionner lorsque Contact Form 7 6.x a introduit la **Schema-based Validation (SWV)**. Validation Muse exécute ses filtres en priorité 20 (après le cœur de CF7) et utilise Reflection pour remplacer le texte d'erreur SWV sur les champs déjà invalidés — votre copie personnalisée s'affiche donc réellement, même avec le nouveau moteur de validation.

= Pourquoi Validation Muse =

* **Compatible CF7 6.x SWV** — fonctionne avec le nouveau moteur Schema-based Validation, pas seulement avec les hooks legacy.
* **Par formulaire, par champ** — chaque formulaire conserve ses propres messages ; pas de surcharge globale.
* **Stocké en post meta** — les messages vivent avec le formulaire, compatibles avec la duplication de formulaire CF7 et les plugins d'import/export tiers.
* **Multilingue via Flavor** — quand le plugin de traduction Flavor est actif, les onglets de langue et un bouton AI Translate apparaissent automatiquement dans l'éditeur. Aucune surcharge si Flavor n'est pas installé.
* **Convivial pour les développeurs** — les hooks d'extensibilité `vmcf7_loaded` et `vmcf7_validation_tag_types` permettent d'ajouter des types de champs personnalisés.
* **Léger** — pas de surcharge admin, pas de tracking, pas d'upselling.

= Types de champs supportés =

* Messages de champ requis : tout tag required (text, textarea, select, checkbox, radio, file, etc.).
* Messages de format invalide : `email`, `url`, `tel`, `number` (y compris `range`) et `date`.
* Le HTML dans les messages est autorisé et nettoyé via `wp_kses_post()`.

= Traductions =

Le plugin est livré avec un fichier `.pot` et est déjà traduit en néerlandais, allemand, russe, espagnol (Chili/Espagne). [Aidez à le traduire dans votre langue.](https://translate.wordpress.org/projects/wp-plugins/validation-muse-for-contact-form-7)

== Installation ==

1. Installez depuis **Extensions → Ajouter** en cherchant *Validation Muse for Contact Form 7*, ou téléversez le dossier `validation-muse-for-contact-form-7` dans `/wp-content/plugins/`.
2. Activez l'extension. Contact Form 7 doit être déjà actif — Validation Muse se désactive automatiquement avec un avis admin si CF7 manque.
3. Modifiez n'importe quel formulaire Contact Form 7, ouvrez le panneau **Custom Validation**, activez-le et écrivez vos messages.
4. (Optionnel) Installez le plugin de traduction Flavor pour traduire les messages par langue avec assistance AI.

== Frequently Asked Questions ==

= Cela fonctionne-t-il avec Contact Form 7 6.x et Schema-based Validation (SWV) ? =

Oui. Depuis la version 1.3.0, Validation Muse s'accroche en priorité 20 (après le cœur CF7) et utilise Reflection pour remplacer le texte d'erreur SWV sur les champs déjà invalidés. Vos messages remplacent à la fois les défauts legacy et SWV.

= En quoi cela diffère-t-il des autres plugins de validation CF7 ? =

Validation Muse est le seul plugin de validation CF7 qui (1) est compatible CF7 6.x SWV dès l'installation, (2) stocke les messages dans le post meta du formulaire pour qu'ils vivent avec lui (compatible duplication CF7 et plugins import/export), et (3) s'intègre au plugin Flavor pour des messages par langue avec traduction AI en un click.

= Puis-je traduire les messages de validation par langue ? =

Oui — installez le plugin Flavor et Validation Muse affichera des onglets de langue dans l'éditeur de formulaire ainsi qu'un bouton *AI Translate*. Les traductions sont stockées dans la base de données de Flavor ; la désinstallation de Validation Muse les nettoie.

= Quels types de champs supportent des messages de format invalide personnalisés ? =

`email`, `url`, `tel`, `number` (y compris `range`) et `date`. Tout champ requis de tout type peut avoir un message de champ requis personnalisé.

= Où les messages sont-ils stockés ? =

Dans le post meta de chaque formulaire. Ils vivent avec le formulaire, donc dupliquer un formulaire (fonction native de CF7) conserve les messages. CF7 n'a pas d'export natif, mais les plugins d'import/export tiers lisent le post meta — les migrations entre sites fonctionnent donc sans étape d'import séparée.

= Puis-je utiliser du HTML dans les messages de validation ? =

Oui, le HTML basique est autorisé et nettoyé via `wp_kses_post()`.

= Cette extension nécessite-t-elle Contact Form 7 ? =

Oui. CF7 doit être installé et actif. L'extension affiche un avis admin et se désactive elle-même si CF7 manque.

= Y a-t-il une page de réglages ? =

Non. La configuration vit à l'intérieur de chaque formulaire, dans le panneau **Custom Validation**. Il n'y a délibérément pas de page de réglages globale — chaque formulaire conserve ses propres messages.

= L'extension trace-t-elle ou envoie-t-elle des données ? =

Non. Validation Muse ne fait aucune requête externe. Le bouton optionnel AI Translate (intégration Flavor) passe par le fournisseur configuré dans Flavor.

== Screenshots ==

1. Le panneau **Custom Validation** dans l'éditeur Contact Form 7 — activation par formulaire, messages par champ.
2. Onglets de langue et bouton **AI Translate** (visibles quand le plugin Flavor est actif).
3. Message de champ requis rendu en frontend.
4. Message de format invalide pour un champ email rendu en frontend.

== Changelog ==

= 1.4.1 =
* Readme : réécriture USP-first pour la visibilité SEO
* Tags : `messages`/`forms`/`customization` génériques remplacés par les ciblés `contact-form-7`, `cf7`, `validation`, `error-message`, `multilingual`
* FAQ : entrées ajoutées sur la compatibilité CF7 6.x SWV, comparaison avec d'autres plugins de validation CF7, multilinguisme via Flavor
* Testé jusqu'à WordPress 6.9.4

= 1.4.0 =
* Ajout du support multilingue via l'intégration au plugin de traduction Flavor
* Les messages de validation peuvent maintenant être traduits par langue dans l'éditeur de formulaire
* Les onglets de langue apparaissent automatiquement quand Flavor est actif
* Bouton AI Translate pour la traduction automatique en un click de tous les messages
* Traductions stockées dans la base de données de Flavor — les données du plugin restent portables
* Aucune surcharge si Flavor n'est pas installé — tous les appels derrière `class_exists()`
* Les traductions Flavor sont nettoyées à la désinstallation du plugin

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

= 1.4.1 =
Release de documentation. Readme rafraîchi pour une meilleure découvrabilité des fonctionnalités et compatibilité confirmée avec WordPress 6.9.4.

= 1.4.0 =
Ajoute le support multilingue via le plugin Flavor et la traduction AI en un click. Aucune migration de données nécessaire.

= 1.3.0 =
Restaure la compatibilité avec Contact Form 7 6.x Schema-based Validation (SWV). Recommandé pour tous les utilisateurs de CF7 6.x.
