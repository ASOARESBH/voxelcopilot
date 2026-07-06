-- ============================================================
-- VOXEL Copilot — Migration complementar
-- Adiciona colunas de diagnóstico completo à cop_ai_provider_tests
-- Compatível: MariaDB 5.7 / MySQL 5.7 / phpMyAdmin / Hostgator
-- Data: 2026-07-05
-- ============================================================

ALTER TABLE cop_ai_provider_tests

  -- Endpoint e modelo efetivamente usados no teste
  ADD COLUMN endpoint_usado      VARCHAR(500)  DEFAULT NULL AFTER model_id,
  ADD COLUMN modelo_enviado      VARCHAR(100)  DEFAULT NULL AFTER endpoint_usado,

  -- Payload enviado (com API Key mascarada) e resposta bruta do provider
  ADD COLUMN payload_json        MEDIUMTEXT    DEFAULT NULL AFTER modelo_enviado,
  ADD COLUMN resposta_raw        MEDIUMTEXT    DEFAULT NULL AFTER payload_json,

  -- Métricas da requisição
  ADD COLUMN tokens_solicitados  INT           DEFAULT NULL AFTER resposta_raw,
  ADD COLUMN prompt_chars        INT           DEFAULT NULL AFTER tokens_solicitados,
  ADD COLUMN http_status         SMALLINT      DEFAULT NULL AFTER prompt_chars,

  -- Diagnóstico estruturado do erro (campos extraídos da resposta do provider)
  ADD COLUMN erro_tipo           VARCHAR(100)  DEFAULT NULL AFTER http_status,
  ADD COLUMN erro_code           VARCHAR(100)  DEFAULT NULL AFTER erro_tipo,
  ADD COLUMN erro_param          VARCHAR(100)  DEFAULT NULL AFTER erro_code,
  ADD COLUMN erro_mensagem       TEXT          DEFAULT NULL AFTER erro_param,
  ADD COLUMN erro_categoria      ENUM('ok','rate_limit','quota_insuficiente','auth_invalida','modelo_invalido','endpoint_invalido','timeout','outro')
                                               DEFAULT 'ok' AFTER erro_mensagem,

  -- Orientação gerada para o usuário
  ADD COLUMN orientacao          TEXT          DEFAULT NULL AFTER erro_categoria,

  -- Resposta bem-sucedida (texto gerado)
  ADD COLUMN resposta_texto      TEXT          DEFAULT NULL AFTER orientacao,
  ADD COLUMN tokens_usados       INT           DEFAULT NULL AFTER resposta_texto;
