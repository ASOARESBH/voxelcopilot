-- ============================================================
-- VOXEL Copilot — Grupos de Médicos
-- Compatível com MariaDB 5.7 / HostGator / phpMyAdmin
-- NÃO usa: IF NOT EXISTS em ALTER, PROCEDURE, FUNCTION, EVENT
-- ============================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── 1. Tabela de grupos ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `cop_grupos_medicos` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nome`        VARCHAR(100) NOT NULL,
    `descricao`   VARCHAR(255) NULL,
    `cor`         VARCHAR(7)   NOT NULL DEFAULT '#1a56db',
    `icone`       VARCHAR(50)  NOT NULL DEFAULT 'fa-user-doctor',
    `ativo`       TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 2. Vínculo médico ↔ grupo ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `cop_medico_grupos` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT UNSIGNED NOT NULL,
    `grupo_id`   INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_user_grupo` (`user_id`, `grupo_id`),
    INDEX `idx_user_id`  (`user_id`),
    INDEX `idx_grupo_id` (`grupo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 3. Adiciona coluna grupo_id em cop_users (referência rápida) ─────────────
ALTER TABLE `cop_users` ADD COLUMN `grupo_id` INT UNSIGNED NULL AFTER `role`;

-- ── 4. Seed dos grupos padrão ────────────────────────────────────────────────
INSERT INTO `cop_grupos_medicos` (`nome`, `descricao`, `cor`, `icone`) VALUES
('Radiologistas',           'Médicos especialistas em radiologia e diagnóstico por imagem', '#1a56db', 'fa-x-ray'),
('Clínico Geral',           'Médicos clínicos gerais e de família', '#16a34a', 'fa-stethoscope'),
('Cardiologistas',          'Especialistas em cardiologia e doenças cardiovasculares', '#dc2626', 'fa-heart-pulse'),
('Neurologistas',           'Especialistas em neurologia e sistema nervoso', '#7c3aed', 'fa-brain'),
('Oncologistas',            'Especialistas em oncologia e tratamento do câncer', '#ea580c', 'fa-ribbon'),
('Ortopedistas',            'Especialistas em ortopedia e traumatologia', '#0891b2', 'fa-bone'),
('Pediatras',               'Médicos especialistas em saúde infantil', '#0d9488', 'fa-baby'),
('Ginecologistas',          'Especialistas em ginecologia e obstetrícia', '#db2777', 'fa-venus'),
('Urologistas',             'Especialistas em urologia e sistema urinário', '#2563eb', 'fa-droplet'),
('Gastroenterologistas',    'Especialistas em gastroenterologia e aparelho digestivo', '#ca8a04', 'fa-stomach'),
('Pneumologistas',          'Especialistas em pneumologia e doenças respiratórias', '#0284c7', 'fa-lungs'),
('Endocrinologistas',       'Especialistas em endocrinologia e metabolismo', '#9333ea', 'fa-flask'),
('Médicos Nucleares',       'Especialistas em medicina nuclear e PET-CT', '#b45309', 'fa-atom'),
('Ultrassonografistas',     'Especialistas em ultrassonografia diagnóstica', '#0f766e', 'fa-wave-square'),
('Residentes / Internos',   'Médicos em formação e residência médica', '#64748b', 'fa-graduation-cap');

SET FOREIGN_KEY_CHECKS = 1;
