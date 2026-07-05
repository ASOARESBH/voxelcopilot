-- ═══════════════════════════════════════════════════════════════════════════
-- VOXEL Copilot — Seed: Médico de Teste
-- Compatível com MariaDB 5.7 / MySQL 5.7 / HostGator / cPanel
-- ═══════════════════════════════════════════════════════════════════════════
-- Médico: Dr. André Soares
-- E-mail: andre.soares@voxelpacs.com.br
-- Senha:  Medico123@
-- CRM:    12345/MG
-- ═══════════════════════════════════════════════════════════════════════════

-- 1. Inserir o médico na tabela de usuários
INSERT INTO users (
    name,
    email,
    password,
    role,
    status,
    email_verified_at,
    created_at,
    updated_at
) VALUES (
    'Dr. André Soares',
    'andre.soares@voxelpacs.com.br',
    '$2b$12$dVRvW9monwJs80n8opPco..imZa1SwxxGJ52IfplcT6lKpFJQmRmO',
    'medico',
    'ativo',
    NOW(),
    NOW(),
    NOW()
);

-- 2. Capturar o ID gerado
SET @medico_id = LAST_INSERT_ID();

-- 3. Inserir o perfil completo do médico
INSERT INTO medico_profiles (
    user_id,
    crm,
    crm_uf,
    especialidades,
    telefone,
    cep,
    logradouro,
    numero,
    complemento,
    bairro,
    cidade,
    estado,
    bio,
    total_laudos,
    created_at,
    updated_at
) VALUES (
    @medico_id,
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
    'Radiologista com 15 anos de experiência em diagnóstico por imagem, especialista em TC e RM de alta complexidade.',
    0,
    NOW(),
    NOW()
);

-- 4. Inserir configurações de IA do médico
INSERT INTO medico_ai_config (
    user_id,
    ai_provider,
    ai_model,
    ai_temperature,
    ai_style,
    ai_language,
    auto_suggest,
    auto_template,
    speech_enabled,
    created_at,
    updated_at
) VALUES (
    @medico_id,
    'openai',
    'gpt-4o',
    '0.30',
    'formal',
    'pt-BR',
    1,
    1,
    0,
    NOW(),
    NOW()
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
    mp.crm,
    mp.crm_uf,
    mp.cidade,
    mp.estado
FROM users u
LEFT JOIN medico_profiles mp ON mp.user_id = u.id
WHERE u.email = 'andre.soares@voxelpacs.com.br';
