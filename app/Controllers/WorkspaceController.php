<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Middlewares\TenantMiddleware;
use App\Services\PacsService;
use App\Services\CopilotAIService;

class WorkspaceController extends Controller {

    /**
     * Retorna WHERE clause e params compatíveis com modo standalone (sem tenant).
     * Em modo standalone, filtra apenas por medico_id.
     */
    private function buildOwnerFilter(int $medicoId, ?int $tenantId, string $alias = 'l'): array {
        if ($tenantId) {
            return [
                'where'  => "{$alias}.medico_id = :mid AND {$alias}.tenant_id = :tid",
                'params' => ['mid' => $medicoId, 'tid' => $tenantId],
            ];
        }
        return [
            'where'  => "{$alias}.medico_id = :mid",
            'params' => ['mid' => $medicoId],
        ];
    }

    public function index(): void {
        TenantMiddleware::handle();
        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId();

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $offset  = ($page - 1) * $perPage;
        $status  = $_GET['status'] ?? '';
        $busca   = trim($_GET['busca'] ?? '');

        $base   = $this->buildOwnerFilter($medicoId, $tenantId, 'l');
        $where  = [$base['where']];
        $params = $base['params'];

        if ($status) { $where[] = "l.status = :status"; $params['status'] = $status; }
        if ($busca)  { $where[] = "(w.patient_nome LIKE :busca OR w.study_uid LIKE :busca2)"; $params['busca'] = "%{$busca}%"; $params['busca2'] = "%{$busca}%"; }

        $whereStr = implode(' AND ', $where);

        $total = $pdo->prepare("SELECT COUNT(*) FROM cop_laudos l JOIN cop_workspaces w ON w.id = l.workspace_id WHERE {$whereStr}");
        $total->execute($params);
        $total = (int) $total->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT l.id, l.status, l.versao, l.created_at, l.assinado_em,
                   w.patient_nome, w.modalidade, w.study_uid, w.patient_uid
            FROM cop_laudos l
            JOIN cop_workspaces w ON w.id = l.workspace_id
            WHERE {$whereStr}
            ORDER BY l.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $laudos = $stmt->fetchAll();

        $this->view('workspace/index', [
            'title'        => 'Laudos — VOXEL Copilot',
            'pageTitle'    => 'Workspace de Laudos',
            'pageSubtitle' => 'Gerencie seus laudos',
            'laudos'       => $laudos,
            'total'        => $total,
            'page'         => $page,
            'perPage'      => $perPage,
            'totalPages'   => (int) ceil($total / $perPage),
            'status'       => $status,
            'busca'        => $busca,
        ]);
    }

    public function novo(): void {
        TenantMiddleware::handle();
        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId() ?: null;

        // Templates disponíveis (por tenant ou por médico em modo standalone)
        $templates = [];
        try {
            if ($tenantId) {
                $tplStmt = $pdo->prepare("SELECT id, nome, modalidade, especialidade FROM cop_templates WHERE tenant_id = :tid AND ativo = 1 ORDER BY uso_count DESC, nome ASC");
                $tplStmt->execute(['tid' => $tenantId]);
            } else {
                $tplStmt = $pdo->prepare("SELECT id, nome, modalidade, especialidade FROM cop_templates WHERE (user_id = :uid OR tenant_id IS NULL) AND ativo = 1 ORDER BY uso_count DESC, nome ASC");
                $tplStmt->execute(['uid' => $medicoId]);
            }
            $templates = $tplStmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('[WorkspaceController::novo] templates: ' . $e->getMessage());
        }

        // Config PACS
        $pacsConfig = null;
        if ($tenantId) {
            $pacsStmt = $pdo->prepare("SELECT pacs_api_url, pacs_api_token FROM cop_tenants WHERE id = :id LIMIT 1");
            $pacsStmt->execute(['id' => $tenantId]);
            $pacsConfig = $pacsStmt->fetch();
        }

        $this->view('workspace/novo', [
            'title'        => 'Novo Laudo — VOXEL Copilot',
            'pageTitle'    => 'Novo Laudo',
            'pageSubtitle' => 'Assistido por IA',
            'templates'    => $templates,
            'pacsConfig'   => $pacsConfig,
            'csrf_token'   => $this->csrfToken(),
        ]);
    }

    public function criar(): void {
        TenantMiddleware::handle();
        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId() ?: null; // NULL explícito para modo standalone

        $studyUid    = trim($_POST['study_uid']    ?? '');
        $patientNome = trim($_POST['patient_nome'] ?? '');
        $patientUid  = trim($_POST['patient_uid']  ?? '');
        $modalidade  = trim($_POST['modalidade']   ?? '');
        $templateId  = (int)($_POST['template_id'] ?? 0);

        if (!$studyUid) {
            $this->redirect('/workspace/novo?erro=study_uid_obrigatorio');
        }

        try {
            // Cria workspace
            $pdo->prepare("
                INSERT INTO cop_workspaces (tenant_id, medico_id, study_uid, patient_uid, patient_nome, modalidade, status, assumido_em, created_at, updated_at)
                VALUES (:tid, :mid, :study_uid, :patient_uid, :patient_nome, :modalidade, 'aberto', NOW(), NOW(), NOW())
            ")->execute([
                'tid'          => $tenantId,
                'mid'          => $medicoId,
                'study_uid'    => $studyUid,
                'patient_uid'  => $patientUid ?: null,
                'patient_nome' => $patientNome ?: null,
                'modalidade'   => $modalidade ?: null,
            ]);
            $workspaceId = (int) $pdo->lastInsertId();

            // Carrega template se selecionado
            $corpo = '';
            if ($templateId) {
                $tpl = $pdo->prepare("SELECT corpo FROM cop_templates WHERE id = :id LIMIT 1");
                $tpl->execute(['id' => $templateId]);
                $tpl = $tpl->fetch();
                if ($tpl) {
                    $corpo = $tpl->corpo ?? '';
                    $pdo->prepare("UPDATE cop_templates SET uso_count = uso_count + 1 WHERE id = :id")->execute(['id' => $templateId]);
                }
            }

            // Cria laudo em rascunho
            $pdo->prepare("
                INSERT INTO cop_laudos (workspace_id, tenant_id, medico_id, versao, achados, status, created_at, updated_at)
                VALUES (:wid, :tid, :mid, 1, :achados, 'rascunho', NOW(), NOW())
            ")->execute([
                'wid'     => $workspaceId,
                'tid'     => $tenantId,
                'mid'     => $medicoId,
                'achados' => $corpo,
            ]);
            $laudoId = (int) $pdo->lastInsertId();

            $this->redirect("/workspace/{$laudoId}");

        } catch (\PDOException $e) {
            // Log detalhado para diagnóstico
            error_log('[WorkspaceController::criar] PDOException: ' . $e->getMessage()
                . ' | tenant_id=' . var_export($tenantId, true)
                . ' | medico_id=' . $medicoId
                . ' | study_uid=' . $studyUid
            );
            $this->redirect('/workspace/novo?erro=db_error&msg=' . urlencode($e->getMessage()));
        }
    }

    public function show(int $id): void {
        TenantMiddleware::handle();
        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId() ?: null;

        // Busca laudo — tolerante a tenant nulo (modo standalone)
        if ($tenantId) {
            $stmt = $pdo->prepare("
                SELECT l.*, w.study_uid, w.patient_nome, w.patient_uid, w.modalidade
                FROM cop_laudos l
                JOIN cop_workspaces w ON w.id = l.workspace_id
                WHERE l.id = :id AND l.medico_id = :mid AND l.tenant_id = :tid
                LIMIT 1
            ");
            $stmt->execute(['id' => $id, 'mid' => $medicoId, 'tid' => $tenantId]);
        } else {
            $stmt = $pdo->prepare("
                SELECT l.*, w.study_uid, w.patient_nome, w.patient_uid, w.modalidade
                FROM cop_laudos l
                JOIN cop_workspaces w ON w.id = l.workspace_id
                WHERE l.id = :id AND l.medico_id = :mid
                LIMIT 1
            ");
            $stmt->execute(['id' => $id, 'mid' => $medicoId]);
        }
        $laudo = $stmt->fetch();

        if (!$laudo) $this->redirect('/workspace');

        // Templates para troca rápida
        if ($tenantId) {
            $tplStmt = $pdo->prepare("SELECT id, nome, modalidade, especialidade FROM cop_templates WHERE tenant_id = :tid AND ativo = 1 ORDER BY uso_count DESC LIMIT 20");
            $tplStmt->execute(['tid' => $tenantId]);
        } else {
            $tplStmt = $pdo->prepare("SELECT id, nome, modalidade, especialidade FROM cop_templates WHERE user_id = :uid AND ativo = 1 ORDER BY uso_count DESC LIMIT 20");
            $tplStmt->execute(['uid' => $medicoId]);
        }
        $templates = $tplStmt->fetchAll();

        // Autotextos
        if ($tenantId) {
            $atStmt = $pdo->prepare("SELECT atalho, texto FROM cop_autotextos WHERE tenant_id = :tid AND ativo = 1 AND (user_id IS NULL OR user_id = :uid) ORDER BY atalho ASC");
            $atStmt->execute(['tid' => $tenantId, 'uid' => $medicoId]);
        } else {
            $atStmt = $pdo->prepare("SELECT atalho, texto FROM cop_autotextos WHERE user_id = :uid AND ativo = 1 ORDER BY atalho ASC");
            $atStmt->execute(['uid' => $medicoId]);
        }
        $autotextos = $atStmt->fetchAll();

        // Histórico de conversa com IA
        $conversas = $pdo->prepare("
            SELECT role, conteudo, created_at FROM cop_ia_conversas
            WHERE workspace_id = :wid
            ORDER BY created_at ASC
            LIMIT 50
        ");
        $conversas->execute(['wid' => $laudo->workspace_id]);
        $conversas = $conversas->fetchAll();

        // URL do viewer PACS
        $pacsViewerUrl = '';
        if ($tenantId) {
            $pacsStmt = $pdo->prepare("SELECT pacs_api_url FROM cop_tenants WHERE id = :id LIMIT 1");
            $pacsStmt->execute(['id' => $tenantId]);
            $pacsRow = $pacsStmt->fetch();
            if ($pacsRow && $pacsRow->pacs_api_url) {
                $pacsViewerUrl = rtrim($pacsRow->pacs_api_url, '/') . '/viewer?study_uid=' . urlencode($laudo->study_uid ?? '');
            }
        }

        // Busca grupo do médico para determinar layout
        // 3 estratégias em cascata para máxima compatibilidade com banco sem migration
        $layoutRadiologista = false;
        $grupoNome = '';

        // Estratégia 1: JOIN via cop_users.grupo_id (requer migration 2026-07-17)
        try {
            $gStmt = $pdo->prepare(
                "SELECT g.nome FROM cop_grupos_medicos g
                 INNER JOIN cop_users u ON u.grupo_id = g.id
                 WHERE u.id = :uid LIMIT 1"
            );
            $gStmt->execute(['uid' => $medicoId]);
            $gRow = $gStmt->fetch();
            if ($gRow && $gRow->nome) {
                $grupoNome = strtolower($gRow->nome);
            }
        } catch (\Exception $e) {
            error_log('[Workspace] grupo estrategia 1 falhou: ' . $e->getMessage());
        }

        // Estratégia 2: tabela de vínculo N:N cop_medico_grupos
        if (!$grupoNome) {
            try {
                $gStmt2 = $pdo->prepare(
                    "SELECT g.nome FROM cop_grupos_medicos g
                     INNER JOIN cop_medico_grupos mg ON mg.grupo_id = g.id
                     WHERE mg.user_id = :uid LIMIT 1"
                );
                $gStmt2->execute(['uid' => $medicoId]);
                $gRow2 = $gStmt2->fetch();
                if ($gRow2 && $gRow2->nome) {
                    $grupoNome = strtolower($gRow2->nome);
                }
            } catch (\Exception $e) {
                error_log('[Workspace] grupo estrategia 2 falhou: ' . $e->getMessage());
            }
        }

        // Estratégia 3: verifica especialidades do médico como último recurso
        if (!$grupoNome) {
            try {
                $espStmt = $pdo->prepare("SELECT especialidades FROM cop_users WHERE id = :uid LIMIT 1");
                $espStmt->execute(['uid' => $medicoId]);
                $espRow = $espStmt->fetch();
                $esp = strtolower($espRow->especialidades ?? '');
                if (strpos($esp, 'radiolog') !== false ||
                    strpos($esp, 'tomografia') !== false ||
                    strpos($esp, 'ressonancia') !== false ||
                    strpos($esp, 'medicina nuclear') !== false) {
                    $grupoNome = 'radiologistas';
                }
            } catch (\Exception $e) {
                error_log('[Workspace] grupo estrategia 3 falhou: ' . $e->getMessage());
            }
        }

        $layoutRadiologista = (strpos($grupoNome, 'radiolog') !== false);

        // Busca máscaras da biblioteca para o seletor de templates
        $mascarasBiblioteca = [];
        try {
            $maskStmt = $pdo->prepare("
                SELECT id, nome, modalidade FROM cop_mascaras_biblioteca
                WHERE ativo = 1
                ORDER BY modalidade ASC, nome ASC
                LIMIT 300
            ");
            $maskStmt->execute();
            $mascarasBiblioteca = $maskStmt->fetchAll();
        } catch (\Exception $e) {
            $mascarasBiblioteca = [];
        }

        // Busca exames anteriores do mesmo paciente (qualquer modalidade)
        $examesAnteriores = [];
        try {
            $patUid  = $laudo->patient_uid  ?? null;
            $patNome = $laudo->patient_nome ?? null;

            if ($patUid) {
                // Busca por patient_uid (mais preciso)
                $exStmt = $pdo->prepare("
                    SELECT l.id, l.status, l.achados, l.impressao, l.indicacao,
                           l.tecnica, l.recomendacao, l.cid, l.assinado_em, l.created_at,
                           w.modalidade, w.study_uid, w.patient_nome
                    FROM cop_laudos l
                    JOIN cop_workspaces w ON w.id = l.workspace_id
                    WHERE w.patient_uid = :puid
                      AND l.id != :lid
                      AND l.status = 'assinado'
                    ORDER BY l.assinado_em DESC
                    LIMIT 10
                ");
                $exStmt->execute(['puid' => $patUid, 'lid' => $id]);
                $examesAnteriores = $exStmt->fetchAll();
            }

            // Fallback: busca por nome do paciente se não achou por UID
            if (empty($examesAnteriores) && $patNome) {
                $exStmt2 = $pdo->prepare("
                    SELECT l.id, l.status, l.achados, l.impressao, l.indicacao,
                           l.tecnica, l.recomendacao, l.cid, l.assinado_em, l.created_at,
                           w.modalidade, w.study_uid, w.patient_nome
                    FROM cop_laudos l
                    JOIN cop_workspaces w ON w.id = l.workspace_id
                    WHERE w.patient_nome LIKE :pnome
                      AND l.id != :lid
                      AND l.status = 'assinado'
                    ORDER BY l.assinado_em DESC
                    LIMIT 10
                ");
                $exStmt2->execute(['pnome' => '%' . $patNome . '%', 'lid' => $id]);
                $examesAnteriores = $exStmt2->fetchAll();
            }
        } catch (\Exception $e) {
            error_log('[Workspace] exames anteriores falhou: ' . $e->getMessage());
            $examesAnteriores = [];
        }

        $this->view('workspace/show', [
            'title'              => 'Laudo — VOXEL Copilot',
            'pageTitle'          => 'Editor de Laudo',
            'pageSubtitle'       => $laudo->patient_nome ?? $laudo->study_uid ?? 'Novo Laudo',
            'laudo'              => $laudo,
            'templates'          => $templates,
            'mascarasBiblioteca' => $mascarasBiblioteca,
            'autotextos'         => $autotextos,
            'conversas'          => $conversas,
            'pacsViewerUrl'      => $pacsViewerUrl,
            'csrf_token'         => $this->csrfToken(),
            'layoutRadiologista' => $layoutRadiologista,
            'examesAnteriores'   => $examesAnteriores,
        ]);
    }

    public function salvar(int $id): void {
        TenantMiddleware::handle();
        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId();

        // Verifica posse do laudo (tolerante a tenant nulo)
        if ($tenantId) {
            $stmt = $pdo->prepare("SELECT id FROM cop_laudos WHERE id = :id AND medico_id = :mid AND tenant_id = :tid AND status = 'rascunho' LIMIT 1");
            $stmt->execute(['id' => $id, 'mid' => $medicoId, 'tid' => $tenantId]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM cop_laudos WHERE id = :id AND medico_id = :mid AND status = 'rascunho' LIMIT 1");
            $stmt->execute(['id' => $id, 'mid' => $medicoId]);
        }

        if (!$stmt->fetch()) {
            $this->json(['ok' => false, 'msg' => 'Laudo não encontrado ou já assinado.'], 403);
            return;
        }

        $pdo->prepare("
            UPDATE cop_laudos SET
                indicacao    = :indicacao,
                tecnica      = :tecnica,
                achados      = :achados,
                impressao    = :impressao,
                recomendacao = :recomendacao,
                cid          = :cid,
                updated_at   = NOW()
            WHERE id = :id
        ")->execute([
            'id'          => $id,
            'indicacao'   => $_POST['indicacao']    ?? null,
            'tecnica'     => $_POST['tecnica']      ?? null,
            'achados'     => $_POST['achados']      ?? null,
            'impressao'   => $_POST['impressao']    ?? null,
            'recomendacao'=> $_POST['recomendacao'] ?? null,
            'cid'         => $_POST['cid']          ?? null,
        ]);

        $this->json(['ok' => true, 'msg' => 'Laudo salvo com sucesso.']);
    }

    public function assinar(int $id): void {
        TenantMiddleware::handle();
        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId();

        // Verifica posse do laudo (tolerante a tenant nulo)
        if ($tenantId) {
            $stmt = $pdo->prepare("SELECT id FROM cop_laudos WHERE id = :id AND medico_id = :mid AND tenant_id = :tid AND status = 'rascunho' LIMIT 1");
            $stmt->execute(['id' => $id, 'mid' => $medicoId, 'tid' => $tenantId]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM cop_laudos WHERE id = :id AND medico_id = :mid AND status = 'rascunho' LIMIT 1");
            $stmt->execute(['id' => $id, 'mid' => $medicoId]);
        }

        if (!$stmt->fetch()) {
            $this->json(['ok' => false, 'msg' => 'Laudo não encontrado ou já assinado.'], 403);
            return;
        }

        $pdo->prepare("
            UPDATE cop_laudos SET status = 'assinado', assinado_em = NOW(), updated_at = NOW()
            WHERE id = :id
        ")->execute(['id' => $id]);

        // Atualiza contagem no perfil (tolerante a tenant nulo)
        try {
            $pdo->prepare("
                INSERT INTO cop_medico_perfil (user_id, tenant_id, total_laudos)
                VALUES (:uid, :tid, 1)
                ON DUPLICATE KEY UPDATE total_laudos = total_laudos + 1, updated_at = NOW()
            ")->execute(['uid' => $medicoId, 'tid' => $tenantId ?? 0]);
        } catch (\Exception $e) {
            // Não bloqueia a assinatura se o perfil falhar
        }

        $this->json(['ok' => true, 'msg' => 'Laudo assinado com sucesso.']);
    }
}
