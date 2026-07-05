<style>
.air-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;}
.air-nav-btn{padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;border:1px solid #e2e8f0;background:#fff;color:#374151;text-decoration:none;transition:all .15s;}
.air-nav-btn:hover,.air-nav-btn.active{background:#2563eb;color:#fff;border-color:#2563eb;}
.air-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;}
.air-table{width:100%;border-collapse:collapse;font-size:13px;}
.air-table th{background:#f8fafc;color:#64748b;font-weight:600;text-transform:uppercase;font-size:11px;letter-spacing:.5px;padding:10px 12px;text-align:left;border-bottom:1px solid #e2e8f0;}
.air-table td{padding:10px 12px;border-bottom:1px solid #f1f5f9;color:#374151;vertical-align:middle;}
.air-table tr:last-child td{border-bottom:none;}
.air-table tr:hover td{background:#f8fafc;}
.air-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.air-badge.ok{background:#f0fdf4;color:#16a34a;}
.air-badge.err{background:#fef2f2;color:#dc2626;}
.form-control{padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#374151;}
.form-control:focus{outline:none;border-color:#2563eb;}
</style>
<div class="air-nav">
    <a href="/ai-router" class="air-nav-btn">📊 Dashboard</a>
    <a href="/ai-router/providers" class="air-nav-btn">🔌 Providers</a>
    <a href="/ai-router/modelos" class="air-nav-btn">🤖 Modelos</a>
    <a href="/ai-router/prompt-base" class="air-nav-btn">📋 Prompt Base</a>
    <a href="/ai-router/prompt-templates" class="air-nav-btn">📝 Templates</a>
    <a href="/ai-router/rotas" class="air-nav-btn">🔀 Rotas</a>
    <a href="/ai-router/historico" class="air-nav-btn active">📜 Histórico</a>
    <a href="/ai-router/tokens" class="air-nav-btn">🪙 Tokens</a>
    <a href="/ai-router/custos" class="air-nav-btn">💰 Custos</a>
    <a href="/ai-router/logs" class="air-nav-btn">📋 Logs</a>
    <a href="/ai-router/testes" class="air-nav-btn">🧪 Testes</a>
    <a href="/ai-router/configuracoes" class="air-nav-btn">⚙️ Config</a>
</div>
<div class="air-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="font-size:18px;font-weight:700;color:#1e293b;margin:0;">Histórico de Chamadas</h2>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0;">Todas as chamadas realizadas ao AI Router</p>
        </div>
        <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;">
            <select name="provider_id" class="form-control" onchange="this.form.submit()">
                <option value="">Todos os providers</option>
                <?php foreach ($data['providers'] as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($data['filtros']['provider_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nome']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">Todos</option>
                <option value="ok" <?= ($data['filtros']['status'] ?? '') === 'ok' ? 'selected' : '' ?>>✓ Sucesso</option>
                <option value="erro" <?= ($data['filtros']['status'] ?? '') === 'erro' ? 'selected' : '' ?>>✗ Erro</option>
            </select>
            <input type="date" name="data" class="form-control" value="<?= $data['filtros']['data'] ?? '' ?>" onchange="this.form.submit()">
        </form>
    </div>
    <?php if (empty($data['historico'])): ?>
    <div style="text-align:center;padding:48px;color:#94a3b8;">
        <div style="font-size:48px;margin-bottom:16px;">📭</div>
        <div>Nenhuma chamada registrada</div>
    </div>
    <?php else: ?>
    <table class="air-table">
        <thead>
            <tr><th>Data/Hora</th><th>Provider</th><th>Modelo</th><th>Tipo</th><th>Tokens In</th><th>Tokens Out</th><th>Custo</th><th>Tempo</th><th>Status</th><th>Ações</th></tr>
        </thead>
        <tbody>
            <?php foreach ($data['historico'] as $h): ?>
            <tr>
                <td style="font-size:12px;color:#64748b;white-space:nowrap;"><?= date('d/m/Y H:i:s', strtotime($h['created_at'])) ?></td>
                <td><?= htmlspecialchars($h['provider_nome']) ?></td>
                <td style="font-family:monospace;font-size:11px;"><?= htmlspecialchars($h['modelo']) ?></td>
                <td><?= htmlspecialchars($h['tipo_solicitacao']) ?></td>
                <td style="text-align:right;"><?= number_format($h['tokens_input']) ?></td>
                <td style="text-align:right;"><?= number_format($h['tokens_output']) ?></td>
                <td style="font-family:monospace;">$<?= number_format($h['custo_usd'], 5) ?></td>
                <td><?= $h['tempo_ms'] ?>ms</td>
                <td><span class="air-badge <?= $h['status'] === 'ok' ? 'ok' : 'err' ?>"><?= $h['status'] === 'ok' ? '✓ OK' : '✗ Erro' ?></span></td>
                <td>
                    <?php if ($h['status'] !== 'ok'): ?>
                    <button onclick="verErro('<?= htmlspecialchars(addslashes($h['erro_mensagem'])) ?>')" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:12px;">Ver erro</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <!-- Paginação -->
    <?php if ($data['total_paginas'] > 1): ?>
    <div style="display:flex;justify-content:center;gap:8px;margin-top:20px;">
        <?php for ($i = 1; $i <= $data['total_paginas']; $i++): ?>
        <a href="?pagina=<?= $i ?>" style="padding:6px 12px;border-radius:6px;font-size:13px;border:1px solid <?= $i == $data['pagina_atual'] ? '#2563eb' : '#e2e8f0' ?>;background:<?= $i == $data['pagina_atual'] ? '#2563eb' : '#fff' ?>;color:<?= $i == $data['pagina_atual'] ? '#fff' : '#374151' ?>;text-decoration:none;"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
<script>
function verErro(msg){alert('Erro: '+msg);}
</script>
