=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact form 7, validation, forms, messages, customization
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Настраивайте сообщения валидации для каждого поля Contact Form 7 прямо в редакторе форм.

== Description ==

Validation Muse for Contact Form 7 позволяет переопределять стандартные сообщения об ошибках Contact Form 7. Включите плагин для конкретной формы и укажите пользовательский текст для уведомлений об обязательных полях и валидации email, URL, телефона, числа, диапазона и даты. Сообщения сохраняются в метаданных записи, поэтому они остаются с формой при экспорте/импорте.

**Возможности:**

* Настройка сообщений "обязательное поле" для каждого поля
* Настройка сообщений "неверный формат" для полей email, URL, телефона, числа, диапазона и даты
* Включение/отключение пользовательской валидации для каждой формы
* Сообщения хранятся в метаданных для простого экспорта/импорта
* Полностью переводимый с включённым POT-файлом
* Доступный интерфейс администратора с ARIA-метками

== Installation ==

1. Загрузите папку `validation-muse-for-contact-form-7` в `/wp-content/plugins/` или установите через админку WordPress в разделе Плагины → Добавить новый.
2. Активируйте плагин на странице Плагины.
3. Откройте любую форму Contact Form 7, перейдите на вкладку **Пользовательская валидация**, включите функцию и сохраните сообщения.

== Frequently Asked Questions ==

= Требуется ли для этого плагина Contact Form 7? =

Да. Contact Form 7 должен быть установлен и активирован. Плагин покажет уведомление администратора и автоматически деактивируется, если Contact Form 7 отсутствует.

= Какие типы полей поддерживают пользовательские сообщения о неверном формате? =

Поля email, URL, телефона, числа (включая диапазон) и даты могут отображать пользовательский текст неверного формата. Любое обязательное поле может иметь пользовательское сообщение "обязательно".

= Где хранятся сообщения? =

Сообщения сохраняются в метаданных каждой формы. Они включаются в экспорт Contact Form 7, поэтому при миграции формы сообщения перемещаются вместе с ней.

= Можно ли использовать HTML в сообщениях валидации? =

Да, базовый HTML разрешён и очищается с помощью `wp_kses_post()`.

== Screenshots ==

1. Панель пользовательской валидации в редакторе Contact Form 7
2. Пример пользовательских сообщений валидации на фронтенде

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
