<?php
function statusBadge(string $status): string {
    $map = [
        'ativo'    => 'badge-ativo',
        'inativo'  => 'badge-inativo',
        'pendente' => 'badge-pendente',
        'trial'    => 'badge-trial',
        'suspenso' => 'badge-suspenso',
    ];
    $label = ucfirst($status);
    $cls   = $map[$status] ?? 'badge-inativo';
    return "<span class=\"badge {$cls}\">{$label}</span>";
}
?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-card-icon"><i class="fa-solid fa-user-doctor"></i></div>
        <div class="stat-card-value"><?= number_format($totalMedicos) ?></div>
        <div class="stat-card-label">Total de Médicos</div>
    </div>
    <div class="stat-card green">
        <div class="stat-card-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-card-value"><?= number_format($medicosAtivos) ?></div>
        <div class="stat-card-label">Médicos Ativos</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-card-icon"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-card-value"><?= number_format($medicosPendentes) ?></div>
        <div class="stat-card-label">Aguardando Ativação</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-card-icon"><i class="fa-solid fa-file-medical"></i></div>
        <div class="stat-card-value"><?= number_format($totalLaudos) ?></div>
        <div class="stat-card-label">Laudos Emitidos</div>
    </div>
</div>

<!-- Últimos cadastros -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-user-plus"></i>
            Últimos Médicos Cadastrados
        </div>
        <a href="/platform/medicos" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-list"></i> Ver todos
        </a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Médico</th>
                    <th>CRM</th>
                    <th>Especialidades</th>
                    <th>Status</th>
                    <th>Cadastro</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ultimos)): ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="fa-solid fa-user-doctor"></i></div>
                            <h3>Nenhum médico cadastrado</h3>
                            <p>Os médicos aparecerão aqui após o primeiro cadastro.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($ultimos as $m): ?>
                <?php
                    $espec = [];
                    if ($m->especialidades) {
                        $espec = json_decode($m->especialidades, true) ?? [];
                    }
                    $especStr = !empty($espec) ? implode(', ', array_slice($espec, 0, 2)) . (count($espec) > 2 ? '...' : '') : '—';
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
                        <?php else: ?>
                        <span style="color:var(--muted);">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.78rem;color:var(--text-2);max-width:200px;">
                        <?= htmlspecialchars($especStr) ?>
                    </td>
                    <td><?= statusBadge($m->status) ?></td>
                    <td style="font-size:.78rem;color:var(--muted);">
                        <?= date('d/m/Y', strtotime($m->created_at)) ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <a href="/platform/medicos/<?= (int)$m->id ?>" class="btn btn-ghost btn-xs" title="Ver detalhes">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="/platform/impersonar/<?= (int)$m->id ?>" class="btn btn-warning btn-xs" title="Visualizar como médico">
                                <i class="fa-solid fa-user-secret"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
