-- ============================================================
-- VOXEL Copilot — Schema Principal
-- MariaDB 5.7 / MySQL 5.7 compatível
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- PLANOS
-- ============================================================
CREATE TABLE IF NOT EXISTS `cop_plans` (
    `id`                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nome`                  VARCHAR(100)   NOT NULL,
    `slug`                  VARCHAR(50)    NOT NULL,
    `max_medicos`           INT            NOT NULL DEFAULT 5,
    `max_laudos_mes`        INT            NOT NULL DEFAULT 1000,
    `permite_ia`            TINYINT(1)     NOT NULL DEFAULT 1,
    `permite_speech`        TINYINT(1)     NOT NULL DEFAULT 0,
    `permite_vision_ai`     TINYINT(1)     NOT NULL DEFAULT 0,
    `permite_marketplace`   TINYINT(1)     NOT NULL DEFAULT 0,
    `preco_mensal`          DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `ativo`                 TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`            TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TENANTS (Clínicas / Hospitais)
-- ============================================================
CREATE TABLE IF NOT EXISTS `cop_tenants` (
    `id`                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nome`                  VARCHAR(255)   NOT NULL,
    `slug`                  VARCHAR(100)   NOT NULL,
    `cnpj`                  VARCHAR(18)    NULL,
    `email_contato`         VARCHAR(255)   NULL,
    `telefone`              VARCHAR(20)    NULL,
    `logo_url`              VARCHAR(500)   NULL,
    `cor_primaria`          VARCHAR(7)     NULL DEFAULT '#0ea5e9',
    `plan_id`               INT UNSIGNED   NOT NULL,
    `status`                VARCHAR(20)    NOT NULL DEFAULT 'trial',
    `trial_expira_em`       DATE           NULL,
    `pacs_api_url`          VARCHAR(500)   NULL COMMENT 'URL da API do VOXEL PACS deste tenant',
    `pacs_api_token`        VARCHAR(500)   NULL COMMENT 'Token de acesso ao VOXEL PACS',
    `configuracoes_json`    TEXT           NULL,
    `created_at`            TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_slug` (`slug`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_cop_tenant_plan` FOREIGN KEY (`plan_id`) REFERENCES `cop_plans`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- USUÁRIOS (Superadmin + Médicos)
-- ============================================================
CREATE TABLE IF NOT EXISTS `cop_users` (
    `id`                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`                  VARCHAR(255)   NOT NULL,
    `email`                 VARCHAR(255)   NOT NULL,
    `password`              VARCHAR(255)   NOT NULL,
    `role`                  VARCHAR(20)    NOT NULL DEFAULT 'medico',
    `status`                VARCHAR(20)    NOT NULL DEFAULT 'pendente',
    `crm`                   VARCHAR(30)    NULL,
    `crm_uf`                CHAR(2)        NULL,
    `especialidades`        TEXT           NULL COMMENT 'JSON array de especialidades',
    `telefone`              VARCHAR(20)    NULL,
    `cep`                   VARCHAR(9)     NULL,
    `logradouro`            VARCHAR(255)   NULL,
    `numero`                VARCHAR(20)    NULL,
    `complemento`           VARCHAR(100)   NULL,
    `bairro`                VARCHAR(100)   NULL,
    `cidade`                VARCHAR(100)   NULL,
    `estado`                CHAR(2)        NULL,
    `avatar_url`            VARCHAR(500)   NULL,
    `ultimo_login`          DATETIME       NULL,
    `email_verificado`      TINYINT(1)     NOT NULL DEFAULT 0,
    `token_senha`           VARCHAR(100)   NULL COMMENT 'Token temporário para primeiro acesso',
    `token_expira_em`       DATETIME       NULL,
    `created_at`            TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_email` (`email`),
    INDEX `idx_crm` (`crm`),
    INDEX `idx_role` (`role`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- VÍNCULO USUÁRIO ↔ TENANT
-- ============================================================
CREATE TABLE IF NOT EXISTS `cop_user_tenants` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT UNSIGNED NOT NULL,
    `tenant_id`  INT UNSIGNED NOT NULL,
    `role`       VARCHAR(20)  NOT NULL DEFAULT 'medico',
    `ativo`      TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_user_tenant` (`user_id`, `tenant_id`),
    INDEX `idx_user_id`   (`user_id`),
    INDEX `idx_tenant_id` (`tenant_id`),
    CONSTRAINT `fk_cop_ut_user`   FOREIGN KEY (`user_id`)   REFERENCES `cop_users`(`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_cop_ut_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `cop_tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PERFIL DO MÉDICO (Medical Profile — memória de estilo)
-- ============================================================
CREATE TABLE IF NOT EXISTS `cop_medico_perfil` (
    `id`                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`               INT UNSIGNED NOT NULL,
    `tenant_id`             INT UNSIGNED NOT NULL,
    `vocabulario_json`      TEXT         NULL COMMENT 'Substituições aprendidas: {"fígado":"parênquima hepático"}',
    `frases_favoritas_json` TEXT         NULL COMMENT 'Frases frequentemente usadas',
    `estilo_conclusao`      VARCHAR(20)  NULL DEFAULT 'normal' COMMENT 'curta|normal|detalhada',
    `preferencias_json`     TEXT         NULL COMMENT 'Outras preferências do médico',
    `total_laudos`          INT UNSIGNED NOT NULL DEFAULT 0,
    `total_correcoes`       INT UNSIGNED NOT NULL DEFAULT 0,
    `updated_at`            TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_user_tenant` (`user_id`, `tenant_id`),
    INDEX `idx_user_id`   (`user_id`),
    INDEX `idx_tenant_id` (`tenant_id`),
    CONSTRAINT `fk_cop_perfil_user`   FOREIGN KEY (`user_id`)   REFERENCES `cop_users`(`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_cop_perfil_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `cop_tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TEMPLATES DE LAUDO (Máscaras)
-- ============================================================
CREATE TABLE IF NOT EXISTS `cop_templates` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id`     INT UNSIGNED NOT NULL,
    `user_id`       INT UNSIGNED NULL COMMENT 'NULL = template da clínica',
    `nome`          VARCHAR(255) NOT NULL,
    `modalidade`    VARCHAR(20)  NULL COMMENT 'TC, RM, RX, US, MG, PET...',
    `especialidade` VARCHAR(100) NULL,
    `corpo`         LONGTEXT     NOT NULL COMMENT 'Conteúdo HTML/texto do template',
    `variaveis_json`TEXT         NULL COMMENT 'Variáveis dinâmicas do template',
    `ativo`         TINYINT(1)   NOT NULL DEFAULT 1,
    `uso_count`     INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tenant`     (`tenant_id`),
    INDEX `idx_user`       (`user_id`),
    INDEX `idx_modalidade` (`modalidade`),
    CONSTRAINT `fk_cop_tpl_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `cop_tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- WORKSPACES (Sessão de laudo)
-- ============================================================
CREATE TABLE IF NOT EXISTS `cop_workspaces` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id`     INT UNSIGNED NOT NULL,
    `medico_id`     INT UNSIGNED NOT NULL,
    `study_uid`     VARCHAR(255) NOT NULL COMMENT 'StudyInstanceUID do PACS',
    `patient_uid`   VARCHAR(255) NULL,
    `patient_nome`  VARCHAR(255) NULL,
    `modalidade`    VARCHAR(20)  NULL,
    `status`        VARCHAR(20)  NOT NULL DEFAULT 'aberto' COMMENT 'aberto|rascunho|assinado|cancelado',
    `assumido_em`   DATETIME     NULL,
    `assinado_em`   DATETIME     NULL,
    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tenant`    (`tenant_id`),
    INDEX `idx_medico`    (`medico_id`),
    INDEX `idx_study_uid` (`study_uid`),
    INDEX `idx_status`    (`status`),
    CONSTRAINT `fk_cop_ws_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `cop_tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cop_ws_medico` FOREIGN KEY (`medico_id`) REFERENCES `cop_users`(`id`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- LAUDOS (Versões)
-- ============================================================
CREATE TABLE IF NOT EXISTS `cop_laudos` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `workspace_id`      INT UNSIGNED NOT NULL,
    `tenant_id`         INT UNSIGNED NOT NULL,
    `medico_id`         INT UNSIGNED NOT NULL,
    `versao`            INT UNSIGNED NOT NULL DEFAULT 1,
    `indicacao`         TEXT         NULL,
    `tecnica`           TEXT         NULL,
    `achados`           LONGTEXT     NULL,
    `impressao`         TEXT         NULL,
    `recomendacao`      TEXT         NULL,
    `cid`               VARCHAR(20)  NULL,
    `status`            VARCHAR(20)  NOT NULL DEFAULT 'rascunho' COMMENT 'rascunho|assinado',
    `ia_sugestao`       LONGTEXT     NULL COMMENT 'Sugestão original da IA',
    `ia_modelo`         VARCHAR(50)  NULL COMMENT 'Modelo de IA utilizado',
    `ia_tokens`         INT          NULL COMMENT 'Tokens consumidos',
    `assinado_em`       DATETIME     NULL,
    `created_at`        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_workspace`  (`workspace_id`),
    INDEX `idx_tenant`     (`tenant_id`),
    INDEX `idx_medico`     (`medico_id`),
    INDEX `idx_status`     (`status`),
    CONSTRAINT `fk_cop_laudo_ws`     FOREIGN KEY (`workspace_id`) REFERENCES `cop_workspaces`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cop_laudo_tenant` FOREIGN KEY (`tenant_id`)    REFERENCES `cop_tenants`(`id`)    ON DELETE CASCADE,
    CONSTRAINT `fk_cop_laudo_medico` FOREIGN KEY (`medico_id`)    REFERENCES `cop_users`(`id`)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- CONVERSAS COM A IA (Histórico de chat)
-- ============================================================
CREATE TABLE IF NOT EXISTS `cop_ia_conversas` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `workspace_id`  INT UNSIGNED NOT NULL,
    `tenant_id`     INT UNSIGNED NOT NULL,
    `medico_id`     INT UNSIGNED NOT NULL,
    `role`          VARCHAR(20)  NOT NULL COMMENT 'user|assistant|system',
    `conteudo`      LONGTEXT     NOT NULL,
    `modelo`        VARCHAR(50)  NULL,
    `tokens`        INT          NULL,
    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_workspace` (`workspace_id`),
    INDEX `idx_tenant`    (`tenant_id`),
    CONSTRAINT `fk_cop_conv_ws` FOREIGN KEY (`workspace_id`) REFERENCES `cop_workspaces`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- AUTOTEXTOS (Frases rápidas)
-- ============================================================
CREATE TABLE IF NOT EXISTS `cop_autotextos` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id`     INT UNSIGNED NOT NULL,
    `user_id`       INT UNSIGNED NULL COMMENT 'NULL = global da clínica',
    `atalho`        VARCHAR(50)  NOT NULL COMMENT 'Ex: /normal',
    `texto`         TEXT         NOT NULL,
    `modalidade`    VARCHAR(20)  NULL,
    `ativo`         TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tenant`  (`tenant_id`),
    INDEX `idx_atalho`  (`atalho`),
    CONSTRAINT `fk_cop_at_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `cop_tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- AUDIT LOGS
-- ============================================================
CREATE TABLE IF NOT EXISTS `cop_audit_logs` (
    `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id`  INT UNSIGNED NULL,
    `user_id`    INT UNSIGNED NULL,
    `action`     VARCHAR(100) NOT NULL,
    `entity`     VARCHAR(100) NULL,
    `entity_id`  INT UNSIGNED NULL,
    `details`    TEXT         NULL,
    `ip`         VARCHAR(45)  NULL,
    `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tenant` (`tenant_id`),
    INDEX `idx_user`   (`user_id`),
    INDEX `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DADOS INICIAIS
-- ============================================================

-- Planos
INSERT IGNORE INTO `cop_plans` (`nome`, `slug`, `max_medicos`, `max_laudos_mes`, `permite_ia`, `permite_speech`, `permite_vision_ai`, `permite_marketplace`, `preco_mensal`) VALUES
('Starter',      'starter',      3,   500,   1, 0, 0, 0,  199.00),
('Professional', 'professional', 15,  5000,  1, 1, 0, 0,  599.00),
('Enterprise',   'enterprise',   999, 99999, 1, 1, 1, 1, 1499.00);

-- Superadmin: admin@voxelpacs.com.br / Admin259087@
INSERT INTO `cop_users` (`name`, `email`, `password`, `role`, `status`, `email_verificado`, `created_at`, `updated_at`)
VALUES (
    'Administrador VOXEL',
    'admin@voxelpacs.com.br',
    '$2y$12$wN04XW/k5DmCARrOVFBf4.KiW8JL61RP40TpUolQGprm5IdX0UqO6',
    'superadmin',
    'ativo',
    1,
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    name              = VALUES(name),
    password          = VALUES(password),
    role              = VALUES(role),
    status            = VALUES(status),
    email_verificado  = VALUES(email_verificado),
    updated_at        = NOW();
