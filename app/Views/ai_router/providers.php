
<style>
.air-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;}
.air-nav-btn{padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;border:1px solid #e2e8f0;background:#fff;color:#374151;text-decoration:none;transition:all .15s;}
.air-nav-btn:hover,.air-nav-btn.active{background:#2563eb;color:#fff;border-color:#2563eb;}
.air-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;}
.air-provider-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;transition:box-shadow .15s;}
.air-provider-card:hover{box-shadow:0 4px 16px rgba(37,99,235,.1);}
.air-provider-card.default{border-color:#2563eb;box-shadow:0 0 0 2px rgba(37,99,235,.15);}
.air-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.air-badge.ok{background:#f0fdf4;color:#16a34a;}
.air-badge.off{background:#f1f5f9;color:#64748b;}
.air-badge.default{background:#eff6ff;color:#2563eb;}
.provider-icon{width:48px;height:48px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:12px;}
.btn-primary{background:#2563eb;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:background .15s;}
.btn-primary:hover{background:#1d4ed8;}
.btn-outline{background:#fff;color:#374151;border:1px solid #e2e8f0;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;transition:all .15s;}
.btn-outline:hover{border-color:#2563eb;color:#2563eb;}
.btn-danger{background:#fff;color:#dc2626;border:1px solid #fecaca;padding:6px 12px;border-radius:6px;font-size:12px;cursor:pointer;}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:16px;padding:32px;width:100%;max-width:640px;max-height:90vh;overflow-y:auto;}
.form-group{margin-bottom:16px;}
.form-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;}
.form-control{width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;color:#1e293b;box-sizing:border-box;transition:border-color .15s;}
.form-control:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1);}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.provider-tipos{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:16px;}
.tipo-btn{padding:10px;border:2px solid #e2e8f0;border-radius:8px;text-align:center;cursor:pointer;transition:all .15s;font-size:12px;}
.tipo-btn:hover,.tipo-btn.selected{border-color:#2563eb;background:#eff6ff;color:#2563eb;}
.tipo-btn .icon{font-size:20px;display:block;margin-bottom:4px;}
</style>

<!-- Navegação -->
<div class="air-nav">
    <a href="/ai-router" class="air-nav-btn">📊 Dashboard</a>
    <a href="/ai-router/providers" class="air-nav-btn active">🔌 Providers</a>
    <a href="/ai-router/modelos" class="air-nav-btn">🤖 Modelos</a>
    <a href="/ai-router/prompt-base" class="air-nav-btn">📋 Prompt Base</a>
    <a href="/ai-router/prompt-templates" class="air-nav-btn">📝 Templates</a>
    <a href="/ai-router/rotas" class="air-nav-btn">🔀 Rotas</a>
    <a href="/ai-router/historico" class="air-nav-btn">📜 Histórico</a>
    <a href="/ai-router/tokens" class="air-nav-btn">🪙 Tokens</a>
    <a href="/ai-router/custos" class="air-nav-btn">💰 Custos</a>
    <a href="/ai-router/logs" class="air-nav-btn">📋 Logs</a>
    <a href="/ai-router/testes" class="air-nav-btn">🧪 Testes</a>
    <a href="/ai-router/configuracoes" class="air-nav-btn">⚙️ Config</a>
</div>

<!-- Header -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <h2 style="font-size:18px; font-weight:700; color:#1e293b; margin:0;">Providers de IA</h2>
        <p style="font-size:13px; color:#64748b; margin:4px 0 0;">Configure suas integrações com OpenAI, Claude, Gemini e outros</p>
    </div>
    <button class="btn-primary" onclick="abrirModal()">+ Adicionar Provider</button>
</div>

<!-- Grid de Providers -->
<?php if (empty($data['providers'])): ?>
<div class="air-card" style="text-align:center; padding:48px;">
    <div style="font-size:48px; margin-bottom:16px;">🔌</div>
    <h3 style="font-size:18px; font-weight:600; color:#1e293b; margin:0 0 8px;">Nenhum provider configurado</h3>
    <p style="color:#64748b; font-size:14px; margin:0 0 24px;">Adicione seu primeiro provider de IA para começar a usar o VOXEL Copilot</p>
    <button class="btn-primary" onclick="abrirModal()">+ Adicionar primeiro provider</button>
</div>
<?php else: ?>
<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:20px;">
    <?php
    $icons = ['openai'=>'🟢','anthropic'=>'🟣','google_gemini'=>'🔵','azure_openai'=>'🔷','ollama'=>'🟡','deepseek'=>'🔴','mistral'=>'🟠','openrouter'=>'⚫','lm_studio'=>'🟤','vertex_ai'=>'🔵','amazon_bedrock'=>'🟠','custom'=>'⚙️'];
    $nomes = ['openai'=>'OpenAI','anthropic'=>'Anthropic','google_gemini'=>'Google Gemini','azure_openai'=>'Azure OpenAI','ollama'=>'Ollama','deepseek'=>'DeepSeek','mistral'=>'Mistral AI','openrouter'=>'OpenRouter','lm_studio'=>'LM Studio','vertex_ai'=>'Vertex AI','amazon_bedrock'=>'Amazon Bedrock','custom'=>'Custom'];
    foreach ($data['providers'] as $p): ?>
    <div class="air-provider-card <?= $p['is_default'] ? 'default' : '' ?>">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div class="provider-icon" style="background:#f8fafc;"><?= $icons[$p['provider_tipo']] ?? '🤖' ?></div>
            <div style="display:flex; gap:6px; flex-wrap:wrap; justify-content:flex-end;">
                <?php if ($p['is_default']): ?><span class="air-badge default">⭐ Padrão</span><?php endif; ?>
                <span class="air-badge <?= $p['is_active'] ? 'ok' : 'off' ?>"><?= $p['is_active'] ? '● Ativo' : '○ Inativo' ?></span>
            </div>
        </div>
        <div style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:4px;"><?= htmlspecialchars($p['nome']) ?></div>
        <div style="font-size:12px; color:#64748b; margin-bottom:12px;"><?= $nomes[$p['provider_tipo']] ?? $p['provider_tipo'] ?> · <?= htmlspecialchars($p['modelo_padrao']) ?></div>
        <?php if ($p['descricao']): ?>
        <div style="font-size:12px; color:#374151; margin-bottom:12px;"><?= htmlspecialchars($p['descricao']) ?></div>
        <?php endif; ?>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:16px;">
            <div style="background:#f8fafc; padding:8px; border-radius:6px; text-align:center;">
                <div style="font-size:11px; color:#64748b;">Temperatura</div>
                <div style="font-size:14px; font-weight:600; color:#1e293b;"><?= $p['temperatura'] ?></div>
            </div>
            <div style="background:#f8fafc; padding:8px; border-radius:6px; text-align:center;">
                <div style="font-size:11px; color:#64748b;">Max Tokens</div>
                <div style="font-size:14px; font-weight:600; color:#1e293b;"><?= number_format($p['max_tokens']) ?></div>
            </div>
        </div>
        <?php if ($p['ultima_utilizacao']): ?>
        <div style="font-size:11px; color:#94a3b8; margin-bottom:12px;">Último uso: <?= date('d/m/Y H:i', strtotime($p['ultima_utilizacao'])) ?> · <?= $p['latencia_ms'] ?>ms</div>
        <?php endif; ?>
        <div style="display:flex; gap:8px;">
            <button class="btn-outline" style="flex:1;" onclick="testarProvider(<?= $p['id'] ?>)">🧪 Testar</button>
            <button class="btn-outline" onclick="editarProvider(<?= htmlspecialchars(json_encode($p)) ?>)">✏️</button>
            <button class="btn-danger" onclick="excluirProvider(<?= $p['id'] ?>)">🗑</button>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Modal Adicionar/Editar Provider -->
<div class="modal-overlay" id="modalProvider">
    <div class="modal">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <h3 style="font-size:18px; font-weight:700; color:#1e293b; margin:0;" id="modalTitle">Adicionar Provider</h3>
            <button onclick="fecharModal()" style="background:none; border:none; font-size:20px; cursor:pointer; color:#64748b;">✕</button>
        </div>
        <form id="formProvider" onsubmit="salvarProvider(event)">
            <input type="hidden" id="providerId" name="id" value="0">
            <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">

            <!-- Tipo de Provider -->
            <div class="form-group">
                <label class="form-label">Tipo de Provider</label>
                <div class="provider-tipos" id="tipoGrid">
                    <?php
                    $tipos = [
                        ['openai','🟢','OpenAI'],['anthropic','🟣','Anthropic'],['google_gemini','🔵','Gemini'],
                        ['azure_openai','🔷','Azure OpenAI'],['deepseek','🔴','DeepSeek'],['mistral','🟠','Mistral'],
                        ['openrouter','⚫','OpenRouter'],['ollama','🟡','Ollama'],['lm_studio','🟤','LM Studio'],
                        ['vertex_ai','🔵','Vertex AI'],['amazon_bedrock','🟠','Bedrock'],['custom','⚙️','Custom'],
                    ];
                    foreach ($tipos as $t): ?>
                    <div class="tipo-btn" data-tipo="<?= $t[0] ?>" onclick="selecionarTipo('<?= $t[0] ?>')">
                        <span class="icon"><?= $t[1] ?></span><?= $t[2] ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="providerTipo" name="provider_tipo" value="openai">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nome *</label>
                    <input type="text" class="form-control" name="nome" id="pNome" placeholder="Ex: OpenAI Principal" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Modelo Padrão *</label>
                    <input type="text" class="form-control" name="modelo_padrao" id="pModelo" placeholder="Ex: gpt-4o" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">API Key</label>
                <input type="password" class="form-control" name="api_key" id="pApiKey" placeholder="sk-... (deixe em branco para manter a atual)">
            </div>

            <div class="form-group" id="endpointGroup">
                <label class="form-label">Endpoint (opcional)</label>
                <input type="text" class="form-control" name="endpoint" id="pEndpoint" placeholder="https://api.openai.com/v1">
            </div>

            <div class="form-group">
                <label class="form-label">Descrição</label>
                <input type="text" class="form-control" name="descricao" id="pDescricao" placeholder="Descrição opcional">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Temperatura (0.0 - 2.0)</label>
                    <input type="number" class="form-control" name="temperatura" id="pTemp" value="0.1" min="0" max="2" step="0.1">
                </div>
                <div class="form-group">
                    <label class="form-label">Max Tokens</label>
                    <input type="number" class="form-control" name="max_tokens" id="pMaxTokens" value="4000" min="100" max="200000">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Timeout (segundos)</label>
                    <input type="number" class="form-control" name="timeout_seg" id="pTimeout" value="120" min="10" max="600">
                </div>
                <div class="form-group">
                    <label class="form-label">Retry (tentativas)</label>
                    <input type="number" class="form-control" name="retry" id="pRetry" value="3" min="1" max="10">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Prompt Base (opcional)</label>
                <select class="form-control" name="prompt_base_id" id="pPromptBase">
                    <option value="">— Sem prompt base —</option>
                    <?php foreach ($data['prompts_base'] as $pb): ?>
                    <option value="<?= $pb['id'] ?>"><?= htmlspecialchars($pb['nome']) ?> (<?= $pb['especialidade'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:flex; align-items:center; gap:8px; margin-bottom:20px;">
                <input type="checkbox" name="is_default" id="pDefault" value="1" style="width:16px;height:16px;">
                <label for="pDefault" style="font-size:14px; color:#374151; cursor:pointer;">Definir como provider padrão</label>
            </div>

            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" class="btn-outline" onclick="fecharModal()">Cancelar</button>
                <button type="submit" class="btn-primary" id="btnSalvar">💾 Salvar Provider</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Teste -->
<div class="modal-overlay" id="modalTeste">
    <div class="modal" style="max-width:480px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin:0;">🧪 Testando Provider</h3>
            <button onclick="document.getElementById('modalTeste').classList.remove('open')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#64748b;">✕</button>
        </div>
        <div id="testeResultado" style="text-align:center; padding:20px; color:#64748b;">
            <div style="font-size:32px; margin-bottom:12px;">⏳</div>
            <div>Testando conexão...</div>
        </div>
    </div>
</div>

<script>
const defaultEndpoints = {
    openai:'https://api.openai.com/v1',anthropic:'https://api.anthropic.com',
    google_gemini:'',azure_openai:'https://YOUR_RESOURCE.openai.azure.com',
    ollama:'http://localhost:11434',lm_studio:'http://localhost:1234/v1',
    deepseek:'https://api.deepseek.com/v1',mistral:'https://api.mistral.ai/v1',
    openrouter:'https://openrouter.ai/api/v1',vertex_ai:'',amazon_bedrock:'',custom:''
};
const defaultModelos = {
    openai:'gpt-4o',anthropic:'claude-3-5-sonnet-20241022',google_gemini:'gemini-1.5-pro',
    azure_openai:'gpt-4o',ollama:'llama3',lm_studio:'local-model',deepseek:'deepseek-chat',
    mistral:'mistral-large-latest',openrouter:'openai/gpt-4o',vertex_ai:'gemini-1.5-pro',
    amazon_bedrock:'anthropic.claude-3-5-sonnet-20241022-v2:0',custom:''
};

function selecionarTipo(tipo) {
    document.querySelectorAll('.tipo-btn').forEach(b => b.classList.remove('selected'));
    document.querySelector('[data-tipo="'+tipo+'"]').classList.add('selected');
    document.getElementById('providerTipo').value = tipo;
    if (!document.getElementById('pEndpoint').value || document.getElementById('pEndpoint').value === document.getElementById('pEndpoint').dataset.prev) {
        document.getElementById('pEndpoint').value = defaultEndpoints[tipo] || '';
    }
    document.getElementById('pEndpoint').dataset.prev = defaultEndpoints[tipo] || '';
    if (!document.getElementById('pModelo').value) {
        document.getElementById('pModelo').value = defaultModelos[tipo] || '';
    }
}

function abrirModal() {
    document.getElementById('modalTitle').textContent = 'Adicionar Provider';
    document.getElementById('formProvider').reset();
    document.getElementById('providerId').value = 0;
    selecionarTipo('openai');
    document.getElementById('modalProvider').classList.add('open');
}

function fecharModal() {
    document.getElementById('modalProvider').classList.remove('open');
}

function editarProvider(p) {
    document.getElementById('modalTitle').textContent = 'Editar Provider';
    document.getElementById('providerId').value = p.id;
    document.getElementById('pNome').value = p.nome;
    document.getElementById('pModelo').value = p.modelo_padrao;
    document.getElementById('pApiKey').value = '';
    document.getElementById('pEndpoint').value = p.endpoint || '';
    document.getElementById('pDescricao').value = p.descricao || '';
    document.getElementById('pTemp').value = p.temperatura;
    document.getElementById('pMaxTokens').value = p.max_tokens;
    document.getElementById('pTimeout').value = p.timeout_seg;
    document.getElementById('pRetry').value = p.retry;
    document.getElementById('pDefault').checked = p.is_default == 1;
    document.getElementById('pPromptBase').value = p.prompt_base_id || '';
    selecionarTipo(p.provider_tipo);
    document.getElementById('modalProvider').classList.add('open');
}

function salvarProvider(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSalvar');
    btn.textContent = '⏳ Salvando...';
    btn.disabled = true;
    const fd = new FormData(document.getElementById('formProvider'));
    fetch('/ai-router/providers/salvar', {method:'POST', body:fd})
        .then(r => r.json())
        .then(d => {
            if (d.ok) { fecharModal(); location.reload(); }
            else { alert('Erro: ' + (d.erro || 'Falha ao salvar')); btn.textContent = '💾 Salvar Provider'; btn.disabled = false; }
        })
        .catch(() => { alert('Erro de comunicação'); btn.textContent = '💾 Salvar Provider'; btn.disabled = false; });
}

function excluirProvider(id) {
    if (!confirm('Desativar este provider?')) return;
    fetch('/ai-router/providers/excluir', {method:'POST', body:new URLSearchParams({id, csrf_token:'<?= $data['csrf_token'] ?>'})})
        .then(r => r.json()).then(d => { if (d.ok) location.reload(); });
}

function testarProvider(id) {
    document.getElementById('testeResultado').innerHTML = '<div style="font-size:32px;margin-bottom:12px;">⏳</div><div>Testando conexão...</div>';
    document.getElementById('modalTeste').classList.add('open');
    fetch('/ai-router/providers/testar', {method:'POST', body:new URLSearchParams({provider_id:id, csrf_token:'<?= $data['csrf_token'] ?>'})})
        .then(r => r.json())
        .then(d => {
            if (d.ok) {
                document.getElementById('testeResultado').innerHTML = `
                    <div style="font-size:32px;margin-bottom:12px;">✅</div>
                    <div style="font-size:16px;font-weight:700;color:#16a34a;margin-bottom:8px;">Conexão bem-sucedida!</div>
                    <div style="font-size:13px;color:#374151;margin-bottom:4px;">Modelo: <strong>${d.modelo}</strong></div>
                    <div style="font-size:13px;color:#374151;margin-bottom:12px;">Latência: <strong>${d.latencia}ms</strong></div>
                    <div style="background:#f8fafc;padding:12px;border-radius:8px;font-size:12px;color:#374151;text-align:left;">${d.resposta}</div>`;
            } else {
                document.getElementById('testeResultado').innerHTML = `
                    <div style="font-size:32px;margin-bottom:12px;">❌</div>
                    <div style="font-size:16px;font-weight:700;color:#dc2626;margin-bottom:8px;">Falha na conexão</div>
                    <div style="background:#fef2f2;padding:12px;border-radius:8px;font-size:12px;color:#dc2626;text-align:left;">${d.erro}</div>`;
            }
        });
}

// Seleciona openai por padrão
selecionarTipo('openai');
</script>

