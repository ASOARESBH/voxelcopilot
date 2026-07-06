-- ============================================================
-- VOXEL Copilot — Provider Wizard
-- Compatível com MariaDB 5.7 / MySQL 5.7 / HostGator / cPanel
-- SEM: IF NOT EXISTS em colunas, PROCEDURE, FUNCTION, EVENT
-- ============================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ------------------------------------------------------------
-- 1. cop_ai_providers (substitui tabela anterior se existir)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS cop_ai_provider_capabilities;
DROP TABLE IF EXISTS cop_ai_provider_logs;
DROP TABLE IF EXISTS cop_ai_provider_tests;
DROP TABLE IF EXISTS cop_ai_provider_models;
DROP TABLE IF EXISTS cop_ai_providers;

CREATE TABLE cop_ai_providers (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    nome            VARCHAR(100) NOT NULL,
    provider_type   VARCHAR(50)  NOT NULL COMMENT 'openai|anthropic|google|azure|ollama|openrouter|lmstudio|deepseek|mistral|qwen|custom',
    api_key_enc     TEXT         COMMENT 'API Key criptografada AES-256',
    api_key_mask    VARCHAR(30)  COMMENT 'Versao mascarada ex: sk-***...abc',
    endpoint        VARCHAR(255) DEFAULT NULL,
    deployment      VARCHAR(100) DEFAULT NULL COMMENT 'Azure: deployment name',
    api_version     VARCHAR(20)  DEFAULT NULL COMMENT 'Azure: api-version',
    regiao          VARCHAR(50)  DEFAULT NULL,
    organizacao     VARCHAR(100) DEFAULT NULL,
    conta           VARCHAR(100) DEFAULT NULL,
    modo            ENUM('producao','teste','sandbox') DEFAULT 'producao',
    is_default      TINYINT(1)   DEFAULT 0,
    is_active       TINYINT(1)   DEFAULT 1,
    latencia_ms     INT          DEFAULT NULL,
    status_conexao  ENUM('conectado','erro','pendente') DEFAULT 'pendente',
    ultimo_teste    DATETIME     DEFAULT NULL,
    -- Configurações avançadas
    temperatura     DECIMAL(3,2) DEFAULT 0.10,
    max_tokens      INT          DEFAULT 4096,
    timeout_s       INT          DEFAULT 30,
    retry           INT          DEFAULT 3,
    top_p           DECIMAL(3,2) DEFAULT 1.00,
    freq_penalty    DECIMAL(3,2) DEFAULT 0.00,
    pres_penalty    DECIMAL(3,2) DEFAULT 0.00,
    idioma          VARCHAR(10)  DEFAULT 'pt',
    prompt_base_id  INT UNSIGNED DEFAULT NULL,
    -- Wizard state
    wizard_step     TINYINT      DEFAULT 1 COMMENT 'Etapa atual do wizard (1-5)',
    wizard_completo TINYINT(1)   DEFAULT 0,
    -- Metadados
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_type (provider_type),
    INDEX idx_default (is_default),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. cop_ai_provider_models
-- ------------------------------------------------------------
CREATE TABLE cop_ai_provider_models (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider_id         INT UNSIGNED NOT NULL,
    user_id             INT UNSIGNED NOT NULL,
    model_id            VARCHAR(100) NOT NULL COMMENT 'ID exato da API ex: gpt-4o',
    model_name          VARCHAR(150) NOT NULL COMMENT 'Nome amigavel ex: GPT-4o',
    model_family        VARCHAR(50)  DEFAULT NULL COMMENT 'gpt|claude|gemini|llama',
    context_window      INT          DEFAULT NULL COMMENT 'Tokens de contexto',
    max_output_tokens   INT          DEFAULT NULL,
    -- Capacidades
    cap_chat            TINYINT(1)   DEFAULT 1,
    cap_vision          TINYINT(1)   DEFAULT 0,
    cap_streaming       TINYINT(1)   DEFAULT 1,
    cap_json_mode       TINYINT(1)   DEFAULT 0,
    cap_function_call   TINYINT(1)   DEFAULT 0,
    cap_structured_out  TINYINT(1)   DEFAULT 0,
    cap_reasoning       TINYINT(1)   DEFAULT 0,
    cap_long_context    TINYINT(1)   DEFAULT 0,
    -- Preços (USD por 1M tokens)
    preco_input         DECIMAL(10,4) DEFAULT NULL,
    preco_output        DECIMAL(10,4) DEFAULT NULL,
    -- Seleção
    is_recommended      TINYINT(1)   DEFAULT 0,
    is_selected         TINYINT(1)   DEFAULT 0,
    is_active           TINYINT(1)   DEFAULT 1,
    -- Metadados
    raw_data            TEXT         COMMENT 'JSON bruto retornado pela API',
    created_at          DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_provider (provider_id),
    INDEX idx_user (user_id),
    INDEX idx_model_id (model_id),
    INDEX idx_selected (is_selected)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. cop_ai_provider_tests (benchmark por validação)
-- ------------------------------------------------------------
CREATE TABLE cop_ai_provider_tests (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider_id     INT UNSIGNED NOT NULL,
    user_id         INT UNSIGNED NOT NULL,
    model_id        VARCHAR(100) DEFAULT NULL,
    -- Resultados
    auth_ok         TINYINT(1)   DEFAULT 0,
    gen_ok          TINYINT(1)   DEFAULT 0,
    latencia_ms     INT          DEFAULT NULL,
    tempo_total_ms  INT          DEFAULT NULL,
    tempo_ia_ms     INT          DEFAULT NULL,
    -- Capacidades verificadas
    cap_chat        TINYINT(1)   DEFAULT 0,
    cap_vision      TINYINT(1)   DEFAULT 0,
    cap_streaming   TINYINT(1)   DEFAULT 0,
    cap_json        TINYINT(1)   DEFAULT 0,
    cap_functions   TINYINT(1)   DEFAULT 0,
    cap_structured  TINYINT(1)   DEFAULT 0,
    cap_long_ctx    TINYINT(1)   DEFAULT 0,
    context_max     INT          DEFAULT NULL,
    saldo_usd       DECIMAL(10,4) DEFAULT NULL,
    saldo_disponivel TINYINT(1)  DEFAULT 0,
    -- Status geral
    status          ENUM('ok','erro','parcial') DEFAULT 'ok',
    erro_msg        TEXT         DEFAULT NULL,
    -- Metadados
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_provider (provider_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. cop_ai_provider_logs (auditoria de todas as operações)
-- ------------------------------------------------------------
CREATE TABLE cop_ai_provider_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider_id INT UNSIGNED DEFAULT NULL,
    user_id     INT UNSIGNED NOT NULL,
    acao        VARCHAR(50)  NOT NULL COMMENT 'test|discover|validate|save|delete',
    status      ENUM('ok','erro') DEFAULT 'ok',
    detalhe     TEXT         DEFAULT NULL,
    ip          VARCHAR(45)  DEFAULT NULL,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_provider (provider_id),
    INDEX idx_user (user_id),
    INDEX idx_acao (acao),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. cop_ai_provider_capabilities (capacidades por provider_type)
-- ------------------------------------------------------------
CREATE TABLE cop_ai_provider_capabilities (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider_type   VARCHAR(50)  NOT NULL UNIQUE,
    nome_exibicao   VARCHAR(100) NOT NULL,
    descricao       TEXT         DEFAULT NULL,
    logo_url        VARCHAR(255) DEFAULT NULL,
    doc_url         VARCHAR(255) DEFAULT NULL,
    homologado      TINYINT(1)   DEFAULT 0,
    rating          TINYINT      DEFAULT 5 COMMENT '1-5 estrelas',
    -- Campos necessários para credenciais
    campos_json     TEXT         NOT NULL COMMENT 'JSON com campos do formulario de credenciais',
    -- Capacidades suportadas
    sup_chat        TINYINT(1)   DEFAULT 1,
    sup_vision      TINYINT(1)   DEFAULT 0,
    sup_streaming   TINYINT(1)   DEFAULT 1,
    sup_json        TINYINT(1)   DEFAULT 0,
    sup_functions   TINYINT(1)   DEFAULT 0,
    sup_structured  TINYINT(1)   DEFAULT 0,
    sup_reasoning   TINYINT(1)   DEFAULT 0,
    sup_long_ctx    TINYINT(1)   DEFAULT 0,
    sup_video       TINYINT(1)   DEFAULT 0,
    -- Tags de destaque
    tags            VARCHAR(255) DEFAULT NULL COMMENT 'CSV: Chat,Vision,Reasoning',
    -- Endpoint padrão
    endpoint_padrao VARCHAR(255) DEFAULT NULL,
    -- Metadados
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- SEEDS — Capabilities dos 12 providers
-- ------------------------------------------------------------
INSERT INTO cop_ai_provider_capabilities
(provider_type, nome_exibicao, descricao, doc_url, homologado, rating, campos_json, sup_chat, sup_vision, sup_streaming, sup_json, sup_functions, sup_structured, sup_reasoning, sup_long_ctx, sup_video, tags, endpoint_padrao)
VALUES

('openai', 'OpenAI', 'Modelos GPT da OpenAI — lider em performance para laudos medicos',
 'https://platform.openai.com/docs', 1, 5,
 '[{"key":"nome","label":"Nome do Provider","type":"text","required":true,"placeholder":"Meu OpenAI"},{"key":"api_key","label":"API Key","type":"password","required":true,"placeholder":"sk-..."},{"key":"endpoint","label":"Endpoint (opcional)","type":"text","required":false,"placeholder":"https://api.openai.com/v1"}]',
 1,1,1,1,1,1,1,1,0, 'Chat,Vision,Reasoning,Function Calling,JSON,Structured Output',
 'https://api.openai.com/v1'),

('anthropic', 'Anthropic', 'Claude — excelente para textos longos e raciocinio clinico',
 'https://docs.anthropic.com', 1, 5,
 '[{"key":"nome","label":"Nome do Provider","type":"text","required":true,"placeholder":"Meu Claude"},{"key":"api_key","label":"API Key","type":"password","required":true,"placeholder":"sk-ant-..."},{"key":"endpoint","label":"Endpoint (opcional)","type":"text","required":false,"placeholder":"https://api.anthropic.com"}]',
 1,0,1,1,1,1,1,1,0, 'Claude,Long Context,Reasoning',
 'https://api.anthropic.com'),

('google', 'Google Gemini', 'Gemini — multimodal com suporte a video e contexto longo',
 'https://ai.google.dev/docs', 1, 4,
 '[{"key":"nome","label":"Nome do Provider","type":"text","required":true,"placeholder":"Meu Gemini"},{"key":"api_key","label":"API Key","type":"password","required":true,"placeholder":"AIza..."},{"key":"endpoint","label":"Endpoint (opcional)","type":"text","required":false,"placeholder":"https://generativelanguage.googleapis.com"}]',
 1,1,1,1,1,1,0,1,1, 'Gemini,Vision,Video,Long Context',
 'https://generativelanguage.googleapis.com/v1beta'),

('azure', 'Azure OpenAI', 'OpenAI hospedado no Azure — ideal para compliance hospitalar',
 'https://learn.microsoft.com/azure/ai-services/openai', 1, 5,
 '[{"key":"nome","label":"Nome do Provider","type":"text","required":true,"placeholder":"Meu Azure OpenAI"},{"key":"endpoint","label":"Endpoint","type":"text","required":true,"placeholder":"https://meu-recurso.openai.azure.com"},{"key":"deployment","label":"Deployment Name","type":"text","required":true,"placeholder":"gpt-4o-deployment"},{"key":"api_version","label":"API Version","type":"text","required":true,"placeholder":"2024-02-01"},{"key":"api_key","label":"API Key","type":"password","required":true,"placeholder":"xxxxxxxxxxxxxxxx"}]',
 1,1,1,1,1,1,1,1,0, 'Azure,Compliance,HIPAA,Chat,Vision',
 NULL),

('ollama', 'Ollama', 'Modelos locais — privacidade total, sem envio de dados',
 'https://ollama.com/docs', 1, 4,
 '[{"key":"nome","label":"Nome do Provider","type":"text","required":true,"placeholder":"Meu Ollama"},{"key":"endpoint","label":"Servidor","type":"text","required":true,"placeholder":"http://localhost"},{"key":"porta","label":"Porta","type":"number","required":false,"placeholder":"11434"}]',
 1,0,1,0,0,0,0,0,0, 'Local,Privacidade,Offline',
 'http://localhost:11434'),

('openrouter', 'OpenRouter', 'Acesso unificado a dezenas de modelos via uma unica API Key',
 'https://openrouter.ai/docs', 1, 4,
 '[{"key":"nome","label":"Nome do Provider","type":"text","required":true,"placeholder":"Meu OpenRouter"},{"key":"api_key","label":"API Key","type":"password","required":true,"placeholder":"sk-or-..."},{"key":"endpoint","label":"Endpoint (opcional)","type":"text","required":false,"placeholder":"https://openrouter.ai/api/v1"}]',
 1,1,1,1,1,0,0,1,0, 'Multi-modelo,Unificado,Economico',
 'https://openrouter.ai/api/v1'),

('lmstudio', 'LM Studio', 'Interface local para modelos GGUF — facil de usar',
 'https://lmstudio.ai/docs', 0, 3,
 '[{"key":"nome","label":"Nome do Provider","type":"text","required":true,"placeholder":"Meu LM Studio"},{"key":"endpoint","label":"Servidor","type":"text","required":true,"placeholder":"http://localhost:1234"}]',
 1,0,1,0,0,0,0,0,0, 'Local,GGUF,Interface',
 'http://localhost:1234/v1'),

('deepseek', 'DeepSeek', 'Modelos chines de alta performance com custo reduzido',
 'https://platform.deepseek.com/docs', 0, 4,
 '[{"key":"nome","label":"Nome do Provider","type":"text","required":true,"placeholder":"Meu DeepSeek"},{"key":"api_key","label":"API Key","type":"password","required":true,"placeholder":"sk-..."},{"key":"endpoint","label":"Endpoint (opcional)","type":"text","required":false,"placeholder":"https://api.deepseek.com/v1"}]',
 1,0,1,1,1,0,1,1,0, 'Economico,Reasoning,Long Context',
 'https://api.deepseek.com/v1'),

('mistral', 'Mistral AI', 'Modelos europeus eficientes com foco em privacidade',
 'https://docs.mistral.ai', 0, 4,
 '[{"key":"nome","label":"Nome do Provider","type":"text","required":true,"placeholder":"Meu Mistral"},{"key":"api_key","label":"API Key","type":"password","required":true,"placeholder":"..."},{"key":"endpoint","label":"Endpoint (opcional)","type":"text","required":false,"placeholder":"https://api.mistral.ai/v1"}]',
 1,0,1,1,1,0,0,0,0, 'Europeu,GDPR,Eficiente',
 'https://api.mistral.ai/v1'),

('qwen', 'Qwen (Alibaba)', 'Modelos multilingues da Alibaba com suporte a portugues',
 'https://help.aliyun.com/zh/dashscope', 0, 3,
 '[{"key":"nome","label":"Nome do Provider","type":"text","required":true,"placeholder":"Meu Qwen"},{"key":"api_key","label":"API Key","type":"password","required":true,"placeholder":"sk-..."},{"key":"endpoint","label":"Endpoint (opcional)","type":"text","required":false,"placeholder":"https://dashscope.aliyuncs.com/compatible-mode/v1"}]',
 1,1,1,1,0,0,0,1,0, 'Multilingue,Portugues,Vision',
 'https://dashscope.aliyuncs.com/compatible-mode/v1'),

('custom', 'Provider Customizado', 'Qualquer API compativel com OpenAI (endpoint proprio)',
 NULL, 0, 3,
 '[{"key":"nome","label":"Nome do Provider","type":"text","required":true,"placeholder":"Meu Provider"},{"key":"api_key","label":"API Key","type":"password","required":false,"placeholder":"(opcional)"},{"key":"endpoint","label":"Endpoint","type":"text","required":true,"placeholder":"https://minha-api.com/v1"}]',
 1,0,1,0,0,0,0,0,0, 'Custom,OpenAI-compatible',
 NULL);

SET foreign_key_checks = 1;
