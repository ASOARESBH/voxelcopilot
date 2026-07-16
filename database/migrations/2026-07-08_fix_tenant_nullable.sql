-- ============================================================
-- VOXEL Copilot — Fix: tenant_id nullable em cop_workspaces e cop_laudos
-- Compatível com MariaDB 5.7 / MySQL 5.7 / HostGator / phpMyAdmin
--
-- PROBLEMA: cop_workspaces.tenant_id e cop_laudos.tenant_id estavam
-- definidos como NOT NULL com FK para cop_tenants, causando erro 500
-- quando médicos em modo standalone (sem tenant) tentavam criar laudos.
--
-- SOLUÇÃO: Tornar tenant_id NULL nas duas tabelas e remover as FKs de
-- tenant (médicos standalone não têm tenant_id).
--
-- INSTRUÇÕES:
--   Execute UMA ÚNICA VEZ após o schema principal.
--   Não requer dados existentes — apenas altera estrutura.
-- ============================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── cop_workspaces ──────────────────────────────────────────
-- Remove FK de tenant antes de alterar a coluna
ALTER TABLE `cop_workspaces`
    DROP FOREIGN KEY `fk_cop_ws_tenant`;

-- Torna tenant_id nullable
ALTER TABLE `cop_workspaces`
    MODIFY COLUMN `tenant_id` INT UNSIGNED NULL DEFAULT NULL;

-- ── cop_laudos ──────────────────────────────────────────────
-- Remove FK de tenant antes de alterar a coluna
ALTER TABLE `cop_laudos`
    DROP FOREIGN KEY `fk_cop_laudo_tenant`;

-- Torna tenant_id nullable
ALTER TABLE `cop_laudos`
    MODIFY COLUMN `tenant_id` INT UNSIGNED NULL DEFAULT NULL;

-- ── cop_templates ─────────────────────────────────────────
ALTER TABLE `cop_templates`
    DROP FOREIGN KEY `fk_cop_tpl_tenant`;

ALTER TABLE `cop_templates`
    MODIFY COLUMN `tenant_id` INT UNSIGNED NULL DEFAULT NULL;

-- ── cop_autotextos ──────────────────────────────────────────
ALTER TABLE `cop_autotextos`
    DROP FOREIGN KEY `fk_cop_at_tenant`;

ALTER TABLE `cop_autotextos`
    MODIFY COLUMN `tenant_id` INT UNSIGNED NULL DEFAULT NULL;

SET FOREIGN_KEY_CHECKS = 1;
