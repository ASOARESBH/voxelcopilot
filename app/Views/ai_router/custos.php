<style>
.air-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;}
.air-nav-btn{padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;border:1px solid #e2e8f0;background:#fff;color:#374151;text-decoration:none;transition:all .15s;}
.air-nav-btn:hover,.air-nav-btn.active{background:#2563eb;color:#fff;border-color:#2563eb;}
.air-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;}
.stat-box{background:#f8fafc;border-radius:10px;padding:16px;text-align:center;}
.stat-val{font-size:28px;font-weight:700;color:#1e293b;}
.stat-lbl{font-size:12px;color:#64748b;margin-top:4px;}
.air-table{width:100%;border-collapse:collapse;font-size:13px;}
.air-table th{background:#f8fafc;color:#64748b;font-weight:600;text-transform:uppercase;font-size:11px;letter-spacing:.5px;padding:10px 12px;text-align:left;border-bottom:1px solid #e2e8f0;}
.air-table td{padding:10px 12px;border-bottom:1px solid #f1f5f9;color:#374151;}
.air-table tr:last-child td{border-bottom:none;}
.progress-bar{height:8px;background:#f1f5f9;border-radius:4px;overflow:hidden;}
.progress-fill{height:100%;background:linear-gradient(90deg,#2563eb,#0891b2);border-radius:4px;}
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
    <a href="/ai-router/custos" class="air-nav-btn active">💰 Custos</a>
    <a href="/ai-router/logs" class="air-nav-btn">📋 Logs</a>
    <a href="/ai-router/testes" class="air-nav-btn">🧪 Testes</a>
    <a href="/ai-router/configuracoes" class="air-nav-btn">⚙️ Config</a>
</div>
<h2 style="font-size:18px;font-weight:700;color:#1e293b;margin:0 0 20px;">Análise de Custos</h2>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px;">
    <div class="stat-box"><div class="stat-val">$<?= number_format($data['custo_hoje'], 4) ?></div><div class="stat-lbl">Custo Hoje (USD)</div></div>
    <div class="stat-box"><div class="stat-val">$<?= number_format($data['custo_semana'], 4) ?></div><div class="stat-lbl">Esta Semana</div></div>
    <div class="stat-box"><div class="stat-val">$<?= number_format($data['custo_mes'], 4) ?></div><div class="stat-lbl">Este Mês</div></div>
    <div class="stat-box"><div class="stat-val">R$<?= number_format($data['custo_mes'] * 5.5, 2) ?></div><div class="stat-lbl">Estimativa BRL</div></div>
    <div class="stat-box">
        <div class="stat-val" style="color:<?= $data['custo_mes'] > ($data['limite_mensal'] * 0.8) ? '#dc2626' : '#16a34a' ?>">
            <?= $data['limite_mensal'] > 0 ? round($data['custo_mes'] / $data['limite_mensal'] * 100) : 0 ?>%
        </div>
        <div class="stat-lbl">Do limite mensal</div>
    </div>
</div>
<?php if ($data['limite_mensal'] > 0): ?>
<div class="air-card" style="margin-bottom:24px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
        <span style="font-size:14px;font-weight:600;color:#374151;">Limite Mensal: $<?= number_format($data['limite_mensal'], 2) ?></span>
        <span style="font-size:13px;color:#64748b;">Usado: $<?= number_format($data['custo_mes'], 4) ?></span>
    </div>
    <div class="progress-bar">
        <div class="progress-fill" style="width:<?= min(100, $data['limite_mensal'] > 0 ? round($data['custo_mes']/$data['limite_mensal']*100) : 0) ?>%;background:<?= $data['custo_mes'] > $data['limite_mensal'] * 0.8 ? '#dc2626' : 'linear-gradient(90deg,#2563eb,#0891b2)' ?>;"></div>
    </div>
</div>
<?php endif; ?>
<div class="air-card">
    <div style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:16px;">💰 Custo por Provider</div>
    <?php if (empty($data['por_provider'])): ?>
    <div style="text-align:center;padding:32px;color:#94a3b8;">Nenhum dado de custo disponível ainda</div>
    <?php else: ?>
    <table class="air-table">
        <thead><tr><th>Provider</th><th>Modelo</th><th>Custo Hoje</th><th>Custo Mês</th><th>Custo Total</th><th>Chamadas</th><th>Custo/Chamada</th></tr></thead>
        <tbody>
            <?php foreach ($data['por_provider'] as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['provider_nome']) ?></td>
                <td style="font-family:monospace;font-size:11px;"><?= htmlspecialchars($r['modelo']) ?></td>
                <td style="font-family:monospace;">$<?= number_format($r['custo_hoje'], 5) ?></td>
                <td style="font-family:monospace;">$<?= number_format($r['custo_mes'], 5) ?></td>
                <td style="font-family:monospace;font-weight:600;">$<?= number_format($r['custo_total'], 5) ?></td>
                <td style="text-align:right;"><?= number_format($r['chamadas']) ?></td>
                <td style="font-family:monospace;">$<?= $r['chamadas'] > 0 ? number_format($r['custo_total']/$r['chamadas'], 6) : '0.000000' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
