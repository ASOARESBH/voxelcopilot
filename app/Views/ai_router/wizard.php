<?php
// VOXEL Copilot — Wizard de Provider do AI Router
// View SPA com 5 etapas: Provider → Credenciais → Descoberta → Configuração → Validação
// NÃO usar $this->layout() — renderizado via View::render() com require direto
use App\Core\Auth;

$pageTitle  = $title ?? 'Configurar Provider — AI Router';
$providers  = $providers  ?? [];
$caps       = $capabilities ?? [];

// Indexar capabilities por tipo
$capsByType = [];
foreach ($caps as $c) {
    $capsByType[$c['provider_type']] = $c;
}

// Logos SVG inline por provider
function providerLogoSvg($type) {
    $logos = [
        'openai' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.28 9.65a5.93 5.93 0 0 0-.51-4.87 6 6 0 0 0-6.44-2.88 5.93 5.93 0 0 0-4.47-2A6 6 0 0 0 5.14 3.5 5.93 5.93 0 0 0 1.2 6.38a6 6 0 0 0 .74 7.12 5.93 5.93 0 0 0 .51 4.87 6 6 0 0 0 6.44 2.88 5.93 5.93 0 0 0 4.47 2 6 6 0 0 0 5.72-4.16 5.93 5.93 0 0 0 3.94-2.88 6 6 0 0 0-.74-7.56zM13.38 21a4.44 4.44 0 0 1-2.85-1.03l.14-.08 4.73-2.73a.78.78 0 0 0 .39-.68v-6.67l2 1.15a.07.07 0 0 1 .04.06v5.52A4.47 4.47 0 0 1 13.38 21zm-9.58-4.1a4.44 4.44 0 0 1-.53-3l.14.08 4.73 2.73a.78.78 0 0 0 .78 0l5.78-3.34v2.3a.07.07 0 0 1-.03.06L9.9 18.5a4.47 4.47 0 0 1-6.1-1.6zm-1.24-10.4a4.44 4.44 0 0 1 2.32-1.95v5.6a.78.78 0 0 0 .39.67l5.77 3.33-2 1.15a.07.07 0 0 1-.07 0L4.6 12.1a4.47 4.47 0 0 1-2.04-5.6zm16.44 3.83L13.22 7l2-1.15a.07.07 0 0 1 .07 0l4.37 2.52a4.47 4.47 0 0 1-.69 8.06v-5.6a.78.78 0 0 0-.37-.5zm2-3.02l-.14-.08-4.73-2.73a.78.78 0 0 0-.78 0L9.57 8.84V6.54a.07.07 0 0 1 .03-.06l4.37-2.52a4.47 4.47 0 0 1 6.63 4.63v.02l-.6-.3zM8.55 12.85 6.56 11.7a.07.07 0 0 1-.04-.06V6.12a4.47 4.47 0 0 1 7.33-3.43l-.14.08-4.73 2.73a.78.78 0 0 0-.39.68v6.67l-.04-.0zm1.08-2.35 2.57-1.48 2.57 1.48v2.96l-2.57 1.48-2.57-1.48V10.5z" fill="#10a37f"/></svg>',
        'anthropic' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.3 3.5H13.8L19.5 20.5H23L17.3 3.5Z" fill="#D97706"/><path d="M6.7 3.5L1 20.5H4.6L5.8 16.9H11.9L13.1 20.5H16.7L11 3.5H6.7ZM6.8 13.9L8.85 7.7L10.9 13.9H6.8Z" fill="#D97706"/></svg>',
        'google' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>',
        'azure' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M13.05 4.24L6.56 18.05l2.68.01 1.27-3.17h4.97l1.1 3.16 2.62-.01L13.05 4.24zm-.1 3.69l1.81 5.09h-3.49l1.68-5.09z" fill="#0078D4"/></svg>',
        'ollama' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" fill="#1a1a1a"/><text x="12" y="16" text-anchor="middle" font-size="10" fill="white" font-family="monospace">ol</text></svg>',
        'openrouter' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="#6366f1" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'lmstudio' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="24" rx="4" fill="#7c3aed"/><text x="12" y="16" text-anchor="middle" font-size="9" fill="white" font-family="monospace">LM</text></svg>',
        'deepseek' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z" fill="#0066cc"/></svg>',
        'mistral' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M3 3h4v4H3V3zm7 0h4v4h-4V3zm7 0h4v4h-4V3zM3 10h4v4H3v-4zm7 0h4v4h-4v-4zm7 0h4v4h-4v-4zM3 17h4v4H3v-4zm14 0h4v4h-4v-4z" fill="#FF7000"/></svg>',
        'qwen' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" fill="#6B21A8"/><path d="M12 6v6l4 2" stroke="#6B21A8" stroke-width="2" fill="none" stroke-linecap="round"/></svg>',
        'custom' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="#64748b" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    ];
    return $logos[$type] ?? $logos['custom'];
}

function starsHtml($rating) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $rating ? '★' : '☆';
    }
    return $html;
}

?>

<div class="wizard-page">
  <div class="wizard-container">

    <!-- ── Header ─────────────────────────────────────────────── -->
    <div class="wizard-header">
      <div class="wizard-header-left">
        <div class="wizard-header-icon">🔌</div>
        <div>
          <div class="wizard-header-title">Configurar Provider de IA</div>
          <div class="wizard-header-sub">Configure provedores de IA para o AI Router do VOXEL Copilot</div>
        </div>
      </div>
      <div class="wizard-header-actions">
        <a href="/ai-router/providers" class="btn btn-secondary btn-sm">← Voltar</a>
      </div>
    </div>

    <!-- ── Providers Existentes ───────────────────────────────── -->
    <?php if (!empty($providers)): ?>
    <div class="existing-providers">
      <div class="existing-providers-title">
        <span>🔗</span>
        Providers Configurados (<?= count($providers) ?>)
      </div>
      <?php foreach ($providers as $p): ?>
        <?php $cap = $capsByType[$p['provider_type']] ?? null; ?>
        <div class="existing-provider-item">
          <div class="existing-provider-logo">
            <?= providerLogoSvg($p['provider_type']) ?>
          </div>
          <div class="existing-provider-info">
            <div class="existing-provider-name"><?= htmlspecialchars($p['nome']) ?></div>
            <div class="existing-provider-type">
              <?= htmlspecialchars($cap['nome_exibicao'] ?? $p['provider_type']) ?>
              <?php if ($p['api_key_mask']): ?>
                &nbsp;·&nbsp; <code style="font-size:11px;color:#64748b;"><?= htmlspecialchars($p['api_key_mask']) ?></code>
              <?php endif; ?>
              <?php if ($p['is_default']): ?>
                &nbsp;<span class="provider-badge green">Padrão</span>
              <?php endif; ?>
            </div>
          </div>
          <span class="existing-provider-status <?= $p['status_conexao'] === 'conectado' ? 'ok' : ($p['status_conexao'] === 'erro' ? 'error' : 'pending') ?>">
            <?= $p['status_conexao'] === 'conectado' ? '✓ Conectado' : ($p['status_conexao'] === 'erro' ? '✗ Erro' : '⏳ Pendente') ?>
          </span>
          <div class="existing-provider-actions">
            <button class="btn btn-secondary btn-sm" onclick="editProvider(<?= $p['id'] ?>, '<?= htmlspecialchars($p['provider_type']) ?>')">
              ✏️ Editar
            </button>
            <button class="btn btn-ghost btn-sm" onclick="deleteProvider(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nome']) ?>')">
              🗑️
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── Barra de Progresso ─────────────────────────────────── -->
    <div class="wizard-progress-bar">
      <div class="wizard-steps" id="wizardSteps">
        <div class="wizard-progress-line" id="progressLine" style="width:0%"></div>
        <div class="wizard-step-item active" data-step="1" onclick="goToStep(1)">
          <div class="wizard-step-circle" id="stepCircle1">1</div>
          <div class="wizard-step-label">Provider</div>
        </div>
        <div class="wizard-step-item" data-step="2" onclick="goToStep(2)">
          <div class="wizard-step-circle" id="stepCircle2">2</div>
          <div class="wizard-step-label">Credenciais</div>
        </div>
        <div class="wizard-step-item" data-step="3" onclick="goToStep(3)">
          <div class="wizard-step-circle" id="stepCircle3">3</div>
          <div class="wizard-step-label">Descoberta</div>
        </div>
        <div class="wizard-step-item" data-step="4" onclick="goToStep(4)">
          <div class="wizard-step-circle" id="stepCircle4">4</div>
          <div class="wizard-step-label">Configuração</div>
        </div>
        <div class="wizard-step-item" data-step="5" onclick="goToStep(5)">
          <div class="wizard-step-circle" id="stepCircle5">5</div>
          <div class="wizard-step-label">Validação</div>
        </div>
      </div>
    </div>

    <!-- ── Corpo do Wizard ────────────────────────────────────── -->
    <div class="wizard-body">

      <!-- ════════════════════════════════════════════════════════
           ETAPA 1 — Escolha do Provider
           ════════════════════════════════════════════════════════ -->
      <div class="wizard-step-panel active" id="panel1">
        <div class="step-title">Escolha o Provider de IA</div>
        <div class="step-subtitle">Selecione o provedor que deseja configurar. Provedores homologados foram testados e validados pela equipe VOXEL.</div>

        <div class="provider-grid" id="providerGrid">
          <?php foreach ($caps as $c): ?>
          <div class="provider-card"
               data-type="<?= htmlspecialchars($c['provider_type']) ?>"
               onclick="selectProvider('<?= htmlspecialchars($c['provider_type']) ?>')">

            <?php if ($c['homologado']): ?>
              <span class="provider-homologado">✓ Homologado</span>
            <?php endif; ?>

            <div class="provider-logo">
              <?= providerLogoSvg($c['provider_type']) ?>
            </div>
            <div class="provider-name"><?= htmlspecialchars($c['nome_exibicao']) ?></div>
            <div class="provider-desc"><?= htmlspecialchars(mb_substr($c['descricao'] ?? '', 0, 60)) ?>...</div>

            <div class="provider-badge-row">
              <?php
              $tags = array_filter(array_map('trim', explode(',', $c['tags'] ?? '')));
              foreach (array_slice($tags, 0, 3) as $tag):
              ?>
                <span class="provider-badge"><?= htmlspecialchars($tag) ?></span>
              <?php endforeach; ?>
            </div>

            <div class="provider-stars"><?= starsHtml((int)$c['rating']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <div id="step1Alert" class="alert alert-info" style="display:none">
          <span class="alert-icon">ℹ️</span>
          <span id="step1AlertMsg"></span>
        </div>
      </div>

      <!-- ════════════════════════════════════════════════════════
           ETAPA 2 — Credenciais
           ════════════════════════════════════════════════════════ -->
      <div class="wizard-step-panel" id="panel2">
        <div class="step-title">Configurar Credenciais</div>
        <div class="step-subtitle">Insira as credenciais do provider. A API Key será criptografada com AES-256 e nunca exibida em texto puro.</div>

        <div class="cred-form-wrapper">
          <div id="credFields">
            <!-- Campos dinâmicos injetados via JS -->
          </div>

          <!-- Teste de Conexão -->
          <div class="test-connection-bar" id="testConnectionBar">
            <div class="test-status-icon" id="testIcon">🔌</div>
            <div class="test-status-text">
              <div class="test-status-title" id="testTitle">Pronto para testar</div>
              <div class="test-status-detail" id="testDetail">Preencha as credenciais e clique em "Testar Conexão"</div>
            </div>
            <button class="btn btn-secondary btn-sm" onclick="testConnection()" id="btnTestConn">
              🔍 Testar Conexão
            </button>
          </div>

          <div id="step2Alert" style="margin-top:12px"></div>
        </div>
      </div>

      <!-- ════════════════════════════════════════════════════════
           ETAPA 3 — Descoberta de Modelos
           ════════════════════════════════════════════════════════ -->
      <div class="wizard-step-panel" id="panel3">
        <div class="step-title">Modelos Descobertos</div>
        <div class="step-subtitle">O sistema consultou a API e listou todos os modelos disponíveis. Selecione o modelo padrão para uso nos laudos.</div>

        <!-- Info da conta -->
        <div class="discovery-info-grid" id="discoveryInfoGrid">
          <div class="discovery-info-card">
            <div class="discovery-info-label">Conta</div>
            <div class="discovery-info-value" id="discConta">—</div>
          </div>
          <div class="discovery-info-card">
            <div class="discovery-info-label">Endpoint</div>
            <div class="discovery-info-value" id="discEndpoint" style="font-size:12px;word-break:break-all">—</div>
          </div>
          <div class="discovery-info-card">
            <div class="discovery-info-label">Latência</div>
            <div class="discovery-info-value green" id="discLatencia">—</div>
          </div>
          <div class="discovery-info-card">
            <div class="discovery-info-label">Status</div>
            <div class="discovery-info-value green" id="discStatus">—</div>
          </div>
          <div class="discovery-info-card">
            <div class="discovery-info-label">Região</div>
            <div class="discovery-info-value" id="discRegiao">—</div>
          </div>
          <div class="discovery-info-card">
            <div class="discovery-info-label">API Version</div>
            <div class="discovery-info-value" id="discApiVersion">—</div>
          </div>
        </div>

        <!-- Tabela de modelos -->
        <div id="modelsTableWrap" class="models-table-wrap">
          <table class="models-table">
            <thead>
              <tr>
                <th style="width:40px">Sel.</th>
                <th>Modelo</th>
                <th>Contexto</th>
                <th>Chat</th>
                <th>Vision</th>
                <th>Stream</th>
                <th>JSON</th>
                <th>Funções</th>
                <th>Reasoning</th>
                <th>Long Ctx</th>
              </tr>
            </thead>
            <tbody id="modelsTableBody">
              <tr><td colspan="10" style="text-align:center;padding:32px;color:#64748b">
                Clique em "Descobrir Modelos" para listar os modelos disponíveis
              </td></tr>
            </tbody>
          </table>
        </div>

        <div style="margin-top:16px;display:flex;gap:10px;align-items:center">
          <button class="btn btn-secondary" onclick="discoverModels()" id="btnDiscover">
            🔍 Descobrir Modelos
          </button>
          <span id="modelsCount" style="font-size:13px;color:#64748b"></span>
        </div>

        <div id="step3Alert" style="margin-top:12px"></div>
      </div>

      <!-- ════════════════════════════════════════════════════════
           ETAPA 4 — Configurações Avançadas
           ════════════════════════════════════════════════════════ -->
      <div class="wizard-step-panel" id="panel4">
        <div class="step-title">Configurações Avançadas</div>
        <div class="step-subtitle">Ajuste os parâmetros do modelo para otimizar a geração de laudos médicos.</div>

        <div class="config-grid">
          <div class="config-section-title">Parâmetros de Geração</div>

          <!-- Temperatura -->
          <div class="form-group">
            <label class="form-label">Temperatura</label>
            <div class="slider-wrap">
              <input type="range" min="0" max="2" step="0.1" value="0.1" id="cfgTemp"
                     oninput="document.getElementById('cfgTempVal').textContent=this.value">
              <span class="slider-value" id="cfgTempVal">0.1</span>
            </div>
            <div class="form-hint">Valores baixos (0.0–0.3) para laudos precisos. Valores altos para respostas mais criativas.</div>
          </div>

          <!-- Max Tokens -->
          <div class="form-group">
            <label class="form-label">Max Tokens</label>
            <div class="slider-wrap">
              <input type="range" min="256" max="32768" step="256" value="4096" id="cfgMaxTokens"
                     oninput="document.getElementById('cfgMaxTokensVal').textContent=Number(this.value).toLocaleString()">
              <span class="slider-value" id="cfgMaxTokensVal">4.096</span>
            </div>
            <div class="form-hint">Número máximo de tokens na resposta. Laudos complexos podem precisar de 8.000+.</div>
          </div>

          <!-- Top P -->
          <div class="form-group">
            <label class="form-label">Top P</label>
            <div class="slider-wrap">
              <input type="range" min="0" max="1" step="0.05" value="1" id="cfgTopP"
                     oninput="document.getElementById('cfgTopPVal').textContent=this.value">
              <span class="slider-value" id="cfgTopPVal">1.0</span>
            </div>
          </div>

          <!-- Frequency Penalty -->
          <div class="form-group">
            <label class="form-label">Frequency Penalty</label>
            <div class="slider-wrap">
              <input type="range" min="-2" max="2" step="0.1" value="0" id="cfgFreqPenalty"
                     oninput="document.getElementById('cfgFreqPenaltyVal').textContent=this.value">
              <span class="slider-value" id="cfgFreqPenaltyVal">0.0</span>
            </div>
          </div>

          <!-- Presence Penalty -->
          <div class="form-group">
            <label class="form-label">Presence Penalty</label>
            <div class="slider-wrap">
              <input type="range" min="-2" max="2" step="0.1" value="0" id="cfgPresPenalty"
                     oninput="document.getElementById('cfgPresPenaltyVal').textContent=this.value">
              <span class="slider-value" id="cfgPresPenaltyVal">0.0</span>
            </div>
          </div>

          <!-- Timeout -->
          <div class="form-group">
            <label class="form-label">Timeout (segundos)</label>
            <input type="number" class="form-control" id="cfgTimeout" value="30" min="5" max="300">
          </div>

          <!-- Retry -->
          <div class="form-group">
            <label class="form-label">Tentativas (retry)</label>
            <input type="number" class="form-control" id="cfgRetry" value="3" min="1" max="10">
          </div>

          <!-- Idioma -->
          <div class="form-group">
            <label class="form-label">Idioma padrão</label>
            <select class="form-control" id="cfgIdioma">
              <option value="pt" selected>Português (Brasil)</option>
              <option value="en">English</option>
              <option value="es">Español</option>
            </select>
          </div>

          <div class="config-section-title">Modo de Operação</div>

          <div class="mode-selector">
            <button class="mode-btn active" data-mode="producao" onclick="selectMode(this)">
              🚀 Produção
            </button>
            <button class="mode-btn" data-mode="teste" onclick="selectMode(this)">
              🧪 Teste
            </button>
            <button class="mode-btn" data-mode="sandbox" onclick="selectMode(this)">
              🏖️ Sandbox
            </button>
          </div>

          <div class="config-section-title">Opções</div>

          <div style="grid-column:1/-1">
            <div class="toggle-row">
              <div>
                <div class="toggle-label">Provider Padrão</div>
                <div class="toggle-desc">Usar este provider como padrão para todos os laudos</div>
              </div>
              <label class="toggle-switch">
                <input type="checkbox" id="cfgIsDefault">
                <span class="toggle-slider"></span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- ════════════════════════════════════════════════════════
           ETAPA 5 — Validação e Diagnóstico Completo
           ════════════════════════════════════════════════════════ -->
      <div class="wizard-step-panel" id="panel5">
        <div class="validation-panel" style="margin:0 auto">

          <!-- Cabeçalho de status -->
          <div class="validation-header">
            <div class="validation-icon-big" id="valIconBig">🔌</div>
            <div class="validation-title" id="valTitle">Pronto para Validar</div>
            <div class="validation-sub" id="valSub">Clique em "Validar Provider" para executar os 9 testes de verificação</div>
          </div>

          <!-- Barra de progresso -->
          <div class="validation-progress" id="valProgressWrap" style="display:none">
            <div class="validation-progress-fill" id="valProgressFill" style="width:0%"></div>
          </div>

          <!-- Lista de 9 testes -->
          <ul class="validation-steps-list" id="valStepsList">
            <?php
            $valSteps = [
                1 => 'Autenticação',
                2 => 'Diagnóstico da API',
                3 => 'Tempo de resposta',
                4 => 'Capacidades',
                5 => 'Contexto máximo',
                6 => 'Endpoint',
                7 => 'Versão da API',
                8 => 'Saldo',
                9 => 'Benchmark salvo',
            ];
            foreach ($valSteps as $n => $nome):
            ?>
            <li class="validation-step-item" id="valStep<?= $n ?>">
              <div class="val-step-num"><?= $n ?></div>
              <div class="val-step-name"><?= $nome ?></div>
              <div class="val-step-detail" id="valStepDetail<?= $n ?>">Aguardando...</div>
              <div class="val-step-icon" id="valStepIcon<?= $n ?>">⏳</div>
            </li>
            <?php endforeach; ?>
          </ul>

          <!-- ── Painel de Diagnóstico Detalhado (aparece após validação) ── -->
          <div id="diagPanel" style="display:none">

            <!-- Alerta de erro categorizado -->
            <div id="diagErrorAlert" style="display:none" class="diag-error-alert">
              <div class="diag-error-header">
                <span class="diag-error-icon" id="diagErrorIcon">⚠</span>
                <div>
                  <div class="diag-error-title" id="diagErrorTitle">Erro</div>
                  <div class="diag-error-cat" id="diagErrorCat"></div>
                </div>
              </div>
              <div class="diag-error-msg" id="diagErrorMsg"></div>
              <div class="diag-orientacao" id="diagOrientacao" style="display:none">
                <div class="diag-orientacao-label">💡 Como resolver</div>
                <div class="diag-orientacao-text" id="diagOrientacaoText"></div>
              </div>
            </div>

            <!-- Seção: Detalhes da Requisição -->
            <div class="diag-section">
              <div class="diag-section-title">🔍 Detalhes da Requisição</div>
              <div class="diag-grid">
                <div class="diag-row">
                  <span class="diag-label">Endpoint utilizado</span>
                  <span class="diag-value diag-mono" id="diagEndpoint">—</span>
                </div>
                <div class="diag-row">
                  <span class="diag-label">Modelo enviado</span>
                  <span class="diag-value diag-mono" id="diagModelo">—</span>
                </div>
                <div class="diag-row">
                  <span class="diag-label">HTTP Status</span>
                  <span class="diag-value" id="diagHttpStatus">—</span>
                </div>
                <div class="diag-row">
                  <span class="diag-label">Tempo da requisição</span>
                  <span class="diag-value" id="diagTempo">—</span>
                </div>
                <div class="diag-row">
                  <span class="diag-label">Tamanho do prompt</span>
                  <span class="diag-value" id="diagPromptChars">—</span>
                </div>
                <div class="diag-row">
                  <span class="diag-label">Tokens solicitados</span>
                  <span class="diag-value" id="diagTokensSolic">—</span>
                </div>
                <div class="diag-row" id="diagTokensUsadosRow" style="display:none">
                  <span class="diag-label">Tokens utilizados</span>
                  <span class="diag-value" id="diagTokensUsados">—</span>
                </div>
              </div>
            </div>

            <!-- Seção: Payload enviado -->
            <div class="diag-section">
              <div class="diag-section-title">📤 Payload Enviado <span class="diag-badge-masked">API Key mascarada</span></div>
              <pre class="diag-code" id="diagPayload">—</pre>
            </div>

            <!-- Seção: Resposta do Provider -->
            <div class="diag-section">
              <div class="diag-section-title" id="diagRespTitle">📥 Resposta do Provider</div>
              <pre class="diag-code" id="diagRespostaRaw">—</pre>
            </div>

            <!-- Seção: Campos de erro estruturados (só aparece em erro) -->
            <div class="diag-section" id="diagErroCamposSection" style="display:none">
              <div class="diag-section-title">🔎 Campos de Erro Estruturados</div>
              <div class="diag-grid">
                <div class="diag-row" id="diagErroTipoRow" style="display:none">
                  <span class="diag-label">type</span>
                  <span class="diag-value diag-mono diag-err" id="diagErroTipo">—</span>
                </div>
                <div class="diag-row" id="diagErroCodeRow" style="display:none">
                  <span class="diag-label">code</span>
                  <span class="diag-value diag-mono diag-err" id="diagErroCode">—</span>
                </div>
                <div class="diag-row" id="diagErroParamRow" style="display:none">
                  <span class="diag-label">param</span>
                  <span class="diag-value diag-mono diag-err" id="diagErroParam">—</span>
                </div>
                <div class="diag-row" id="diagErroMsgRow" style="display:none">
                  <span class="diag-label">message</span>
                  <span class="diag-value diag-err" id="diagErroMsgFull">—</span>
                </div>
              </div>
            </div>

          </div><!-- /#diagPanel -->

          <div style="text-align:center;margin-top:16px">
            <button class="btn btn-primary btn-lg" id="btnValidate" onclick="runValidation()">
              🔬 VALIDAR PROVIDER
            </button>
          </div>
        </div>
      </div>

      <!-- ── Footer de Navegação ─────────────────────────────── -->
      <div class="wizard-footer">
        <div class="wizard-footer-left">
          <button class="btn btn-secondary" id="btnBack" onclick="prevStep()" style="display:none">
            ← Anterior
          </button>
        </div>
        <div class="wizard-footer-right">
          <button class="btn btn-secondary" onclick="window.location.href='/ai-router/providers'">
            Cancelar
          </button>
          <button class="btn btn-primary" id="btnNext" onclick="nextStep()">
            Próximo →
          </button>
          <button class="btn btn-success" id="btnSave" onclick="saveProvider()" style="display:none">
            💾 Salvar Provider
          </button>
        </div>
      </div>

    </div><!-- /.wizard-body -->
  </div><!-- /.wizard-container -->
</div><!-- /.wizard-page -->

<!-- ── Loading Overlay ──────────────────────────────────────── -->
<div class="wiz-loading-overlay" id="loadingOverlay" style="display:none">
  <div class="wiz-loading-spinner"></div>
  <div class="wiz-loading-text" id="loadingText">Processando...</div>
</div>

<script>
// ============================================================
// VOXEL Copilot — Wizard de Provider (JavaScript SPA)
// ============================================================

const WIZ = {
  currentStep: 1,
  totalSteps:  5,
  selectedType: null,
  credData:    {},
  connInfo:    {},
  models:      [],
  selectedModel: null,
  editingId:   null,

  // Capabilities por tipo (injetado do PHP)
  caps: <?= json_encode($capsByType, JSON_UNESCAPED_UNICODE) ?>,
};

// ─── Navegação ───────────────────────────────────────────────
function goToStep(n) {
  if (n < 1 || n > WIZ.totalSteps) return;
  if (n > WIZ.currentStep) {
    // Só avança se a etapa atual estiver válida
    if (!validateCurrentStep()) return;
  }
  WIZ.currentStep = n;
  renderStep();
}

function nextStep() {
  if (!validateCurrentStep()) return;
  if (WIZ.currentStep < WIZ.totalSteps) {
    WIZ.currentStep++;
    renderStep();
    // Auto-ações ao entrar em certas etapas
    if (WIZ.currentStep === 3 && WIZ.models.length === 0) {
      discoverModels();
    }
  }
}

function prevStep() {
  if (WIZ.currentStep > 1) {
    WIZ.currentStep--;
    renderStep();
  }
}

function renderStep() {
  const n = WIZ.currentStep;

  // Painéis
  document.querySelectorAll('.wizard-step-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('panel' + n).classList.add('active');

  // Indicadores
  document.querySelectorAll('.wizard-step-item').forEach((el, i) => {
    const stepN = i + 1;
    el.classList.remove('active', 'done');
    if (stepN === n)       el.classList.add('active');
    else if (stepN < n)    el.classList.add('done');
  });

  // Círculos
  for (let i = 1; i <= WIZ.totalSteps; i++) {
    const c = document.getElementById('stepCircle' + i);
    if (i < n)      c.innerHTML = '✓';
    else if (i === n) c.textContent = i;
    else            c.textContent = i;
  }

  // Linha de progresso
  const pct = ((n - 1) / (WIZ.totalSteps - 1)) * 100;
  document.getElementById('progressLine').style.width = pct + '%';

  // Botões de navegação
  document.getElementById('btnBack').style.display = n > 1 ? 'inline-flex' : 'none';
  document.getElementById('btnNext').style.display = n < WIZ.totalSteps ? 'inline-flex' : 'none';
  document.getElementById('btnSave').style.display = n === WIZ.totalSteps ? 'inline-flex' : 'none';

  // Etapa 2: renderizar campos
  if (n === 2) renderCredFields();
  // Etapa 4: preencher com valores atuais
  if (n === 4) fillConfigStep();
}

function validateCurrentStep() {
  const n = WIZ.currentStep;
  if (n === 1) {
    if (!WIZ.selectedType) {
      showStep1Alert('Selecione um provider para continuar.', 'warning');
      return false;
    }
  }
  if (n === 2) {
    const cap = WIZ.caps[WIZ.selectedType];
    if (!cap) return true;
    const fields = JSON.parse(cap.campos_json || '[]');
    for (const f of fields) {
      if (f.required) {
        const el = document.getElementById('cred_' + f.key);
        if (!el || !el.value.trim()) {
          el && el.classList.add('error');
          showStep2Alert('O campo "' + f.label + '" é obrigatório.', 'error');
          return false;
        }
      }
    }
    collectCredData();
  }
  if (n === 3) {
    if (!WIZ.selectedModel) {
      showStep3Alert('Selecione um modelo para continuar.', 'warning');
      return false;
    }
  }
  return true;
}

// ─── Etapa 1: Seleção de Provider ────────────────────────────
function selectProvider(type) {
  WIZ.selectedType = type;
  WIZ.models = [];
  WIZ.selectedModel = null;
  WIZ.connInfo = {};

  document.querySelectorAll('.provider-card').forEach(c => {
    c.classList.toggle('selected', c.dataset.type === type);
  });

  const cap = WIZ.caps[type];
  if (cap) {
    showStep1Alert(cap.nome_exibicao + ' selecionado. ' + (cap.descricao || ''), 'info');
  }
}

function showStep1Alert(msg, type) {
  const el = document.getElementById('step1Alert');
  const msgEl = document.getElementById('step1AlertMsg');
  el.className = 'alert alert-' + type;
  msgEl.textContent = msg;
  el.style.display = 'flex';
}

// ─── Etapa 2: Campos de Credenciais ──────────────────────────
function renderCredFields() {
  const cap = WIZ.caps[WIZ.selectedType];
  if (!cap) return;

  const fields = JSON.parse(cap.campos_json || '[]');
  const container = document.getElementById('credFields');
  container.innerHTML = '';

  fields.forEach(f => {
    const div = document.createElement('div');
    div.className = 'form-group';

    const label = document.createElement('label');
    label.className = 'form-label';
    label.htmlFor = 'cred_' + f.key;
    label.innerHTML = f.label + (f.required ? '<span class="required">*</span>' : '');
    div.appendChild(label);

    if (f.type === 'password') {
      const wrap = document.createElement('div');
      wrap.className = 'input-password-wrap';

      const input = document.createElement('input');
      input.type = 'password';
      input.className = 'form-control';
      input.id = 'cred_' + f.key;
      input.placeholder = f.placeholder || '';
      input.value = WIZ.credData[f.key] || '';
      input.addEventListener('input', () => { input.classList.remove('error'); });
      wrap.appendChild(input);

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'btn-toggle-password';
      btn.innerHTML = '👁';
      btn.onclick = () => {
        input.type = input.type === 'password' ? 'text' : 'password';
        btn.innerHTML = input.type === 'password' ? '👁' : '🙈';
      };
      wrap.appendChild(btn);
      div.appendChild(wrap);
    } else {
      const input = document.createElement('input');
      input.type = f.type || 'text';
      input.className = 'form-control';
      input.id = 'cred_' + f.key;
      input.placeholder = f.placeholder || '';
      input.value = WIZ.credData[f.key] || '';
      input.addEventListener('input', () => { input.classList.remove('error'); });
      div.appendChild(input);
    }

    if (f.key === 'api_key' && WIZ.caps[WIZ.selectedType] && WIZ.caps[WIZ.selectedType].doc_url) {
      const hint = document.createElement('div');
      hint.className = 'form-hint';
      hint.innerHTML = 'Obtenha sua API Key em: <a href="' + WIZ.caps[WIZ.selectedType].doc_url + '" target="_blank">Documentação oficial ↗</a>';
      div.appendChild(hint);
    }

    container.appendChild(div);
  });
}

function collectCredData() {
  const cap = WIZ.caps[WIZ.selectedType];
  if (!cap) return;
  const fields = JSON.parse(cap.campos_json || '[]');
  fields.forEach(f => {
    const el = document.getElementById('cred_' + f.key);
    if (el) WIZ.credData[f.key] = el.value.trim();
  });
}

function showStep2Alert(msg, type) {
  const el = document.getElementById('step2Alert');
  el.innerHTML = '<div class="alert alert-' + type + '"><span class="alert-icon">' +
    (type === 'error' ? '❌' : type === 'success' ? '✅' : 'ℹ️') +
    '</span><span>' + msg + '</span></div>';
}

// ─── Teste de Conexão ─────────────────────────────────────────
async function testConnection() {
  collectCredData();

  const icon  = document.getElementById('testIcon');
  const title = document.getElementById('testTitle');
  const detail = document.getElementById('testDetail');
  const btn   = document.getElementById('btnTestConn');

  icon.className  = 'test-status-icon loading';
  icon.textContent = '⟳';
  title.textContent = 'Testando conexão...';
  detail.textContent = 'Aguarde, conectando à API do provider';
  btn.disabled = true;

  try {
    const payload = {
      provider_type: WIZ.selectedType,
      api_key:       WIZ.credData.api_key       || '',
      endpoint:      WIZ.credData.endpoint      || '',
      deployment:    WIZ.credData.deployment    || '',
      api_version:   WIZ.credData.api_version   || '',
    };

    const res = await fetch('/api/ai/provider/test', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await res.json();

    if (data.ok) {
      icon.className  = 'test-status-icon ok';
      icon.textContent = '✓';
      title.textContent = 'Conexão estabelecida!';
      detail.textContent = 'Latência: ' + data.latencia_ms + 'ms · ' + (data.conta || '') + (data.organizacao ? ' · ' + data.organizacao : '');
      WIZ.connInfo = data;
      showStep2Alert('Conexão testada com sucesso! Clique em "Próximo" para descobrir os modelos.', 'success');
    } else {
      icon.className  = 'test-status-icon error';
      icon.textContent = '✗';
      title.textContent = 'Falha na conexão';
      detail.textContent = data.error || 'Verifique as credenciais e tente novamente';
      showStep2Alert('Erro: ' + (data.error || 'Falha na conexão'), 'error');
    }
  } catch (e) {
    icon.className  = 'test-status-icon error';
    icon.textContent = '✗';
    title.textContent = 'Erro de rede';
    detail.textContent = e.message;
  } finally {
    btn.disabled = false;
  }
}

// ─── Etapa 3: Descoberta de Modelos ──────────────────────────
async function discoverModels() {
  collectCredData();

  const btn = document.getElementById('btnDiscover');
  const tbody = document.getElementById('modelsTableBody');
  const count = document.getElementById('modelsCount');

  btn.disabled = true;
  btn.textContent = '⟳ Descobrindo...';
  tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:32px;color:#64748b">Consultando a API...</td></tr>';

  try {
    const payload = {
      provider_type: WIZ.selectedType,
      api_key:       WIZ.credData.api_key       || '',
      endpoint:      WIZ.credData.endpoint      || '',
      deployment:    WIZ.credData.deployment    || '',
      api_version:   WIZ.credData.api_version   || '',
    };

    const res = await fetch('/api/ai/provider/discover-models', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await res.json();

    if (data.ok && data.models && data.models.length > 0) {
      WIZ.models = data.models;
      renderModelsTable(data.models);
      count.textContent = data.models.length + ' modelo(s) encontrado(s)';

      // Preencher info da conta
      if (WIZ.connInfo.conta)      document.getElementById('discConta').textContent      = WIZ.connInfo.conta;
      if (WIZ.connInfo.endpoint)   document.getElementById('discEndpoint').textContent   = WIZ.connInfo.endpoint;
      if (WIZ.connInfo.latencia_ms) document.getElementById('discLatencia').textContent  = WIZ.connInfo.latencia_ms + 'ms';
      if (WIZ.connInfo.status)     document.getElementById('discStatus').textContent     = WIZ.connInfo.status;
      if (WIZ.connInfo.regiao)     document.getElementById('discRegiao').textContent     = WIZ.connInfo.regiao;
      if (WIZ.connInfo.api_version) document.getElementById('discApiVersion').textContent = WIZ.connInfo.api_version;

      // Selecionar o recomendado automaticamente
      const rec = data.models.find(m => m.recommended || m.selected);
      if (rec) selectModel(rec.id || rec.model_id);

    } else {
      tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:32px;color:#ef4444">Nenhum modelo encontrado. Verifique as credenciais.</td></tr>';
      count.textContent = '';
    }
  } catch (e) {
    tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:32px;color:#ef4444">Erro: ' + e.message + '</td></tr>';
  } finally {
    btn.disabled = false;
    btn.innerHTML = '🔍 Descobrir Modelos';
  }
}

function renderModelsTable(models) {
  const tbody = document.getElementById('modelsTableBody');
  tbody.innerHTML = '';

  models.forEach(m => {
    const id = m.id || m.model_id;
    const tr = document.createElement('tr');
    tr.id = 'modelRow_' + id.replace(/[^a-z0-9]/gi, '_');
    if (m.selected) tr.classList.add('selected-model');

    const cap = (v) => '<span class="cap-dot ' + (v ? 'on' : 'off') + '"></span>';
    const ctx = m.context_window ? (m.context_window >= 1000000 ? (m.context_window/1000000).toFixed(1)+'M' : (m.context_window >= 1000 ? Math.round(m.context_window/1000)+'K' : m.context_window)) : '—';

    tr.innerHTML = `
      <td><input type="radio" class="model-radio" name="selectedModel" value="${id}"
           ${m.selected ? 'checked' : ''} onchange="selectModel('${id}')"></td>
      <td>
        <strong>${m.name || m.model_name || id}</strong>
        ${m.recommended ? '<span class="model-badge-rec">Recomendado</span>' : ''}
        <div style="font-size:11px;color:#64748b;margin-top:2px">${id}</div>
      </td>
      <td>${ctx}</td>
      <td>${cap(m.cap_chat)}</td>
      <td>${cap(m.cap_vision)}</td>
      <td>${cap(m.cap_streaming)}</td>
      <td>${cap(m.cap_json || m.cap_json_mode)}</td>
      <td>${cap(m.cap_functions || m.cap_function_call)}</td>
      <td>${cap(m.cap_reasoning)}</td>
      <td>${cap(m.cap_long_ctx || m.cap_long_context)}</td>
    `;
    tbody.appendChild(tr);
  });
}

function selectModel(id) {
  WIZ.selectedModel = id;
  document.querySelectorAll('#modelsTableBody tr').forEach(tr => {
    tr.classList.remove('selected-model');
  });
  const rowId = 'modelRow_' + id.replace(/[^a-z0-9]/gi, '_');
  const row = document.getElementById(rowId);
  if (row) row.classList.add('selected-model');

  // Marcar radio
  document.querySelectorAll('.model-radio').forEach(r => {
    r.checked = r.value === id;
  });

  // Atualizar modelos
  WIZ.models.forEach(m => {
    const mid = m.id || m.model_id;
    m.selected = (mid === id) ? 1 : 0;
  });
}

function showStep3Alert(msg, type) {
  const el = document.getElementById('step3Alert');
  el.innerHTML = '<div class="alert alert-' + type + '"><span class="alert-icon">' +
    (type === 'error' ? '❌' : type === 'warning' ? '⚠️' : 'ℹ️') +
    '</span><span>' + msg + '</span></div>';
}

// ─── Etapa 4: Configurações ───────────────────────────────────
function fillConfigStep() {
  // Valores já preenchidos pelos sliders — apenas garantir
}

function selectMode(btn) {
  document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}

// ─── Etapa 5: Validação e Diagnóstico Completo ───────────────
async function runValidation() {
  const btn = document.getElementById('btnValidate');
  btn.disabled = true;
  btn.innerHTML = '⟳ Validando...';

  // Ocultar painel de diagnóstico anterior
  document.getElementById('diagPanel').style.display = 'none';
  document.getElementById('diagErrorAlert').style.display = 'none';

  document.getElementById('valProgressWrap').style.display = 'block';
  document.getElementById('valProgressFill').style.width = '0%';
  document.getElementById('valIconBig').className = 'validation-icon-big';
  document.getElementById('valIconBig').textContent = '⟳';
  document.getElementById('valTitle').textContent = 'Validando Provider...';
  document.getElementById('valSub').textContent = 'Executando 9 testes de verificação';

  // Resetar todos os steps
  for (let i = 1; i <= 9; i++) {
    const item = document.getElementById('valStep' + i);
    item.className = 'validation-step-item';
    document.getElementById('valStepDetail' + i).textContent = 'Aguardando...';
    document.getElementById('valStepIcon' + i).textContent = '⏳';
  }

  try {
    const payload = {
      provider_type: WIZ.selectedType,
      api_key:       WIZ.credData.api_key     || '',
      endpoint:      WIZ.credData.endpoint    || '',
      deployment:    WIZ.credData.deployment  || '',
      api_version:   WIZ.credData.api_version || '',
      model_id:      WIZ.selectedModel        || '',
    };

    // Animar steps enquanto aguarda resposta
    let animStep = 1;
    const animInterval = setInterval(() => {
      if (animStep <= 9) {
        document.getElementById('valStep' + animStep).className = 'validation-step-item running';
        document.getElementById('valStepDetail' + animStep).textContent = 'Testando...';
        document.getElementById('valStepIcon' + animStep).textContent = '⟳';
        document.getElementById('valProgressFill').style.width = ((animStep / 9) * 70) + '%';
        animStep++;
      }
    }, 400);

    const res  = await fetch('/api/ai/provider/validate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    clearInterval(animInterval);

    // ── Preencher resultados nos steps ──
    if (data.steps && data.steps.length > 0) {
      data.steps.forEach((s, i) => {
        const n    = s.num || (i + 1);
        const item = document.getElementById('valStep' + n);
        if (!item) return;
        item.className = 'validation-step-item ' + (s.ok ? 'ok' : 'error');
        document.getElementById('valStepDetail' + n).textContent = s.detalhe || '';
        document.getElementById('valStepIcon' + n).textContent   = s.ok ? '✓' : '✗';
        document.getElementById('valProgressFill').style.width   = ((n / 9) * 100) + '%';
      });
    } else {
      for (let i = 1; i <= 9; i++) {
        document.getElementById('valStep' + i).className = 'validation-step-item ok';
        document.getElementById('valStepDetail' + i).textContent = 'Verificado';
        document.getElementById('valStepIcon' + i).textContent   = '✓';
      }
      document.getElementById('valProgressFill').style.width = '100%';
    }

    // ── Cabeçalho de resultado ──
    const allOk   = data.ok !== false;
    const bigIcon = document.getElementById('valIconBig');
    bigIcon.className   = 'validation-icon-big ' + (allOk ? 'ok' : 'error');
    bigIcon.textContent = allOk ? '✓' : '✗';
    document.getElementById('valTitle').textContent = allOk
      ? 'Provider Validado com Sucesso!'
      : 'Validação com Erros — Veja o diagnóstico abaixo';
    document.getElementById('valSub').textContent = allOk
      ? 'Todos os testes passaram. Clique em "Salvar Provider" para finalizar.'
      : 'Alguns testes falharam. Analise o diagnóstico detalhado e corrija as configurações.';
    document.getElementById('valProgressFill').style.width = '100%';

    // ── Popular painel de diagnóstico ──
    const diag = data.diagnostico || {};
    renderDiagnostico(diag, data, allOk);

  } catch (e) {
    document.getElementById('valTitle').textContent   = 'Erro na Validação';
    document.getElementById('valSub').textContent     = e.message;
    document.getElementById('valIconBig').className   = 'validation-icon-big error';
    document.getElementById('valIconBig').textContent = '✗';
  } finally {
    btn.disabled = false;
    btn.innerHTML = '🔬 VALIDAR PROVIDER';
  }
}

/** Popula o painel de diagnóstico detalhado com os dados retornados pelo backend */
function renderDiagnostico(diag, data, allOk) {
  const panel = document.getElementById('diagPanel');
  panel.style.display = 'block';

  // ── Detalhes da requisição ──
  setText('diagEndpoint',    diag.endpoint_usado     || '—');
  setText('diagModelo',      diag.modelo_enviado     || '—');
  setText('diagTempo',       data.tempo_ia_ms != null ? data.tempo_ia_ms + ' ms' : '—');
  setText('diagPromptChars', diag.prompt_chars != null ? diag.prompt_chars + ' caracteres' : '—');
  setText('diagTokensSolic', diag.tokens_solicitados != null ? diag.tokens_solicitados + ' tokens' : '—');

  // HTTP Status com cor
  const httpEl = document.getElementById('diagHttpStatus');
  const code   = diag.http_status;
  if (code) {
    httpEl.textContent = code;
    httpEl.className   = 'diag-value diag-http-' + (code >= 200 && code < 300 ? 'ok' : code >= 400 && code < 500 ? 'warn' : 'err');
  } else {
    httpEl.textContent = '—';
    httpEl.className   = 'diag-value';
  }

  // Tokens utilizados (só em sucesso)
  if (diag.tokens_usados != null) {
    document.getElementById('diagTokensUsadosRow').style.display = '';
    setText('diagTokensUsados', diag.tokens_usados + ' tokens');
  } else {
    document.getElementById('diagTokensUsadosRow').style.display = 'none';
  }

  // ── Payload (com API Key mascarada) ──
  if (diag.payload && Object.keys(diag.payload).length > 0) {
    document.getElementById('diagPayload').textContent = JSON.stringify(diag.payload, null, 2);
  } else {
    document.getElementById('diagPayload').textContent = '—';
  }

  // ── Resposta bruta do provider ──
  const respTitle = document.getElementById('diagRespTitle');
  const respEl    = document.getElementById('diagRespostaRaw');
  if (allOk) {
    respTitle.textContent = '📥 Resposta do Provider (sucesso)';
    respEl.className      = 'diag-code diag-code-ok';
  } else {
    respTitle.textContent = '📥 Resposta do Provider (erro)';
    respEl.className      = 'diag-code diag-code-err';
  }
  if (diag.resposta_raw) {
    try {
      const parsed = JSON.parse(diag.resposta_raw);
      respEl.textContent = JSON.stringify(parsed, null, 2);
    } catch (e) {
      respEl.textContent = diag.resposta_raw;
    }
  } else {
    respEl.textContent = '—';
  }

  // ── Alerta de erro categorizado ──
  if (!allOk && diag.erro_categoria && diag.erro_categoria !== 'ok') {
    const alertEl = document.getElementById('diagErrorAlert');
    alertEl.style.display = 'block';
    alertEl.className     = 'diag-error-alert diag-cat-' + diag.erro_categoria;

    const catLabels = {
      'rate_limit':        { icon: '⚠️', title: 'Rate Limit Excedido',                    cat: 'HTTP 429 — Limite de requisições por minuto atingido' },
      'quota_insuficiente':{ icon: '🚫', title: 'Créditos Insuficientes (insufficient_quota)', cat: 'HTTP 429 — Saldo insuficiente na conta do provider' },
      'auth_invalida':     { icon: '🔑', title: 'API Key Inválida ou Revogada',            cat: 'HTTP 401 — Falha de autenticação' },
      'modelo_invalido':   { icon: '🤖', title: 'Modelo Não Encontrado',                   cat: 'HTTP 404 — O modelo informado não existe ou não está disponível' },
      'endpoint_invalido': { icon: '🔌', title: 'Endpoint Inacessível',                    cat: 'Falha de conexão com o servidor' },
      'timeout':           { icon: '⏱️', title: 'Timeout na Requisição',                  cat: 'O servidor não respondeu dentro do tempo limite' },
      'outro':             { icon: '✗',  title: 'Erro Inesperado',                         cat: 'Verifique a mensagem de erro abaixo' },
    };
    const info = catLabels[diag.erro_categoria] || catLabels['outro'];
    setText('diagErrorIcon',  info.icon);
    setText('diagErrorTitle', info.title);
    setText('diagErrorCat',   info.cat);
    setText('diagErrorMsg',   diag.erro_mensagem || '—');

    if (diag.orientacao) {
      document.getElementById('diagOrientacao').style.display = 'block';
      setText('diagOrientacaoText', diag.orientacao);
    } else {
      document.getElementById('diagOrientacao').style.display = 'none';
    }

    // Campos estruturados
    const hasCampos = diag.erro_tipo || diag.erro_code || diag.erro_param || diag.erro_mensagem;
    document.getElementById('diagErroCamposSection').style.display = hasCampos ? 'block' : 'none';
    showDiagField('diagErroTipoRow',  'diagErroTipo',    diag.erro_tipo);
    showDiagField('diagErroCodeRow',  'diagErroCode',    diag.erro_code);
    showDiagField('diagErroParamRow', 'diagErroParam',   diag.erro_param);
    showDiagField('diagErroMsgRow',   'diagErroMsgFull', diag.erro_mensagem);
  } else {
    document.getElementById('diagErrorAlert').style.display       = 'none';
    document.getElementById('diagErroCamposSection').style.display = 'none';
  }
}

function setText(id, val) {
  const el = document.getElementById(id);
  if (el) el.textContent = val;
}

function showDiagField(rowId, valId, val) {
  const row = document.getElementById(rowId);
  const el  = document.getElementById(valId);
  if (val) {
    row.style.display = '';
    el.textContent    = val;
  } else {
    row.style.display = 'none';
  }
}

// ─── Salvar Provider ──────────────────────────────────────────
async function saveProvider() {
  collectCredData();

  showLoading('Salvando provider...');

  const modo = document.querySelector('.mode-btn.active')?.dataset.mode || 'producao';

  const payload = {
    provider_id:   WIZ.editingId,
    provider_type: WIZ.selectedType,
    nome:          WIZ.credData.nome || WIZ.caps[WIZ.selectedType]?.nome_exibicao || WIZ.selectedType,
    api_key:       WIZ.credData.api_key       || '',
    endpoint:      WIZ.credData.endpoint      || '',
    deployment:    WIZ.credData.deployment    || '',
    api_version:   WIZ.credData.api_version   || '',
    regiao:        WIZ.connInfo.regiao        || '',
    organizacao:   WIZ.connInfo.organizacao   || '',
    conta:         WIZ.connInfo.conta         || '',
    modo:          modo,
    is_default:    document.getElementById('cfgIsDefault').checked ? 1 : 0,
    temperatura:   parseFloat(document.getElementById('cfgTemp').value),
    max_tokens:    parseInt(document.getElementById('cfgMaxTokens').value),
    timeout_s:     parseInt(document.getElementById('cfgTimeout').value),
    retry:         parseInt(document.getElementById('cfgRetry').value),
    top_p:         parseFloat(document.getElementById('cfgTopP').value),
    freq_penalty:  parseFloat(document.getElementById('cfgFreqPenalty').value),
    pres_penalty:  parseFloat(document.getElementById('cfgPresPenalty').value),
    idioma:        document.getElementById('cfgIdioma').value,
    wizard_step:   5,
    models:        WIZ.models,
  };

  try {
    const res = await fetch('/ai-router/providers/salvar', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await res.json();

    hideLoading();

    if (data.ok) {
      showToast('Provider salvo com sucesso!', 'success');
      setTimeout(() => { window.location.href = '/ai-router/providers'; }, 1500);
    } else {
      showToast('Erro ao salvar: ' + (data.error || 'Erro desconhecido'), 'error');
    }
  } catch (e) {
    hideLoading();
    showToast('Erro: ' + e.message, 'error');
  }
}

// ─── Editar Provider Existente ────────────────────────────────
function editProvider(id, type) {
  WIZ.editingId = id;
  WIZ.selectedType = type;
  WIZ.currentStep = 1;
  selectProvider(type);
  renderStep();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ─── Excluir Provider ─────────────────────────────────────────
async function deleteProvider(id, nome) {
  if (!confirm('Excluir o provider "' + nome + '"? Esta ação não pode ser desfeita.')) return;

  showLoading('Excluindo provider...');

  try {
    const res = await fetch('/ai-router/providers/excluir', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id }),
    });
    const data = await res.json();
    hideLoading();

    if (data.ok) {
      showToast('Provider excluído.', 'success');
      setTimeout(() => location.reload(), 1000);
    } else {
      showToast('Erro: ' + (data.error || 'Erro desconhecido'), 'error');
    }
  } catch (e) {
    hideLoading();
    showToast('Erro: ' + e.message, 'error');
  }
}

// ─── Utilitários ──────────────────────────────────────────────
function showLoading(msg) {
  document.getElementById('loadingText').textContent = msg || 'Processando...';
  document.getElementById('loadingOverlay').style.display = 'flex';
}

function hideLoading() {
  document.getElementById('loadingOverlay').style.display = 'none';
}

function showToast(msg, type) {
  const t = document.createElement('div');
  t.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;padding:14px 20px;border-radius:8px;font-size:14px;font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,.15);animation:wizFadeIn .25s ease;max-width:360px;';
  t.style.background = type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#1e4db7';
  t.style.color = '#fff';
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}

// ─── Init ─────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  renderStep();
});
</script>


