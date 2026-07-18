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
    <div class="page-header-actions">
        <a href="/platform/grupos" class="btn btn-ghost">
            <i class="fa-solid fa-layer-group"></i> Grupos
        </a>
        <a href="/platform/medicos/novo" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Novo Médico
        </a>
    </div>
</div>

<?php if ($msg === 'criado'): ?>
<div class="alert alert-success" style="margin-bottom:16px;">
    <i class="fa-solid fa-circle-check"></i> Médico cadastrado com sucesso!
</div>
<?php elseif ($msg === 'atualizado'): ?>
<div class="alert alert-success" style="margin-bottom:16px;">
    <i class="fa-solid fa-circle-check"></i> Dados do médico atualizados com sucesso!
</div>
<?php elseif ($msg === 'status_atualizado'): ?>
<div class="alert alert-success" style="margin-bottom:16px;">
    <i class="fa-solid fa-circle-check"></i> Status atualizado com sucesso!
</div>
<?php endif; ?>

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
            <?php if (!empty($grupos)): ?>
            <div style="min-width:160px;">
                <label class="form-label">Grupo</label>
                <select name="grupo" class="form-select">
                    <option value="">Todos os grupos</option>
                    <?php foreach ($grupos as $g): ?>
                    <option value="<?= (int)$g->id ?>" <?= $grupo == $g->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g->nome) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-magnifying-glass"></i> Filtrar
            </button>
            <?php if ($busca || $status || $grupo): ?>
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
                    <th>Grupo</th>
                    <th>Especialidades</th>
                    <th>Localização</th>
                    <th>Último Login</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($medicos)): ?>
                <tr><td colspan="8">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fa-solid fa-user-doctor"></i></div>
                        <h3>Nenhum médico encontrado</h3>
                        <p>Tente ajustar os filtros ou <a href="/platform/medicos/novo" style="color:var(--blue-600);">cadastre um novo médico</a>.</p>
                    </div>
                </td></tr>
                <?php else: ?>
                <?php foreach ($medicos as $m): ?>
                <?php
                    $espec    = json_decode($m->especialidades ?? '[]', true) ?? [];
                    $especStr = !empty($espec) ? implode(', ', array_slice($espec, 0, 2)) . (count($espec) > 2 ? ' +' . (count($espec)-2) : '') : '—';
                ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="avatar avatar-sm avatar-blue"><?= strtoupper(substr($m->name, 0, 2)) ?></div>
                            <div>
                                <div style="font-weight:600;color:var(--gray-800);font-size:.83rem;"><?= htmlspecialchars($m->name) ?></div>
                                <div style="font-size:.72rem;color:var(--muted);"><?= htmlspecialchars($m->email) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if ($m->crm): ?>
                        <span style="font-size:.77rem;font-weight:600;color:var(--gray-700);background:var(--gray-100);border:1px solid var(--border);padding:2px 8px;border-radius:5px;">
                            <?= htmlspecialchars($m->crm) ?>/<?= htmlspecialchars($m->crm_uf ?? '') ?>
                        </span>
                        <?php else: ?><span style="color:var(--muted);">—</span><?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($m->grupo_nome)): ?>
                        <span style="font-size:.75rem;font-weight:600;padding:2px 9px;border-radius:20px;color:#fff;background:<?= htmlspecialchars($m->grupo_cor ?? '#1a56db') ?>;">
                            <?= htmlspecialchars($m->grupo_nome) ?>
                        </span>
                        <?php else: ?><span style="color:var(--muted);font-size:.78rem;">—</span><?php endif; ?>
                    </td>
                    <td style="font-size:.78rem;color:var(--text-2);max-width:200px;"><?= htmlspecialchars($especStr) ?></td>
                    <td style="font-size:.78rem;color:var(--text-2);">
                        <?= ($m->cidade ? htmlspecialchars($m->cidade) . '/' . htmlspecialchars($m->estado ?? '') : '—') ?>
                    </td>
                    <td style="font-size:.78rem;color:var(--muted);">
                        <?= $m->ultimo_login ? date('d/m/Y H:i', strtotime($m->ultimo_login)) : 'Nunca' ?>
                    </td>
                    <td><?= statusBadge($m->status) ?></td>
                    <td>
                        <div style="display:flex;gap:5px;">
                            <a href="/platform/medicos/<?= (int)$m->id ?>" class="btn btn-ghost btn-xs" title="Ver detalhes">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="/platform/medicos/<?= (int)$m->id ?>/editar" class="btn btn-ghost btn-xs"
                               title="Editar médico" style="color:var(--blue-600);">
                                <i class="fa-solid fa-pen-to-square"></i>
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
        <a href="?page=<?= $page-1 ?>&busca=<?= urlencode($busca) ?>&status=<?= urlencode($status) ?>&grupo=<?= urlencode($grupo) ?>">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <?php else: ?>
        <span class="disabled"><i class="fa-solid fa-chevron-left"></i></span>
        <?php endif; ?>

        <?php for ($p = max(1, $page-2); $p <= min($totalPages, $page+2); $p++): ?>
        <a href="?page=<?= $p ?>&busca=<?= urlencode($busca) ?>&status=<?= urlencode($status) ?>&grupo=<?= urlencode($grupo) ?>"
           class="<?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page+1 ?>&busca=<?= urlencode($busca) ?>&status=<?= urlencode($status) ?>&grupo=<?= urlencode($grupo) ?>">
            <i class="fa-solid fa-chevron-right"></i>
        </a>
        <?php else: ?>
        <span class="disabled"><i class="fa-solid fa-chevron-right"></i></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
