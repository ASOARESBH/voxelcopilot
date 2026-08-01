<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Middlewares\TenantMiddleware;
use App\Services\PacsService;
use App\Services\CopilotAIService;
use App\Services\ReportEngineService;

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

        // ── Worklist PACS: exames assumidos pelo médico vinculado ──────────────
        $pacsWorklist  = [];
        $pacsTotal     = 0;
        $pacsUnitName  = null;
        try {
            $authStmt = $pdo->prepare("
                SELECT a.id, u.codigo_unidade, u.nome_instituicao, u.pacs_tipo
                FROM cop_pacs_autorizacoes a
                JOIN cop_pacs_unidades u ON u.id = a.unidade_id
                WHERE a.medico_user_id = :uid AND a.status = 'ativo'
                LIMIT 1
            ");
            $authStmt->execute(['uid' => $medicoId]);
            $pacsAuth = $authStmt->fetch();
            if ($pacsAuth) {
                $pacsUnitName = $pacsAuth->nome_instituicao ?? $pacsAuth->codigo_unidade;
                $wStmt = $pdo->prepare("
                    SELECT w.id, w.study_instance_uid, w.patient_name, w.patient_id,
                           w.modalities, w.study_date, w.study_description,
                           w.institution_name, w.status_copilot, w.laudo_id,
                           w.assumido_em, w.created_at
                    FROM cop_pacs_worklist w
                    WHERE w.medico_user_id = :uid
                      AND w.status_copilot NOT IN ('assinado','cancelado')
                    ORDER BY w.assumido_em DESC
                    LIMIT 50
                ");
                $wStmt->execute(['uid' => $medicoId]);
                $pacsWorklist = $wStmt->fetchAll();
                $pacsTotal    = count($pacsWorklist);
            }
        } catch (\Throwable $e) {
            // Tabela pode não existir ainda — silencia
        }

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
            'pacsWorklist' => $pacsWorklist,
            'pacsTotal'    => $pacsTotal,
            'pacsUnitName' => $pacsUnitName,
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

        // ── Report Engine: Cabeçalho, Assinatura e Quality Engine ──
        $reportEngine    = new ReportEngineService();
        $reportCabecalho = $reportEngine->getCabecalho((int)$laudo->workspace_id, $tenantId);
        $reportAssinatura = [];
        if ($laudo->status === 'assinado') {
            $reportAssinatura = $reportEngine->getAssinatura((int)$laudo->id, $medicoId);
        }

        // Quality Engine: valida o laudo atual
        $qualityAlertas = $reportEngine->validate([
            'modalidade'   => $laudo->modalidade ?? '',
            'sexo'         => $laudo->patient_sexo ?? '',
            'indicacao'    => $laudo->indicacao ?? '',
            'tecnica'      => $laudo->tecnica ?? '',
            'achados'      => $laudo->achados ?? '',
            'impressao'    => $laudo->impressao ?? '',
            'recomendacao' => $laudo->recomendacao ?? '',
        ]);

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
            'reportCabecalho'    => $reportCabecalho,
            'reportAssinatura'   => $reportAssinatura,
            'qualityAlertas'     => $qualityAlertas,
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

        // ── Notifica o PACS sobre o laudo assinado ──────────────────────────────
        try {
            $this->notificarPacsLaudoAssinado($pdo, $id, $medicoId, $tenantId);
        } catch (\Throwable $e) {
            // Não bloqueia a assinatura se a notificação falhar
        }

        $this->json(['ok' => true, 'msg' => 'Laudo assinado com sucesso.']);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // GET /workspace/worklist
    // Página da worklist de exames recebidos do PACS
    // ─────────────────────────────────────────────────────────────────────────────
    public function worklist(): void {
        TenantMiddleware::handle();
        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();

        $itens = [];
        try {
            $stmt = $pdo->prepare("
                SELECT w.*,
                    u.nome_instituicao, u.codigo_unidade
                FROM cop_pacs_worklist w
                JOIN cop_pacs_autorizacoes a ON a.id = w.autorizacao_id
                JOIN cop_pacs_unidades u ON u.id = a.unidade_id
                WHERE w.user_id = :uid
                  AND w.status NOT IN ('enviado')
                ORDER BY
                    CASE w.prioridade WHEN 'urgente' THEN 0 WHEN 'alta' THEN 1 ELSE 2 END,
                    w.assumido_em DESC
                LIMIT 100
            ");
            $stmt->execute(['uid' => $medicoId]);
            $itens = $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\Throwable $e) {
            // Tabela pode não existir ainda
        }

        $this->view('workspace/worklist', [
            'title' => 'Worklist PACS',
            'itens' => $itens,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // GET /api/pacs/worklist
    // JSON: lista de exames pendentes do PACS para o médico logado
    // ─────────────────────────────────────────────────────────────────────────────
    public function apiWorklist(): void {
        TenantMiddleware::handle();
        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();

        $itens = [];
        try {
            $stmt = $pdo->prepare("
                SELECT w.id, w.study_instance_uid, w.accession_number,
                    w.patient_nome, w.patient_id, w.modalidade,
                    w.study_date, w.study_description, w.institution_name,
                    w.prioridade, w.status, w.assumido_em,
                    w.workspace_id, w.laudo_id,
                    u.nome_instituicao, u.codigo_unidade
                FROM cop_pacs_worklist w
                JOIN cop_pacs_autorizacoes a ON a.id = w.autorizacao_id
                JOIN cop_pacs_unidades u ON u.id = a.unidade_id
                WHERE w.user_id = :uid AND w.status NOT IN ('enviado', 'erro')
                ORDER BY
                    CASE w.prioridade WHEN 'urgente' THEN 0 WHEN 'alta' THEN 1 ELSE 2 END,
                    w.assumido_em DESC
                LIMIT 50
            ");
            $stmt->execute(['uid' => $medicoId]);
            $itens = $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\Throwable $e) {
            // Silencioso
        }

        $this->json(['ok' => true, 'total' => count($itens), 'itens' => $itens]);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // POST /api/pacs/worklist/{id}/finalizar
    // Finaliza o laudo e notifica o PACS via webhook
    // ─────────────────────────────────────────────────────────────────────────────
    public function finalizarLaudo(int $id): void {
        TenantMiddleware::handle();
        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();

        $stmt = $pdo->prepare("
            SELECT w.*, a.token_integracao, a.pacs_medico_token,
                u.pacs_webhook_url, u.pacs_api_token, u.codigo_unidade, u.chave_secreta
            FROM cop_pacs_worklist w
            JOIN cop_pacs_autorizacoes a ON a.id = w.autorizacao_id
            JOIN cop_pacs_unidades u ON u.id = a.unidade_id
            WHERE w.id = :id AND w.user_id = :uid LIMIT 1
        ");
        $stmt->execute(['id' => $id, 'uid' => $medicoId]);
        $wl = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$wl) {
            $this->json(['ok' => false, 'msg' => 'Item não encontrado.'], 404);
            return;
        }

        $laudoStmt = $pdo->prepare("SELECT * FROM cop_laudos WHERE id = :id AND medico_id = :mid LIMIT 1");
        $laudoStmt->execute(['id' => $wl->laudo_id, 'mid' => $medicoId]);
        $laudo = $laudoStmt->fetch(\PDO::FETCH_OBJ);

        $resultado = $this->enviarLaudoAoPacs($wl, $laudo);

        if ($resultado) {
            $pdo->prepare("
                UPDATE cop_pacs_worklist SET
                    status = 'enviado', enviado_pacs_em = NOW(), updated_at = NOW()
                WHERE id = :id
            ")->execute(['id' => $id]);
            $this->json(['ok' => true, 'msg' => 'Laudo enviado ao PACS com sucesso.']);
        } else {
            $pdo->prepare("
                UPDATE cop_pacs_worklist SET
                    status = 'erro', erro_msg = 'Falha ao enviar ao PACS', updated_at = NOW()
                WHERE id = :id
            ")->execute(['id' => $id]);
            $this->json(['ok' => false, 'msg' => 'Falha ao notificar o PACS. O laudo foi salvo.']);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // HELPER: notifica o PACS após assinar laudo (via cop_pacs_worklist)
    // ─────────────────────────────────────────────────────────────────────────────
    private function notificarPacsLaudoAssinado(\PDO $pdo, int $laudoId, int $medicoId, ?int $tenantId): void {
        $stmt = $pdo->prepare("
            SELECT w.*, a.token_integracao, a.pacs_medico_token,
                u.pacs_webhook_url, u.pacs_api_token, u.codigo_unidade, u.chave_secreta
            FROM cop_pacs_worklist w
            JOIN cop_pacs_autorizacoes a ON a.id = w.autorizacao_id
            JOIN cop_pacs_unidades u ON u.id = a.unidade_id
            WHERE w.laudo_id = :lid AND w.user_id = :uid AND w.status != 'enviado' LIMIT 1
        ");
        $stmt->execute(['lid' => $laudoId, 'uid' => $medicoId]);
        $wl = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$wl || !$wl->pacs_webhook_url) return;

        $laudoStmt = $pdo->prepare("SELECT * FROM cop_laudos WHERE id = :id LIMIT 1");
        $laudoStmt->execute(['id' => $laudoId]);
        $laudo = $laudoStmt->fetch(\PDO::FETCH_OBJ);

        $ok = $this->enviarLaudoAoPacs($wl, $laudo);
        if ($ok) {
            $pdo->prepare("
                UPDATE cop_pacs_worklist SET
                    status = 'enviado', enviado_pacs_em = NOW(), updated_at = NOW()
                WHERE id = :id
            ")->execute(['id' => $wl->id]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // HELPER: envia o laudo ao PACS via webhook
    // ─────────────────────────────────────────────────────────────────────────────
    // ─────────────────────────────────────────────────────────────────────────────
    // GET /api/pacs/viewer-url?study_uid=...
    // Retorna a URL do viewer PACS para abrir as imagens de um estudo
    // ─────────────────────────────────────────────────────────────────────────────
    public function apiViewerUrl(): void {
        TenantMiddleware::handle();
        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $studyUid = trim($_GET['study_uid'] ?? '');

        if (!$studyUid) {
            $this->json(['ok' => false, 'url' => null, 'msg' => 'study_uid obrigatório']);
            return;
        }

        try {
            // Busca a URL base do PACS vinculado ao médico
            $stmt = $pdo->prepare("
                SELECT u.pacs_viewer_url, u.pacs_webhook_url, u.codigo_unidade
                FROM cop_pacs_worklist w
                JOIN cop_pacs_autorizacoes a ON a.id = w.autorizacao_id
                JOIN cop_pacs_unidades u ON u.id = a.unidade_id
                WHERE w.user_id = :uid AND w.study_instance_uid = :uid2 LIMIT 1
            ");
            $stmt->execute(['uid' => $medicoId, 'uid2' => $studyUid]);
            $row = $stmt->fetch(\PDO::FETCH_OBJ);

            if ($row) {
                // Usa pacs_viewer_url se disponível, senão deriva do pacs_webhook_url
                $baseUrl = $row->pacs_viewer_url ?? null;
                if (!$baseUrl && $row->pacs_webhook_url) {
                    $parsed  = parse_url($row->pacs_webhook_url);
                    $baseUrl = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');
                    if (!empty($parsed['port'])) $baseUrl .= ':' . $parsed['port'];
                }
                if ($baseUrl) {
                    $viewerUrl = rtrim($baseUrl, '/') . '/viewer?StudyInstanceUID=' . urlencode($studyUid);
                    $this->json(['ok' => true, 'url' => $viewerUrl]);
                    return;
                }
            }
        } catch (\Throwable $e) {
            \App\Core\Logger::error('[WorkspaceController::apiViewerUrl] ' . $e->getMessage());
        }

        $this->json(['ok' => false, 'url' => null, 'msg' => 'URL do viewer não configurada.']);
    }

    private function enviarLaudoAoPacs(object $wl, ?object $laudo): bool {
        if (!$wl->pacs_webhook_url) return false;

        $payload = [
            'evento'             => 'laudo.finalizado',
            'study_instance_uid' => $wl->study_instance_uid,
            'accession_number'   => $wl->accession_number,
            'medico_nome'        => $wl->medico_nome ?? '',
            'medico_token'       => $wl->pacs_medico_token ?? '',
            'laudo_html'         => $laudo ? ($laudo->achados ?? '') : '',
            'laudo_texto'        => $laudo ? strip_tags($laudo->achados ?? '') : '',
            'assinado_em'        => $laudo ? ($laudo->assinado_em ?? date('c')) : date('c'),
            '_meta' => [
                'codigo_unidade'  => $wl->codigo_unidade,
                'copilot_version' => '2026',
            ],
        ];

        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $assinatura  = '';
        if (!empty($wl->chave_secreta)) {
            $assinatura = 'sha256=' . hash_hmac('sha256', $payloadJson, $wl->chave_secreta);
        }

        $url = rtrim($wl->pacs_webhook_url, '/') . '/api/copilot/webhook/laudo-finalizado';

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payloadJson,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'X-Copilot-Signature: ' . $assinatura,
                    'Authorization: Bearer ' . ($wl->pacs_api_token ?? ''),
                ],
            ]);
            $resp   = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $status >= 200 && $status < 300;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
