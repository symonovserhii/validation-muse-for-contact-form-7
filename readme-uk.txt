=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact-form-7, cf7, validation, error-message, multilingual
Requires at least: 5.8
Tested up to: 6.9.4
Requires PHP: 7.4
Stable tag: 1.4.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Користувацькі повідомлення про помилки та обов'язкові поля для Contact Form 7. Per-form, per-field, сумісність з CF7 6.x SWV, мультимовність.

== Description ==

**Validation Muse** дозволяє писати власні повідомлення про помилки для кожного поля Contact Form 7 — прямо в редакторі форми, для кожної форми та поля окремо. Без коду, без глобальних налаштувань, без JavaScript-хаків.

Більшість плагінів валідації CF7 перестали працювати, коли Contact Form 7 6.x представив **Schema-based Validation (SWV)**. Validation Muse запускає свої фільтри з пріоритетом 20 (після ядра CF7) і використовує Reflection, щоб замінити SWV-помилки для вже невалідних полів — тож ваш користувацький текст справді відображається на новому валідаційному рушії.

= Чому Validation Muse =

* **Сумісність з CF7 6.x SWV** — працює з новим Schema-based Validation, не лише зі старими хуками.
* **Per-form, per-field** — кожна форма зберігає свої повідомлення; немає глобального перевизначення.
* **Зберігається у post meta** — повідомлення живуть з формою, сумісні з дублюванням форми CF7 та сторонніми плагінами імпорту/експорту CF7.
* **Мультимовність через Flavor** — коли активний плагін Flavor, у редакторі автоматично з'являються мовні таби та кнопка AI Translate. Коли Flavor не встановлено — нульовий оверхед.
* **Дружній до розробників** — хуки розширюваності `vmcf7_loaded` та `vmcf7_validation_tag_types` дозволяють додавати власні типи полів.
* **Легкий** — без admin-роздуття, без трекінгу, без апселів.

= Підтримувані типи полів =

* Повідомлення про обов'язкові поля: будь-який required-тег (text, textarea, select, checkbox, radio, file тощо).
* Повідомлення про невалідний формат: `email`, `url`, `tel`, `number` (включно з `range`), та `date`.
* HTML усередині повідомлень дозволений і очищується через `wp_kses_post()`.

= Переклади =

Плагін постачається з `.pot`-файлом і вже перекладений нідерландською, німецькою, російською, іспанською (Чилі/Іспанія). [Допоможіть перекласти на свою мову.](https://translate.wordpress.org/projects/wp-plugins/validation-muse-for-contact-form-7)

== Installation ==

1. Встановіть з **Плагіни → Додати новий** і знайдіть *Validation Muse for Contact Form 7*, або завантажте папку `validation-muse-for-contact-form-7` до `/wp-content/plugins/`.
2. Активуйте плагін. Contact Form 7 має бути активним — Validation Muse автоматично деактивується із сповіщенням, якщо CF7 відсутній.
3. Відредагуйте будь-яку форму Contact Form 7, відкрийте панель **Custom Validation**, увімкніть її та напишіть свої повідомлення.
4. (Опційно) Встановіть плагін перекладу Flavor для перекладу повідомлень за мовами з AI-асистенцією.

== Frequently Asked Questions ==

= Чи працює це з Contact Form 7 6.x та Schema-based Validation (SWV)? =

Так. Починаючи з версії 1.3.0, Validation Muse чіпляється на пріоритет 20 (після ядра CF7) і використовує Reflection для заміни SWV-помилок для вже невалідних полів. Ваші повідомлення перевизначають як легасі, так і SWV-дефолти.

= Чим це відрізняється від інших плагінів валідації CF7? =

Validation Muse — єдиний плагін валідації CF7, який (1) сумісний з CF7 6.x SWV з коробки, (2) зберігає повідомлення у post meta форми, тож вони живуть з формою (сумісно з дублюванням форми CF7 та плагінами імпорту/експорту), та (3) інтегрується з плагіном перекладу Flavor для повідомлень per language з one-click AI-перекладом.

= Чи можна перекладати повідомлення валідації за мовами? =

Так — встановіть плагін Flavor, і Validation Muse покаже мовні таби в редакторі форми плюс кнопку *AI Translate*. Переклади зберігаються в БД Flavor; видалення Validation Muse очищує їх.

= Які типи полів підтримують власні повідомлення про невалідний формат? =

`email`, `url`, `tel`, `number` (включно з `range`), та `date`. Будь-яке обов'язкове поле будь-якого типу може мати власне повідомлення про обов'язковість.

= Де зберігаються повідомлення? =

У post meta кожної форми. Вони живуть з формою, тому дублювання форми (вбудоване в CF7) зберігає повідомлення. CF7 не має нативного експорту, але сторонні плагіни імпорту/експорту CF7 читають post meta — тож міграції між сайтами працюють без окремого кроку імпорту.

= Чи можна використовувати HTML у повідомленнях валідації? =

Так, базовий HTML дозволений і очищується через `wp_kses_post()`.

= Чи потребує цей плагін Contact Form 7? =

Так. CF7 має бути встановлений та активний. Плагін показує admin-сповіщення і самодеактивується, якщо CF7 відсутній.

= Чи є сторінка налаштувань? =

Ні. Конфігурація живе всередині кожної форми, у панелі **Custom Validation**. Глобальної сторінки налаштувань немає за дизайном — кожна форма зберігає свої повідомлення.

= Чи трекає або відсилає плагін якісь дані? =

Ні. Validation Muse не робить жодних зовнішніх запитів. Опційна кнопка AI Translate (інтеграція з Flavor) маршрутизується через власного провайдера Flavor.

== Screenshots ==

1. Панель **Custom Validation** усередині редактора Contact Form 7 — увімкнення per-form, повідомлення per-field.
2. Мовні таби та кнопка **AI Translate** (видимі коли активний плагін перекладу Flavor).
3. Повідомлення про обов'язкове поле, відрендерене на фронтенді.
4. Повідомлення про невалідний формат для поля email, відрендерене на фронтенді.

== Changelog ==

= 1.4.1 =
* Readme: USP-first переписання для SEO-помітності
* Tags: загальні `messages`/`forms`/`customization` замінено на цільові `contact-form-7`, `cf7`, `validation`, `error-message`, `multilingual`
* FAQ: додано записи про сумісність з CF7 6.x SWV, порівняння з іншими плагінами валідації CF7, мультимовність через Flavor
* Tested up to WordPress 6.9.4

= 1.4.0 =
* Додано мультимовну підтримку через інтеграцію з плагіном перекладу Flavor
* Повідомлення валідації тепер можна перекладати per language у редакторі форми
* Мовні таби з'являються автоматично коли активний плагін Flavor
* Кнопка AI Translate для one-click машинного перекладу всіх повідомлень
* Переклади зберігаються в БД Flavor — дані плагіна залишаються портативними
* Нульовий оверхед коли Flavor не встановлено — всі виклики за `class_exists()`-перевірками
* Переклади Flavor очищаються при видаленні плагіна

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
Документаційний реліз. Оновлено readme для кращої помітності функцій і підтверджено сумісність з WordPress 6.9.4.

= 1.4.0 =
Додає мультимовну підтримку через плагін перекладу Flavor та one-click AI-переклад повідомлень валідації. Міграція даних не потрібна.

= 1.3.0 =
Відновлює сумісність з Contact Form 7 6.x Schema-based Validation (SWV). Рекомендовано для всіх користувачів CF7 6.x.
