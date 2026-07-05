<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Middlewares\AuthMiddleware;

class PesquisaController extends Controller {

    public function index(): void {
        AuthMiddleware::handle();

        $query = trim($_GET['q'] ?? '');
        $resultados = [];

        if ($query) {
            // Simulação de busca em base de conhecimento clínico
            $resultados = $this->buscarConhecimento($query);
        }

        $this->view('pesquisa/index', [
            'title'        => 'Pesquisa Clínica — VOXEL Copilot',
            'pageTitle'    => 'Pesquisa Clínica',
            'pageSubtitle' => 'Base de conhecimento em diagnóstico por imagem',
            'query'        => $query,
            'resultados'   => $resultados,
        ]);
    }

    public function buscar(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $query = trim($input['query'] ?? '');

        if (!$query) {
            echo json_encode(['error' => 'Query vazia']);
            return;
        }

        try {
            // Busca com IA (RAG simulado)
            $apiKey = $_ENV['OPENAI_API_KEY'] ?? '';
            if (!$apiKey) throw new \Exception('API Key não configurada');

            $client = new \OpenAI\Client($apiKey);
            $response = $client->chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'Você é um especialista em diagnóstico por imagem. Responda de forma técnica e baseada em evidências, citando referências quando possível. Responda em português.'],
                    ['role' => 'user', 'content' => $query],
                ],
                'max_tokens' => 800,
            ]);

            $resposta = $response->choices[0]->message->content ?? 'Sem resposta';
            echo json_encode(['ok' => true, 'resposta' => $resposta]);
        } catch (\Throwable $e) {
            // Fallback com resposta simulada
            echo json_encode(['ok' => true, 'resposta' => $this->respostaSimulada($query)]);
        }
    }

    private function buscarConhecimento(string $query): array {
        return [
            ['titulo' => 'Nódulo Pulmonar Solitário — Critérios de Fleischner', 'fonte' => 'Fleischner Society 2017', 'relevancia' => 95, 'resumo' => 'Nódulos < 6mm em baixo risco não requerem seguimento. Nódulos 6-8mm requerem TC em 6-12 meses.'],
            ['titulo' => 'Classificação BIRADS — ACR 2013', 'fonte' => 'American College of Radiology', 'relevancia' => 88, 'resumo' => 'BIRADS 0: avaliação incompleta. BIRADS 1: negativo. BIRADS 2: benigno. BIRADS 3: provavelmente benigno (< 2% malignidade).'],
            ['titulo' => 'AVC Isquêmico — Protocolo de Neuroimagem', 'fonte' => 'AHA/ASA Guidelines 2023', 'relevancia' => 82, 'resumo' => 'TC sem contraste é o exame inicial. DWI na RM é mais sensível nas primeiras 24h. Janela terapêutica para trombólise: 4,5h.'],
        ];
    }

    private function respostaSimulada(string $query): string {
        return "Com base na literatura de diagnóstico por imagem, a consulta sobre **{$query}** envolve os seguintes aspectos clínicos relevantes:\n\n" .
               "1. **Achados típicos**: Os padrões de imagem mais frequentemente associados incluem alterações de densidade/sinal características da condição.\n\n" .
               "2. **Diagnóstico diferencial**: Deve-se considerar as principais entidades que podem apresentar padrão similar.\n\n" .
               "3. **Recomendações**: Seguir protocolos das sociedades de radiologia (ACR, CBR) para conduta e seguimento.\n\n" .
               "*Configure sua chave OpenAI no .env para respostas baseadas em IA real.*";
    }
}
