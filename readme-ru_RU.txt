=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact-form-7, cf7, validation, error-message, multilingual
Requires at least: 5.8
Tested up to: 6.9.4
Requires PHP: 7.4
Stable tag: 1.4.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Пользовательские сообщения об ошибках и обязательных полях для Contact Form 7. Per-form, per-field, совместимость с CF7 6.x SWV, мультиязычность.

== Description ==

**Validation Muse** позволяет писать собственные сообщения об ошибках для каждого поля Contact Form 7 — прямо в редакторе формы, для каждой формы и поля отдельно. Без кода, без глобальных настроек, без JavaScript-хаков.

Большинство плагинов валидации CF7 перестали работать, когда Contact Form 7 6.x представил **Schema-based Validation (SWV)**. Validation Muse запускает свои фильтры с приоритетом 20 (после ядра CF7) и использует Reflection, чтобы заменить SWV-ошибки для уже невалидных полей — поэтому ваш пользовательский текст действительно отображается на новом валидационном движке.

= Почему Validation Muse =

* **Совместимость с CF7 6.x SWV** — работает с новой Schema-based Validation, не только со старыми хуками.
* **Per-form, per-field** — каждая форма хранит свои сообщения; нет глобального переопределения.
* **Хранится в post meta** — сообщения живут с формой, совместимы с дублированием формы CF7 и сторонними плагинами импорта/экспорта CF7.
* **Мультиязычность через Flavor** — когда плагин Flavor активен, в редакторе автоматически появляются языковые табы и кнопка AI Translate. Когда Flavor не установлен — нулевой оверхед.
* **Дружественен к разработчикам** — хуки расширяемости `vmcf7_loaded` и `vmcf7_validation_tag_types` позволяют добавлять собственные типы полей.
* **Лёгкий** — без admin-раздутия, без трекинга, без апселов.

= Поддерживаемые типы полей =

* Сообщения для обязательных полей: любой required-тег (text, textarea, select, checkbox, radio, file и т.д.).
* Сообщения о невалидном формате: `email`, `url`, `tel`, `number` (включая `range`) и `date`.
* HTML внутри сообщений разрешён и очищается через `wp_kses_post()`.

= Переводы =

Плагин поставляется с `.pot`-файлом и уже переведён на голландский, немецкий, русский, испанский (Чили/Испания). [Помогите перевести на ваш язык.](https://translate.wordpress.org/projects/wp-plugins/validation-muse-for-contact-form-7)

== Installation ==

1. Установите из **Плагины → Добавить новый**, найдя *Validation Muse for Contact Form 7*, или загрузите папку `validation-muse-for-contact-form-7` в `/wp-content/plugins/`.
2. Активируйте плагин. Contact Form 7 должен быть активен — Validation Muse автоматически деактивируется с уведомлением, если CF7 отсутствует.
3. Откройте любую форму Contact Form 7, перейдите в панель **Custom Validation**, включите её и напишите свои сообщения.
4. (Опционально) Установите плагин перевода Flavor для перевода сообщений по языкам с AI-помощью.

== Frequently Asked Questions ==

= Работает ли это с Contact Form 7 6.x и Schema-based Validation (SWV)? =

Да. Начиная с версии 1.3.0, Validation Muse цепляется на приоритет 20 (после ядра CF7) и использует Reflection для замены SWV-ошибок для уже невалидных полей. Ваши сообщения переопределяют как legacy, так и SWV-дефолты.

= Чем это отличается от других плагинов валидации CF7? =

Validation Muse — единственный плагин валидации CF7, который (1) совместим с CF7 6.x SWV из коробки, (2) хранит сообщения в post meta формы, поэтому они живут с формой (совместимо с дублированием формы CF7 и плагинами импорта/экспорта), и (3) интегрируется с плагином перевода Flavor для сообщений per language с one-click AI-переводом.

= Можно ли переводить сообщения валидации по языкам? =

Да — установите плагин Flavor, и Validation Muse покажет языковые табы в редакторе формы плюс кнопку *AI Translate*. Переводы хранятся в БД Flavor; удаление Validation Muse очищает их.

= Какие типы полей поддерживают пользовательские сообщения о невалидном формате? =

`email`, `url`, `tel`, `number` (включая `range`) и `date`. Любое обязательное поле любого типа может иметь собственное сообщение об обязательности.

= Где хранятся сообщения? =

В post meta каждой формы. Они живут с формой, поэтому дублирование формы (встроено в CF7) сохраняет сообщения. CF7 не имеет нативного экспорта, но сторонние плагины импорта/экспорта CF7 читают post meta — поэтому миграции между сайтами работают без отдельного шага импорта.

= Можно ли использовать HTML в сообщениях валидации? =

Да, базовый HTML разрешён и очищается через `wp_kses_post()`.

= Требует ли этот плагин Contact Form 7? =

Да. CF7 должен быть установлен и активен. Плагин показывает admin-уведомление и самодеактивируется, если CF7 отсутствует.

= Есть ли страница настроек? =

Нет. Конфигурация живёт внутри каждой формы, в панели **Custom Validation**. Глобальной страницы настроек нет по дизайну — каждая форма хранит свои сообщения.

= Трекает или отправляет ли плагин какие-либо данные? =

Нет. Validation Muse не делает никаких внешних запросов. Опциональная кнопка AI Translate (интеграция с Flavor) маршрутизируется через собственного провайдера Flavor.

== Screenshots ==

1. Панель **Custom Validation** внутри редактора Contact Form 7 — включение per-form, сообщения per-field.
2. Языковые табы и кнопка **AI Translate** (видимы когда плагин Flavor активен).
3. Сообщение об обязательном поле, отрендеренное на фронтенде.
4. Сообщение о невалидном формате email, отрендеренное на фронтенде.

== Changelog ==

= 1.4.2 =
* Plugin URI: теперь указывает на отдельную страницу https://plugins.symonov.com/validation-muse-for-cf7/
* Без изменений кода или поведения

= 1.4.1 =
* Readme: USP-first переписывание для SEO-видимости
* Tags: общие `messages`/`forms`/`customization` заменены на целевые `contact-form-7`, `cf7`, `validation`, `error-message`, `multilingual`
* FAQ: добавлены записи про совместимость с CF7 6.x SWV, сравнение с другими плагинами валидации CF7, мультиязычность через Flavor
* Tested up to WordPress 6.9.4

= 1.4.0 =
* Добавлена мультиязычная поддержка через интеграцию с плагином перевода Flavor
* Сообщения валидации теперь можно переводить per language в редакторе формы
* Языковые табы появляются автоматически когда плагин Flavor активен
* Кнопка AI Translate для one-click машинного перевода всех сообщений
* Переводы хранятся в БД Flavor — данные плагина остаются портативными
* Нулевой оверхед когда Flavor не установлен — все вызовы за `class_exists()`-проверками
* Переводы Flavor очищаются при удалении плагина

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
Plugin URI теперь указывает на отдельную страницу на plugins.symonov.com. Без изменений кода.

= 1.4.1 =
Документационный релиз. Обновлён readme для лучшей видимости функций и подтверждена совместимость с WordPress 6.9.4.

= 1.4.0 =
Добавляет мультиязычную поддержку через плагин перевода Flavor и one-click AI-перевод сообщений валидации. Миграция данных не требуется.

= 1.3.0 =
Восстанавливает совместимость с Contact Form 7 6.x Schema-based Validation (SWV). Рекомендуется для всех пользователей CF7 6.x.
