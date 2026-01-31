=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact form 7, validation, forms, messages, customization
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Personalize as mensagens de validação para cada campo do Contact Form 7 diretamente no editor de formulários.

== Description ==

O Validation Muse for Contact Form 7 permite que você substitua as mensagens de erro padrão exibidas pelo Contact Form 7. Ative o plugin por formulário e forneça texto personalizado para avisos de campos obrigatórios e validação de e-mail, URL, telefone, número, intervalo e data. As mensagens são armazenadas em post meta, então permanecem com o formulário ao exportar/importar.

**Recursos:**

* Personalize mensagens de "campo obrigatório" por campo
* Personalize mensagens de "formato inválido" para campos de e-mail, URL, telefone, número, intervalo e data
* Ative/desative a validação personalizada por formulário
* Mensagens armazenadas em post meta para fácil exportação/importação
* Totalmente traduzível com arquivo POT incluído
* Interface de administração acessível com labels ARIA

== Installation ==

1. Envie a pasta `validation-muse-for-contact-form-7` para `/wp-content/plugins/`, ou instale pelo admin do WordPress em Plugins → Adicionar novo.
2. Ative o plugin pela tela de Plugins.
3. Abra qualquer formulário do Contact Form 7, vá para o painel **Validação personalizada**, ative o recurso e salve suas mensagens.

== Frequently Asked Questions ==

= Este plugin requer o Contact Form 7? =

Sim. O Contact Form 7 deve estar instalado e ativo. O plugin exibirá um aviso de administrador e se desativará automaticamente se o Contact Form 7 estiver ausente.

= Quais tipos de campos suportam mensagens de formato inválido personalizadas? =

Campos de e-mail, URL, telefone, número (incluindo intervalo) e data podem exibir texto personalizado de formato inválido. Qualquer campo obrigatório pode ter uma mensagem personalizada de "obrigatório".

= Onde as mensagens são armazenadas? =

As mensagens são salvas no post meta de cada formulário. Elas são incluídas nas exportações do Contact Form 7, então migrar um formulário moverá as mensagens junto.

= Posso usar HTML nas mensagens de validação? =

Sim, HTML básico é permitido e sanitizado usando `wp_kses_post()`.

== Screenshots ==

1. O painel de Validação personalizada no editor do Contact Form 7
2. Exemplo de mensagens de validação personalizadas no frontend

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
