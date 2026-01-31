=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact form 7, validation, forms, messages, customization
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Personaliza los mensajes de validación para cada campo de Contact Form 7 directamente en el editor de formularios.

== Description ==

Validation Muse for Contact Form 7 te permite sobrescribir los mensajes de error predeterminados que muestra Contact Form 7. Activa el plugin por formulario y proporciona texto personalizado para avisos de campos obligatorios y validación de email, URL, teléfono, número, rango y fecha. Los mensajes se almacenan en post meta, por lo que permanecen con el formulario al exportar/importar.

**Características:**

* Personaliza mensajes de "campo obligatorio" por campo
* Personaliza mensajes de "formato inválido" para campos de email, URL, teléfono, número, rango y fecha
* Activa/desactiva la validación personalizada por formulario
* Mensajes almacenados en post meta para fácil exportación/importación
* Totalmente traducible con archivo POT incluido
* Interfaz de administración accesible con etiquetas ARIA

== Installation ==

1. Sube la carpeta `validation-muse-for-contact-form-7` a `/wp-content/plugins/`, o instálalo desde el administrador de WordPress en Plugins → Añadir nuevo.
2. Activa el plugin a través de la pantalla de Plugins.
3. Abre cualquier formulario de Contact Form 7, ve al panel de **Validación personalizada**, activa la función y guarda tus mensajes.

== Frequently Asked Questions ==

= ¿Este plugin requiere Contact Form 7? =

Sí. Contact Form 7 debe estar instalado y activo. El plugin mostrará un aviso de administrador y se desactivará automáticamente si Contact Form 7 no está presente.

= ¿Qué tipos de campos soportan mensajes de formato inválido personalizados? =

Los campos de email, URL, teléfono, número (incluyendo rango) y fecha pueden mostrar texto personalizado de formato inválido. Cualquier campo obligatorio puede tener un mensaje personalizado de "obligatorio".

= ¿Dónde se almacenan los mensajes? =

Los mensajes se guardan en el post meta de cada formulario. Se incluyen en las exportaciones de Contact Form 7, por lo que migrar un formulario moverá los mensajes con él.

= ¿Puedo usar HTML en los mensajes de validación? =

Sí, se permite HTML básico y se sanitiza usando `wp_kses_post()`.

== Screenshots ==

1. El panel de Validación personalizada en el editor de Contact Form 7
2. Ejemplo de mensajes de validación personalizados en el frontend

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
