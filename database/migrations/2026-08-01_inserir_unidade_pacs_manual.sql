-- =============================================================================
-- Migration: 2026-08-01_inserir_unidade_pacs_manual.sql
-- Objetivo : Insere manualmente a unidade PACS em cop_pacs_unidades do
--            VoxelCopilot para resolver o erro "unidade_nao_encontrada".
--
-- CONTEXTO: A chamada automática do PACS ao Copilot não funcionou porque
--           o campo copilot_url estava NULL em bi_copilot_unidades.
--           Este script insere diretamente a unidade no banco do Copilot.
--
-- COMO USAR:
--   1. Copie o codigo_unidade e chave_secreta de bi_copilot_unidades
--      no banco do VoxelPACS (inlaud99_voxelpacs)
--   2. Cole os valores abaixo substituindo os placeholders
--   3. Execute no banco do VoxelCopilot (inlaud99_voxelbi)
--
-- Compatível com MySQL 5.7 / MariaDB 5.7 / Hostgator compartilhado.
-- =============================================================================
SET NAMES utf8mb4;

-- ─── PASSO 1: Verifique se já existe ─────────────────────────────────────────
-- Execute primeiro para ver se o código já está cadastrado:
-- SELECT id, codigo_unidade, status FROM cop_pacs_unidades WHERE codigo_unidade = 'PACS-2026-0002-8A05FE';

-- ─── PASSO 2: Insira a unidade ────────────────────────────────────────────────
-- Substitua os valores conforme os dados do seu VoxelPACS:
--   @codigo_unidade : copie de bi_copilot_unidades.codigo_unidade
--   @chave_secreta  : copie de bi_copilot_unidades.chave_secreta
--   @nome           : nome da clínica/hospital
--   @cnpj           : CNPJ da clínica (pode deixar NULL)

SET @codigo_unidade = 'PACS-2026-0002-8A05FE';
SET @chave_secreta  = 'b6028a9ca6d44fb6cd71feef0d1a0adc545af2dbdafd8d9b73...';  -- Substitua pela chave completa do VoxelPACS
SET @nome           = 'ORIX TELERRADIOLOGIA LTDA';  -- Substitua pelo nome real
SET @cnpj           = NULL;   -- Substitua pelo CNPJ se disponível
SET @cidade         = NULL;   -- Substitua pela cidade se disponível
SET @estado         = NULL;   -- Substitua pelo estado (UF) se disponível

-- Insere ou atualiza (UPSERT seguro para MySQL 5.7)
INSERT INTO `cop_pacs_unidades`
    (codigo_unidade, chave_secreta, nome_instituicao, cnpj, cidade, estado,
     pacs_tipo, status, created_at, updated_at)
VALUES
    (@codigo_unidade, @chave_secreta, @nome, @cnpj, @cidade, @estado,
     'VoxelPACS', 'pendente', NOW(), NOW())
ON DUPLICATE KEY UPDATE
    chave_secreta    = VALUES(chave_secreta),
    nome_instituicao = COALESCE(VALUES(nome_instituicao), nome_instituicao),
    updated_at       = NOW();

-- ─── PASSO 3: Confirme a inserção ────────────────────────────────────────────
SELECT id, codigo_unidade, nome_instituicao, status, created_at
FROM `cop_pacs_unidades`
WHERE codigo_unidade = @codigo_unidade;

-- =============================================================================
-- APÓS EXECUTAR ESTE SCRIPT:
-- 1. O médico acessa Configurações > Autorização no VoxelCopilot
-- 2. Informa o Código da Unidade: PACS-2026-0002-8A05FE
--    (NÃO é o e-mail — é o código gerado no VoxelPACS)
-- 3. Informa o Token de Integração: CPLT-... (gerado no VoxelPACS)
-- 4. Clica em Vincular Unidade
-- =============================================================================
