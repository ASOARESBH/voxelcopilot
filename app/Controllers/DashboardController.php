<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Middlewares\AuthMiddleware;

class DashboardController extends Controller {

    public function index(): void {
        // Apenas verifica se está autenticado (sem exigir tenant — modo standalone)
        AuthMiddleware::handle();

        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId(); // pode ser null em modo standalone

        // Laudos do médico (tolerante a tenant nulo)
        if ($tenantId) {
            $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM cop_laudos WHERE medico_id = :mid AND tenant_id = :tid");
            $stmtTotal->execute(['mid' => $medicoId, 'tid' => $tenantId]);
            $totalLaudos = (int) $stmtTotal->fetchColumn();

            $stmtHoje = $pdo->prepare("SELECT COUNT(*) FROM cop_laudos WHERE medico_id = :mid AND tenant_id = :tid AND DATE(created_at) = CURDATE()");
            $stmtHoje->execute(['mid' => $medicoId, 'tid' => $tenantId]);
            $laudosHoje = (int) $stmtHoje->fetchColumn();

            $stmtMes = $pdo->prepare("SELECT COUNT(*) FROM cop_laudos WHERE medico_id = :mid AND tenant_id = :tid AND YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())");
            $stmtMes->execute(['mid' => $medicoId, 'tid' => $tenantId]);
            $laudosMes = (int) $stmtMes->fetchColumn();

            $stmtTpl = $pdo->prepare("SELECT COUNT(*) FROM cop_templates WHERE tenant_id = :tid AND ativo = 1");
            $stmtTpl->execute(['tid' => $tenantId]);
            $totalTemplates = (int) $stmtTpl->fetchColumn();

            $stmtUlt = $pdo->prepare("
                SELECT l.id, l.status, l.created_at, l.assinado_em,
                       w.patient_nome, w.modalidade, w.study_uid
                FROM cop_laudos l
                JOIN cop_workspaces w ON w.id = l.workspace_id
                WHERE l.medico_id = :mid AND l.tenant_id = :tid
                ORDER BY l.created_at DESC LIMIT 8
            ");
            $stmtUlt->execute(['mid' => $medicoId, 'tid' => $tenantId]);
            $ultimosLaudos = $stmtUlt->fetchAll();

            $stmtPerfil = $pdo->prepare("SELECT * FROM cop_medico_perfil WHERE user_id = :uid AND tenant_id = :tid LIMIT 1");
            $stmtPerfil->execute(['uid' => $medicoId, 'tid' => $tenantId]);
            $perfil = $stmtPerfil->fetch();
        } else {
            // Modo standalone: sem tenant vinculado, valores zerados
            $totalLaudos    = 0;
            $laudosHoje     = 0;
            $laudosMes      = 0;
            $totalTemplates = 0;
            $ultimosLaudos  = [];
            $perfil         = null;
        }

        $this->view('dashboard/index', [
            'title'          => 'Dashboard — VOXEL Copilot',
            'pageTitle'      => 'Dashboard',
            'pageSubtitle'   => 'Bem-vindo ao seu workspace',
            'totalLaudos'    => $totalLaudos,
            'laudosHoje'     => $laudosHoje,
            'laudosMes'      => $laudosMes,
            'totalTemplates' => $totalTemplates,
            'ultimosLaudos'  => $ultimosLaudos,
            'perfil'         => $perfil,
        ]);
    }
}
