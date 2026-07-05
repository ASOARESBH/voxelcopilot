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
    <div class="page-header-actions">
        <a href="/workspace/novo" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Novo Laudo
        </a>
    </div>
</div>

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
