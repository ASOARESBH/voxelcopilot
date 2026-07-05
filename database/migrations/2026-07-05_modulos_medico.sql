-- ============================================================
-- VOXEL Copilot — Módulos do Médico
-- Compatível com MariaDB 5.7 / MySQL 5.7 / HostGator / cPanel
-- ============================================================

-- -------------------------------------------------------
-- Fila de Exames
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS cop_fila (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  medico_id     INT UNSIGNED NOT NULL,
  tenant_id     INT UNSIGNED,
  accession     VARCHAR(64),
  study_uid     VARCHAR(128),
  paciente_nome VARCHAR(200),
  paciente_cpf  VARCHAR(20),
  modalidade    VARCHAR(20),
  descricao     VARCHAR(255),
  prioridade    ENUM('urgente','alta','normal','baixa') NOT NULL DEFAULT 'normal',
  categoria     VARCHAR(50),
  status        ENUM('aguardando','em_laudo','laudado','assinado','cancelado') NOT NULL DEFAULT 'aguardando',
  data_exame    DATETIME,
  data_entrada  DATETIME DEFAULT CURRENT_TIMESTAMP,
  data_inicio   DATETIME,
  data_fim      DATETIME,
  sla_minutos   INT UNSIGNED,
  ia_tags       TEXT,
  pacs_url      VARCHAR(500),
  INDEX idx_fila_medico (medico_id),
  INDEX idx_fila_status (status),
  INDEX idx_fila_prioridade (prioridade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Pacientes
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS cop_pacientes (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id     INT UNSIGNED,
  nome          VARCHAR(200) NOT NULL,
  cpf           VARCHAR(20),
  nascimento    DATE,
  sexo          CHAR(1),
  telefone      VARCHAR(30),
  email         VARCHAR(150),
  cep           VARCHAR(10),
  logradouro    VARCHAR(200),
  bairro        VARCHAR(100),
  cidade        VARCHAR(100),
  estado        CHAR(2),
  pacs_patient_id VARCHAR(64),
  criado_em     DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pac_cpf (cpf),
  INDEX idx_pac_nome (nome),
  INDEX idx_pac_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Comparativos
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS cop_comparativos (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  medico_id          INT UNSIGNED NOT NULL,
  paciente_id        INT UNSIGNED,
  accession_atual    VARCHAR(64),
  accession_anterior VARCHAR(64),
  study_uid_atual    VARCHAR(128),
  study_uid_anterior VARCHAR(128),
  modalidade         VARCHAR(20),
  descricao_atual    VARCHAR(255),
  descricao_anterior VARCHAR(255),
  data_atual         DATETIME,
  data_anterior      DATETIME,
  ia_delta           TEXT,
  status             ENUM('pendente','em_analise','concluido') DEFAULT 'pendente',
  criado_em          DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_comp_medico (medico_id),
  INDEX idx_comp_paciente (paciente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Vision AI — Análises de Imagem
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS cop_vision_analises (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  medico_id     INT UNSIGNED NOT NULL,
  paciente_id   INT UNSIGNED,
  accession     VARCHAR(64),
  study_uid     VARCHAR(128),
  modalidade    VARCHAR(20),
  descricao     VARCHAR(255),
  status        ENUM('processando','concluido','erro') DEFAULT 'processando',
  achados_ia    TEXT,
  confianca     TINYINT UNSIGNED DEFAULT 0,
  modelo_usado  VARCHAR(50),
  tokens_usados INT UNSIGNED DEFAULT 0,
  criado_em     DATETIME DEFAULT CURRENT_TIMESTAMP,
  concluido_em  DATETIME,
  INDEX idx_vision_medico (medico_id),
  INDEX idx_vision_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Speech — Histórico de Ditados
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS cop_speech_historico (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  medico_id     INT UNSIGNED NOT NULL,
  transcricao   TEXT,
  duracao_seg   INT UNSIGNED DEFAULT 0,
  palavras      INT UNSIGNED DEFAULT 0,
  modelo_usado  VARCHAR(50),
  status        ENUM('processando','concluido','erro') DEFAULT 'processando',
  criado_em     DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_speech_medico (medico_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Pesquisa Clínica — Histórico de Buscas
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS cop_pesquisa_historico (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  medico_id     INT UNSIGNED NOT NULL,
  query         VARCHAR(500),
  tipo          ENUM('pubmed','uptodate','radiopaedia','copilot') DEFAULT 'copilot',
  resultado     TEXT,
  criado_em     DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pesq_medico (medico_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Marketplace — Plugins Instalados
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS cop_plugins (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug          VARCHAR(50) NOT NULL UNIQUE,
  nome          VARCHAR(100) NOT NULL,
  categoria     VARCHAR(50),
  descricao     TEXT,
  versao        VARCHAR(20),
  icone         VARCHAR(50),
  cor           VARCHAR(10),
  preco         VARCHAR(30),
  ativo         TINYINT(1) DEFAULT 1,
  criado_em     DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cop_medico_plugins (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  medico_id     INT UNSIGNED NOT NULL,
  plugin_id     INT UNSIGNED NOT NULL,
  instalado_em  DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_medico_plugin (medico_id, plugin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Integrações
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS cop_integracoes (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  medico_id     INT UNSIGNED NOT NULL,
  tenant_id     INT UNSIGNED,
  nome          VARCHAR(100) NOT NULL,
  tipo          VARCHAR(50),
  protocolo     VARCHAR(30),
  url           VARCHAR(500),
  usuario       VARCHAR(100),
  senha_enc     VARCHAR(500),
  token_enc     VARCHAR(1000),
  status        ENUM('conectado','desconectado','erro') DEFAULT 'desconectado',
  ultima_sync   DATETIME,
  criado_em     DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_integ_medico (medico_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Plugins padrão do Marketplace
-- -------------------------------------------------------
INSERT IGNORE INTO cop_plugins (slug, nome, categoria, descricao, versao, icone, cor, preco) VALUES
('lung-ai',    'Lung AI',        'Diagnóstico',  'Detecção automática de nódulos pulmonares com classificação Lung-RADS.',  '2.1', 'fa-lungs',       '#0ea5e9', 'Incluído'),
('cardio-ai',  'Cardio AI',      'Diagnóstico',  'Análise de função ventricular e detecção de anomalias cardíacas em RM.',  '1.4', 'fa-heart-pulse',  '#ef4444', 'Incluído'),
('speech',     'Speech Engine',  'Produtividade','Ditado de laudos por voz com transcrição em tempo real.',                 '3.0', 'fa-microphone',   '#8b5cf6', 'Incluído'),
('research',   'Research AI',    'Pesquisa',     'Busca automática em PubMed, Radiopaedia e UpToDate.',                    '1.2', 'fa-book-medical', '#10b981', 'Incluído'),
('workflow',   'Workflow AI',    'Automação',    'Priorização inteligente de fila e alertas de SLA.',                      '1.0', 'fa-diagram-project','#f59e0b','Incluído'),
('neuro-ai',   'Neuro AI',       'Diagnóstico',  'Análise de RM de encéfalo com segmentação automática.',                  '1.0', 'fa-brain',        '#6366f1', 'Em breve'),
('mammo-ai',   'Mammo AI',       'Diagnóstico',  'Classificação BI-RADS automática em mamografias.',                      '1.0', 'fa-ribbon',       '#ec4899', 'Em breve');

-- -------------------------------------------------------
-- Adicionar coluna ia_config em cop_users se não existir
-- -------------------------------------------------------
ALTER TABLE cop_users ADD COLUMN IF NOT EXISTS ia_modelo VARCHAR(50) DEFAULT 'gpt-4o';
ALTER TABLE cop_users ADD COLUMN IF NOT EXISTS ia_temperatura DECIMAL(3,2) DEFAULT 0.30;
ALTER TABLE cop_users ADD COLUMN IF NOT EXISTS ia_estilo VARCHAR(30) DEFAULT 'formal';
ALTER TABLE cop_users ADD COLUMN IF NOT EXISTS ia_vocabulario TEXT;
ALTER TABLE cop_users ADD COLUMN IF NOT EXISTS assinatura_img TEXT;
