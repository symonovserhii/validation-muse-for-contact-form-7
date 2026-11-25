=== Validation Muse for Contact Form 7 ===
Contributors: simmotorlp
Tags: contact-form-7, cf7, validation, error-message, multilingual
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Regras e mensagens de validação personalizadas para Contact Form 7 — regex, comprimento, required-if, por campo, compatível com CF7 6.x SWV, multilíngue.

== Description ==

**Validation Muse** permite escrever suas próprias regras de validação e mensagens de erro para cada campo do Contact Form 7 — diretamente no editor do formulário, por formulário e por campo. Sem código, sem página global de configurações, sem hacks JavaScript.

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
* **Amigável ao desenvolvedor** — os hooks de extensibilidade `vmcf7_loaded` e `vmcf7_validation_tag_types` permitem adicionar tipos de campo personalizados; os filtros `vmcf7_message`, `vmcf7_default_message`, `vmcf7_placeholders` e `vmcf7_field_label` permitem ler as mensagens, definir mensagens padrão para todo o site e adicionar seus próprios `{tokens}`.
* **Leve** — sem inchaço admin, sem tracking, sem upsells.

= Tipos de campo suportados =

* Mensagens de campo obrigatório: qualquer tag required (`text`, `textarea`, `select`, `checkbox`, `radio`, `file`, etc.).
* Mensagens de formato inválido: `email`, `url`, `tel`, `number` (incluindo `range`), `date` e `time`.
* Mensagens de erro personalizadas em falhas de validação: `file` (verificações de tamanho/tipo), `acceptance` (estado não aceito) e `quiz` (resposta incorreta).
* HTML dentro das mensagens é permitido e sanitizado via `wp_kses_post()`.
* Regras personalizadas de Regex e comprimento Mín/Máx estão disponíveis para todos os tipos de campo, exceto `file`.

= Traduções =

O plugin acompanha um arquivo `.pot` e já está traduzido para alemão, espanhol, francês, italiano, japonês, polonês, português (Brasil), russo e ucraniano. [Ajude a traduzi-lo para o seu idioma.](https://translate.wordpress.org/projects/wp-plugins/validation-muse-for-contact-form-7)

== Installation ==

1. Instale via **Plugins → Adicionar novo** procurando *Validation Muse for Contact Form 7*, ou faça upload da pasta `validation-muse-for-contact-form-7` para `/wp-content/plugins/`.
2. Ative o plugin. O Contact Form 7 deve já estar ativo — o Validation Muse se autodesativa com um aviso admin se o CF7 estiver faltando.
3. Edite qualquer formulário do Contact Form 7, abra a aba **Validação personalizada**, ative-a e escreva suas mensagens.
4. (Opcional) Instale o plugin de tradução Flavor para traduzir mensagens por idioma com assistência de AI.

== Frequently Asked Questions ==

= Funciona com Contact Form 7 6.x e Schema-based Validation (SWV)? =

Sim. Desde a versão 1.3.0, o Validation Muse engata na prioridade 20 (após o core do CF7) e usa Reflection para substituir o texto de erro do SWV em campos já invalidados. Desde a 1.6.1, regras padrão (required, email/url/tel/number/date/time, comprimento mín/máx) também são injetadas diretamente no próprio esquema SWV do CF7, então a validação do lado do cliente (JS do frontend) mostra suas mensagens personalizadas instantaneamente — através do motor de validação nativo do CF7, sem nenhum script adicional deste plugin.

= Como isso difere de outros plugins de validação do CF7? =

O Validation Muse é o único plugin de validação do CF7 que (1) é compatível com CF7 6.x SWV de fábrica — tanto no lado do servidor quanto na própria validação do lado do cliente do CF7, (2) armazena mensagens no post meta do formulário para que vivam com ele (compatível com duplicação CF7 e plugins import/export), e (3) oferece mensagens por idioma via WPML, Polylang ou o plugin Flavor (com tradução AI em um click).

= Posso traduzir mensagens de validação por idioma? =

Sim. WPML (String Translation) e Polylang são compatíveis nativamente: suas mensagens são registradas para tradução sempre que você salva o formulário, e a aba **Validação personalizada** leva à tela onde você as traduz. Com o plugin Flavor, o Validation Muse também mostra abas de idioma no editor do formulário e um botão *AI Translate*; essas traduções são armazenadas no banco de dados do Flavor, e desinstalar o Validation Muse as limpa.

= Quais tipos de campo suportam mensagens personalizadas de formato inválido ou falha de validação? =

* `email`, `url`, `tel`, `number` (incluindo `range`), `date` e `time` suportam mensagens de formato inválido personalizadas.
* `file` suporta mensagens de erro de validação de tamanho/tipo de arquivo personalizadas.
* `acceptance` suporta mensagens de erro personalizadas quando a caixa não é aceita.
* `quiz` suporta mensagens de erro personalizadas quando a resposta é incorreta.
* Qualquer campo obrigatório de qualquer tipo pode ter uma mensagem de obrigatoriedade personalizada.

= Onde as mensagens são armazenadas? =

No post meta de cada formulário. Elas vivem com o formulário, então duplicar um formulário (recurso nativo do CF7) mantém as mensagens. O CF7 não tem export nativo, mas plugins de import/export de terceiros leem o post meta — então migrações entre sites funcionam sem um passo de import separado.

= Posso usar HTML nas mensagens de validação? =

Sim, HTML básico é permitido e sanitizado via `wp_kses_post()`.

= Este plugin requer o Contact Form 7? =

Sim. O CF7 deve estar instalado e ativo. O plugin mostra um aviso admin e se autodesativa se o CF7 estiver faltando.

= Existe uma página de configurações? =

Não. A configuração vive dentro de cada formulário, na aba **Validação personalizada**. Não há página global de configurações por design — cada formulário guarda suas próprias mensagens.

= O plugin trackeia ou envia algum dado? =

Não. O Validation Muse não faz nenhuma requisição externa. O botão opcional AI Translate (integração com o Flavor) passa pelo provedor configurado no Flavor.

== Screenshots ==

1. A aba **Validação personalizada** dentro do editor do Contact Form 7 — um cartão por campo com mensagens, regras extras (comprimento, padrão com testador ao vivo, obrigatório só às vezes) e uma pré-visualização do que os visitantes verão.
2. **Copiar, fazer backup e reutilizar** — copie regras de outro formulário, baixe/carregue-as como arquivo, guarde um modelo ou use-as em todos os formulários.
3. Mensagem de campo obrigatório renderizada no frontend.
4. Mensagem de formato inválido para um campo email renderizada no frontend.

== Changelog ==

= 1.8.0 =
* Novo: a aba "Validação personalizada" foi redesenhada. Cada campo é um cartão com seu rótulo, seu tipo e a indicação de estar ou não personalizado; as regras extras (comprimento, padrão, obrigatório só às vezes) ficam atrás de uma única chave "Regras extras"; cada cartão mostra o que os visitantes verão. A busca, "Mostrar apenas campos personalizados" e abrir/fechar tudo ajudam em formulários longos.
* Novo: exemplos prontos de padrões e uma caixa "Experimente: digite uma resposta de exemplo" que verifica um padrão do mesmo jeito que o site.
* Novo: as ferramentas de cópia, backup, modelo e "Usar em todos os formulários" estão agrupadas e explicadas, pedem confirmação no próprio painel e mostram o resultado nele, em vez de em pop-ups do navegador.
* Novo: quando o WPML String Translation ou o Polylang está ativo, a aba leva direto à tela onde as mensagens são traduzidas.
* Novo: mensagem personalizada de "resposta errada" para campos quiz e de "arquivo errado" para campos file.
* Novo: `{min}` e `{max}` funcionam em todas as mensagens; filtros para desenvolvedores `vmcf7_default_message`, `vmcf7_placeholders`, `vmcf7_field_label`, `vmcf7_supports_text_rules` e `vmcf7_swv_invalid_rules`.
* Novo: o estilo dos erros no frontend pode ser alterado com propriedades personalizadas de CSS (`--vmcf7-error-color`, `--vmcf7-error-font-size` e outras).
* Novo: traduções para japonês, italiano e polonês.
* Fix: o editor do Contact Form 7 não pergunta mais "Sair do site? As alterações feitas talvez não sejam salvas" quando nada foi alterado.
* Fix: mensagens personalizadas e limites de comprimento voltam a chegar à validação do lado do cliente do Contact Form 7 6.2+ (o hook do esquema silenciosamente não fazia nada na 6.2).
* Fix: as barras invertidas eram removidas dos padrões e mensagens salvos (`^\d+$` era armazenado como `^d+$`), também ao importar, copiar e em "Usar em todos os formulários".
* Fix: um padrão inválido não rejeita mais todas as respostas; uma regra de padrão ou de comprimento em caixas de seleção ou seleções múltiplas não causa mais erro no envio.
* Fix: copiar as regras de um formulário para o mesmo formulário não as apaga mais; as ferramentas de conjuntos de regras aceitam apenas formulários do Contact Form 7 que o usuário pode editar, e os arquivos de importação são verificados e têm tamanho limitado.
* Fix: WPML/Polylang recebiam cada mensagem com um salvamento de atraso, o Polylang não listava as mensagens em sua tela Traduções e depois as listava duas vezes.
* Fix: a mensagem personalizada de campo obrigatório agora também se aplica às caixas de aceitação; required-if funciona quando o nome do outro campo tem letras maiúsculas.
* Fix: o AI Translate não envia mais padrões e números ao tradutor.
* Fix: a desinstalação remove todas as opções do plugin; os estilos do frontend também são carregados para formulários colocados em widgets e modelos.

= 1.7.2 =
* Fix: abrir Contato → Adicionar novo com o Flavor ativo não para mais com um erro fatal. Um formulário que ainda não foi salvo não tem ID, e o Validation Muse agora ignora suas traduções até que o formulário seja salvo.

= 1.7.1 =
* Fix: o arquivo que o GitHub e o Composer geram a partir de uma tag não traz mais testes, ferramentas de desenvolvimento e documentação, de modo que um projeto que instala o plugin a partir do Git recebe apenas o plugin. Nenhuma mudança no comportamento do plugin.

= 1.7.0 =
* Novo: o filtro `vmcf7_message` entrega a outro código uma mensagem escrita aqui: `apply_filters( 'vmcf7_message', $default, $form_id, $field_name, $type )` retorna a mensagem personalizada, ou `$default` quando nenhuma foi escrita ou a validação personalizada está desativada para o formulário. Nada muda para os visitantes, a menos que outro código peça.

= 1.6.5 =
* Novo: um pedido único e dispensável para deixar uma avaliação no WordPress.org. Ele aparece apenas uma semana depois de você salvar pela primeira vez uma mensagem de validação personalizada, apenas para usuários que podem editar formulários do Contact Form 7 e apenas nas telas do Contact Form 7. "Talvez mais tarde" adia por 30 dias, "Não perguntar de novo" oculta de vez. Nada é enviado a lugar nenhum.

= 1.6.4 =
* Testado com WordPress 7.1 (verificado na 7.1.1 com Contact Form 7 6.1).
* Fix: removidas as chamadas obsoletas no PHP 8.5 (`finfo_close()`, e `ReflectionProperty::setAccessible()` no PHP 8.1+), de modo que o plugin não gera mais avisos de obsolescência no PHP 8.5.

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
* Zero overhead quando o Flavor não está instalado — todas as chamadas atrás de class_exists()
* Traduções do Flavor são limpas ao desinstalar o plugin

= 1.3.0 =
* Corrigida a compatibilidade com Contact Form 7 6.x SWV (Schema-based Validation)
* Filtros de validação agora rodam com prioridade 20 (após o core do CF7) para substituir mensagens de erro SWV
* Adicionada substituição de erro baseada em Reflection para campos já invalidados
* Mensagens personalizadas agora substituem corretamente o texto padrão "The field is required." do CF7

= 1.2.1 =
* Corrigida a incompatibilidade de nome de variável que causava o erro "No required fields"

= 1.2.0 =
* Base de código refatorada para seguir os WordPress Coding Standards
* Estrutura de arquivos reorganizada para melhor manutenibilidade
* Adicionados blocos PHPDoc a todas as funções e métodos
* Acessibilidade melhorada com rótulos ARIA
* Corrigida a inconsistência de prefixo no JavaScript
* Corrigido o script de desinstalação para usar o prefixo meta correto
* Adicionados hooks de extensibilidade (`vmcf7_loaded`, `vmcf7_validation_tag_types`)
* Verificação de capacidade alterada de `manage_options` para `wpcf7_edit_contact_forms`
* Nome do arquivo POT atualizado para corresponder ao domínio de texto

= 1.1.2 =
* Nome do plugin alterado.

= 1.1.1 =
* Adicionado arquivo .gitignore.

= 1.1.0 =
* Adicionado material do repositório WordPress (readme, licença, arquivo POT).
* Hooks de validação retrabalhados para substituir mensagens required e invalid sem depender de filtros AJAX.
* Sanitização, carregamento do domínio de texto e limpeza de desinstalação reforçados para prontidão de lançamento.

= 1.0.1 =
* Iteração pública inicial incluída no projeto.

== Upgrade Notice ==

= 1.8.0 =
Aba "Validação personalizada" redesenhada, mensagens do lado do cliente funcionando de novo no Contact Form 7 6.2+ e várias correções de validação. As regras e mensagens existentes são mantidas como estão.

= 1.7.2 =
Corrige um erro fatal em Contato → Adicionar novo quando o Flavor está ativo. Sem mudanças no comportamento da validação.

= 1.7.1 =
Apenas empacotamento: o arquivo do Git não traz mais testes e ferramentas. Sem mudanças no comportamento da validação.

= 1.7.0 =
Adiciona o filtro `vmcf7_message` para que outro código leia as mensagens escritas aqui. Sem mudanças no comportamento da validação.

= 1.6.5 =
Adiciona um pedido de avaliação único e dispensável (exibido uma semana depois de você salvar pela primeira vez uma mensagem personalizada). Sem mudanças no comportamento da validação.

= 1.6.4 =
Lançamento de compatibilidade: testado com WordPress 7.1 e limpo no PHP 8.5. Sem mudanças de comportamento.

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
