=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact-form-7, cf7, validation, error-message, multilingual
Requires at least: 6.0
Tested up to: 7.2
Requires PHP: 8.0
Stable tag: 1.8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Własne reguły i komunikaty walidacji dla Contact Form 7 — regex, długość, wymagane warunkowo, dla każdego pola, zgodność z SWV w CF7 6.x, wielojęzyczność.

== Description ==

**Validation Muse** pozwala tworzyć własne reguły walidacji i komunikaty o błędach dla każdego pola Contact Form 7 — bezpośrednio w edytorze formularza, osobno dla każdego formularza i każdego pola. Bez kodu, bez globalnej strony ustawień, bez sztuczek w JavaScripcie.

Większość wtyczek walidacji dla CF7 przestała działać, gdy Contact Form 7 6.x wprowadził **Schema-based Validation (SWV)**. Validation Muse uruchamia swoje filtry z priorytetem 20 (po rdzeniu CF7) i za pomocą Reflection podmienia tekst błędu SWV w polach już oznaczonych jako nieprawidłowe, dzięki czemu własne komunikaty faktycznie się wyświetlają — także w nowym silniku walidacji.

= Dlaczego Validation Muse =

* **Zgodność z SWV w CF7 6.x** — działa z nowym silnikiem Schema-based Validation, a nie tylko ze starszymi hakami.
* **Własne reguły regex i długości** — własne wzorce wyrażeń regularnych oraz minimalna i maksymalna długość dla każdego pola, każda reguła z własnym komunikatem o błędzie.
* **Reguły warunkowe „wymagane, jeśli”** — pole staje się wymagane tylko wtedy, gdy powiązane pole jest wypełnione lub zaznaczone.
* **Integracja z SWV po stronie klienta** — standardowe reguły walidacji (wymagane, e-mail, długość) są dodawane do natywnego silnika SWV w CF7, co zapewnia natychmiastową informację zwrotną na froncie witryny z pełną obsługą dostępności (A11y).
* **Import/eksport zestawów reguł i szablony** — eksport i import reguł walidacji w formacie JSON, kopiowanie ich z innych formularzy oraz zbiorcze stosowanie globalnych szablonów.
* **Znaczniki zastępcze** — `{field_label}`, `{min}` i `{max}` w komunikatach walidacji pozwalają tworzyć dynamiczne teksty.
* **Dla każdego formularza i każdego pola** — każdy formularz zachowuje własne komunikaty; bez globalnego nadpisywania.
* **Zapis w metadanych wpisu** — komunikaty są przechowywane razem z formularzem, zgodnie z duplikowaniem formularzy w CF7 i zewnętrznymi wtyczkami importu/eksportu dla CF7.
* **Wielojęzyczność dzięki WPML, Polylang i Flavor** — reguły są tłumaczone za pomocą standardowych haków WPML/Polylang, a gdy włączona jest wtyczka Flavor, dostępne są karty języków i AI Translate jednym kliknięciem.
* **Przyjazna dla programistów** — haki rozszerzeń `vmcf7_loaded` i `vmcf7_validation_tag_types` pozwalają dodawać własne typy pól; filtry `vmcf7_message`, `vmcf7_default_message`, `vmcf7_placeholders` i `vmcf7_field_label` pozwalają odczytywać komunikaty, ustawiać wartości domyślne dla całej witryny i dodawać własne `{tokens}`.
* **Lekka** — bez zbędnych elementów w kokpicie, bez śledzenia, bez nachalnej sprzedaży.

= Obsługiwane typy pól =

* Komunikaty dla pól wymaganych: dowolny wymagany znacznik (`text`, `textarea`, `select`, `checkbox`, `radio`, `file` itd.).
* Komunikaty o nieprawidłowym formacie: pola `email`, `url`, `tel`, `number` (w tym `range`), `date` i `time`.
* Własne komunikaty o błędach walidacji: `file` (sprawdzanie rozmiaru/typu), `acceptance` (pole niezaznaczone/brak zgody) i `quiz` (błędna odpowiedź).
* HTML w komunikatach jest dozwolony i oczyszczany za pomocą `wp_kses_post()`.
* Własne reguły regex oraz minimalnej/maksymalnej długości są dostępne dla każdego typu pola z wyjątkiem `file`.

= Tłumaczenia =

Wtyczka zawiera plik `.pot` i jest już przetłumaczona na niemiecki, hiszpański, francuski, włoski, japoński, polski, portugalski (Brazylia), rosyjski i ukraiński. [Pomóż przetłumaczyć ją na swój język.](https://translate.wordpress.org/projects/wp-plugins/validation-muse-for-contact-form-7)

== Installation ==

1. Wtyczkę można zainstalować z ekranu **Wtyczki → Dodaj wtyczkę**, wyszukując *Validation Muse for Contact Form 7*, albo wgrać katalog `validation-muse-for-contact-form-7` do `/wp-content/plugins/`.
2. Należy włączyć wtyczkę. Contact Form 7 musi być już włączony — w przeciwnym razie Validation Muse wyłączy się samoczynnie i wyświetli powiadomienie w kokpicie.
3. Należy otworzyć edycję dowolnego formularza Contact Form 7, przejść na kartę **Własna walidacja**, włączyć ją i wpisać własne komunikaty.
4. (Opcjonalnie) Można zainstalować wtyczkę tłumaczeń Flavor, aby tłumaczyć komunikaty na poszczególne języki z pomocą AI.

== Frequently Asked Questions ==

= Czy to działa z Contact Form 7 6.x i Schema-based Validation (SWV)? =

Tak. Od wersji 1.3.0 Validation Muse podpina się z priorytetem 20 (po rdzeniu CF7) i za pomocą Reflection podmienia tekst błędu SWV w polach już oznaczonych jako nieprawidłowe. Od wersji 1.6.1 standardowe reguły (wymagane, email/url/tel/number/date/time, minimalna/maksymalna długość) są też dodawane bezpośrednio do schematu SWV w CF7, więc walidacja po stronie klienta (JS na froncie witryny) od razu pokazuje własne komunikaty — przez natywny silnik walidacji CF7, bez dodatkowego skryptu tej wtyczki.

= Czym ta wtyczka różni się od innych wtyczek walidacji dla CF7? =

Validation Muse to jedyna wtyczka walidacji dla CF7, która (1) od razu jest zgodna z SWV w CF7 6.x — zarówno po stronie serwera, jak i we własnej walidacji CF7 po stronie klienta, (2) zapisuje komunikaty w metadanych wpisu formularza, dzięki czemu są przechowywane razem z formularzem (zgodnie z duplikowaniem formularzy w CF7 i wtyczkami importu/eksportu), oraz (3) obsługuje komunikaty w poszczególnych językach za pomocą WPML, Polylang lub wtyczki tłumaczeń Flavor (z tłumaczeniem AI jednym kliknięciem).

= Czy komunikaty walidacji można tłumaczyć na poszczególne języki? =

Tak. WPML (String Translation) i Polylang są obsługiwane natywnie: komunikaty są rejestrowane do tłumaczenia przy każdym zapisie formularza, a karta **Własna walidacja** zawiera odnośnik do ekranu, na którym można je przetłumaczyć. Z wtyczką tłumaczeń Flavor Validation Muse pokazuje ponadto karty języków w edytorze formularza oraz przycisk *AI Translate*; te tłumaczenia są zapisywane w bazie danych Flavor, a odinstalowanie Validation Muse je usuwa.

= Które typy pól obsługują własne komunikaty o nieprawidłowym formacie lub błędach walidacji? =

* `email`, `url`, `tel`, `number` (w tym `range`), `date` i `time` obsługują własne komunikaty o nieprawidłowym formacie.
* `file` obsługuje własne komunikaty o błędach walidacji rozmiaru/typu pliku.
* `acceptance` obsługuje własne komunikaty o błędach, gdy pole wyboru nie zostało zaznaczone.
* `quiz` obsługuje własne komunikaty o błędach, gdy odpowiedź jest nieprawidłowa.
* Każde wymagane pole dowolnego typu może mieć własny komunikat dla pola wymaganego.

= Gdzie są przechowywane komunikaty? =

W metadanych wpisu każdego formularza. Są przechowywane razem z formularzem, więc duplikowanie formularza (funkcja wbudowana w CF7) zachowuje komunikaty. CF7 nie ma natywnego eksportu, ale zewnętrzne wtyczki importu/eksportu dla CF7 odczytują metadane wpisu — przenoszenie między witrynami działa więc bez osobnego kroku importu.

= Czy w komunikatach walidacji można używać HTML? =

Tak, podstawowy HTML jest dozwolony i oczyszczany za pomocą `wp_kses_post()`.

= Czy ta wtyczka wymaga Contact Form 7? =

Tak. CF7 musi być zainstalowany i włączony. Jeśli CF7 brakuje, wtyczka wyświetla powiadomienie w kokpicie i wyłącza się samoczynnie.

= Czy jest strona ustawień? =

Nie. Konfiguracja znajduje się w każdym formularzu, na karcie **Własna walidacja**. Globalnej strony ustawień celowo nie ma — każdy formularz zachowuje własne komunikaty.

= Czy wtyczka śledzi lub wysyła jakiekolwiek dane? =

Nie. Validation Muse nie wysyła żadnych żądań zewnętrznych. Opcjonalny przycisk AI Translate (integracja z Flavor) korzysta z dostawcy skonfigurowanego we wtyczce Flavor.

== Screenshots ==

1. Karta **Własna walidacja** w edytorze Contact Form 7 — jedna karta dla każdego pola z komunikatami, dodatkowymi regułami (długość, wzorzec z testerem na żywo, wymagane tylko czasami) i podglądem tego, co zobaczą odwiedzający.
2. **Kopiowanie, kopia zapasowa i ponowne użycie** — kopiowanie reguł z innego formularza, pobieranie/wczytywanie ich jako pliku, zachowanie szablonu lub użycie we wszystkich formularzach.
3. Komunikat dla pola wymaganego wyświetlony na froncie witryny.
4. Komunikat o nieprawidłowym formacie dla pola e-mail wyświetlony na froncie witryny.

== Changelog ==

= 1.8.0 =
* Nowość: przeprojektowana karta „Własna walidacja”. Każde pole to karta z etykietą, typem i informacją, czy zostało dostosowane; dodatkowe reguły (długość, wzorzec, wymagane tylko czasami) kryją się za jednym przełącznikiem „Dodatkowe reguły”; każda karta pokazuje, co zobaczą odwiedzający. Wyszukiwanie, „Pokaż tylko dostosowane pola” oraz rozwijanie/zwijanie wszystkich ułatwiają pracę z długimi formularzami.
* Nowość: gotowe przykłady wzorców oraz pole „Test: wpisz przykładową odpowiedź”, które sprawdza wzorzec tak samo, jak robi to witryna.
* Nowość: narzędzia kopiowania, kopii zapasowej, szablonu i „Użycie we wszystkich formularzach” są pogrupowane i opisane, proszą o potwierdzenie na miejscu i pokazują wynik w panelu zamiast w wyskakujących oknach przeglądarki.
* Nowość: gdy włączona jest wtyczka WPML String Translation lub Polylang, karta zawiera bezpośredni odnośnik do ekranu, na którym tłumaczy się komunikaty.
* Nowość: własny komunikat „błędna odpowiedź” dla pól quizu oraz „niewłaściwy plik” dla pól pliku.
* Nowość: `{min}` i `{max}` działają w każdym komunikacie; filtry dla programistów `vmcf7_default_message`, `vmcf7_placeholders`, `vmcf7_field_label`, `vmcf7_supports_text_rules` i `vmcf7_swv_invalid_rules`.
* Nowość: wygląd błędów na froncie witryny można zmieniać za pomocą własności niestandardowych CSS (`--vmcf7-error-color`, `--vmcf7-error-font-size` i innych).
* Nowość: tłumaczenia na japoński, włoski i polski.
* Poprawka: edytor Contact Form 7 nie pyta już „Opuścić stronę? Wprowadzone zmiany mogą nie zostać zapisane”, gdy nic nie zostało zmienione.
* Poprawka: własne komunikaty i limity długości znów trafiają do walidacji po stronie klienta w Contact Form 7 6.2+ (w wersji 6.2 hak schematu po cichu nic nie robił).
* Poprawka: z zapisywanych wzorców i komunikatów usuwane były ukośniki wsteczne (`^\d+$` zapisywano jako `^d+$`), także przy imporcie, kopiowaniu i w narzędziu „Użycie we wszystkich formularzach”.
* Poprawka: nieprawidłowy wzorzec nie odrzuca już każdej odpowiedzi; reguła wzorca lub długości w polach wyboru i listach wielokrotnego wyboru nie powoduje już błędu przy wysyłaniu.
* Poprawka: skopiowanie reguł formularza do tego samego formularza już ich nie usuwa; narzędzia zestawów reguł przyjmują wyłącznie formularze Contact Form 7, które użytkownik może edytować, a importowane pliki są sprawdzane i mają ograniczony rozmiar.
* Poprawka: WPML/Polylang otrzymywały każdy komunikat z opóźnieniem o jeden zapis, a Polylang nie pokazywał komunikatów na ekranie tłumaczeń, a następnie pokazywał je dwukrotnie.
* Poprawka: własny komunikat dla pola wymaganego dotyczy teraz także pól wyboru zgody (acceptance); reguła „wymagane, jeśli” działa, gdy nazwa drugiego pola zawiera wielkie litery.
* Poprawka: AI Translate nie wysyła już wzorców i liczb do tłumacza.
* Poprawka: odinstalowanie usuwa wszystkie opcje wtyczki; style frontu witryny są wczytywane także dla formularzy umieszczonych w widżetach i szablonach.

= 1.7.2 =
* Poprawka: otwarcie ekranu Formularze → Dodaj nowy przy włączonej wtyczce Flavor nie kończy się już błędem krytycznym. Formularz, który nie został jeszcze zapisany, nie ma identyfikatora, więc Validation Muse pomija teraz jego tłumaczenia do momentu zapisania formularza.

= 1.7.1 =
* Poprawka: archiwum, które GitHub i Composer budują z tagu, nie zawiera już testów, narzędzi deweloperskich ani dokumentacji, więc projekt instalujący wtyczkę z Gita otrzymuje tylko samą wtyczkę. Bez zmian w działaniu wtyczki.

= 1.7.0 =
* Nowość: filtr `vmcf7_message` przekazuje wpisany tutaj komunikat innemu kodowi: `apply_filters( 'vmcf7_message', $default, $form_id, $field_name, $type )` zwraca własny komunikat albo `$default`, gdy żadnego nie wpisano lub własna walidacja jest dla formularza wyłączona. Dla odwiedzających nic się nie zmienia, dopóki inny kod o to nie poprosi.

= 1.6.5 =
* Nowość: jednorazowa, możliwa do odrzucenia prośba o dodanie recenzji na WordPress.org. Pojawia się dopiero tydzień po pierwszym zapisaniu własnego komunikatu walidacji, tylko u użytkowników, którzy mogą edytować formularze Contact Form 7, i tylko na ekranach Contact Form 7. „Może później” odkłada ją na 30 dni, „Nie pytaj ponownie” ukrywa ją na stałe. Nic nie jest nigdzie wysyłane.

= 1.6.4 =
* Przetestowano z WordPressem 7.1 (sprawdzono na 7.1.1 z Contact Form 7 6.1).
* Poprawka: usunięto wywołania przestarzałe w PHP 8.5 (`finfo_close()` oraz `ReflectionProperty::setAccessible()` w PHP 8.1+), więc wtyczka nie wywołuje już ostrzeżeń o przestarzałych funkcjach w PHP 8.5.

= 1.6.3 =
* i18n: całkowicie wygenerowano na nowo szablon tłumaczeń `.pot` — katalog był zamrożony od wersji 1.2.0 i obejmował tylko około 20% tekstów wtyczki do przetłumaczenia (reguł regex/długości, szablonów reguł, błędów AI Translate i innych nie dało się wcześniej wyodrębnić). Wszystkie sześć dostarczanych języków (niemiecki, hiszpański, francuski, portugalski, rosyjski, ukraiński) jest teraz w pełni przetłumaczonych zgodnie z aktualnym interfejsem.
* Dokumentacja: zsynchronizowano wszystkie przetłumaczone pliki readme — lista funkcji, obsługiwane typy pól i FAQ odzwierciedlają teraz funkcje wersji 1.6.0–1.6.2 (własne reguły regex/długości, „wymagane, jeśli”, natywna walidacja SWV po stronie klienta, szablony reguł); ich sekcje Changelog/Upgrade Notice, dotąd zatrzymane na wersji 1.4.2, obejmują teraz wszystkie wydania do 1.6.2.
* Poprawka: dodano brakujący komentarz `translators:` do tekstu z symbolem zastępczym (jakość kodu i18n, bez zmian w działaniu).

= 1.6.2 =
* Poprawka: komunikaty zapisane przez wersje sprzed zmiany nazwy wtyczki (przechowywane pod starym prefiksem metadanych `_cf7cv_`) były niewidoczne dla obecnego kodu. Jednorazowa migracja automatycznie zmienia teraz ich prefiks na `_vmcf7_`, przywracając utracone komunikaty. Bez ryzyka kolizji, uruchamia się tylko raz.

= 1.6.1 =
* Poprawka: zduplikowane/nadpisywane komunikaty walidacji na froncie witryny — dzięki integracji z natywnym silnikiem SWV w CF7.
* Poprawka: wysuwane sekcje panelu administracyjnego i karty podglądu wyświetlały się rozwinięte, ponieważ panel edytora CF7 usuwał style inline.

= 1.6.0 =
* Nowość: własne reguły walidacji regex oraz minimalnej/maksymalnej długości dla każdego pola, każda z własnym komunikatem.
* Nowość: komunikaty walidacji dla pól `file`, `acceptance` i `quiz`; warunkowe komunikaty „wymagane, jeśli”.
* Nowość: znaczniki zastępcze w komunikatach: `{field_label}`, `{min}`, `{max}`.
* Nowość: walidacja inline po stronie klienta odzwierciedlająca komunikaty serwera, z dostępnością dzięki `aria-describedby`.
* Nowość: podgląd komunikatów na żywo, kopiowanie komunikatów między formularzami, import/eksport zestawów komunikatów w formacie JSON oraz lint w edytorze.
* Nowość: rejestracja ciągów znaków w WPML/Polylang obok mostu Flavor; import/eksport tłumaczeń.

= 1.5.0 =
* Minimalna wersja PHP podniesiona do 8.0; minimalna wersja WordPressa podniesiona do 6.0; przetestowano do WordPressa 7.0.
* Nowość: własne komunikaty walidacji dla pól `time`.
* Czytelniejsze informacje o błędach AI Translate w edytorze formularza.
* Podmiana komunikatów SWV oparta na Reflection zapisuje teraz niepowodzenia w dzienniku przy włączonym WP_DEBUG, co ułatwia diagnostykę.
* Ulepszona obsługa oczekujących tłumaczeń (koniec z cichym użyciem wartości zastępczej) z pamięcią podręczną w obrębie żądania.
* Kod unowocześniony do idiomów PHP 8; wzmocnione zabezpieczanie danych wyjściowych; dodano pierwszy zestaw testów jednostkowych.

= 1.4.2 =
* Plugin URI: wskazuje teraz dedykowaną stronę docelową pod adresem https://plugins.symonov.com/validation-muse-for-cf7/
* Bez zmian w kodzie i działaniu

= 1.4.1 =
* Readme: przepisane od nowa z naciskiem na główne zalety, dla lepszej widoczności w wyszukiwarkach
* Tagi: ogólne `messages`/`forms`/`customization` zastąpiono precyzyjnymi `contact-form-7`, `cf7`, `validation`, `error-message`, `multilingual`
* FAQ: dodano pozycje o zgodności z SWV w CF7 6.x, porównaniu z innymi wtyczkami walidacji dla CF7 oraz wielojęzyczności dzięki Flavor

= 1.4.0 =
* Dodano obsługę wielu języków dzięki integracji z wtyczką tłumaczeń Flavor
* Komunikaty walidacji można teraz tłumaczyć na poszczególne języki w edytorze formularza
* Karty języków pojawiają się automatycznie, gdy wtyczka Flavor jest włączona
* Przycisk AI Translate do tłumaczenia maszynowego wszystkich komunikatów jednym kliknięciem
* Tłumaczenia są zapisywane w bazie danych Flavor, dzięki czemu dane wtyczki pozostają przenośne
* Zero narzutu, gdy Flavor nie jest zainstalowany — wszystkie wywołania są poprzedzone sprawdzeniem class_exists()
* Tłumaczenia Flavor są usuwane przy odinstalowaniu wtyczki

= 1.3.0 =
* Naprawiono zgodność z SWV (Schema-based Validation) w Contact Form 7 6.x
* Filtry walidacji działają teraz z priorytetem 20 (po rdzeniu CF7), aby podmieniać komunikaty o błędach SWV
* Dodano opartą na Reflection podmianę błędów w polach już oznaczonych jako nieprawidłowe
* Własne komunikaty poprawnie zastępują teraz domyślny tekst CF7 „The field is required.”

= 1.2.1 =
* Naprawiono niezgodność nazw zmiennych powodującą błąd „No required fields”

= 1.2.0 =
* Zrefaktoryzowano kod zgodnie ze standardami kodowania WordPressa (WordPress Coding Standards)
* Zreorganizowano strukturę plików dla łatwiejszego utrzymania
* Dodano bloki PHPDoc do wszystkich funkcji i metod
* Poprawiono dostępność dzięki etykietom ARIA
* Naprawiono niespójność prefiksu w JavaScripcie
* Poprawiono skrypt odinstalowania, aby używał właściwego prefiksu metadanych
* Dodano haki rozszerzeń (`vmcf7_loaded`, `vmcf7_validation_tag_types`)
* Zmieniono sprawdzane uprawnienie z `manage_options` na `wpcf7_edit_contact_forms`
* Zaktualizowano nazwę pliku POT, aby odpowiadała domenie tekstowej

= 1.1.2 =
* Zmieniono nazwę wtyczki.

= 1.1.1 =
* Dodano plik .gitignore.

= 1.1.0 =
* Dodano materiały do repozytorium WordPressa (readme, licencja, plik POT).
* Przebudowano haki walidacji, aby zastępowały komunikaty o polach wymaganych i nieprawidłowych bez polegania na filtrach AJAX.
* Wzmocniono oczyszczanie danych, wczytywanie domeny tekstowej i sprzątanie przy odinstalowaniu przed wydaniem.

= 1.0.1 =
* Pierwsza publiczna wersja dołączona do projektu.

== Upgrade Notice ==

= 1.8.0 =
Przeprojektowana karta „Własna walidacja”, ponownie działające komunikaty po stronie klienta w Contact Form 7 6.2+ oraz kilka poprawek walidacji. Istniejące reguły i komunikaty pozostają bez zmian.

= 1.7.2 =
Naprawia błąd krytyczny na ekranie Formularze → Dodaj nowy przy włączonej wtyczce Flavor. Bez zmian w działaniu walidacji.

= 1.7.1 =
Tylko pakowanie: archiwum Git nie zawiera już testów ani narzędzi. Bez zmian w działaniu walidacji.

= 1.7.0 =
Dodaje filtr `vmcf7_message`, dzięki któremu inny kod może odczytać wpisane tutaj komunikaty. Bez zmian w działaniu walidacji.

= 1.6.5 =
Dodaje jednorazową, możliwą do odrzucenia prośbę o recenzję (wyświetlaną tydzień po pierwszym zapisaniu własnego komunikatu). Bez zmian w działaniu walidacji.

= 1.6.4 =
Wydanie zgodnościowe: przetestowano z WordPressem 7.1, bez ostrzeżeń w PHP 8.5. Bez zmian w działaniu.

= 1.6.3 =
Wydanie dokumentacyjne i tłumaczeniowe — bez zmian funkcjonalnych i zmian w działaniu. W pełni odświeża przetłumaczone pliki readme oraz katalog tłumaczeń .pot/.po/.mo.

= 1.6.2 =
Przywraca komunikaty walidacji zapisane przez wersje sprzed 1.2.0 (stary prefiks metadanych `_cf7cv_`) dzięki jednorazowej, automatycznej migracji. Bezpieczne, nie wymaga żadnych działań.

= 1.6.1 =
Naprawia zduplikowane/nadpisywane komunikaty walidacji na froncie witryny oraz regresje układu panelu administracyjnego z wersji 1.6.0. Zalecane dla wszystkich użytkowników wersji 1.6.0.

= 1.6.0 =
Dodaje własne reguły regex/długości, więcej typów pól, komunikaty „wymagane, jeśli”, odzwierciedlenie po stronie klienta, podgląd na żywo, import/eksport/szablony reguł oraz obsługę WPML/Polylang. Migracja danych nie jest wymagana.

= 1.5.0 =
Podnosi minimalną wersję PHP do 8.0 i minimalną wersję WordPressa do 6.0. Przed aktualizacją należy sprawdzić środowisko hostingowe.

= 1.4.2 =
Plugin URI wskazuje teraz dedykowaną stronę docelową w plugins.symonov.com. Bez zmian w kodzie.

= 1.4.1 =
Wydanie wyłącznie dokumentacyjne. Odświeżone readme ułatwia poznanie funkcji.

= 1.4.0 =
Dodaje obsługę wielu języków dzięki wtyczce tłumaczeń Flavor oraz tłumaczenie komunikatów walidacji przez AI jednym kliknięciem. Migracja danych nie jest wymagana.

= 1.3.0 =
Przywraca zgodność z Schema-based Validation (SWV) w Contact Form 7 6.x. Zalecane dla wszystkich użytkowników CF7 6.x.
