-- =============================================================================
-- Migration: 2026-07-31_alter_cop_pacs_unidades_nullable.sql
-- Objetivo : Tornar campos obrigatórios nullable em cop_pacs_unidades para
--            permitir o registro automático de unidades via API do PACS.
--
--            O PACS pode não ter todos os dados cadastrais da unidade no
--            momento da geração do token — eles podem ser completados depois.
--
-- Compatível com MySQL 5.7 / MariaDB 5.7 / Hostgator compartilhado.
-- Execute ANTES de usar o endpoint /api/pacs/registrar-unidade.
-- =============================================================================
SET NAMES utf8mb4;

-- Tornar campos NOT NULL → NULL para aceitar registros automáticos do PACS
ALTER TABLE `cop_pacs_unidades`
    MODIFY COLUMN `nome_instituicao` VARCHAR(200) NULL    DEFAULT NULL,
    MODIFY COLUMN `cnpj`             VARCHAR(20)  NULL    DEFAULT NULL,
    MODIFY COLUMN `cidade`           VARCHAR(100) NULL    DEFAULT NULL,
    MODIFY COLUMN `estado`           CHAR(2)      NULL    DEFAULT NULL;

-- Remover UNIQUE de cnpj (pode haver múltiplos registros sem CNPJ ainda)
-- e substituir por índice normal
ALTER TABLE `cop_pacs_unidades`
    DROP INDEX `uq_cnpj`;

ALTER TABLE `cop_pacs_unidades`
    ADD INDEX `idx_cnpj` (`cnpj`);
