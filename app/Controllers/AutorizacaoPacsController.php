<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Logger;
use App\Middlewares\AuthMiddleware;

/**
 * AutorizacaoPacsController
 *
 * Gerencia o vínculo entre médicos do VOXEL Copilot e unidades PACS externas.
 * Cada unidade cadastra o código e token do médico para autorizar a integração
 * e permitir o envio de laudos estruturados de volta ao PACS via DICOM SR / REST.
 */
class AutorizacaoPacsController extends Controller {

    // ─────────────────────────────────────────────────────────
    //  LISTAGEM — GET /configuracoes/autorizacao
    // ─────────────────────────────────────────────────────────
    public function index(): void {
        AuthMiddleware::handle();

        $pdo    = Database::getInstance();
        $userId = Auth::userId();

        // Busca todas as autorizações do médico logado com dados da unidade
        $stmt = $pdo->prepare("
            SELECT
                a.id,
                a.codigo_medico,
                a.token_integracao,
                a.status           AS auth_status,
                a.modalidades_permitidas,
                a.total_laudos,
                a.ultimo_laudo,
                a.data_ativacao,
                a.created_at,
                u.id               AS unidade_id,
                u.codigo_unidade,
                u.nome_instituicao,
                u.cnpj,
                u.cidade,
                u.estado,
                u.pacs_tipo,
                u.pacs_ae_title,
                u.status           AS unidade_status
            FROM cop_pacs_autorizacoes a
            JOIN cop_pacs_unidades u ON u.id = a.unidade_id
            WHERE a.medico_user_id = :uid
            ORDER BY a.created_at DESC
        ");
        $stmt->execute(['uid' => $userId]);
        $autorizacoes = $stmt->fetchAll(\PDO::FETCH_OBJ);

        // Estatísticas rápidas
        $total     = count($autorizacoes);
        $ativas    = 0;
        $pendentes = 0;
        $laudos    = 0;
        foreach ($autorizacoes as $a) {
            if ($a->auth_status === 'ativo')    $ativas++;
            if ($a->auth_status === 'pendente') $pendentes++;
            $laudos += (int)$a->total_laudos;
        }

        $this->view('configuracoes/autorizacao', [
            'title'        => 'Autorização PACS — VOXEL Copilot',
            'pageTitle'    => 'Configurações',
            'pageSubtitle' => 'Perfil, preferências e configurações de IA',
            'autorizacoes' => $autorizacoes,
            'stats'        => compact('total', 'ativas', 'pendentes', 'laudos'),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  CADASTRAR AUTORIZAÇÃO — POST /configuracoes/autorizacao/cadastrar
    //  O médico informa o código e token fornecidos pela unidade PACS.
    // ─────────────────────────────────────────────────────────
    public function cadastrar(): void {
        AuthMiddleware::handle();

        $pdo    = Database::getInstance();
        $userId = Auth::userId();

        $codigoMedico = trim($_POST['codigo_medico']    ?? '');
        $token        = trim($_POST['token_integracao'] ?? '');

        Logger::pacs('INFO', '[AutorizacaoPacsController::cadastrar] Tentativa de vincular unidade PACS', [
            'user_id'       => $userId,
            'codigo_medico' => $codigoMedico,
            'token_len'     => strlen($token),
            'ip'            => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        if (!$codigoMedico || !$token) {
            Logger::pacs('WARNING', '[AutorizacaoPacsController::cadastrar] Campos obrigatórios ausentes', [
                'user_id' => $userId,
                'codigo'  => $codigoMedico ?: '(vazio)',
                'token'   => $token ? '(preenchido)' : '(vazio)',
            ]);
            header('Location: /configuracoes?tab=autorizacao&erro=campos_obrigatorios');
            exit;
        }

        // Valida se o código+token corresponde a uma unidade cadastrada
        $stmt = $pdo->prepare("
            SELECT id, nome_instituicao, status
            FROM cop_pacs_unidades
            WHERE codigo_unidade = :codigo
            LIMIT 1
        ");
        $stmt->execute(['codigo' => $codigoMedico]);
        $unidade = $stmt->fetch(\PDO::FETCH_OBJ);

        if (!$unidade) {
            Logger::pacs('ERROR', '[AutorizacaoPacsController::cadastrar] Código de unidade NAO ENCONTRADO em cop_pacs_unidades', [
                'user_id'       => $userId,
                'codigo_medico' => $codigoMedico,
                'ip'            => $_SERVER['REMOTE_ADDR'] ?? '',
                'causa'         => 'O codigo gerado pelo VoxelPACS (bi_copilot_unidades) precisa ser cadastrado manualmente em cop_pacs_unidades do Copilot, OU o webhook do PACS precisa criar a unidade automaticamente',
            ]);
            header('Location: /configuracoes?tab=autorizacao&erro=unidade_nao_encontrada');
            exit;
        }

        if ($unidade->status === 'suspenso') {
            header('Location: /configuracoes?tab=autorizacao&erro=unidade_suspensa');
            exit;
        }

                // Verifica se já existe vínculo
        $stmt = $pdo->prepare("
            SELECT id, status FROM cop_pacs_autorizacoes
            WHERE unidade_id = :uid AND medico_user_id = :mid
            LIMIT 1
        ");
        $stmt->execute(['uid' => $unidade->id, 'mid' => $userId]);
        $existente = $stmt->fetch(\PDO::FETCH_OBJ);
        if ($existente) {
            // Permite re-vínculo se o status for revogado ou inativo
            // (médico revogou e quer vincular novamente com novo token)
            if ($existente->status === 'ativo' || $existente->status === 'pendente') {
                Logger::pacs('WARNING', '[AutorizacaoPacsController::cadastrar] Vínculo já ativo', [
                    'user_id'        => $userId,
                    'unidade_id'     => $unidade->id,
                    'autorizacao_id' => $existente->id,
                    'status'         => $existente->status,
                ]);
                header('Location: /configuracoes?tab=autorizacao&erro=ja_vinculado');
                exit;
            }
            // Status revogado ou inativo: reativa o vínculo com o novo token
            Logger::pacs('INFO', '[AutorizacaoPacsController::cadastrar] Reativando vínculo revogado/inativo', [
                'user_id'        => $userId,
                'unidade_id'     => $unidade->id,
                'autorizacao_id' => $existente->id,
                'status_anterior'=> $existente->status,
            ]);
            $pdo->prepare("
                UPDATE cop_pacs_autorizacoes SET
                    token_integracao   = :token,
                    status             = 'ativo',
                    motivo_revogacao   = NULL,
                    data_ativacao      = NOW(),
                    updated_at         = NOW()
                WHERE id = :id
            ")->execute(['token' => $token, 'id' => $existente->id]);
            Logger::pacs('INFO', '[AutorizacaoPacsController::cadastrar] Vínculo reativado com sucesso', [
                'user_id'        => $userId,
                'unidade_id'     => $unidade->id,
                'autorizacao_id' => $existente->id,
            ]);
            header('Location: /configuracoes?tab=autorizacao&sucesso=vinculado');
            exit;
        }

        // Busca dados do médico para preencher o vínculo
        $stmt = $pdo->prepare("SELECT name, crm, crm_uf, especialidades FROM cop_users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $userId]);
        $medico = $stmt->fetch(\PDO::FETCH_OBJ);

        // Gera código único do médico para esta unidade
        $codigoMedicoUnidade = 'MED-' . strtoupper(substr(md5($userId . $unidade->id . time()), 0, 12));

        $stmt = $pdo->prepare("
            INSERT INTO cop_pacs_autorizacoes
                (unidade_id, medico_user_id, codigo_medico, token_integracao,
                 medico_nome, medico_crm, medico_crm_uf, medico_especialidade,
                 status, data_ativacao, created_at, updated_at)
            VALUES
                (:unidade_id, :medico_id, :codigo, :token,
                 :nome, :crm, :crm_uf, :espec,
                 'ativo', NOW(), NOW(), NOW())
        ");
        $stmt->execute([
            'unidade_id' => $unidade->id,
            'medico_id'  => $userId,
            'codigo'     => $codigoMedicoUnidade,
            'token'      => $token,
            'nome'       => $medico->name ?? '',
            'crm'        => $medico->crm  ?? '',
            'crm_uf'     => $medico->crm_uf ?? '',
            'espec'      => $medico->especialidades ?? '',
        ]);

        $autorizacaoId = $pdo->lastInsertId();

        // Cria configuração DICOM padrão para o vínculo
        $this->criarDicomConfigPadrao($pdo, (int)$autorizacaoId, $unidade, $medico);

        // Log de auditoria
        $this->registrarLog($pdo, $unidade->id, (int)$autorizacaoId, $userId, 'autorizacao_criada', 'sucesso',
            'Médico vinculado à unidade ' . $unidade->nome_instituicao);

        header('Location: /configuracoes?tab=autorizacao&sucesso=vinculo_criado');
        exit;
    }

    // ─────────────────────────────────────────────────────────
    //  REVOGAR AUTORIZAÇÃO — POST /configuracoes/autorizacao/revogar
    // ─────────────────────────────────────────────────────────
    public function revogar(): void {
        AuthMiddleware::handle();

        $pdo    = Database::getInstance();
        $userId = Auth::userId();
        $authId = (int)($_POST['autorizacao_id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? 'Revogado pelo médico');

        if (!$authId) {
            header('Location: /configuracoes?tab=autorizacao&erro=id_invalido');
            exit;
        }

        // Garante que o vínculo pertence ao médico logado
        $stmt = $pdo->prepare("
            SELECT a.id, a.unidade_id FROM cop_pacs_autorizacoes a
            WHERE a.id = :id AND a.medico_user_id = :uid LIMIT 1
        ");
        $stmt->execute(['id' => $authId, 'uid' => $userId]);
        $auth = $stmt->fetch(\PDO::FETCH_OBJ);

        if (!$auth) {
            header('Location: /configuracoes?tab=autorizacao&erro=nao_autorizado');
            exit;
        }

        $pdo->prepare("
            UPDATE cop_pacs_autorizacoes
            SET status = 'revogado', motivo_revogacao = :motivo, updated_at = NOW()
            WHERE id = :id
        ")->execute(['motivo' => $motivo, 'id' => $authId]);

        $this->registrarLog($pdo, $auth->unidade_id, $authId, $userId, 'autorizacao_revogada', 'sucesso', $motivo);

        header('Location: /configuracoes?tab=autorizacao&sucesso=vinculo_revogado');
        exit;
    }

    // ─────────────────────────────────────────────────────────
    //  DETALHES / TAGS DICOM — GET /configuracoes/autorizacao/{id}
    // ─────────────────────────────────────────────────────────
    public function detalhe(): void {
        AuthMiddleware::handle();

        $pdo    = Database::getInstance();
        $userId = Auth::userId();
        $authId = (int)($this->params['id'] ?? 0);

        $stmt = $pdo->prepare("
            SELECT
                a.*,
                u.nome_instituicao, u.cnpj, u.cidade, u.estado,
                u.pacs_tipo, u.pacs_ae_title, u.pacs_host, u.pacs_port,
                u.pacs_wado_url, u.pacs_stow_url, u.pacs_qido_url,
                u.status AS unidade_status,
                d.formato_laudo, d.enviar_automatico, d.incluir_assinatura_img,
                d.incluir_qr_code, d.charset_dicom,
                d.tag_institution_name, d.tag_station_name,
                d.tag_referring_physician, d.tag_reading_physician,
                d.tag_sop_class_uid, d.tag_completion_flag, d.tag_verification_flag
            FROM cop_pacs_autorizacoes a
            JOIN cop_pacs_unidades u ON u.id = a.unidade_id
            LEFT JOIN cop_pacs_dicom_config d ON d.autorizacao_id = a.id
            WHERE a.id = :id AND a.medico_user_id = :uid
            LIMIT 1
        ");
        $stmt->execute(['id' => $authId, 'uid' => $userId]);
        $detalhe = $stmt->fetch(\PDO::FETCH_OBJ);

        if (!$detalhe) {
            header('Location: /configuracoes?tab=autorizacao&erro=nao_encontrado');
            exit;
        }

        // Log recente
        $stmt = $pdo->prepare("
            SELECT evento, status, detalhes, created_at
            FROM cop_pacs_audit_log
            WHERE autorizacao_id = :id
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute(['id' => $authId]);
        $logs = $stmt->fetchAll(\PDO::FETCH_OBJ);

        $this->view('configuracoes/autorizacao_detalhe', [
            'title'        => 'Detalhes da Autorização — VOXEL Copilot',
            'pageTitle'    => 'Configurações',
            'pageSubtitle' => 'Autorização PACS',
            'detalhe'      => $detalhe,
            'logs'         => $logs,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  API: VALIDAR TOKEN — POST /api/pacs/validar-token
    //  Chamado pelo PACS para verificar se o token é válido.
    // ─────────────────────────────────────────────────────────
    public function apiValidarToken(): void {
        header('Content-Type: application/json; charset=utf-8');

        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $token = trim($body['token'] ?? '');
        $pdo   = Database::getInstance();

        if (!$token) {
            echo json_encode(['ok' => false, 'erro' => 'token_ausente']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT
                a.id, a.codigo_medico, a.medico_nome, a.medico_crm, a.medico_crm_uf,
                a.medico_especialidade, a.modalidades_permitidas, a.status,
                a.token_expira_em,
                u.nome_instituicao, u.cnpj, u.pacs_ae_title
            FROM cop_pacs_autorizacoes a
            JOIN cop_pacs_unidades u ON u.id = a.unidade_id
            WHERE a.token_integracao = :token
            LIMIT 1
        ");
        $stmt->execute(['token' => $token]);
        $auth = $stmt->fetch(\PDO::FETCH_OBJ);

        if (!$auth) {
            echo json_encode(['ok' => false, 'erro' => 'token_invalido']);
            exit;
        }

        if ($auth->status !== 'ativo') {
            echo json_encode(['ok' => false, 'erro' => 'autorizacao_' . $auth->status]);
            exit;
        }

        if ($auth->token_expira_em && strtotime($auth->token_expira_em) < time()) {
            echo json_encode(['ok' => false, 'erro' => 'token_expirado']);
            exit;
        }

        // Registra uso
        $this->registrarLog($pdo, 0, $auth->id, 0, 'token_validado', 'sucesso',
            'Token validado pelo PACS ' . $auth->pacs_ae_title);

        echo json_encode([
            'ok'     => true,
            'medico' => [
                'codigo'        => $auth->codigo_medico,
                'nome'          => $auth->medico_nome,
                'crm'           => $auth->medico_crm . '/' . $auth->medico_crm_uf,
                'especialidade' => $auth->medico_especialidade,
                'modalidades'   => $auth->modalidades_permitidas ?: 'todas',
            ],
            'unidade' => [
                'nome' => $auth->nome_instituicao,
                'cnpj' => $auth->cnpj,
            ],
        ]);
        exit;
    }

    // ─────────────────────────────────────────────────────────
    //  API: REGISTRAR UNIDADE — POST /api/pacs/registrar-unidade
    //  Chamado automaticamente pelo VoxelPACS ao gerar um token.
    //  Cria ou atualiza a entrada em cop_pacs_unidades para que
    //  o médico possa vincular usando o código gerado pelo PACS.
    // ─────────────────────────────────────────────────────────
    public function apiRegistrarUnidade(): void {
        header('Content-Type: application/json; charset=utf-8');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $pdo  = Database::getInstance();

        // Campos obrigatórios
        $codigoUnidade = trim($body['codigo_unidade'] ?? '');
        $chaveSecreta  = trim($body['chave_secreta']  ?? '');

        Logger::pacs('INFO', '[AutorizacaoPacsController::apiRegistrarUnidade] Recebida requisição de registro', [
            'codigo_unidade'  => $codigoUnidade,
            'chave_len'       => strlen($chaveSecreta),
            'ip'              => $_SERVER['REMOTE_ADDR'] ?? '',
            'payload_keys'    => array_keys($body),
        ]);

        if (!$codigoUnidade || !$chaveSecreta) {
            Logger::pacs('ERROR', '[AutorizacaoPacsController::apiRegistrarUnidade] Campos obrigatórios ausentes', [
                'codigo_unidade' => $codigoUnidade ?: '(vazio)',
                'chave_secreta'  => $chaveSecreta  ? '(preenchido)' : '(vazio)',
            ]);
            http_response_code(400);
            echo json_encode(['ok' => false, 'erro' => 'codigo_unidade e chave_secreta são obrigatórios']);
            exit;
        }

        // Dados opcionais da unidade
        $nomeInstituicao = trim($body['nome_instituicao'] ?? '');
        $cnpj            = preg_replace('/\D/', '', $body['cnpj'] ?? '');
        $cidade          = trim($body['cidade']           ?? '');
        $estado          = strtoupper(trim($body['estado'] ?? ''));
        $telefone        = trim($body['telefone']         ?? '');
        $emailContato    = trim($body['email_contato']    ?? '');
        $pacsWebhookUrl  = trim($body['pacs_webhook_url'] ?? '');
        $pacsApiToken    = trim($body['pacs_api_token']   ?? '');
        $pacsTipo        = trim($body['pacs_tipo']        ?? 'VoxelPACS');

        try {
            // Verifica se já existe
            $stmt = $pdo->prepare("
                SELECT id, copilot_api_token FROM cop_pacs_unidades
                WHERE codigo_unidade = :codigo LIMIT 1
            ");
            $stmt->execute(['codigo' => $codigoUnidade]);
            $existente = $stmt->fetch(\PDO::FETCH_OBJ);
            $existenteId = $existente ? (int)$existente->id : null;

            // Gera ou reutiliza o copilot_api_token (token que o PACS usa no Bearer)
            // Este token é gerado pelo Copilot e retornado ao PACS para autenticar webhooks
            $copilotApiToken = ($existente && $existente->copilot_api_token)
                ? $existente->copilot_api_token
                : 'COPILOT-' . strtoupper(bin2hex(random_bytes(20)));

            if ($existenteId) {
                // Atualiza dados se a unidade já foi registrada
                $pdo->prepare("
                    UPDATE cop_pacs_unidades SET
                        chave_secreta      = :chave,
                        nome_instituicao   = COALESCE(NULLIF(:nome,''), nome_instituicao),
                        cnpj               = COALESCE(NULLIF(:cnpj,''), cnpj),
                        cidade             = COALESCE(NULLIF(:cidade,''), cidade),
                        estado             = COALESCE(NULLIF(:estado,''), estado),
                        telefone           = COALESCE(NULLIF(:tel,''), telefone),
                        email_contato      = COALESCE(NULLIF(:email,''), email_contato),
                        pacs_webhook_url   = COALESCE(NULLIF(:webhook,''), pacs_webhook_url),
                        pacs_api_token     = COALESCE(NULLIF(:api_token,''), pacs_api_token),
                        pacs_tipo          = COALESCE(NULLIF(:tipo,''), pacs_tipo),
                        copilot_api_token  = :copilot_token,
                        updated_at         = NOW()
                    WHERE codigo_unidade = :codigo
                ")->execute([
                    'chave'          => $chaveSecreta,
                    'nome'           => $nomeInstituicao,
                    'cnpj'           => $cnpj,
                    'cidade'         => $cidade,
                    'estado'         => $estado,
                    'tel'            => $telefone,
                    'email'          => $emailContato,
                    'webhook'        => $pacsWebhookUrl,
                    'api_token'      => $pacsApiToken,
                    'tipo'           => $pacsTipo,
                    'copilot_token'  => $copilotApiToken,
                    'codigo'         => $codigoUnidade,
                ]);

                Logger::pacs('INFO', '[AutorizacaoPacsController::apiRegistrarUnidade] Unidade atualizada', [
                    'unidade_id'     => $existenteId,
                    'codigo_unidade' => $codigoUnidade,
                ]);

                echo json_encode([
                    'ok'               => true,
                    'acao'             => 'atualizada',
                    'codigo_unidade'   => $codigoUnidade,
                    'unidade_id'       => $existenteId,
                    'copilot_api_token'=> $copilotApiToken,
                    'msg'              => 'Use copilot_api_token como Bearer em todos os webhooks enviados ao Copilot.',
                ]);
                exit;
            }

            // Cria nova entrada
            $pdo->prepare("
                INSERT INTO cop_pacs_unidades
                    (codigo_unidade, chave_secreta, nome_instituicao, cnpj,
                     cidade, estado, telefone, email_contato,
                     pacs_webhook_url, pacs_api_token, pacs_tipo,
                     copilot_api_token,
                     status, created_at, updated_at)
                VALUES
                    (:codigo, :chave, :nome, :cnpj,
                     :cidade, :estado, :tel, :email,
                     :webhook, :api_token, :tipo,
                     :copilot_token,
                     'pendente', NOW(), NOW())
            ")->execute([
                'codigo'         => $codigoUnidade,
                'chave'          => $chaveSecreta,
                'nome'           => $nomeInstituicao ?: null,
                'cnpj'           => $cnpj            ?: null,
                'cidade'         => $cidade          ?: null,
                'estado'         => $estado          ?: null,
                'tel'            => $telefone        ?: null,
                'email'          => $emailContato    ?: null,
                'webhook'        => $pacsWebhookUrl  ?: null,
                'api_token'      => $pacsApiToken    ?: null,
                'tipo'           => $pacsTipo,
                'copilot_token'  => $copilotApiToken,
            ]);

            $novaId = (int) $pdo->lastInsertId();

            Logger::pacs('INFO', '[AutorizacaoPacsController::apiRegistrarUnidade] Nova unidade criada em cop_pacs_unidades', [
                'unidade_id'     => $novaId,
                'codigo_unidade' => $codigoUnidade,
                'nome'           => $nomeInstituicao,
                'status'         => 'pendente',
            ]);

            echo json_encode([
                'ok'               => true,
                'acao'             => 'criada',
                'codigo_unidade'   => $codigoUnidade,
                'unidade_id'       => $novaId,
                'status'           => 'pendente',
                'copilot_api_token'=> $copilotApiToken,
                'msg'              => 'Unidade registrada. Use copilot_api_token como Bearer em todos os webhooks enviados ao Copilot.',
            ]);
            exit;

        } catch (\Throwable $e) {
            Logger::pacs('ERROR', '[AutorizacaoPacsController::apiRegistrarUnidade] Erro ao registrar unidade', [
                'codigo_unidade' => $codigoUnidade,
                'erro'           => $e->getMessage(),
                'arquivo'        => $e->getFile() . ':' . $e->getLine(),
            ]);
            http_response_code(500);
            echo json_encode(['ok' => false, 'erro' => 'Erro interno: ' . $e->getMessage()]);
            exit;
        }
    }

    // ─────────────────────────────────────────────────────────
    //  HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────

    /** Cria a configuração DICOM padrão para um novo vínculo */
    private function criarDicomConfigPadrao(\PDO $pdo, int $autorizacaoId, object $unidade, object $medico): void {
        $nomeMedicoDicom = $this->formatarNomeDicom($medico->name ?? '');
        $nomeInstDicom   = $unidade->nome_instituicao ?? '';

        $pdo->prepare("
            INSERT INTO cop_pacs_dicom_config
                (autorizacao_id, tag_institution_name, tag_reading_physician,
                 tag_referring_physician, tag_sop_class_uid,
                 tag_completion_flag, tag_verification_flag,
                 formato_laudo, enviar_automatico, incluir_assinatura_img,
                 charset_dicom, created_at, updated_at)
            VALUES
                (:auth_id, :inst, :reading, :referring,
                 '1.2.840.10008.5.1.4.1.1.88.33',
                 'COMPLETE', 'VERIFIED',
                 'SR_DICOM', 0, 1,
                 'ISO_IR 192', NOW(), NOW())
        ")->execute([
            'auth_id'   => $autorizacaoId,
            'inst'      => $nomeInstDicom,
            'reading'   => $nomeMedicoDicom,
            'referring' => $nomeMedicoDicom,
        ]);
    }

    /**
     * Converte nome para formato DICOM: "Sobrenome^Nome^Meio^Prefixo^Sufixo"
     * Ex: "Dr. João Silva" → "Silva^João^^^Dr."
     */
    private function formatarNomeDicom(string $nome): string {
        $partes = explode(' ', trim($nome));
        if (count($partes) < 2) return $nome;
        $sobrenome = array_pop($partes);
        $primeiro  = $partes[0] ?? '';
        return $sobrenome . '^' . $primeiro;
    }

    /** Registra evento no log de auditoria */
    private function registrarLog(
        \PDO   $pdo,
        int    $unidadeId,
        int    $autorizacaoId,
        int    $medicoId,
        string $evento,
        string $status,
        string $detalhes = ''
    ): void {
        try {
            $pdo->prepare("
                INSERT INTO cop_pacs_audit_log
                    (unidade_id, autorizacao_id, medico_user_id, evento, status, detalhes, ip, created_at)
                VALUES
                    (:uid, :aid, :mid, :evento, :status, :det, :ip, NOW())
            ")->execute([
                'uid'    => $unidadeId,
                'aid'    => $autorizacaoId,
                'mid'    => $medicoId,
                'evento' => $evento,
                'status' => $status,
                'det'    => $detalhes,
                'ip'     => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch (\Exception $e) {
            // Log silencioso — não interrompe o fluxo principal
        }
    }
}
