-- ============================================================
-- Migration: 2026-07-30_alter_cop_templates_estrutura_json.sql
-- Adiciona coluna estrutura_json (e outras colunas faltantes)
-- na tabela cop_templates.
--
-- CONTEXTO:
--   A migration original (2026-07-05_copilot_schema.sql) criou a
--   tabela cop_templates sem as colunas de seções estruturadas.
--   A migration 2026-07-08_mascaras_importacao.sql adicionou
--   secao_tecnica, secao_analise, secao_impressao, secao_adicional,
--   origem, origem_arquivo, tags, publico — mas NÃO estrutura_json.
--   O TemplatesController e o SQL de importação dos 121 templates TC
--   dependem da coluna estrutura_json para funcionar corretamente.
--
-- ORDEM DE EXECUÇÃO NO SERVIDOR:
--   1. Este arquivo (2026-07-30_alter_cop_templates_estrutura_json.sql)
--   2. 2026-07-27_dicom_study_description.sql (se ainda não executou)
--   3. 2026-07-30_mascaras_tc_import.sql (ajustando @tenant_id)
--
-- Compatível com MariaDB 5.7 / MySQL 5.7 / HostGator / phpMyAdmin.
-- Execute cada ALTER TABLE separadamente no phpMyAdmin caso alguma
-- coluna já exista (o servidor retornará erro apenas para a duplicada).
-- ============================================================

SET NAMES utf8mb4;

-- ── Adiciona estrutura_json em cop_templates ─────────────────────────────────
-- Coluna principal: JSON com chaves indicacao, tecnica, achados, impressao, recomendacao
ALTER TABLE `cop_templates`
    ADD COLUMN `estrutura_json` LONGTEXT NULL
        COMMENT 'JSON estruturado com seções do laudo: indicacao, tecnica, achados, impressao, recomendacao'
        AFTER `corpo`;

-- ── Verifica e adiciona colunas auxiliares (caso não existam) ────────────────
-- ATENÇÃO: Se a migration 2026-07-08_mascaras_importacao.sql já foi executada,
-- as colunas abaixo já existem. Execute apenas as que faltarem.

-- Seções legadas (compatibilidade com importação DOCX antiga)
-- ALTER TABLE `cop_templates` ADD COLUMN `secao_tecnica`   LONGTEXT NULL AFTER `corpo`;
-- ALTER TABLE `cop_templates` ADD COLUMN `secao_analise`   LONGTEXT NULL AFTER `secao_tecnica`;
-- ALTER TABLE `cop_templates` ADD COLUMN `secao_impressao` LONGTEXT NULL AFTER `secao_analise`;
-- ALTER TABLE `cop_templates` ADD COLUMN `secao_adicional` LONGTEXT NULL AFTER `secao_impressao`;

-- Origem e controle de visibilidade
-- ALTER TABLE `cop_templates` ADD COLUMN `origem`          VARCHAR(30) NOT NULL DEFAULT 'manual' AFTER `secao_adicional`;
-- ALTER TABLE `cop_templates` ADD COLUMN `origem_arquivo`  VARCHAR(255) NULL AFTER `origem`;
-- ALTER TABLE `cop_templates` ADD COLUMN `tags`            VARCHAR(500) NULL AFTER `origem_arquivo`;
-- ALTER TABLE `cop_templates` ADD COLUMN `publico`         TINYINT(1) NOT NULL DEFAULT 0 AFTER `tags`;

-- ── Índice para busca por estrutura_json (opcional, melhora performance) ─────
-- Não é possível indexar LONGTEXT diretamente no MySQL 5.7/MariaDB sem prefix.
-- O índice abaixo usa os primeiros 100 chars para verificação rápida de nulidade.
-- ALTER TABLE `cop_templates` ADD INDEX `idx_estrutura_json_exists` ((estrutura_json IS NOT NULL));
-- Nota: expressões em índices só são suportadas no MySQL 8+. Omitido para compatibilidade.
