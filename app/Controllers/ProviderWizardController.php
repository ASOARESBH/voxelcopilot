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
        $result     = ['ok'=>false,'steps'=>[],'latencia_ms'=>null,'tempo_total_ms'=>null,'tempo_ia_ms'=>null,'context_max'=>null,'saldo'=>null,'saldo_disp'=>false,'caps'=>[]];
        $totalStart = microtime(true);

        $authResult = $this->testConnection($type,$apiKey,$endpoint,$deployment,$apiVersion);
        $result['steps'][] = ['num'=>1,'nome'=>'Autenticacao','ok'=>$authResult['ok'],'detalhe'=>$authResult['ok']?'API Key valida':($authResult['error']??'Falha')];
        if (!$authResult['ok']) { $result['steps'][] = ['num'=>2,'nome'=>'Geracao de texto','ok'=>false,'detalhe'=>'Pulado']; return $result; }
        $result['latencia_ms'] = $authResult['latencia_ms']??null;

        $genStart  = microtime(true);
        $genResult = $this->testGeneration($type,$apiKey,$endpoint,$modelId,$deployment,$apiVersion);
        $genTime   = round((microtime(true)-$genStart)*1000);
        $result['steps'][] = ['num'=>2,'nome'=>'Geracao de texto','ok'=>$genResult['ok'],'detalhe'=>$genResult['ok']?('Resposta: "'.($genResult['response']??'').'"'):($genResult['error']??'Falha')];
        $result['tempo_ia_ms'] = $genTime;

        $result['steps'][] = ['num'=>3,'nome'=>'Tempo de resposta','ok'=>true,'detalhe'=>"Latencia: {$result['latencia_ms']}ms | IA: {$genTime}ms"];

        $caps = $this->detectCapabilities($type,$modelId);
        $result['caps']    = $caps;
        $result['steps'][] = ['num'=>4,'nome'=>'Capacidades','ok'=>true,'detalhe'=>implode(', ',array_keys(array_filter($caps)))];

        $ctx = $this->contextWindowForModel($modelId);
        $result['context_max'] = $ctx;
        $result['steps'][] = ['num'=>5,'nome'=>'Contexto maximo','ok'=>true,'detalhe'=>$ctx?number_format($ctx).' tokens':'Nao identificado'];

        $result['steps'][] = ['num'=>6,'nome'=>'Endpoint','ok'=>true,'detalhe'=>$authResult['endpoint']??$endpoint];
        $result['steps'][] = ['num'=>7,'nome'=>'Versao da API','ok'=>true,'detalhe'=>$authResult['api_version']??'v1'];
        $result['steps'][] = ['num'=>8,'nome'=>'Saldo','ok'=>true,'detalhe'=>'Saldo indisponivel para este Provider'];

        $result['tempo_total_ms'] = round((microtime(true)-$totalStart)*1000);
        $result['steps'][] = ['num'=>9,'nome'=>'Benchmark salvo','ok'=>true,'detalhe'=>"Total: {$result['tempo_total_ms']}ms"];

        $result['ok'] = $genResult['ok'];
        return $result;
    }

    private function testGeneration(string $type, string $apiKey, string $endpoint, string $modelId, string $deployment, string $apiVersion): array
    {
        try {
            $payload = json_encode(['model'=>$modelId,'messages'=>[['role'=>'user','content'=>'Responda apenas: OK']],'max_tokens'=>10]);
            switch ($type) {
                case 'openai': case 'openrouter': case 'deepseek': case 'mistral': case 'qwen': case 'custom': case 'lmstudio':
                    $ep  = rtrim($endpoint?:$this->getDefaultEndpoint($type),'/');
                    $res = $this->httpPost("$ep/chat/completions",$payload,['Authorization: Bearer '.$apiKey,'Content-Type: application/json']);
                    break;
                case 'anthropic':
                    $ep   = rtrim($endpoint?:'https://api.anthropic.com','/');
                    $body = json_encode(['model'=>$modelId,'max_tokens'=>10,'messages'=>[['role'=>'user','content'=>'Responda apenas: OK']]]);
                    $res  = $this->httpPost("$ep/v1/messages",$body,['x-api-key: '.$apiKey,'anthropic-version: 2023-06-01','Content-Type: application/json']);
                    break;
                case 'google':
                    $ep   = rtrim($endpoint?:'https://generativelanguage.googleapis.com/v1beta','/');
                    $body = json_encode(['contents'=>[['parts'=>[['text'=>'Responda apenas: OK']]]]]);
                    $res  = $this->httpPost("$ep/models/$modelId:generateContent?key=$apiKey",$body,['Content-Type: application/json']);
                    break;
                case 'azure':
                    $ep  = rtrim($endpoint,'/');
                    $ver = $apiVersion?:'2024-02-01';
                    $res = $this->httpPost("$ep/openai/deployments/$deployment/chat/completions?api-version=$ver",$payload,['api-key: '.$apiKey,'Content-Type: application/json']);
                    break;
                case 'ollama':
                    $ep   = rtrim($endpoint?:'http://localhost:11434','/');
                    $body = json_encode(['model'=>$modelId,'messages'=>[['role'=>'user','content'=>'Responda apenas: OK']],'stream'=>false]);
                    $res  = $this->httpPost("$ep/api/chat",$body,['Content-Type: application/json']);
                    break;
                default: return ['ok'=>false,'error'=>'Provider nao suportado'];
            }
            if ($res['ok']) {
                $b    = json_decode($res['body'],true)??[];
                $text = $b['choices'][0]['message']['content']??$b['content'][0]['text']??$b['candidates'][0]['content']['parts'][0]['text']??$b['message']['content']??'OK';
                return ['ok'=>true,'response'=>trim(substr($text,0,20))];
            }
            return ['ok'=>false,'error'=>'HTTP '.$res['code']];
        } catch (\Exception $e) { return ['ok'=>false,'error'=>$e->getMessage()]; }
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
