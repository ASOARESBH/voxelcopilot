-- ============================================================
-- VOXEL Copilot -- AI Router + Medical Profiles
-- MariaDB 5.7 / MySQL 5.7 / HostGator / cPanel
-- SEM: IF NOT EXISTS em colunas, PROCEDURE, FUNCTION,
--      EVENT, TRIGGER, INFORMATION_SCHEMA, MySQL 8 syntax
-- ============================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ------------------------------------------------------------
-- 1. AI PROVIDERS
-- ------------------------------------------------------------
CREATE TABLE cop_ai_providers (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED NOT NULL,
    tenant_id       INT UNSIGNED DEFAULT NULL,
    nome            VARCHAR(120) NOT NULL,
    descricao       TEXT,
    provider_tipo   ENUM('openai','anthropic','google_gemini','azure_openai','ollama','openrouter','deepseek','mistral','qwen','lm_studio','amazon_bedrock','vertex_ai','custom') NOT NULL DEFAULT 'openai',
    api_key         TEXT,
    endpoint        VARCHAR(500),
    modelo_padrao   VARCHAR(120),
    temperatura     DECIMAL(3,2) NOT NULL DEFAULT 0.10,
    max_tokens      INT NOT NULL DEFAULT 4000,
    timeout_seg     INT NOT NULL DEFAULT 120,
    retry           TINYINT NOT NULL DEFAULT 3,
    top_p           DECIMAL(3,2) NOT NULL DEFAULT 1.00,
    frequency_penalty DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    presence_penalty  DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    idioma          ENUM('pt','en','es') NOT NULL DEFAULT 'pt',
    prompt_base_id  INT UNSIGNED DEFAULT NULL,
    is_default      TINYINT(1) NOT NULL DEFAULT 0,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    ultima_utilizacao DATETIME DEFAULT NULL,
    latencia_ms     INT DEFAULT NULL,
    versao_modelo   VARCHAR(80),
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user (user_id),
    KEY idx_tenant (tenant_id),
    KEY idx_tipo (provider_tipo),
    KEY idx_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. AI MODELOS (catálogo de modelos por provider)
-- ------------------------------------------------------------
CREATE TABLE cop_ai_modelos (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    provider_id     INT UNSIGNED NOT NULL,
    nome            VARCHAR(120) NOT NULL,
    nome_display    VARCHAR(120),
    descricao       TEXT,
    contexto_tokens INT DEFAULT NULL,
    preco_input     DECIMAL(10,6) DEFAULT NULL COMMENT 'USD por 1k tokens',
    preco_output    DECIMAL(10,6) DEFAULT NULL COMMENT 'USD por 1k tokens',
    suporta_vision  TINYINT(1) NOT NULL DEFAULT 0,
    suporta_tools   TINYINT(1) NOT NULL DEFAULT 0,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_provider (provider_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. PROMPT BASE (por especialidade)
-- ------------------------------------------------------------
CREATE TABLE cop_ai_prompt_base (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED NOT NULL,
    tenant_id       INT UNSIGNED DEFAULT NULL,
    especialidade   ENUM('radiologia_geral','tomografia','ressonancia','raio_x','ultrassom','mamografia','pet','cardiologia','neurologia','personalizado') NOT NULL DEFAULT 'radiologia_geral',
    nome            VARCHAR(200) NOT NULL,
    versao          VARCHAR(20) NOT NULL DEFAULT '1.0',
    modelo_recomendado VARCHAR(120),
    temperatura     DECIMAL(3,2) NOT NULL DEFAULT 0.10,
    prompt          LONGTEXT NOT NULL,
    notas           TEXT,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user (user_id),
    KEY idx_especialidade (especialidade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. PROMPT TEMPLATES (reutilizáveis)
-- ------------------------------------------------------------
CREATE TABLE cop_ai_prompt_templates (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED NOT NULL,
    tenant_id       INT UNSIGNED DEFAULT NULL,
    nome            VARCHAR(200) NOT NULL,
    tipo            ENUM('laudo_estruturado','resumo_clinico','comparacao_temporal','correcao_ortografica','revisao_final','conclusao','cid','snomed','radlex','bi_rads','pi_rads','lung_rads','personalizado') NOT NULL DEFAULT 'laudo_estruturado',
    descricao       TEXT,
    prompt          LONGTEXT NOT NULL,
    variaveis       TEXT COMMENT 'JSON com variáveis do template',
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    usos            INT NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user (user_id),
    KEY idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. ROTAS INTELIGENTES
-- ------------------------------------------------------------
CREATE TABLE cop_ai_rotas (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED NOT NULL,
    tenant_id       INT UNSIGNED DEFAULT NULL,
    nome            VARCHAR(200) NOT NULL,
    tipo_solicitacao ENUM('gerar_laudo','comparacao','pesquisa','resumo','correcao','cid','snomed','personalizado') NOT NULL DEFAULT 'gerar_laudo',
    provider_id     INT UNSIGNED NOT NULL,
    modelo          VARCHAR(120),
    prompt_base_id  INT UNSIGNED DEFAULT NULL,
    temperatura     DECIMAL(3,2) DEFAULT NULL,
    max_tokens      INT DEFAULT NULL,
    condicoes       TEXT COMMENT 'JSON com condições de roteamento',
    prioridade      TINYINT NOT NULL DEFAULT 1,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user (user_id),
    KEY idx_tipo (tipo_solicitacao),
    KEY idx_provider (provider_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. HISTÓRICO DO AI ROUTER (cada chamada)
-- ------------------------------------------------------------
CREATE TABLE cop_ai_historico (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED NOT NULL,
    tenant_id       INT UNSIGNED DEFAULT NULL,
    workspace_id    INT UNSIGNED DEFAULT NULL,
    provider_id     INT UNSIGNED DEFAULT NULL,
    provider_nome   VARCHAR(120),
    modelo          VARCHAR(120),
    prompt_base_id  INT UNSIGNED DEFAULT NULL,
    tipo_solicitacao VARCHAR(80),
    prompt_enviado  LONGTEXT,
    resposta        LONGTEXT,
    tokens_input    INT DEFAULT 0,
    tokens_output   INT DEFAULT 0,
    tokens_total    INT DEFAULT 0,
    custo_usd       DECIMAL(10,6) DEFAULT 0,
    tempo_ms        INT DEFAULT 0,
    temperatura     DECIMAL(3,2) DEFAULT NULL,
    versao_modelo   VARCHAR(80),
    aprovado_por    INT UNSIGNED DEFAULT NULL,
    editado_por     INT UNSIGNED DEFAULT NULL,
    status          ENUM('ok','erro','timeout','cancelado') NOT NULL DEFAULT 'ok',
    erro_msg        TEXT,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user (user_id),
    KEY idx_tenant (tenant_id),
    KEY idx_workspace (workspace_id),
    KEY idx_provider (provider_id),
    KEY idx_created (created_at),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 7. TOKENS E CUSTOS (agregado diário)
-- ------------------------------------------------------------
CREATE TABLE cop_ai_custos_diarios (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED NOT NULL,
    tenant_id       INT UNSIGNED DEFAULT NULL,
    data_ref        DATE NOT NULL,
    provider_id     INT UNSIGNED DEFAULT NULL,
    provider_nome   VARCHAR(120),
    modelo          VARCHAR(120),
    total_chamadas  INT NOT NULL DEFAULT 0,
    tokens_input    BIGINT NOT NULL DEFAULT 0,
    tokens_output   BIGINT NOT NULL DEFAULT 0,
    tokens_total    BIGINT NOT NULL DEFAULT 0,
    custo_usd       DECIMAL(10,4) NOT NULL DEFAULT 0,
    tempo_medio_ms  INT DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_diario (user_id, data_ref, provider_id),
    KEY idx_user (user_id),
    KEY idx_data (data_ref)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 8. LOGS DO AI ROUTER
-- ------------------------------------------------------------
CREATE TABLE cop_ai_logs (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED DEFAULT NULL,
    tenant_id       INT UNSIGNED DEFAULT NULL,
    nivel           ENUM('info','warning','error','debug') NOT NULL DEFAULT 'info',
    provider_nome   VARCHAR(120),
    modelo          VARCHAR(120),
    acao            VARCHAR(200),
    mensagem        TEXT,
    contexto        TEXT COMMENT 'JSON com dados extras',
    ip              VARCHAR(45),
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user (user_id),
    KEY idx_nivel (nivel),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 9. CONFIGURAÇÕES DO AI ROUTER
-- ------------------------------------------------------------
CREATE TABLE cop_ai_config (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED NOT NULL,
    tenant_id       INT UNSIGNED DEFAULT NULL,
    chave           VARCHAR(100) NOT NULL,
    valor           TEXT,
    descricao       VARCHAR(300),
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_chave (user_id, chave),
    KEY idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 10. MEDICAL PROFILES
-- ------------------------------------------------------------
CREATE TABLE cop_medical_profiles (
    id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id                 INT UNSIGNED NOT NULL,
    provider_id             INT UNSIGNED DEFAULT NULL COMMENT 'Provider IA preferido',
    modelo_preferido        VARCHAR(120),
    temperatura             DECIMAL(3,2) NOT NULL DEFAULT 0.10,
    idioma                  ENUM('pt','en','es') NOT NULL DEFAULT 'pt',
    estilo_laudo            ENUM('objetivo','detalhado','estruturado') NOT NULL DEFAULT 'estruturado',
    templates_favoritos     TEXT COMMENT 'JSON array de IDs',
    terminologia_preferencial TEXT COMMENT 'JSON array de termos',
    forma_conclusao         ENUM('curta','completa','topicos') NOT NULL DEFAULT 'completa',
    gerar_auto_ao_abrir     TINYINT(1) NOT NULL DEFAULT 0,
    prompt_personalizado    LONGTEXT,
    vocabulario_aprendido   LONGTEXT COMMENT 'JSON com termos frequentes',
    frases_favoritas        LONGTEXT COMMENT 'JSON com frases salvas',
    precisao_ia             DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    total_laudos            INT NOT NULL DEFAULT 0,
    total_correcoes         INT NOT NULL DEFAULT 0,
    created_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user (user_id),
    KEY idx_provider (provider_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 11. COMPARAÇÕES ENTRE MODELOS (Comparar IA)
-- ------------------------------------------------------------
CREATE TABLE cop_ai_comparacoes (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED NOT NULL,
    workspace_id    INT UNSIGNED DEFAULT NULL,
    prompt_enviado  LONGTEXT,
    resultados      LONGTEXT COMMENT 'JSON com respostas de cada modelo',
    modelo_escolhido VARCHAR(120),
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user (user_id),
    KEY idx_workspace (workspace_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- DADOS INICIAIS — Prompt Base por especialidade
-- ------------------------------------------------------------
INSERT INTO cop_ai_prompt_base (user_id, especialidade, nome, versao, modelo_recomendado, temperatura, prompt) VALUES
(1, 'radiologia_geral', 'Radiologia Geral - Padrao', '1.0', 'gpt-4o', 0.10,
'Voce e um radiologista especialista em diagnostico por imagem. Analise o exame descrito e gere um laudo radiologico estruturado em portugues brasileiro, com linguagem tecnica, clara e objetiva. Inclua: Indicacao clinica, Tecnica utilizada, Achados radiologicos detalhados, Impressao diagnostica e Recomendacoes quando pertinente. Seja preciso, evite ambiguidades e use terminologia medica padronizada.'),

(1, 'tomografia', 'Tomografia Computadorizada - Padrao', '1.0', 'gpt-4o', 0.10,
'Voce e um radiologista especialista em tomografia computadorizada. Analise o exame de TC descrito e gere um laudo estruturado. Descreva sistematicamente todos os orgaos e estruturas visualizados, identifique alteracoes, mensure lesoes quando indicado, e forneça impressao diagnostica clara com hipoteses em ordem de probabilidade.'),

(1, 'ressonancia', 'Ressonancia Magnetica - Padrao', '1.0', 'gpt-4o', 0.10,
'Voce e um radiologista especialista em ressonancia magnetica. Analise o exame de RM descrito e gere um laudo detalhado. Descreva as sequencias utilizadas, os achados em cada sequencia, caracterize lesoes quanto a sinal, morfologia e realce pelo contraste quando aplicavel. Forneca impressao diagnostica com correlacao clinica.'),

(1, 'raio_x', 'Radiografia Simples - Padrao', '1.0', 'gpt-4o', 0.10,
'Voce e um radiologista especialista em radiologia convencional. Analise a radiografia descrita e gere um laudo objetivo. Avalie sistematicamente todas as estruturas visiveis, identifique alteracoes e forneça impressao diagnostica concisa.'),

(1, 'ultrassom', 'Ultrassonografia - Padrao', '1.0', 'gpt-4o', 0.10,
'Voce e um radiologista especialista em ultrassonografia. Analise o exame de US descrito e gere um laudo estruturado. Descreva os orgaos avaliados com suas dimensoes, ecotextura e ecogenicidade. Identifique e caracterize lesoes focais. Forneca impressao diagnostica com classificacao quando aplicavel.'),

(1, 'mamografia', 'Mamografia - BI-RADS', '1.0', 'gpt-4o', 0.10,
'Voce e um radiologista especialista em mastologia e mamografia. Analise o exame descrito e gere um laudo seguindo o sistema BI-RADS. Descreva a composicao mamaria, identifique e caracterize achados (nodulos, calcificacoes, assimetrias, distorcoes), e classifique conforme BI-RADS 0-6 com recomendacao de conduta.'),

(1, 'pet', 'PET-CT - Padrao', '1.0', 'gpt-4o', 0.10,
'Voce e um medico nuclear especialista em PET-CT. Analise o exame descrito e gere um laudo estruturado. Descreva a distribuicao fisiologica do radiofarmaco, identifique focos de hipercaptacao patologica, mensure SUVmax das lesoes relevantes e forneca impressao diagnostica com estadiamento quando pertinente.'),

(1, 'cardiologia', 'Cardiologia - Padrao', '1.0', 'gpt-4o', 0.10,
'Voce e um cardiologista especialista em imagem cardiovascular. Analise o exame descrito e gere um laudo estruturado. Avalie funcao ventricular, morfologia cardiaca, valvas, pericardio e grandes vasos quando visualizados. Forneca impressao diagnostica com correlacao clinica.'),

(1, 'neurologia', 'Neurologia - Padrao', '1.0', 'gpt-4o', 0.10,
'Voce e um neurorradiologista especialista em imagem do sistema nervoso. Analise o exame descrito e gere um laudo detalhado. Avalie sistematicamente o parenquima cerebral, estruturas da fossa posterior, espacos liquoricos, vasos e estruturas osseas. Identifique e caracterize lesoes com diagnostico diferencial.');

-- ------------------------------------------------------------
-- DADOS INICIAIS — Prompt Templates
-- ------------------------------------------------------------
INSERT INTO cop_ai_prompt_templates (user_id, nome, tipo, descricao, prompt) VALUES
(1, 'Laudo Estruturado Completo', 'laudo_estruturado', 'Template padrao para laudo estruturado com todas as secoes', 'Gere um laudo radiologico estruturado com as seguintes secoes:\n\n**INDICACAO:** {{indicacao}}\n\n**TECNICA:** {{tecnica}}\n\n**ACHADOS:** Descreva detalhadamente todos os achados.\n\n**IMPRESSAO:** Conclusao diagnostica objetiva.\n\n**RECOMENDACOES:** Condutas sugeridas quando pertinente.'),

(1, 'Resumo Clinico', 'resumo_clinico', 'Resumo objetivo do exame para comunicacao clinica', 'Com base nos achados do exame de {{modalidade}} de {{paciente}}, gere um resumo clinico objetivo em 3-5 linhas, destacando os achados principais e a impressao diagnostica, adequado para comunicacao com o medico solicitante.'),

(1, 'Comparacao Temporal', 'comparacao_temporal', 'Comparacao com exame anterior', 'Compare o exame atual de {{modalidade}} com o exame anterior de {{data_anterior}}. Descreva: 1) Achados estáveis, 2) Achados com progressao, 3) Achados com regressao, 4) Achados novos. Forneca conclusao sobre a evolucao.'),

(1, 'Correcao Ortografica', 'correcao_ortografica', 'Corrige e melhora o texto do laudo', 'Corrija a ortografia, gramatica e pontuacao do seguinte laudo radiologico, mantendo toda a terminologia medica e o conteudo clinico intactos. Apenas corrija erros linguisticos:\n\n{{texto}}'),

(1, 'Revisao Final', 'revisao_final', 'Revisao completa antes da assinatura', 'Revise o seguinte laudo radiologico e verifique: 1) Consistencia clinica, 2) Completude das secoes, 3) Clareza da impressao diagnostica, 4) Adequacao das recomendacoes. Aponte melhorias sem alterar o conteudo clinico:\n\n{{texto}}'),

(1, 'Classificacao BI-RADS', 'bi_rads', 'Classifica achados mamograficos conforme BI-RADS', 'Com base nos achados descritos, classifique conforme o sistema BI-RADS:\n\nAchados: {{achados}}\n\nForneça: Categoria BI-RADS (0-6), Descricao da categoria e Recomendacao de conduta.'),

(1, 'Classificacao PI-RADS', 'pi_rads', 'Classifica lesoes prostaticas conforme PI-RADS', 'Com base nos achados de RM de prostata descritos, classifique conforme PI-RADS v2.1:\n\nAchados: {{achados}}\n\nForneça: Categoria PI-RADS (1-5), Localizacao na prostata e Recomendacao.'),

(1, 'Lung-RADS', 'lung_rads', 'Classifica nodulos pulmonares conforme Lung-RADS', 'Com base nos nodulos pulmonares descritos, classifique conforme Lung-RADS:\n\nAchados: {{achados}}\n\nForneça: Categoria Lung-RADS, Caracteristicas do nodulo e Recomendacao de seguimento.');

SET foreign_key_checks = 1;
