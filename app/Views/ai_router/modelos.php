<?php $this->layout('layout/copilot_header', $data); ?>
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
.air-badge.off{background:#f1f5f9;color:#64748b;}
.air-badge.blue{background:#eff6ff;color:#2563eb;}
</style>
<div class="air-nav">
    <a href="/ai-router" class="air-nav-btn">📊 Dashboard</a>
    <a href="/ai-router/providers" class="air-nav-btn">🔌 Providers</a>
    <a href="/ai-router/modelos" class="air-nav-btn active">🤖 Modelos</a>
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
<div class="air-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <div>
            <h2 style="font-size:18px;font-weight:700;color:#1e293b;margin:0;">Catálogo de Modelos</h2>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0;">Modelos disponíveis por provider — criados automaticamente ao adicionar um provider</p>
        </div>
    </div>
    <?php if (empty($data['modelos'])): ?>
    <div style="text-align:center;padding:48px;color:#94a3b8;">
        <div style="font-size:48px;margin-bottom:16px;">🤖</div>
        <div style="font-size:16px;font-weight:600;color:#374151;margin-bottom:8px;">Nenhum modelo instalado</div>
        <div style="font-size:13px;">Adicione um provider em <a href="/ai-router/providers" style="color:#2563eb;">Providers</a> para ver os modelos disponíveis</div>
    </div>
    <?php else: ?>
    <table class="air-table">
        <thead>
            <tr>
                <th>Modelo</th>
                <th>Provider</th>
                <th>Contexto</th>
                <th>Preço Input /1k</th>
                <th>Preço Output /1k</th>
                <th>Vision</th>
                <th>Tools</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['modelos'] as $m): ?>
            <tr>
                <td>
                    <div style="font-weight:600;color:#1e293b;"><?= htmlspecialchars($m['nome_display'] ?: $m['nome']) ?></div>
                    <div style="font-size:11px;color:#94a3b8;font-family:monospace;"><?= htmlspecialchars($m['nome']) ?></div>
                </td>
                <td><?= htmlspecialchars($m['provider_nome']) ?></td>
                <td><?= number_format($m['contexto_tokens']) ?> tokens</td>
                <td style="font-family:monospace;">$<?= number_format($m['preco_input'], 5) ?></td>
                <td style="font-family:monospace;">$<?= number_format($m['preco_output'], 5) ?></td>
                <td><?= $m['suporta_vision'] ? '<span class="air-badge ok">✓</span>' : '<span class="air-badge off">—</span>' ?></td>
                <td><?= $m['suporta_tools'] ? '<span class="air-badge ok">✓</span>' : '<span class="air-badge off">—</span>' ?></td>
                <td><span class="air-badge <?= $m['is_active'] ? 'ok' : 'off' ?>"><?= $m['is_active'] ? '● Ativo' : '○ Inativo' ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php $this->layout('layout/copilot_footer', $data); ?>
