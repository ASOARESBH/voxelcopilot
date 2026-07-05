<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Middlewares\AuthMiddleware;
use App\Services\AiRouterService;

class AiRouterController extends Controller {

    private function pdo() { return Database::getInstance(); }
    private function uid() { return Auth::id(); }

    // ─── 1. DASHBOARD ────────────────────────────────────────────────────────────

    public function dashboard(): void {
        AuthMiddleware::handle();
        $pdo = $this->pdo();
        $uid = $this->uid();
        $hoje = date('Y-m-d');

        // Cards de métricas
        $providers_ativos = (int)$pdo->prepare("SELECT COUNT(*) FROM cop_ai_providers WHERE user_id = :uid AND is_active = 1")->execute(['uid'=>$uid]) ? $pdo->query("SELECT COUNT(*) FROM cop_ai_providers WHERE user_id = {$uid} AND is_active = 1")->fetchColumn() : 0;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cop_ai_providers WHERE user_id = :uid AND is_active = 1");
        $stmt->execute(['uid' => $uid]);
        $providers_ativos = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cop_ai_modelos m JOIN cop_ai_providers p ON p.id = m.provider_id WHERE p.user_id = :uid AND m.is_active = 1");
        $stmt->execute(['uid' => $uid]);
        $modelos_instalados = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(tokens_total),0) FROM cop_ai_custos_diarios WHERE user_id = :uid AND data_ref = :hoje");
        $stmt->execute(['uid' => $uid, 'hoje' => $hoje]);
        $tokens_hoje = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(custo_usd),0) FROM cop_ai_custos_diarios WHERE user_id = :uid AND data_ref = :hoje");
        $stmt->execute(['uid' => $uid, 'hoje' => $hoje]);
        $custo_hoje = (float)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COALESCE(AVG(tempo_ms),0) FROM cop_ai_historico WHERE user_id = :uid AND status = 'ok' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $stmt->execute(['uid' => $uid]);
        $tempo_medio = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT MAX(created_at) FROM cop_ai_historico WHERE user_id = :uid");
        $stmt->execute(['uid' => $uid]);
        $ultima_sync = $stmt->fetchColumn();

        // Gráfico uso por provider (últimos 7 dias)
        $stmt = $pdo->prepare("SELECT provider_nome, SUM(total_chamadas) as total FROM cop_ai_custos_diarios WHERE user_id = :uid AND data_ref >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY provider_nome ORDER BY total DESC");
        $stmt->execute(['uid' => $uid]);
        $uso_provider = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Últimas chamadas
        $stmt = $pdo->prepare("SELECT * FROM cop_ai_historico WHERE user_id = :uid ORDER BY created_at DESC LIMIT 10");
        $stmt->execute(['uid' => $uid]);
        $ultimas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('ai_router/dashboard', [
            'title'              => 'AI Router — Dashboard',
            'pageTitle'          => 'AI Router',
            'pageSubtitle'       => 'Central de inteligência do VOXEL Copilot',
            'providers_ativos'   => $providers_ativos,
            'modelos_instalados' => $modelos_instalados,
            'tokens_hoje'        => $tokens_hoje,
            'custo_hoje'         => $custo_hoje,
            'tempo_medio'        => $tempo_medio,
            'ultima_sync'        => $ultima_sync,
            'uso_provider'       => $uso_provider,
            'ultimas'            => $ultimas,
        ]);
    }

    // ─── 2. PROVIDERS ────────────────────────────────────────────────────────────

    public function providers(): void {
        AuthMiddleware::handle();
        $pdo = $this->pdo();
        $uid = $this->uid();

        $stmt = $pdo->prepare("SELECT * FROM cop_ai_providers WHERE user_id = :uid ORDER BY is_default DESC, nome ASC");
        $stmt->execute(['uid' => $uid]);
        $providers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT * FROM cop_ai_prompt_base WHERE user_id = :uid AND is_active = 1 ORDER BY especialidade");
        $stmt->execute(['uid' => $uid]);
        $prompts_base = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('ai_router/providers', [
            'title'        => 'AI Router — Providers',
            'pageTitle'    => 'Providers de IA',
            'pageSubtitle' => 'Gerencie suas integrações com provedores de IA',
            'providers'    => $providers,
            'prompts_base' => $prompts_base,
            'csrf_token'   => $this->csrfToken(),
        ]);
    }

    public function providerSalvar(): void {
        AuthMiddleware::handle();
        $pdo = $this->pdo();
        $uid = $this->uid();

        $id          = (int)($_POST['id'] ?? 0);
        $isDefault   = !empty($_POST['is_default']) ? 1 : 0;

        // Se vai ser padrão, remove padrão dos outros
        if ($isDefault) {
            $pdo->prepare("UPDATE cop_ai_providers SET is_default = 0 WHERE user_id = :uid")->execute(['uid' => $uid]);
        }

        $data = [
            'nome'             => trim($_POST['nome'] ?? ''),
            'descricao'        => trim($_POST['descricao'] ?? ''),
            'provider_tipo'    => $_POST['provider_tipo'] ?? 'openai',
            'endpoint'         => trim($_POST['endpoint'] ?? ''),
            'modelo_padrao'    => trim($_POST['modelo_padrao'] ?? ''),
            'temperatura'      => (float)($_POST['temperatura'] ?? 0.1),
            'max_tokens'       => (int)($_POST['max_tokens'] ?? 4000),
            'timeout_seg'      => (int)($_POST['timeout_seg'] ?? 120),
            'retry'            => (int)($_POST['retry'] ?? 3),
            'top_p'            => (float)($_POST['top_p'] ?? 1.0),
            'frequency_penalty'=> (float)($_POST['frequency_penalty'] ?? 0.0),
            'presence_penalty' => (float)($_POST['presence_penalty'] ?? 0.0),
            'idioma'           => $_POST['idioma'] ?? 'pt',
            'prompt_base_id'   => !empty($_POST['prompt_base_id']) ? (int)$_POST['prompt_base_id'] : null,
            'is_default'       => $isDefault,
            'is_active'        => 1,
        ];

        // API Key — só atualiza se fornecida
        $apiKey = trim($_POST['api_key'] ?? '');

        if ($id > 0) {
            $sets = "nome=:nome, descricao=:descricao, provider_tipo=:provider_tipo, endpoint=:endpoint, modelo_padrao=:modelo_padrao, temperatura=:temperatura, max_tokens=:max_tokens, timeout_seg=:timeout_seg, retry=:retry, top_p=:top_p, frequency_penalty=:frequency_penalty, presence_penalty=:presence_penalty, idioma=:idioma, prompt_base_id=:prompt_base_id, is_default=:is_default, updated_at=NOW()";
            if ($apiKey) $sets .= ", api_key=:api_key";
            $stmt = $pdo->prepare("UPDATE cop_ai_providers SET {$sets} WHERE id=:id AND user_id=:uid");
            $params = array_merge($data, ['id' => $id, 'uid' => $uid]);
            if ($apiKey) $params['api_key'] = $apiKey;
            $stmt->execute($params);
        } else {
            $data['user_id']  = $uid;
            $data['api_key']  = $apiKey;
            $cols = implode(',', array_keys($data));
            $vals = ':' . implode(', :', array_keys($data));
            $pdo->prepare("INSERT INTO cop_ai_providers ({$cols}) VALUES ({$vals})")->execute($data);
            $id = (int)$pdo->lastInsertId();

            // Cria modelos padrão para o provider
            self::criarModelosPadrao($pdo, $id, $data['provider_tipo']);
        }

        $this->json(['ok' => true, 'id' => $id]);
    }

    public function providerExcluir(): void {
        AuthMiddleware::handle();
        $id = (int)($_POST['id'] ?? 0);
        $this->pdo()->prepare("UPDATE cop_ai_providers SET is_active = 0 WHERE id = :id AND user_id = :uid")
            ->execute(['id' => $id, 'uid' => $this->uid()]);
        $this->json(['ok' => true]);
    }

    public function providerTestar(): void {
        AuthMiddleware::handle();
        $pdo = $this->pdo();
        $uid = $this->uid();

        $id = (int)($_POST['provider_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM cop_ai_providers WHERE id = :id AND user_id = :uid LIMIT 1");
        $stmt->execute(['id' => $id, 'uid' => $uid]);
        $provider = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$provider) {
            $this->json(['ok' => false, 'erro' => 'Provider não encontrado']);
            return;
        }

        $provider['modelo'] = $provider['modelo_padrao'] ?? 'gpt-4o';

        $start = microtime(true);
        try {
            $result = AiRouterService::route([
                'provider_id' => $id,
                'prompt'      => 'Responda apenas: "VOXEL Copilot AI Router OK"',
                'tipo'        => 'teste',
                'temperature' => 0.0,
                'tokens'      => 50,
            ]);
            $tempo = (int)((microtime(true) - $start) * 1000);
            $this->json([
                'ok'       => $result['ok'],
                'latencia' => $tempo,
                'modelo'   => $result['modelo'] ?? '',
                'resposta' => substr($result['texto'] ?? '', 0, 200),
                'erro'     => $result['erro'] ?? null,
            ]);
        } catch (\Exception $e) {
            $this->json(['ok' => false, 'erro' => $e->getMessage()]);
        }
    }

    // ─── 3. MODELOS ──────────────────────────────────────────────────────────────

    public function modelos(): void {
        AuthMiddleware::handle();
        $pdo = $this->pdo();
        $uid = $this->uid();

        $stmt = $pdo->prepare("SELECT m.*, p.nome as provider_nome, p.provider_tipo FROM cop_ai_modelos m JOIN cop_ai_providers p ON p.id = m.provider_id WHERE p.user_id = :uid ORDER BY p.nome, m.nome");
        $stmt->execute(['uid' => $uid]);
        $modelos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT id, nome, provider_tipo FROM cop_ai_providers WHERE user_id = :uid AND is_active = 1 ORDER BY nome");
        $stmt->execute(['uid' => $uid]);
        $providers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('ai_router/modelos', [
            'title'        => 'AI Router — Modelos',
            'pageTitle'    => 'Modelos de IA',
            'pageSubtitle' => 'Catálogo de modelos instalados',
            'modelos'      => $modelos,
            'providers'    => $providers,
            'csrf_token'   => $this->csrfToken(),
        ]);
    }

    // ─── 4. PROMPT BASE ──────────────────────────────────────────────────────────

    public function promptBase(): void {
        AuthMiddleware::handle();
        $pdo = $this->pdo();
        $uid = $this->uid();

        $stmt = $pdo->prepare("SELECT * FROM cop_ai_prompt_base WHERE user_id = :uid ORDER BY especialidade, versao DESC");
        $stmt->execute(['uid' => $uid]);
        $prompts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('ai_router/prompt_base', [
            'title'        => 'AI Router — Prompt Base',
            'pageTitle'    => 'Prompt Base',
            'pageSubtitle' => 'Prompts base por especialidade médica',
            'prompts'      => $prompts,
            'csrf_token'   => $this->csrfToken(),
        ]);
    }

    public function promptBaseSalvar(): void {
        AuthMiddleware::handle();
        $pdo = $this->pdo();
        $uid = $this->uid();
        $id  = (int)($_POST['id'] ?? 0);

        $data = [
            'user_id'            => $uid,
            'especialidade'      => $_POST['especialidade'] ?? 'radiologia_geral',
            'nome'               => trim($_POST['nome'] ?? ''),
            'versao'             => trim($_POST['versao'] ?? '1.0'),
            'modelo_recomendado' => trim($_POST['modelo_recomendado'] ?? ''),
            'temperatura'        => (float)($_POST['temperatura'] ?? 0.1),
            'prompt'             => trim($_POST['prompt'] ?? ''),
            'notas'              => trim($_POST['notas'] ?? ''),
            'is_active'          => 1,
        ];

        if ($id > 0) {
            unset($data['user_id']);
            $sets = implode(', ', array_map(fn($k) => "{$k}=:{$k}", array_keys($data)));
            $pdo->prepare("UPDATE cop_ai_prompt_base SET {$sets}, updated_at=NOW() WHERE id=:id AND user_id=:uid")
                ->execute(array_merge($data, ['id' => $id, 'uid' => $uid]));
        } else {
            $cols = implode(',', array_keys($data));
            $vals = ':' . implode(', :', array_keys($data));
            $pdo->prepare("INSERT INTO cop_ai_prompt_base ({$cols}) VALUES ({$vals})")->execute($data);
            $id = (int)$pdo->lastInsertId();
        }

        $this->json(['ok' => true, 'id' => $id]);
    }

    // ─── 5. PROMPT TEMPLATES ─────────────────────────────────────────────────────

    public function promptTemplates(): void {
        AuthMiddleware::handle();
        $pdo = $this->pdo();
        $uid = $this->uid();

        $stmt = $pdo->prepare("SELECT * FROM cop_ai_prompt_templates WHERE user_id = :uid ORDER BY tipo, nome");
        $stmt->execute(['uid' => $uid]);
        $templates = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('ai_router/prompt_templates', [
            'title'        => 'AI Router — Prompt Templates',
            'pageTitle'    => 'Prompt Templates',
            'pageSubtitle' => 'Templates reutilizáveis de prompts',
            'templates'    => $templates,
            'csrf_token'   => $this->csrfToken(),
        ]);
    }

    public function promptTemplateSalvar(): void {
        AuthMiddleware::handle();
        $pdo = $this->pdo();
        $uid = $this->uid();
        $id  = (int)($_POST['id'] ?? 0);

        $data = [
            'user_id'   => $uid,
            'nome'      => trim($_POST['nome'] ?? ''),
            'tipo'      => $_POST['tipo'] ?? 'laudo_estruturado',
            'descricao' => trim($_POST['descricao'] ?? ''),
            'prompt'    => trim($_POST['prompt'] ?? ''),
            'is_active' => 1,
        ];

        if ($id > 0) {
            unset($data['user_id']);
            $sets = implode(', ', array_map(fn($k) => "{$k}=:{$k}", array_keys($data)));
            $pdo->prepare("UPDATE cop_ai_prompt_templates SET {$sets}, updated_at=NOW() WHERE id=:id AND user_id=:uid")
                ->execute(array_merge($data, ['id' => $id, 'uid' => $uid]));
        } else {
            $cols = implode(',', array_keys($data));
            $vals = ':' . implode(', :', array_keys($data));
            $pdo->prepare("INSERT INTO cop_ai_prompt_templates ({$cols}) VALUES ({$vals})")->execute($data);
            $id = (int)$pdo->lastInsertId();
        }

        $this->json(['ok' => true, 'id' => $id]);
    }

    // ─── 6. ROTAS INTELIGENTES ───────────────────────────────────────────────────

    public function rotas(): void {
        AuthMiddleware::handle();
        $pdo = $this->pdo();
        $uid = $this->uid();

        $stmt = $pdo->prepare("SELECT r.*, p.nome as provider_nome, p.provider_tipo FROM cop_ai_rotas r JOIN cop_ai_providers p ON p.id = r.provider_id WHERE r.user_id = :uid ORDER BY r.tipo_solicitacao, r.prioridade DESC");
        $stmt->execute(['uid' => $uid]);
        $rotas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT id, nome, provider_tipo, modelo_padrao FROM cop_ai_providers WHERE user_id = :uid AND is_active = 1 ORDER BY nome");
        $stmt->execute(['uid' => $uid]);
        $providers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT id, nome, especialidade FROM cop_ai_prompt_base WHERE user_id = :uid AND is_active = 1 ORDER BY especialidade");
        $stmt->execute(['uid' => $uid]);
        $prompts_base = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('ai_router/rotas', [
            'title'        => 'AI Router — Rotas Inteligentes',
            'pageTitle'    => 'Rotas Inteligentes',
            'pageSubtitle' => 'Motor de decisão — qual IA usar para cada tarefa',
            'rotas'        => $rotas,
            'providers'    => $providers,
            'prompts_base' => $prompts_base,
            'csrf_token'   => $this->csrfToken(),
        ]);
    }

    public function rotaSalvar(): void {
        AuthMiddleware::handle();
        $pdo = $this->pdo();
        $uid = $this->uid();
        $id  = (int)($_POST['id'] ?? 0);

        $data = [
            'user_id'          => $uid,
            'nome'             => trim($_POST['nome'] ?? ''),
            'tipo_solicitacao' => $_POST['tipo_solicitacao'] ?? 'gerar_laudo',
            'provider_id'      => (int)($_POST['provider_id'] ?? 0),
            'modelo'           => trim($_POST['modelo'] ?? ''),
            'prompt_base_id'   => !empty($_POST['prompt_base_id']) ? (int)$_POST['prompt_base_id'] : null,
            'temperatura'      => !empty($_POST['temperatura']) ? (float)$_POST['temperatura'] : null,
            'max_tokens'       => !empty($_POST['max_tokens']) ? (int)$_POST['max_tokens'] : null,
            'prioridade'       => (int)($_POST['prioridade'] ?? 1),
            'is_active'        => 1,
        ];

        if ($id > 0) {
            unset($data['user_id']);
            $sets = implode(', ', array_map(fn($k) => "{$k}=:{$k}", array_keys($data)));
            $pdo->prepare("UPDATE cop_ai_rotas SET {$sets}, updated_at=NOW() WHERE id=:id AND user_id=:uid")
                ->execute(array_merge($data, ['id' => $id, 'uid' => $uid]));
        } else {
            $cols = implode(',', array_keys($data));
            $vals = ':' . implode(', :', array_keys($data));
            $pdo->prepare("INSERT INTO cop_ai_rotas ({$cols}) VALUES ({$vals})")->execute($data);
            $id = (int)$pdo->lastInsertId();
        }

        $this->json(['ok' => true, 'id' => $id]);
    }

    // ─── 7. HISTÓRICO ────────────────────────────────────────────────────────────

    public function historico(): void {
        AuthMiddleware::handle();
        $pdo  = $this->pdo();
        $uid  = $this->uid();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per  = 20;
        $off  = ($page - 1) * $per;

        $filtro_provider = trim($_GET['provider'] ?? '');
        $filtro_status   = trim($_GET['status'] ?? '');
        $filtro_data     = trim($_GET['data'] ?? '');

        $where = "WHERE user_id = :uid";
        $params = ['uid' => $uid];
        if ($filtro_provider) { $where .= " AND provider_nome = :prov"; $params['prov'] = $filtro_provider; }
        if ($filtro_status)   { $where .= " AND status = :status"; $params['status'] = $filtro_status; }
        if ($filtro_data)     { $where .= " AND DATE(created_at) = :data"; $params['data'] = $filtro_data; }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cop_ai_historico {$where}");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT * FROM cop_ai_historico {$where} ORDER BY created_at DESC LIMIT {$per} OFFSET {$off}");
        $stmt->execute($params);
        $historico = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT DISTINCT provider_nome FROM cop_ai_historico WHERE user_id = :uid ORDER BY provider_nome");
        $stmt->execute(['uid' => $uid]);
        $providers_lista = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $this->view('ai_router/historico', [
            'title'           => 'AI Router — Histórico',
            'pageTitle'       => 'Histórico de Chamadas',
            'pageSubtitle'    => 'Rastreabilidade completa de prompts e respostas',
            'historico'       => $historico,
            'total'           => $total,
            'page'            => $page,
            'per'             => $per,
            'providers_lista' => $providers_lista,
            'filtro_provider' => $filtro_provider,
            'filtro_status'   => $filtro_status,
            'filtro_data'     => $filtro_data,
        ]);
    }

    // ─── 8. TOKENS ───────────────────────────────────────────────────────────────

    public function tokens(): void {
        AuthMiddleware::handle();
        $pdo = $this->pdo();
        $uid = $this->uid();

        $stmt = $pdo->prepare("SELECT data_ref, SUM(tokens_total) as tokens, SUM(custo_usd) as custo, SUM(total_chamadas) as chamadas FROM cop_ai_custos_diarios WHERE user_id = :uid AND data_ref >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY data_ref ORDER BY data_ref ASC");
        $stmt->execute(['uid' => $uid]);
        $por_dia = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT provider_nome, SUM(tokens_total) as tokens, SUM(custo_usd) as custo, SUM(total_chamadas) as chamadas FROM cop_ai_custos_diarios WHERE user_id = :uid AND data_ref >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY provider_nome ORDER BY tokens DESC");
        $stmt->execute(['uid' => $uid]);
        $por_provider = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('ai_router/tokens', [
            'title'        => 'AI Router — Tokens',
            'pageTitle'    => 'Uso de Tokens',
            'pageSubtitle' => 'Consumo de tokens por provider e período',
            'por_dia'      => $por_dia,
            'por_provider' => $por_provider,
        ]);
    }

    // ─── 9. CUSTOS ───────────────────────────────────────────────────────────────

    public function custos(): void {
        AuthMiddleware::handle();
        $pdo = $this->pdo();
        $uid = $this->uid();
        $hoje = date('Y-m-d');

        $stmt = $pdo->prepare("SELECT provider_nome, SUM(custo_usd) as custo_hoje FROM cop_ai_custos_diarios WHERE user_id = :uid AND data_ref = :hoje GROUP BY provider_nome ORDER BY custo_hoje DESC");
        $stmt->execute(['uid' => $uid, 'hoje' => $hoje]);
        $custo_hoje_provider = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT SUM(custo_usd) as total FROM cop_ai_custos_diarios WHERE user_id = :uid AND data_ref = :hoje");
        $stmt->execute(['uid' => $uid, 'hoje' => $hoje]);
        $total_hoje = (float)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT SUM(custo_usd) as total FROM cop_ai_custos_diarios WHERE user_id = :uid AND YEAR(data_ref) = YEAR(CURDATE()) AND MONTH(data_ref) = MONTH(CURDATE())");
        $stmt->execute(['uid' => $uid]);
        $total_mes = (float)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT data_ref, provider_nome, custo_usd, tokens_total, total_chamadas FROM cop_ai_custos_diarios WHERE user_id = :uid ORDER BY data_ref DESC, custo_usd DESC LIMIT 60");
        $stmt->execute(['uid' => $uid]);
        $detalhe = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('ai_router/custos', [
            'title'                 => 'AI Router — Custos',
            'pageTitle'             => 'Custos de IA',
            'pageSubtitle'          => 'Controle financeiro do uso de inteligência artificial',
            'custo_hoje_provider'   => $custo_hoje_provider,
            'total_hoje'            => $total_hoje,
            'total_mes'             => $total_mes,
            'detalhe'               => $detalhe,
        ]);
    }

    // ─── 10. LOGS ────────────────────────────────────────────────────────────────

    public function logs(): void {
        AuthMiddleware::handle();
        $pdo  = $this->pdo();
        $uid  = $this->uid();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per  = 50;
        $off  = ($page - 1) * $per;
        $nivel = trim($_GET['nivel'] ?? '');

        $where  = "WHERE user_id = :uid";
        $params = ['uid' => $uid];
        if ($nivel) { $where .= " AND nivel = :nivel"; $params['nivel'] = $nivel; }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cop_ai_logs {$where}");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT * FROM cop_ai_logs {$where} ORDER BY created_at DESC LIMIT {$per} OFFSET {$off}");
        $stmt->execute($params);
        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('ai_router/logs', [
            'title'     => 'AI Router — Logs',
            'pageTitle' => 'Logs do AI Router',
            'pageSubtitle' => 'Rastreamento de eventos e erros',
            'logs'      => $logs,
            'total'     => $total,
            'page'      => $page,
            'per'       => $per,
            'nivel'     => $nivel,
        ]);
    }

    // ─── 11. TESTES ──────────────────────────────────────────────────────────────

    public function testes(): void {
        AuthMiddleware::handle();
        $pdo = $this->pdo();
        $uid = $this->uid();

        $stmt = $pdo->prepare("SELECT id, nome, provider_tipo, modelo_padrao FROM cop_ai_providers WHERE user_id = :uid AND is_active = 1 ORDER BY nome");
        $stmt->execute(['uid' => $uid]);
        $providers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('ai_router/testes', [
            'title'        => 'AI Router — Testes',
            'pageTitle'    => 'Testes de IA',
            'pageSubtitle' => 'Teste e compare providers em tempo real',
            'providers'    => $providers,
            'csrf_token'   => $this->csrfToken(),
        ]);
    }

    public function testeExecutar(): void {
        AuthMiddleware::handle();
        $prompt      = trim($_POST['prompt'] ?? '');
        $providerIds = array_map('intval', (array)($_POST['provider_ids'] ?? []));
        $temperatura = (float)($_POST['temperatura'] ?? 0.1);
        $tokens      = (int)($_POST['tokens'] ?? 1000);

        if (!$prompt || empty($providerIds)) {
            $this->json(['ok' => false, 'erro' => 'Prompt e ao menos um provider são obrigatórios']);
            return;
        }

        $resultados = AiRouterService::comparar([
            'prompt'      => $prompt,
            'tipo'        => 'teste',
            'temperature' => $temperatura,
            'tokens'      => $tokens,
        ], $providerIds);

        $this->json(['ok' => true, 'resultados' => $resultados]);
    }

    // ─── 12. CONFIGURAÇÕES ───────────────────────────────────────────────────────

    public function configuracoes(): void {
        AuthMiddleware::handle();
        $pdo = $this->pdo();
        $uid = $this->uid();

        $stmt = $pdo->prepare("SELECT chave, valor FROM cop_ai_config WHERE user_id = :uid");
        $stmt->execute(['uid' => $uid]);
        $rows   = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $config = [];
        foreach ($rows as $r) $config[$r['chave']] = $r['valor'];

        $this->view('ai_router/configuracoes', [
            'title'        => 'AI Router — Configurações',
            'pageTitle'    => 'Configurações do AI Router',
            'pageSubtitle' => 'Parâmetros globais de inteligência artificial',
            'config'       => $config,
            'csrf_token'   => $this->csrfToken(),
        ]);
    }

    public function configuracoesSalvar(): void {
        AuthMiddleware::handle();
        $pdo = $this->pdo();
        $uid = $this->uid();

        $chaves = ['modo_fallback', 'log_nivel', 'custo_alerta_usd', 'tokens_alerta', 'timeout_global', 'retry_global'];
        foreach ($chaves as $chave) {
            if (isset($_POST[$chave])) {
                $stmt = $pdo->prepare("SELECT id FROM cop_ai_config WHERE user_id = :uid AND chave = :chave LIMIT 1");
                $stmt->execute(['uid' => $uid, 'chave' => $chave]);
                if ($stmt->fetchColumn()) {
                    $pdo->prepare("UPDATE cop_ai_config SET valor = :val, updated_at = NOW() WHERE user_id = :uid AND chave = :chave")
                        ->execute(['val' => $_POST[$chave], 'uid' => $uid, 'chave' => $chave]);
                } else {
                    $pdo->prepare("INSERT INTO cop_ai_config (user_id, chave, valor) VALUES (:uid, :chave, :val)")
                        ->execute(['uid' => $uid, 'chave' => $chave, 'val' => $_POST[$chave]]);
                }
            }
        }

        $this->json(['ok' => true]);
    }

    // ─── API ÚNICA: POST /api/ai/router ──────────────────────────────────────────

    public function apiRouter(): void {
        AuthMiddleware::handle();

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        if (empty($input['prompt'])) {
            $this->json(['ok' => false, 'erro' => 'Campo prompt é obrigatório']);
            return;
        }

        $resultado = AiRouterService::route($input);
        $this->json($resultado);
    }

    // ─── HELPERS ─────────────────────────────────────────────────────────────────

    private static function criarModelosPadrao(\PDO $pdo, int $providerId, string $tipo): void {
        $modelos = [
            'openai'        => [['gpt-4o','GPT-4o',128000,0.005,0.015,1,1],['gpt-4o-mini','GPT-4o Mini',128000,0.00015,0.0006,0,0],['gpt-4.1','GPT-4.1',128000,0.002,0.008,1,1]],
            'anthropic'     => [['claude-3-5-sonnet-20241022','Claude 3.5 Sonnet',200000,0.003,0.015,1,1],['claude-3-haiku-20240307','Claude 3 Haiku',200000,0.00025,0.00125,0,0]],
            'google_gemini' => [['gemini-1.5-pro','Gemini 1.5 Pro',1000000,0.00125,0.005,1,1],['gemini-1.5-flash','Gemini 1.5 Flash',1000000,0.000075,0.0003,0,0]],
            'deepseek'      => [['deepseek-chat','DeepSeek Chat',128000,0.00014,0.00028,0,0],['deepseek-coder','DeepSeek Coder',128000,0.00014,0.00028,0,0]],
            'mistral'       => [['mistral-large-latest','Mistral Large',128000,0.002,0.006,0,0],['mistral-small-latest','Mistral Small',128000,0.001,0.003,0,0]],
        ];

        $lista = $modelos[$tipo] ?? [];
        foreach ($lista as $m) {
            $pdo->prepare("INSERT INTO cop_ai_modelos (provider_id, nome, nome_display, contexto_tokens, preco_input, preco_output, suporta_vision, suporta_tools) VALUES (:pid, :nome, :display, :ctx, :pi, :po, :vision, :tools)")
                ->execute(['pid' => $providerId, 'nome' => $m[0], 'display' => $m[1], 'ctx' => $m[2], 'pi' => $m[3], 'po' => $m[4], 'vision' => $m[5], 'tools' => $m[6]]);
        }
    }
}
