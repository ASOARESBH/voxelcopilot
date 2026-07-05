
<style>
.air-stat-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px 24px; display:flex; align-items:center; gap:16px; }
.air-stat-icon { width:48px; height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; }
.air-stat-icon.blue   { background:#eff6ff; color:#2563eb; }
.air-stat-icon.green  { background:#f0fdf4; color:#16a34a; }
.air-stat-icon.orange { background:#fff7ed; color:#ea580c; }
.air-stat-icon.purple { background:#faf5ff; color:#7c3aed; }
.air-stat-icon.cyan   { background:#ecfeff; color:#0891b2; }
.air-stat-val  { font-size:26px; font-weight:700; color:#1e293b; line-height:1; }
.air-stat-lbl  { font-size:12px; color:#64748b; margin-top:4px; }
.air-card      { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; }
.air-card-title{ font-size:15px; font-weight:600; color:#1e293b; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
.air-table     { width:100%; border-collapse:collapse; font-size:13px; }
.air-table th  { background:#f8fafc; color:#64748b; font-weight:600; text-transform:uppercase; font-size:11px; letter-spacing:.5px; padding:10px 12px; text-align:left; border-bottom:1px solid #e2e8f0; }
.air-table td  { padding:10px 12px; border-bottom:1px solid #f1f5f9; color:#374151; vertical-align:middle; }
.air-table tr:last-child td { border-bottom:none; }
.air-table tr:hover td { background:#f8fafc; }
.air-badge     { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
.air-badge.ok  { background:#f0fdf4; color:#16a34a; }
.air-badge.err { background:#fef2f2; color:#dc2626; }
.air-badge.warn{ background:#fff7ed; color:#ea580c; }
.air-nav       { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:24px; }
.air-nav-btn   { padding:8px 16px; border-radius:8px; font-size:13px; font-weight:500; border:1px solid #e2e8f0; background:#fff; color:#374151; text-decoration:none; transition:all .15s; }
.air-nav-btn:hover,.air-nav-btn.active { background:#2563eb; color:#fff; border-color:#2563eb; }
.air-provider-bar { display:flex; align-items:center; gap:12px; margin-bottom:10px; }
.air-provider-bar-label { width:120px; font-size:13px; color:#374151; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.air-provider-bar-track { flex:1; height:8px; background:#f1f5f9; border-radius:4px; overflow:hidden; }
.air-provider-bar-fill  { height:100%; background:linear-gradient(90deg,#2563eb,#0891b2); border-radius:4px; transition:width .4s; }
.air-provider-bar-val   { font-size:12px; color:#64748b; width:50px; text-align:right; }
</style>

<!-- Navegação do AI Router -->
<div class="air-nav">
    <a href="/ai-router" class="air-nav-btn active">📊 Dashboard</a>
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
    <a href="/ai-router/configuracoes" class="air-nav-btn">⚙️ Config</a>
</div>

<!-- Stats -->
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; margin-bottom:24px;">
    <div class="air-stat-card">
        <div class="air-stat-icon blue">🔌</div>
        <div>
            <div class="air-stat-val"><?= $data['providers_ativos'] ?></div>
            <div class="air-stat-lbl">Providers Ativos</div>
        </div>
    </div>
    <div class="air-stat-card">
        <div class="air-stat-icon purple">🤖</div>
        <div>
            <div class="air-stat-val"><?= $data['modelos_instalados'] ?></div>
            <div class="air-stat-lbl">Modelos Instalados</div>
        </div>
    </div>
    <div class="air-stat-card">
        <div class="air-stat-icon cyan">🪙</div>
        <div>
            <div class="air-stat-val"><?= number_format($data['tokens_hoje']) ?></div>
            <div class="air-stat-lbl">Tokens Hoje</div>
        </div>
    </div>
    <div class="air-stat-card">
        <div class="air-stat-icon orange">💰</div>
        <div>
            <div class="air-stat-val">$<?= number_format($data['custo_hoje'], 4) ?></div>
            <div class="air-stat-lbl">Custo Hoje (USD)</div>
        </div>
    </div>
    <div class="air-stat-card">
        <div class="air-stat-icon green">⚡</div>
        <div>
            <div class="air-stat-val"><?= $data['tempo_medio'] ?>ms</div>
            <div class="air-stat-lbl">Latência Média</div>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
    <!-- Uso por Provider -->
    <div class="air-card">
        <div class="air-card-title">📊 Uso por Provider — últimos 7 dias</div>
        <?php if (empty($data['uso_provider'])): ?>
            <div style="text-align:center; padding:32px; color:#94a3b8;">
                <div style="font-size:32px; margin-bottom:8px;">🔌</div>
                <div>Nenhum provider configurado ainda</div>
                <a href="/ai-router/providers" style="color:#2563eb; font-size:13px; margin-top:8px; display:inline-block;">Adicionar primeiro provider →</a>
            </div>
        <?php else:
            $maxTotal = max(array_column($data['uso_provider'], 'total'));
            foreach ($data['uso_provider'] as $p): ?>
            <div class="air-provider-bar">
                <div class="air-provider-bar-label"><?= htmlspecialchars($p['provider_nome']) ?></div>
                <div class="air-provider-bar-track">
                    <div class="air-provider-bar-fill" style="width:<?= $maxTotal > 0 ? round($p['total']/$maxTotal*100) : 0 ?>%"></div>
                </div>
                <div class="air-provider-bar-val"><?= $p['total'] ?></div>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <!-- Status do Sistema -->
    <div class="air-card">
        <div class="air-card-title">🟢 Status do Sistema</div>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#f8fafc; border-radius:8px;">
                <span style="font-size:13px; color:#374151;">🔀 AI Router</span>
                <span class="air-badge ok">● Operacional</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#f8fafc; border-radius:8px;">
                <span style="font-size:13px; color:#374151;">📜 Histórico</span>
                <span class="air-badge ok">● Ativo</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#f8fafc; border-radius:8px;">
                <span style="font-size:13px; color:#374151;">💰 Custo Monitor</span>
                <span class="air-badge ok">● Monitorando</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#f8fafc; border-radius:8px;">
                <span style="font-size:13px; color:#374151;">🔌 Providers</span>
                <span class="air-badge <?= $data['providers_ativos'] > 0 ? 'ok' : 'warn' ?>">
                    <?= $data['providers_ativos'] > 0 ? '● ' . $data['providers_ativos'] . ' ativo(s)' : '⚠ Nenhum' ?>
                </span>
            </div>
            <?php if ($data['ultima_sync']): ?>
            <div style="font-size:12px; color:#94a3b8; text-align:center; margin-top:4px;">
                Última chamada: <?= date('d/m/Y H:i', strtotime($data['ultima_sync'])) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Últimas chamadas -->
<div class="air-card">
    <div class="air-card-title" style="justify-content:space-between;">
        <span>📜 Últimas Chamadas</span>
        <a href="/ai-router/historico" style="font-size:12px; color:#2563eb; font-weight:400;">Ver histórico completo →</a>
    </div>
    <?php if (empty($data['ultimas'])): ?>
        <div style="text-align:center; padding:32px; color:#94a3b8;">
            <div style="font-size:32px; margin-bottom:8px;">📭</div>
            <div>Nenhuma chamada registrada ainda</div>
            <div style="font-size:12px; margin-top:4px;">As chamadas aparecerão aqui após o primeiro uso do Copilot IA</div>
        </div>
    <?php else: ?>
        <table class="air-table">
            <thead>
                <tr>
                    <th>Provider</th>
                    <th>Modelo</th>
                    <th>Tipo</th>
                    <th>Tokens</th>
                    <th>Custo</th>
                    <th>Tempo</th>
                    <th>Status</th>
                    <th>Quando</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['ultimas'] as $h): ?>
                <tr>
                    <td><?= htmlspecialchars($h['provider_nome']) ?></td>
                    <td style="font-family:monospace; font-size:12px;"><?= htmlspecialchars($h['modelo']) ?></td>
                    <td><?= htmlspecialchars($h['tipo_solicitacao']) ?></td>
                    <td><?= number_format($h['tokens_total']) ?></td>
                    <td>$<?= number_format($h['custo_usd'], 5) ?></td>
                    <td><?= $h['tempo_ms'] ?>ms</td>
                    <td><span class="air-badge <?= $h['status'] === 'ok' ? 'ok' : 'err' ?>"><?= $h['status'] === 'ok' ? '✓ OK' : '✗ Erro' ?></span></td>
                    <td style="font-size:12px; color:#64748b;"><?= date('d/m H:i', strtotime($h['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

