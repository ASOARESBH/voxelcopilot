<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Middlewares\TenantMiddleware;

class DashboardController extends Controller {

    public function index(): void {
        TenantMiddleware::handle();

        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId();

        // Laudos do médico
        $totalLaudos = $pdo->prepare("
            SELECT COUNT(*) FROM cop_laudos WHERE medico_id = :mid AND tenant_id = :tid
        ");
        $totalLaudos->execute(['mid' => $medicoId, 'tid' => $tenantId]);
        $totalLaudos = (int) $totalLaudos->fetchColumn();

        $laudosHoje = $pdo->prepare("
            SELECT COUNT(*) FROM cop_laudos
            WHERE medico_id = :mid AND tenant_id = :tid AND DATE(created_at) = CURDATE()
        ");
        $laudosHoje->execute(['mid' => $medicoId, 'tid' => $tenantId]);
        $laudosHoje = (int) $laudosHoje->fetchColumn();

        $laudosMes = $pdo->prepare("
            SELECT COUNT(*) FROM cop_laudos
            WHERE medico_id = :mid AND tenant_id = :tid
              AND YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())
        ");
        $laudosMes->execute(['mid' => $medicoId, 'tid' => $tenantId]);
        $laudosMes = (int) $laudosMes->fetchColumn();

        $totalTemplates = $pdo->prepare("
            SELECT COUNT(*) FROM cop_templates WHERE tenant_id = :tid AND ativo = 1
        ");
        $totalTemplates->execute(['tid' => $tenantId]);
        $totalTemplates = (int) $totalTemplates->fetchColumn();

        // Últimos laudos
        $ultimosLaudos = $pdo->prepare("
            SELECT l.id, l.status, l.created_at, l.assinado_em,
                   w.patient_nome, w.modalidade, w.study_uid
            FROM cop_laudos l
            JOIN cop_workspaces w ON w.id = l.workspace_id
            WHERE l.medico_id = :mid AND l.tenant_id = :tid
            ORDER BY l.created_at DESC
            LIMIT 8
        ");
        $ultimosLaudos->execute(['mid' => $medicoId, 'tid' => $tenantId]);
        $ultimosLaudos = $ultimosLaudos->fetchAll();

        // Perfil do médico
        $perfil = $pdo->prepare("
            SELECT * FROM cop_medico_perfil WHERE user_id = :uid AND tenant_id = :tid LIMIT 1
        ");
        $perfil->execute(['uid' => $medicoId, 'tid' => $tenantId]);
        $perfil = $perfil->fetch();

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
