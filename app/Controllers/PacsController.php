<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Middlewares\AuthMiddleware;
use App\Services\PacsService;

class PacsController extends Controller {

    /**
     * Página de configuração de integração PACS (tenant)
     */
    public function index(): void {
        AuthMiddleware::handle();
        $pdo      = Database::getInstance();
        $tenantId = Auth::tenantId();

        $config = null;
        if ($tenantId) {
            $stmt = $pdo->prepare("SELECT pacs_api_url, pacs_api_token FROM cop_tenants WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $tenantId]);
            $config = $stmt->fetch();
        }

        $this->view('pacs/index', [
            'title'        => 'Integração PACS — VOXEL Copilot',
            'pageTitle'    => 'Integração PACS',
            'pageSubtitle' => 'Configure a conexão com seu PACS',
            'config'       => $config,
            'csrf_token'   => $this->csrfToken(),
        ]);
    }

    /**
     * Salva configuração de integração PACS
     */
    public function salvar(): void {
        AuthMiddleware::handle();
        $pdo      = Database::getInstance();
        $tenantId = Auth::tenantId();

        if (!$tenantId) {
            $this->redirect('/pacs?erro=sem_tenant');
            return;
        }

        $url   = trim($_POST['pacs_api_url']   ?? '');
        $token = trim($_POST['pacs_api_token'] ?? '');

        $pdo->prepare("UPDATE cop_tenants SET pacs_api_url = :url, pacs_api_token = :token, updated_at = NOW() WHERE id = :id")
            ->execute(['url' => $url ?: null, 'token' => $token ?: null, 'id' => $tenantId]);

        $this->redirect('/pacs?ok=1');
    }

    /**
     * API AJAX — busca estudos no PACS
     * GET /api/pacs/buscar?q=TERMO
     */
    public function buscar(): void {
        AuthMiddleware::handle();

        $q        = trim($_GET['q'] ?? '');
        $tenantId = Auth::tenantId();

        // Sem PACS configurado ou sem query → retorna array vazio
        if (!$q || strlen($q) < 2) {
            $this->json(['ok' => true, 'estudos' => []]);
            return;
        }

        // Tenta buscar via PACS real se tenant tiver configuração
        if ($tenantId) {
            try {
                $pdo    = Database::getInstance();
                $stmt   = $pdo->prepare("SELECT pacs_api_url, pacs_api_token FROM cop_tenants WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => $tenantId]);
                $config = $stmt->fetch();

                if ($config && $config->pacs_api_url) {
                    $pacs    = new PacsService($config->pacs_api_url, $config->pacs_api_token ?? '');
                    $estudos = $pacs->buscarEstudos($q);
                    $this->json(['ok' => true, 'estudos' => $estudos]);
                    return;
                }
            } catch (\Exception $e) {
                // Fallback para modo demo abaixo
            }
        }

        // Modo demo — retorna estudos simulados para desenvolvimento
        $demo = [
            [
                'study_uid'    => '1.2.840.10008.5.1.4.1.1.2.' . rand(1000,9999),
                'patient_nome' => 'PACIENTE DEMO ' . strtoupper($q),
                'patient_uid'  => 'PAC' . rand(10000,99999),
                'modalidade'   => 'TC',
                'data_estudo'  => date('Y-m-d'),
                'descricao'    => 'TC Tórax com contraste',
                'instituicao'  => 'VOXEL Demo',
            ],
            [
                'study_uid'    => '1.2.840.10008.5.1.4.1.1.4.' . rand(1000,9999),
                'patient_nome' => 'PACIENTE DEMO ' . strtoupper($q),
                'patient_uid'  => 'PAC' . rand(10000,99999),
                'modalidade'   => 'RM',
                'data_estudo'  => date('Y-m-d', strtotime('-7 days')),
                'descricao'    => 'RM Encéfalo sem contraste',
                'instituicao'  => 'VOXEL Demo',
            ],
        ];

        $this->json(['ok' => true, 'estudos' => $demo, 'demo' => true]);
    }
}
