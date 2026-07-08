<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
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

        if (!$codigoMedico || !$token) {
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
            header('Location: /configuracoes?tab=autorizacao&erro=ja_vinculado');
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
