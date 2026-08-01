<?php
$statusMap = [
    'rascunho' => ['badge-pendente', 'Rascunho'],
    'assinado' => ['badge-ativo',    'Assinado'],
    'cancelado'=> ['badge-inativo',  'Cancelado'],
];
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Workspace de Laudos</h1>
        <p><?= number_format($total) ?> laudo(s) encontrado(s)</p>
    </div>
    <div class="page-header-actions" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <?php if (!empty($pacsWorklist) || isset($pacsUnitName)): ?>
        <button id="btn-sincronizar-pacs" onclick="pacsSync()" class="btn" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;display:flex;align-items:center;gap:6px;">
            <i class="fa-solid fa-rotate" id="pacs-sync-icon"></i> Sincronizar com VoxelPACS
        </button>
        <?php endif; ?>
        <a href="/workspace/novo" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Novo Laudo
        </a>
    </div>
</div>

<?php if (isset($pacsWorklist)): ?>
<!-- ═══ WORKLIST PACS ═══════════════════════════════════════════════════════ -->
<div class="card" style="margin-bottom:20px;border:1px solid rgba(99,102,241,.25);" id="pacs-worklist-card">
    <div class="card-body" style="padding:0;">
        <!-- Header -->
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;
                    background:linear-gradient(135deg,rgba(99,102,241,.08),rgba(139,92,246,.06));
                    border-bottom:1px solid rgba(99,102,241,.15);border-radius:8px 8px 0 0;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;background:linear-gradient(135deg,#6366f1,#8b5cf6);
                            border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-hospital" style="color:#fff;font-size:.8rem;"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:.9rem;color:#e2e8f0;">Worklist PACS</div>
                    <div style="font-size:.7rem;color:var(--muted);">
                        <?php if ($pacsUnitName): ?>
                            <?= htmlspecialchars($pacsUnitName) ?> &mdash;
                        <?php endif; ?>
                        <span id="pacs-count"><?= $pacsTotal ?></span> exame(s) aguardando laudo
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <span id="pacs-last-sync" style="font-size:.68rem;color:var(--muted);">
                    Atualizado: <?= date('H:i:s') ?>
                </span>
                <div style="width:8px;height:8px;background:#22c55e;border-radius:50%;" id="pacs-status-dot" title="Sincronizado"></div>
            </div>
        </div>

        <!-- Tabela -->
        <div id="pacs-worklist-body">
        <?php if (empty($pacsWorklist)): ?>
        <div style="padding:28px;text-align:center;color:var(--muted);">
            <i class="fa-solid fa-inbox" style="font-size:2rem;margin-bottom:8px;display:block;opacity:.4;"></i>
            <div style="font-size:.85rem;">Nenhum exame assumido no VoxelPACS no momento.</div>
            <div style="font-size:.75rem;margin-top:4px;">Quando você assumir um exame no PACS, ele aparecerá aqui automaticamente.</div>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="table" style="margin:0;">
            <thead>
                <tr>
                    <th style="font-size:.72rem;">Paciente</th>
                    <th style="font-size:.72rem;">Modalidade</th>
                    <th style="font-size:.72rem;">Unidade</th>
                    <th style="font-size:.72rem;">Data Estudo</th>
                    <th style="font-size:.72rem;">Status</th>
                    <th style="font-size:.72rem;">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $pacsStatusMap = [
                'pendente'        => ['#f59e0b','Aguardando'],
                'enviado_copilot' => ['#6366f1','Assumido'],
                'em_laudo'        => ['#0ea5e9','Em Laudo'],
                'rascunho'        => ['#8b5cf6','Rascunho'],
                'assinado'        => ['#22c55e','Assinado'],
                'erro'            => ['#ef4444','Erro'],
            ];
            foreach ($pacsWorklist as $pw):
                $st = $pw->status_copilot ?? $pw['status_copilot'] ?? 'pendente';
                [$stColor, $stLabel] = $pacsStatusMap[$st] ?? ['#6b7280','Desconhecido'];
                $pName = $pw->patient_name ?? $pw['patient_name'] ?? 'Não identificado';
                $pMods = $pw->modalities   ?? $pw['modalities']   ?? '';
                $pInst = $pw->institution_name ?? $pw['institution_name'] ?? '';
                $pDate = $pw->study_date   ?? $pw['study_date']   ?? '';
                $pUid  = $pw->study_instance_uid ?? $pw['study_instance_uid'] ?? '';
                $pLaudo= $pw->laudo_id     ?? $pw['laudo_id']     ?? null;
                $pwId  = $pw->id           ?? $pw['id']           ?? 0;
            ?>
            <tr>
                <td>
                    <div style="font-weight:600;font-size:.82rem;color:#e2e8f0;"><?= htmlspecialchars($pName) ?></div>
                    <div style="font-size:.68rem;color:var(--muted);font-family:monospace;"><?= htmlspecialchars(substr($pUid, 0, 24)) ?>...</div>
                </td>
                <td>
                    <?php foreach (explode('\\', $pMods) as $m): $m = trim($m); if (!$m) continue; ?>
                    <span style="background:rgba(14,165,233,.1);border:1px solid rgba(14,165,233,.2);color:#38bdf8;
                                 padding:2px 8px;border-radius:100px;font-size:.68rem;font-weight:700;margin-right:2px;">
                        <?= htmlspecialchars($m) ?>
                    </span>
                    <?php endforeach; ?>
                </td>
                <td style="font-size:.75rem;color:var(--muted);"><?= htmlspecialchars($pInst) ?></td>
                <td style="font-size:.75rem;color:var(--muted);">
                    <?= $pDate ? date('d/m/Y', strtotime($pDate)) : '—' ?>
                </td>
                <td>
                    <span style="background:<?= $stColor ?>22;border:1px solid <?= $stColor ?>44;color:<?= $stColor ?>;
                                 padding:2px 10px;border-radius:100px;font-size:.7rem;font-weight:700;">
                        <?= $stLabel ?>
                    </span>
                </td>
                <td>
                    <?php if ($pLaudo): ?>
                    <a href="/workspace/<?= (int)$pLaudo ?>" class="btn btn-ghost btn-xs" style="margin-right:4px;">
                        <i class="fa-solid fa-pen"></i> Laudar
                    </a>
                    <?php else: ?>
                    <button onclick="pacsAbrirViewer('<?= htmlspecialchars($pUid) ?>', <?= (int)$pwId ?>)"
                            class="btn btn-ghost btn-xs" style="margin-right:4px;">
                        <i class="fa-solid fa-eye"></i> Abrir
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
        </div><!-- /#pacs-worklist-body -->
    </div>
</div>

<script>
// ── Worklist PACS: auto-refresh 60s + botão Sincronizar ──────────────────
let pacsAutoTimer = setInterval(pacsSync, 60000);

function pacsSync() {
    const icon = document.getElementById('pacs-sync-icon');
    const dot  = document.getElementById('pacs-status-dot');
    if (icon) icon.classList.add('fa-spin');
    if (dot)  dot.style.background = '#f59e0b';

    fetch('/api/workspace/worklist', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            const countEl = document.getElementById('pacs-count');
            if (countEl) countEl.textContent = data.total;

            // Atualiza o corpo da tabela
            const body = document.getElementById('pacs-worklist-body');
            if (body && data.html) {
                body.innerHTML = data.html;
            } else if (body && data.itens !== undefined) {
                if (data.itens.length === 0) {
                    body.innerHTML = '<div style="padding:28px;text-align:center;color:var(--muted);">' +
                        '<i class="fa-solid fa-inbox" style="font-size:2rem;margin-bottom:8px;display:block;opacity:.4;"></i>' +
                        '<div style="font-size:.85rem;">Nenhum exame assumido no VoxelPACS no momento.</div></div>';
                }
            }

            const ts = document.getElementById('pacs-last-sync');
            if (ts) ts.textContent = 'Atualizado: ' + new Date().toLocaleTimeString('pt-BR');
            if (dot) dot.style.background = '#22c55e';
        } else {
            if (dot) dot.style.background = '#ef4444';
        }
    })
    .catch(() => { if (dot) dot.style.background = '#ef4444'; })
    .finally(() => { if (icon) icon.classList.remove('fa-spin'); });
}

function pacsAbrirViewer(studyUid, worklistId) {
    fetch('/api/workspace/viewer-url?study_uid=' + encodeURIComponent(studyUid), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok && data.url) {
            window.open(data.url, '_blank');
        } else {
            alert('Não foi possível obter a URL do viewer. Verifique a configuração do PACS.');
        }
    })
    .catch(() => alert('Erro ao conectar ao servidor.'));
}

window.addEventListener('beforeunload', () => clearInterval(pacsAutoTimer));
</script>
<?php endif; ?>
<!-- ═══ FIM WORKLIST PACS ═══════════════════════════════════════════════════ -->

<!-- Filtros -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <label class="form-label">Buscar</label>
                <input type="text" name="busca" class="form-control" placeholder="Paciente ou Study UID..."
                    value="<?= htmlspecialchars($busca) ?>">
            </div>
            <div style="min-width:140px;">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="rascunho" <?= $status === 'rascunho' ? 'selected' : '' ?>>Rascunho</option>
                    <option value="assinado" <?= $status === 'assinado' ? 'selected' : '' ?>>Assinado</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-magnifying-glass"></i> Filtrar
            </button>
            <?php if ($busca || $status): ?>
            <a href="/workspace" class="btn btn-ghost">
                <i class="fa-solid fa-xmark"></i> Limpar
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabela -->
<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Modalidade</th>
                    <th>Status</th>
                    <th>Criado em</th>
                    <th>Assinado em</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($laudos)): ?>
                <tr><td colspan="6">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fa-solid fa-file-medical"></i></div>
                        <h3>Nenhum laudo encontrado</h3>
                        <p><a href="/workspace/novo" class="auth-link">Criar o primeiro laudo</a></p>
                    </div>
                </td></tr>
                <?php else: ?>
                <?php foreach ($laudos as $l):
                    [$badgeCls, $badgeLabel] = $statusMap[$l->status] ?? ['badge-inativo', ucfirst($l->status)];
                ?>
                <tr>
                    <td>
                        <div style="font-weight:600;color:#e2e8f0;">
                            <?= $l->patient_nome ? htmlspecialchars($l->patient_nome) : '<span style="color:var(--muted);">Não identificado</span>' ?>
                        </div>
                        <div style="font-size:.7rem;color:var(--muted);font-family:monospace;">
                            <?= htmlspecialchars(substr($l->study_uid ?? '', 0, 28)) ?>...
                        </div>
                    </td>
                    <td>
                        <?php if ($l->modalidade): ?>
                        <span style="background:rgba(14,165,233,.08);border:1px solid rgba(14,165,233,.2);color:var(--primary);padding:3px 10px;border-radius:100px;font-size:.72rem;font-weight:700;">
                            <?= htmlspecialchars($l->modalidade) ?>
                        </span>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><span class="badge <?= $badgeCls ?>"><?= $badgeLabel ?></span></td>
                    <td style="font-size:.75rem;color:var(--muted);">
                        <?= date('d/m/Y H:i', strtotime($l->created_at)) ?>
                    </td>
                    <td style="font-size:.75rem;color:var(--muted);">
                        <?= $l->assinado_em ? date('d/m/Y H:i', strtotime($l->assinado_em)) : '—' ?>
                    </td>
                    <td>
                        <a href="/workspace/<?= (int)$l->id ?>" class="btn btn-ghost btn-xs">
                            <i class="fa-solid <?= $l->status === 'rascunho' ? 'fa-pen' : 'fa-eye' ?>"></i>
                            <?= $l->status === 'rascunho' ? 'Editar' : 'Ver' ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>&busca=<?= urlencode($busca) ?>&status=<?= urlencode($status) ?>">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <?php else: ?><span class="disabled"><i class="fa-solid fa-chevron-left"></i></span><?php endif; ?>
        <?php for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
        <a href="?page=<?= $p ?>&busca=<?= urlencode($busca) ?>&status=<?= urlencode($status) ?>"
           class="<?= $p===$page?'active':'' ?>"><?= $p ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page+1 ?>&busca=<?= urlencode($busca) ?>&status=<?= urlencode($status) ?>">
            <i class="fa-solid fa-chevron-right"></i>
        </a>
        <?php else: ?><span class="disabled"><i class="fa-solid fa-chevron-right"></i></span><?php endif; ?>
    </div>
    <?php endif; ?>
</div>
