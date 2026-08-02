-- =============================================================================
-- Migration: 2026-08-02_pacs_copilot_api_token.sql
-- Objetivo : Adicionar copilot_api_token em cop_pacs_unidades.
--            Este token é gerado pelo Copilot e retornado ao PACS para que
--            o PACS use como Bearer em todos os webhooks enviados ao Copilot.
--
-- Compatível com MySQL 5.7 / MariaDB 5.7 / Hostgator compartilhado.
-- Execute manualmente no phpMyAdmin APÓS 2026-08-02_pacs_viewer_url.sql.
-- =============================================================================
SET NAMES utf8mb4;

ALTER TABLE `cop_pacs_unidades`
    ADD COLUMN `copilot_api_token`  VARCHAR(256) NULL
        COMMENT 'Token gerado pelo Copilot para autenticar webhooks recebidos do PACS (Bearer)'
        AFTER `pacs_viewer_url`;

-- Índice para busca rápida por token
ALTER TABLE `cop_pacs_unidades`
    ADD INDEX `idx_copilot_api_token` (`copilot_api_token`(64));
