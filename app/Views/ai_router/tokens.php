<?php $this->layout('layout/copilot_header', $data); ?>
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
</style>
<div class="air-nav">
    <a href="/ai-router" class="air-nav-btn">📊 Dashboard</a>
    <a href="/ai-router/providers" class="air-nav-btn">🔌 Providers</a>
    <a href="/ai-router/modelos" class="air-nav-btn">🤖 Modelos</a>
    <a href="/ai-router/prompt-base" class="air-nav-btn">📋 Prompt Base</a>
    <a href="/ai-router/prompt-templates" class="air-nav-btn">📝 Templates</a>
    <a href="/ai-router/rotas" class="air-nav-btn">🔀 Rotas</a>
    <a href="/ai-router/historico" class="air-nav-btn">📜 Histórico</a>
    <a href="/ai-router/tokens" class="air-nav-btn active">🪙 Tokens</a>
    <a href="/ai-router/custos" class="air-nav-btn">💰 Custos</a>
    <a href="/ai-router/logs" class="air-nav-btn">📋 Logs</a>
    <a href="/ai-router/testes" class="air-nav-btn">🧪 Testes</a>
    <a href="/ai-router/configuracoes" class="air-nav-btn">⚙️ Config</a>
</div>
<h2 style="font-size:18px;font-weight:700;color:#1e293b;margin:0 0 20px;">Consumo de Tokens</h2>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px;">
    <div class="stat-box"><div class="stat-val"><?= number_format($data['tokens_hoje']) ?></div><div class="stat-lbl">Tokens Hoje</div></div>
    <div class="stat-box"><div class="stat-val"><?= number_format($data['tokens_semana']) ?></div><div class="stat-lbl">Esta Semana</div></div>
    <div class="stat-box"><div class="stat-val"><?= number_format($data['tokens_mes']) ?></div><div class="stat-lbl">Este Mês</div></div>
    <div class="stat-box"><div class="stat-val"><?= number_format($data['tokens_total']) ?></div><div class="stat-lbl">Total Acumulado</div></div>
    <div class="stat-box"><div class="stat-val"><?= number_format($data['media_por_chamada']) ?></div><div class="stat-lbl">Média por Chamada</div></div>
</div>
<div class="air-card">
    <div style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:16px;">📊 Consumo por Provider</div>
    <?php if (empty($data['por_provider'])): ?>
    <div style="text-align:center;padding:32px;color:#94a3b8;">Nenhum dado disponível ainda</div>
    <?php else: ?>
    <table class="air-table">
        <thead><tr><th>Provider</th><th>Modelo</th><th>Tokens Input</th><th>Tokens Output</th><th>Total</th><th>Chamadas</th><th>Média/Chamada</th></tr></thead>
        <tbody>
            <?php foreach ($data['por_provider'] as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['provider_nome']) ?></td>
                <td style="font-family:monospace;font-size:11px;"><?= htmlspecialchars($r['modelo']) ?></td>
                <td style="text-align:right;"><?= number_format($r['total_input']) ?></td>
                <td style="text-align:right;"><?= number_format($r['total_output']) ?></td>
                <td style="text-align:right;font-weight:600;"><?= number_format($r['total_input'] + $r['total_output']) ?></td>
                <td style="text-align:right;"><?= number_format($r['chamadas']) ?></td>
                <td style="text-align:right;"><?= $r['chamadas'] > 0 ? number_format(($r['total_input']+$r['total_output'])/$r['chamadas']) : 0 ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php $this->layout('layout/copilot_footer', $data); ?>
