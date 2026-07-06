<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Controller;
use App\Middlewares\AuthMiddleware;

class ProviderWizardController extends Controller
{
    private \PDO $db;
    private int  $userId;
    private string $encKey;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->db     = Database::getInstance();
        $this->userId = Auth::userId();
        $appKey       = defined('APP_KEY') ? APP_KEY : 'voxelcopilot_aes_key_2026';
        $this->encKey = hash('sha256', $appKey, true);
    }

    // ─── WIZARD — tela principal ──────────────────────────────
    public function index(): void
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM cop_ai_providers WHERE user_id = ? ORDER BY created_at DESC"
        );
        $stmt->execute([$this->userId]);
        $providers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt2 = $this->db->prepare(
            "SELECT * FROM cop_ai_provider_capabilities ORDER BY homologado DESC, rating DESC"
        );
        $stmt2->execute();
        $capabilities = $stmt2->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('ai_router/wizard', [
            'title'        => 'Configurar Provider — AI Router',
            'providers'    => $providers,
            'capabilities' => $capabilities,
            'extraCss'     => ['/assets/css/wizard.css'],
        ]);
    }

    // ─── API: POST /api/ai/provider/test ─────────────────────
    public function apiTest(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $result = $this->testConnection(
            $data['provider_type'] ?? '',
            $data['api_key']       ?? '',
            $data['endpoint']      ?? '',
            $data['deployment']    ?? '',
            $data['api_version']   ?? ''
        );
        $this->logAction(null, 'test', $result['ok'] ? 'ok' : 'erro',
            $result['ok'] ? 'Conexao testada' : ($result['error'] ?? 'Erro'));

        $this->json($result);
    }

    // ─── API: POST /api/ai/provider/discover-models ──────────
    public function apiDiscoverModels(): void
    {
        $data   = json_decode(file_get_contents('php://input'), true) ?? [];
        $models = $this->discoverModels(
            $data['provider_type'] ?? '',
            $data['api_key']       ?? '',
            $data['endpoint']      ?? '',
            $data['deployment']    ?? '',
            $data['api_version']   ?? ''
        );
        $this->json(['ok' => true, 'models' => $models]);
    }

    // ─── API: POST /api/ai/provider/validate ─────────────────
    public function apiValidate(): void
    {
        $data   = json_decode(file_get_contents('php://input'), true) ?? [];
        $result = $this->runFullValidation(
            $data['provider_type'] ?? '',
            $data['api_key']       ?? '',
            $data['endpoint']      ?? '',
            $data['model_id']      ?? '',
            $data['deployment']    ?? '',
            $data['api_version']   ?? ''
        );
        $this->json($result);
    }

    // ─── API: GET /api/ai/provider/models ────────────────────
    public function apiModels(): void
    {
        $providerId = (int)($_GET['provider_id'] ?? 0);
        if (!$providerId) {
            $this->json(['ok' => false, 'error' => 'provider_id obrigatorio'], 400);
        }
        $stmt = $this->db->prepare(
            "SELECT * FROM cop_ai_provider_models WHERE provider_id = ? AND user_id = ?
             ORDER BY is_recommended DESC, model_name ASC"
        );
        $stmt->execute([$providerId, $this->userId]);
        $this->json(['ok' => true, 'models' => $stmt->fetchAll(\PDO::FETCH_ASSOC)]);
    }

    // ─── API: GET /api/ai/provider/capabilities ──────────────
    public function apiCapabilities(): void
    {
        $type = $_GET['type'] ?? '';
        if ($type) {
            $stmt = $this->db->prepare(
                "SELECT * FROM cop_ai_provider_capabilities WHERE provider_type = ?"
            );
            $stmt->execute([$type]);
            $this->json(['ok' => true, 'capabilities' => $stmt->fetch(\PDO::FETCH_ASSOC)]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT * FROM cop_ai_provider_capabilities ORDER BY homologado DESC, rating DESC"
            );
            $stmt->execute();
            $this->json(['ok' => true, 'capabilities' => $stmt->fetchAll(\PDO::FETCH_ASSOC)]);
        }
    }

    // ─── POST /ai-router/providers/salvar ────────────────────
    public function salvar(): void
    {
        $data       = json_decode(file_get_contents('php://input'), true) ?? [];
        $type       = $data['provider_type'] ?? '';
        $apiKey     = $data['api_key']       ?? '';
        $nome       = $data['nome']          ?? $type;
        $endpoint   = $data['endpoint']      ?? null;
        $providerId = isset($data['provider_id']) ? (int)$data['provider_id'] : null;

        $apiKeyEnc  = $apiKey ? $this->encrypt($apiKey) : null;
        $apiKeyMask = $apiKey ? $this->maskApiKey($apiKey) : null;

        if (!empty($data['is_default'])) {
            $stmt = $this->db->prepare("UPDATE cop_ai_providers SET is_default = 0 WHERE user_id = ?");
            $stmt->execute([$this->userId]);
        }

        if ($providerId) {
            $sql    = "UPDATE cop_ai_providers SET nome=?,provider_type=?,endpoint=?,deployment=?,api_version=?,regiao=?,organizacao=?,conta=?,modo=?,is_default=?,temperatura=?,max_tokens=?,timeout_s=?,retry=?,top_p=?,freq_penalty=?,pres_penalty=?,idioma=?,wizard_step=?,wizard_completo=1,updated_at=NOW()";
            $params = [
                $nome,$type,$endpoint,
                $data['deployment']??null,$data['api_version']??null,
                $data['regiao']??null,$data['organizacao']??null,$data['conta']??null,
                $data['modo']??'producao',!empty($data['is_default'])?1:0,
                $data['temperatura']??0.1,$data['max_tokens']??4096,
                $data['timeout_s']??30,$data['retry']??3,
                $data['top_p']??1.0,$data['freq_penalty']??0.0,$data['pres_penalty']??0.0,
                $data['idioma']??'pt',$data['wizard_step']??5,
            ];
            if ($apiKey) { $sql .= ",api_key_enc=?,api_key_mask=?"; $params[] = $apiKeyEnc; $params[] = $apiKeyMask; }
            $sql .= " WHERE id=? AND user_id=?";
            $params[] = $providerId; $params[] = $this->userId;
            $this->db->prepare($sql)->execute($params);
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO cop_ai_providers (user_id,nome,provider_type,api_key_enc,api_key_mask,endpoint,deployment,api_version,regiao,organizacao,conta,modo,is_default,temperatura,max_tokens,timeout_s,retry,top_p,freq_penalty,pres_penalty,idioma,wizard_step,wizard_completo)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1)"
            );
            $stmt->execute([
                $this->userId,$nome,$type,$apiKeyEnc,$apiKeyMask,
                $endpoint,$data['deployment']??null,$data['api_version']??null,
                $data['regiao']??null,$data['organizacao']??null,$data['conta']??null,
                $data['modo']??'producao',!empty($data['is_default'])?1:0,
                $data['temperatura']??0.1,$data['max_tokens']??4096,
                $data['timeout_s']??30,$data['retry']??3,
                $data['top_p']??1.0,$data['freq_penalty']??0.0,$data['pres_penalty']??0.0,
                $data['idioma']??'pt',$data['wizard_step']??5,
            ]);
            $providerId = (int)$this->db->lastInsertId();
        }

        if (!empty($data['models']) && is_array($data['models'])) {
            $del = $this->db->prepare("DELETE FROM cop_ai_provider_models WHERE provider_id=? AND user_id=?");
            $del->execute([$providerId, $this->userId]);
            $ins = $this->db->prepare(
                "INSERT INTO cop_ai_provider_models (provider_id,user_id,model_id,model_name,model_family,context_window,cap_chat,cap_vision,cap_streaming,cap_json_mode,cap_function_call,cap_structured_out,cap_reasoning,cap_long_context,is_recommended,is_selected,raw_data)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
            );
            foreach ($data['models'] as $m) {
                $ins->execute([
                    $providerId,$this->userId,
                    $m['id']??$m['model_id']??'',$m['name']??$m['model_name']??'',
                    $m['family']??null,$m['context_window']??null,
                    $m['cap_chat']??1,$m['cap_vision']??0,$m['cap_streaming']??1,
                    $m['cap_json']??$m['cap_json_mode']??0,
                    $m['cap_functions']??$m['cap_function_call']??0,
                    $m['cap_structured']??$m['cap_structured_out']??0,
                    $m['cap_reasoning']??0,$m['cap_long_ctx']??$m['cap_long_context']??0,
                    $m['recommended']??$m['is_recommended']??0,
                    $m['selected']??$m['is_selected']??0,
                    json_encode($m),
                ]);
            }
        }

        $this->logAction($providerId, 'save', 'ok', 'Provider salvo');
        $this->json(['ok' => true, 'provider_id' => $providerId]);
    }

    // ─── POST /ai-router/providers/excluir ───────────────────
    public function excluir(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $id   = (int)($data['id'] ?? 0);
        if (!$id) { $this->json(['ok' => false, 'error' => 'ID invalido'], 400); }

        $this->db->prepare("DELETE FROM cop_ai_provider_models WHERE provider_id=? AND user_id=?")->execute([$id, $this->userId]);
        $this->db->prepare("DELETE FROM cop_ai_providers WHERE id=? AND user_id=?")->execute([$id, $this->userId]);

        $this->logAction($id, 'delete', 'ok', 'Provider excluido');
        $this->json(['ok' => true]);
    }

    // ========================================================
    // MÉTODOS PRIVADOS
    // ========================================================

    private function testConnection(string $type, string $apiKey, string $endpoint, string $deployment, string $apiVersion): array
    {
        $start = microtime(true);
        try {
            switch ($type) {
                case 'openai': case 'openrouter': case 'deepseek': case 'mistral': case 'qwen': case 'custom':
                    $ep  = rtrim($endpoint ?: $this->getDefaultEndpoint($type), '/');
                    $res = $this->httpGet("$ep/models", ['Authorization: Bearer '.$apiKey]);
                    break;
                case 'anthropic':
                    $ep  = rtrim($endpoint ?: 'https://api.anthropic.com', '/');
                    $res = $this->httpGet("$ep/v1/models", ['x-api-key: '.$apiKey,'anthropic-version: 2023-06-01']);
                    break;
                case 'google':
                    $ep  = rtrim($endpoint ?: 'https://generativelanguage.googleapis.com/v1beta', '/');
                    $res = $this->httpGet("$ep/models?key=".$apiKey, []);
                    break;
                case 'azure':
                    $ep  = rtrim($endpoint, '/');
                    $ver = $apiVersion ?: '2024-02-01';
                    $res = $this->httpGet("$ep/openai/deployments?api-version=$ver", ['api-key: '.$apiKey]);
                    break;
                case 'ollama': case 'lmstudio':
                    $ep  = rtrim($endpoint ?: 'http://localhost:11434', '/');
                    $res = $this->httpGet("$ep/api/tags", []);
                    if (!$res['ok']) $res = $this->httpGet("$ep/v1/models", []);
                    break;
                default:
                    $ep  = rtrim($endpoint, '/');
                    $res = $this->httpGet("$ep/models", ['Authorization: Bearer '.$apiKey]);
            }
            $latencia = round((microtime(true) - $start) * 1000);
            if ($res['ok']) {
                $body = json_decode($res['body'], true) ?? [];
                return ['ok'=>true,'latencia_ms'=>$latencia,'conta'=>$this->extractConta($type,$body),'organizacao'=>$this->extractOrg($type,$body),'endpoint'=>$ep??$endpoint,'api_version'=>$apiVersion?:'v1','regiao'=>$this->extractRegiao($type,$body),'status'=>'Online'];
            }
            return ['ok'=>false,'error'=>'HTTP '.$res['code']];
        } catch (\Exception $e) {
            return ['ok'=>false,'error'=>$e->getMessage()];
        }
    }

    private function discoverModels(string $type, string $apiKey, string $endpoint, string $deployment, string $apiVersion): array
    {
        try {
            switch ($type) {
                case 'openai':    return $this->discoverOpenAI($apiKey, $endpoint);
                case 'anthropic': return $this->discoverAnthropic($apiKey, $endpoint);
                case 'google':    return $this->discoverGoogle($apiKey, $endpoint);
                case 'azure':     return $this->discoverAzure($apiKey, $endpoint, $deployment, $apiVersion);
                case 'ollama': case 'lmstudio': return $this->discoverOllama($endpoint);
                case 'openrouter': return $this->discoverOpenRouter($apiKey, $endpoint);
                default: return $this->discoverGeneric($apiKey, $endpoint);
            }
        } catch (\Exception $e) { return []; }
    }

    private function discoverOpenAI(string $apiKey, string $endpoint): array
    {
        $ep  = rtrim($endpoint ?: 'https://api.openai.com/v1', '/');
        $res = $this->httpGet("$ep/models", ['Authorization: Bearer '.$apiKey]);
        if (!$res['ok']) return $this->openAIFallback();

        $result = [];
        $chatPrefixes = ['gpt-','o1','o3','o4','chatgpt'];
        foreach (json_decode($res['body'],true)['data']??[] as $m) {
            $id = $m['id']??'';
            $relevant = false;
            foreach ($chatPrefixes as $p) { if (strpos($id,$p)===0){$relevant=true;break;} }
            if (!$relevant) continue;
            $result[] = ['id'=>$id,'model_id'=>$id,'name'=>$this->friendlyModelName($id),'model_name'=>$this->friendlyModelName($id),'family'=>'gpt','context_window'=>$this->contextWindowForModel($id),'cap_chat'=>1,'cap_vision'=>(strpos($id,'gpt-4')!==false)?1:0,'cap_streaming'=>1,'cap_json'=>1,'cap_functions'=>1,'cap_structured'=>1,'cap_reasoning'=>(strpos($id,'o1')===0||strpos($id,'o3')===0||strpos($id,'o4')===0)?1:0,'cap_long_ctx'=>($this->contextWindowForModel($id)>=100000)?1:0,'recommended'=>(strpos($id,'gpt-4o')===0||strpos($id,'gpt-4.1')===0)?1:0,'selected'=>0];
        }
        if (empty($result)) return $this->openAIFallback();
        foreach ($result as &$r) { if ($r['recommended']){$r['selected']=1;break;} }
        if (empty(array_filter($result,fn($r)=>$r['selected']))&&!empty($result)) $result[0]['selected']=1;
        usort($result,fn($a,$b)=>$b['recommended']-$a['recommended']);
        return $result;
    }

    private function openAIFallback(): array
    {
        return [
            ['id'=>'gpt-4o','model_id'=>'gpt-4o','name'=>'GPT-4o','model_name'=>'GPT-4o','family'=>'gpt','context_window'=>128000,'cap_chat'=>1,'cap_vision'=>1,'cap_streaming'=>1,'cap_json'=>1,'cap_functions'=>1,'cap_structured'=>1,'cap_reasoning'=>0,'cap_long_ctx'=>1,'recommended'=>1,'selected'=>1],
            ['id'=>'gpt-4o-mini','model_id'=>'gpt-4o-mini','name'=>'GPT-4o mini','model_name'=>'GPT-4o mini','family'=>'gpt','context_window'=>128000,'cap_chat'=>1,'cap_vision'=>1,'cap_streaming'=>1,'cap_json'=>1,'cap_functions'=>1,'cap_structured'=>1,'cap_reasoning'=>0,'cap_long_ctx'=>1,'recommended'=>0,'selected'=>0],
            ['id'=>'gpt-4.1','model_id'=>'gpt-4.1','name'=>'GPT-4.1','model_name'=>'GPT-4.1','family'=>'gpt','context_window'=>1000000,'cap_chat'=>1,'cap_vision'=>1,'cap_streaming'=>1,'cap_json'=>1,'cap_functions'=>1,'cap_structured'=>1,'cap_reasoning'=>0,'cap_long_ctx'=>1,'recommended'=>0,'selected'=>0],
            ['id'=>'o4-mini','model_id'=>'o4-mini','name'=>'o4 mini','model_name'=>'o4 mini','family'=>'gpt','context_window'=>200000,'cap_chat'=>1,'cap_vision'=>0,'cap_streaming'=>1,'cap_json'=>1,'cap_functions'=>1,'cap_structured'=>1,'cap_reasoning'=>1,'cap_long_ctx'=>1,'recommended'=>0,'selected'=>0],
        ];
    }

    private function discoverAnthropic(string $apiKey, string $endpoint): array
    {
        $ep  = rtrim($endpoint ?: 'https://api.anthropic.com', '/');
        $res = $this->httpGet("$ep/v1/models", ['x-api-key: '.$apiKey,'anthropic-version: 2023-06-01']);
        $models = [];
        if ($res['ok']) {
            foreach (json_decode($res['body'],true)['data']??[] as $m) {
                $id = $m['id']??'';
                $models[] = ['id'=>$id,'model_id'=>$id,'name'=>$m['display_name']??$this->friendlyModelName($id),'model_name'=>$m['display_name']??$this->friendlyModelName($id),'family'=>'claude','context_window'=>$m['context_window']??200000,'cap_chat'=>1,'cap_vision'=>1,'cap_streaming'=>1,'cap_json'=>1,'cap_functions'=>1,'cap_structured'=>1,'cap_reasoning'=>(strpos($id,'opus')!==false)?1:0,'cap_long_ctx'=>1,'recommended'=>(strpos($id,'sonnet')!==false)?1:0,'selected'=>0];
            }
        }
        if (empty($models)) {
            foreach ([['claude-opus-4-5','Claude Opus 4.5',1],['claude-sonnet-4-5','Claude Sonnet 4.5',1],['claude-haiku-4-5','Claude Haiku 4.5',0],['claude-3-5-sonnet-20241022','Claude 3.5 Sonnet',1]] as [$id,$name,$rec]) {
                $models[] = ['id'=>$id,'model_id'=>$id,'name'=>$name,'model_name'=>$name,'family'=>'claude','context_window'=>200000,'cap_chat'=>1,'cap_vision'=>1,'cap_streaming'=>1,'cap_json'=>1,'cap_functions'=>1,'cap_structured'=>1,'cap_reasoning'=>1,'cap_long_ctx'=>1,'recommended'=>$rec,'selected'=>0];
            }
        }
        foreach ($models as &$m) { if ($m['recommended']){$m['selected']=1;break;} }
        return $models;
    }

    private function discoverGoogle(string $apiKey, string $endpoint): array
    {
        $ep  = rtrim($endpoint ?: 'https://generativelanguage.googleapis.com/v1beta', '/');
        $res = $this->httpGet("$ep/models?key=$apiKey", []);
        $models = [];
        if ($res['ok']) {
            foreach (json_decode($res['body'],true)['models']??[] as $m) {
                $id = str_replace('models/','',$m['name']??'');
                if (strpos($id,'gemini')===false) continue;
                $models[] = ['id'=>$id,'model_id'=>$id,'name'=>$m['displayName']??$this->friendlyModelName($id),'model_name'=>$m['displayName']??$this->friendlyModelName($id),'family'=>'gemini','context_window'=>$m['inputTokenLimit']??1000000,'cap_chat'=>1,'cap_vision'=>1,'cap_streaming'=>1,'cap_json'=>1,'cap_functions'=>1,'cap_structured'=>1,'cap_reasoning'=>(strpos($id,'pro')!==false)?1:0,'cap_long_ctx'=>1,'recommended'=>(strpos($id,'2.5-pro')!==false)?1:0,'selected'=>0];
            }
        }
        if (empty($models)) {
            foreach ([['gemini-2.5-pro','Gemini 2.5 Pro',1,1000000],['gemini-2.5-flash','Gemini 2.5 Flash',0,1000000],['gemini-2.0-flash','Gemini 2.0 Flash',0,1000000],['gemini-1.5-pro','Gemini 1.5 Pro',0,2000000]] as [$id,$name,$rec,$ctx]) {
                $models[] = ['id'=>$id,'model_id'=>$id,'name'=>$name,'model_name'=>$name,'family'=>'gemini','context_window'=>$ctx,'cap_chat'=>1,'cap_vision'=>1,'cap_streaming'=>1,'cap_json'=>1,'cap_functions'=>1,'cap_structured'=>1,'cap_reasoning'=>1,'cap_long_ctx'=>1,'recommended'=>$rec,'selected'=>0];
            }
        }
        foreach ($models as &$m) { if ($m['recommended']){$m['selected']=1;break;} }
        return $models;
    }

    private function discoverAzure(string $apiKey, string $endpoint, string $deployment, string $apiVersion): array
    {
        $ep  = rtrim($endpoint, '/');
        $ver = $apiVersion ?: '2024-02-01';
        $res = $this->httpGet("$ep/openai/deployments?api-version=$ver", ['api-key: '.$apiKey]);
        $models = [];
        if ($res['ok']) {
            foreach (json_decode($res['body'],true)['data']??[] as $m) {
                $id = $m['id']??'';
                $models[] = ['id'=>$id,'model_id'=>$id,'name'=>$m['model']??$id,'model_name'=>$m['model']??$id,'family'=>'azure','context_window'=>128000,'cap_chat'=>1,'cap_vision'=>1,'cap_streaming'=>1,'cap_json'=>1,'cap_functions'=>1,'cap_structured'=>1,'cap_reasoning'=>0,'cap_long_ctx'=>1,'recommended'=>1,'selected'=>0];
            }
        }
        if (empty($models)&&$deployment) $models[] = ['id'=>$deployment,'model_id'=>$deployment,'name'=>$deployment,'model_name'=>$deployment,'family'=>'azure','context_window'=>128000,'cap_chat'=>1,'cap_vision'=>1,'cap_streaming'=>1,'cap_json'=>1,'cap_functions'=>1,'cap_structured'=>1,'cap_reasoning'=>0,'cap_long_ctx'=>1,'recommended'=>1,'selected'=>1];
        if (!empty($models)&&!array_filter($models,fn($m)=>$m['selected'])) $models[0]['selected']=1;
        return $models;
    }

    private function discoverOllama(string $endpoint): array
    {
        $ep  = rtrim($endpoint ?: 'http://localhost:11434', '/');
        $res = $this->httpGet("$ep/api/tags", []);
        $models = [];
        if ($res['ok']) {
            foreach (json_decode($res['body'],true)['models']??[] as $m) {
                $id = $m['name']??'';
                $models[] = ['id'=>$id,'model_id'=>$id,'name'=>$id,'model_name'=>$id,'family'=>'ollama','context_window'=>8192,'cap_chat'=>1,'cap_vision'=>0,'cap_streaming'=>1,'cap_json'=>0,'cap_functions'=>0,'cap_structured'=>0,'cap_reasoning'=>0,'cap_long_ctx'=>0,'recommended'=>0,'selected'=>0];
            }
        }
        if (!empty($models)) $models[0]['selected']=1;
        return $models;
    }

    private function discoverOpenRouter(string $apiKey, string $endpoint): array
    {
        $ep  = rtrim($endpoint ?: 'https://openrouter.ai/api/v1', '/');
        $res = $this->httpGet("$ep/models", ['Authorization: Bearer '.$apiKey]);
        $models = [];
        if ($res['ok']) {
            foreach (array_slice(json_decode($res['body'],true)['data']??[],0,20) as $m) {
                $id = $m['id']??'';
                $models[] = ['id'=>$id,'model_id'=>$id,'name'=>$m['name']??$id,'model_name'=>$m['name']??$id,'family'=>'openrouter','context_window'=>$m['context_length']??128000,'cap_chat'=>1,'cap_vision'=>0,'cap_streaming'=>1,'cap_json'=>1,'cap_functions'=>0,'cap_structured'=>0,'cap_reasoning'=>0,'cap_long_ctx'=>($m['context_length']??0)>=100000?1:0,'recommended'=>(strpos($id,'gpt-4o')!==false||strpos($id,'claude-3-5')!==false)?1:0,'selected'=>0];
            }
        }
        foreach ($models as &$m) { if ($m['recommended']){$m['selected']=1;break;} }
        if (!empty($models)&&!array_filter($models,fn($m)=>$m['selected'])) $models[0]['selected']=1;
        return $models;
    }

    private function discoverGeneric(string $apiKey, string $endpoint): array
    {
        $ep  = rtrim($endpoint, '/');
        $res = $this->httpGet("$ep/models", ['Authorization: Bearer '.$apiKey]);
        $models = [];
        if ($res['ok']) {
            $body = json_decode($res['body'],true)??[];
            foreach ($body['data']??$body['models']??[] as $m) {
                $id = $m['id']??$m['name']??'';
                if (!$id) continue;
                $models[] = ['id'=>$id,'model_id'=>$id,'name'=>$id,'model_name'=>$id,'family'=>'custom','context_window'=>4096,'cap_chat'=>1,'cap_vision'=>0,'cap_streaming'=>1,'cap_json'=>0,'cap_functions'=>0,'cap_structured'=>0,'cap_reasoning'=>0,'cap_long_ctx'=>0,'recommended'=>0,'selected'=>0];
            }
        }
        if (!empty($models)) $models[0]['selected']=1;
        return $models;
    }

    private function runFullValidation(string $type, string $apiKey, string $endpoint, string $modelId, string $deployment, string $apiVersion): array
    {
        $result = [
            'ok'            => false,
            'steps'         => [],
            'latencia_ms'   => null,
            'tempo_total_ms'=> null,
            'tempo_ia_ms'   => null,
            'context_max'   => null,
            'saldo'         => null,
            'saldo_disp'    => false,
            'caps'          => [],
            'diagnostico'   => null,   // detalhes completos da requisição de geração
        ];
        $totalStart = microtime(true);

        // ── Passo 1: Autenticação ─────────────────────────────────
        $authResult = $this->testConnection($type, $apiKey, $endpoint, $deployment, $apiVersion);
        $authOk     = $authResult['ok'];
        $result['steps'][] = [
            'num'    => 1,
            'nome'   => 'Autenticação',
            'ok'     => $authOk,
            'detalhe'=> $authOk
                ? 'API Key válida — conta: ' . ($authResult['conta'] ?? '—') . ' | latência: ' . ($authResult['latencia_ms'] ?? '—') . 'ms'
                : $this->formatAuthError($authResult),
        ];
        $result['latencia_ms'] = $authResult['latencia_ms'] ?? null;

        if (!$authOk) {
            $result['steps'][] = ['num'=>2,'nome'=>'Diagnóstico da API','ok'=>false,'detalhe'=>'Pulado — autenticação falhou'];
            $result['steps'][] = ['num'=>3,'nome'=>'Tempo de resposta','ok'=>false,'detalhe'=>'Pulado'];
            $this->saveTestRecord(null, $type, $modelId, $endpoint, $apiKey, $deployment, $apiVersion, false, false, $authResult['latencia_ms']??null, null, null, null, null, null, $authResult, null, null, null);
            return $result;
        }

        // ── Passo 2: Diagnóstico completo da API ──────────────────
        $genStart  = microtime(true);
        $genResult = $this->testGenerationDetailed($type, $apiKey, $endpoint, $modelId, $deployment, $apiVersion);
        $genTime   = round((microtime(true) - $genStart) * 1000);
        $genOk     = $genResult['ok'];
        $result['tempo_ia_ms'] = $genTime;
        $result['diagnostico'] = $genResult['diagnostico'] ?? null;

        $step2Detalhe = $genOk
            ? 'Resposta: "' . ($genResult['response'] ?? '') . '" — ' . ($genResult['tokens_usados'] ?? '?') . ' tokens'
            : $this->formatGenError($genResult);

        $result['steps'][] = [
            'num'    => 2,
            'nome'   => 'Diagnóstico da API',
            'ok'     => $genOk,
            'detalhe'=> $step2Detalhe,
            'error_detail' => $genOk ? null : ($genResult['diagnostico'] ?? null),
        ];

        // ── Passo 3: Tempo de resposta ────────────────────────────
        $result['steps'][] = [
            'num'    => 3,
            'nome'   => 'Tempo de resposta',
            'ok'     => true,
            'detalhe'=> 'Auth: ' . ($result['latencia_ms'] ?? '—') . 'ms | Geração: ' . $genTime . 'ms',
        ];

        // ── Passo 4: Capacidades ──────────────────────────────────
        $caps = $this->detectCapabilities($type, $modelId);
        $result['caps']    = $caps;
        $result['steps'][] = [
            'num'    => 4,
            'nome'   => 'Capacidades',
            'ok'     => true,
            'detalhe'=> implode(', ', array_keys(array_filter($caps))),
        ];

        // ── Passo 5: Contexto máximo ──────────────────────────────
        $ctx = $this->contextWindowForModel($modelId);
        $result['context_max'] = $ctx;
        $result['steps'][] = [
            'num'    => 5,
            'nome'   => 'Contexto máximo',
            'ok'     => true,
            'detalhe'=> $ctx ? number_format($ctx, 0, ',', '.') . ' tokens' : 'Não identificado',
        ];

        // ── Passo 6: Endpoint ─────────────────────────────────────
        $epUsado = $genResult['diagnostico']['endpoint_usado'] ?? $authResult['endpoint'] ?? $endpoint;
        $result['steps'][] = ['num'=>6,'nome'=>'Endpoint','ok'=>true,'detalhe'=> $epUsado];

        // ── Passo 7: Versão da API ────────────────────────────────
        $result['steps'][] = ['num'=>7,'nome'=>'Versão da API','ok'=>true,'detalhe'=> $authResult['api_version'] ?? 'v1'];

        // ── Passo 8: Saldo ────────────────────────────────────────
        $result['steps'][] = ['num'=>8,'nome'=>'Saldo','ok'=>true,'detalhe'=>'Consulta de saldo não disponível para este provider'];

        // ── Passo 9: Benchmark / persistência ────────────────────
        $result['tempo_total_ms'] = round((microtime(true) - $totalStart) * 1000);
        $testId = $this->saveTestRecord(
            null, $type, $modelId,
            $epUsado, $apiKey, $deployment, $apiVersion,
            $authOk, $genOk,
            $result['latencia_ms'], $result['tempo_total_ms'], $genTime,
            $ctx, null,
            $genResult['diagnostico'] ?? null,
            $authResult,
            $genResult['response'] ?? null,
            $genResult['tokens_usados'] ?? null,
            $caps
        );
        $result['steps'][] = [
            'num'    => 9,
            'nome'   => 'Benchmark salvo',
            'ok'     => true,
            'detalhe'=> 'Total: ' . $result['tempo_total_ms'] . 'ms | Registro #' . ($testId ?: '—'),
        ];

        $result['ok'] = $genOk;
        return $result;
    }

    /**
     * Diagnóstico completo da API: monta payload, executa chamada de geração,
     * captura resposta bruta, extrai campos de erro estruturados e gera orientação.
     */
    private function testGenerationDetailed(string $type, string $apiKey, string $endpoint, string $modelId, string $deployment, string $apiVersion): array
    {
        $promptText     = 'Responda apenas: OK';
        $maxTokens      = 10;
        $promptChars    = strlen($promptText);
        $epUsado        = '';
        $payloadArr     = [];
        $payloadMasked  = [];

        try {
            switch ($type) {
                case 'openai': case 'openrouter': case 'deepseek': case 'mistral': case 'qwen': case 'custom': case 'lmstudio':
                    $epUsado    = rtrim($endpoint ?: $this->getDefaultEndpoint($type), '/') . '/chat/completions';
                    $payloadArr = ['model'=>$modelId,'messages'=>[['role'=>'user','content'=>$promptText]],'max_tokens'=>$maxTokens];
                    $payloadMasked = $payloadArr;
                    $res = $this->httpPost($epUsado, json_encode($payloadArr), ['Authorization: Bearer '.$this->maskApiKey($apiKey).' [MASKED]', 'Content-Type: application/json']);
                    // Chamada real com key real
                    $res = $this->httpPost($epUsado, json_encode($payloadArr), ['Authorization: Bearer '.$apiKey, 'Content-Type: application/json']);
                    break;

                case 'anthropic':
                    $epUsado    = rtrim($endpoint ?: 'https://api.anthropic.com', '/') . '/v1/messages';
                    $payloadArr = ['model'=>$modelId,'max_tokens'=>$maxTokens,'messages'=>[['role'=>'user','content'=>$promptText]]];
                    $payloadMasked = $payloadArr;
                    $res = $this->httpPost($epUsado, json_encode($payloadArr), ['x-api-key: '.$apiKey,'anthropic-version: 2023-06-01','Content-Type: application/json']);
                    break;

                case 'google':
                    $epUsado    = rtrim($endpoint ?: 'https://generativelanguage.googleapis.com/v1beta', '/') . '/models/' . $modelId . ':generateContent?key=[MASKED]';
                    $epReal     = rtrim($endpoint ?: 'https://generativelanguage.googleapis.com/v1beta', '/') . '/models/' . $modelId . ':generateContent?key=' . $apiKey;
                    $payloadArr = ['contents'=>[['parts'=>[['text'=>$promptText]]]]];
                    $payloadMasked = $payloadArr;
                    $res = $this->httpPost($epReal, json_encode($payloadArr), ['Content-Type: application/json']);
                    break;

                case 'azure':
                    $ver        = $apiVersion ?: '2024-02-01';
                    $epUsado    = rtrim($endpoint, '/') . '/openai/deployments/' . $deployment . '/chat/completions?api-version=' . $ver;
                    $payloadArr = ['model'=>$modelId,'messages'=>[['role'=>'user','content'=>$promptText]],'max_tokens'=>$maxTokens];
                    $payloadMasked = $payloadArr;
                    $res = $this->httpPost($epUsado, json_encode($payloadArr), ['api-key: '.$apiKey,'Content-Type: application/json']);
                    break;

                case 'ollama':
                    $epUsado    = rtrim($endpoint ?: 'http://localhost:11434', '/') . '/api/chat';
                    $payloadArr = ['model'=>$modelId,'messages'=>[['role'=>'user','content'=>$promptText]],'stream'=>false];
                    $payloadMasked = $payloadArr;
                    $res = $this->httpPost($epUsado, json_encode($payloadArr), ['Content-Type: application/json']);
                    break;

                default:
                    return [
                        'ok'          => false,
                        'error'       => 'Provider não suportado: ' . $type,
                        'diagnostico' => ['endpoint_usado'=>$epUsado,'modelo_enviado'=>$modelId,'payload'=>[],'resposta_raw'=>null,'http_status'=>null,'erro_tipo'=>null,'erro_code'=>null,'erro_param'=>null,'erro_mensagem'=>'Provider não suportado','erro_categoria'=>'outro','orientacao'=>'Verifique o tipo de provider configurado.','tokens_solicitados'=>$maxTokens,'prompt_chars'=>$promptChars],
                    ];
            }

            $httpCode    = $res['code'];
            $respostaRaw = $res['body'];
            $bodyArr     = json_decode($respostaRaw, true) ?? [];

            // ── Extrair campos de erro estruturados ───────────────
            $erroTipo      = null;
            $erroCode      = null;
            $erroParam     = null;
            $erroMensagem  = null;
            $erroCategoria = 'ok';
            $orientacao    = null;
            $respostaTexto = null;
            $tokensUsados  = null;

            if ($res['ok']) {
                // Sucesso — extrair texto e tokens
                $respostaTexto = trim(
                    $bodyArr['choices'][0]['message']['content']
                    ?? $bodyArr['content'][0]['text']
                    ?? $bodyArr['candidates'][0]['content']['parts'][0]['text']
                    ?? $bodyArr['message']['content']
                    ?? 'OK'
                );
                $tokensUsados = $bodyArr['usage']['total_tokens']
                    ?? $bodyArr['usage']['input_tokens'] + ($bodyArr['usage']['output_tokens'] ?? 0)
                    ?? $bodyArr['usage']['prompt_tokens'] + ($bodyArr['usage']['completion_tokens'] ?? 0)
                    ?? null;
            } else {
                // Erro — extrair campos estruturados por provider
                $errObj = $bodyArr['error'] ?? $bodyArr;

                // OpenAI / Azure / OpenRouter / DeepSeek / Mistral / Qwen / Custom
                $erroMensagem = $errObj['message'] ?? $errObj['error_description'] ?? $errObj['detail'] ?? null;
                $erroTipo     = $errObj['type']    ?? $errObj['error'] ?? null;
                $erroCode     = $errObj['code']    ?? null;
                $erroParam    = $errObj['param']   ?? null;

                // Anthropic: { error: { type, message } }
                if (isset($bodyArr['error']['type'])) {
                    $erroTipo     = $bodyArr['error']['type'];
                    $erroMensagem = $bodyArr['error']['message'] ?? $erroMensagem;
                }

                // Google: { error: { code, message, status } }
                if (isset($bodyArr['error']['status'])) {
                    $erroTipo     = $bodyArr['error']['status'];
                    $erroCode     = (string)($bodyArr['error']['code'] ?? $httpCode);
                    $erroMensagem = $bodyArr['error']['message'] ?? $erroMensagem;
                }

                // Fallback: se não extraiu nada, usar o body bruto truncado
                if (!$erroMensagem) {
                    $erroMensagem = substr($respostaRaw, 0, 500);
                }

                // ── Categorizar o erro ────────────────────────────
                $erroCategoria = $this->categorizarErro($httpCode, $erroTipo, $erroCode, $erroMensagem);
                $orientacao    = $this->gerarOrientacao($erroCategoria, $type, $erroMensagem);
            }

            $diagnostico = [
                'endpoint_usado'    => $epUsado,
                'modelo_enviado'    => $modelId,
                'payload'           => $payloadMasked,
                'resposta_raw'      => $respostaRaw,
                'http_status'       => $httpCode,
                'tokens_solicitados'=> $maxTokens,
                'prompt_chars'      => $promptChars,
                'erro_tipo'         => $erroTipo,
                'erro_code'         => $erroCode,
                'erro_param'        => $erroParam,
                'erro_mensagem'     => $erroMensagem,
                'erro_categoria'    => $erroCategoria,
                'orientacao'        => $orientacao,
                'resposta_texto'    => $respostaTexto,
                'tokens_usados'     => $tokensUsados,
            ];

            return [
                'ok'           => $res['ok'],
                'response'     => $respostaTexto ? substr($respostaTexto, 0, 60) : null,
                'tokens_usados'=> $tokensUsados,
                'diagnostico'  => $diagnostico,
            ];

        } catch (\Exception $e) {
            $diagnostico = [
                'endpoint_usado'    => $epUsado,
                'modelo_enviado'    => $modelId,
                'payload'           => $payloadMasked,
                'resposta_raw'      => null,
                'http_status'       => null,
                'tokens_solicitados'=> $maxTokens,
                'prompt_chars'      => $promptChars,
                'erro_tipo'         => 'exception',
                'erro_code'         => null,
                'erro_param'        => null,
                'erro_mensagem'     => $e->getMessage(),
                'erro_categoria'    => 'outro',
                'orientacao'        => 'Verifique a conectividade com o endpoint e se o servidor está acessível.',
                'resposta_texto'    => null,
                'tokens_usados'     => null,
            ];
            return ['ok'=>false,'error'=>$e->getMessage(),'diagnostico'=>$diagnostico];
        }
    }

    /** Categoriza o erro HTTP + campos do provider em uma categoria semântica */
    private function categorizarErro(int $httpCode, ?string $tipo, ?string $code, ?string $mensagem): string
    {
        $m = strtolower($mensagem ?? '');
        $c = strtolower($code ?? '');
        $t = strtolower($tipo ?? '');

        if ($httpCode === 401 || $t === 'invalid_api_key' || $t === 'authentication_error' || strpos($m,'invalid api key')!==false || strpos($m,'unauthorized')!==false) {
            return 'auth_invalida';
        }
        if ($httpCode === 429) {
            // Diferenciar rate limit de quota insuficiente
            if ($c === 'insufficient_quota' || strpos($m,'insufficient_quota')!==false || strpos($m,'exceeded your current quota')!==false || strpos($m,'billing')!==false || strpos($m,'credit')!==false || strpos($m,'no credits')!==false || strpos($m,'out of credits')!==false) {
                return 'quota_insuficiente';
            }
            return 'rate_limit';
        }
        if ($httpCode === 404 || strpos($m,'model not found')!==false || strpos($m,'does not exist')!==false || $c === 'model_not_found') {
            return 'modelo_invalido';
        }
        if ($httpCode === 0 || strpos($m,'timed out')!==false || strpos($m,'timeout')!==false || strpos($m,'connection refused')!==false || strpos($m,'could not connect')!==false) {
            return strpos($m,'timeout')!==false ? 'timeout' : 'endpoint_invalido';
        }
        if ($httpCode >= 400 && $httpCode < 500) return 'outro';
        if ($httpCode >= 500) return 'outro';
        return 'ok';
    }

    /** Gera orientação textual para o usuário com base na categoria do erro */
    private function gerarOrientacao(string $categoria, string $type, ?string $mensagem): string
    {
        switch ($categoria) {
            case 'auth_invalida':
                return 'A API Key informada é inválida ou foi revogada. Acesse o painel do provider, gere uma nova chave e atualize as credenciais no Wizard.';

            case 'rate_limit':
                return 'Limite de requisições por minuto (RPM) atingido. Aguarde alguns segundos e tente novamente. Se o problema persistir, verifique o plano contratado ou distribua as chamadas em um intervalo maior.';

            case 'quota_insuficiente':
                return 'Créditos insuficientes (insufficient_quota). Sua conta não possui saldo disponível para realizar chamadas. Acesse o painel de faturamento do provider e adicione créditos ou configure um método de pagamento.';

            case 'modelo_invalido':
                return 'O modelo "' . $mensagem . '" não foi encontrado ou não está disponível para sua conta. Retorne à Etapa 3 e selecione um modelo diferente, ou verifique se o modelo está ativo no painel do provider.';

            case 'endpoint_invalido':
                return 'Não foi possível conectar ao endpoint informado. Verifique se a URL está correta, se o serviço está online e se não há bloqueio de firewall ou proxy.';

            case 'timeout':
                return 'A requisição excedeu o tempo limite. O provider pode estar com alta latência. Tente aumentar o Timeout nas configurações (Etapa 4) ou tente novamente em alguns instantes.';

            default:
                return 'Erro inesperado retornado pelo provider. Verifique a mensagem de erro acima e consulte a documentação oficial do provider para mais detalhes.';
        }
    }

    /** Formata a mensagem de erro de autenticação para exibição */
    private function formatAuthError(array $authResult): string
    {
        $err = $authResult['error'] ?? 'Falha na autenticação';
        $code = $authResult['code'] ?? null;
        return $code ? "HTTP {$code} — {$err}" : $err;
    }

    /** Formata a mensagem de erro de geração para exibição no step */
    private function formatGenError(array $genResult): string
    {
        $diag = $genResult['diagnostico'] ?? [];
        $cat  = $diag['erro_categoria'] ?? 'outro';
        $http = $diag['http_status'] ?? null;
        $msg  = $diag['erro_mensagem'] ?? ($genResult['error'] ?? 'Falha');

        $catLabel = [
            'rate_limit'        => '⚠ Rate Limit Excedido',
            'quota_insuficiente'=> '🚫 Créditos Insuficientes (insufficient_quota)',
            'auth_invalida'     => '🔑 API Key Inválida',
            'modelo_invalido'   => '🤖 Modelo Não Encontrado',
            'endpoint_invalido' => '🔌 Endpoint Inacessível',
            'timeout'           => '⏱ Timeout',
            'outro'             => '✗ Erro',
        ][$cat] ?? '✗ Erro';

        $prefix = $http ? "HTTP {$http} — {$catLabel}" : $catLabel;
        return $prefix . ': ' . substr($msg, 0, 120);
    }

    /** Persiste o resultado do teste na tabela cop_ai_provider_tests */
    private function saveTestRecord(
        ?int    $providerId,
        string  $type,
        string  $modelId,
        string  $endpoint,
        string  $apiKey,
        string  $deployment,
        string  $apiVersion,
        bool    $authOk,
        bool    $genOk,
        ?int    $latenciaMs,
        ?int    $tempoTotalMs,
        ?int    $tempoIaMs,
        ?int    $contextMax,
        ?float  $saldoUsd,
        ?array  $diagnostico,
        ?array  $authResult,
        ?string $respostaTexto,
        ?int    $tokensUsados,
        ?array  $caps
    ): ?int {
        try {
            $status = $genOk ? 'ok' : ($authOk ? 'parcial' : 'erro');

            // Payload mascarado para auditoria
            $payloadJson = null;
            if (!empty($diagnostico['payload'])) {
                $payloadJson = json_encode($diagnostico['payload'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            $stmt = $this->db->prepare(
                "INSERT INTO cop_ai_provider_tests
                    (provider_id, user_id, model_id, endpoint_usado, modelo_enviado,
                     payload_json, resposta_raw, tokens_solicitados, prompt_chars,
                     http_status, erro_tipo, erro_code, erro_param, erro_mensagem,
                     erro_categoria, orientacao, resposta_texto, tokens_usados,
                     auth_ok, gen_ok, latencia_ms, tempo_total_ms, tempo_ia_ms,
                     cap_chat, cap_vision, cap_streaming, cap_json, cap_functions,
                     cap_structured, cap_long_ctx, context_max, saldo_usd,
                     saldo_disponivel, status, erro_msg)
                 VALUES
                    (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
            );
            $stmt->execute([
                $providerId,
                $this->userId,
                $modelId ?: null,
                $endpoint ?: null,
                $modelId ?: null,
                $payloadJson,
                $diagnostico['resposta_raw'] ?? null,
                $diagnostico['tokens_solicitados'] ?? null,
                $diagnostico['prompt_chars'] ?? null,
                $diagnostico['http_status'] ?? null,
                $diagnostico['erro_tipo'] ?? null,
                $diagnostico['erro_code'] ?? null,
                $diagnostico['erro_param'] ?? null,
                $diagnostico['erro_mensagem'] ?? null,
                $diagnostico['erro_categoria'] ?? ($genOk ? 'ok' : 'outro'),
                $diagnostico['orientacao'] ?? null,
                $respostaTexto,
                $tokensUsados,
                $authOk ? 1 : 0,
                $genOk  ? 1 : 0,
                $latenciaMs,
                $tempoTotalMs,
                $tempoIaMs,
                $caps['Chat']              ?? 0,
                $caps['Vision']            ?? 0,
                $caps['Streaming']         ?? 0,
                $caps['JSON Mode']         ?? 0,
                $caps['Function Calling']  ?? 0,
                $caps['Structured Output'] ?? 0,
                $caps['Long Context']      ?? 0,
                $contextMax,
                $saldoUsd,
                0,
                $status,
                $diagnostico['erro_mensagem'] ?? null,
            ]);
            return (int)$this->db->lastInsertId();
        } catch (\Exception $e) {
            // Silencioso — não bloquear o fluxo de validação
            return null;
        }
    }

    private function detectCapabilities(string $type, string $modelId): array
    {
        $caps = ['Chat'=>true,'Vision'=>false,'Streaming'=>true,'JSON Mode'=>false,'Function Calling'=>false,'Structured Output'=>false,'Long Context'=>false];
        switch ($type) {
            case 'openai':    $caps['Vision']=$caps['JSON Mode']=$caps['Function Calling']=$caps['Structured Output']=true; $caps['Long Context']=$this->contextWindowForModel($modelId)>=100000; break;
            case 'anthropic': case 'google': case 'azure': $caps['Vision']=$caps['JSON Mode']=$caps['Function Calling']=$caps['Structured Output']=$caps['Long Context']=true; break;
        }
        return $caps;
    }

    private function httpGet(string $url, array $headers): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_HTTPHEADER=>$headers,CURLOPT_FOLLOWLOCATION=>true]);
        $body = curl_exec($ch); $code = curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
        return ['ok'=>($code>=200&&$code<300),'code'=>$code,'body'=>$body];
    }

    private function httpPost(string $url, string $payload, array $headers): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>30,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_HTTPHEADER=>$headers]);
        $body = curl_exec($ch); $code = curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
        return ['ok'=>($code>=200&&$code<300),'code'=>$code,'body'=>$body];
    }

    private function getDefaultEndpoint(string $type): string
    {
        return ['openai'=>'https://api.openai.com/v1','anthropic'=>'https://api.anthropic.com','google'=>'https://generativelanguage.googleapis.com/v1beta','openrouter'=>'https://openrouter.ai/api/v1','deepseek'=>'https://api.deepseek.com/v1','mistral'=>'https://api.mistral.ai/v1','qwen'=>'https://dashscope.aliyuncs.com/compatible-mode/v1','lmstudio'=>'http://localhost:1234/v1','ollama'=>'http://localhost:11434'][$type]??'';
    }

    private function contextWindowForModel(string $modelId): int
    {
        $map = ['gpt-4o'=>128000,'gpt-4o-mini'=>128000,'gpt-4-turbo'=>128000,'gpt-4.1'=>1000000,'gpt-4.1-mini'=>1000000,'gpt-4.1-nano'=>1000000,'gpt-5'=>1000000,'o1'=>200000,'o1-mini'=>128000,'o3'=>200000,'o3-mini'=>200000,'o4-mini'=>200000,'claude-opus'=>200000,'claude-sonnet'=>200000,'claude-haiku'=>200000,'gemini-2.5-pro'=>1000000,'gemini-2.5-flash'=>1000000,'gemini-1.5-pro'=>2000000];
        foreach ($map as $prefix=>$ctx) { if (strpos($modelId,$prefix)===0) return $ctx; }
        return 4096;
    }

    private function friendlyModelName(string $id): string
    {
        $map = ['gpt-4o'=>'GPT-4o','gpt-4o-mini'=>'GPT-4o mini','gpt-4.1'=>'GPT-4.1','gpt-4.1-mini'=>'GPT-4.1 mini','gpt-4.1-nano'=>'GPT-4.1 nano','gpt-4-turbo'=>'GPT-4 Turbo','gpt-4'=>'GPT-4','gpt-3.5-turbo'=>'GPT-3.5 Turbo','o1'=>'o1','o1-mini'=>'o1 mini','o3'=>'o3','o3-mini'=>'o3 mini','o4-mini'=>'o4 mini','claude-opus-4-5'=>'Claude Opus 4.5','claude-sonnet-4-5'=>'Claude Sonnet 4.5','claude-haiku-4-5'=>'Claude Haiku 4.5','claude-3-5-sonnet-20241022'=>'Claude 3.5 Sonnet','gemini-2.5-pro'=>'Gemini 2.5 Pro','gemini-2.5-flash'=>'Gemini 2.5 Flash','gemini-2.0-flash'=>'Gemini 2.0 Flash','gemini-1.5-pro'=>'Gemini 1.5 Pro'];
        return $map[$id]??ucwords(str_replace(['-','_'],' ',$id));
    }

    private function extractConta(string $type, array $body): string
    {
        if ($type==='openai') return $body['data'][0]['owned_by']??'OpenAI';
        return ['anthropic'=>'Anthropic','google'=>'Google','azure'=>'Azure OpenAI'][$type]??ucfirst($type);
    }

    private function extractOrg(string $type, array $body): ?string { return $body['organization_id']??$body['org_id']??null; }

    private function extractRegiao(string $type, array $body): string { return $type==='azure'?'Azure Region':($type==='google'?'Global':'US-East'); }

    private function encrypt(string $text): string
    {
        $iv  = openssl_random_pseudo_bytes(16);
        $enc = openssl_encrypt($text,'AES-256-CBC',$this->encKey,0,$iv);
        return base64_encode($iv.$enc);
    }

    private function maskApiKey(string $key): string
    {
        if (strlen($key)<=8) return str_repeat('*',strlen($key));
        return substr($key,0,6).str_repeat('*',max(6,strlen($key)-10)).substr($key,-4);
    }

    private function logAction(?int $providerId, string $acao, string $status, string $detalhe): void
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO cop_ai_provider_logs (provider_id,user_id,acao,status,detalhe,ip) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$providerId,$this->userId,$acao,$status,$detalhe,$_SERVER['REMOTE_ADDR']??null]);
        } catch (\Exception $e) { /* silencioso */ }
    }
}
