=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact-form-7, cf7, validation, error-message, multilingual
Requires at least: 6.0
Tested up to: 7.2
Requires PHP: 8.0
Stable tag: 1.8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Reglas y mensajes de validación personalizados para Contact Form 7 — regex, longitud, required-if, por campo, compatible con CF7 6.x SWV, multilingüe.

== Description ==

**Validation Muse** te permite escribir tus propias reglas de validación y mensajes de error para cada campo de Contact Form 7 — directamente en el editor del formulario, por formulario y por campo. Sin código, sin página global de ajustes, sin trucos en JavaScript.

La mayoría de plugins de validación de CF7 dejaron de funcionar cuando Contact Form 7 6.x introdujo **Schema-based Validation (SWV)**. Validation Muse ejecuta sus filtros con prioridad 20 (después del core de CF7) y usa Reflection para reemplazar el texto de error de SWV en campos ya invalidados — así tu copia personalizada aparece realmente, incluso con el nuevo motor de validación.

= Por qué Validation Muse =

* **Compatible con CF7 6.x SWV** — funciona con el nuevo motor Schema-based Validation, no solo con los hooks legacy.
* **Reglas personalizadas de regex y longitud** — define patrones de expresión regular, longitud mínima y máxima por campo, cada una con su propio mensaje de error.
* **Reglas condicionales "Required-If"** — haz que los campos sean obligatorios solo cuando un campo compañero esté completado/marcado.
* **Integración SWV del lado del cliente** — las reglas de validación estándar (required, email, length) se inyectan en el motor SWV nativo de CF7 para feedback instantáneo en el frontend con soporte completo de accesibilidad (A11y).
* **Import/export y plantillas de conjuntos de reglas** — exporta/importa reglas de validación como JSON, cópialas de otros formularios, y aplica plantillas globales en bloque.
* **Tokens de marcador de posición** — usa `{field_label}`, `{min}` y `{max}` en los mensajes de validación para generar textos dinámicos.
* **Por formulario, por campo** — cada formulario guarda sus propios mensajes; sin sobrescritura global.
* **Almacenado en post meta** — los mensajes viven con el formulario, compatibles con la duplicación de formularios de CF7 y con plugins de import/export de terceros.
* **Multilingüe vía WPML, Polylang y Flavor** — traduce las reglas usando los hooks estándar de WPML/Polylang, y cuenta con pestañas de idioma + botón AI Translate de un click cuando Flavor está activo.
* **Amigable para desarrolladores** — los hooks de extensibilidad `vmcf7_loaded` y `vmcf7_validation_tag_types` permiten añadir tipos de campo personalizados; los filtros `vmcf7_message`, `vmcf7_default_message`, `vmcf7_placeholders` y `vmcf7_field_label` permiten leer los mensajes, definir mensajes por defecto para todo el sitio y añadir tus propios `{tokens}`.
* **Ligero** — sin saturación admin, sin tracking, sin upsells.

= Tipos de campo soportados =

* Mensajes de campo obligatorio: cualquier tag required (`text`, `textarea`, `select`, `checkbox`, `radio`, `file`, etc.).
* Mensajes de formato inválido: `email`, `url`, `tel`, `number` (incluyendo `range`), `date` y `time`.
* Mensajes de error personalizados en fallos de validación: `file` (comprobaciones de tamaño/tipo), `acceptance` (estado no aceptado) y `quiz` (respuesta incorrecta).
* HTML dentro de los mensajes está permitido y se sanitiza vía `wp_kses_post()`.
* Las reglas personalizadas de Regex y longitud Mín/Máx están disponibles para todos los tipos de campo excepto `file`.

= Traducciones =

El plugin incluye un archivo `.pot` y ya está traducido al alemán, español, francés, italiano, japonés, polaco, portugués (Brasil), ruso y ucraniano. [Ayuda a traducirlo a tu idioma.](https://translate.wordpress.org/projects/wp-plugins/validation-muse-for-contact-form-7)

== Installation ==

1. Instálalo desde **Plugins → Añadir nuevo** buscando *Validation Muse for Contact Form 7*, o sube la carpeta `validation-muse-for-contact-form-7` a `/wp-content/plugins/`.
2. Activa el plugin. Contact Form 7 debe estar ya activo — Validation Muse se desactivará automáticamente con un aviso si CF7 falta.
3. Edita cualquier formulario de Contact Form 7, abre la pestaña **Validación personalizada**, actívala y escribe tus mensajes.
4. (Opcional) Instala el plugin de traducción Flavor para traducir mensajes por idioma con asistencia de AI.

== Frequently Asked Questions ==

= ¿Funciona con Contact Form 7 6.x y Schema-based Validation (SWV)? =

Sí. Desde la versión 1.3.0, Validation Muse engancha con prioridad 20 (después del core de CF7) y usa Reflection para reemplazar el texto de error de SWV en campos ya invalidados. Desde la 1.6.1, las reglas estándar (required, email/url/tel/number/date/time, longitud mín/máx) también se inyectan directamente en el propio esquema SWV de CF7, así que la validación del lado del cliente (JS del frontend) muestra tus mensajes personalizados al instante — a través del motor de validación nativo de CF7, sin ningún script adicional de este plugin.

= ¿En qué se diferencia de otros plugins de validación CF7? =

Validation Muse es el único plugin de validación CF7 que (1) es compatible con CF7 6.x SWV de fábrica — tanto del lado del servidor como en la propia validación del lado del cliente de CF7, (2) almacena los mensajes en el post meta del formulario para que vivan con él (compatible con la duplicación de formularios CF7 y plugins import/export), y (3) admite mensajes por idioma mediante WPML, Polylang o el plugin Flavor (con traducción AI de un click).

= ¿Puedo traducir los mensajes de validación por idioma? =

Sí. WPML (String Translation) y Polylang son compatibles de forma nativa: tus mensajes se registran para su traducción cada vez que guardas el formulario, y la pestaña **Validación personalizada** enlaza a la pantalla donde los traduces. Con el plugin Flavor, Validation Muse muestra además pestañas de idioma en el editor del formulario y un botón *AI Translate*; esas traducciones se almacenan en la base de datos de Flavor, y desinstalar Validation Muse las limpia.

= ¿Qué tipos de campo soportan mensajes personalizados de formato inválido o fallo de validación? =

* `email`, `url`, `tel`, `number` (incluyendo `range`), `date` y `time` soportan mensajes de formato inválido personalizados.
* `file` soporta mensajes de error de validación de tamaño/tipo de archivo personalizados.
* `acceptance` soporta mensajes de error personalizados cuando la casilla no está aceptada.
* `quiz` soporta mensajes de error personalizados cuando la respuesta es incorrecta.
* Cualquier campo obligatorio de cualquier tipo puede tener un mensaje de obligatoriedad personalizado.

= ¿Dónde se almacenan los mensajes? =

En el post meta de cada formulario. Viven con el formulario, así que duplicar un formulario (función nativa de CF7) mantiene los mensajes. CF7 no tiene export nativo, pero los plugins de import/export de terceros leen el post meta — así que las migraciones entre sitios funcionan sin un paso de import separado.

= ¿Puedo usar HTML en los mensajes de validación? =

Sí, HTML básico está permitido y se sanitiza vía `wp_kses_post()`.

= ¿Este plugin requiere Contact Form 7? =

Sí. CF7 debe estar instalado y activo. El plugin muestra un aviso admin y se autodesactiva si CF7 falta.

= ¿Hay una página de ajustes? =

No. La configuración vive dentro de cada formulario, en la pestaña **Validación personalizada**. No hay página global de ajustes por diseño — cada formulario guarda sus propios mensajes.

= ¿El plugin trackea o envía datos? =

No. Validation Muse no hace ninguna petición externa. El botón opcional AI Translate (integración con Flavor) se enruta a través del proveedor configurado en Flavor.

== Screenshots ==

1. La pestaña **Validación personalizada** dentro del editor de Contact Form 7 — una tarjeta por campo con mensajes, reglas adicionales (longitud, patrón con probador en vivo, obligatorio solo a veces) y una vista previa de lo que verán los visitantes.
2. **Copiar, respaldar y reutilizar** — copia reglas de otro formulario, descárgalas/cárgalas como archivo, guarda una plantilla o úsalas en todos los formularios.
3. Mensaje de campo obligatorio renderizado en el frontend.
4. Mensaje de formato inválido para un campo email renderizado en el frontend.

== Changelog ==

= 1.8.0 =
* Nuevo: la pestaña «Validación personalizada» se ha rediseñado. Cada campo es una tarjeta con su etiqueta, su tipo y la indicación de si está personalizado; las reglas adicionales (longitud, patrón, obligatorio solo a veces) quedan tras un único interruptor «Reglas adicionales»; cada tarjeta muestra lo que verán los visitantes. La búsqueda, «Mostrar solo los campos personalizados» y abrir/cerrar todo ayudan en los formularios largos.
* Nuevo: ejemplos de patrones ya preparados y un cuadro «Pruébalo: escribe una respuesta de ejemplo» que comprueba un patrón igual que lo hace el sitio.
* Nuevo: las herramientas de copia, respaldo, plantilla y «Usar en todos los formularios» están agrupadas y explicadas, piden confirmación en línea y muestran el resultado en el panel en lugar de en ventanas emergentes del navegador.
* Nuevo: cuando WPML String Translation o Polylang está activo, la pestaña enlaza directamente a la pantalla donde se traducen los mensajes.
* Nuevo: mensaje personalizado de «respuesta incorrecta» para campos quiz y de «archivo incorrecto» para campos file.
* Nuevo: `{min}` y `{max}` funcionan en todos los mensajes; filtros para desarrolladores `vmcf7_default_message`, `vmcf7_placeholders`, `vmcf7_field_label`, `vmcf7_supports_text_rules` y `vmcf7_swv_invalid_rules`.
* Nuevo: el estilo de los errores en el frontend se puede cambiar con propiedades personalizadas de CSS (`--vmcf7-error-color`, `--vmcf7-error-font-size` y otras).
* Nuevo: traducciones al japonés, italiano y polaco.
* Fix: el editor de Contact Form 7 ya no pregunta «¿Salir del sitio? Es posible que los cambios que has hecho no se guarden» cuando no se ha cambiado nada.
* Fix: los mensajes personalizados y los límites de longitud vuelven a llegar a la validación del lado del cliente de Contact Form 7 6.2+ (el hook del esquema no hacía nada, sin avisar, en 6.2).
* Fix: las barras invertidas se eliminaban de los patrones y mensajes guardados (`^\d+$` se guardaba como `^d+$`), también al importar, copiar y «Usar en todos los formularios».
* Fix: un patrón no válido ya no rechaza todas las respuestas; una regla de patrón o de longitud en casillas de verificación o selecciones múltiples ya no provoca un error al enviar.
* Fix: copiar las reglas de un formulario sobre el mismo formulario ya no las borra; las herramientas de conjuntos de reglas solo aceptan formularios de Contact Form 7 que el usuario puede editar, y los archivos de importación se comprueban y tienen un límite de tamaño.
* Fix: WPML/Polylang recibían cada mensaje con un guardado de retraso, Polylang no mostraba los mensajes en su pantalla Traducciones y después los mostraba dos veces.
* Fix: el mensaje personalizado de campo obligatorio ahora también se aplica a las casillas de aceptación; required-if funciona cuando el nombre del otro campo tiene mayúsculas.
* Fix: AI Translate ya no envía patrones ni números al traductor.
* Fix: la desinstalación elimina todas las opciones del plugin; los estilos del frontend también se cargan para formularios colocados en widgets y plantillas.

= 1.7.2 =
* Fix: abrir Contacto → Añadir nuevo con Flavor activo ya no se detiene con un error fatal. Un formulario que aún no se ha guardado no tiene ID, y Validation Muse ahora omite sus traducciones hasta que el formulario se guarda.

= 1.7.1 =
* Fix: el archivo que GitHub y Composer construyen a partir de un tag ya no incluye tests, herramientas de desarrollo ni documentación, de modo que un proyecto que instala el plugin desde Git recibe solo el plugin. Sin cambios en el comportamiento del plugin.

= 1.7.0 =
* Nuevo: el filtro `vmcf7_message` entrega a otro código un mensaje escrito aquí: `apply_filters( 'vmcf7_message', $default, $form_id, $field_name, $type )` devuelve el mensaje personalizado, o `$default` cuando no se ha escrito ninguno o la validación personalizada está desactivada para el formulario. Nada cambia para los visitantes salvo que otro código lo pida.

= 1.6.5 =
* Nuevo: una solicitud única y descartable para dejar una valoración en WordPress.org. Aparece solo una semana después de que guardes por primera vez un mensaje de validación personalizado, solo a usuarios que pueden editar formularios de Contact Form 7 y solo en las pantallas de Contact Form 7. «Quizás más tarde» la pospone 30 días, «No volver a preguntar» la oculta para siempre. No se envía nada a ningún sitio.

= 1.6.4 =
* Probado con WordPress 7.1 (verificado en 7.1.1 con Contact Form 7 6.1).
* Fix: eliminadas las llamadas obsoletas en PHP 8.5 (`finfo_close()`, y `ReflectionProperty::setAccessible()` en PHP 8.1+), por lo que el plugin ya no genera avisos de obsolescencia en PHP 8.5.

= 1.6.3 =
* i18n: se regeneró por completo la plantilla de traducción `.pot` — el catálogo estaba congelado desde la 1.2.0 y cubría solo cerca del 20% de las cadenas traducibles del plugin (las reglas de regex/longitud, las plantillas de reglas, los errores de AI Translate y más nunca eran extraíbles antes). Los seis idiomas incluidos (alemán, español, francés, portugués, ruso y ucraniano) ahora están completamente traducidos frente a la interfaz actual.
* Documentación: se sincronizaron todos los readme traducidos — la lista de funciones, los tipos de campo soportados y las FAQ ahora reflejan la funcionalidad de la 1.6.0–1.6.2 (reglas personalizadas de regex/longitud, required-if, validación nativa del lado del cliente vía SWV, plantillas de reglas); sus secciones de Changelog/Upgrade Notice, antes estancadas en la 1.4.2, ahora cubren todas las versiones hasta la 1.6.2.
* Fix: se añadió un comentario `translators:` que faltaba para una cadena con marcador de posición (calidad de código i18n, sin cambios de comportamiento).

= 1.6.2 =
* Fix: los mensajes guardados por versiones anteriores al renombrado del plugin (almacenados bajo el prefijo meta legacy `_cf7cv_`) eran invisibles para el código actual. Una migración única ahora los renombra automáticamente al prefijo `_vmcf7_`, restaurando los mensajes perdidos. Segura ante colisiones y se ejecuta una sola vez.

= 1.6.1 =
* Fix: mensajes de validación duplicados/sobrescritos en el frontend, solucionado integrando con el motor SWV nativo de CF7.
* Fix: los cajones y tarjetas de vista previa del panel admin se renderizaban expandidos porque el editor de CF7 elimina los estilos inline.

= 1.6.0 =
* Nuevo: reglas de validación personalizadas de regex y longitud mín/máx por campo, cada una con su propio mensaje.
* Nuevo: mensajes de validación para campos `file`, `acceptance` y `quiz`; mensajes condicionales "required-if".
* Nuevo: tokens de marcador de posición `{field_label}`, `{min}`, `{max}` en los mensajes.
* Nuevo: validación en línea del lado del cliente que refleja los mensajes del servidor, con accesibilidad `aria-describedby`.
* Nuevo: vista previa en vivo de mensajes, copiar mensajes entre formularios, import/export JSON de conjuntos de mensajes, y linting del editor.
* Nuevo: registro de cadenas WPML/Polylang junto con la integración Flavor; import/export de traducciones.

= 1.5.0 =
* PHP mínimo elevado a 8.0; WordPress mínimo elevado a 6.0; probado hasta WordPress 7.0.
* Nuevo: mensajes de validación personalizados para campos `time`.
* Feedback de error más claro en AI Translate dentro del editor de formularios.
* El reemplazo de mensajes SWV basado en Reflection ahora registra fallos bajo WP_DEBUG para un diagnóstico más fácil.
* Mejor manejo de traducciones pendientes (sin fallback silencioso) con caché por petición.
* Código modernizado a idiomas de PHP 8; escapado de salida reforzado; primera suite de tests añadida.

= 1.4.2 =
* Plugin URI: ahora apunta a la landing page dedicada en https://plugins.symonov.com/validation-muse-for-cf7/
* Sin cambios de código ni de comportamiento

= 1.4.1 =
* Readme: reescritura USP-first para visibilidad SEO
* Tags: reemplazadas las genéricas `messages`/`forms`/`customization` por `contact-form-7`, `cf7`, `validation`, `error-message`, `multilingual`
* FAQ: añadidas entradas sobre compatibilidad con CF7 6.x SWV, comparación con otros plugins de validación CF7, multilingüismo vía Flavor

= 1.4.0 =
* Añadido soporte multilingüe vía integración con el plugin de traducción Flavor
* Los mensajes de validación ahora se pueden traducir por idioma en el editor del formulario
* Las pestañas de idioma aparecen automáticamente cuando Flavor está activo
* Botón AI Translate para traducción automática de un click de todos los mensajes
* Traducciones almacenadas en la base de datos de Flavor — los datos del plugin permanecen portables
* Cero overhead cuando Flavor no está instalado — todas las llamadas detrás de class_exists()
* Las traducciones de Flavor se limpian al desinstalar el plugin

= 1.3.0 =
* Corregida la compatibilidad con Contact Form 7 6.x SWV (Schema-based Validation)
* Los filtros de validación ahora se ejecutan con prioridad 20 (después del core de CF7) para reemplazar los mensajes de error SWV
* Añadido reemplazo de errores basado en Reflection para campos ya invalidados
* Los mensajes personalizados ahora sobrescriben correctamente el texto predeterminado "The field is required." de CF7

= 1.2.1 =
* Corregida la discrepancia de nombre de variable que causaba el error "No required fields"

= 1.2.0 =
* Refactorizada la base de código para seguir los WordPress Coding Standards
* Reorganizada la estructura de archivos para mejor mantenibilidad
* Añadidos bloques PHPDoc a todas las funciones y métodos
* Mejorada la accesibilidad con etiquetas ARIA
* Corregida la inconsistencia de prefijos en JavaScript
* Corregido el script de desinstalación para usar el prefijo meta correcto
* Añadidos hooks de extensibilidad (`vmcf7_loaded`, `vmcf7_validation_tag_types`)
* Cambiada la comprobación de capacidad de `manage_options` a `wpcf7_edit_contact_forms`
* Actualizado el nombre del archivo POT para coincidir con el dominio de texto

= 1.1.2 =
* Cambiado el nombre del plugin.

= 1.1.1 =
* Añadido archivo .gitignore.

= 1.1.0 =
* Añadido material del repositorio de WordPress (readme, licencia, archivo POT).
* Rediseñados los hooks de validación para sobrescribir los mensajes required e invalid sin depender de filtros AJAX.
* Reforzada la sanitización, la carga del dominio de texto y la limpieza de desinstalación para preparar el lanzamiento.

= 1.0.1 =
* Iteración pública inicial incluida en el proyecto.

== Upgrade Notice ==

= 1.8.0 =
Pestaña «Validación personalizada» rediseñada, los mensajes del lado del cliente vuelven a funcionar en Contact Form 7 6.2+ y varias correcciones de validación. Las reglas y mensajes existentes se conservan tal cual.

= 1.7.2 =
Corrige un error fatal en Contacto → Añadir nuevo cuando Flavor está activo. Sin cambios en el comportamiento de la validación.

= 1.7.1 =
Solo empaquetado: el archivo de Git ya no incluye tests ni herramientas. Sin cambios en el comportamiento de la validación.

= 1.7.0 =
Añade el filtro `vmcf7_message` para que otro código lea los mensajes escritos aquí. Sin cambios en el comportamiento de la validación.

= 1.6.5 =
Añade una solicitud de valoración única y descartable (se muestra una semana después de que guardes por primera vez un mensaje personalizado). Sin cambios en el comportamiento de la validación.

= 1.6.4 =
Versión de compatibilidad: probado con WordPress 7.1 y limpio en PHP 8.5. Sin cambios de comportamiento.

= 1.6.3 =
Versión de documentación y traducción — sin cambios funcionales ni de comportamiento. Actualiza por completo los readme traducidos y el catálogo de traducción .pot/.po/.mo.

= 1.6.2 =
Recupera los mensajes de validación guardados por versiones anteriores a 1.2.0 (prefijo meta legacy `_cf7cv_`) mediante una migración única y automática. Segura, no requiere ninguna acción.

= 1.6.1 =
Corrige mensajes de validación duplicados/sobrescritos en el frontend y regresiones de diseño en el panel admin de la 1.6.0. Recomendado para todos los usuarios de 1.6.0.

= 1.6.0 =
Añade reglas personalizadas de regex/longitud, más tipos de campo, mensajes required-if, reflejo en el lado del cliente, vista previa en vivo, import/export/plantillas de reglas y soporte WPML/Polylang. No requiere migración de datos.

= 1.5.0 =
Eleva el PHP mínimo a 8.0 y el WordPress mínimo a 6.0. Revisa tu entorno de hosting antes de actualizar.

= 1.4.2 =
Plugin URI ahora apunta a la landing page dedicada en plugins.symonov.com. Sin cambios de código.

= 1.4.1 =
Release de documentación. Readme refrescado para mejor descubrimiento de funciones.

= 1.4.0 =
Añade soporte multilingüe vía el plugin Flavor y traducción AI de un click. No se requiere migración de datos.

= 1.3.0 =
Restaura la compatibilidad con Contact Form 7 6.x Schema-based Validation (SWV). Recomendado para todos los usuarios de CF7 6.x.
