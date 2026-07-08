-- ============================================================
-- VOXEL Copilot — Módulo de Autorização PACS
-- 100% compatível com MariaDB 5.7 / MySQL 5.7
-- HostGator / cPanel / phpMyAdmin
--
-- Tabelas:
--   cop_pacs_unidades       — Unidades/clínicas com PACS cadastrado
--   cop_pacs_autorizacoes   — Vínculo médico ↔ unidade (código + token)
--   cop_pacs_dicom_config   — Configuração de tags DICOM por vínculo
--   cop_pacs_audit_log      — Log de uso do Copilot por unidade
--
-- INSTRUÇÕES:
--   Execute APÓS o schema principal (2026-07-05_copilot_schema.sql).
--   Execute UMA ÚNICA VEZ.
-- ============================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. UNIDADES PACS
--    Representa cada estabelecimento que possui um PACS e
--    deseja integrar com o VOXEL Copilot.
-- ============================================================
CREATE TABLE `cop_pacs_unidades` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `tenant_id`         INT UNSIGNED    NULL COMMENT 'Tenant do Copilot (NULL = plataforma)',

    -- Identificação da unidade
    `codigo_unidade`    VARCHAR(32)     NOT NULL COMMENT 'Código único gerado pelo Copilot para a unidade',
    `nome_instituicao`  VARCHAR(200)    NOT NULL,
    `cnpj`              VARCHAR(20)     NOT NULL,
    `razao_social`      VARCHAR(200)    NULL,
    `nome_fantasia`     VARCHAR(200)    NULL,

    -- Localização
    `cidade`            VARCHAR(100)    NOT NULL,
    `estado`            CHAR(2)         NOT NULL,
    `cep`               VARCHAR(10)     NULL,
    `endereco`          VARCHAR(300)    NULL,
    `telefone`          VARCHAR(20)     NULL,
    `email_contato`     VARCHAR(200)    NULL,

    -- PACS / DICOM
    `pacs_tipo`         VARCHAR(80)     NULL COMMENT 'Ex: Orthanc, DCM4CHEE, Horos, OsiriX, Conquest',
    `pacs_versao`       VARCHAR(40)     NULL,
    `pacs_ae_title`     VARCHAR(64)     NULL COMMENT 'DICOM AE Title do PACS',
    `pacs_host`         VARCHAR(255)    NULL COMMENT 'IP ou hostname do PACS',
    `pacs_port`         SMALLINT UNSIGNED NULL COMMENT 'Porta DICOM (padrão 4242)',
    `pacs_wado_url`     VARCHAR(500)    NULL COMMENT 'URL base WADO-RS ou WADO-URI',
    `pacs_stow_url`     VARCHAR(500)    NULL COMMENT 'URL STOW-RS para envio de laudos',
    `pacs_qido_url`     VARCHAR(500)    NULL COMMENT 'URL QIDO-RS para consulta',

    -- Status e controle
    `status`            ENUM('autorizado','nao_autorizado','pendente','suspenso') NOT NULL DEFAULT 'pendente',
    `motivo_status`     VARCHAR(500)    NULL COMMENT 'Motivo de suspensão ou não autorização',
    `total_laudos`      INT UNSIGNED    NOT NULL DEFAULT 0,
    `ultimo_uso`        DATETIME        NULL,
    `data_autorizacao`  DATETIME        NULL,
    `autorizado_por`    INT UNSIGNED    NULL COMMENT 'user_id do admin que autorizou',

    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_codigo_unidade` (`codigo_unidade`),
    UNIQUE KEY `uq_cnpj` (`cnpj`),
    KEY `idx_tenant` (`tenant_id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Unidades/clínicas com PACS integrado ao VOXEL Copilot';

-- ============================================================
-- 2. AUTORIZAÇÕES MÉDICO ↔ UNIDADE
--    Cada médico pode ser autorizado em múltiplas unidades.
--    O estabelecimento cadastra o código e token do médico.
-- ============================================================
CREATE TABLE `cop_pacs_autorizacoes` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `unidade_id`        INT UNSIGNED    NOT NULL COMMENT 'FK cop_pacs_unidades.id',
    `medico_user_id`    INT UNSIGNED    NOT NULL COMMENT 'FK cop_users.id (médico autorizado)',
    `tenant_id`         INT UNSIGNED    NULL,

    -- Credenciais de integração (geradas pelo Copilot, cadastradas no PACS)
    `codigo_medico`     VARCHAR(64)     NOT NULL COMMENT 'Código único do médico para esta unidade',
    `token_integracao`  VARCHAR(128)    NOT NULL COMMENT 'Token de autenticação para chamadas REST/DICOM',
    `token_expira_em`   DATETIME        NULL COMMENT 'NULL = sem expiração',

    -- Dados do médico para composição do laudo DICOM
    `medico_nome`       VARCHAR(200)    NOT NULL,
    `medico_crm`        VARCHAR(20)     NULL,
    `medico_crm_uf`     CHAR(2)         NULL,
    `medico_especialidade` VARCHAR(200) NULL,

    -- Permissões por modalidade
    `modalidades_permitidas` VARCHAR(200) NULL COMMENT 'Ex: CT,MR,CR,DX,PT,NM — NULL = todas',

    -- Status
    `status`            ENUM('ativo','inativo','revogado','pendente') NOT NULL DEFAULT 'pendente',
    `motivo_revogacao`  VARCHAR(500)    NULL,
    `total_laudos`      INT UNSIGNED    NOT NULL DEFAULT 0,
    `ultimo_laudo`      DATETIME        NULL,
    `data_ativacao`     DATETIME        NULL,

    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_codigo_medico_unidade` (`codigo_medico`, `unidade_id`),
    UNIQUE KEY `uq_token` (`token_integracao`),
    KEY `idx_unidade` (`unidade_id`),
    KEY `idx_medico` (`medico_user_id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Vínculo médico ↔ unidade PACS com código e token de integração';

-- ============================================================
-- 3. CONFIGURAÇÃO DE TAGS DICOM POR VÍNCULO
--    Define como o Copilot preenche as tags DICOM ao enviar
--    o laudo estruturado de volta ao PACS.
-- ============================================================
CREATE TABLE `cop_pacs_dicom_config` (
    `id`                    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `autorizacao_id`        INT UNSIGNED    NOT NULL COMMENT 'FK cop_pacs_autorizacoes.id',

    -- Tags de identificação do estudo (DICOM Study Module)
    `tag_study_instance_uid`    VARCHAR(128)    NULL COMMENT '(0020,000D) StudyInstanceUID — gerado pelo PACS',
    `tag_accession_number`      VARCHAR(64)     NULL COMMENT '(0008,0050) AccessionNumber',
    `tag_study_date`            VARCHAR(20)     NULL COMMENT '(0008,0020) StudyDate YYYYMMDD',
    `tag_study_time`            VARCHAR(20)     NULL COMMENT '(0008,0030) StudyTime HHMMSS',
    `tag_study_description`     VARCHAR(255)    NULL COMMENT '(0008,1030) StudyDescription',
    `tag_modality`              VARCHAR(20)     NULL COMMENT '(0008,0060) Modality CT/MR/CR/DX/PT/NM/US...',

    -- Tags do paciente (DICOM Patient Module)
    `tag_patient_id`            VARCHAR(64)     NULL COMMENT '(0010,0020) PatientID',
    `tag_patient_name`          VARCHAR(200)    NULL COMMENT '(0010,0010) PatientName (Last^First)',
    `tag_patient_birth_date`    VARCHAR(20)     NULL COMMENT '(0010,0030) PatientBirthDate YYYYMMDD',
    `tag_patient_sex`           CHAR(1)         NULL COMMENT '(0010,0040) PatientSex M/F/O',

    -- Tags do médico laudador (DICOM General Study Module)
    `tag_referring_physician`   VARCHAR(200)    NULL COMMENT '(0008,0090) ReferringPhysicianName',
    `tag_performing_physician`  VARCHAR(200)    NULL COMMENT '(0008,1048) PhysicianOfRecord',
    `tag_reading_physician`     VARCHAR(200)    NULL COMMENT '(0008,1060) NameOfPhysiciansReadingStudy',
    `tag_operator_name`         VARCHAR(200)    NULL COMMENT '(0008,1070) OperatorsName',

    -- Tags da instituição (DICOM Equipment Module)
    `tag_institution_name`      VARCHAR(200)    NULL COMMENT '(0008,0080) InstitutionName',
    `tag_institution_address`   VARCHAR(500)    NULL COMMENT '(0008,0081) InstitutionAddress',
    `tag_station_name`          VARCHAR(64)     NULL COMMENT '(0008,1010) StationName',
    `tag_manufacturer`          VARCHAR(100)    NULL COMMENT '(0008,0070) Manufacturer',

    -- Tags do SR (Structured Report — laudo DICOM)
    `tag_sop_class_uid`         VARCHAR(128)    NULL COMMENT '(0008,0016) SOPClassUID — 1.2.840.10008.5.1.4.1.1.88.33 para SR',
    `tag_sop_instance_uid`      VARCHAR(128)    NULL COMMENT '(0008,0018) SOPInstanceUID — gerado pelo Copilot',
    `tag_series_instance_uid`   VARCHAR(128)    NULL COMMENT '(0020,000E) SeriesInstanceUID',
    `tag_series_number`         VARCHAR(10)     NULL COMMENT '(0020,0011) SeriesNumber',
    `tag_instance_number`       VARCHAR(10)     NULL COMMENT '(0020,0013) InstanceNumber',
    `tag_content_date`          VARCHAR(20)     NULL COMMENT '(0008,0023) ContentDate',
    `tag_content_time`          VARCHAR(20)     NULL COMMENT '(0008,0033) ContentTime',
    `tag_completion_flag`       VARCHAR(20)     NULL COMMENT '(0040,A491) CompletionFlag COMPLETE/PARTIAL',
    `tag_verification_flag`     VARCHAR(20)     NULL COMMENT '(0040,A493) VerificationFlag VERIFIED/UNVERIFIED',

    -- Configurações de envio
    `formato_laudo`             ENUM('SR_DICOM','PDF_ENCAPSULADO','HL7_ORU','TEXTO_PLANO') NOT NULL DEFAULT 'SR_DICOM',
    `enviar_automatico`         TINYINT(1)      NOT NULL DEFAULT 0 COMMENT 'Enviar laudo ao PACS após assinatura',
    `incluir_assinatura_img`    TINYINT(1)      NOT NULL DEFAULT 1,
    `incluir_qr_code`           TINYINT(1)      NOT NULL DEFAULT 0,
    `charset_dicom`             VARCHAR(20)     NOT NULL DEFAULT 'ISO_IR 192' COMMENT 'SpecificCharacterSet (0008,0005)',

    `created_at`                TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`                TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_autorizacao` (`autorizacao_id`),
    KEY `idx_autorizacao` (`autorizacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Mapeamento de tags DICOM por vínculo médico-unidade';

-- ============================================================
-- 4. LOG DE AUDITORIA DE USO
--    Registra cada uso do Copilot vinculado a uma unidade PACS.
-- ============================================================
CREATE TABLE `cop_pacs_audit_log` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `unidade_id`        INT UNSIGNED    NOT NULL,
    `autorizacao_id`    INT UNSIGNED    NULL,
    `medico_user_id`    INT UNSIGNED    NOT NULL,
    `tenant_id`         INT UNSIGNED    NULL,

    -- Dados do exame
    `accession_number`  VARCHAR(64)     NULL COMMENT '(0008,0050)',
    `study_uid`         VARCHAR(128)    NULL COMMENT '(0020,000D)',
    `modalidade`        VARCHAR(20)     NULL,
    `paciente_id_dicom` VARCHAR(64)     NULL COMMENT '(0010,0020)',

    -- Evento
    `evento`            ENUM('laudo_gerado','laudo_assinado','laudo_enviado_pacs','token_validado','token_invalido','autorizacao_criada','autorizacao_revogada') NOT NULL,
    `status`            ENUM('sucesso','erro','pendente') NOT NULL DEFAULT 'sucesso',
    `detalhes`          TEXT            NULL,
    `ip`                VARCHAR(45)     NULL,
    `user_agent`        VARCHAR(500)    NULL,

    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_unidade` (`unidade_id`),
    KEY `idx_medico` (`medico_user_id`),
    KEY `idx_evento` (`evento`),
    KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Log de auditoria de uso do Copilot por unidade PACS';

SET FOREIGN_KEY_CHECKS = 1;
