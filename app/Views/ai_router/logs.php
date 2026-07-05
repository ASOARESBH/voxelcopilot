<?php $this->layout('layout/copilot_header', $data); ?>
<style>
.air-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;}
.air-nav-btn{padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;border:1px solid #e2e8f0;background:#fff;color:#374151;text-decoration:none;transition:all .15s;}
.air-nav-btn:hover,.air-nav-btn.active{background:#2563eb;color:#fff;border-color:#2563eb;}
.air-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;}
.log-entry{padding:10px 14px;border-radius:8px;margin-bottom:8px;font-size:12px;font-family:monospace;border-left:3px solid;}
.log-entry.error{background:#fef2f2;border-color:#dc2626;color:#991b1b;}
.log-entry.warn{background:#fff7ed;border-color:#ea580c;color:#9a3412;}
.log-entry.info{background:#f0f9ff;border-color:#0891b2;color:#0c4a6e;}
.log-entry.debug{background:#f8fafc;border-color:#94a3b8;color:#475569;}
.form-control{padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#374151;}
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
    <a href="/ai-router/logs" class="air-nav-btn active">📋 Logs</a>
    <a href="/ai-router/testes" class="air-nav-btn">🧪 Testes</a>
    <a href="/ai-router/configuracoes" class="air-nav-btn">⚙️ Config</a>
</div>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <h2 style="font-size:18px;font-weight:700;color:#1e293b;margin:0;">Logs do AI Router</h2>
        <p style="font-size:13px;color:#64748b;margin:4px 0 0;">Registro detalhado de todas as operações</p>
    </div>
    <form method="GET" style="display:flex;gap:8px;">
        <select name="nivel" class="form-control" onchange="this.form.submit()">
            <option value="">Todos os níveis</option>
            <option value="error" <?= ($data['filtros']['nivel'] ?? '') === 'error' ? 'selected' : '' ?>>🔴 Error</option>
            <option value="warn" <?= ($data['filtros']['nivel'] ?? '') === 'warn' ? 'selected' : '' ?>>🟡 Warning</option>
            <option value="info" <?= ($data['filtros']['nivel'] ?? '') === 'info' ? 'selected' : '' ?>>🔵 Info</option>
            <option value="debug" <?= ($data['filtros']['nivel'] ?? '') === 'debug' ? 'selected' : '' ?>>⚪ Debug</option>
        </select>
    </form>
</div>
<div class="air-card">
    <?php if (empty($data['logs'])): ?>
    <div style="text-align:center;padding:48px;color:#94a3b8;">
        <div style="font-size:48px;margin-bottom:16px;">📋</div>
        <div>Nenhum log registrado</div>
    </div>
    <?php else: ?>
    <?php foreach ($data['logs'] as $log): ?>
    <div class="log-entry <?= $log['nivel'] ?>">
        <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
            <span style="font-weight:700;">[<?= strtoupper($log['nivel']) ?>] <?= htmlspecialchars($log['contexto']) ?></span>
            <span style="color:inherit;opacity:.7;"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></span>
        </div>
        <div><?= htmlspecialchars($log['mensagem']) ?></div>
        <?php if ($log['dados_extras']): ?>
        <details style="margin-top:6px;">
            <summary style="cursor:pointer;opacity:.7;">Ver dados extras</summary>
            <pre style="margin:6px 0 0;white-space:pre-wrap;font-size:11px;"><?= htmlspecialchars($log['dados_extras']) ?></pre>
        </details>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php $this->layout('layout/copilot_footer', $data); ?>
