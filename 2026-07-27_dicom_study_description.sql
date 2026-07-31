-- ============================================================
-- Migration: 2026-07-27_dicom_study_description.sql
-- Adiciona campo dicom_study_description (TAG DICOM 0008,1030)
-- nas tabelas cop_templates e cop_mascaras_biblioteca para
-- vinculação automática de templates no Workspace.
-- Compatível com MariaDB 5.7 / MySQL 5.7 / HostGator.
-- ============================================================

-- 1. Tabela cop_templates (máscaras importadas / criadas pelo médico)
ALTER TABLE `cop_templates`
    ADD COLUMN `dicom_study_description` VARCHAR(500) NULL
        COMMENT 'TAG DICOM (0008,1030) StudyDescription — vincula template ao exame automaticamente'
        AFTER `modalidade`;

-- 2. Tabela cop_mascaras_biblioteca (biblioteca do sistema)
ALTER TABLE `cop_mascaras_biblioteca`
    ADD COLUMN `dicom_study_description` VARCHAR(500) NULL
        COMMENT 'TAG DICOM (0008,1030) StudyDescription — vincula máscara ao exame automaticamente'
        AFTER `modalidade`;

-- 3. Índice para busca rápida por study description
CREATE INDEX `idx_templates_dicom_study`
    ON `cop_templates` (`dicom_study_description`(100));

CREATE INDEX `idx_mascaras_bib_dicom_study`
    ON `cop_mascaras_biblioteca` (`dicom_study_description`(100));
