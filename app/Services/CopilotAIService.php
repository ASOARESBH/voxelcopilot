<?php
namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Logger;

class CopilotAIService {

    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct() {
        $this->apiKey  = $_ENV['OPENAI_API_KEY'] ?? '';
        $this->model   = $_ENV['OPENAI_MODEL']   ?? 'gpt-4o';
        $this->baseUrl = $_ENV['OPENAI_API_BASE'] ?? 'https://api.openai.com/v1';
    }

    /**
     * Gera sugestão de laudo baseada no contexto do estudo e perfil do médico
     */
    public function gerarSugestao(
        int    $workspaceId,
        string $modalidade,
        string $indicacao,
        string $achados = ''
    ): array {
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId();

        // Carrega perfil do médico para personalização
        $pdo    = Database::getInstance();
        $perfil = $pdo->prepare("SELECT * FROM cop_medico_perfil WHERE user_id = :uid AND tenant_id = :tid LIMIT 1");
        $perfil->execute(['uid' => $medicoId, 'tid' => $tenantId]);
        $perfil = $perfil->fetch();

        $estilo = $perfil?->estilo_conclusao ?? 'normal';
        $vocab  = $perfil?->vocabulario_json ? json_decode($perfil->vocabulario_json, true) : [];

        $systemPrompt = $this->buildSystemPrompt($estilo, $vocab);
        $userPrompt   = $this->buildUserPrompt($modalidade, $indicacao, $achados);

        // Carrega histórico de conversa
        $historico = $pdo->prepare("
            SELECT role, conteudo FROM cop_ia_conversas
            WHERE workspace_id = :wid ORDER BY created_at ASC LIMIT 20
        ");
        $historico->execute(['wid' => $workspaceId]);
        $historico = $historico->fetchAll();

        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($historico as $h) {
            $messages[] = ['role' => $h->role, 'content' => $h->conteudo];
        }
        $messages[] = ['role' => 'user', 'content' => $userPrompt];

        try {
            $response = $this->callOpenAI($messages);
            $content  = $response['choices'][0]['message']['content'] ?? '';
            $tokens   = $response['usage']['total_tokens'] ?? 0;

            // Salva no histórico
            $pdo->prepare("
                INSERT INTO cop_ia_conversas (workspace_id, tenant_id, medico_id, role, conteudo, modelo, tokens, created_at)
                VALUES (:wid, :tid, :mid, 'user', :user_msg, :model, 0, NOW())
            ")->execute([
                'wid'      => $workspaceId,
                'tid'      => $tenantId,
                'mid'      => $medicoId,
                'user_msg' => $userPrompt,
                'model'    => $this->model,
            ]);

            $pdo->prepare("
                INSERT INTO cop_ia_conversas (workspace_id, tenant_id, medico_id, role, conteudo, modelo, tokens, created_at)
                VALUES (:wid, :tid, :mid, 'assistant', :content, :model, :tokens, NOW())
            ")->execute([
                'wid'     => $workspaceId,
                'tid'     => $tenantId,
                'mid'     => $medicoId,
                'content' => $content,
                'model'   => $this->model,
                'tokens'  => $tokens,
            ]);

            return ['ok' => true, 'content' => $content, 'tokens' => $tokens];

        } catch (\Throwable $e) {
            Logger::error('CopilotAI error', ['error' => $e->getMessage()]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Chat livre com o Copilot
     */
    public function chat(int $workspaceId, string $mensagem): array {
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId();
        $pdo      = Database::getInstance();

        $historico = $pdo->prepare("
            SELECT role, conteudo FROM cop_ia_conversas
            WHERE workspace_id = :wid ORDER BY created_at ASC LIMIT 30
        ");
        $historico->execute(['wid' => $workspaceId]);
        $historico = $historico->fetchAll();

        $messages = [['role' => 'system', 'content' => $this->buildSystemPrompt()]];
        foreach ($historico as $h) {
            $messages[] = ['role' => $h->role, 'content' => $h->conteudo];
        }
        $messages[] = ['role' => 'user', 'content' => $mensagem];

        try {
            $response = $this->callOpenAI($messages);
            $content  = $response['choices'][0]['message']['content'] ?? '';
            $tokens   = $response['usage']['total_tokens'] ?? 0;

            // Salva conversa
            $pdo->prepare("
                INSERT INTO cop_ia_conversas (workspace_id, tenant_id, medico_id, role, conteudo, modelo, tokens, created_at)
                VALUES (:wid, :tid, :mid, 'user', :msg, :model, 0, NOW())
            ")->execute(['wid'=>$workspaceId,'tid'=>$tenantId,'mid'=>$medicoId,'msg'=>$mensagem,'model'=>$this->model]);

            $pdo->prepare("
                INSERT INTO cop_ia_conversas (workspace_id, tenant_id, medico_id, role, conteudo, modelo, tokens, created_at)
                VALUES (:wid, :tid, :mid, 'assistant', :content, :model, :tokens, NOW())
            ")->execute(['wid'=>$workspaceId,'tid'=>$tenantId,'mid'=>$medicoId,'content'=>$content,'model'=>$this->model,'tokens'=>$tokens]);

            return ['ok' => true, 'content' => $content, 'tokens' => $tokens];

        } catch (\Throwable $e) {
            Logger::error('CopilotAI chat error', ['error' => $e->getMessage()]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function buildSystemPrompt(string $estilo = 'normal', array $vocab = []): string {
        $vocabStr = '';
        if (!empty($vocab)) {
            $vocabStr = "\n\nVocabulário preferido do médico:\n";
            foreach ($vocab as $original => $preferido) {
                $vocabStr .= "- Use '{$preferido}' em vez de '{$original}'\n";
            }
        }

        $estiloMap = [
            'curta'    => 'Seja conciso e objetivo. Conclusões curtas e diretas.',
            'normal'   => 'Use linguagem médica padrão, clara e objetiva.',
            'detalhada'=> 'Seja detalhado e descritivo, incluindo achados negativos relevantes.',
        ];
        $estiloInstr = $estiloMap[$estilo] ?? $estiloMap['normal'];

        return <<<PROMPT
Você é o VOXEL Copilot, um assistente especializado em laudos de diagnóstico por imagem.

Suas responsabilidades:
- Auxiliar médicos radiologistas na elaboração de laudos estruturados
- Sugerir achados, impressões diagnósticas e recomendações baseadas em evidências
- Identificar inconsistências e erros no laudo (lateralidade, CID, terminologia)
- Aprender e se adaptar ao estilo de cada médico

Estilo de redação: {$estiloInstr}

Sempre use terminologia médica em português brasileiro.
Estruture o laudo em seções: Indicação, Técnica, Achados, Impressão, Recomendações.
Não invente achados — baseie-se apenas nas informações fornecidas.
Quando não houver informação suficiente, solicite esclarecimentos.{$vocabStr}
PROMPT;
    }

    private function buildUserPrompt(string $modalidade, string $indicacao, string $achados): string {
        $prompt = "Modalidade: {$modalidade}\n";
        if ($indicacao) $prompt .= "Indicação clínica: {$indicacao}\n";
        if ($achados)   $prompt .= "Achados preliminares: {$achados}\n";
        $prompt .= "\nGere uma sugestão de laudo estruturado com base nas informações acima.";
        return $prompt;
    }

    private function callOpenAI(array $messages): array {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('Chave de API OpenAI não configurada.');
        }

        $payload = json_encode([
            'model'       => $this->model,
            'messages'    => $messages,
            'temperature' => 0.3,
            'max_tokens'  => 2000,
        ]);

        $ch = curl_init("{$this->baseUrl}/chat/completions");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$this->apiKey}",
                "Content-Type: application/json",
            ],
        ]);

        $body  = curl_exec($ch);
        $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) throw new \RuntimeException("cURL error: {$error}");
        if ($code >= 400) throw new \RuntimeException("OpenAI API error {$code}: {$body}");

        $decoded = json_decode($body, true);
        if (!$decoded) throw new \RuntimeException("Resposta inválida da API de IA.");

        return $decoded;
    }
}
