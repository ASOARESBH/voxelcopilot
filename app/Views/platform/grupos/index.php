<?php
$icones = [
    'fa-user-doctor','fa-stethoscope','fa-x-ray','fa-heart-pulse','fa-brain',
    'fa-ribbon','fa-bone','fa-baby','fa-venus','fa-droplet','fa-lungs',
    'fa-flask','fa-atom','fa-wave-square','fa-graduation-cap','fa-hospital',
];
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Grupos de Médicos</h1>
        <p>Organização e categorização dos médicos cadastrados</p>
    </div>
    <div class="page-header-actions">
        <a href="/platform/medicos" class="btn btn-ghost">
            <i class="fa-solid fa-arrow-left"></i> Médicos
        </a>
        <a href="/platform/grupos/novo" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Novo Grupo
        </a>
    </div>
</div>

<?php if ($msg === 'criado'): ?>
<div class="alert alert-success" style="margin-bottom:16px;">
    <i class="fa-solid fa-circle-check"></i> Grupo criado com sucesso!
</div>
<?php elseif ($msg === 'atualizado'): ?>
<div class="alert alert-success" style="margin-bottom:16px;">
    <i class="fa-solid fa-circle-check"></i> Grupo atualizado com sucesso!
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:30px;">
    <?php if (empty($grupos)): ?>
    <div class="card" style="grid-column:1/-1;">
        <div class="card-body">
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fa-solid fa-layer-group"></i></div>
                <h3>Nenhum grupo cadastrado</h3>
                <p>Execute a migration SQL para carregar os grupos padrão.</p>
                <a href="/platform/grupos/novo" class="btn btn-primary" style="margin-top:12px;">
                    <i class="fa-solid fa-plus"></i> Criar Grupo
                </a>
            </div>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($grupos as $g): ?>
    <div class="card" style="border-top:3px solid <?= htmlspecialchars($g->cor ?? '#1a56db') ?>;">
        <div class="card-body" style="padding:18px 20px;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                <div style="width:40px;height:40px;border-radius:10px;background:<?= htmlspecialchars($g->cor ?? '#1a56db') ?>20;display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid <?= htmlspecialchars($g->icone ?? 'fa-user-doctor') ?>"
                       style="color:<?= htmlspecialchars($g->cor ?? '#1a56db') ?>;font-size:1.1rem;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:700;font-size:.9rem;color:var(--gray-800);"><?= htmlspecialchars($g->nome) ?></div>
                    <?php if ($g->descricao): ?>
                    <div style="font-size:.75rem;color:var(--muted);margin-top:2px;"><?= htmlspecialchars($g->descricao) ?></div>
                    <?php endif; ?>
                </div>
                <?php if (!$g->ativo): ?>
                <span class="badge badge-inativo">Inativo</span>
                <?php endif; ?>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div style="font-size:.8rem;color:var(--text-2);">
                    <i class="fa-solid fa-user-doctor" style="color:var(--muted);margin-right:4px;"></i>
                    <strong><?= (int)($g->total_medicos ?? 0) ?></strong> médico(s)
                </div>
                <div style="display:flex;gap:6px;">
                    <a href="/platform/medicos?grupo=<?= (int)$g->id ?>" class="btn btn-ghost btn-xs" title="Ver médicos do grupo">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <a href="/platform/grupos/<?= (int)$g->id ?>/editar" class="btn btn-ghost btn-xs"
                       title="Editar grupo" style="color:var(--blue-600);">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
