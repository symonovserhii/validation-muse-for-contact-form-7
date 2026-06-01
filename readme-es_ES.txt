=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact-form-7, cf7, validation, error-message, multilingual
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.5.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Mensajes personalizados de error y de campo obligatorio para Contact Form 7. Por formulario, por campo, compatible con CF7 6.x SWV, multilingüe.

== Description ==

**Validation Muse** te permite escribir tus propios mensajes de error para cada campo de Contact Form 7 — directamente en el editor del formulario, por formulario y por campo. Sin código, sin página global de ajustes, sin trucos en JavaScript.

La mayoría de plugins de validación de CF7 dejaron de funcionar cuando Contact Form 7 6.x introdujo **Schema-based Validation (SWV)**. Validation Muse ejecuta sus filtros con prioridad 20 (después del core de CF7) y usa Reflection para reemplazar el texto de error de SWV en campos ya invalidados — así tu copia personalizada aparece realmente, incluso con el nuevo motor de validación.

= Por qué Validation Muse =

* **Compatible con CF7 6.x SWV** — funciona con el nuevo motor Schema-based Validation, no solo con los hooks legacy.
* **Por formulario, por campo** — cada formulario guarda sus propios mensajes; sin sobrescritura global.
* **Almacenado en post meta** — los mensajes viven con el formulario, compatibles con la duplicación de formularios de CF7 y con plugins de import/export de terceros.
* **Multilingüe vía Flavor** — cuando el plugin de traducción Flavor está activo, las pestañas de idioma y un botón AI Translate aparecen automáticamente en el editor. Cero overhead cuando Flavor no está instalado.
* **Amigable para desarrolladores** — los hooks de extensibilidad `vmcf7_loaded` y `vmcf7_validation_tag_types` permiten añadir tipos de campo personalizados.
* **Ligero** — sin saturación admin, sin tracking, sin upsells.

= Tipos de campo soportados =

* Mensajes de campo obligatorio: cualquier tag required (text, textarea, select, checkbox, radio, file, etc.).
* Mensajes de formato inválido: `email`, `url`, `tel`, `number` (incluyendo `range`) y `date`.
* HTML dentro de los mensajes está permitido y se sanitiza vía `wp_kses_post()`.

= Traducciones =

El plugin incluye un archivo `.pot` y ya está traducido al neerlandés, alemán, ruso, español (Chile/España). [Ayuda a traducirlo a tu idioma.](https://translate.wordpress.org/projects/wp-plugins/validation-muse-for-contact-form-7)

== Installation ==

1. Instálalo desde **Plugins → Añadir nuevo** buscando *Validation Muse for Contact Form 7*, o sube la carpeta `validation-muse-for-contact-form-7` a `/wp-content/plugins/`.
2. Activa el plugin. Contact Form 7 debe estar ya activo — Validation Muse se desactivará automáticamente con un aviso si CF7 falta.
3. Edita cualquier formulario de Contact Form 7, abre el panel **Custom Validation**, actívalo y escribe tus mensajes.
4. (Opcional) Instala el plugin de traducción Flavor para traducir mensajes por idioma con asistencia de AI.

== Frequently Asked Questions ==

= ¿Funciona con Contact Form 7 6.x y Schema-based Validation (SWV)? =

Sí. Desde la versión 1.3.0, Validation Muse engancha con prioridad 20 (después del core de CF7) y usa Reflection para reemplazar el texto de error de SWV en campos ya invalidados. Tus mensajes anulan tanto los defaults legacy como los de SWV.

= ¿En qué se diferencia de otros plugins de validación CF7? =

Validation Muse es el único plugin de validación CF7 que (1) es compatible con CF7 6.x SWV de fábrica, (2) almacena los mensajes en el post meta del formulario para que vivan con él (compatible con la duplicación de formularios CF7 y plugins import/export), y (3) se integra con el plugin Flavor para mensajes por idioma con traducción AI de un click.

= ¿Puedo traducir los mensajes de validación por idioma? =

Sí — instala el plugin Flavor y Validation Muse mostrará pestañas de idioma en el editor del formulario más un botón *AI Translate*. Las traducciones se almacenan en la base de datos de Flavor; desinstalar Validation Muse las limpia.

= ¿Qué tipos de campo soportan mensajes personalizados de formato inválido? =

`email`, `url`, `tel`, `number` (incluyendo `range`) y `date`. Cualquier campo obligatorio de cualquier tipo puede tener un mensaje de obligatoriedad personalizado.

= ¿Dónde se almacenan los mensajes? =

En el post meta de cada formulario. Viven con el formulario, así que duplicar un formulario (función nativa de CF7) mantiene los mensajes. CF7 no tiene export nativo, pero los plugins de import/export de terceros leen el post meta — así que las migraciones entre sitios funcionan sin un paso de import separado.

= ¿Puedo usar HTML en los mensajes de validación? =

Sí, HTML básico está permitido y se sanitiza vía `wp_kses_post()`.

= ¿Este plugin requiere Contact Form 7? =

Sí. CF7 debe estar instalado y activo. El plugin muestra un aviso admin y se autodesactiva si CF7 falta.

= ¿Hay una página de ajustes? =

No. La configuración vive dentro de cada formulario, en el panel **Custom Validation**. No hay página global de ajustes por diseño — cada formulario guarda sus propios mensajes.

= ¿El plugin trackea o envía datos? =

No. Validation Muse no hace ninguna petición externa. El botón opcional AI Translate (integración con Flavor) se enruta a través del proveedor configurado en Flavor.

== Screenshots ==

1. El panel **Custom Validation** dentro del editor de Contact Form 7 — activación por formulario, mensajes por campo.
2. Pestañas de idioma y botón **AI Translate** (visibles cuando el plugin Flavor está activo).
3. Mensaje de campo obligatorio renderizado en el frontend.
4. Mensaje de formato inválido para un campo email renderizado en el frontend.

== Changelog ==

= 1.4.2 =
* Plugin URI: ahora apunta a la landing page dedicada en https://plugins.symonov.com/validation-muse-for-cf7/
* Sin cambios de código ni de comportamiento

= 1.4.1 =
* Readme: reescritura USP-first para visibilidad SEO
* Tags: reemplazadas las genéricas `messages`/`forms`/`customization` por `contact-form-7`, `cf7`, `validation`, `error-message`, `multilingual`
* FAQ: añadidas entradas sobre compatibilidad con CF7 6.x SWV, comparación con otros plugins de validación CF7, multilingüismo vía Flavor
* Tested up to WordPress 6.9.4

= 1.4.0 =
* Añadido soporte multilingüe vía integración con el plugin de traducción Flavor
* Los mensajes de validación ahora se pueden traducir por idioma en el editor del formulario
* Las pestañas de idioma aparecen automáticamente cuando Flavor está activo
* Botón AI Translate para traducción automática de un click de todos los mensajes
* Traducciones almacenadas en la base de datos de Flavor — los datos del plugin permanecen portables
* Cero overhead cuando Flavor no está instalado — todas las llamadas detrás de `class_exists()`
* Las traducciones de Flavor se limpian al desinstalar el plugin

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

= 1.4.2 =
Plugin URI ahora apunta a la landing page dedicada en plugins.symonov.com. Sin cambios de código.

= 1.4.1 =
Release de documentación. Readme refrescado para mejor descubrimiento de funciones y compatibilidad con WordPress 6.9.4 confirmada.

= 1.4.0 =
Añade soporte multilingüe vía el plugin Flavor y traducción AI de un click. No se requiere migración de datos.

= 1.3.0 =
Restaura la compatibilidad con Contact Form 7 6.x Schema-based Validation (SWV). Recomendado para todos los usuarios de CF7 6.x.
