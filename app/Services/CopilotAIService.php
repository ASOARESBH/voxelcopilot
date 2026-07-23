<?php
namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Logger;

class CopilotAIService {

    private string $apiKey;
    private string $model;
    private string $baseUrl;
    private string $providerType = 'openai';

    public function __construct() {
        $this->apiKey  = '';
        $this->model   = 'gpt-4o';
        $this->baseUrl = 'https://api.openai.com/v1';
        $this->resolveApiKey();
    }

    /**
     * Resolve a chave de API na seguinte ordem de prioridade:
     * 1. Provider padrão do AI Router (cop_ai_providers) do médico logado
     * 2. Variável de ambiente OPENAI_API_KEY (fallback global)
     */
    private function resolveApiKey(): void {
        try {
            $userId = Auth::userId();
            if ($userId) {
                $pdo  = Database::getInstance();
                // Busca provider padrão ativo do médico no AI Router
                $stmt = $pdo->prepare("
                    SELECT id, api_key_enc, provider_type, endpoint, temperatura, max_tokens
                    FROM cop_ai_providers
                    WHERE user_id = :uid
                      AND wizard_completo = 1
                    ORDER BY is_default DESC, updated_at DESC
                    LIMIT 1
                ");
                $stmt->execute(['uid' => $userId]);
                $provider = $stmt->fetch(\PDO::FETCH_OBJ);

                if ($provider && !empty($provider->api_key_enc)) {
                    $decrypted = $this->decrypt($provider->api_key_enc);
                    if ($decrypted) {
                        $this->apiKey       = $decrypted;
                        $this->providerType = $provider->provider_type;
                        $this->model        = $this->resolveDefaultModel($provider->provider_type);

                        // Endpoint customizado para providers não-OpenAI
                        if (!empty($provider->endpoint)) {
                            $this->baseUrl = rtrim($provider->endpoint, '/');
                        } else {
                            $this->baseUrl = $this->resolveBaseUrl($provider->provider_type);
                        }

                        Logger::info('CopilotAI: usando provider do AI Router', [
                            'provider_id'   => $provider->id,
                            'provider_type' => $provider->provider_type,
                            'user_id'       => $userId,
                        ]);
                        return;
                    }
                }
            }
        } catch (\Throwable $e) {
            Logger::error('CopilotAI resolveApiKey error', ['error' => $e->getMessage()]);
        }

        // Fallback: variável de ambiente
        $this->apiKey       = $_ENV['OPENAI_API_KEY'] ?? '';
        $this->model        = $_ENV['OPENAI_MODEL']   ?? 'gpt-4o';
        $this->baseUrl      = $_ENV['OPENAI_API_BASE'] ?? 'https://api.openai.com/v1';
        $this->providerType = 'openai';
    }

    private function resolveDefaultModel(string $type): string {
        $map = [
            'openai'     => 'gpt-4o',
            'anthropic'  => 'claude-3-5-sonnet-20241022',
            'google'     => 'gemini-1.5-pro',
            'azure'      => 'gpt-4o',
            'deepseek'   => 'deepseek-chat',
            'mistral'    => 'mistral-large-latest',
            'openrouter' => 'openai/gpt-4o',
            'ollama'     => 'llama3.2',
            'lmstudio'   => 'local-model',
            'qwen'       => 'qwen-turbo',
        ];
        return $map[$type] ?? 'gpt-4o';
    }

    private function resolveBaseUrl(string $type): string {
        $map = [
            'openai'     => 'https://api.openai.com/v1',
            'anthropic'  => 'https://api.anthropic.com/v1',
            'google'     => 'https://generativelanguage.googleapis.com/v1beta/openai',
            'deepseek'   => 'https://api.deepseek.com/v1',
            'mistral'    => 'https://api.mistral.ai/v1',
            'openrouter' => 'https://openrouter.ai/api/v1',
            'ollama'     => 'http://localhost:11434/v1',
            'lmstudio'   => 'http://localhost:1234/v1',
        ];
        return $map[$type] ?? 'https://api.openai.com/v1';
    }

    /**
     * Descriptografa a API Key salva pelo ProviderWizardController (AES-256-CBC)
     *
     * O ProviderWizardController usa:
     *   $iv  = openssl_random_pseudo_bytes(16);          // IV em bytes brutos (16 bytes)
     *   $enc = openssl_encrypt($text, 'AES-256-CBC', $key, 0, $iv);  // flag=0: retorna base64
     *   return base64_encode($iv . $enc);                // salva: base64( IV_raw + cipher_base64 )
     *
     * Portanto para descriptografar:
     *   1. Decodifica o outer base64 => obtém IV_raw(16) + cipher_base64(restante)
     *   2. Descriptografa com flag=0 (openssl espera input em base64)
     */
    private function decrypt(string $encrypted): string {
        try {
            // Mesma lógica do ProviderWizardController: $_ENV primeiro, depois constante, depois fallback
            $appKey = $_ENV['APP_KEY'] ?? (defined('APP_KEY') ? APP_KEY : 'voxelcopilot_aes_key_2026');
            $encKey = hash('sha256', $appKey, true);

            // Decodifica o outer base64
            $data = base64_decode($encrypted);
            if (strlen($data) <= 16) {
                Logger::error('CopilotAI decrypt: dado muito curto', ['len' => strlen($data)]);
                return '';
            }

            // Os primeiros 16 bytes são o IV (raw bytes)
            $iv = substr($data, 0, 16);

            // O restante é o cipher em base64 (gerado por openssl_encrypt com flag=0)
            $cipherBase64 = substr($data, 16);

            // Descriptografa com flag=0 (mesmo flag usado no encrypt)
            $result = openssl_decrypt($cipherBase64, 'AES-256-CBC', $encKey, 0, $iv);

            if ($result === false) {
                Logger::error('CopilotAI decrypt falhou (openssl_decrypt retornou false)', [
                    'encrypted_len'  => strlen($encrypted),
                    'data_len'       => strlen($data),
                    'cipher_b64_len' => strlen($cipherBase64),
                    'app_key_src'    => defined('APP_KEY') ? 'APP_KEY_const' : 'env_fallback',
                    'app_key_hash'   => substr(bin2hex($encKey), 0, 8) . '...',
                ]);
                return '';
            }
            return $result;
        } catch (\Throwable $e) {
            Logger::error('CopilotAI decrypt error', ['error' => $e->getMessage()]);
            return '';
        }
    }

    /**
     * Retorna informações sobre o provider ativo (para exibir na UI)
     */
    public function getProviderInfo(): array {
        return [
            'type'    => $this->providerType,
            'model'   => $this->model,
            'baseUrl' => $this->baseUrl,
            'hasKey'  => !empty($this->apiKey),
        ];
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
        $tenantId = $this->resolveTenantId($medicoId);

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

            return ['ok' => true, 'content' => $content, 'tokens' => $tokens, 'model' => $this->model];

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
        $tenantId = $this->resolveTenantId($medicoId);
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

            return ['ok' => true, 'content' => $content, 'tokens' => $tokens, 'model' => $this->model];

        } catch (\Throwable $e) {
            Logger::error('CopilotAI chat error', ['error' => $e->getMessage()]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Resolve o tenant_id do usuário logado com fallback robusto.
     * Garante que nunca retorna null para evitar violação de constraint no banco.
     */
    private function resolveTenantId(?int $userId): int {
        // 1. Tenta da sessão (setado pelo login ou impersonação)
        $tenantId = Auth::tenantId();
        if ($tenantId) return $tenantId;

        // 2. Fallback: busca direto no banco pelo primeiro tenant ativo do usuário
        if ($userId) {
            try {
                $pdo  = Database::getInstance();
                $stmt = $pdo->prepare("
                    SELECT ut.tenant_id FROM cop_user_tenants ut
                    JOIN cop_tenants t ON t.id = ut.tenant_id
                    WHERE ut.user_id = :uid AND ut.ativo = 1 AND t.status = 'ativo'
                    ORDER BY ut.id ASC LIMIT 1
                ");
                $stmt->execute(['uid' => $userId]);
                $row = $stmt->fetch();
                if ($row) {
                    $tenantId = (int)$row->tenant_id;
                    // Seta na sessão para próximas requisições
                    Auth::setTenant($tenantId);
                    return $tenantId;
                }
            } catch (\Throwable $e) {
                Logger::error('CopilotAI resolveTenantId error', ['error' => $e->getMessage()]);
            }
        }

        // 3. Último recurso: retorna 0 (evita NULL no banco, mas loga o problema)
        Logger::error('CopilotAI: tenant_id não resolvido', ['user_id' => $userId]);
        return 0;
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
            'curta'     => 'Seja conciso e objetivo. Conclusões curtas e diretas.',
            'normal'    => 'Use linguagem médica padrão, clara e objetiva.',
            'detalhada' => 'Seja detalhado e descritivo, incluindo achados negativos relevantes.',
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
            throw new \RuntimeException('Chave de API não configurada. Configure um provider no AI Router (Gestão → AI Router → Providers) ou acesse Configurações → Configurações de IA.');
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
        if ($code >= 400) throw new \RuntimeException("API error {$code}: {$body}");

        $decoded = json_decode($body, true);
        if (!$decoded) throw new \RuntimeException("Resposta inválida da API de IA.");

        return $decoded;
    }
}
