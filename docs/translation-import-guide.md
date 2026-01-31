# Импорт переводов на translate.wordpress.org

## Статус
- [x] Переводы созданы (.po/.mo файлы)
- [x] Загружены в SVN и GitHub
- [x] Запрос PTE отправлен в #polyglots Slack
- [ ] Получен PTE доступ
- [ ] Переводы импортированы

## После получения PTE доступа

### 1. Перейти на страницу переводов плагина
https://translate.wordpress.org/projects/wp-plugins/validation-muse-for-contact-form-7/

### 2. Импортировать переводы для каждого языка

Для каждого языка выполнить:

1. Нажать на язык (например, "Russian")
2. Выбрать "Development (Readme)" или "Development" в зависимости от типа строк
3. Нажать "Import" в боковой панели
4. Загрузить соответствующий .po файл из `languages/`

| Язык | Файл |
|------|------|
| Spanish (Spain) | `validation-muse-for-contact-form-7-es_ES.po` |
| German | `validation-muse-for-contact-form-7-de_DE.po` |
| French (France) | `validation-muse-for-contact-form-7-fr_FR.po` |
| Portuguese (Brazil) | `validation-muse-for-contact-form-7-pt_BR.po` |
| Russian | `validation-muse-for-contact-form-7-ru_RU.po` |
| Ukrainian | `validation-muse-for-contact-form-7-uk.po` |

### 3. Проверить статус
После импорта переводы появятся на странице плагина:
https://wordpress.org/plugins/validation-muse-for-contact-form-7/

В секции справа появится "Languages" со списком доступных языков.

## Альтернатива: ручной ввод
Если импорт не работает, можно вводить переводы вручную через веб-интерфейс GlotPress. Все строки для перевода:

1. This plugin requires Contact Form 7 to be installed and activated.
2. Plugin dependency check
3. Validation Muse requires Contact Form 7 to be installed and activated. %1$sInstall Contact Form 7%2$s
4. Custom Validation
5. Please enter a valid email address
6. Please enter a valid URL
7. Please enter a valid phone number
8. Please enter a valid number
9. Please enter a valid date
10. Enable custom validation messages for this form
11. Custom validation messages
12. Field
13. Required Message
14. Invalid Format Message
15. Required message for %s
16. This field is required
17. Invalid format message for %s
18. No required fields found in this form.

## Полезные ссылки
- Страница переводов: https://translate.wordpress.org/projects/wp-plugins/validation-muse-for-contact-form-7/
- Polyglots Handbook: https://make.wordpress.org/polyglots/handbook/
- Slack #polyglots: https://wordpress.slack.com/archives/C02RP50LK
