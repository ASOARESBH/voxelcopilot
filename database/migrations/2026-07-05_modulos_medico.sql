-- ============================================================
-- VOXEL Copilot — Módulos do Médico
-- 100% compatível com MariaDB 5.7 / MySQL 5.7
-- HostGator / cPanel / phpMyAdmin
--
-- INSTRUÇÕES DE EXECUÇÃO:
-- Execute APÓS o schema principal (2026-07-05_copilot_schema.sql).
-- Execute UMA ÚNICA VEZ em banco limpo.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- FILA DE EXAMES
-- ============================================================
CREATE TABLE `cop_fila` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `medico_id`     INT UNSIGNED NOT NULL,
    `tenant_id`     INT UNSIGNED NULL,
    `accession`     VARCHAR(64)  NULL,
    `study_uid`     VARCHAR(128) NULL,
    `paciente_nome` VARCHAR(200) NULL,
    `paciente_cpf`  VARCHAR(20)  NULL,
    `modalidade`    VARCHAR(20)  NULL,
    `descricao`     VARCHAR(255) NULL,
    `prioridade`    ENUM('urgente','alta','normal','baixa') NOT NULL DEFAULT 'normal',
    `categoria`     VARCHAR(50)  NULL,
    `status`        ENUM('aguardando','em_laudo','laudado','assinado','cancelado') NOT NULL DEFAULT 'aguardando',
    `data_exame`    DATETIME     NULL,
    `data_entrada`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_inicio`   DATETIME     NULL,
    `data_fim`      DATETIME     NULL,
    `sla_minutos`   INT UNSIGNED NULL,
    `ia_tags`       TEXT         NULL,
    `pacs_url`      VARCHAR(500) NULL,
    INDEX `idx_fila_medico`     (`medico_id`),
    INDEX `idx_fila_status`     (`status`),
    INDEX `idx_fila_prioridade` (`prioridade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PACIENTES
-- ============================================================
CREATE TABLE `cop_pacientes` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id`       INT UNSIGNED NULL,
    `nome`            VARCHAR(200) NOT NULL,
    `cpf`             VARCHAR(20)  NULL,
    `nascimento`      DATE         NULL,
    `sexo`            CHAR(1)      NULL,
    `telefone`        VARCHAR(30)  NULL,
    `email`           VARCHAR(150) NULL,
    `cep`             VARCHAR(10)  NULL,
    `logradouro`      VARCHAR(200) NULL,
    `bairro`          VARCHAR(100) NULL,
    `cidade`          VARCHAR(100) NULL,
    `estado`          CHAR(2)      NULL,
    `pacs_patient_id` VARCHAR(64)  NULL,
    `criado_em`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pac_cpf`    (`cpf`),
    INDEX `idx_pac_nome`   (`nome`),
    INDEX `idx_pac_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- COMPARATIVOS
-- ============================================================
CREATE TABLE `cop_comparativos` (
    `id`                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `medico_id`          INT UNSIGNED NOT NULL,
    `paciente_id`        INT UNSIGNED NULL,
    `accession_atual`    VARCHAR(64)  NULL,
    `accession_anterior` VARCHAR(64)  NULL,
    `study_uid_atual`    VARCHAR(128) NULL,
    `study_uid_anterior` VARCHAR(128) NULL,
    `modalidade`         VARCHAR(20)  NULL,
    `descricao_atual`    VARCHAR(255) NULL,
    `descricao_anterior` VARCHAR(255) NULL,
    `data_atual`         DATETIME     NULL,
    `data_anterior`      DATETIME     NULL,
    `ia_delta`           TEXT         NULL,
    `status`             ENUM('pendente','em_analise','concluido') NOT NULL DEFAULT 'pendente',
    `criado_em`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_comp_medico`   (`medico_id`),
    INDEX `idx_comp_paciente` (`paciente_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- VISION AI — Análises de Imagem
-- ============================================================
CREATE TABLE `cop_vision_analises` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `medico_id`     INT UNSIGNED NOT NULL,
    `paciente_id`   INT UNSIGNED NULL,
    `accession`     VARCHAR(64)  NULL,
    `study_uid`     VARCHAR(128) NULL,
    `modalidade`    VARCHAR(20)  NULL,
    `descricao`     VARCHAR(255) NULL,
    `status`        ENUM('processando','concluido','erro') NOT NULL DEFAULT 'processando',
    `achados_ia`    TEXT         NULL,
    `confianca`     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `modelo_usado`  VARCHAR(50)  NULL,
    `tokens_usados` INT UNSIGNED NOT NULL DEFAULT 0,
    `criado_em`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `concluido_em`  DATETIME     NULL,
    INDEX `idx_vision_medico` (`medico_id`),
    INDEX `idx_vision_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SPEECH — Histórico de Ditados
-- ============================================================
CREATE TABLE `cop_speech_historico` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `medico_id`    INT UNSIGNED NOT NULL,
    `transcricao`  TEXT         NULL,
    `duracao_seg`  INT UNSIGNED NOT NULL DEFAULT 0,
    `palavras`     INT UNSIGNED NOT NULL DEFAULT 0,
    `modelo_usado` VARCHAR(50)  NULL,
    `status`       ENUM('processando','concluido','erro') NOT NULL DEFAULT 'processando',
    `criado_em`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_speech_medico` (`medico_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PESQUISA CLÍNICA — Histórico de Buscas
-- ============================================================
CREATE TABLE `cop_pesquisa_historico` (
    `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `medico_id` INT UNSIGNED NOT NULL,
    `query`     VARCHAR(500) NULL,
    `tipo`      ENUM('pubmed','uptodate','radiopaedia','copilot') NOT NULL DEFAULT 'copilot',
    `resultado` TEXT         NULL,
    `criado_em` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pesq_medico` (`medico_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- MARKETPLACE — Plugins disponíveis
-- ============================================================
CREATE TABLE `cop_plugins` (
    `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug`      VARCHAR(50)  NOT NULL,
    `nome`      VARCHAR(100) NOT NULL,
    `categoria` VARCHAR(50)  NULL,
    `descricao` TEXT         NULL,
    `versao`    VARCHAR(20)  NULL,
    `icone`     VARCHAR(50)  NULL,
    `cor`       VARCHAR(10)  NULL,
    `preco`     VARCHAR(30)  NULL,
    `ativo`     TINYINT(1)   NOT NULL DEFAULT 1,
    `criado_em` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- MARKETPLACE — Plugins instalados por médico
-- ============================================================
CREATE TABLE `cop_medico_plugins` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `medico_id`   INT UNSIGNED NOT NULL,
    `plugin_id`   INT UNSIGNED NOT NULL,
    `instalado_em`DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_medico_plugin` (`medico_id`, `plugin_id`),
    INDEX `idx_mp_medico` (`medico_id`),
    INDEX `idx_mp_plugin` (`plugin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- INTEGRAÇÕES
-- ============================================================
CREATE TABLE `cop_integracoes` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `medico_id`   INT UNSIGNED NOT NULL,
    `tenant_id`   INT UNSIGNED NULL,
    `nome`        VARCHAR(100) NOT NULL,
    `tipo`        VARCHAR(50)  NULL,
    `protocolo`   VARCHAR(30)  NULL,
    `url`         VARCHAR(500) NULL,
    `usuario`     VARCHAR(100) NULL,
    `senha_enc`   VARCHAR(500) NULL,
    `token_enc`   VARCHAR(1000)NULL,
    `status`      ENUM('conectado','desconectado','erro') NOT NULL DEFAULT 'desconectado',
    `ultima_sync` DATETIME     NULL,
    `criado_em`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_integ_medico` (`medico_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DADOS INICIAIS — Plugins do Marketplace
-- ============================================================
INSERT IGNORE INTO `cop_plugins` (`slug`, `nome`, `categoria`, `descricao`, `versao`, `icone`, `cor`, `preco`) VALUES
('lung-ai',   'Lung AI',       'Diagnostico',   'Deteccao automatica de nodulos pulmonares com classificacao Lung-RADS.',  '2.1', 'fa-lungs',          '#0ea5e9', 'Incluido'),
('cardio-ai', 'Cardio AI',     'Diagnostico',   'Analise de funcao ventricular e deteccao de anomalias cardiacas em RM.',  '1.4', 'fa-heart-pulse',     '#ef4444', 'Incluido'),
('speech',    'Speech Engine', 'Produtividade', 'Ditado de laudos por voz com transcricao em tempo real.',                 '3.0', 'fa-microphone',      '#8b5cf6', 'Incluido'),
('research',  'Research AI',   'Pesquisa',      'Busca automatica em PubMed, Radiopaedia e UpToDate.',                    '1.2', 'fa-book-medical',    '#10b981', 'Incluido'),
('workflow',  'Workflow AI',   'Automacao',     'Priorizacao inteligente de fila e alertas de SLA.',                      '1.0', 'fa-diagram-project', '#f59e0b', 'Incluido'),
('neuro-ai',  'Neuro AI',      'Diagnostico',   'Analise de RM de encefalo com segmentacao automatica.',                  '1.0', 'fa-brain',           '#6366f1', 'Em breve'),
('mammo-ai',  'Mammo AI',      'Diagnostico',   'Classificacao BI-RADS automatica em mamografias.',                      '1.0', 'fa-ribbon',          '#ec4899', 'Em breve');
