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

        $totalMedicos = $pdo->query("SELECT COUNT(*) FROM cop_users WHERE role = 'medico'")->fetchColumn();
        $medicosAtivos = $pdo->query("SELECT COUNT(*) FROM cop_users WHERE role = 'medico' AND status = 'ativo'")->fetchColumn();
        $medicosPendentes = $pdo->query("SELECT COUNT(*) FROM cop_users WHERE role = 'medico' AND status = 'pendente'")->fetchColumn();
        $totalLaudos = $pdo->query("SELECT COUNT(*) FROM cop_laudos")->fetchColumn() ?? 0;

        // Últimos cadastros
        $ultimos = $pdo->query("
            SELECT u.id, u.name, u.email, u.crm, u.crm_uf, u.status, u.created_at,
                   u.especialidades
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

    // ─── MÉDICOS ─────────────────────────────────────────────────────────────

    public function medicos(): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        $busca  = trim($_GET['busca'] ?? '');
        $status = $_GET['status'] ?? '';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        $where  = ["u.role = 'medico'"];
        $params = [];

        if ($busca) {
            $where[]       = "(u.name LIKE :busca OR u.email LIKE :busca OR u.crm LIKE :busca)";
            $params['busca'] = "%{$busca}%";
        }
        if ($status) {
            $where[]         = "u.status = :status";
            $params['status'] = $status;
        }

        $whereStr = implode(' AND ', $where);

        $total = $pdo->prepare("SELECT COUNT(*) FROM cop_users u WHERE {$whereStr}");
        $total->execute($params);
        $total = (int) $total->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT u.id, u.name, u.email, u.crm, u.crm_uf, u.status,
                   u.especialidades, u.cidade, u.estado, u.ultimo_login, u.created_at
            FROM cop_users u
            WHERE {$whereStr}
            ORDER BY u.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $medicos = $stmt->fetchAll();

        $this->view('platform/medicos/index', [
            'title'       => 'Médicos — VOXEL Copilot',
            'pageTitle'   => 'Médicos',
            'pageSubtitle'=> 'Gestão de médicos cadastrados',
            'medicos'     => $medicos,
            'busca'       => $busca,
            'status'      => $status,
            'total'       => $total,
            'page'        => $page,
            'perPage'     => $perPage,
            'totalPages'  => (int) ceil($total / $perPage),
        ]);
    }

    public function medicoShow(int $id): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare("SELECT * FROM cop_users WHERE id = :id AND role = 'medico' LIMIT 1");
        $stmt->execute(['id' => $id]);
        $medico = $stmt->fetch();

        if (!$medico) {
            $this->redirect('/platform/medicos');
        }

        $this->view('platform/medicos/show', [
            'title'      => "Dr. {$medico->name} — VOXEL Copilot",
            'pageTitle'  => 'Detalhes do Médico',
            'medico'     => $medico,
        ]);
    }

    public function medicoToggleStatus(int $id): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare("SELECT status FROM cop_users WHERE id = :id AND role = 'medico' LIMIT 1");
        $stmt->execute(['id' => $id]);
        $medico = $stmt->fetch();

        if (!$medico) {
            $this->redirect('/platform/medicos');
        }

        $novoStatus = $medico->status === 'ativo' ? 'inativo' : 'ativo';
        $pdo->prepare("UPDATE cop_users SET status = :status, updated_at = NOW() WHERE id = :id")
            ->execute(['status' => $novoStatus, 'id' => $id]);

        AuditLogger::log("medico_{$novoStatus}", 'user', $id, ['by' => Auth::userId()]);

        $this->redirect('/platform/medicos?msg=status_atualizado');
    }

    // ─── IMPERSONAÇÃO ────────────────────────────────────────────────────────

    public function impersonate(int $medicoId): void {
        $this->requirePlatformAdmin();
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare("SELECT * FROM cop_users WHERE id = :id AND role = 'medico' AND status = 'ativo' LIMIT 1");
        $stmt->execute(['id' => $medicoId]);
        $medico = $stmt->fetch();

        if (!$medico) {
            $this->redirect('/platform/medicos?erro=medico_nao_encontrado');
        }

        // Salva sessão original
        $_SESSION['impersonating_tenant_id'] = $medicoId;
        $_SESSION['original_user']           = $_SESSION['user'];
        $_SESSION['original_user_id']        = $_SESSION['user_id'];

        // Substitui sessão pelo médico
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

        // Restaura sessão original
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
            'title'      => 'Planos — VOXEL Copilot',
            'pageTitle'  => 'Planos',
            'pageSubtitle'=> 'Gestão de planos da plataforma',
            'planos'     => $planos,
        ]);
    }
}
