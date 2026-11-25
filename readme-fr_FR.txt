=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact-form-7, cf7, validation, error-message, multilingual
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Règles et messages de validation personnalisés pour Contact Form 7 — regex, longueur, required-if, par champ, compatible CF7 6.x SWV, multilingue.

== Description ==

**Validation Muse** vous permet d'écrire vos propres règles de validation et messages d'erreur pour chaque champ de Contact Form 7 — directement dans l'éditeur de formulaire, par formulaire et par champ. Sans code, sans page de réglages globale, sans bidouilles JavaScript.

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
* **Convivial pour les développeurs** — les hooks d'extensibilité `vmcf7_loaded` et `vmcf7_validation_tag_types` permettent d'ajouter des types de champs personnalisés ; les filtres `vmcf7_message`, `vmcf7_default_message`, `vmcf7_placeholders` et `vmcf7_field_label` permettent de lire les messages, de définir des messages par défaut pour tout le site et d'ajouter vos propres `{tokens}`.
* **Léger** — pas de surcharge admin, pas de tracking, pas d'upselling.

= Types de champs supportés =

* Messages de champ requis : tout tag required (`text`, `textarea`, `select`, `checkbox`, `radio`, `file`, etc.).
* Messages de format invalide : `email`, `url`, `tel`, `number` (y compris `range`), `date` et `time`.
* Messages d'erreur personnalisés en cas d'échec de validation : `file` (contrôles taille/type), `acceptance` (case non acceptée) et `quiz` (réponse incorrecte).
* Le HTML dans les messages est autorisé et nettoyé via `wp_kses_post()`.
* Les règles personnalisées de regex et de longueur Min/Max sont disponibles pour tous les types de champs sauf `file`.

= Traductions =

Le plugin est livré avec un fichier `.pot` et est déjà traduit en allemand, espagnol, français, italien, japonais, polonais, portugais (Brésil), russe et ukrainien. [Aidez à le traduire dans votre langue.](https://translate.wordpress.org/projects/wp-plugins/validation-muse-for-contact-form-7)

== Installation ==

1. Installez depuis **Extensions → Ajouter** en cherchant *Validation Muse for Contact Form 7*, ou téléversez le dossier `validation-muse-for-contact-form-7` dans `/wp-content/plugins/`.
2. Activez l'extension. Contact Form 7 doit être déjà actif — Validation Muse se désactive automatiquement avec un avis admin si CF7 manque.
3. Modifiez n'importe quel formulaire Contact Form 7, ouvrez l'onglet **Validation personnalisée**, activez-la et écrivez vos messages.
4. (Optionnel) Installez le plugin de traduction Flavor pour traduire les messages par langue avec assistance AI.

== Frequently Asked Questions ==

= Cela fonctionne-t-il avec Contact Form 7 6.x et Schema-based Validation (SWV) ? =

Oui. Depuis la version 1.3.0, Validation Muse s'accroche en priorité 20 (après le cœur CF7) et utilise Reflection pour remplacer le texte d'erreur SWV sur les champs déjà invalidés. Depuis la 1.6.1, les règles standard (required, email/url/tel/number/date/time, longueur min/max) sont aussi injectées directement dans le schéma SWV propre à CF7, si bien que la validation côté client (JS frontend) affiche vos messages personnalisés instantanément — via le moteur de validation natif de CF7, sans script supplémentaire de ce plugin.

= En quoi cela diffère-t-il des autres plugins de validation CF7 ? =

Validation Muse est le seul plugin de validation CF7 qui (1) est compatible CF7 6.x SWV dès l'installation — à la fois côté serveur et dans la validation côté client propre à CF7, (2) stocke les messages dans le post meta du formulaire pour qu'ils vivent avec lui (compatible duplication CF7 et plugins import/export), et (3) prend en charge les messages par langue via WPML, Polylang ou le plugin Flavor (avec traduction AI en un click).

= Puis-je traduire les messages de validation par langue ? =

Oui. WPML (String Translation) et Polylang sont pris en charge nativement : vos messages sont enregistrés pour la traduction à chaque enregistrement du formulaire, et l'onglet **Validation personnalisée** renvoie vers l'écran où vous les traduisez. Avec le plugin Flavor, Validation Muse affiche en plus des onglets de langue dans l'éditeur de formulaire ainsi qu'un bouton *AI Translate* ; ces traductions sont stockées dans la base de données de Flavor, et la désinstallation de Validation Muse les nettoie.

= Quels types de champs prennent en charge des messages personnalisés de format invalide ou d'échec de validation ? =

* `email`, `url`, `tel`, `number` (y compris `range`), `date` et `time` prennent en charge des messages de format invalide personnalisés.
* `file` prend en charge des messages d'erreur de validation de taille/type de fichier personnalisés.
* `acceptance` prend en charge des messages d'erreur personnalisés lorsque la case n'est pas acceptée.
* `quiz` prend en charge des messages d'erreur personnalisés lorsque la réponse est incorrecte.
* Tout champ requis de tout type peut avoir un message de champ requis personnalisé.

= Où les messages sont-ils stockés ? =

Dans le post meta de chaque formulaire. Ils vivent avec le formulaire, donc dupliquer un formulaire (fonction native de CF7) conserve les messages. CF7 n'a pas d'export natif, mais les plugins d'import/export tiers lisent le post meta — les migrations entre sites fonctionnent donc sans étape d'import séparée.

= Puis-je utiliser du HTML dans les messages de validation ? =

Oui, le HTML basique est autorisé et nettoyé via `wp_kses_post()`.

= Cette extension nécessite-t-elle Contact Form 7 ? =

Oui. CF7 doit être installé et actif. L'extension affiche un avis admin et se désactive elle-même si CF7 manque.

= Y a-t-il une page de réglages ? =

Non. La configuration vit à l'intérieur de chaque formulaire, dans l'onglet **Validation personnalisée**. Il n'y a délibérément pas de page de réglages globale — chaque formulaire conserve ses propres messages.

= L'extension trace-t-elle ou envoie-t-elle des données ? =

Non. Validation Muse ne fait aucune requête externe. Le bouton optionnel AI Translate (intégration Flavor) passe par le fournisseur configuré dans Flavor.

== Screenshots ==

1. L'onglet **Validation personnalisée** dans l'éditeur Contact Form 7 — une carte par champ avec les messages, les règles supplémentaires (longueur, motif avec testeur en direct, obligatoire seulement dans certains cas) et un aperçu de ce que verront les visiteurs.
2. **Copier, sauvegarder et réutiliser** — copiez les règles d'un autre formulaire, téléchargez-les/chargez-les sous forme de fichier, conservez un modèle ou utilisez-les sur tous les formulaires.
3. Message de champ requis rendu en frontend.
4. Message de format invalide pour un champ email rendu en frontend.

== Changelog ==

= 1.8.0 =
* Nouveau : l'onglet « Validation personnalisée » a été repensé. Chaque champ est une carte avec son libellé, son type et l'indication s'il est personnalisé ; les règles supplémentaires (longueur, motif, obligatoire seulement dans certains cas) se trouvent derrière un seul interrupteur « Règles supplémentaires » ; chaque carte montre ce que verront les visiteurs. La recherche, « Afficher uniquement les champs personnalisés » et tout ouvrir/tout fermer aident sur les longs formulaires.
* Nouveau : des exemples de motifs prêts à l'emploi et une zone « Essayez : saisissez un exemple de réponse » qui vérifie un motif de la même façon que le site.
* Nouveau : les outils de copie, de sauvegarde, de modèle et « Utiliser sur tous les formulaires » sont regroupés et expliqués, demandent une confirmation sur place et affichent le résultat dans le panneau plutôt que dans des pop-ups du navigateur.
* Nouveau : quand WPML String Translation ou Polylang est actif, l'onglet renvoie directement vers l'écran où les messages sont traduits.
* Nouveau : message personnalisé « mauvaise réponse » pour les champs quiz et « mauvais fichier » pour les champs file.
* Nouveau : `{min}` et `{max}` fonctionnent dans tous les messages ; filtres pour développeurs `vmcf7_default_message`, `vmcf7_placeholders`, `vmcf7_field_label`, `vmcf7_supports_text_rules` et `vmcf7_swv_invalid_rules`.
* Nouveau : le style des erreurs en frontend peut être modifié avec des propriétés personnalisées CSS (`--vmcf7-error-color`, `--vmcf7-error-font-size` et d'autres).
* Nouveau : traductions en japonais, italien et polonais.
* Fix : l'éditeur Contact Form 7 ne demande plus « Quitter le site ? Les modifications que vous avez apportées ne seront peut-être pas enregistrées » quand rien n'a été modifié.
* Fix : les messages personnalisés et les limites de longueur atteignent de nouveau la validation côté client de Contact Form 7 6.2+ (le hook du schéma ne faisait silencieusement rien sur 6.2).
* Fix : les barres obliques inverses étaient supprimées des motifs et messages enregistrés (`^\d+$` était stocké sous la forme `^d+$`), y compris lors de l'import, de la copie et de « Utiliser sur tous les formulaires ».
* Fix : un motif invalide ne rejette plus toutes les réponses ; une règle de motif ou de longueur sur des cases à cocher ou des sélections multiples ne provoque plus d'erreur à l'envoi.
* Fix : copier les règles d'un formulaire sur ce même formulaire ne les efface plus ; les outils de jeux de règles n'acceptent que les formulaires Contact Form 7 que l'utilisateur peut modifier, et les fichiers d'import sont vérifiés et limités en taille.
* Fix : WPML/Polylang recevaient chaque message avec un enregistrement de retard, Polylang n'affichait pas les messages sur son écran Traductions, puis les affichait en double.
* Fix : le message personnalisé de champ obligatoire s'applique désormais aussi aux cases d'acceptation ; required-if fonctionne quand le nom de l'autre champ contient des majuscules.
* Fix : AI Translate n'envoie plus les motifs et les nombres au traducteur.
* Fix : la désinstallation supprime toutes les options du plugin ; les styles frontend se chargent aussi pour les formulaires placés dans des widgets et des modèles.

= 1.7.2 =
* Fix : l'ouverture de Contact → Créer un formulaire avec Flavor actif ne s'arrête plus sur une erreur fatale. Un formulaire qui n'a pas encore été enregistré n'a pas d'ID, et Validation Muse ignore désormais ses traductions jusqu'à ce que le formulaire soit enregistré.

= 1.7.1 =
* Fix : l'archive que GitHub et Composer construisent à partir d'un tag ne contient plus les tests, les outils de développement ni la documentation, de sorte qu'un projet qui installe le plugin depuis Git ne reçoit que le plugin. Aucun changement dans le comportement du plugin.

= 1.7.0 =
* Nouveau : le filtre `vmcf7_message` transmet à un autre code un message écrit ici : `apply_filters( 'vmcf7_message', $default, $form_id, $field_name, $type )` renvoie le message personnalisé, ou `$default` si aucun n'a été écrit ou si la validation personnalisée est désactivée pour le formulaire. Rien ne change pour les visiteurs tant qu'un autre code ne le demande pas.

= 1.6.5 =
* Nouveau : une demande unique, que l'on peut ignorer, de laisser un avis sur WordPress.org. Elle n'apparaît qu'une semaine après le premier enregistrement d'un message de validation personnalisé, uniquement pour les utilisateurs qui peuvent modifier les formulaires Contact Form 7 et uniquement sur les écrans de Contact Form 7. « Peut-être plus tard » la reporte de 30 jours, « Ne plus demander » la masque définitivement. Rien n'est envoyé nulle part.

= 1.6.4 =
* Testé avec WordPress 7.1 (vérifié sur 7.1.1 avec Contact Form 7 6.1).
* Fix : suppression des appels obsolètes en PHP 8.5 (`finfo_close()`, et `ReflectionProperty::setAccessible()` à partir de PHP 8.1), le plugin ne déclenche donc plus d'avis d'obsolescence sous PHP 8.5.

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
* Aucune surcharge si Flavor n'est pas installé — tous les appels derrière class_exists()
* Les traductions Flavor sont nettoyées à la désinstallation du plugin

= 1.3.0 =
* Correction de la compatibilité avec Contact Form 7 6.x SWV (Schema-based Validation)
* Les filtres de validation s'exécutent désormais en priorité 20 (après le cœur CF7) pour remplacer les messages d'erreur SWV
* Ajout du remplacement d'erreur basé sur Reflection pour les champs déjà invalidés
* Les messages personnalisés remplacent désormais correctement le texte par défaut "The field is required." de CF7

= 1.2.1 =
* Correction d'une incohérence de nom de variable causant l'erreur "No required fields"

= 1.2.0 =
* Refactorisation du code pour suivre les WordPress Coding Standards
* Réorganisation de la structure des fichiers pour une meilleure maintenabilité
* Ajout de blocs PHPDoc à toutes les fonctions et méthodes
* Amélioration de l'accessibilité avec des libellés ARIA
* Correction d'une incohérence de préfixe JavaScript
* Correction du script de désinstallation pour utiliser le bon préfixe meta
* Ajout de hooks d'extensibilité (`vmcf7_loaded`, `vmcf7_validation_tag_types`)
* Changement de la vérification de capacité de `manage_options` à `wpcf7_edit_contact_forms`
* Mise à jour du nom du fichier POT pour correspondre au domaine de texte

= 1.1.2 =
* Changement du nom du plugin.

= 1.1.1 =
* Ajout du fichier .gitignore.

= 1.1.0 =
* Ajout des éléments du dépôt WordPress (readme, licence, fichier POT).
* Refonte des hooks de validation pour remplacer les messages required et invalid sans dépendre des filtres AJAX.
* Renforcement de la sanitisation, du chargement du domaine de texte et du nettoyage à la désinstallation pour la préparation à la publication.

= 1.0.1 =
* Itération publique initiale intégrée au projet.

== Upgrade Notice ==

= 1.8.0 =
Onglet « Validation personnalisée » repensé, messages côté client de nouveau fonctionnels sur Contact Form 7 6.2+ et plusieurs correctifs de validation. Les règles et messages existants sont conservés tels quels.

= 1.7.2 =
Corrige une erreur fatale sur Contact → Créer un formulaire quand Flavor est actif. Aucun changement dans le comportement de la validation.

= 1.7.1 =
Packaging uniquement : l'archive Git ne contient plus les tests ni les outils. Aucun changement dans le comportement de la validation.

= 1.7.0 =
Ajoute le filtre `vmcf7_message` pour qu'un autre code puisse lire les messages écrits ici. Aucun changement dans le comportement de la validation.

= 1.6.5 =
Ajoute une demande d'avis unique, que l'on peut ignorer (affichée une semaine après le premier enregistrement d'un message personnalisé). Aucun changement dans le comportement de la validation.

= 1.6.4 =
Version de compatibilité : testée avec WordPress 7.1 et propre sous PHP 8.5. Aucun changement de comportement.

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
