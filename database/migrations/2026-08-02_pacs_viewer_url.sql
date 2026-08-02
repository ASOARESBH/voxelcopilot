-- =============================================================================
-- Migration: 2026-08-02_pacs_viewer_url.sql
-- Objetivo : Adicionar pacs_viewer_url em cop_pacs_unidades para permitir
--            abertura do viewer DICOM diretamente do Workspace do Copilot.
--
-- Compatível com MySQL 5.7 / MariaDB 5.7 / Hostgator compartilhado.
-- Execute manualmente no phpMyAdmin APÓS 2026-07-31_alter_pacs_unidades_integracao.sql.
-- =============================================================================
SET NAMES utf8mb4;

ALTER TABLE `cop_pacs_unidades`
    ADD COLUMN `pacs_viewer_url`    VARCHAR(500) NULL
        COMMENT 'URL base do viewer DICOM (ex: https://server.voxelpacs.com.br/viewer)'
        AFTER `pacs_webhook_url`;
