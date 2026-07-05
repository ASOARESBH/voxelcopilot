<?php $this->layout('layout/copilot_header', $data); ?>
<style>
.air-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;}
.air-nav-btn{padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;border:1px solid #e2e8f0;background:#fff;color:#374151;text-decoration:none;transition:all .15s;}
.air-nav-btn:hover,.air-nav-btn.active{background:#2563eb;color:#fff;border-color:#2563eb;}
.air-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;margin-bottom:24px;}
.btn-primary{background:#2563eb;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;}
.btn-primary:hover{background:#1d4ed8;}
.form-control{width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;color:#1e293b;box-sizing:border-box;}
.form-control:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1);}
.form-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;}
.form-group{margin-bottom:16px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.section-title{font-size:15px;font-weight:700;color:#1e293b;margin:0 0 16px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;}
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
    <a href="/ai-router/testes" class="air-nav-btn">🧪 Testes</a>
    <a href="/ai-router/configuracoes" class="air-nav-btn active">⚙️ Config</a>
</div>
<?php if (!empty($data['sucesso'])): ?>
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#16a34a;font-size:14px;">✓ Configurações salvas com sucesso!</div>
<?php endif; ?>
<form method="POST" action="/ai-router/configuracoes/salvar">
    <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
    <!-- Comportamento Geral -->
    <div class="air-card">
        <div class="section-title">⚙️ Comportamento Geral</div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Provider Padrão</label>
                <select class="form-control" name="provider_padrao_id">
                    <option value="">— Selecionar —</option>
                    <?php foreach ($data['providers'] as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($data['config']['provider_padrao_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Temperatura Padrão</label>
                <input type="number" class="form-control" name="temperatura_padrao" value="<?= $data['config']['temperatura_padrao'] ?? '0.1' ?>" min="0" max="2" step="0.1">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Max Tokens Padrão</label>
                <input type="number" class="form-control" name="max_tokens_padrao" value="<?= $data['config']['max_tokens_padrao'] ?? '4000' ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Timeout Padrão (segundos)</label>
                <input type="number" class="form-control" name="timeout_padrao" value="<?= $data['config']['timeout_padrao'] ?? '120' ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Retry em caso de falha</label>
                <input type="number" class="form-control" name="retry_padrao" value="<?= $data['config']['retry_padrao'] ?? '3' ?>" min="1" max="10">
            </div>
            <div class="form-group">
                <label class="form-label">Idioma de Resposta</label>
                <select class="form-control" name="idioma_resposta">
                    <option value="pt-BR" <?= ($data['config']['idioma_resposta'] ?? 'pt-BR') === 'pt-BR' ? 'selected' : '' ?>>Português (Brasil)</option>
                    <option value="en" <?= ($data['config']['idioma_resposta'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
                    <option value="es" <?= ($data['config']['idioma_resposta'] ?? '') === 'es' ? 'selected' : '' ?>>Español</option>
                </select>
            </div>
        </div>
    </div>
    <!-- Limites e Alertas -->
    <div class="air-card">
        <div class="section-title">💰 Limites e Alertas de Custo</div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Limite Mensal (USD)</label>
                <input type="number" class="form-control" name="limite_mensal_usd" value="<?= $data['config']['limite_mensal_usd'] ?? '0' ?>" min="0" step="0.01" placeholder="0 = sem limite">
            </div>
            <div class="form-group">
                <label class="form-label">Alerta em (% do limite)</label>
                <input type="number" class="form-control" name="alerta_percentual" value="<?= $data['config']['alerta_percentual'] ?? '80' ?>" min="10" max="100">
            </div>
        </div>
    </div>
    <!-- Logging -->
    <div class="air-card">
        <div class="section-title">📋 Logging e Auditoria</div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Nível de Log</label>
                <select class="form-control" name="log_nivel">
                    <option value="error" <?= ($data['config']['log_nivel'] ?? '') === 'error' ? 'selected' : '' ?>>Error apenas</option>
                    <option value="warn" <?= ($data['config']['log_nivel'] ?? '') === 'warn' ? 'selected' : '' ?>>Warning+</option>
                    <option value="info" <?= ($data['config']['log_nivel'] ?? 'info') === 'info' ? 'selected' : '' ?>>Info+</option>
                    <option value="debug" <?= ($data['config']['log_nivel'] ?? '') === 'debug' ? 'selected' : '' ?>>Debug (tudo)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Retenção de Logs (dias)</label>
                <input type="number" class="form-control" name="log_retencao_dias" value="<?= $data['config']['log_retencao_dias'] ?? '90' ?>" min="7" max="365">
            </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <label style="display:flex;align-items:center;gap:8px;font-size:14px;color:#374151;cursor:pointer;">
                <input type="checkbox" name="log_salvar_prompt" value="1" <?= !empty($data['config']['log_salvar_prompt']) ? 'checked' : '' ?> style="width:16px;height:16px;">
                Salvar prompt completo no histórico
            </label>
            <label style="display:flex;align-items:center;gap:8px;font-size:14px;color:#374151;cursor:pointer;">
                <input type="checkbox" name="log_salvar_resposta" value="1" <?= !empty($data['config']['log_salvar_resposta']) ? 'checked' : '' ?> style="width:16px;height:16px;">
                Salvar resposta completa no histórico
            </label>
        </div>
    </div>
    <div style="display:flex;justify-content:flex-end;">
        <button type="submit" class="btn-primary">💾 Salvar Configurações</button>
    </div>
</form>
<?php $this->layout('layout/copilot_footer', $data); ?>
