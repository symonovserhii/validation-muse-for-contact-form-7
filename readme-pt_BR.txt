=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact-form-7, cf7, validation, error-message, multilingual
Requires at least: 5.8
Tested up to: 6.9.4
Requires PHP: 7.4
Stable tag: 1.4.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Mensagens personalizadas de erro e de campo obrigatório para Contact Form 7. Por formulário, por campo, compatível com CF7 6.x SWV, multilíngue.

== Description ==

**Validation Muse** permite escrever suas próprias mensagens de erro para cada campo do Contact Form 7 — diretamente no editor do formulário, por formulário e por campo. Sem código, sem página global de configurações, sem hacks JavaScript.

A maioria dos plugins de validação do CF7 parou de funcionar quando o Contact Form 7 6.x introduziu o **Schema-based Validation (SWV)**. O Validation Muse executa seus filtros com prioridade 20 (após o core do CF7) e usa Reflection para substituir o texto de erro do SWV em campos já invalidados — assim sua cópia personalizada realmente aparece, mesmo no novo motor de validação.

= Por que Validation Muse =

* **Compatível com CF7 6.x SWV** — funciona com o novo motor Schema-based Validation, não apenas com hooks legacy.
* **Por formulário, por campo** — cada formulário mantém suas próprias mensagens; sem sobrescrita global.
* **Armazenado em post meta** — as mensagens vivem com o formulário, compatíveis com a duplicação de formulário do CF7 e com plugins de import/export de terceiros.
* **Multilíngue via Flavor** — quando o plugin de tradução Flavor está ativo, abas de idioma e um botão AI Translate aparecem automaticamente no editor. Zero overhead quando o Flavor não está instalado.
* **Amigável ao desenvolvedor** — os hooks de extensibilidade `vmcf7_loaded` e `vmcf7_validation_tag_types` permitem adicionar tipos de campo personalizados.
* **Leve** — sem inchaço admin, sem tracking, sem upsells.

= Tipos de campo suportados =

* Mensagens de campo obrigatório: qualquer tag required (text, textarea, select, checkbox, radio, file, etc.).
* Mensagens de formato inválido: `email`, `url`, `tel`, `number` (incluindo `range`) e `date`.
* HTML dentro das mensagens é permitido e sanitizado via `wp_kses_post()`.

= Traduções =

O plugin acompanha um arquivo `.pot` e já está traduzido para holandês, alemão, russo, espanhol (Chile/Espanha). [Ajude a traduzi-lo para o seu idioma.](https://translate.wordpress.org/projects/wp-plugins/validation-muse-for-contact-form-7)

== Installation ==

1. Instale via **Plugins → Adicionar novo** procurando *Validation Muse for Contact Form 7*, ou faça upload da pasta `validation-muse-for-contact-form-7` para `/wp-content/plugins/`.
2. Ative o plugin. O Contact Form 7 deve já estar ativo — o Validation Muse se autodesativa com um aviso admin se o CF7 estiver faltando.
3. Edite qualquer formulário do Contact Form 7, abra o painel **Custom Validation**, ative-o e escreva suas mensagens.
4. (Opcional) Instale o plugin de tradução Flavor para traduzir mensagens por idioma com assistência de AI.

== Frequently Asked Questions ==

= Funciona com Contact Form 7 6.x e Schema-based Validation (SWV)? =

Sim. Desde a versão 1.3.0, o Validation Muse engata na prioridade 20 (após o core do CF7) e usa Reflection para substituir o texto de erro do SWV em campos já invalidados. Suas mensagens substituem tanto os defaults legacy quanto os do SWV.

= Como isso difere de outros plugins de validação do CF7? =

O Validation Muse é o único plugin de validação do CF7 que (1) é compatível com CF7 6.x SWV de fábrica, (2) armazena mensagens no post meta do formulário para que vivam com ele (compatível com duplicação CF7 e plugins import/export), e (3) integra-se ao plugin Flavor para mensagens por idioma com tradução AI em um click.

= Posso traduzir mensagens de validação por idioma? =

Sim — instale o plugin Flavor e o Validation Muse mostrará abas de idioma no editor do formulário além de um botão *AI Translate*. As traduções são armazenadas no banco de dados do Flavor; desinstalar o Validation Muse as limpa.

= Quais tipos de campo suportam mensagens de formato inválido personalizadas? =

`email`, `url`, `tel`, `number` (incluindo `range`) e `date`. Qualquer campo obrigatório de qualquer tipo pode ter uma mensagem de obrigatoriedade personalizada.

= Onde as mensagens são armazenadas? =

No post meta de cada formulário. Elas vivem com o formulário, então duplicar um formulário (recurso nativo do CF7) mantém as mensagens. O CF7 não tem export nativo, mas plugins de import/export de terceiros leem o post meta — então migrações entre sites funcionam sem um passo de import separado.

= Posso usar HTML nas mensagens de validação? =

Sim, HTML básico é permitido e sanitizado via `wp_kses_post()`.

= Este plugin requer o Contact Form 7? =

Sim. O CF7 deve estar instalado e ativo. O plugin mostra um aviso admin e se autodesativa se o CF7 estiver faltando.

= Existe uma página de configurações? =

Não. A configuração vive dentro de cada formulário, no painel **Custom Validation**. Não há página global de configurações por design — cada formulário guarda suas próprias mensagens.

= O plugin trackeia ou envia algum dado? =

Não. O Validation Muse não faz nenhuma requisição externa. O botão opcional AI Translate (integração com o Flavor) passa pelo provedor configurado no Flavor.

== Screenshots ==

1. O painel **Custom Validation** dentro do editor do Contact Form 7 — ativação por formulário, mensagens por campo.
2. Abas de idioma e o botão **AI Translate** (visíveis quando o plugin Flavor está ativo).
3. Mensagem de campo obrigatório renderizada no frontend.
4. Mensagem de formato inválido para um campo email renderizada no frontend.

== Changelog ==

= 1.4.1 =
* Readme: reescrita USP-first para visibilidade SEO
* Tags: substituídas as genéricas `messages`/`forms`/`customization` pelas direcionadas `contact-form-7`, `cf7`, `validation`, `error-message`, `multilingual`
* FAQ: adicionadas entradas sobre compatibilidade com CF7 6.x SWV, comparação com outros plugins de validação do CF7, multilinguismo via Flavor
* Testado até WordPress 6.9.4

= 1.4.0 =
* Adicionado suporte multilíngue via integração com o plugin de tradução Flavor
* Mensagens de validação agora podem ser traduzidas por idioma no editor do formulário
* Abas de idioma aparecem automaticamente quando o plugin Flavor está ativo
* Botão AI Translate para tradução automática em um click de todas as mensagens
* Traduções armazenadas no banco de dados do Flavor — os dados do plugin permanecem portáveis
* Zero overhead quando o Flavor não está instalado — todas as chamadas atrás de `class_exists()`
* Traduções do Flavor são limpas ao desinstalar o plugin

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
Release de documentação. Readme atualizado para melhor descoberta de funcionalidades e compatibilidade com WordPress 6.9.4 confirmada.

= 1.4.0 =
Adiciona suporte multilíngue via plugin Flavor e tradução AI em um click. Nenhuma migração de dados necessária.

= 1.3.0 =
Restaura a compatibilidade com Contact Form 7 6.x Schema-based Validation (SWV). Recomendado para todos os usuários do CF7 6.x.
