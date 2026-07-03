=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact-form-7, cf7, validation, error-message, multilingual
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.6.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Mensagens personalizadas de erro e de campo obrigatório para Contact Form 7. Por formulário, por campo, compatível com CF7 6.x SWV, multilíngue.

== Description ==

**Validation Muse** permite escrever suas próprias mensagens de erro para cada campo do Contact Form 7 — diretamente no editor do formulário, por formulário e por campo. Sem código, sem página global de configurações, sem hacks JavaScript.

A maioria dos plugins de validação do CF7 parou de funcionar quando o Contact Form 7 6.x introduziu o **Schema-based Validation (SWV)**. O Validation Muse executa seus filtros com prioridade 20 (após o core do CF7) e usa Reflection para substituir o texto de erro do SWV em campos já invalidados — assim sua cópia personalizada realmente aparece, mesmo no novo motor de validação.

= Por que Validation Muse =

* **Compatível com CF7 6.x SWV** — funciona com o novo motor Schema-based Validation, não apenas com hooks legacy.
* **Regras personalizadas de regex e comprimento** — defina padrões de expressão regular, comprimento mínimo e máximo por campo, cada um com sua própria mensagem de erro.
* **Regras condicionais "Required-If"** — torne campos obrigatórios apenas quando um campo companheiro estiver preenchido/marcado.
* **Integração SWV no lado do cliente** — regras de validação padrão (required, email, length) são injetadas no motor SWV nativo do CF7 para feedback instantâneo no frontend, com suporte completo a acessibilidade (A11y).
* **Import/export e modelos de conjuntos de regras** — exporte/importe regras de validação como JSON, copie-as de outros formulários, e aplique modelos globais em massa.
* **Tokens de espaço reservado** — use `{field_label}`, `{min}` e `{max}` nas mensagens de validação para gerar textos dinâmicos.
* **Por formulário, por campo** — cada formulário mantém suas próprias mensagens; sem sobrescrita global.
* **Armazenado em post meta** — as mensagens vivem com o formulário, compatíveis com a duplicação de formulário do CF7 e com plugins de import/export de terceiros.
* **Multilíngue via WPML, Polylang e Flavor** — traduz as regras usando os hooks padrão do WPML/Polylang, e conta com abas de idioma + botão AI Translate em um click quando o Flavor está ativo.
* **Amigável ao desenvolvedor** — os hooks de extensibilidade `vmcf7_loaded` e `vmcf7_validation_tag_types` permitem adicionar tipos de campo personalizados.
* **Leve** — sem inchaço admin, sem tracking, sem upsells.

= Tipos de campo suportados =

* Mensagens de campo obrigatório: qualquer tag required (`text`, `textarea`, `select`, `checkbox`, `radio`, `file`, etc.).
* Mensagens de formato inválido: `email`, `url`, `tel`, `number` (incluindo `range`), `date` e `time`.
* Mensagens de erro personalizadas em falhas de validação: `file` (verificações de tamanho/tipo), `acceptance` (estado não aceito) e `quiz` (resposta incorreta).
* HTML dentro das mensagens é permitido e sanitizado via `wp_kses_post()`.
* Regras personalizadas de regex e comprimento mín/máx funcionam em todos os campos de entrada (exceto acceptance e quiz).

= Traduções =

O plugin acompanha um arquivo `.pot` e já está traduzido para alemão, espanhol, francês, português (Brasil), russo e ucraniano. [Ajude a traduzi-lo para o seu idioma.](https://translate.wordpress.org/projects/wp-plugins/validation-muse-for-contact-form-7)

== Installation ==

1. Instale via **Plugins → Adicionar novo** procurando *Validation Muse for Contact Form 7*, ou faça upload da pasta `validation-muse-for-contact-form-7` para `/wp-content/plugins/`.
2. Ative o plugin. O Contact Form 7 deve já estar ativo — o Validation Muse se autodesativa com um aviso admin se o CF7 estiver faltando.
3. Edite qualquer formulário do Contact Form 7, abra o painel **Custom Validation**, ative-o e escreva suas mensagens.
4. (Opcional) Instale o plugin de tradução Flavor para traduzir mensagens por idioma com assistência de AI.

== Frequently Asked Questions ==

= Funciona com Contact Form 7 6.x e Schema-based Validation (SWV)? =

Sim. Desde a versão 1.3.0, o Validation Muse engata na prioridade 20 (após o core do CF7) e usa Reflection para substituir o texto de erro do SWV em campos já invalidados. Desde a 1.6.1, regras padrão (required, email/url/tel/number/date/time, comprimento mín/máx) também são injetadas diretamente no próprio esquema SWV do CF7, então a validação do lado do cliente (JS do frontend) mostra suas mensagens personalizadas instantaneamente — através do motor de validação nativo do CF7, sem nenhum script adicional deste plugin.

= Como isso difere de outros plugins de validação do CF7? =

O Validation Muse é o único plugin de validação do CF7 que (1) é compatível com CF7 6.x SWV de fábrica — tanto no lado do servidor quanto na própria validação do lado do cliente do CF7, (2) armazena mensagens no post meta do formulário para que vivam com ele (compatível com duplicação CF7 e plugins import/export), e (3) integra-se ao plugin Flavor para mensagens por idioma com tradução AI em um click.

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

= 1.6.3 =
* i18n: catálogo de tradução `.pot` totalmente regenerado — o catálogo estava congelado desde a 1.2.0 e cobria apenas cerca de 20% das strings traduzíveis do plugin (regras de regex/comprimento, modelos de regras, erros do AI Translate e mais nunca eram extraíveis antes). Os seis idiomas disponíveis (alemão, espanhol, francês, português, russo e ucraniano) agora estão totalmente traduzidos de acordo com a interface atual.
* Docs: todos os arquivos readme traduzidos foram sincronizados — a lista de recursos, os tipos de campo suportados e o FAQ agora refletem a funcionalidade de 1.6.0–1.6.2 (regras personalizadas de regex/comprimento, required-if, validação nativa do lado do cliente via SWV, modelos de regras); suas seções de Changelog/Upgrade Notice, antes travadas na 1.4.2, agora cobrem todas as versões até a 1.6.2.
* Fix: adicionado um comentário `translators:` que faltava para uma string com espaço reservado (qualidade de código i18n, sem mudança de comportamento).

= 1.6.2 =
* Fix: mensagens salvas por versões anteriores à renomeação do plugin (armazenadas sob o prefixo meta legado `_cf7cv_`) ficavam invisíveis para o código atual. Uma migração única agora as renomeia automaticamente para o prefixo `_vmcf7_`, restaurando mensagens perdidas. Segura contra colisões e executa apenas uma vez.

= 1.6.1 =
* Fix: mensagens de validação duplicadas/sobrescritas no frontend, corrigido integrando com o motor SWV nativo do CF7.
* Fix: gavetas e cartões de pré-visualização do painel admin eram renderizados expandidos porque o editor do CF7 remove estilos inline.

= 1.6.0 =
* Novo: regras de validação de regex e comprimento mín/máx personalizadas por campo, cada uma com sua própria mensagem.
* Novo: mensagens de validação para campos `file`, `acceptance` e `quiz`; mensagens condicionais "required-if".
* Novo: tokens de espaço reservado `{field_label}`, `{min}`, `{max}` nas mensagens.
* Novo: validação inline no lado do cliente espelhando as mensagens do servidor, com acessibilidade `aria-describedby`.
* Novo: pré-visualização ao vivo de mensagens, cópia de mensagens entre formulários, import/export JSON de conjuntos de mensagens e lint do editor.
* Novo: registro de strings WPML/Polylang junto com a ponte Flavor; import/export de traduções.

= 1.5.0 =
* PHP mínimo elevado para 8.0; WordPress mínimo elevado para 6.0; testado até WordPress 7.0.
* Novo: mensagens de validação personalizadas para campos `time`.
* Feedback de erro mais claro do AI Translate no editor de formulário.
* A substituição de mensagens SWV baseada em Reflection agora registra falhas sob WP_DEBUG para diagnóstico mais fácil.
* Tratamento melhorado de traduções pendentes (sem fallback silencioso) com cache por requisição.
* Código modernizado para idiomas do PHP 8; escaping de saída reforçado; primeira suíte de testes adicionada.

= 1.4.2 =
* Plugin URI: agora aponta para a página dedicada em https://plugins.symonov.com/validation-muse-for-cf7/
* Sem mudanças de código ou comportamento

= 1.4.1 =
* Readme: reescrita USP-first para visibilidade SEO
* Tags: substituídas as genéricas `messages`/`forms`/`customization` pelas direcionadas `contact-form-7`, `cf7`, `validation`, `error-message`, `multilingual`
* FAQ: adicionadas entradas sobre compatibilidade com CF7 6.x SWV, comparação com outros plugins de validação do CF7, multilinguismo via Flavor

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

= 1.6.3 =
Lançamento de documentação e tradução — sem mudanças funcionais ou de comportamento. Atualiza totalmente os readmes traduzidos e o catálogo de tradução .pot/.po/.mo.

= 1.6.2 =
Recupera mensagens de validação salvas por versões anteriores à 1.2.0 (prefixo meta legado `_cf7cv_`) por meio de uma migração única e automática. Segura, nenhuma ação necessária.

= 1.6.1 =
Corrige mensagens de validação duplicadas/sobrescritas no frontend e regressões de layout no painel admin da 1.6.0. Recomendado para todos os usuários da 1.6.0.

= 1.6.0 =
Adiciona regras personalizadas de regex/comprimento, mais tipos de campo, mensagens required-if, espelhamento no lado do cliente, pré-visualização ao vivo, import/export/modelos de regras e suporte a WPML/Polylang. Nenhuma migração de dados necessária.

= 1.5.0 =
Eleva o PHP mínimo para 8.0 e o WordPress mínimo para 6.0. Verifique seu ambiente de hospedagem antes de atualizar.

= 1.4.2 =
Plugin URI agora aponta para a página dedicada em plugins.symonov.com. Sem mudanças de código.

= 1.4.1 =
Release de documentação. Readme atualizado para melhor descoberta de funcionalidades.

= 1.4.0 =
Adiciona suporte multilíngue via plugin Flavor e tradução AI em um click. Nenhuma migração de dados necessária.

= 1.3.0 =
Restaura a compatibilidade com Contact Form 7 6.x Schema-based Validation (SWV). Recomendado para todos os usuários do CF7 6.x.
