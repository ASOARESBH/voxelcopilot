<?php
namespace App\Controllers\Platform;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Audit\AuditLogger;

class PlatformController extends Controller {

    private function requirePlatformAdmin(): void {
        if (!Auth::check() || !Auth::isPlatformAdmin()) {
            $this->redirect('/login');
        }
    }

    // ─── DASHBOARD ───────────────────────────────────────────────────────────

    public function dashboard(): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        $totalMedicos     = $pdo->query("SELECT COUNT(*) FROM cop_users WHERE role = 'medico'")->fetchColumn();
        $medicosAtivos    = $pdo->query("SELECT COUNT(*) FROM cop_users WHERE role = 'medico' AND status = 'ativo'")->fetchColumn();
        $medicosPendentes = $pdo->query("SELECT COUNT(*) FROM cop_users WHERE role = 'medico' AND status = 'pendente'")->fetchColumn();
        $totalLaudos      = $pdo->query("SELECT COUNT(*) FROM cop_laudos")->fetchColumn() ?? 0;

        $ultimos = $pdo->query("
            SELECT u.id, u.name, u.email, u.crm, u.crm_uf, u.status, u.created_at, u.especialidades
            FROM cop_users u
            WHERE u.role = 'medico'
            ORDER BY u.created_at DESC
            LIMIT 8
        ")->fetchAll();

        $this->view('platform/dashboard', [
            'title'           => 'Dashboard — VOXEL Copilot',
            'pageTitle'       => 'Dashboard',
            'pageSubtitle'    => 'Visão geral da plataforma',
            'totalMedicos'    => $totalMedicos,
            'medicosAtivos'   => $medicosAtivos,
            'medicosPendentes'=> $medicosPendentes,
            'totalLaudos'     => $totalLaudos,
            'ultimos'         => $ultimos,
        ]);
    }

    // ─── MÉDICOS — LISTAGEM ──────────────────────────────────────────────────

    public function medicos(): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        $busca   = trim($_GET['busca']   ?? '');
        $status  = $_GET['status']  ?? '';
        $grupo   = $_GET['grupo']   ?? '';
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        $where  = ["u.role = 'medico'"];
        $params = [];

        if ($busca) {
            $where[]         = "(u.name LIKE :busca OR u.email LIKE :busca2 OR u.crm LIKE :busca3)";
            $params['busca']  = "%{$busca}%";
            $params['busca2'] = "%{$busca}%";
            $params['busca3'] = "%{$busca}%";
        }
        if ($status) {
            $where[]          = "u.status = :status";
            $params['status'] = $status;
        }
        if ($grupo) {
            $where[]          = "u.grupo_id = :grupo";
            $params['grupo']  = $grupo;
        }

        $whereStr = implode(' AND ', $where);

        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM cop_users u WHERE {$whereStr}");
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT u.id, u.name, u.email, u.crm, u.crm_uf, u.status,
                   u.especialidades, u.cidade, u.estado, u.telefone,
                   u.ultimo_login, u.created_at, u.grupo_id,
                   g.nome AS grupo_nome, g.cor AS grupo_cor
            FROM cop_users u
            LEFT JOIN cop_grupos_medicos g ON g.id = u.grupo_id
            WHERE {$whereStr}
            ORDER BY u.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $medicos = $stmt->fetchAll();

        // Grupos para filtro
        try {
            $grupos = $pdo->query("SELECT id, nome, cor FROM cop_grupos_medicos WHERE ativo = 1 ORDER BY nome ASC")->fetchAll();
        } catch (\PDOException $e) {
            $grupos = [];
        }

        $msg = $_GET['msg'] ?? '';

        $this->view('platform/medicos/index', [
            'title'       => 'Médicos — VOXEL Copilot',
            'pageTitle'   => 'Médicos',
            'pageSubtitle'=> 'Gestão de médicos cadastrados',
            'medicos'     => $medicos,
            'busca'       => $busca,
            'status'      => $status,
            'grupo'       => $grupo,
            'grupos'      => $grupos,
            'total'       => $total,
            'page'        => $page,
            'perPage'     => $perPage,
            'totalPages'  => (int) ceil($total / $perPage),
            'msg'         => $msg,
        ]);
    }

    // ─── MÉDICOS — SHOW ──────────────────────────────────────────────────────

    public function medicoShow(int $id): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare("
            SELECT u.*, g.nome AS grupo_nome, g.cor AS grupo_cor, g.icone AS grupo_icone
            FROM cop_users u
            LEFT JOIN cop_grupos_medicos g ON g.id = u.grupo_id
            WHERE u.id = :id AND u.role = 'medico'
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $medico = $stmt->fetch();

        if (!$medico) { $this->redirect('/platform/medicos'); }

        // Laudos recentes
        try {
            $laudos = $pdo->prepare("SELECT id, titulo, status, created_at FROM cop_laudos WHERE medico_id = :uid ORDER BY created_at DESC LIMIT 10");
            $laudos->execute(['uid' => $id]);
            $laudos = $laudos->fetchAll();
        } catch (\PDOException $e) {
            $laudos = [];
        }

        $this->view('platform/medicos/show', [
            'title'     => "Dr. {$medico->name} — VOXEL Copilot",
            'pageTitle' => 'Detalhes do Médico',
            'medico'    => $medico,
            'laudos'    => $laudos,
        ]);
    }

    // ─── MÉDICOS — NOVO (form) ───────────────────────────────────────────────

    public function medicoNovo(): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        try {
            $grupos = $pdo->query("SELECT id, nome, cor, icone FROM cop_grupos_medicos WHERE ativo = 1 ORDER BY nome ASC")->fetchAll();
        } catch (\PDOException $e) {
            $grupos = [];
        }

        $this->view('platform/medicos/form', [
            'title'       => 'Novo Médico — VOXEL Copilot',
            'pageTitle'   => 'Novo Médico',
            'pageSubtitle'=> 'Cadastrar novo médico na plataforma',
            'medico'      => null,
            'grupos'      => $grupos,
            'erro'        => $_GET['erro'] ?? '',
            'old'         => [],
        ]);
    }

    // ─── MÉDICOS — CRIAR ─────────────────────────────────────────────────────

    public function medicoCreate(): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        $nome         = trim($_POST['name']          ?? '');
        $email        = trim($_POST['email']         ?? '');
        $senha        = trim($_POST['password']      ?? '');
        $crm          = trim($_POST['crm']           ?? '');
        $crmUf        = strtoupper(trim($_POST['crm_uf']       ?? ''));
        $telefone     = trim($_POST['telefone']      ?? '');
        $especialidades = $_POST['especialidades']   ?? [];
        $grupoId      = (int)($_POST['grupo_id']     ?? 0) ?: null;
        $status       = $_POST['status']             ?? 'ativo';
        $cep          = preg_replace('/\D/', '', $_POST['cep']          ?? '');
        $logradouro   = trim($_POST['logradouro']    ?? '');
        $numero       = trim($_POST['numero']        ?? '');
        $complemento  = trim($_POST['complemento']  ?? '');
        $bairro       = trim($_POST['bairro']        ?? '');
        $cidade       = trim($_POST['cidade']        ?? '');
        $estado       = strtoupper(trim($_POST['estado']       ?? ''));
        $iaModelo     = $_POST['ia_modelo']          ?? 'gpt-4o';
        $iaTemp       = (float)($_POST['ia_temperatura'] ?? 0.30);
        $iaEstilo     = $_POST['ia_estilo']          ?? 'formal';

        // Validações básicas
        if (!$nome || !$email) {
            $this->redirect('/platform/medicos/novo?erro=campos_obrigatorios');
            return;
        }

        // Verifica e-mail duplicado
        $check = $pdo->prepare("SELECT id FROM cop_users WHERE email = :email LIMIT 1");
        $check->execute(['email' => $email]);
        if ($check->fetch()) {
            $this->redirect('/platform/medicos/novo?erro=email_duplicado');
            return;
        }

        $hash = $senha ? password_hash($senha, PASSWORD_DEFAULT) : password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        $especJson = json_encode(array_filter(array_map('trim', (array)$especialidades)));

        try {
            $pdo->prepare("
                INSERT INTO cop_users
                    (name, email, password, role, status, grupo_id,
                     crm, crm_uf, telefone, especialidades,
                     cep, logradouro, numero, complemento, bairro, cidade, estado,
                     ia_modelo, ia_temperatura, ia_estilo,
                     email_verificado, created_at, updated_at)
                VALUES
                    (:name, :email, :pass, 'medico', :status, :grupo,
                     :crm, :crmuf, :tel, :espec,
                     :cep, :log, :num, :comp, :bairro, :cidade, :estado,
                     :iamod, :iatemp, :iaest,
                     1, NOW(), NOW())
            ")->execute([
                'name'   => $nome,
                'email'  => $email,
                'pass'   => $hash,
                'status' => $status,
                'grupo'  => $grupoId,
                'crm'    => $crm ?: null,
                'crmuf'  => $crmUf ?: null,
                'tel'    => $telefone ?: null,
                'espec'  => $especJson,
                'cep'    => $cep ?: null,
                'log'    => $logradouro ?: null,
                'num'    => $numero ?: null,
                'comp'   => $complemento ?: null,
                'bairro' => $bairro ?: null,
                'cidade' => $cidade ?: null,
                'estado' => $estado ?: null,
                'iamod'  => $iaModelo,
                'iatemp' => $iaTemp,
                'iaest'  => $iaEstilo,
            ]);
            $novoId = (int) $pdo->lastInsertId();
            AuditLogger::log('medico_criado', 'user', $novoId, ['by' => Auth::userId()]);
            $this->redirect('/platform/medicos?msg=criado');
        } catch (\PDOException $e) {
            error_log('[PlatformController::medicoCreate] ' . $e->getMessage());
            $this->redirect('/platform/medicos/novo?erro=db_error');
        }
    }

    // ─── MÉDICOS — EDITAR (form) ─────────────────────────────────────────────

    public function medicoEditar(int $id): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare("SELECT * FROM cop_users WHERE id = :id AND role = 'medico' LIMIT 1");
        $stmt->execute(['id' => $id]);
        $medico = $stmt->fetch();

        if (!$medico) { $this->redirect('/platform/medicos'); }

        try {
            $grupos = $pdo->query("SELECT id, nome, cor, icone FROM cop_grupos_medicos WHERE ativo = 1 ORDER BY nome ASC")->fetchAll();
        } catch (\PDOException $e) {
            $grupos = [];
        }

        $this->view('platform/medicos/form', [
            'title'       => "Editar Dr. {$medico->name} — VOXEL Copilot",
            'pageTitle'   => 'Editar Médico',
            'pageSubtitle'=> 'Atualizar dados do médico',
            'medico'      => $medico,
            'grupos'      => $grupos,
            'erro'        => $_GET['erro'] ?? '',
            'old'         => [],
        ]);
    }

    // ─── MÉDICOS — ATUALIZAR ─────────────────────────────────────────────────

    public function medicoAtualizar(int $id): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        // Verifica se médico existe
        $check = $pdo->prepare("SELECT id FROM cop_users WHERE id = :id AND role = 'medico' LIMIT 1");
        $check->execute(['id' => $id]);
        if (!$check->fetch()) { $this->redirect('/platform/medicos'); }

        $nome        = trim($_POST['name']          ?? '');
        $email       = trim($_POST['email']         ?? '');
        $senha       = trim($_POST['password']      ?? '');
        $crm         = trim($_POST['crm']           ?? '');
        $crmUf       = strtoupper(trim($_POST['crm_uf']       ?? ''));
        $telefone    = trim($_POST['telefone']      ?? '');
        $especialidades = $_POST['especialidades']  ?? [];
        $grupoId     = (int)($_POST['grupo_id']     ?? 0) ?: null;
        $status      = $_POST['status']             ?? 'ativo';
        $cep         = preg_replace('/\D/', '', $_POST['cep']          ?? '');
        $logradouro  = trim($_POST['logradouro']    ?? '');
        $numero      = trim($_POST['numero']        ?? '');
        $complemento = trim($_POST['complemento']  ?? '');
        $bairro      = trim($_POST['bairro']        ?? '');
        $cidade      = trim($_POST['cidade']        ?? '');
        $estado      = strtoupper(trim($_POST['estado']       ?? ''));
        $iaModelo    = $_POST['ia_modelo']          ?? 'gpt-4o';
        $iaTemp      = (float)($_POST['ia_temperatura'] ?? 0.30);
        $iaEstilo    = $_POST['ia_estilo']          ?? 'formal';

        if (!$nome || !$email) {
            $this->redirect("/platform/medicos/{$id}/editar?erro=campos_obrigatorios");
            return;
        }

        $especJson = json_encode(array_filter(array_map('trim', (array)$especialidades)));

        $params = [
            'name'   => $nome,
            'email'  => $email,
            'status' => $status,
            'grupo'  => $grupoId,
            'crm'    => $crm ?: null,
            'crmuf'  => $crmUf ?: null,
            'tel'    => $telefone ?: null,
            'espec'  => $especJson,
            'cep'    => $cep ?: null,
            'log'    => $logradouro ?: null,
            'num'    => $numero ?: null,
            'comp'   => $complemento ?: null,
            'bairro' => $bairro ?: null,
            'cidade' => $cidade ?: null,
            'estado' => $estado ?: null,
            'iamod'  => $iaModelo,
            'iatemp' => $iaTemp,
            'iaest'  => $iaEstilo,
            'id'     => $id,
        ];

        $setSenha = '';
        if ($senha) {
            $setSenha = ', password = :pass';
            $params['pass'] = password_hash($senha, PASSWORD_DEFAULT);
        }

        try {
            $pdo->prepare("
                UPDATE cop_users SET
                    name = :name, email = :email, status = :status, grupo_id = :grupo,
                    crm = :crm, crm_uf = :crmuf, telefone = :tel, especialidades = :espec,
                    cep = :cep, logradouro = :log, numero = :num, complemento = :comp,
                    bairro = :bairro, cidade = :cidade, estado = :estado,
                    ia_modelo = :iamod, ia_temperatura = :iatemp, ia_estilo = :iaest
                    {$setSenha},
                    updated_at = NOW()
                WHERE id = :id
            ")->execute($params);

            AuditLogger::log('medico_atualizado', 'user', $id, ['by' => Auth::userId()]);
            $this->redirect('/platform/medicos?msg=atualizado');
        } catch (\PDOException $e) {
            error_log('[PlatformController::medicoAtualizar] ' . $e->getMessage());
            $this->redirect("/platform/medicos/{$id}/editar?erro=db_error");
        }
    }

    // ─── MÉDICOS — TOGGLE STATUS ─────────────────────────────────────────────

    public function medicoToggleStatus(int $id): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare("SELECT status FROM cop_users WHERE id = :id AND role = 'medico' LIMIT 1");
        $stmt->execute(['id' => $id]);
        $medico = $stmt->fetch();

        if (!$medico) { $this->redirect('/platform/medicos'); }

        $novoStatus = $medico->status === 'ativo' ? 'inativo' : 'ativo';
        $pdo->prepare("UPDATE cop_users SET status = :status, updated_at = NOW() WHERE id = :id")
            ->execute(['status' => $novoStatus, 'id' => $id]);

        AuditLogger::log("medico_{$novoStatus}", 'user', $id, ['by' => Auth::userId()]);
        $this->redirect('/platform/medicos?msg=status_atualizado');
    }

    // ─── GRUPOS DE MÉDICOS ───────────────────────────────────────────────────

    public function grupos(): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        try {
            $grupos = $pdo->query("
                SELECT g.*, COUNT(u.id) AS total_medicos
                FROM cop_grupos_medicos g
                LEFT JOIN cop_users u ON u.grupo_id = g.id AND u.role = 'medico'
                GROUP BY g.id
                ORDER BY g.nome ASC
            ")->fetchAll();
        } catch (\PDOException $e) {
            $grupos = [];
            error_log('[PlatformController::grupos] ' . $e->getMessage());
        }

        $this->view('platform/grupos/index', [
            'title'       => 'Grupos de Médicos — VOXEL Copilot',
            'pageTitle'   => 'Grupos de Médicos',
            'pageSubtitle'=> 'Organização e categorização dos médicos',
            'grupos'      => $grupos,
            'msg'         => $_GET['msg'] ?? '',
        ]);
    }

    public function grupoNovo(): void {
        $this->requirePlatformAdmin();
        $this->view('platform/grupos/form', [
            'title'     => 'Novo Grupo — VOXEL Copilot',
            'pageTitle' => 'Novo Grupo',
            'grupo'     => null,
            'erro'      => $_GET['erro'] ?? '',
        ]);
    }

    public function grupoCreate(): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        $nome     = trim($_POST['nome']     ?? '');
        $descricao= trim($_POST['descricao']?? '');
        $cor      = trim($_POST['cor']      ?? '#1a56db');
        $icone    = trim($_POST['icone']    ?? 'fa-user-doctor');

        if (!$nome) { $this->redirect('/platform/grupos/novo?erro=nome_obrigatorio'); return; }

        try {
            $pdo->prepare("INSERT INTO cop_grupos_medicos (nome, descricao, cor, icone) VALUES (:nome, :desc, :cor, :icone)")
                ->execute(['nome' => $nome, 'desc' => $descricao ?: null, 'cor' => $cor, 'icone' => $icone]);
            $this->redirect('/platform/grupos?msg=criado');
        } catch (\PDOException $e) {
            error_log('[PlatformController::grupoCreate] ' . $e->getMessage());
            $this->redirect('/platform/grupos/novo?erro=duplicado');
        }
    }

    public function grupoEditar(int $id): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare("SELECT * FROM cop_grupos_medicos WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $grupo = $stmt->fetch();

        if (!$grupo) { $this->redirect('/platform/grupos'); }

        $this->view('platform/grupos/form', [
            'title'     => "Editar Grupo — VOXEL Copilot",
            'pageTitle' => 'Editar Grupo',
            'grupo'     => $grupo,
            'erro'      => $_GET['erro'] ?? '',
        ]);
    }

    public function grupoAtualizar(int $id): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        $nome     = trim($_POST['nome']      ?? '');
        $descricao= trim($_POST['descricao'] ?? '');
        $cor      = trim($_POST['cor']       ?? '#1a56db');
        $icone    = trim($_POST['icone']     ?? 'fa-user-doctor');
        $ativo    = isset($_POST['ativo']) ? 1 : 0;

        if (!$nome) { $this->redirect("/platform/grupos/{$id}/editar?erro=nome_obrigatorio"); return; }

        $pdo->prepare("UPDATE cop_grupos_medicos SET nome=:nome, descricao=:desc, cor=:cor, icone=:icone, ativo=:ativo, updated_at=NOW() WHERE id=:id")
            ->execute(['nome' => $nome, 'desc' => $descricao ?: null, 'cor' => $cor, 'icone' => $icone, 'ativo' => $ativo, 'id' => $id]);

        $this->redirect('/platform/grupos?msg=atualizado');
    }

    // ─── IMPERSONAÇÃO ────────────────────────────────────────────────────────

    public function impersonate(int $medicoId): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare("SELECT * FROM cop_users WHERE id = :id AND role = 'medico' AND status = 'ativo' LIMIT 1");
        $stmt->execute(['id' => $medicoId]);
        $medico = $stmt->fetch();

        if (!$medico) { $this->redirect('/platform/medicos?erro=medico_nao_encontrado'); }

        $_SESSION['impersonating_tenant_id'] = $medicoId;
        $_SESSION['original_user']           = $_SESSION['user'];
        $_SESSION['original_user_id']        = $_SESSION['user_id'];

        unset($medico->password);
        $_SESSION['user']    = $medico;
        $_SESSION['user_id'] = $medico->id;

        AuditLogger::log('impersonate_medico', 'user', $medicoId, ['by' => Auth::userId()]);
        $this->redirect('/dashboard');
    }

    public function exitImpersonate(): void {
        if (!isset($_SESSION['impersonating_tenant_id'])) {
            $this->redirect('/platform/dashboard');
        }

        $_SESSION['user']    = $_SESSION['original_user'];
        $_SESSION['user_id'] = $_SESSION['original_user_id'];

        unset(
            $_SESSION['impersonating_tenant_id'],
            $_SESSION['original_user'],
            $_SESSION['original_user_id'],
            $_SESSION['tenant_id']
        );

        $this->redirect('/platform/medicos');
    }

    // ─── PLANOS ──────────────────────────────────────────────────────────────

    public function planos(): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        $planos = $pdo->query("SELECT * FROM cop_plans ORDER BY preco_mensal ASC")->fetchAll();

        $this->view('platform/planos/index', [
            'title'       => 'Planos — VOXEL Copilot',
            'pageTitle'   => 'Planos',
            'pageSubtitle'=> 'Gestão de planos da plataforma',
            'planos'      => $planos,
        ]);
    }
}
