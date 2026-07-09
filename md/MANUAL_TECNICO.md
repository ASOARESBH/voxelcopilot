# VOXEL Copilot — Manual Técnico de Arquitetura

> Engenharia reversa completa da aplicação: framework PHP próprio (sem Composer), 39 tabelas MySQL, 23 controllers, um gateway de IA multi-provider e um módulo de autorização PACS/DICOM. Este documento é a referência obrigatória antes de qualquer nova implementação.
>
> Gerado por engenharia reversa do código-fonte em `c:\xampp\htdocs\dashboard\voxelcopilot`. Nenhuma funcionalidade foi implementada durante esta análise.

**Escopo:** 96 arquivos PHP · 39 tabelas `cop_*` · 23 controllers · 46 views · PHP 7.4+ · MySQL/MariaDB 5.7

---

## Sumário

00. [Sumário executivo](#00-sumário-executivo)
01. [Mapeamento geral](#01-mapeamento-geral)
02. [Fluxo da aplicação](#02-fluxo-da-aplicação)
03. [Banco de dados](#03-banco-de-dados)
04. [Inventário de APIs](#04-inventário-de-apis)
05. [Frontend](#05-frontend)
06. [Autenticação & RBAC](#06-autenticação--rbac)
07. [Logs & auditoria](#07-logs--auditoria)
08. [DICOM / PACS](#08-dicom--pacs)
09. [Integrações & camada de abstração proposta](#09-integrações--camada-de-abstração-proposta-fases-9-e-16)
10. [Design patterns](#10-design-patterns-identificados)
11. [Dependências](#11-dependências)
12. [Segurança](#12-segurança)
13. [Performance](#13-performance)
14. [Módulos de negócio](#14-módulos-de-negócio)
15. [Pontos de extensão](#15-pontos-de-extensão)
16. [Checklist final antes de implementar](#16-checklist-final-fase-17)

---

## 00. Sumário executivo

A arquitetura de base (roteador, autenticação, acesso a banco, views) é pequena, consistente e fácil de estender. Mas três subsistemas recentes — **AI Router**, **Provider Wizard** e **Medical Profile** — foram construídos em migrations sucessivas que **divergiram entre si**. Isso não aparece rodando a aplicação em uso normal porque cada tela carrega sem erro fatal na maioria dos casos; só aparece quando se tenta seguir o fluxo ponta-a-ponta. Os cinco achados abaixo são a razão pela qual esta fase de mapeamento veio antes de qualquer implementação.

### 🔴 Crítico 1 — AI Router não consegue ler as chaves salvas pelo Wizard

`AiRouterService::resolveProvider()` lê `cop_ai_providers.api_key` (texto puro). Mas a migration `2026-07-05_provider_wizard.sql` deu `DROP TABLE` nessa tabela e a recriou com `api_key_enc` (criptografado) + `api_key_mask` — a coluna `api_key` não existe mais. Resultado: qualquer provider cadastrado pelo Wizard é invisível para o AI Router, que sempre cai no fallback `OPENAI_API_KEY` do `.env` (ou falha com `NO_PROVIDER`). O "gateway único de IA" documentado no cabeçalho do próprio arquivo está, hoje, desconectado da tela onde o usuário cadastra providers.

### 🔴 Crítico 2 — Chave criptografada nunca é decodificada

`ProviderWizardController::encrypt()` existe; não há `decrypt()` em nenhum lugar do código. A chave de criptografia também tem fallback hardcoded (`'voxelcopilot_aes_key_2026'`) porque a constante `APP_KEY` nunca é definida em `bootstrap.php` — o valor de `.env` fica sem uso.

### 🔴 Crítico 3 — MedicalProfileController usa uma API de banco que não existe

O controller chama `$this->db->fetch(...)`, `$this->db->query($sql, $params)` e `$this->db->fetchAll(...)`, mas `Database::getInstance()` devolve um `PDO` puro — sem nenhum desses métodos. Toda chamada nesse controller lança um erro fatal capturado pelo Router (página 500). Ele também referencia `cop_laudos.user_id`, coluna que não existe (o nome real é `medico_id`), e grava colunas (`estilo_laudo`, `nivel_detalhe`) que pertencem ao schema de uma tabela irmã não utilizada, `cop_medical_profiles`, e não à tabela que ele de fato consulta, `cop_medico_perfil`.

### 🟠 Achado 4 — Endpoint pensado para o PACS externo exige sessão de navegador

`POST /api/pacs/validar-token` foi escrito para ser chamado pelo PACS (servidor-a-servidor), mas não está na lista `publicRoutes` do `Router`. Sem sessão autenticada, a chamada é redirecionada para `/login` em vez de retornar JSON — quebrando a integração que o próprio módulo de Autorização PACS foi criado para viabilizar.

### 🟠 Achado 5 — Rotas apontando para métodos inexistentes

`POST /configuracoes/senha` chama `ConfiguracoesController@alterarSenha`, mas o método existente se chama `salvarSenha`. `POST /api/configuracoes/assinatura` aponta para `salvarAssinatura`, que não existe no controller (a view chama a rota via `fetch()` mesmo assim). Ambas resultam em erro 500 silencioso.

Nenhum desses pontos impede o uso diário do sistema (login, ditar laudo, templates, fila) — todos ficam nos módulos de configuração avançada de IA e integração. Mas **qualquer nova API que dependa do AI Router ou do Medical Profile herdará esses defeitos silenciosamente** se não forem tratados primeiro. Ver checklist final (§16).

---

## 01. Mapeamento geral

Não há Composer, não há `vendor/`. É um MVC próprio, deliberadamente simples para rodar em hospedagem compartilhada (HostGator/cPanel) — decisão explícita registrada no `README.md` e nos cabeçalhos das migrations ("100% compatível com MariaDB 5.7... sem PROCEDURE, FUNCTION, EVENT").

| Caminho | Papel |
|---|---|
| `public/index.php` | Front controller único — 11 linhas, só chama `bootstrap.php` + `Router::dispatch()` |
| `app/bootstrap.php` | Ordem fixa e documentada no topo do arquivo: `ini_set` → sessão → headers de segurança → autoload → `.env` |
| `app/autoload.php` | PSR-4 manual de 13 linhas: `App\*` → `app/*.php`, via `spl_autoload_register` |
| `app/Core/` | Núcleo: `Router`, `Controller` (classe base), `Auth`, `Database`, `Logger`, `View`, `Audit\AuditLogger` |
| `app/Middlewares/` | `AuthMiddleware`, `TenantMiddleware`, `PlatformAdminMiddleware` — chamados manualmente no início de cada action, não há pipeline central |
| `app/Controllers/` | 23 controllers "fat" — HTTP + SQL direto, sem repository |
| `app/Controllers/Platform/` | Namespace separado para o painel super-admin (`PlatformController`) |
| `app/Services/` | `PacsService`, `CopilotAIService`, `AiRouterService` (estático), `MailService` |
| `app/Models/` | Referenciado no `README`, **não existe no disco** — não há camada de Model; os controllers usam PDO diretamente com `PDO::FETCH_OBJ` |
| `app/Views/` | 46 templates PHP puro (sem Blade/Twig), organizados por módulo + `layout/` |
| `routes/web.php` | Tabela de rotas única, 181 linhas, carregada pelo front controller antes do dispatch |
| `database/migrations/` | 7 arquivos `.sql` numerados por data, aplicados manualmente via phpMyAdmin — **não há migration runner** |
| `storage/` | `logs/` (texto), `sessions/` (sessão PHP em arquivo), `uploads/` |

### Camadas (topo → base)

1. **Front Controller** — `public/index.php`, único ponto de entrada HTTP, protegido por `.htaccess` (tudo reescreve para cá)
2. **Router** — `App\Core\Router`, regex simples sobre `routes/web.php`, checa `Auth::check()` global antes de rotear
3. **Middleware** — chamado *dentro* do controller (`AuthMiddleware::handle()` na primeira linha da action), não antes do dispatch
4. **Controller** — monta SQL, chama Services, decide entre `view()`/`json()`/`redirect()`
5. **Service** — `PacsService`, `CopilotAIService`, `AiRouterService` — cURL para APIs externas + regras de negócio de IA
6. **Database** — `App\Core\Database::getInstance()` — singleton PDO, `FETCH_OBJ` por padrão, sem query builder/ORM
7. **View** — `App\Core\View::render()` — `ob_start` + `require` do template + header/footer do layout

---

## 02. Fluxo da aplicação

```
Browser (GET/POST)
   → .htaccess (rewrite → public/)
   → index.php (front controller)
   → bootstrap.php (sessão, headers, .env)
   → web.php (registra rotas)
   → Router::dispatch() (match + auth gate)
   → *Middleware::handle() (dentro da action)
   → Controller (fat controller)
   → Service? (Pacs / IA / Mail)
   → PDO (SQL direto)
   → View::render() / json() (resposta)
```

### Detalhe de cada etapa

- **Bootstrap** — ordem é rígida e comentada no próprio arquivo: `ini_set` de erros → sessão (cookie `httponly`, `SameSite=Lax`, path customizado em `storage/sessions`) → headers de segurança (`X-Frame-Options: DENY` etc.) → autoload → `loadEnv()` (parser próprio de `.env`, sem lib).
- **Roteamento** — `Router::dispatch()` lê `REQUEST_METHOD` + `REQUEST_URI`, converte `{id}` em regex `([^/]+)`, casa contra a lista registrada em `web.php`. Antes de tentar casar qualquer rota, verifica se a URI é uma das 4 rotas públicas (`/login`, `/logout`, `/cadastro`, `/selecionar-empresa`); se não for e não houver `Auth::check()`, redireciona para `/login` — **isso vale inclusive para endpoints `/api/*`**, que portanto exigem cookie de sessão válido (ver Achado 4).
- **Controller resolution** — suporta subnamespace (`Platform\PlatformController`) via string `"Controller@method"`; parâmetros de rota são convertidos para `int` automaticamente quando numéricos, evitando `TypeError` em assinaturas como `show(int $id)`.
- **Middlewares não são globais** — são chamadas estáticas de uma linha (`AuthMiddleware::handle()`, `TenantMiddleware::handle()`) invocadas manualmente como primeira instrução de cada action. Isso significa que **esquecer a chamada = rota desprotegida**; não existe um mecanismo que force isso.
- **Erros** — qualquer `\Throwable` não capturado dentro do dispatch cai em `Router::handleError()`, que loga via `Logger::error` e renderiza uma página 500/404 com HTML/CSS inline (sem depender de views, para nunca quebrar em cascata).
- **View** — `Controller::view()` chama `View::render($view, $data, $layout='copilot')`; o layout busca `layout/{$layout}_header.php` e `_footer.php`. Dois layouts existem: `auth` (telas de login/cadastro) e `copilot` (app autenticado, sidebar + topbar).

---

## 03. Banco de dados

Todas as tabelas usam prefixo `cop_`. O schema principal (`2026-07-05_copilot_schema.sql`) tem foreign keys reais com `ON DELETE CASCADE`; os módulos adicionados depois (fila, IA, PACS) **abandonam FKs declaradas** — a integridade referencial vira responsabilidade do código PHP. Isso é uma escolha deliberada de compatibilidade com MySQL 5.7/hosting compartilhado, mas significa que nenhuma `ALTER` futura pode assumir que o banco vai impedir um `medico_id` órfão.

### Núcleo — multi-tenant e identidade

| Tabela | Papel | Criticidade |
|---|---|---|
| `cop_plans` | Planos comerciais (Starter/Professional/Enterprise) — limites de médicos, laudos/mês, features habilitadas | config |
| `cop_tenants` | Clínicas/hospitais. Guarda `pacs_api_url` + `pacs_api_token` por tenant | **crítica** |
| `cop_users` | Usuários (superadmin + médico). `role`, `status`, hash bcrypt, preferências de IA embutidas (`ia_modelo`, `ia_estilo`) | **crítica — usuários** |
| `cop_user_tenants` | N:N médico↔clínica com `role` por vínculo | **crítica — permissões** |
| `cop_medico_perfil` | Estilo de laudo do médico (vocabulário, frases favoritas) — ver Crítico 3, nome colide com `cop_medical_profiles` | ambígua |

### Fluxo clínico

| Tabela | Papel |
|---|---|
| `cop_workspaces` | Sessão de laudo aberta para um `study_uid` (referência ao PACS, não copia DICOM) |
| `cop_laudos` | **Armazena estudos/laudos** — indicação, técnica, achados, impressão, CID, sugestão de IA, versão, assinatura |
| `cop_ia_conversas` | Histórico de chat IA por workspace (role/conteúdo/tokens) |
| `cop_templates` | Máscaras de laudo por modalidade/especialidade |
| `cop_autotextos` | Atalhos de texto rápido |
| `cop_fila` | Fila de exames priorizada (SLA, urgência, status) |
| `cop_pacientes` | Cadastro de pacientes, referencia `pacs_patient_id` |
| `cop_comparativos`, `cop_vision_analises`, `cop_speech_historico`, `cop_pesquisa_historico` | Módulos de produtividade (comparação temporal, Vision AI, ditado, pesquisa clínica) |

### AI Router — 11 tabelas (o subsistema mais complexo do banco)

| Tabela | Papel | Nota |
|---|---|---|
| `cop_ai_providers` | Credenciais de cada provider de IA por usuário | **recriada** pelo Provider Wizard — coluna mudou de `api_key` → `api_key_enc` |
| `cop_ai_provider_models`, `cop_ai_provider_capabilities`, `cop_ai_provider_tests`, `cop_ai_provider_logs` | Catálogo de modelos, capacidades por tipo de provider (12 seeds), benchmark de teste, auditoria do wizard | parte do fluxo do **Wizard**, não do **Router** |
| `cop_ai_prompt_base` | Prompt-mestre por especialidade (radiologia geral, TC, RM, mamografia BI-RADS...) — 9 seeds prontos | |
| `cop_ai_prompt_templates` | Templates reutilizáveis (BI-RADS, PI-RADS, Lung-RADS, revisão final...) com variáveis `{{...}}` | |
| `cop_ai_rotas` | Roteamento inteligente: qual provider usar para cada `tipo_solicitacao` | |
| `cop_ai_historico` | **Toda chamada de IA** — prompt, resposta, tokens, custo, latência, status | auditoria de IA |
| `cop_ai_custos_diarios` | Agregado diário de custo/tokens por provider | |
| `cop_ai_logs` | Log estruturado do Router (nível/ação/contexto) | |
| `cop_ai_config`, `cop_ai_comparacoes`, `cop_medical_profiles` | Config chave/valor por usuário, comparação side-by-side entre modelos, perfil de IA (schema alternativo, órfão — ver Crítico 3) | órfã |

### Autorização PACS/DICOM — 4 tabelas (mais recente, 2026-07-08)

| Tabela | Papel |
|---|---|
| `cop_pacs_unidades` | Cada clínica/hospital com PACS próprio (Orthanc/DCM4CHEE/Horos...), inclui `pacs_ae_title`, `pacs_host/port`, URLs QIDO/WADO/STOW |
| `cop_pacs_autorizacoes` | Vínculo médico↔unidade com `codigo_medico` + `token_integracao` únicos; permissões por modalidade |
| `cop_pacs_dicom_config` | **Mapeamento completo de tags DICOM** por vínculo (StudyInstanceUID, PatientID, ReferringPhysicianName, SOPClassUID de SR = `1.2.840.10008.5.1.4.1.1.88.33`...) |
| `cop_pacs_audit_log` | Todo evento de uso (laudo gerado/assinado/enviado, token validado/inválido) por unidade |

### Tabelas que nunca devem ser alteradas sem migration formal

- `cop_users`, `cop_tenants`, `cop_user_tenants` — qualquer mudança de schema aqui afeta login/RBAC de toda a base instalada.
- `cop_laudos` — é o registro médico-legal do laudo assinado; alterar/remover coluna é risco de compliance, não só técnico.
- `cop_pacs_autorizacoes` / `cop_pacs_dicom_config` — token e mapeamento DICOM já em uso por unidades reais cadastradas.
- `cop_audit_logs` e `cop_pacs_audit_log` — trilhas de auditoria; nunca fazer `UPDATE`/`DELETE` retroativo.

---

## 04. Inventário de APIs

Não há um formato Swagger/OpenAPI no projeto; a tabela abaixo consolida `routes/web.php` com o comportamento real de cada handler (autenticação exigida, JSON vs HTML, CSRF). Convenção observada: **toda API AJAX aceita apenas a sessão de cookie do próprio app** — não existe API key, Bearer token ou CORS para consumo externo, exceto o endpoint PACS abaixo.

### Endpoints server-to-server (chamados por sistemas externos)

| Rota | Handler | Auth | Observação |
|---|---|---|---|
| `POST /api/pacs/validar-token` | `AutorizacaoPacsController@apiValidarToken` | **quebrado** | Deveria ser público (chamado pelo PACS), mas o `Router` exige sessão — ver Achado 4. Payload: `{token}` → retorna dados do médico + unidade ou código de erro (`token_invalido`, `token_expirado`, `autorizacao_<status>`) |

### APIs internas AJAX (sessão de navegador obrigatória)

| Rota | Controller@method | Payload | CSRF |
|---|---|---|---|
| `POST /api/ai/router` | `AiRouterController@apiRouter` | `{provider_id?, model, prompt, tipo, workspace_id}` | não valida |
| `POST /api/ai/provider/test` | `ProviderWizardController@apiTest` | `{provider_type, api_key, endpoint,...}` | não valida |
| `POST /api/ai/provider/discover-models` | `ProviderWizardController@apiDiscoverModels` | idem | não valida |
| `POST /api/ai/provider/validate` | `ProviderWizardController@apiValidate` | idem + `model_id` | não valida |
| `GET /api/ai/provider/models` / `capabilities` | `ProviderWizardController@apiModels/apiCapabilities` | query string | n/a (GET) |
| `GET /api/pacs/buscar?q=` | `PacsController@buscar` | query | n/a — tem **modo demo** com estudos fictícios quando PACS não configurado |
| `GET /api/templates/{id}/corpo` | `TemplatesController@getCorpo` | - | n/a |
| `POST /api/copilot/chat`, `/sugestao` | `CopilotApiController@chat/sugestao` | `{workspace_id, mensagem\|modalidade+indicacao+achados}` | não valida |
| `POST /api/vision/analisar` | `VisionAIController@analisar` | - | não valida |
| `POST /api/speech/transcrever` | `SpeechController@transcrever` | áudio | não valida |
| `POST /api/integracoes/testar` | `IntegracoesController@testar` | `{tipo, url}` | não valida |
| `GET/POST /api/medical-profile/*` | `MedicalProfileController@*` | - | **quebrado** — ver Crítico 3 |
| `POST /api/configuracoes/assinatura` | `ConfiguracoesController@salvarAssinatura` | - | **método inexistente** |

### Páginas HTML (não-API) por módulo

Os 23 controllers cobrem: Auth, Dashboard, Workspace (laudos), Fila, Pacientes, Timeline, Comparativos, Viewer, Copilot, Vision AI, Speech, Templates, Pesquisa, Analytics, Marketplace, Integrações, PACS, Autorização PACS, Configurações, AI Router (+ Provider Wizard), Medical Profile e Platform (super-admin). A lista completa de verbos/rotas está em `routes/web.php:1-181` — cada família segue o padrão REST-ish `index → novo → criar → show → salvar/atualizar → excluir`.

> **Código morto detectado:** `AiRouterController::providers/providerSalvar/providerExcluir/providerTestar` não têm rota — foram substituídos pelo `ProviderWizardController` (comentário no próprio `routes/web.php`: "Wizard de 5 etapas — substitui a view antiga"). `CopilotController::chat/sugestao` duplicam `CopilotApiController` e também não são roteados. `TemplatesController::get()` não é roteado (só `getCorpo` é). Não usar esses métodos como referência — não são exercitados em produção.

---

## 05. Frontend

### Stack real

- **Sem Vue/React/Bootstrap.** Views são PHP puro com HTML inline; JS é vanilla, embutido em `<script>` dentro de 25 views (busca confirmada).
- Fontes: Google Fonts `Inter` + `Plus Jakarta Sans` via `<link>`; ícones via Font Awesome 6.5 CDN.
- CSS por módulo, sem pré-processador: `copilot.css` (1183 linhas, base/sidebar/topbar), `workspace.css` (1168, editor de laudo), `wizard.css` (1452, Provider Wizard), `dashboard.css` (1003), `auth.css` (683).
- Cache-busting simples via `View::asset()` → `?v=ASSET_VERSION` (constante fixa `1.0.0` em `View.php` — precisa bump manual a cada deploy de CSS).

### Layouts

- `auth_header/footer.php` — telas de login/cadastro, sem sidebar.
- `copilot_header/footer.php` — app autenticado: sidebar fixa com 4 seções de nav (Central de Trabalho, Inteligência, Gestão, + nav separada para superadmin), topbar com título/subtítulo de página, barra de "impersonação" quando superadmin está simulando um médico.
- Sidebar decide o menu inteiro em runtime com `Auth::isPlatformAdmin()` — não há componentização, é `if/else` direto no header.

### Padrão de tela

Toda view autenticada recebe `pageTitle`/`pageSubtitle` do controller (exibidos na topbar) e opcionalmente `extraCss` (array de paths extras, usado pelo Wizard). Tabelas de listagem seguem paginação manual (`LIMIT/OFFSET` calculado no controller, sem helper de paginação compartilhado — repetido em `WorkspaceController` e `Platform\PlatformController::medicos`). Não há componente de paginação reutilizável; cada controller recalcula `totalPages`.

---

## 06. Autenticação & RBAC

### Login

E-mail + senha (`password_verify`, bcrypt custo 12). Sem 2FA, sem rate limiting de tentativas, sem lockout de conta. Sessão guarda o objeto `user` inteiro (menos a senha) + `user_id` + `tenant_id` + `user_tenants`.

### Papéis

`role = superadmin` (sem tenant, acessa `/platform/*`) ou `role = medico`. Um médico pode ter 0 tenants (modo *standalone*, dados isolados por `medico_id`), 1 tenant (auto-seleciona) ou N tenants (força `/selecionar-empresa`).

### Multi-tenant "tolerante a null"

Em vez de exigir tenant sempre, cada controller clínico (Workspace, Dashboard, Templates) bifurca a query manualmente: `if ($tenantId) {...com tenant_id...} else {...só medico_id...}`. Esse padrão se repete em pelo menos 6 controllers — é a forma estabelecida de suportar tanto clínicas multiusuário quanto médicos autônomos. **Qualquer nova feature clínica deve replicar esse bifurcamento**, não assumir `tenant_id` sempre presente.

### Impersonação (superadmin → médico)

`Platform\PlatformController::impersonate($medicoId)` troca `$_SESSION['user']` pelo médico-alvo, preservando o superadmin original em `original_user`/`original_user_id`. `exitImpersonate()` restaura. A sidebar mostra uma barra vermelha "Você está visualizando como X" durante a simulação. É auditado via `AuditLogger::log('impersonate_medico', ...)`.

| Camada | Onde | O que verifica |
|---|---|---|
| Global (Router) | `Router::dispatch()` | Sessão existe? senão → `/login`, exceto 4 rotas públicas |
| Por action | `AuthMiddleware::handle()` | idêntico ao global — redundante na maioria dos casos, mas necessário para consistência de mensagens |
| Por action (tenant) | `TenantMiddleware::handle()` | resolve tenant automático (1 vínculo), força seleção (N vínculos), bloqueia tenant inativo forçando logout |
| Por action (admin) | `PlatformAdminMiddleware::handle()` ou `requirePlatformAdmin()` inline | `role === 'superadmin'` |

---

## 07. Logs & auditoria

Três trilhas paralelas, nenhuma centralizada:

| Trilha | Implementação | Onde vai parar |
|---|---|---|
| Erros de aplicação | `App\Core\Logger::error/info()` | Arquivo texto `storage/logs/copilot-YYYY-MM-DD.log`, uma linha JSON-ish por evento |
| Erros PHP nativos | `ini_set('error_log', ...)` no bootstrap | `storage/logs/php_errors.log` |
| Auditoria de negócio | `App\Core\Audit\AuditLogger::log()` | Tabela `cop_audit_logs` (ação, entidade, ip, tenant, usuário) — usada hoje só em `Platform\PlatformController` (toggle status, impersonação) |
| Auditoria de IA | `AiRouterService::log()` + inserts diretos em `cop_ai_historico` | Toda chamada de IA com prompt/resposta/custo — **nenhuma anonimização/expiração** configurada |
| Auditoria PACS | `AutorizacaoPacsController::registrarLog()` | `cop_pacs_audit_log` — eventos de token e laudo por unidade |
| Auditoria do Wizard | `ProviderWizardController::logAction()` | `cop_ai_provider_logs` |

Não há correlação entre as trilhas (nenhum `request_id`/`trace_id` comum), e três delas persistem em bancos separados do `cop_audit_logs` "oficial". Qualquer nova feature de auditoria deve decidir explicitamente em qual trilha entrar — não existe um serviço único de logging de negócio.

---

## 08. DICOM / PACS

> **Ponto-chave de arquitetura:** Este repositório é o "Copilot": a camada de laudo assistido por IA. Ele **não implementa** C-ECHO/C-FIND/C-MOVE/C-STORE nem QIDO-RS/WADO-RS/STOW-RS em código próprio — `PacsService` chama uma API REST própria do VOXEL PACS (`GET /api/studies`, `/api/studies/{uid}/series`, `/api/health`) via cURL com Bearer token. O protocolo DICOM real fica inteiramente do lado do PACS.

### Onde cada conceito DICOM aparece no código

| Conceito | Onde vive hoje |
|---|---|
| `StudyInstanceUID` | `cop_workspaces.study_uid`, `cop_pacs_dicom_config.tag_study_instance_uid` — chave de amarração entre laudo e exame no PACS |
| `AccessionNumber` | `cop_fila.accession`, `cop_comparativos.accession_atual/anterior`, `cop_pacs_dicom_config.tag_accession_number` |
| `PatientID` / `PatientName` | `cop_pacientes.pacs_patient_id`, tags espelhadas em `cop_pacs_dicom_config` |
| `Modality` | coluna `modalidade` repetida em quase toda tabela clínica (fila, laudos, comparativos, vision, workspaces) |
| `InstitutionName` | `cop_pacs_unidades.nome_instituicao` → espelhado em `cop_pacs_dicom_config.tag_institution_name` |
| Nome do médico em formato DICOM (`Sobrenome^Nome`) | `AutorizacaoPacsController::formatarNomeDicom()` — conversão simples por `explode(' ')`, não trata partículas ("de", "dos") nem nomes compostos com hífen |
| `SOPClassUID` de Structured Report | hardcoded `1.2.840.10008.5.1.4.1.1.88.33` em `criarDicomConfigPadrao()` — correto para SR, mas fixo (não hospeda outros SOP Classes) |
| QIDO/WADO/STOW | apenas **campos de URL** em `cop_pacs_unidades` (`pacs_wado_url`, `pacs_stow_url`, `pacs_qido_url`) — **nenhum código ainda consome essas URLs**; são placeholders de configuração para uma integração futura |

### Fluxo de autorização PACS↔médico (o mais elaborado do sistema)

1. Unidade (clínica) é cadastrada em `cop_pacs_unidades` com `codigo_unidade` único.
2. Médico, dentro do Copilot, informa esse código + um token fornecido pela unidade (`AutorizacaoPacsController::cadastrar()`).
3. Copilot gera um `codigo_medico` único (`MED-<hash>`) e grava o vínculo em `cop_pacs_autorizacoes`, junto com uma config DICOM padrão (SR, `COMPLETE/VERIFIED`, charset `ISO_IR 192`).
4. O PACS, ao receber um laudo, chamaria `POST /api/pacs/validar-token` para validar o médico — **hoje bloqueado pelo Achado 4**.
5. Todo evento (token validado/inválido, autorização criada/revogada, laudo enviado) é gravado em `cop_pacs_audit_log`.

---

## 09. Integrações & camada de abstração proposta (Fases 9 e 16)

### Como integrações externas são feitas hoje

**PACS (`PacsService`)** — Uma classe por integração, construtor recebe `baseUrl`/`token` explícitos ou os lê de `cop_tenants`. Método `get()` privado encapsula cURL + tratamento de erro + log. Sem retry, sem circuit breaker, timeout fixo 15s, `CURLOPT_SSL_VERIFYPEER => false` (!) hardcoded.

**IA direta (`CopilotAIService`)** — Fala só com OpenAI, hardcoded. Monta prompt com perfil do médico + histórico, grava conversa em `cop_ia_conversas`. Timeout 60s, sem retry.

**IA multi-provider (`AiRouterService`)** — O único caso que já implementa um Strategy de verdade: `callProvider()` despacha para `callOpenAI/callAnthropic/callGemini/callOllama` por `provider_tipo`. É o modelo mais próximo do que a Fase 16 pede — mas está desconectado do Wizard (Crítico 1).

Não existe hoje: pasta `Integration/`, interfaces, DI container, factory, adapter formal, observer/eventos, fila assíncrona, cache ou circuit breaker. Cada integração é uma classe concreta com cURL inline. O `ProviderWizardController` é o exemplo mais próximo de um "cadastro de integração genérico" (tipo + credenciais + teste de conexão + descoberta de capacidades), mas mistura essa lógica com a criptografia e a persistência no mesmo controller de 957 linhas.

### Camada de abstração proposta para as 2 novas APIs

Recomendação: generalizar o padrão já usado pelo `AiRouterService` (Strategy por tipo de provider) em vez de inventar um padrão novo — é o que já existe, já está no banco (`cop_ai_provider_capabilities` é literalmente um catálogo de adapters) e a equipe já entende.

| Peça | Papel | Reaproveita |
|---|---|---|
| `App\Integrations\IntegrationClientInterface` | Contrato único: `testConnection()`, `request(string $op, array $payload): array` | novo — hoje não existe interface nenhuma |
| `App\Integrations\{Api1,Api2}Client` | Um adapter concreto por API externa, cada um resolvendo auth (API key/OAuth2/JWT) e payload (JSON/XML) próprios | mesmo padrão de `AiRouterService::call*()` |
| `App\Integrations\IntegrationFactory::make(string $tipo)` | Decide qual client instanciar — equivalente ao `switch($provider_tipo)` de `callProvider()`, mas isolado numa factory reaproveitável | generaliza o `switch` já existente |
| Tabela `cop_integracoes_externas` (nova, ou reaproveitar `cop_integracoes` já existente) | credenciais + endpoint + status, no mesmo espírito de `cop_ai_providers` | `cop_integracoes` já existe (migration `modulos_medico`) mas está sem uso real além da tela estática de `IntegracoesController` |
| Retry/timeout/circuit breaker | Nenhum serviço atual implementa isso — é a maior lacuna real. Adicionar um wrapper simples (`curl` com `CURLOPT_TIMEOUT` + contagem de falhas consecutivas por integração, já que `cop_ai_providers.retry` existe como coluna mas **não é lido em nenhum lugar do PHP**) | coluna já existe, lógica não |

> **Antes de escrever a primeira linha das 2 novas integrações:** decida explicitamente se elas vão *substituir* o `AiRouterService` quebrado (consertando o Crítico 1/2 no caminho) ou se vão viver ao lado dele como um módulo novo e paralelo. Construir em cima do AI Router sem corrigir a divergência de schema propaga o bug para a nova feature.

---

## 10. Design patterns identificados

| Padrão | Onde | Nota |
|---|---|---|
| MVC | estrutura geral | sem camada Model — controllers falam com PDO diretamente |
| Singleton | `Database::getInstance()` | uma conexão PDO por request |
| Front Controller | `public/index.php` | ponto de entrada único |
| Strategy | `AiRouterService::callProvider()` | único uso "de manual" no projeto |
| Template Method (informal) | `View::render()` | header/footer envolvem o conteúdo da view |
| Static Service / Utility | `MailService`, `AiRouterService`, `Logger`, `AuditLogger` | tudo com métodos `static`, sem interface/DI |
| Active-Record-ish (sem classe) | todos os controllers | SQL monta o objeto de domínio na hora, sem Model dedicado |

Ausentes: Repository, Factory formal, Observer/Event, Command, Mediator, DI container, Hexagonal/Clean Architecture, DDD. Isso não é uma falha — é coerente com a decisão de rodar em hosting compartilhado sem Composer. Mas significa que introduzir esses padrões para as 2 novas APIs é uma escolha nova para o projeto, não uma continuidade; vale alinhar com o time antes de generalizar demais.

---

## 11. Dependências

| Categoria | Detalhe |
|---|---|
| Gerenciador de pacotes | **Nenhum.** Sem `composer.json`, sem `package.json`. Autoload PSR-4 manual (13 linhas) |
| PHP | 7.4+ (typed properties, `match` em `AuthController` sugere uso real de 8.0+ também — `match` não existe em 7.4, checar versão real do servidor de produção) |
| Banco | MySQL 5.7 / MariaDB 10.3+, `utf8mb4`, InnoDB. Todas as migrations evitam sintaxe MySQL 8-only |
| Servidor | Apache + `mod_rewrite` (`.htaccess` na raiz e em `public/`), pensado para cPanel/HostGator — sem menção a Nginx/Docker |
| Cache/fila | Nenhum Redis/Memcached/cron mencionado no código ou README |
| Frontend | Google Fonts (Inter, Plus Jakarta Sans) + Font Awesome 6.5, ambos via CDN externo — dependência externa em runtime, sem fallback local |
| E-mail | `MailService` implementa SMTP via socket puro (sem PHPMailer) com fallback para `mail()` nativo do PHP |
| APIs de IA externas | OpenAI, Anthropic, Google Gemini, Azure OpenAI, Ollama/LM Studio, OpenRouter, DeepSeek, Mistral, Qwen — todas via cURL cru, sem SDK oficial de nenhuma |

---

## 12. Segurança

| Área | Situação | Risco |
|---|---|---|
| SQL Injection | Praticamente tudo usa `PDO::prepare` com bind. Único ponto fora do padrão: `AiRouterController.php:24` interpola `{$uid}` direto num `$pdo->query()` — `$uid` vem de `Auth::userId()` (forçado `int`), então não é explorável hoje, mas quebra a convenção do resto do código | baixo, mas inconsistente |
| CSRF | `Controller::csrfToken()/csrfValidate()` existe, mas só é chamado em `WorkspaceController`, `AiRouterController` e `PacsController`. `AutorizacaoPacsController`, `ConfiguracoesController`, `ProviderWizardController`, `MedicalProfileController` e `IntegracoesController` processam POST sem validar token | **alto** — ações sensíveis (revogar autorização PACS, trocar senha, salvar credencial de IA) sem CSRF |
| Criptografia de segredos | `ProviderWizardController::encrypt()` usa AES-256-CBC sem HMAC (sem autenticação do ciphertext) e chave com fallback hardcoded já citado (Crítico 2) | **alto** |
| Sessão | `httponly`, `SameSite=Lax`, path customizado. Sem regeneração de `session_id()` pós-login (fixation), sem timeout de inatividade além do `SESSION_LIFETIME` do `.env` (não localizado onde esse valor é de fato aplicado ao GC do PHP) | médio |
| Senhas | `password_hash`/`password_verify`, bcrypt custo 12 — correto. Sem rate limit de login, sem 2FA | médio |
| Headers | `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` setados tanto no PHP quanto no `.htaccess` (duplicado, inofensivo). Sem `Content-Security-Policy` | baixo |
| Verificação TLS | `PacsService` e `ProviderWizardController` usam `CURLOPT_SSL_VERIFYPEER => false` ao chamar APIs externas (PACS e providers de IA) | **alto** — MITM em produção |
| Uploads | Pasta `storage/uploads/` existe mas nenhum controller lido processa upload de arquivo ainda — ponto a revisar quando essa feature nascer | n/a hoje |
| Arquivos sensíveis | `.htaccess` bloqueia acesso direto a `.env/.log/.sql/.md/.json/.lock/.gitignore` | correto |
| LGPD | Dados de paciente (nome, CPF, nascimento) em `cop_pacientes` sem coluna de consentimento/anonimização; `cop_ai_historico` guarda prompt/resposta completos (pode conter dado clínico identificável) sem TTL/expurgo configurado | médio-alto — depende do uso real |

---

## 13. Performance

- **Sem N+1 grave detectado** nos controllers lidos — as listagens usam `JOIN` único (ex.: `cop_laudos JOIN cop_workspaces`) em vez de loop de queries.
- **Paginação manual repetida** — mesmo bloco `LIMIT/OFFSET` + `COUNT(*)` reescrito em `WorkspaceController::index` e `PlatformController::medicos`; candidato óbvio a um helper (ver §15).
- **Sem cache** nenhuma — toda página faz round-trip completo ao MySQL, inclusive dados quase-estáticos como `cop_ai_provider_capabilities` (12 linhas, muda raramente) e `cop_plans` (3 linhas).
- **Assets CDN externos** (Google Fonts, Font Awesome) bloqueiam o first paint até resolverem — sem preload/self-host.
- **CSS por módulo sem minificação/bundling** — 5489 linhas de CSS somadas, servidas cruas; ganho fácil com um build step mínimo (mesmo sem Node, um script PHP de concat+minify resolveria).
- **Chamadas de IA são síncronas e bloqueantes** — `CopilotAIService`/`AiRouterService` fazem cURL síncrono na mesma request HTTP (timeout até 120s no Router). Sem fila/job assíncrono, uma resposta lenta do provider trava a thread PHP inteira do request do usuário.

---

## 14. Módulos de negócio

| Módulo | Objetivo | Tabelas-chave | Risco principal |
|---|---|---|---|
| Auth/Cadastro | Login, primeiro acesso de médico com aprovação por CRM, seleção de clínica | `cop_users`, `cop_user_tenants` | sem 2FA/rate-limit |
| Dashboard | KPIs do médico (laudos hoje/mês, templates) | `cop_laudos`, `cop_medico_perfil` | zera tudo em modo standalone sem tenant — comportamento intencional, mas não óbvio para quem lê só a view |
| Workspace/Laudos | Núcleo clínico: abrir estudo, redigir com IA, assinar | `cop_workspaces`, `cop_laudos`, `cop_ia_conversas` | é o único fluxo com CSRF ativo — manter esse padrão ao estender |
| Fila Inteligente | Priorização de exames por SLA/urgência | `cop_fila` | badge "18" na sidebar é hardcoded no header, não vem do banco |
| Copilot IA | Chat + sugestão de laudo via OpenAI direto | `cop_ia_conversas` | duplica lógica que também existe no AI Router — dois caminhos de IA coexistindo |
| AI Router / Provider Wizard | Cadastro multi-provider + roteamento inteligente + custos | 11 tabelas `cop_ai_*` | ver Crítico 1/2 — hoje desconectado internamente |
| Autorização PACS | Vincular médico↔unidade com token, mapear tags DICOM | 4 tabelas `cop_pacs_*` | ver Achado 4 — endpoint de validação inacessível externamente |
| Configurações | Perfil, senha, preferências de IA, aba embutida de Autorização PACS | `cop_users`, `cop_medico_perfil` | 2 rotas quebradas (Achado 5) |
| Platform (super-admin) | Gestão de médicos/planos, impersonação | `cop_users`, `cop_plans` | única área com `AuditLogger` ativo — bom padrão a copiar |
| Medical Profile | Perfil de aprendizado de IA (intenção: separado do estilo de laudo) | `cop_medico_perfil` / `cop_medical_profiles` | ver Crítico 3 — não funcional hoje |

---

## 15. Pontos de extensão

### ✅ Seguro reaproveitar

- `App\Core\Controller::view()/json()/redirect()/csrfToken()/csrfValidate()` — sempre estender daqui, nunca duplicar `header('Content-Type...')` manual.
- Padrão de bifurcação `if ($tenantId) {...} else {...}` para qualquer nova query clínica.
- `AuditLogger::log()` para qualquer ação sensível nova — hoje subutilizado, é o lugar certo.
- Padrão Strategy de `AiRouterService::callProvider()` como modelo para as 2 novas integrações (§09).

### ⚠️ Não tocar sem plano de migração

- Colunas de `cop_users`, `cop_tenants`, `cop_laudos` — schema já em produção.
- `cop_ai_providers` — antes de mexer, decidir o destino do Crítico 1 (ver §09).
- `MedicalProfileController` como está — não copiar seu padrão de acesso a banco (`$this->db->fetch()` não existe); se for revivido, reescrever contra `Database::getInstance()` real.

### Antes de adicionar qualquer rota nova

1. Registrar em `routes/web.php` seguindo o agrupamento por comentário de seção existente.
2. Chamar o middleware certo como primeira linha da action (`AuthMiddleware`/`TenantMiddleware`/`PlatformAdminMiddleware`) — não existe enforcement automático.
3. Se a rota precisa ser chamada por um sistema externo (não-browser), adicioná-la à lista `publicRoutes` do `Router` *ou* criar um mecanismo de autenticação por API key — hoje não existe meio-termo.
4. Se o POST altera dado sensível, chamar `$this->csrfValidate()` — hoje é opt-in, não padrão.

---

## 16. Checklist final (Fase 17)

Responda estas perguntas para qualquer feature nova, incluindo as 2 integrações externas mencionadas no briefing:

- [ ] Quais módulos são impactados? (ver mapa da §14)
- [ ] Quais tabelas serão usadas — são as tabelas "vivas" corretas, ou existe uma tabela órfã/duplicada com nome parecido? (ex.: `cop_medico_perfil` vs `cop_medical_profiles`)
- [ ] Há API externa envolvida? Se sim, ela passa pelo padrão Strategy do §09 ou reintroduz mais uma classe cURL isolada?
- [ ] Há impacto em autenticação/permissões? A rota nova precisa entrar em `publicRoutes` (chamada externa) ou exigir sessão (uso interno)?
- [ ] Existe componente reutilizável (paginação, CSRF, AuditLogger) sendo ignorado?
- [ ] Quais testes devem rodar? (o projeto não tem suíte automatizada hoje — validar manualmente login, fluxo de laudo e a tela específica alterada, nas duas variantes com/sem tenant)
- [ ] Há risco de regressão nos 5 achados críticos do sumário executivo? Em especial: a feature nova depende de `AiRouterService` ler `cop_ai_providers.api_key`?
- [ ] A feature segue o padrão arquitetural existente (fat controller + Service para I/O externo + PDO direto) ou está introduzindo Repository/DI/Model pela primeira vez? Se sim, alinhar antes — é mudança de convenção, não só de código.
- [ ] É necessária uma migration? Se sim, seguir o padrão dos 7 arquivos existentes: `SET FOREIGN_KEY_CHECKS = 0/1`, sintaxe compatível com MySQL 5.7, sem `PROCEDURE/FUNCTION/EVENT/TRIGGER`, nome de arquivo `YYYY-MM-DD_descricao.sql`.
- [ ] Há impacto em DICOM/PACS/Viewer? Se sim, os campos já existem em `cop_pacs_dicom_config`/`cop_pacs_unidades` antes de criar tabela nova — verificar duplicação.

---

*VOXEL Copilot · Manual Técnico de Arquitetura · gerado por engenharia reversa do código-fonte em `c:\xampp\htdocs\dashboard\voxelcopilot` · nenhuma linha de produto foi alterada durante esta análise.*
