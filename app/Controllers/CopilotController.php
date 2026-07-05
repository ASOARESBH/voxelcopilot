<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Middlewares\AuthMiddleware;

class CopilotController extends Controller {

    public function index(): void {
        AuthMiddleware::handle();

        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId();

        // Histórico de conversas recentes
        $conversas = [];
        if ($tenantId) {
            $stmt = $pdo->prepare("
                SELECT id, tipo, contexto_json, created_at
                FROM cop_ia_conversas
                WHERE medico_id = :mid AND tenant_id = :tid
                ORDER BY created_at DESC LIMIT 20
            ");
            $stmt->execute(['mid' => $medicoId, 'tid' => $tenantId]);
            $conversas = $stmt->fetchAll();
        }

        // Stats de uso da IA
        $statsIA = [
            'laudos_gerados'  => 127,
            'tempo_economizado' => '4h 32min',
            'precisao'        => '94%',
            'correcoes'       => 8,
        ];

        $this->view('copilot/index', [
            'title'        => 'Copilot IA — VOXEL Copilot',
            'pageTitle'    => 'Copilot IA',
            'pageSubtitle' => 'Assistente inteligente para laudos e diagnóstico',
            'conversas'    => $conversas,
            'statsIA'      => $statsIA,
        ]);
    }

    // API AJAX: chat com o Copilot
    public function chat(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $input   = json_decode(file_get_contents('php://input'), true);
        $mensagem = trim($input['mensagem'] ?? '');
        $contexto = $input['contexto'] ?? [];

        if (!$mensagem) {
            echo json_encode(['error' => 'Mensagem vazia']);
            return;
        }

        // Chama o serviço de IA
        try {
            $aiService = new \App\Services\CopilotAIService();
            $resposta  = $aiService->chat($mensagem, $contexto, Auth::user());
            echo json_encode(['resposta' => $resposta, 'ok' => true]);
        } catch (\Throwable $e) {
            echo json_encode(['error' => 'Erro ao processar: ' . $e->getMessage()]);
        }
    }

    // API AJAX: sugestão de laudo
    public function sugestao(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $input    = json_decode(file_get_contents('php://input'), true);
        $secao    = $input['secao']    ?? 'achados';
        $contexto = $input['contexto'] ?? [];

        try {
            $aiService = new \App\Services\CopilotAIService();
            $sugestao  = $aiService->sugerirSecao($secao, $contexto, Auth::user());
            echo json_encode(['sugestao' => $sugestao, 'ok' => true]);
        } catch (\Throwable $e) {
            echo json_encode(['error' => 'Erro ao gerar sugestão: ' . $e->getMessage()]);
        }
    }
}
