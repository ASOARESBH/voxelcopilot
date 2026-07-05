-- ═══════════════════════════════════════════════════════════════════════════
-- VOXEL Copilot — Seed: Médico de Teste
-- Compatível com MariaDB 5.7 / MySQL 5.7 / HostGator / cPanel
-- Prefixo das tabelas: cop_
-- ═══════════════════════════════════════════════════════════════════════════
-- Médico: Dr. André Soares
-- E-mail: andre.soares@voxelpacs.com.br
-- Senha:  Medico123@
-- CRM:    12345/MG
-- ═══════════════════════════════════════════════════════════════════════════

-- 1. Inserir o médico na tabela cop_users
--    (todos os campos de perfil ficam nesta tabela conforme o schema)
INSERT INTO `cop_users` (
    `name`,
    `email`,
    `password`,
    `role`,
    `status`,
    `crm`,
    `crm_uf`,
    `especialidades`,
    `telefone`,
    `cep`,
    `logradouro`,
    `numero`,
    `complemento`,
    `bairro`,
    `cidade`,
    `estado`,
    `email_verificado`,
    `created_at`,
    `updated_at`
) VALUES (
    'Dr. André Soares',
    'andre.soares@voxelpacs.com.br',
    '$2b$12$dVRvW9monwJs80n8opPco..imZa1SwxxGJ52IfplcT6lKpFJQmRmO',
    'medico',
    'ativo',
    '12345',
    'MG',
    '["Radiologia e Diagnóstico por Imagem","Tomografia Computadorizada","Ressonância Magnética","Medicina Nuclear","Ultrassonografia"]',
    '(31) 99999-0001',
    '30130-110',
    'Av. Afonso Pena',
    '1234',
    'Sala 501',
    'Centro',
    'Belo Horizonte',
    'MG',
    1,
    NOW(),
    NOW()
);

-- 2. Capturar o ID gerado
SET @medico_id = LAST_INSERT_ID();

-- 3. Inserir o perfil de estilo de IA na cop_medico_perfil
--    (tenant_id = 1 — ajuste se necessário)
INSERT INTO `cop_medico_perfil` (
    `user_id`,
    `tenant_id`,
    `estilo_conclusao`,
    `total_laudos`,
    `total_correcoes`
) VALUES (
    @medico_id,
    1,
    'normal',
    0,
    0
);

-- ═══════════════════════════════════════════════════════════════════════════
-- Verificação: confirmar inserção
-- ═══════════════════════════════════════════════════════════════════════════
SELECT
    u.id,
    u.name,
    u.email,
    u.role,
    u.status,
    u.crm,
    u.crm_uf,
    u.cidade,
    u.estado,
    p.estilo_conclusao
FROM `cop_users` u
LEFT JOIN `cop_medico_perfil` p ON p.user_id = u.id
WHERE u.email = 'andre.soares@voxelpacs.com.br';
