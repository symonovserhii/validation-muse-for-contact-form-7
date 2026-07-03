=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact-form-7, cf7, validation, error-message, multilingual
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.6.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Messages d'erreur et de champ requis personnalisés pour Contact Form 7. Par formulaire, par champ, compatible CF7 6.x SWV, multilingue.

== Description ==

**Validation Muse** vous permet d'écrire vos propres messages d'erreur pour chaque champ de Contact Form 7 — directement dans l'éditeur de formulaire, par formulaire et par champ. Sans code, sans page de réglages globale, sans bidouilles JavaScript.

La plupart des plugins de validation CF7 ont cessé de fonctionner lorsque Contact Form 7 6.x a introduit la **Schema-based Validation (SWV)**. Validation Muse exécute ses filtres en priorité 20 (après le cœur de CF7) et utilise Reflection pour remplacer le texte d'erreur SWV sur les champs déjà invalidés — votre copie personnalisée s'affiche donc réellement, même avec le nouveau moteur de validation.

= Pourquoi Validation Muse =

* **Compatible CF7 6.x SWV** — fonctionne avec le nouveau moteur Schema-based Validation, pas seulement avec les hooks legacy.
* **Règles regex et longueur personnalisées** — définissez des motifs regex, une longueur min et max par champ, chacune avec son propre message d'erreur.
* **Règles conditionnelles « Required-If »** — rendez un champ obligatoire uniquement quand un champ compagnon est rempli/coché.
* **Intégration SWV côté client** — les règles de validation standard (required, email, length) sont injectées dans le moteur SWV natif de CF7 pour un retour instantané en frontend, avec une accessibilité (A11y) complète.
* **Import/export et modèles d'ensembles de règles** — exportez/importez des règles de validation en JSON, copiez-les depuis d'autres formulaires, et appliquez des modèles globaux en masse.
* **Jetons d'espace réservé** — utilisez `{field_label}`, `{min}` et `{max}` dans les messages de validation pour générer des textes dynamiques.
* **Par formulaire, par champ** — chaque formulaire conserve ses propres messages ; pas de surcharge globale.
* **Stocké en post meta** — les messages vivent avec le formulaire, compatibles avec la duplication de formulaire CF7 et les plugins d'import/export tiers.
* **Multilingue via WPML, Polylang & Flavor** — traduit les règles via les hooks standards WPML/Polylang, et propose des onglets de langue + un bouton AI Translate en un clic quand Flavor est actif.
* **Convivial pour les développeurs** — les hooks d'extensibilité `vmcf7_loaded` et `vmcf7_validation_tag_types` permettent d'ajouter des types de champs personnalisés.
* **Léger** — pas de surcharge admin, pas de tracking, pas d'upselling.

= Types de champs supportés =

* Messages de champ requis : tout tag required (`text`, `textarea`, `select`, `checkbox`, `radio`, `file`, etc.).
* Messages de format invalide : `email`, `url`, `tel`, `number` (y compris `range`), `date` et `time`.
* Messages d'erreur personnalisés en cas d'échec de validation : `file` (contrôles taille/type), `acceptance` (case non acceptée) et `quiz` (réponse incorrecte).
* Le HTML dans les messages est autorisé et nettoyé via `wp_kses_post()`.
* Les règles regex et longueur min/max personnalisées fonctionnent sur tous les champs de saisie (sauf acceptance et quiz).

= Traductions =

Le plugin est livré avec un fichier `.pot` et est déjà traduit en allemand, espagnol, français, portugais (Brésil), russe et ukrainien. [Aidez à le traduire dans votre langue.](https://translate.wordpress.org/projects/wp-plugins/validation-muse-for-contact-form-7)

== Installation ==

1. Installez depuis **Extensions → Ajouter** en cherchant *Validation Muse for Contact Form 7*, ou téléversez le dossier `validation-muse-for-contact-form-7` dans `/wp-content/plugins/`.
2. Activez l'extension. Contact Form 7 doit être déjà actif — Validation Muse se désactive automatiquement avec un avis admin si CF7 manque.
3. Modifiez n'importe quel formulaire Contact Form 7, ouvrez le panneau **Custom Validation**, activez-le et écrivez vos messages.
4. (Optionnel) Installez le plugin de traduction Flavor pour traduire les messages par langue avec assistance AI.

== Frequently Asked Questions ==

= Cela fonctionne-t-il avec Contact Form 7 6.x et Schema-based Validation (SWV) ? =

Oui. Depuis la version 1.3.0, Validation Muse s'accroche en priorité 20 (après le cœur CF7) et utilise Reflection pour remplacer le texte d'erreur SWV sur les champs déjà invalidés. Depuis la 1.6.1, les règles standard (required, email/url/tel/number/date/time, longueur min/max) sont aussi injectées directement dans le schéma SWV propre à CF7, si bien que la validation côté client (JS frontend) affiche vos messages personnalisés instantanément — via le moteur de validation natif de CF7, sans script supplémentaire de ce plugin.

= En quoi cela diffère-t-il des autres plugins de validation CF7 ? =

Validation Muse est le seul plugin de validation CF7 qui (1) est compatible CF7 6.x SWV dès l'installation — à la fois côté serveur et dans la validation côté client propre à CF7, (2) stocke les messages dans le post meta du formulaire pour qu'ils vivent avec lui (compatible duplication CF7 et plugins import/export), et (3) s'intègre au plugin Flavor pour des messages par langue avec traduction AI en un click.

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

= 1.6.3 =
* i18n : régénération complète du modèle de traduction `.pot` — le catalogue était figé depuis la 1.2.0 et ne couvrait qu'environ 20 % des chaînes traduisibles du plugin (règles regex/longueur, modèles de règles, erreurs AI Translate, etc. n'étaient jamais extractibles auparavant). Les six langues fournies (allemand, espagnol, français, portugais, russe, ukrainien) sont désormais entièrement traduites par rapport à l'interface actuelle.
* Doc : synchronisation de tous les fichiers readme traduits — la liste des fonctionnalités, les types de champs supportés et la FAQ reflètent désormais les fonctionnalités de la 1.6.0 à la 1.6.2 (règles regex/longueur personnalisées, required-if, validation côté client SWV native, modèles de règles) ; leurs sections Changelog/Upgrade Notice, auparavant bloquées à la 1.4.2, couvrent désormais toutes les versions jusqu'à la 1.6.2.
* Fix : ajout d'un commentaire `translators:` manquant pour une chaîne avec espace réservé (qualité du code i18n, aucun changement de comportement).

= 1.6.2 =
* Fix : les messages enregistrés par des versions antérieures au renommage du plugin (stockés sous l'ancien préfixe meta `_cf7cv_`) étaient invisibles pour le code actuel. Une migration unique les renomme désormais automatiquement avec le préfixe `_vmcf7_`, restaurant les messages perdus. Sûre en cas de collision, s'exécute une seule fois.

= 1.6.1 =
* Fix : messages de validation dupliqués/écrasés en frontend, résolu en intégrant le moteur SWV natif de CF7.
* Fix : les tiroirs et cartes d'aperçu du panneau admin s'affichaient dépliés car l'éditeur CF7 supprime les styles inline.

= 1.6.0 =
* Nouveau : règles de validation regex et longueur min/max personnalisées par champ, chacune avec son propre message.
* Nouveau : messages de validation pour les champs `file`, `acceptance` et `quiz` ; messages conditionnels « required-if ».
* Nouveau : jetons d'espace réservé `{field_label}`, `{min}`, `{max}` dans les messages.
* Nouveau : validation en ligne côté client reflétant les messages serveur, avec accessibilité `aria-describedby`.
* Nouveau : aperçu en direct des messages, copie de messages entre formulaires, import/export JSON des ensembles de messages, et lint de l'éditeur.
* Nouveau : enregistrement de chaînes WPML/Polylang en plus du pont Flavor ; import/export des traductions.

= 1.5.0 =
* PHP minimum relevé à 8.0 ; WordPress minimum relevé à 6.0 ; testé jusqu'à WordPress 7.0.
* Nouveau : messages de validation personnalisés pour les champs `time`.
* Retour d'erreur plus clair pour AI Translate dans l'éditeur de formulaire.
* Le remplacement de messages SWV basé sur Reflection journalise désormais les échecs sous WP_DEBUG pour un diagnostic plus facile.
* Meilleure gestion des traductions en attente (plus de repli silencieux) avec mise en cache par requête.
* Code modernisé aux idiomes PHP 8 ; échappement de sortie renforcé ; première suite de tests ajoutée.

= 1.4.2 =
* Plugin URI : pointe maintenant vers la page dédiée sur https://plugins.symonov.com/validation-muse-for-cf7/
* Aucun changement de code ni de comportement

= 1.4.1 =
* Readme : réécriture USP-first pour la visibilité SEO
* Tags : `messages`/`forms`/`customization` génériques remplacés par les ciblés `contact-form-7`, `cf7`, `validation`, `error-message`, `multilingual`
* FAQ : entrées ajoutées sur la compatibilité CF7 6.x SWV, comparaison avec d'autres plugins de validation CF7, multilinguisme via Flavor

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

= 1.6.3 =
Version documentation et traduction — aucun changement fonctionnel ni de comportement. Actualise entièrement les readme traduits et le catalogue de traduction .pot/.po/.mo.

= 1.6.2 =
Récupère les messages de validation enregistrés par les versions antérieures à 1.2.0 (ancien préfixe meta `_cf7cv_`) via une migration unique et automatique. Sûr, aucune action requise.

= 1.6.1 =
Corrige les messages de validation dupliqués/écrasés en frontend et les régressions de mise en page du panneau admin de la 1.6.0. Recommandé pour tous les utilisateurs de la 1.6.0.

= 1.6.0 =
Ajoute des règles regex/longueur personnalisées, plus de types de champs, des messages required-if, un reflet côté client, un aperçu en direct, l'import/export/modèles de règles et le support WPML/Polylang. Aucune migration de données requise.

= 1.5.0 =
Relève le PHP minimum à 8.0 et le WordPress minimum à 6.0. Vérifiez votre environnement d'hébergement avant la mise à jour.

= 1.4.2 =
Plugin URI pointe maintenant vers la page dédiée sur plugins.symonov.com. Aucun changement de code.

= 1.4.1 =
Release de documentation. Readme rafraîchi pour une meilleure découvrabilité des fonctionnalités.

= 1.4.0 =
Ajoute le support multilingue via le plugin Flavor et la traduction AI en un click. Aucune migration de données nécessaire.

= 1.3.0 =
Restaure la compatibilité avec Contact Form 7 6.x Schema-based Validation (SWV). Recommandé pour tous les utilisateurs de CF7 6.x.
