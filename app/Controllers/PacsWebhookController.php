<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Logger;

/**
 * PacsWebhookController
 *
 * Recebe eventos enviados pelo VoxelPACS para o VOXEL Copilot.
 *
 * Rotas (web.php):
 *   POST /api/pacs/webhook/evento
 *
 * Autenticação: Bearer token no header Authorization (copilot_api_token da unidade).
 * Assinatura:   Header X-Copilot-Signature: sha256=<hmac> (chave_secreta da unidade).
 *
 * Eventos suportados:
 *   estudo.assumido  → cria workspace + laudo rascunho no Copilot
 *   estudo.aberto    → atualiza status do workspace para em_laudo
 *   estudo.liberado  → marca laudo como liberado pelo PACS
 */
class PacsWebhookController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/pacs/webhook/evento
    // ─────────────────────────────────────────────────────────────────────────
    public function evento(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // 1. Extrai Bearer token
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = '';
        if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $m)) {
            $token = trim($m[1]);
        }
        if (!$token) {
            $token = trim($_GET['token'] ?? '');
        }
        if (!$token) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'erro' => 'token_ausente']);
            return;
        }

        // 2. Valida token contra cop_pacs_autorizacoes
        $pdo  = Database::getInstance();
        $auth = $this->validarToken($pdo, $token);
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'erro' => 'token_invalido']);
            return;
        }

        // 3. Lê payload
        $rawBody = file_get_contents('php://input');
        $input   = json_decode($rawBody, true) ?? [];
        $evento  = $input['evento'] ?? '';

        // 4. Valida assinatura HMAC (se chave_secreta disponível)
        $assinatura = $_SERVER['HTTP_X_COPILOT_SIGNATURE'] ?? '';
        if ($auth->chave_secreta && $assinatura) {
            $esperada = 'sha256=' . hash_hmac('sha256', $rawBody, $auth->chave_secreta);
            if (!hash_equals($esperada, $assinatura)) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'erro' => 'assinatura_invalida']);
                return;
            }
        }

        // 5. Despacha para o handler correto
        $result = match ($evento) {
            'estudo.assumido' => $this->handleEstudoAssumido($pdo, $auth, $input),
            'estudo.aberto'   => $this->handleEstudoAberto($pdo, $auth, $input),
            'estudo.liberado' => $this->handleEstudoLiberado($pdo, $auth, $input),
            default           => ['ok' => true, 'msg' => "Evento '{$evento}' recebido (sem ação específica)."],
        };

        // 6. Registra no log
        $this->registrarLog($pdo, $auth, $evento, $rawBody, json_encode($result));

        echo json_encode($result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HANDLER: estudo.assumido
    // Cria workspace + laudo rascunho no Copilot para o médico.
    // ─────────────────────────────────────────────────────────────────────────
    private function handleEstudoAssumido(\PDO $pdo, object $auth, array $input): array
    {
        $estudo    = $input['estudo']  ?? [];
        $medico    = $input['medico']  ?? [];
        $studyUid  = trim($estudo['study_instance_uid'] ?? '');
        $medicoToken = trim($input['_meta']['medico_token'] ?? '');

        if (!$studyUid) {
            return ['ok' => false, 'erro' => 'study_instance_uid_obrigatorio'];
        }

        // Resolve o user_id do médico via token de integração
        $userId = $auth->medico_user_id;
        if ($medicoToken) {
            $stmtMed = $pdo->prepare("
                SELECT medico_user_id FROM cop_pacs_autorizacoes
                WHERE token_integracao = :tok AND unidade_id = :uid AND status = 'ativo' LIMIT 1
            ");
            $stmtMed->execute(['tok' => $medicoToken, 'uid' => $auth->unidade_id]);
            $row = $stmtMed->fetch(\PDO::FETCH_OBJ);
            if ($row) $userId = $row->medico_user_id;
        }

        try {
            // Verifica se já existe worklist entry para este estudo + médico
            $stmtCheck = $pdo->prepare("
                SELECT id, workspace_id, laudo_id, status FROM cop_pacs_worklist
                WHERE study_instance_uid = :uid AND user_id = :mid LIMIT 1
            ");
            $stmtCheck->execute(['uid' => $studyUid, 'mid' => $userId]);
            $existente = $stmtCheck->fetch(\PDO::FETCH_OBJ);

            if ($existente && $existente->workspace_id) {
                // Já existe — apenas atualiza status
                $pdo->prepare("
                    UPDATE cop_pacs_worklist SET status = 'aguardando', updated_at = NOW()
                    WHERE id = :id
                ")->execute(['id' => $existente->id]);
                return [
                    'ok'          => true,
                    'msg'         => 'Estudo já existe no Copilot.',
                    'workspace_id'=> $existente->workspace_id,
                    'laudo_id'    => $existente->laudo_id,
                ];
            }

            // Cria workspace
            $patientNome = trim($estudo['patient_name'] ?? '');
            $modalidade  = trim($estudo['modalities']   ?? '');
            $studyDate   = $estudo['study_date'] ?? null;
            $studyDesc   = trim($estudo['study_description'] ?? '');

            $pdo->prepare("
                INSERT INTO cop_workspaces
                    (tenant_id, medico_id, study_uid, patient_uid, patient_nome, modalidade, status, assumido_em, created_at, updated_at)
                VALUES
                    (:tid, :mid, :study_uid, :patient_uid, :patient_nome, :modalidade, 'aberto', NOW(), NOW(), NOW())
            ")->execute([
                'tid'          => $auth->tenant_id,
                'mid'          => $userId,
                'study_uid'    => $studyUid,
                'patient_uid'  => $estudo['patient_id'] ?? null,
                'patient_nome' => $patientNome ?: 'Paciente PACS',
                'modalidade'   => $modalidade ?: null,
            ]);
            $workspaceId = (int) $pdo->lastInsertId();

            // Cria laudo rascunho
            $pdo->prepare("
                INSERT INTO cop_laudos
                    (workspace_id, tenant_id, medico_id, versao, achados, status, created_at, updated_at)
                VALUES
                    (:wid, :tid, :mid, 1, '', 'rascunho', NOW(), NOW())
            ")->execute([
                'wid' => $workspaceId,
                'tid' => $auth->tenant_id,
                'mid' => $userId,
            ]);
            $laudoId = (int) $pdo->lastInsertId();

            // Insere na worklist do Copilot
            if ($existente) {
                $pdo->prepare("
                    UPDATE cop_pacs_worklist SET
                        workspace_id  = :wid,
                        laudo_id      = :lid,
                        status        = 'aguardando',
                        assumido_em   = NOW(),
                        updated_at    = NOW()
                    WHERE id = :id
                ")->execute(['wid' => $workspaceId, 'lid' => $laudoId, 'id' => $existente->id]);
            } else {
                $pdo->prepare("
                    INSERT INTO cop_pacs_worklist
                        (autorizacao_id, user_id, study_instance_uid, accession_number,
                         pacs_estudo_id, patient_nome, patient_id, patient_birth_date, patient_sex,
                         modalidade, study_date, study_description, institution_name,
                         num_series, num_instances, prioridade,
                         medico_nome, medico_crm, medico_especialidade,
                         workspace_id, laudo_id, status, assumido_em, created_at, updated_at)
                    VALUES
                        (:auth_id, :uid, :study_uid, :acc_num,
                         :pacs_id, :patient_nome, :patient_id, :birth_date, :sex,
                         :modalidade, :study_date, :study_desc, :institution,
                         :num_series, :num_instances, :prioridade,
                         :med_nome, :med_crm, :med_esp,
                         :wid, :lid, 'aguardando', NOW(), NOW(), NOW())
                ")->execute([
                    'auth_id'      => $auth->id,
                    'uid'          => $userId,
                    'study_uid'    => $studyUid,
                    'acc_num'      => $estudo['accession_number']   ?? null,
                    'pacs_id'      => $estudo['id']                 ?? null,
                    'patient_nome' => $patientNome ?: null,
                    'patient_id'   => $estudo['patient_id']         ?? null,
                    'birth_date'   => $estudo['patient_birth_date'] ?? null,
                    'sex'          => $estudo['patient_sex']        ?? null,
                    'modalidade'   => $modalidade ?: null,
                    'study_date'   => $studyDate,
                    'study_desc'   => $studyDesc ?: null,
                    'institution'  => $estudo['institution_name']   ?? null,
                    'num_series'   => (int)($estudo['num_series']   ?? 0),
                    'num_instances'=> (int)($estudo['num_instances'] ?? 0),
                    'prioridade'   => $estudo['prioridade']         ?? 'normal',
                    'med_nome'     => $medico['nome']               ?? null,
                    'med_crm'      => $medico['crm']                ?? null,
                    'med_esp'      => $medico['especialidade']      ?? null,
                    'wid'          => $workspaceId,
                    'lid'          => $laudoId,
                ]);
            }

            Logger::info("[PacsWebhookController::handleEstudoAssumido] study_uid={$studyUid} workspace_id={$workspaceId} laudo_id={$laudoId}");

            return [
                'ok'           => true,
                'msg'          => 'Workspace e laudo criados no Copilot.',
                'workspace_id' => $workspaceId,
                'laudo_id'     => $laudoId,
                'url_laudo'    => '/workspace/' . $laudoId,
            ];
        } catch (\Throwable $e) {
            Logger::error('[PacsWebhookController::handleEstudoAssumido] ' . $e->getMessage());
            return ['ok' => false, 'erro' => $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HANDLER: estudo.aberto (médico abriu o viewer no PACS)
    // ─────────────────────────────────────────────────────────────────────────
    private function handleEstudoAberto(\PDO $pdo, object $auth, array $input): array
    {
        $studyUid = trim($input['estudo']['study_instance_uid'] ?? '');
        if (!$studyUid) return ['ok' => false, 'erro' => 'study_uid_obrigatorio'];

        try {
            $stmt = $pdo->prepare("
                SELECT id, workspace_id FROM cop_pacs_worklist
                WHERE study_instance_uid = :uid AND autorizacao_id = :aid LIMIT 1
            ");
            $stmt->execute(['uid' => $studyUid, 'aid' => $auth->id]);
            $wl = $stmt->fetch(\PDO::FETCH_OBJ);

            if ($wl && $wl->workspace_id) {
                $pdo->prepare("
                    UPDATE cop_workspaces SET status = 'em_laudo', updated_at = NOW()
                    WHERE id = :id
                ")->execute(['id' => $wl->workspace_id]);
                $pdo->prepare("
                    UPDATE cop_pacs_worklist SET status = 'em_laudo', updated_at = NOW()
                    WHERE id = :id
                ")->execute(['id' => $wl->id]);
            }
            return ['ok' => true, 'msg' => 'Status atualizado para em_laudo.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'erro' => $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HANDLER: estudo.liberado (laudo liberado no PACS)
    // ─────────────────────────────────────────────────────────────────────────
    private function handleEstudoLiberado(\PDO $pdo, object $auth, array $input): array
    {
        $studyUid = trim($input['estudo']['study_instance_uid'] ?? '');
        if (!$studyUid) return ['ok' => false, 'erro' => 'study_uid_obrigatorio'];

        try {
            $stmt = $pdo->prepare("
                SELECT id, laudo_id FROM cop_pacs_worklist
                WHERE study_instance_uid = :uid AND autorizacao_id = :aid LIMIT 1
            ");
            $stmt->execute(['uid' => $studyUid, 'aid' => $auth->id]);
            $wl = $stmt->fetch(\PDO::FETCH_OBJ);

            if ($wl) {
                $pdo->prepare("
                    UPDATE cop_pacs_worklist SET status = 'enviado', updated_at = NOW()
                    WHERE id = :id
                ")->execute(['id' => $wl->id]);
            }
            return ['ok' => true, 'msg' => 'Estudo marcado como liberado.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'erro' => $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Valida o Bearer token contra cop_pacs_autorizacoes.
     * Retorna o objeto de autorização ou null.
     */
    private function validarToken(\PDO $pdo, string $token): ?\stdClass
    {
        try {
            $stmt = $pdo->prepare("
                SELECT
                    a.id, a.unidade_id, a.medico_user_id, a.status,
                    a.token_expira_em, a.modalidades_permitidas,
                    a.medico_nome, a.medico_crm, a.medico_crm_uf,
                    u.codigo_unidade, u.chave_secreta,
                    COALESCE(a.tenant_id, u.tenant_id) AS tenant_id
                FROM cop_pacs_autorizacoes a
                JOIN cop_pacs_unidades u ON u.id = a.unidade_id
                WHERE a.token_integracao = :tok AND a.status = 'ativo'
                LIMIT 1
            ");
            $stmt->execute(['tok' => $token]);
            $auth = $stmt->fetch(\PDO::FETCH_OBJ);
            if (!$auth) return null;
            if ($auth->token_expira_em && strtotime($auth->token_expira_em) < time()) return null;
            return $auth;
        } catch (\Throwable $e) {
            Logger::error('[PacsWebhookController::validarToken] ' . $e->getMessage());
            return null;
        }
    }

    private function registrarLog(\PDO $pdo, object $auth, string $evento, string $payload, string $resposta): void
    {
        try {
            $pdo->prepare("
                INSERT INTO cop_pacs_sync_log
                    (autorizacao_id, user_id, evento, direcao, status, payload_json, resposta_json, ip, created_at)
                VALUES
                    (:aid, :uid, :evento, 'pacs_para_copilot', 'sucesso', :payload, :resp, :ip, NOW())
            ")->execute([
                'aid'     => $auth->id,
                'uid'     => $auth->medico_user_id,
                'evento'  => $evento,
                'payload' => substr($payload, 0, 4000),
                'resp'    => substr($resposta, 0, 2000),
                'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch (\Throwable $e) {
            // Silencioso
        }
    }
}
