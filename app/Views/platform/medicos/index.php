<?php
function statusBadge(string $status): string {
    $map = ['ativo'=>'badge-ativo','inativo'=>'badge-inativo','pendente'=>'badge-pendente'];
    return '<span class="badge ' . ($map[$status] ?? 'badge-inativo') . '">' . ucfirst($status) . '</span>';
}
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Médicos</h1>
        <p>Total de <?= number_format($total) ?> médico(s) cadastrado(s)</p>
    </div>
</div>

<!-- Filtros -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px 22px;">
        <form method="GET" action="/platform/medicos" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <label class="form-label">Buscar</label>
                <input type="text" name="busca" class="form-control" placeholder="Nome, e-mail ou CRM..."
                    value="<?= htmlspecialchars($busca) ?>">
            </div>
            <div style="min-width:140px;">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="ativo"    <?= $status === 'ativo'    ? 'selected' : '' ?>>Ativo</option>
                    <option value="inativo"  <?= $status === 'inativo'  ? 'selected' : '' ?>>Inativo</option>
                    <option value="pendente" <?= $status === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-magnifying-glass"></i> Filtrar
            </button>
            <?php if ($busca || $status): ?>
            <a href="/platform/medicos" class="btn btn-ghost">
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
                    <th>Médico</th>
                    <th>CRM</th>
                    <th>Especialidades</th>
                    <th>Localização</th>
                    <th>Último Login</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($medicos)): ?>
                <tr><td colspan="7">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fa-solid fa-user-doctor"></i></div>
                        <h3>Nenhum médico encontrado</h3>
                        <p>Tente ajustar os filtros de busca.</p>
                    </div>
                </td></tr>
                <?php else: ?>
                <?php foreach ($medicos as $m): ?>
                <?php
                    $espec = json_decode($m->especialidades ?? '[]', true) ?? [];
                    $especStr = !empty($espec) ? implode(', ', array_slice($espec, 0, 2)) . (count($espec) > 2 ? ' +' . (count($espec)-2) : '') : '—';
                ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,rgba(14,165,233,.15),rgba(6,182,212,.08));border:1px solid rgba(14,165,233,.15);display:flex;align-items:center;justify-content:center;font-size:.72rem;color:var(--primary);font-weight:700;flex-shrink:0;">
                                <?= strtoupper(substr($m->name, 0, 2)) ?>
                            </div>
                            <div>
                                <div style="font-weight:600;color:#e2e8f0;"><?= htmlspecialchars($m->name) ?></div>
                                <div style="font-size:.72rem;color:var(--muted);"><?= htmlspecialchars($m->email) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if ($m->crm): ?>
                        <code style="font-size:.78rem;color:#7dd3fc;background:rgba(14,165,233,.08);padding:2px 8px;border-radius:4px;">
                            <?= htmlspecialchars($m->crm) ?>/<?= htmlspecialchars($m->crm_uf ?? '') ?>
                        </code>
                        <?php else: ?><span style="color:var(--muted);">—</span><?php endif; ?>
                    </td>
                    <td style="font-size:.75rem;color:var(--muted);max-width:220px;">
                        <?= htmlspecialchars($especStr) ?>
                    </td>
                    <td style="font-size:.78rem;color:var(--muted);">
                        <?php if ($m->cidade): ?>
                        <?= htmlspecialchars($m->cidade) ?>/<?= htmlspecialchars($m->estado ?? '') ?>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td style="font-size:.75rem;color:var(--muted);">
                        <?= $m->ultimo_login ? date('d/m/Y H:i', strtotime($m->ultimo_login)) : 'Nunca' ?>
                    </td>
                    <td><?= statusBadge($m->status) ?></td>
                    <td>
                        <div style="display:flex;gap:5px;">
                            <a href="/platform/medicos/<?= (int)$m->id ?>" class="btn btn-ghost btn-xs" title="Ver detalhes">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="/platform/impersonar/<?= (int)$m->id ?>" class="btn btn-warning btn-xs" title="Ver como médico">
                                <i class="fa-solid fa-user-secret"></i>
                            </a>
                            <a href="/platform/medicos/<?= (int)$m->id ?>/toggle-status"
                               class="btn <?= $m->status === 'ativo' ? 'btn-danger' : 'btn-success' ?> btn-xs"
                               onclick="return confirm('Confirmar alteração de status?')"
                               title="<?= $m->status === 'ativo' ? 'Desativar' : 'Ativar' ?>">
                                <i class="fa-solid <?= $m->status === 'ativo' ? 'fa-ban' : 'fa-circle-check' ?>"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>&busca=<?= urlencode($busca) ?>&status=<?= urlencode($status) ?>">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <?php else: ?>
        <span class="disabled"><i class="fa-solid fa-chevron-left"></i></span>
        <?php endif; ?>

        <?php for ($p = max(1, $page-2); $p <= min($totalPages, $page+2); $p++): ?>
        <a href="?page=<?= $p ?>&busca=<?= urlencode($busca) ?>&status=<?= urlencode($status) ?>"
           class="<?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page+1 ?>&busca=<?= urlencode($busca) ?>&status=<?= urlencode($status) ?>">
            <i class="fa-solid fa-chevron-right"></i>
        </a>
        <?php else: ?>
        <span class="disabled"><i class="fa-solid fa-chevron-right"></i></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
