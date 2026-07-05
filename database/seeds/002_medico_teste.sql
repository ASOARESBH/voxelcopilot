-- ============================================================
-- VOXEL Copilot — Seed: Médico de Teste
-- 100% compatível com MariaDB 5.7 / MySQL 5.7
-- HostGator / cPanel / phpMyAdmin
--
-- Execute APÓS os dois migrations principais.
-- Médico: Dr. André Soares
-- E-mail: andre.soares@voxelpacs.com.br
-- Senha:  Medico123@
-- CRM:    12345/MG
-- ============================================================

-- 1. Inserir o médico em cop_users
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
    `ia_modelo`,
    `ia_temperatura`,
    `ia_estilo`,
    `email_verificado`,
    `created_at`,
    `updated_at`
) VALUES (
    'Dr. Andre Soares',
    'andre.soares@voxelpacs.com.br',
    '$2b$12$dVRvW9monwJs80n8opPco..imZa1SwxxGJ52IfplcT6lKpFJQmRmO',
    'medico',
    'ativo',
    '12345',
    'MG',
    '["Radiologia e Diagnostico por Imagem","Tomografia Computadorizada","Ressonancia Magnetica","Medicina Nuclear","Ultrassonografia"]',
    '(31) 99999-0001',
    '30130-110',
    'Av. Afonso Pena',
    '1234',
    'Sala 501',
    'Centro',
    'Belo Horizonte',
    'MG',
    'gpt-4o',
    0.30,
    'formal',
    1,
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    `name`             = VALUES(`name`),
    `status`           = VALUES(`status`),
    `email_verificado` = VALUES(`email_verificado`),
    `updated_at`       = NOW();

-- 2. Capturar o ID do médico inserido
SET @medico_id = LAST_INSERT_ID();

-- 3. Inserir perfil de estilo de IA em cop_medico_perfil
--    Nota: tenant_id = 1 pressupõe que existe ao menos 1 tenant.
--    Se o banco não tiver tenant, comente as linhas abaixo.
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
)
ON DUPLICATE KEY UPDATE
    `estilo_conclusao` = VALUES(`estilo_conclusao`),
    `updated_at`       = NOW();

-- ============================================================
-- Verificação — confirmar inserção
-- ============================================================
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
    u.ia_modelo,
    p.estilo_conclusao
FROM `cop_users` u
LEFT JOIN `cop_medico_perfil` p ON p.user_id = u.id
WHERE u.email = 'andre.soares@voxelpacs.com.br';
