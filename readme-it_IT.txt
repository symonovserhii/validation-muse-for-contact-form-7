=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact-form-7, cf7, validation, error-message, multilingual
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Regole e messaggi di validazione personalizzati per Contact Form 7 — regex, lunghezza, required-if, per campo, compatibile con SWV di CF7 6.x, multilingua.

== Description ==

**Validation Muse** ti permette di scrivere le tue regole di validazione e i tuoi messaggi di errore per ogni campo di Contact Form 7 — direttamente nell'editor del modulo, per modulo e per campo. Niente codice, nessuna pagina di impostazioni globale, nessun trucco JavaScript.

La maggior parte dei plugin di validazione per CF7 ha smesso di funzionare quando Contact Form 7 6.x ha introdotto la **Schema-based Validation (SWV)**. Validation Muse esegue i suoi filtri con priorità 20 (dopo il core di CF7) e usa Reflection per sostituire il testo di errore SWV nei campi già invalidati, così i tuoi testi personalizzati vengono mostrati davvero — anche con il nuovo motore di validazione.

= Perché Validation Muse =

* **Compatibile con SWV di CF7 6.x** — funziona con il nuovo motore Schema-based Validation, non solo con gli hook legacy.
* **Regole regex e di lunghezza personalizzate** — definisci per ogni campo espressioni regolari personalizzate e regole di lunghezza minima e massima, ciascuna con il proprio messaggio di errore.
* **Regole condizionali "Required-If"** — rendi un campo obbligatorio solo quando un campo collegato è compilato/selezionato.
* **Integrazione SWV lato client** — le regole di validazione standard (obbligatorio, email, lunghezza) vengono inserite nel motore SWV nativo di CF7 per un riscontro immediato sul frontend con pieno supporto all'accessibilità (A11y).
* **Import/export di set di regole e modelli** — esporta/importa le regole di validazione in JSON, copiale da altri moduli e applica in blocco modelli globali.
* **Token segnaposto** — usa `{field_label}`, `{min}` e `{max}` nei messaggi di validazione per generare testi dinamici.
* **Per modulo, per campo** — ogni modulo mantiene i propri messaggi; nessuna sovrascrittura globale.
* **Salvati nei post meta** — i messaggi vivono con il modulo e sono compatibili con la duplicazione dei moduli di CF7 e con i plugin di import/export per CF7 di terze parti.
* **Multilingua con WPML, Polylang e Flavor** — traduce le regole tramite gli hook standard di WPML/Polylang e offre schede per lingua + AI Translate con un clic quando Flavor è attivo.
* **Pensato per gli sviluppatori** — gli hook di estensione `vmcf7_loaded` e `vmcf7_validation_tag_types` ti permettono di aggiungere tipi di campo personalizzati; i filtri `vmcf7_message`, `vmcf7_default_message`, `vmcf7_placeholders` e `vmcf7_field_label` ti permettono di leggere i messaggi, impostare valori predefiniti per tutto il sito e aggiungere i tuoi `{tokens}`.
* **Leggero** — nessun appesantimento della bacheca, nessun tracciamento, nessun upsell.

= Tipi di campo supportati =

* Messaggi per i campi obbligatori: qualsiasi tag obbligatorio (`text`, `textarea`, `select`, `checkbox`, `radio`, `file`, ecc.).
* Messaggi per formato non valido: campi `email`, `url`, `tel`, `number` (incluso `range`), `date` e `time`.
* Messaggi di errore personalizzati quando la validazione fallisce: `file` (controlli su dimensione/tipo), `acceptance` (casella non selezionata/non accettata) e `quiz` (risposta errata).
* L'HTML nei messaggi è consentito e viene sanificato tramite `wp_kses_post()`.
* Le regole regex e di lunghezza min/max personalizzate sono disponibili per ogni tipo di campo tranne `file`.

= Traduzioni =

Il plugin include un file `.pot` ed è già tradotto in tedesco, spagnolo, francese, italiano, giapponese, polacco, portoghese (Brasile), russo e ucraino. [Aiuta a tradurlo nella tua lingua.](https://translate.wordpress.org/projects/wp-plugins/validation-muse-for-contact-form-7)

== Installation ==

1. Installa da **Plugin → Aggiungi nuovo** cercando *Validation Muse for Contact Form 7*, oppure carica la cartella `validation-muse-for-contact-form-7` in `/wp-content/plugins/`.
2. Attiva il plugin. Contact Form 7 deve essere già attivo — in caso contrario Validation Muse si disattiva da solo mostrando un avviso nella bacheca.
3. Modifica un qualsiasi modulo di Contact Form 7, apri la scheda **Validazione personalizzata**, attivala e scrivi i tuoi messaggi.
4. (Facoltativo) Installa il plugin di traduzione Flavor per tradurre i messaggi in ogni lingua con l'aiuto dell'IA.

== Frequently Asked Questions ==

= Funziona con Contact Form 7 6.x e la Schema-based Validation (SWV)? =

Sì. Dalla versione 1.3.0, Validation Muse si aggancia con priorità 20 (dopo il core di CF7) e usa Reflection per sostituire il testo di errore SWV nei campi già invalidati. Dalla 1.6.1, le regole standard (obbligatorio, email/url/tel/number/date/time, lunghezza min/max) vengono inoltre inserite direttamente nello schema SWV di CF7, così la validazione lato client (JS del frontend) mostra subito i tuoi messaggi personalizzati — tramite il motore di validazione nativo di CF7, senza alcuno script aggiuntivo di questo plugin.

= In cosa è diverso dagli altri plugin di validazione per CF7? =

Validation Muse è l'unico plugin di validazione per CF7 che (1) è compatibile fin da subito con SWV di CF7 6.x — sia lato server sia nella validazione lato client di CF7, (2) salva i messaggi nei post meta del modulo, così vivono con il modulo (compatibile con la duplicazione dei moduli di CF7 e con i plugin di import/export), e (3) supporta messaggi diversi per ogni lingua tramite WPML, Polylang o il plugin di traduzione Flavor (con traduzione IA con un clic).

= Posso tradurre i messaggi di validazione in ogni lingua? =

Sì. WPML (String Translation) e Polylang sono supportati nativamente: i tuoi messaggi vengono registrati per la traduzione ogni volta che salvi il modulo, e la scheda **Validazione personalizzata** rimanda alla schermata in cui tradurli. Con il plugin di traduzione Flavor, Validation Muse mostra inoltre le schede per lingua nell'editor del modulo e un pulsante *AI Translate*; queste traduzioni sono salvate nel database di Flavor e la disinstallazione di Validation Muse le rimuove.

= Quali tipi di campo supportano messaggi personalizzati per formato non valido o validazione fallita? =

* `email`, `url`, `tel`, `number` (incluso `range`), `date` e `time` supportano messaggi personalizzati per formato non valido.
* `file` supporta messaggi di errore personalizzati per la validazione di dimensione/tipo del file.
* `acceptance` supporta messaggi di errore personalizzati quando la casella non viene accettata.
* `quiz` supporta messaggi di errore personalizzati quando la risposta è errata.
* Qualsiasi campo obbligatorio, di qualunque tipo, può avere un messaggio personalizzato per il campo obbligatorio.

= Dove vengono salvati i messaggi? =

Nei post meta di ciascun modulo. Vivono con il modulo, quindi duplicando un modulo (funzione integrata in CF7) i messaggi vengono mantenuti. CF7 non ha un'esportazione nativa, ma i plugin di import/export per CF7 di terze parti leggono i post meta — quindi le migrazioni tra siti funzionano senza un passaggio di importazione separato.

= Posso usare l'HTML nei messaggi di validazione? =

Sì, l'HTML di base è consentito e viene sanificato tramite `wp_kses_post()`.

= Questo plugin richiede Contact Form 7? =

Sì. CF7 deve essere installato e attivo. Se CF7 manca, il plugin mostra un avviso nella bacheca e si disattiva da solo.

= C'è una pagina di impostazioni? =

No. La configurazione si trova all'interno di ogni modulo, nella scheda **Validazione personalizzata**. Per scelta non esiste una pagina di impostazioni globale — ogni modulo mantiene i propri messaggi.

= Il plugin traccia o invia dati? =

No. Validation Muse non effettua richieste esterne. Il pulsante facoltativo AI Translate (integrazione con Flavor) passa attraverso il provider configurato in Flavor.

== Screenshots ==

1. La scheda **Validazione personalizzata** nell'editor di Contact Form 7 — una scheda per ogni campo con i messaggi, le regole aggiuntive (lunghezza, modello con test in tempo reale, obbligatorio solo a volte) e un'anteprima di cosa vedranno i visitatori.
2. **Copia, backup e riutilizzo** — copia le regole da un altro modulo, scaricale/caricale come file, conserva un modello o usale in tutti i moduli.
3. Messaggio per campo obbligatorio mostrato sul frontend.
4. Messaggio per formato non valido di un campo email mostrato sul frontend.

== Changelog ==

= 1.8.0 =
* Novità: la scheda Validazione personalizzata è stata ridisegnata. Ogni campo è una scheda con etichetta, tipo e indicazione se è personalizzato; le regole aggiuntive (lunghezza, modello, obbligatorio solo a volte) si trovano dietro un unico interruttore "Regole aggiuntive"; ogni scheda mostra cosa vedranno i visitatori. La ricerca, "Mostra solo i campi personalizzati" e apri/chiudi tutto aiutano nei moduli lunghi.
* Novità: esempi di modelli già pronti e una casella "Provalo: scrivi una risposta di esempio" che controlla un modello allo stesso modo del sito.
* Novità: gli strumenti di copia, backup, modello e "Usa in tutti i moduli" sono raggruppati e spiegati, chiedono conferma direttamente nella pagina e mostrano il risultato nel pannello invece che nei pop-up del browser.
* Novità: quando WPML String Translation o Polylang è attivo, la scheda rimanda direttamente alla schermata in cui si traducono i messaggi.
* Novità: messaggio personalizzato "risposta sbagliata" per i campi quiz e messaggio "file non valido" per i campi file.
* Novità: `{min}` e `{max}` funzionano in ogni messaggio; filtri per sviluppatori `vmcf7_default_message`, `vmcf7_placeholders`, `vmcf7_field_label`, `vmcf7_supports_text_rules` e `vmcf7_swv_invalid_rules`.
* Novità: lo stile degli errori sul frontend può essere modificato con le proprietà personalizzate CSS (`--vmcf7-error-color`, `--vmcf7-error-font-size` e altre).
* Novità: traduzioni in giapponese, italiano e polacco.
* Correzione: l'editor di Contact Form 7 non chiede più "Abbandonare il sito? Le modifiche apportate potrebbero non essere salvate" quando non è stato modificato nulla.
* Correzione: i messaggi personalizzati e i limiti di lunghezza raggiungono di nuovo la validazione lato client di Contact Form 7 6.2+ (con la 6.2 l'hook dello schema non faceva nulla, senza segnalarlo).
* Correzione: i backslash venivano rimossi dai modelli e dai messaggi salvati (`^\d+$` veniva salvato come `^d+$`), anche con importazione, copia e "Usa in tutti i moduli".
* Correzione: un modello non valido non rifiuta più ogni risposta; una regola di modello o di lunghezza su caselle di controllo o selezioni multiple non causa più un errore all'invio.
* Correzione: copiare le regole di un modulo sullo stesso modulo non le cancella più; gli strumenti per i set di regole accettano solo moduli di Contact Form 7 che l'utente può modificare, e i file di importazione vengono controllati e limitati in dimensione.
* Correzione: WPML/Polylang riceveva ogni messaggio con un salvataggio di ritardo; Polylang non elencava i messaggi nella sua schermata Traduzioni e poi li elencava due volte.
* Correzione: il messaggio personalizzato per il campo obbligatorio ora si applica anche alle caselle di accettazione; required-if funziona quando il nome dell'altro campo contiene lettere maiuscole.
* Correzione: AI Translate non invia più modelli e numeri al traduttore.
* Correzione: la disinstallazione rimuove tutte le opzioni del plugin; gli stili del frontend vengono caricati anche per i moduli inseriti in widget e template.

= 1.7.2 =
* Correzione: l'apertura di Contatti → Aggiungi nuovo con Flavor attivo non si interrompe più con un errore fatale. Un modulo non ancora salvato non ha un ID, e ora Validation Muse salta le sue traduzioni finché il modulo non viene salvato.

= 1.7.1 =
* Correzione: l'archivio che GitHub e Composer generano da un tag non contiene più test, strumenti di sviluppo e documentazione, quindi un progetto che installa il plugin da Git ottiene solo il plugin. Nessuna modifica al comportamento del plugin.

= 1.7.0 =
* Novità: il filtro `vmcf7_message` passa ad altro codice un messaggio scritto qui: `apply_filters( 'vmcf7_message', $default, $form_id, $field_name, $type )` restituisce il messaggio personalizzato, oppure `$default` quando non ne è stato scritto nessuno o la validazione personalizzata è disattivata per il modulo. Per i visitatori non cambia nulla finché altro codice non lo richiede.

= 1.6.5 =
* Novità: una richiesta, mostrata una sola volta e ignorabile, di lasciare una recensione su WordPress.org. Compare solo una settimana dopo il primo salvataggio di un messaggio di validazione personalizzato, solo agli utenti che possono modificare i moduli di Contact Form 7 e solo nelle schermate di Contact Form 7. "Forse più tardi" la rimanda di 30 giorni, "Non chiederlo più" la nasconde definitivamente. Non viene inviato nulla da nessuna parte.

= 1.6.4 =
* Testato con WordPress 7.1 (verificato su 7.1.1 con Contact Form 7 6.1).
* Correzione: rimosse le chiamate deprecate in PHP 8.5 (`finfo_close()` e `ReflectionProperty::setAccessible()` su PHP 8.1+), così il plugin non genera più avvisi di deprecazione su PHP 8.5.

= 1.6.3 =
* i18n: rigenerato completamente il modello di traduzione `.pot` — il catalogo era fermo alla 1.2.0 e copriva solo circa il 20% delle stringhe traducibili del plugin (regole regex/di lunghezza, modelli di regole, errori di AI Translate e altro non erano mai stati estraibili prima). Tutte e sei le lingue incluse (tedesco, spagnolo, francese, portoghese, russo, ucraino) sono ora completamente tradotte rispetto all'interfaccia attuale.
* Documentazione: sincronizzati tutti i file readme tradotti — l'elenco delle funzionalità, i tipi di campo supportati e le FAQ ora riflettono le funzionalità delle versioni 1.6.0–1.6.2 (regole regex/di lunghezza personalizzate, required-if, validazione SWV nativa lato client, modelli di regole); le loro sezioni Changelog/Upgrade Notice, prima ferme alla 1.4.2, ora coprono ogni versione fino alla 1.6.2.
* Correzione: aggiunto un commento `translators:` mancante per una stringa con segnaposto (qualità del codice i18n, nessuna modifica al comportamento).

= 1.6.2 =
* Correzione: i messaggi salvati dalle versioni precedenti alla rinomina del plugin (memorizzati con il vecchio prefisso meta `_cf7cv_`) erano invisibili al codice attuale. Una migrazione eseguita una sola volta ora li rinomina automaticamente con il prefisso `_vmcf7_`, ripristinando i messaggi persi. Sicura rispetto alle collisioni, viene eseguita una volta sola.

= 1.6.1 =
* Correzione: messaggi di validazione duplicati/sovrascritti sul frontend, risolti con l'integrazione nel motore SWV nativo di CF7.
* Correzione: i riquadri a comparsa e le schede di anteprima del pannello di amministrazione venivano mostrati espansi perché il pannello dell'editor di CF7 rimuoveva gli stili inline.

= 1.6.0 =
* Novità: regole di validazione regex e di lunghezza min/max personalizzate per ogni campo, ciascuna con il proprio messaggio.
* Novità: messaggi di validazione per i campi `file`, `acceptance` e `quiz`; messaggi condizionali "required-if".
* Novità: token segnaposto per i messaggi `{field_label}`, `{min}`, `{max}`.
* Novità: validazione inline lato client che rispecchia i messaggi del server, con accessibilità tramite `aria-describedby`.
* Novità: anteprima dei messaggi in tempo reale, copia dei messaggi tra moduli, import/export JSON dei set di messaggi e lint nell'editor.
* Novità: registrazione delle stringhe per WPML/Polylang accanto al bridge per Flavor; import/export delle traduzioni.

= 1.5.0 =
* Versione minima di PHP portata a 8.0; versione minima di WordPress portata a 6.0; testato fino a WordPress 7.0.
* Novità: messaggi di validazione personalizzati per i campi `time`.
* Segnalazione più chiara degli errori di AI Translate nell'editor del modulo.
* La sostituzione dei messaggi SWV basata su Reflection ora registra gli errori nel log con WP_DEBUG attivo, per una diagnosi più semplice.
* Gestione migliorata delle traduzioni in sospeso (niente più fallback silenzioso) con cache per singola richiesta.
* Codice modernizzato secondo gli idiomi di PHP 8; escaping dell'output rafforzato; aggiunta la prima suite di unit test.

= 1.4.2 =
* Plugin URI: ora punta alla landing page dedicata all'indirizzo https://plugins.symonov.com/validation-muse-for-cf7/
* Nessuna modifica al codice o al comportamento

= 1.4.1 =
* Readme: riscrittura incentrata sui punti di forza per una migliore reperibilità SEO
* Tag: sostituiti i generici `messages`/`forms`/`customization` con i più mirati `contact-form-7`, `cf7`, `validation`, `error-message`, `multilingual`
* FAQ: aggiunte voci su compatibilità con SWV di CF7 6.x, confronto con altri plugin di validazione per CF7 e multilingua tramite Flavor

= 1.4.0 =
* Aggiunto il supporto multilingua tramite l'integrazione con il plugin di traduzione Flavor
* I messaggi di validazione ora possono essere tradotti in ogni lingua nell'editor del modulo
* Le schede per lingua compaiono automaticamente quando il plugin Flavor è attivo
* Pulsante AI Translate per la traduzione automatica di tutti i messaggi con un clic
* Traduzioni salvate nel database di Flavor, così i dati del plugin restano portabili
* Nessun overhead quando Flavor non è installato — tutte le chiamate sono protette da controlli class_exists()
* Le traduzioni di Flavor vengono rimosse alla disinstallazione del plugin

= 1.3.0 =
* Corretta la compatibilità con SWV (Schema-based Validation) di Contact Form 7 6.x
* I filtri di validazione ora vengono eseguiti con priorità 20 (dopo il core di CF7) per sostituire i messaggi di errore SWV
* Aggiunta la sostituzione degli errori basata su Reflection per i campi già invalidati
* I messaggi personalizzati ora sostituiscono correttamente il testo predefinito di CF7 "The field is required."

= 1.2.1 =
* Corretta un'incongruenza nel nome di una variabile che causava l'errore "No required fields"

= 1.2.0 =
* Codice rifattorizzato per seguire i WordPress Coding Standards
* Struttura dei file riorganizzata per una migliore manutenibilità
* Aggiunti blocchi PHPDoc a tutte le funzioni e a tutti i metodi
* Accessibilità migliorata con etichette ARIA
* Corretta un'incoerenza nel prefisso JavaScript
* Corretto lo script di disinstallazione per usare il prefisso meta corretto
* Aggiunti hook di estensione (`vmcf7_loaded`, `vmcf7_validation_tag_types`)
* Controllo della capability modificato da `manage_options` a `wpcf7_edit_contact_forms`
* Aggiornato il nome del file POT in modo che corrisponda al text domain

= 1.1.2 =
* Modificato il nome del plugin.

= 1.1.1 =
* Aggiunto il file .gitignore.

= 1.1.0 =
* Aggiunti i materiali per la directory dei plugin di WordPress (readme, licenza, file POT).
* Rielaborati gli hook di validazione per sostituire i messaggi per campo obbligatorio e formato non valido senza dipendere dai filtri AJAX.
* Rafforzati sanificazione, caricamento del text domain e pulizia alla disinstallazione in vista del rilascio.

= 1.0.1 =
* Prima versione pubblica inclusa nel progetto.

== Upgrade Notice ==

= 1.8.0 =
Scheda Validazione personalizzata ridisegnata, messaggi lato client di nuovo funzionanti con Contact Form 7 6.2+ e diverse correzioni alla validazione. Le regole e i messaggi esistenti restano invariati.

= 1.7.2 =
Corregge un errore fatale in Contatti → Aggiungi nuovo quando Flavor è attivo. Nessuna modifica al comportamento della validazione.

= 1.7.1 =
Solo packaging: l'archivio Git non include più test e strumenti di sviluppo. Nessuna modifica al comportamento della validazione.

= 1.7.0 =
Aggiunge il filtro `vmcf7_message` con cui altro codice può leggere i messaggi scritti qui. Nessuna modifica al comportamento della validazione.

= 1.6.5 =
Aggiunge una richiesta di recensione mostrata una sola volta e ignorabile (compare una settimana dopo il primo salvataggio di un messaggio personalizzato). Nessuna modifica al comportamento della validazione.

= 1.6.4 =
Versione di compatibilità: testato con WordPress 7.1 e senza avvisi su PHP 8.5. Nessuna modifica al comportamento.

= 1.6.3 =
Versione dedicata a documentazione e traduzioni — nessuna modifica funzionale o di comportamento. Aggiorna completamente i readme tradotti e il catalogo di traduzione .pot/.po/.mo.

= 1.6.2 =
Recupera i messaggi di validazione salvati dalle versioni precedenti alla 1.2.0 (vecchio prefisso meta `_cf7cv_`) tramite una migrazione automatica eseguita una sola volta. Sicura, nessuna azione richiesta.

= 1.6.1 =
Corregge i messaggi di validazione duplicati/sovrascritti sul frontend e le regressioni di layout del pannello di amministrazione introdotte nella 1.6.0. Consigliata a tutti gli utenti della 1.6.0.

= 1.6.0 =
Aggiunge regole regex/di lunghezza personalizzate, altri tipi di campo, messaggi required-if, validazione speculare lato client, anteprima in tempo reale, import/export/modelli di regole e supporto a WPML/Polylang. Nessuna migrazione dei dati necessaria.

= 1.5.0 =
Porta la versione minima di PHP a 8.0 e quella di WordPress a 6.0. Controlla il tuo ambiente di hosting prima di aggiornare.

= 1.4.2 =
Plugin URI ora punta alla landing page dedicata su plugins.symonov.com. Nessuna modifica al codice.

= 1.4.1 =
Versione di sola documentazione. Readme aggiornato per far scoprire più chiaramente le funzionalità.

= 1.4.0 =
Aggiunge il supporto multilingua tramite il plugin di traduzione Flavor e la traduzione IA dei messaggi di validazione con un clic. Nessuna migrazione dei dati necessaria.

= 1.3.0 =
Ripristina la compatibilità con la Schema-based Validation (SWV) di Contact Form 7 6.x. Consigliata a tutti gli utenti di CF7 6.x.
