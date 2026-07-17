-- ============================================================
-- VOXEL Copilot — Módulo de Importação de Máscaras de Laudo
-- Compatível com MariaDB 5.7 / MySQL 5.7 / HostGator / phpMyAdmin
--
-- ATENÇÃO: Execute em partes no phpMyAdmin caso alguma coluna já exista.
-- Cada ALTER TABLE está separado para facilitar execução manual.
-- ============================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── 1. Adiciona colunas de seções estruturadas em cop_templates ──────────────
-- Execute cada linha separadamente se alguma coluna já existir

ALTER TABLE `cop_templates` ADD COLUMN `secao_tecnica`   LONGTEXT NULL AFTER `corpo`;
ALTER TABLE `cop_templates` ADD COLUMN `secao_analise`   LONGTEXT NULL AFTER `secao_tecnica`;
ALTER TABLE `cop_templates` ADD COLUMN `secao_impressao` LONGTEXT NULL AFTER `secao_analise`;
ALTER TABLE `cop_templates` ADD COLUMN `secao_adicional` LONGTEXT NULL AFTER `secao_impressao`;
ALTER TABLE `cop_templates` ADD COLUMN `origem`          VARCHAR(30) NOT NULL DEFAULT 'manual' AFTER `secao_adicional`;
ALTER TABLE `cop_templates` ADD COLUMN `origem_arquivo`  VARCHAR(255) NULL AFTER `origem`;
ALTER TABLE `cop_templates` ADD COLUMN `tags`            VARCHAR(500) NULL AFTER `origem_arquivo`;
ALTER TABLE `cop_templates` ADD COLUMN `publico`         TINYINT(1) NOT NULL DEFAULT 0 AFTER `tags`;

-- ── 2. Tabela de importações (histórico de uploads de DOCX) ─────────────────
CREATE TABLE IF NOT EXISTS `cop_mascaras_importacoes` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`         INT UNSIGNED NOT NULL,
    `tenant_id`       INT UNSIGNED NULL,
    `arquivo_nome`    VARCHAR(255) NOT NULL,
    `arquivo_hash`    VARCHAR(64)  NULL,
    `total_mascaras`  INT UNSIGNED NOT NULL DEFAULT 0,
    `importadas`      INT UNSIGNED NOT NULL DEFAULT 0,
    `ignoradas`       INT UNSIGNED NOT NULL DEFAULT 0,
    `status`          VARCHAR(20)  NOT NULL DEFAULT 'pendente',
    `log_json`        TEXT         NULL,
    `created_at`      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user`   (`user_id`),
    INDEX `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 3. Tabela de biblioteca de máscaras pré-carregadas ──────────────────────
CREATE TABLE IF NOT EXISTS `cop_mascaras_biblioteca` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `titulo`          VARCHAR(500) NOT NULL,
    `nome_amigavel`   VARCHAR(255) NOT NULL,
    `modalidade`      VARCHAR(20)  NOT NULL DEFAULT 'TC',
    `especialidade`   VARCHAR(100) NOT NULL DEFAULT 'Radiologia',
    `corpo`           LONGTEXT     NOT NULL,
    `secao_tecnica`   LONGTEXT     NULL,
    `secao_analise`   LONGTEXT     NULL,
    `secao_impressao` LONGTEXT     NULL,
    `secao_adicional` LONGTEXT     NULL,
    `tags`            VARCHAR(500) NULL,
    `origem_arquivo`  VARCHAR(255) NULL,
    `ativo`           TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_modalidade`    (`modalidade`),
    INDEX `idx_especialidade` (`especialidade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
