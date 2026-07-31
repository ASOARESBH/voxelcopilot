-- =============================================================================
-- Migration: 2026-07-31_pacs_integracao_sync.sql
-- Objetivo : Tabelas de suporte à integração sistêmica VoxelPACS ↔ VOXEL Copilot
--            no lado do Copilot.
--
-- Cria:
--   cop_pacs_sync_log   — Log de eventos recebidos do PACS (assumir, abrir, liberar)
--   cop_pacs_worklist   — Fila de exames recebidos do PACS para laudar no Copilot
--
-- Compatível com MySQL 5.7 / MariaDB 5.7 / Hostgator compartilhado.
-- Execute manualmente no phpMyAdmin.
-- =============================================================================

SET NAMES utf8mb4;

-- ─────────────────────────────────────────────────────────────────────────────
-- 1. cop_pacs_sync_log
--    Registra todos os eventos recebidos do PACS via webhook.
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `cop_pacs_sync_log` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `autorizacao_id`    INT UNSIGNED    NULL     COMMENT 'FK cop_pacs_autorizacoes.id',
    `user_id`           INT UNSIGNED    NULL     COMMENT 'FK cop_users.id (médico)',
    `evento`            VARCHAR(60)     NOT NULL COMMENT 'ex: estudo.assumido, estudo.liberado',
    `direcao`           ENUM('pacs_para_copilot','copilot_para_pacs') NOT NULL DEFAULT 'pacs_para_copilot',
    `status`            ENUM('sucesso','erro','pendente') NOT NULL DEFAULT 'sucesso',
    `study_instance_uid` VARCHAR(255)   NULL,
    `payload_json`      TEXT            NULL,
    `resposta_json`     TEXT            NULL,
    `erro_msg`          VARCHAR(500)    NULL,
    `ip`                VARCHAR(45)     NULL,
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_autorizacao` (`autorizacao_id`),
    KEY `idx_user`        (`user_id`),
    KEY `idx_evento`      (`evento`),
    KEY `idx_study_uid`   (`study_instance_uid`(64)),
    KEY `idx_created`     (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Log de sincronização bidirecional VoxelPACS ↔ VOXEL Copilot';

-- ─────────────────────────────────────────────────────────────────────────────
-- 2. cop_pacs_worklist
--    Fila de exames recebidos do PACS que aguardam laudo no Copilot.
--    Cada linha representa um estudo DICOM que o médico assumiu no PACS.
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `cop_pacs_worklist` (
    `id`                    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `autorizacao_id`        INT UNSIGNED    NOT NULL COMMENT 'FK cop_pacs_autorizacoes.id',
    `user_id`               INT UNSIGNED    NOT NULL COMMENT 'FK cop_users.id (médico)',
    -- Identificação do estudo DICOM
    `study_instance_uid`    VARCHAR(255)    NOT NULL,
    `accession_number`      VARCHAR(100)    NULL,
    `pacs_estudo_id`        BIGINT UNSIGNED NULL     COMMENT 'bi_pacs_estudos.id no VoxelPACS',
    -- Dados do paciente (recebidos via webhook)
    `patient_nome`          VARCHAR(255)    NULL,
    `patient_id`            VARCHAR(64)     NULL,
    `patient_birth_date`    DATE            NULL,
    `patient_sex`           CHAR(1)         NULL,
    `modalidade`            VARCHAR(20)     NULL,
    `study_date`            DATE            NULL,
    `study_description`     VARCHAR(500)    NULL,
    `institution_name`      VARCHAR(255)    NULL,
    `num_series`            INT             NULL DEFAULT 0,
    `num_instances`         INT             NULL DEFAULT 0,
    `prioridade`            VARCHAR(20)     NULL DEFAULT 'normal',
    -- Dados do médico (snapshot do momento da assunção)
    `medico_nome`           VARCHAR(200)    NULL,
    `medico_crm`            VARCHAR(30)     NULL,
    `medico_especialidade`  VARCHAR(200)    NULL,
    -- Vínculo com o workspace de laudo (criado automaticamente)
    `workspace_id`          INT UNSIGNED    NULL     COMMENT 'FK cop_workspaces.id',
    `laudo_id`              INT UNSIGNED    NULL     COMMENT 'FK cop_laudos.id',
    -- Status do fluxo
    `status`                ENUM('aguardando','em_laudo','rascunho','assinado','enviado','erro') NOT NULL DEFAULT 'aguardando',
    `assumido_em`           DATETIME        NULL     COMMENT 'Quando o médico assumiu no PACS',
    `laudo_assinado_em`     DATETIME        NULL,
    `enviado_pacs_em`       DATETIME        NULL,
    `erro_msg`              VARCHAR(500)    NULL,
    -- Auditoria
    `created_at`            TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_study_user` (`study_instance_uid`(191), `user_id`),
    KEY `idx_autorizacao`  (`autorizacao_id`),
    KEY `idx_user`         (`user_id`),
    KEY `idx_status`       (`status`),
    KEY `idx_workspace`    (`workspace_id`),
    KEY `idx_study_uid`    (`study_instance_uid`(64))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Fila de exames recebidos do PACS para laudar no VOXEL Copilot';
