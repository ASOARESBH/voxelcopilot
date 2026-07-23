<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Services\CopilotAIService;
use App\Services\ReportEngineService;

class CopilotApiController extends Controller {

    public function chat(): void {
        AuthMiddleware::handle();

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
        AuthMiddleware::handle();

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

    /**
     * Report Engine — endpoint unificado para:
     *   - tecnica_auto : gera texto de técnica padrão para a modalidade
     *   - dicionario   : aplica dicionário radiológico ao texto
     *   - revisar      : revisão completa do laudo via IA
     *   - impressao    : gera Impressão Diagnóstica com base nos achados
     */
    public function reportEngine(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $workspaceId  = (int)($_POST['workspace_id']  ?? 0);
        $acao         = trim($_POST['acao']            ?? '');
        $modalidade   = trim($_POST['modalidade']      ?? '');
        $indicacao    = trim($_POST['indicacao']       ?? '');
        $tecnica      = trim($_POST['tecnica']         ?? '');
        $achados      = trim($_POST['achados']         ?? '');
        $impressao    = trim($_POST['impressao']       ?? '');
        $recomendacao = trim($_POST['recomendacao']    ?? '');
        $texto        = trim($_POST['texto']           ?? '');

        $engine = new ReportEngineService();

        switch ($acao) {

            // ── Técnica automática por modalidade ──
            case 'tecnica_auto':
                if (!$modalidade) {
                    echo json_encode(['ok' => false, 'error' => 'Modalidade não informada.']);
                    return;
                }
                $tecnicaGerada = $engine->getTecnicaPadrao($modalidade);
                if (!$tecnicaGerada) {
                    echo json_encode(['ok' => false, 'error' => "Técnica padrão não disponível para a modalidade {$modalidade}."]);
                    return;
                }
                echo json_encode(['ok' => true, 'tecnica' => $tecnicaGerada]);
                return;

            // ── Dicionário radiológico ──
            case 'dicionario':
                if (!$texto) {
                    echo json_encode(['ok' => false, 'error' => 'Texto não informado.']);
                    return;
                }
                $textoNormalizado = $engine->aplicarDicionario($texto);
                echo json_encode(['ok' => true, 'texto' => $textoNormalizado]);
                return;

            // ── Revisão completa do laudo via IA ──
            case 'revisar':
                if (!$workspaceId) {
                    echo json_encode(['ok' => false, 'error' => 'workspace_id obrigatório.']);
                    return;
                }
                $ctx = [
                    'workspace_id' => $workspaceId,
                    'acao'         => 'revisar',
                    'modalidade'   => $modalidade,
                    'indicacao'    => $indicacao,
                    'tecnica'      => $tecnica,
                    'achados'      => $achados,
                    'impressao'    => $impressao,
                    'recomendacao' => $recomendacao,
                ];
                $promptJson = $engine->buildPrompt($ctx);
                $promptData = json_decode($promptJson, true);

                $ai = new CopilotAIService();
                $result = $ai->chatComPrompt(
                    $workspaceId,
                    $promptData['user'],
                    $promptData['system']
                );
                echo json_encode($result);
                return;

            // ── Gerar Impressão Diagnóstica ──
            case 'impressao':
                if (!$workspaceId) {
                    echo json_encode(['ok' => false, 'error' => 'workspace_id obrigatório.']);
                    return;
                }
                $ctx = [
                    'workspace_id' => $workspaceId,
                    'acao'         => 'impressao',
                    'modalidade'   => $modalidade,
                    'indicacao'    => $indicacao,
                    'achados'      => $achados,
                ];
                $promptJson = $engine->buildPrompt($ctx);
                $promptData = json_decode($promptJson, true);

                $ai = new CopilotAIService();
                $result = $ai->chatComPrompt(
                    $workspaceId,
                    $promptData['user'],
                    $promptData['system']
                );
                echo json_encode($result);
                return;

            default:
                echo json_encode(['ok' => false, 'error' => "Ação desconhecida: {$acao}"]);
                return;
        }
    }
}
