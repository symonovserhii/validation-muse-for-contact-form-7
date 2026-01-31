=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact form 7, validation, forms, messages, customization
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Personnalisez les messages de validation pour chaque champ de Contact Form 7 directement dans l'éditeur de formulaire.

== Description ==

Validation Muse for Contact Form 7 vous permet de remplacer les messages d'erreur par défaut affichés par Contact Form 7. Activez l'extension par formulaire, puis fournissez un texte personnalisé pour les avis de champs obligatoires et la validation des e-mails, URL, téléphones, nombres, plages et dates. Les messages sont stockés dans les métadonnées de l'article, ils restent donc avec le formulaire lors de l'export/import.

**Fonctionnalités :**

* Personnalisation des messages "champ obligatoire" par champ
* Personnalisation des messages "format invalide" pour les champs e-mail, URL, téléphone, nombre, plage et date
* Activation/désactivation de la validation personnalisée par formulaire
* Messages stockés dans les métadonnées pour un export/import facile
* Entièrement traduisible avec fichier POT inclus
* Interface d'administration accessible avec labels ARIA

== Installation ==

1. Téléversez le dossier `validation-muse-for-contact-form-7` dans `/wp-content/plugins/`, ou installez-le depuis l'admin WordPress sous Extensions → Ajouter.
2. Activez l'extension via l'écran des Extensions.
3. Ouvrez n'importe quel formulaire Contact Form 7, allez dans le panneau **Validation personnalisée**, activez la fonctionnalité et enregistrez vos messages.

== Frequently Asked Questions ==

= Cette extension nécessite-t-elle Contact Form 7 ? =

Oui. Contact Form 7 doit être installé et actif. L'extension affichera un avis d'administration et se désactivera automatiquement si Contact Form 7 est absent.

= Quels types de champs supportent les messages d'invalidité personnalisés ? =

Les champs e-mail, URL, téléphone, nombre (y compris plage) et date peuvent afficher un texte personnalisé de format invalide. Tout champ obligatoire peut avoir un message "obligatoire" personnalisé.

= Où sont stockés les messages ? =

Les messages sont enregistrés dans les métadonnées de chaque formulaire. Ils sont inclus dans les exports Contact Form 7, donc migrer un formulaire déplacera les messages avec lui.

= Puis-je utiliser du HTML dans les messages de validation ? =

Oui, le HTML basique est autorisé et assaini avec `wp_kses_post()`.

== Screenshots ==

1. Le panneau de validation personnalisée dans l'éditeur Contact Form 7
2. Exemple de messages de validation personnalisés sur le frontend

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
