<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Middlewares\TenantMiddleware;
use App\Services\CopilotAIService;

class CopilotApiController extends Controller {

    public function chat(): void {
        TenantMiddleware::handle();

        $workspaceId = (int)($_POST['workspace_id'] ?? 0);
        $mensagem    = trim($_POST['mensagem'] ?? '');

        if (!$workspaceId || !$mensagem) {
            $this->json(['ok' => false, 'error' => 'Parâmetros inválidos.'], 400);
        }

        $ai     = new CopilotAIService();
        $result = $ai->chat($workspaceId, $mensagem);

        $this->json($result);
    }

    public function sugestao(): void {
        TenantMiddleware::handle();

        $workspaceId = (int)($_POST['workspace_id'] ?? 0);
        $modalidade  = trim($_POST['modalidade']   ?? '');
        $indicacao   = trim($_POST['indicacao']    ?? '');
        $achados     = trim($_POST['achados']      ?? '');

        if (!$workspaceId) {
            $this->json(['ok' => false, 'error' => 'workspace_id obrigatório.'], 400);
        }

        $ai     = new CopilotAIService();
        $result = $ai->gerarSugestao($workspaceId, $modalidade, $indicacao, $achados);

        $this->json($result);
    }
}
