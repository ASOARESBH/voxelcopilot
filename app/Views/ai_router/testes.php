<style>
.air-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;}
.air-nav-btn{padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;border:1px solid #e2e8f0;background:#fff;color:#374151;text-decoration:none;transition:all .15s;}
.air-nav-btn:hover,.air-nav-btn.active{background:#2563eb;color:#fff;border-color:#2563eb;}
.air-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;}
.btn-primary{background:#2563eb;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;}
.btn-primary:hover{background:#1d4ed8;}
.btn-outline{background:#fff;color:#374151;border:1px solid #e2e8f0;padding:8px 16px;border-radius:8px;font-size:13px;cursor:pointer;}
.form-control{width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;color:#1e293b;box-sizing:border-box;}
.form-control:focus{outline:none;border-color:#2563eb;}
.form-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;}
.form-group{margin-bottom:16px;}
.resultado-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;font-size:13px;line-height:1.6;min-height:120px;white-space:pre-wrap;font-family:monospace;}
.meta-box{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;}
.meta-item{background:#f8fafc;border-radius:8px;padding:12px;text-align:center;}
.meta-val{font-size:18px;font-weight:700;color:#1e293b;}
.meta-lbl{font-size:11px;color:#64748b;margin-top:2px;}
</style>
<div class="air-nav">
    <a href="/ai-router" class="air-nav-btn">📊 Dashboard</a>
    <a href="/ai-router/providers" class="air-nav-btn">🔌 Providers</a>
    <a href="/ai-router/modelos" class="air-nav-btn">🤖 Modelos</a>
    <a href="/ai-router/prompt-base" class="air-nav-btn">📋 Prompt Base</a>
    <a href="/ai-router/prompt-templates" class="air-nav-btn">📝 Templates</a>
    <a href="/ai-router/rotas" class="air-nav-btn">🔀 Rotas</a>
    <a href="/ai-router/historico" class="air-nav-btn">📜 Histórico</a>
    <a href="/ai-router/tokens" class="air-nav-btn">🪙 Tokens</a>
    <a href="/ai-router/custos" class="air-nav-btn">💰 Custos</a>
    <a href="/ai-router/logs" class="air-nav-btn">📋 Logs</a>
    <a href="/ai-router/testes" class="air-nav-btn active">🧪 Testes</a>
    <a href="/ai-router/configuracoes" class="air-nav-btn">⚙️ Config</a>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
    <!-- Painel de Teste -->
    <div class="air-card">
        <h2 style="font-size:18px;font-weight:700;color:#1e293b;margin:0 0 20px;">🧪 Playground AI Router</h2>
        <div class="form-group">
            <label class="form-label">Provider</label>
            <select class="form-control" id="testProvider">
                <?php foreach ($data['providers'] as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?> — <?= htmlspecialchars($p['modelo_padrao']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Tipo de Solicitação</label>
            <select class="form-control" id="testTipo">
                <option value="laudo_gerar">Gerar Laudo</option>
                <option value="laudo_sugerir">Sugerir Achados</option>
                <option value="laudo_revisar">Revisar Laudo</option>
                <option value="laudo_cid">Sugerir CID</option>
                <option value="chat">Chat Livre</option>
                <option value="resumo">Resumo Clínico</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Prompt Base (opcional)</label>
            <select class="form-control" id="testPromptBase">
                <option value="">— Sem prompt base —</option>
                <?php foreach ($data['prompts_base'] as $pb): ?>
                <option value="<?= $pb['id'] ?>"><?= htmlspecialchars($pb['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Mensagem / Contexto *</label>
            <textarea class="form-control" id="testMensagem" rows="6" placeholder="Digite o contexto clínico ou a mensagem para testar...&#10;&#10;Ex: TC de tórax com contraste, paciente 65 anos, masculino. Achados: nódulo pulmonar de 8mm no lobo superior direito, espiculado."></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
            <div class="form-group" style="margin:0;">
                <label class="form-label">Temperatura</label>
                <input type="number" class="form-control" id="testTemp" value="0.1" min="0" max="2" step="0.1">
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Max Tokens</label>
                <input type="number" class="form-control" id="testMaxTokens" value="2000" min="100" max="8000">
            </div>
        </div>
        <div style="display:flex;gap:12px;">
            <button class="btn-primary" style="flex:1;" onclick="executarTeste()">▶ Executar Teste</button>
            <button class="btn-outline" onclick="limparTeste()">🗑 Limpar</button>
        </div>
    </div>

    <!-- Resultado -->
    <div class="air-card">
        <h2 style="font-size:18px;font-weight:700;color:#1e293b;margin:0 0 20px;">📊 Resultado</h2>
        <div id="metaBox" class="meta-box" style="display:none;">
            <div class="meta-item"><div class="meta-val" id="metaTokens">—</div><div class="meta-lbl">Tokens</div></div>
            <div class="meta-item"><div class="meta-val" id="metaTempo">—</div><div class="meta-lbl">Tempo (ms)</div></div>
            <div class="meta-item"><div class="meta-val" id="metaCusto">—</div><div class="meta-lbl">Custo USD</div></div>
            <div class="meta-item"><div class="meta-val" id="metaStatus">—</div><div class="meta-lbl">Status</div></div>
        </div>
        <div id="resultadoBox" class="resultado-box" style="color:#94a3b8;">
            Aguardando execução do teste...
        </div>
        <div id="btnCopiar" style="display:none;margin-top:12px;">
            <button class="btn-outline" onclick="copiarResultado()">📋 Copiar resultado</button>
        </div>
    </div>
</div>

<script>
function executarTeste() {
    const provider = document.getElementById('testProvider').value;
    const tipo = document.getElementById('testTipo').value;
    const mensagem = document.getElementById('testMensagem').value.trim();
    const promptBase = document.getElementById('testPromptBase').value;
    const temp = document.getElementById('testTemp').value;
    const maxTokens = document.getElementById('testMaxTokens').value;
    if (!mensagem) { alert('Digite uma mensagem para testar'); return; }
    if (!provider) { alert('Selecione um provider'); return; }
    document.getElementById('resultadoBox').style.color = '#64748b';
    document.getElementById('resultadoBox').textContent = '⏳ Processando... aguarde.';
    document.getElementById('metaBox').style.display = 'none';
    document.getElementById('btnCopiar').style.display = 'none';
    const body = new URLSearchParams({provider_id:provider, tipo, mensagem, prompt_base_id:promptBase, temperatura:temp, max_tokens:maxTokens, csrf_token:'<?= $data['csrf_token'] ?>'});
    fetch('/api/ai/router', {method:'POST', body})
        .then(r => r.json())
        .then(d => {
            document.getElementById('metaBox').style.display = 'grid';
            document.getElementById('metaTokens').textContent = d.tokens_total ? d.tokens_total.toLocaleString() : '—';
            document.getElementById('metaTempo').textContent = d.tempo_ms || '—';
            document.getElementById('metaCusto').textContent = d.custo_usd ? '$'+parseFloat(d.custo_usd).toFixed(5) : '—';
            document.getElementById('metaStatus').textContent = d.ok ? '✓ OK' : '✗ Erro';
            document.getElementById('metaStatus').style.color = d.ok ? '#16a34a' : '#dc2626';
            if (d.ok) {
                document.getElementById('resultadoBox').style.color = '#1e293b';
                document.getElementById('resultadoBox').textContent = d.resposta;
                document.getElementById('btnCopiar').style.display = 'block';
            } else {
                document.getElementById('resultadoBox').style.color = '#dc2626';
                document.getElementById('resultadoBox').textContent = 'Erro: ' + (d.erro || 'Falha desconhecida');
            }
        })
        .catch(err => {
            document.getElementById('resultadoBox').style.color = '#dc2626';
            document.getElementById('resultadoBox').textContent = 'Erro de comunicação: ' + err.message;
        });
}
function limparTeste() {
    document.getElementById('testMensagem').value = '';
    document.getElementById('resultadoBox').style.color = '#94a3b8';
    document.getElementById('resultadoBox').textContent = 'Aguardando execução do teste...';
    document.getElementById('metaBox').style.display = 'none';
    document.getElementById('btnCopiar').style.display = 'none';
}
function copiarResultado() {
    navigator.clipboard.writeText(document.getElementById('resultadoBox').textContent).then(() => alert('Copiado!'));
}
</script>
