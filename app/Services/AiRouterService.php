<?php
namespace App\Services;

use App\Core\Auth;
use App\Core\Database;

/**
 * AI Router Service
 * Camada central de inteligência do VOXEL Copilot.
 * NENHUM módulo deve consumir diretamente OpenAI, Claude, Gemini etc.
 * Toda comunicação passa obrigatoriamente por este serviço.
 */
class AiRouterService {

    /**
     * Endpoint único do AI Router
     * POST /api/ai/router
     *
     * @param array $params {
     *   provider: string (openai|anthropic|google_gemini|azure_openai|ollama|deepseek|...),
     *   model: string,
     *   prompt: string,
     *   temperature: float,
     *   tokens: int,
     *   workspace_id: int|null,
     *   tipo: string (gerar_laudo|comparacao|pesquisa|resumo|...)
     * }
     */
    public static function route(array $params): array {
        $startTime = microtime(true);
        $userId    = Auth::userId();
        $tenantId  = Auth::tenantId();

        // Resolve provider — usa rota inteligente se não especificado
        $provider = self::resolveProvider($params, $userId, $tenantId);

        if (!$provider) {
            return [
                'ok'    => false,
                'erro'  => 'Nenhum provider de IA configurado. Configure um provider no AI Router.',
                'code'  => 'NO_PROVIDER',
            ];
        }

        // Monta o prompt completo com Prompt Base se disponível
        $promptFinal = self::buildPrompt($params, $provider, $userId);

        // Executa a chamada ao provider
        try {
            $resposta = self::callProvider($provider, $promptFinal, $params);
        } catch (\Exception $e) {
            self::log($userId, $tenantId, 'error', $provider['nome'] ?? 'unknown', $provider['modelo'] ?? '', 'route', $e->getMessage());
            self::registrarHistorico($userId, $tenantId, $params, $provider, $promptFinal, '', 0, 0, 0, 0, microtime(true) - $startTime, 'erro', $e->getMessage());
            return ['ok' => false, 'erro' => $e->getMessage(), 'code' => 'PROVIDER_ERROR'];
        }

        $tempoMs       = (int)((microtime(true) - $startTime) * 1000);
        $tokensInput   = $resposta['tokens_input']  ?? 0;
        $tokensOutput  = $resposta['tokens_output'] ?? 0;
        $tokensTotal   = $tokensInput + $tokensOutput;
        $custoUsd      = self::calcularCusto($provider, $tokensInput, $tokensOutput);

        // Registra no histórico
        $historicoId = self::registrarHistorico(
            $userId, $tenantId, $params, $provider,
            $promptFinal, $resposta['texto'] ?? '',
            $tokensInput, $tokensOutput, $tokensTotal, $custoUsd,
            $tempoMs, 'ok', null
        );

        // Atualiza custo diário
        self::atualizarCustoDiario($userId, $tenantId, $provider, $tokensInput, $tokensOutput, $tokensTotal, $custoUsd, $tempoMs);

        // Atualiza última utilização do provider
        self::atualizarProvider($provider['id'] ?? null, $tempoMs);

        return [
            'ok'           => true,
            'texto'        => $resposta['texto'] ?? '',
            'provider'     => $provider['nome'] ?? '',
            'modelo'       => $provider['modelo'] ?? '',
            'tokens_input' => $tokensInput,
            'tokens_output'=> $tokensOutput,
            'tokens_total' => $tokensTotal,
            'custo_usd'    => $custoUsd,
            'tempo_ms'     => $tempoMs,
            'historico_id' => $historicoId,
        ];
    }

    /**
     * Resolve qual provider usar baseado na rota inteligente ou parâmetro direto
     */
    private static function resolveProvider(array $params, int $userId, ?int $tenantId): ?array {
        $pdo = Database::getInstance();

        // 1. Provider explicitamente especificado por ID
        if (!empty($params['provider_id'])) {
            $stmt = $pdo->prepare("SELECT * FROM cop_ai_providers WHERE id = :id AND user_id = :uid AND is_active = 1 LIMIT 1");
            $stmt->execute(['id' => (int)$params['provider_id'], 'uid' => $userId]);
            $p = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($p) {
                $p['modelo'] = $params['model'] ?? $p['modelo_padrao'];
                return $p;
            }
        }

        // 2. Rota inteligente por tipo de solicitação
        $tipo = $params['tipo'] ?? 'gerar_laudo';
        $stmt = $pdo->prepare("
            SELECT r.*, p.nome, p.provider_tipo, p.api_key, p.endpoint, p.temperatura as temp_provider
            FROM cop_ai_rotas r
            JOIN cop_ai_providers p ON p.id = r.provider_id
            WHERE r.user_id = :uid AND r.tipo_solicitacao = :tipo AND r.is_active = 1 AND p.is_active = 1
            ORDER BY r.prioridade DESC LIMIT 1
        ");
        $stmt->execute(['uid' => $userId, 'tipo' => $tipo]);
        $rota = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($rota) {
            $rota['modelo'] = $params['model'] ?? $rota['modelo'] ?? $rota['modelo_padrao'] ?? 'gpt-4o';
            return $rota;
        }

        // 3. Provider padrão do usuário
        $stmt = $pdo->prepare("SELECT * FROM cop_ai_providers WHERE user_id = :uid AND is_default = 1 AND is_active = 1 LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        $p = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($p) {
            $p['modelo'] = $params['model'] ?? $p['modelo_padrao'];
            return $p;
        }

        // 4. Qualquer provider ativo do usuário
        $stmt = $pdo->prepare("SELECT * FROM cop_ai_providers WHERE user_id = :uid AND is_active = 1 ORDER BY id ASC LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        $p = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($p) {
            $p['modelo'] = $params['model'] ?? $p['modelo_padrao'];
            return $p;
        }

        // 5. Fallback: usa variável de ambiente OPENAI_API_KEY se disponível
        $envKey = getenv('OPENAI_API_KEY');
        if ($envKey) {
            return [
                'id'            => null,
                'nome'          => 'OpenAI (Sistema)',
                'provider_tipo' => 'openai',
                'api_key'       => $envKey,
                'endpoint'      => 'https://api.openai.com/v1',
                'modelo'        => $params['model'] ?? 'gpt-4o',
                'temperatura'   => $params['temperature'] ?? 0.1,
                'max_tokens'    => $params['tokens'] ?? 4000,
                'timeout_seg'   => 120,
            ];
        }

        return null;
    }

    /**
     * Constrói o prompt final com Prompt Base da especialidade
     */
    private static function buildPrompt(array $params, array $provider, int $userId): string {
        $prompt = $params['prompt'] ?? '';

        // Tenta buscar Prompt Base da especialidade
        if (!empty($params['especialidade']) || !empty($provider['prompt_base_id'])) {
            $pdo = Database::getInstance();
            if (!empty($provider['prompt_base_id'])) {
                $stmt = $pdo->prepare("SELECT prompt FROM cop_ai_prompt_base WHERE id = :id AND is_active = 1 LIMIT 1");
                $stmt->execute(['id' => $provider['prompt_base_id']]);
            } else {
                $esp  = $params['especialidade'] ?? 'radiologia_geral';
                $stmt = $pdo->prepare("SELECT prompt FROM cop_ai_prompt_base WHERE user_id = :uid AND especialidade = :esp AND is_active = 1 ORDER BY id DESC LIMIT 1");
                $stmt->execute(['uid' => $userId, 'esp' => $esp]);
            }
            $base = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($base) {
                $prompt = $base['prompt'] . "\n\n---\n\n" . $prompt;
            }
        }

        return $prompt;
    }

    /**
     * Executa a chamada ao provider de IA
     */
    private static function callProvider(array $provider, string $prompt, array $params): array {
        $tipo = $provider['provider_tipo'] ?? 'openai';

        switch ($tipo) {
            case 'openai':
            case 'azure_openai':
                return self::callOpenAI($provider, $prompt, $params);

            case 'anthropic':
                return self::callAnthropic($provider, $prompt, $params);

            case 'google_gemini':
            case 'vertex_ai':
                return self::callGemini($provider, $prompt, $params);

            case 'ollama':
            case 'lm_studio':
                return self::callOllama($provider, $prompt, $params);

            case 'openrouter':
            case 'deepseek':
            case 'mistral':
            case 'qwen':
            case 'amazon_bedrock':
            case 'custom':
                return self::callOpenAICompatible($provider, $prompt, $params);

            default:
                return self::callOpenAI($provider, $prompt, $params);
        }
    }

    /**
     * Chama API compatível com OpenAI (OpenAI, Azure, OpenRouter, DeepSeek, Mistral etc.)
     */
    private static function callOpenAI(array $provider, string $prompt, array $params): array {
        $apiKey    = $provider['api_key'] ?? '';
        $endpoint  = rtrim($provider['endpoint'] ?? 'https://api.openai.com/v1', '/');
        $modelo    = $provider['modelo'] ?? 'gpt-4o';
        $temp      = (float)($params['temperature'] ?? $provider['temperatura'] ?? 0.1);
        $maxTokens = (int)($params['tokens'] ?? $provider['max_tokens'] ?? 4000);
        $timeout   = (int)($provider['timeout_seg'] ?? 120);

        $body = json_encode([
            'model'       => $modelo,
            'messages'    => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $temp,
            'max_tokens'  => $maxTokens,
        ]);

        $ch = curl_init($endpoint . '/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
        ]);

        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err) throw new \RuntimeException('cURL error: ' . $err);
        if ($code !== 200) throw new \RuntimeException('HTTP ' . $code . ': ' . substr($raw, 0, 300));

        $data = json_decode($raw, true);
        if (!$data) throw new \RuntimeException('Resposta inválida do provider');

        return [
            'texto'         => $data['choices'][0]['message']['content'] ?? '',
            'tokens_input'  => $data['usage']['prompt_tokens']     ?? 0,
            'tokens_output' => $data['usage']['completion_tokens'] ?? 0,
        ];
    }

    /**
     * Chama API compatível com OpenAI (mesmo formato, endpoint diferente)
     */
    private static function callOpenAICompatible(array $provider, string $prompt, array $params): array {
        return self::callOpenAI($provider, $prompt, $params);
    }

    /**
     * Chama API Anthropic Claude
     */
    private static function callAnthropic(array $provider, string $prompt, array $params): array {
        $apiKey    = $provider['api_key'] ?? '';
        $endpoint  = rtrim($provider['endpoint'] ?? 'https://api.anthropic.com', '/');
        $modelo    = $provider['modelo'] ?? 'claude-3-5-sonnet-20241022';
        $temp      = (float)($params['temperature'] ?? $provider['temperatura'] ?? 0.1);
        $maxTokens = (int)($params['tokens'] ?? $provider['max_tokens'] ?? 4000);
        $timeout   = (int)($provider['timeout_seg'] ?? 120);

        $body = json_encode([
            'model'      => $modelo,
            'max_tokens' => $maxTokens,
            'temperature'=> $temp,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]);

        $ch = curl_init($endpoint . '/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
            ],
        ]);

        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err) throw new \RuntimeException('cURL error: ' . $err);
        if ($code !== 200) throw new \RuntimeException('HTTP ' . $code . ': ' . substr($raw, 0, 300));

        $data = json_decode($raw, true);
        return [
            'texto'         => $data['content'][0]['text'] ?? '',
            'tokens_input'  => $data['usage']['input_tokens']  ?? 0,
            'tokens_output' => $data['usage']['output_tokens'] ?? 0,
        ];
    }

    /**
     * Chama API Google Gemini
     */
    private static function callGemini(array $provider, string $prompt, array $params): array {
        $apiKey    = $provider['api_key'] ?? '';
        $modelo    = $provider['modelo'] ?? 'gemini-1.5-pro';
        $temp      = (float)($params['temperature'] ?? $provider['temperatura'] ?? 0.1);
        $maxTokens = (int)($params['tokens'] ?? $provider['max_tokens'] ?? 4000);
        $timeout   = (int)($provider['timeout_seg'] ?? 120);

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key={$apiKey}";

        $body = json_encode([
            'contents'          => [['parts' => [['text' => $prompt]]]],
            'generationConfig'  => ['temperature' => $temp, 'maxOutputTokens' => $maxTokens],
        ]);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);

        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err) throw new \RuntimeException('cURL error: ' . $err);
        if ($code !== 200) throw new \RuntimeException('HTTP ' . $code . ': ' . substr($raw, 0, 300));

        $data = json_decode($raw, true);
        $texto = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        return [
            'texto'         => $texto,
            'tokens_input'  => $data['usageMetadata']['promptTokenCount']     ?? 0,
            'tokens_output' => $data['usageMetadata']['candidatesTokenCount'] ?? 0,
        ];
    }

    /**
     * Chama Ollama / LM Studio (endpoint local ou remoto)
     */
    private static function callOllama(array $provider, string $prompt, array $params): array {
        $endpoint  = rtrim($provider['endpoint'] ?? 'http://localhost:11434', '/');
        $modelo    = $provider['modelo'] ?? 'llama3';
        $temp      = (float)($params['temperature'] ?? $provider['temperatura'] ?? 0.1);
        $timeout   = (int)($provider['timeout_seg'] ?? 180);

        $body = json_encode([
            'model'   => $modelo,
            'prompt'  => $prompt,
            'options' => ['temperature' => $temp],
            'stream'  => false,
        ]);

        $ch = curl_init($endpoint . '/api/generate');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);

        $raw = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) throw new \RuntimeException('cURL error: ' . $err);

        $data = json_decode($raw, true);
        return [
            'texto'         => $data['response'] ?? '',
            'tokens_input'  => $data['prompt_eval_count']    ?? 0,
            'tokens_output' => $data['eval_count']           ?? 0,
        ];
    }

    /**
     * Calcula custo estimado em USD
     */
    private static function calcularCusto(array $provider, int $tokensInput, int $tokensOutput): float {
        // Preços padrão por 1k tokens (USD) — fallback se não configurado
        $precos = [
            'openai'         => ['input' => 0.005,  'output' => 0.015],
            'anthropic'      => ['input' => 0.003,  'output' => 0.015],
            'google_gemini'  => ['input' => 0.00125,'output' => 0.005],
            'azure_openai'   => ['input' => 0.005,  'output' => 0.015],
            'deepseek'       => ['input' => 0.00014,'output' => 0.00028],
            'mistral'        => ['input' => 0.002,  'output' => 0.006],
            'openrouter'     => ['input' => 0.003,  'output' => 0.010],
            'ollama'         => ['input' => 0.0,    'output' => 0.0],
            'lm_studio'      => ['input' => 0.0,    'output' => 0.0],
        ];

        $tipo = $provider['provider_tipo'] ?? 'openai';
        $pi   = $precos[$tipo]['input']  ?? 0.005;
        $po   = $precos[$tipo]['output'] ?? 0.015;

        return round(($tokensInput / 1000 * $pi) + ($tokensOutput / 1000 * $po), 6);
    }

    /**
     * Registra chamada no histórico
     */
    private static function registrarHistorico(
        int $userId, ?int $tenantId, array $params, array $provider,
        string $promptEnviado, string $resposta,
        int $tokensInput, int $tokensOutput, int $tokensTotal, float $custoUsd,
        float $tempoSeg, string $status, ?string $erroMsg
    ): int {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare("
                INSERT INTO cop_ai_historico
                    (user_id, tenant_id, workspace_id, provider_id, provider_nome, modelo,
                     tipo_solicitacao, prompt_enviado, resposta,
                     tokens_input, tokens_output, tokens_total, custo_usd,
                     tempo_ms, temperatura, status, erro_msg)
                VALUES
                    (:uid, :tid, :wid, :pid, :pnome, :modelo,
                     :tipo, :prompt, :resp,
                     :ti, :to, :tt, :custo,
                     :tempo, :temp, :status, :erro)
            ");
            $stmt->execute([
                'uid'    => $userId,
                'tid'    => $tenantId,
                'wid'    => $params['workspace_id'] ?? null,
                'pid'    => $provider['id'] ?? null,
                'pnome'  => $provider['nome'] ?? '',
                'modelo' => $provider['modelo'] ?? '',
                'tipo'   => $params['tipo'] ?? 'gerar_laudo',
                'prompt' => substr($promptEnviado, 0, 65535),
                'resp'   => substr($resposta, 0, 65535),
                'ti'     => $tokensInput,
                'to'     => $tokensOutput,
                'tt'     => $tokensTotal,
                'custo'  => $custoUsd,
                'tempo'  => (int)($tempoSeg * 1000),
                'temp'   => $params['temperature'] ?? $provider['temperatura'] ?? 0.1,
                'status' => $status,
                'erro'   => $erroMsg,
            ]);
            return (int)$pdo->lastInsertId();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Atualiza custo diário agregado
     */
    private static function atualizarCustoDiario(
        int $userId, ?int $tenantId, array $provider,
        int $ti, int $to, int $tt, float $custo, int $tempoMs
    ): void {
        try {
            $pdo  = Database::getInstance();
            $hoje = date('Y-m-d');
            $pid  = $provider['id'] ?? null;

            $stmt = $pdo->prepare("SELECT id, total_chamadas, tokens_total, custo_usd, tempo_medio_ms FROM cop_ai_custos_diarios WHERE user_id = :uid AND data_ref = :data AND provider_id <=> :pid LIMIT 1");
            $stmt->execute(['uid' => $userId, 'data' => $hoje, 'pid' => $pid]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row) {
                $novoTempo = (int)(($row['tempo_medio_ms'] * $row['total_chamadas'] + $tempoMs) / ($row['total_chamadas'] + 1));
                $pdo->prepare("UPDATE cop_ai_custos_diarios SET total_chamadas = total_chamadas + 1, tokens_input = tokens_input + :ti, tokens_output = tokens_output + :to, tokens_total = tokens_total + :tt, custo_usd = custo_usd + :custo, tempo_medio_ms = :tempo WHERE id = :id")
                    ->execute(['ti' => $ti, 'to' => $to, 'tt' => $tt, 'custo' => $custo, 'tempo' => $novoTempo, 'id' => $row['id']]);
            } else {
                $pdo->prepare("INSERT INTO cop_ai_custos_diarios (user_id, tenant_id, data_ref, provider_id, provider_nome, modelo, total_chamadas, tokens_input, tokens_output, tokens_total, custo_usd, tempo_medio_ms) VALUES (:uid, :tid, :data, :pid, :pnome, :modelo, 1, :ti, :to, :tt, :custo, :tempo)")
                    ->execute(['uid' => $userId, 'tid' => $tenantId, 'data' => $hoje, 'pid' => $pid, 'pnome' => $provider['nome'] ?? '', 'modelo' => $provider['modelo'] ?? '', 'ti' => $ti, 'to' => $to, 'tt' => $tt, 'custo' => $custo, 'tempo' => $tempoMs]);
            }
        } catch (\Exception $e) {
            // Silencioso — não quebra o fluxo principal
        }
    }

    /**
     * Atualiza última utilização e latência do provider
     */
    private static function atualizarProvider(?int $providerId, int $tempoMs): void {
        if (!$providerId) return;
        try {
            Database::getInstance()
                ->prepare("UPDATE cop_ai_providers SET ultima_utilizacao = NOW(), latencia_ms = :ms WHERE id = :id")
                ->execute(['ms' => $tempoMs, 'id' => $providerId]);
        } catch (\Exception $e) {}
    }

    /**
     * Registra log do AI Router
     */
    public static function log(?int $userId, ?int $tenantId, string $nivel, string $provider, string $modelo, string $acao, string $mensagem, array $contexto = []): void {
        try {
            Database::getInstance()
                ->prepare("INSERT INTO cop_ai_logs (user_id, tenant_id, nivel, provider_nome, modelo, acao, mensagem, contexto, ip) VALUES (:uid, :tid, :nivel, :prov, :modelo, :acao, :msg, :ctx, :ip)")
                ->execute([
                    'uid'    => $userId,
                    'tid'    => $tenantId,
                    'nivel'  => $nivel,
                    'prov'   => $provider,
                    'modelo' => $modelo,
                    'acao'   => $acao,
                    'msg'    => $mensagem,
                    'ctx'    => $contexto ? json_encode($contexto) : null,
                    'ip'     => $_SERVER['REMOTE_ADDR'] ?? null,
                ]);
        } catch (\Exception $e) {}
    }

    /**
     * Executa comparação simultânea entre múltiplos modelos
     */
    public static function comparar(array $params, array $providerIds): array {
        $resultados = [];
        foreach ($providerIds as $pid) {
            $p = $params;
            $p['provider_id'] = $pid;
            $r = self::route($p);
            $resultados[] = [
                'provider_id' => $pid,
                'provider'    => $r['provider'] ?? '',
                'modelo'      => $r['modelo'] ?? '',
                'texto'       => $r['texto'] ?? '',
                'tokens'      => $r['tokens_total'] ?? 0,
                'custo_usd'   => $r['custo_usd'] ?? 0,
                'tempo_ms'    => $r['tempo_ms'] ?? 0,
                'ok'          => $r['ok'] ?? false,
                'erro'        => $r['erro'] ?? null,
            ];
        }
        return $resultados;
    }
}
