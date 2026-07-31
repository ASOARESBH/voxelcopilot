-- =============================================================================
-- Migration: 2026-07-31_alter_pacs_unidades_integracao.sql
-- Objetivo : Adicionar campos necessários para a integração bidirecional
--            VoxelPACS → VOXEL Copilot nas tabelas já existentes.
--
-- Compatível com MySQL 5.7 / MariaDB 5.7 / Hostgator compartilhado.
-- Execute manualmente no phpMyAdmin APÓS 2026-07-08_autorizacao_pacs.sql.
-- =============================================================================

SET NAMES utf8mb4;

-- ─────────────────────────────────────────────────────────────────────────────
-- 1. cop_pacs_unidades
--    Adiciona campos para receber webhooks do PACS e validar assinatura HMAC.
--    O PACS gera a chave_secreta ao criar o código de unidade.
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE `cop_pacs_unidades`
    ADD COLUMN `chave_secreta`      VARCHAR(128) NULL
        COMMENT 'Chave HMAC-SHA256 gerada pelo PACS para assinar webhooks'
        AFTER `codigo_unidade`,
    ADD COLUMN `pacs_api_token`     VARCHAR(256) NULL
        COMMENT 'Bearer token para autenticar chamadas recebidas do PACS'
        AFTER `chave_secreta`,
    ADD COLUMN `pacs_webhook_url`   VARCHAR(500) NULL
        COMMENT 'URL do endpoint webhook no PACS (para enviar laudo finalizado)'
        AFTER `pacs_api_token`,
    ADD COLUMN `total_exames_recv`  INT UNSIGNED NOT NULL DEFAULT 0
        COMMENT 'Total de exames recebidos via webhook'
        AFTER `total_laudos`,
    ADD COLUMN `ultimo_webhook`     DATETIME NULL
        COMMENT 'Último evento recebido via webhook'
        AFTER `ultimo_uso`;

-- ─────────────────────────────────────────────────────────────────────────────
-- 2. cop_pacs_autorizacoes
--    Adiciona campo para armazenar o token de integração do médico gerado
--    pelo PACS (bi_copilot_medico_tokens.token_integracao).
--    Também adiciona snapshot de dados do médico para uso offline.
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE `cop_pacs_autorizacoes`
    ADD COLUMN `pacs_medico_token`  VARCHAR(128) NULL
        COMMENT 'Token gerado pelo PACS (bi_copilot_medico_tokens.token_integracao)'
        AFTER `token_integracao`,
    ADD COLUMN `pacs_tenant_id`     INT UNSIGNED NULL
        COMMENT 'ID do tenant no VoxelPACS (bi_tenants.id)'
        AFTER `pacs_medico_token`,
    ADD COLUMN `pacs_medico_id`     INT UNSIGNED NULL
        COMMENT 'ID do médico no VoxelPACS (bi_medicos.id)'
        AFTER `pacs_tenant_id`;
