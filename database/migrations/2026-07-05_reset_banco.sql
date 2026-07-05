-- ============================================================
-- VOXEL Copilot — Reset do Banco (USE APENAS EM DEV/HOMOLOG)
-- 100% compatível com MariaDB 5.7 / MySQL 5.7
-- HostGator / cPanel / phpMyAdmin
--
-- ATENÇÃO: Este script APAGA TODOS OS DADOS.
-- Use apenas para recriar o banco do zero em ambiente
-- de desenvolvimento ou homologação.
--
-- Ordem de execução após este reset:
--   1. 2026-07-05_copilot_schema.sql
--   2. 2026-07-05_modulos_medico.sql
--   3. seeds/002_medico_teste.sql  (opcional)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `cop_integracoes`;
DROP TABLE IF EXISTS `cop_medico_plugins`;
DROP TABLE IF EXISTS `cop_plugins`;
DROP TABLE IF EXISTS `cop_pesquisa_historico`;
DROP TABLE IF EXISTS `cop_speech_historico`;
DROP TABLE IF EXISTS `cop_vision_analises`;
DROP TABLE IF EXISTS `cop_comparativos`;
DROP TABLE IF EXISTS `cop_pacientes`;
DROP TABLE IF EXISTS `cop_fila`;
DROP TABLE IF EXISTS `cop_audit_logs`;
DROP TABLE IF EXISTS `cop_autotextos`;
DROP TABLE IF EXISTS `cop_ia_conversas`;
DROP TABLE IF EXISTS `cop_laudos`;
DROP TABLE IF EXISTS `cop_workspaces`;
DROP TABLE IF EXISTS `cop_templates`;
DROP TABLE IF EXISTS `cop_medico_perfil`;
DROP TABLE IF EXISTS `cop_user_tenants`;
DROP TABLE IF EXISTS `cop_users`;
DROP TABLE IF EXISTS `cop_tenants`;
DROP TABLE IF EXISTS `cop_plans`;

SET FOREIGN_KEY_CHECKS = 1;

-- Confirmação
SELECT 'Reset concluido. Execute os migrations na ordem indicada.' AS status;
