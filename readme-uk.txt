=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact form 7, validation, forms, messages, customization
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Налаштовуйте повідомлення валідації для кожного поля Contact Form 7 безпосередньо в редакторі форм.

== Description ==

Validation Muse for Contact Form 7 дозволяє перевизначати стандартні повідомлення про помилки Contact Form 7. Увімкніть плагін для конкретної форми та вкажіть користувацький текст для сповіщень про обов'язкові поля та валідацію email, URL, телефону, числа, діапазону та дати. Повідомлення зберігаються в метаданих запису, тому вони залишаються з формою при експорті/імпорті.

**Можливості:**

* Налаштування повідомлень "обов'язкове поле" для кожного поля
* Налаштування повідомлень "невірний формат" для полів email, URL, телефону, числа, діапазону та дати
* Увімкнення/вимкнення користувацької валідації для кожної форми
* Повідомлення зберігаються в метаданих для простого експорту/імпорту
* Повністю перекладається з включеним POT-файлом
* Доступний інтерфейс адміністратора з ARIA-мітками

== Installation ==

1. Завантажте папку `validation-muse-for-contact-form-7` до `/wp-content/plugins/` або встановіть через адмінку WordPress у розділі Плагіни → Додати новий.
2. Активуйте плагін на сторінці Плагіни.
3. Відкрийте будь-яку форму Contact Form 7, перейдіть на вкладку **Користувацька валідація**, увімкніть функцію та збережіть повідомлення.

== Frequently Asked Questions ==

= Чи потрібен для цього плагіна Contact Form 7? =

Так. Contact Form 7 має бути встановлений та активований. Плагін покаже сповіщення адміністратора та автоматично деактивується, якщо Contact Form 7 відсутній.

= Які типи полів підтримують користувацькі повідомлення про невірний формат? =

Поля email, URL, телефону, числа (включаючи діапазон) та дати можуть відображати користувацький текст невірного формату. Будь-яке обов'язкове поле може мати користувацьке повідомлення "обов'язково".

= Де зберігаються повідомлення? =

Повідомлення зберігаються в метаданих кожної форми. Вони включаються в експорт Contact Form 7, тому при міграції форми повідомлення переміщуються разом з нею.

= Чи можна використовувати HTML в повідомленнях валідації? =

Так, базовий HTML дозволений та очищується за допомогою `wp_kses_post()`.

== Screenshots ==

1. Панель користувацької валідації в редакторі Contact Form 7
2. Приклад користувацьких повідомлень валідації на фронтенді

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
