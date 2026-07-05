<?php use App\Core\Auth; $user = Auth::user(); ?>

<!-- Welcome Hero -->
<div class="welcome-hero">
    <div class="welcome-hero-text">
        <h2>Olá, Dr(a). <?= htmlspecialchars(explode(' ', $user?->name ?? 'Médico')[0]) ?>!</h2>
        <p>
            <?= date('d/m/Y') ?>
            &nbsp;·&nbsp;
            <?php if (!empty($perfil) && ($perfil->total_laudos ?? 0) > 0): ?>
                <span style="color:rgba(255,255,255,.9);"><?= number_format($perfil->total_laudos) ?> laudos emitidos no total</span>
            <?php else: ?>
                <span>Bem-vindo ao VOXEL Copilot</span>
            <?php endif; ?>
        </p>
    </div>
    <div class="welcome-hero-avatar" aria-hidden="true">
        <?= strtoupper(substr($user?->name ?? 'M', 0, 2)) ?>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-card-icon"><i class="fa-solid fa-file-medical" aria-hidden="true"></i></div>
        <div class="stat-card-value"><?= number_format($laudosHoje ?? 0) ?></div>
        <div class="stat-card-label">Laudos Hoje</div>
    </div>
    <div class="stat-card green">
        <div class="stat-card-icon"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i></div>
        <div class="stat-card-value"><?= number_format($laudosMes ?? 0) ?></div>
        <div class="stat-card-label">Laudos este Mês</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-card-icon"><i class="fa-solid fa-file-lines" aria-hidden="true"></i></div>
        <div class="stat-card-value"><?= number_format($totalTemplates ?? 0) ?></div>
        <div class="stat-card-label">Templates Ativos</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-card-icon"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></div>
        <div class="stat-card-value"><?= number_format($totalLaudos ?? 0) ?></div>
        <div class="stat-card-label">Total de Laudos</div>
    </div>
</div>

<!-- Ações rápidas -->
<div class="quick-actions" role="list">
    <a href="/workspace/novo" class="quick-action-card" role="listitem">
        <div class="quick-action-icon blue" aria-hidden="true">
            <i class="fa-solid fa-plus-circle"></i>
        </div>
        <div>
            <div class="quick-action-label">Novo Laudo</div>
            <div style="font-size:.7rem;color:var(--muted);margin-top:2px;">Iniciar laudo com IA</div>
        </div>
    </a>
    <a href="/templates" class="quick-action-card" role="listitem">
        <div class="quick-action-icon purple" aria-hidden="true">
            <i class="fa-solid fa-file-lines"></i>
        </div>
        <div>
            <div class="quick-action-label">Templates</div>
            <div style="font-size:.7rem;color:var(--muted);margin-top:2px;">Máscaras de laudo</div>
        </div>
    </a>
    <a href="/autotextos" class="quick-action-card" role="listitem">
        <div class="quick-action-icon green" aria-hidden="true">
            <i class="fa-solid fa-bolt"></i>
        </div>
        <div>
            <div class="quick-action-label">Autotextos</div>
            <div style="font-size:.7rem;color:var(--muted);margin-top:2px;">Frases e atalhos</div>
        </div>
    </a>
    <a href="/perfil" class="quick-action-card" role="listitem">
        <div class="quick-action-icon orange" aria-hidden="true">
            <i class="fa-solid fa-user-gear"></i>
        </div>
        <div>
            <div class="quick-action-label">Meu Perfil</div>
            <div style="font-size:.7rem;color:var(--muted);margin-top:2px;">Preferências de IA</div>
        </div>
    </a>
</div>

<!-- Laudos recentes -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
            Laudos Recentes
        </div>
        <a href="/workspace" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-list" aria-hidden="true"></i>
            Ver todos
        </a>
    </div>
    <div class="table-wrap">
        <table class="table" role="table">
            <thead>
                <tr>
                    <th scope="col">Paciente</th>
                    <th scope="col">Modalidade</th>
                    <th scope="col">Status</th>
                    <th scope="col">Data</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ultimosLaudos)): ?>
                <tr><td colspan="5">
                    <div class="empty-state">
                        <div class="empty-state-icon" aria-hidden="true"><i class="fa-solid fa-file-medical"></i></div>
                        <h3>Nenhum laudo ainda</h3>
                        <p>Seus laudos aparecerão aqui após o primeiro registro.</p>
                        <a href="/workspace/novo" class="btn btn-primary" style="margin-top:16px;">
                            <i class="fa-solid fa-plus" aria-hidden="true"></i>
                            Criar primeiro laudo
                        </a>
                    </div>
                </td></tr>
                <?php else: ?>
                <?php foreach ($ultimosLaudos as $l):
                    $statusMap = [
                        'rascunho' => ['badge-pendente', 'Rascunho'],
                        'assinado' => ['badge-ativo',    'Assinado'],
                        'cancelado'=> ['badge-inativo',  'Cancelado'],
                    ];
                    [$badgeCls, $badgeLabel] = $statusMap[$l->status ?? 'rascunho'] ?? ['badge-inativo', ucfirst($l->status ?? '')];
                ?>
                <tr>
                    <td>
                        <div style="font-weight:600;color:var(--gray-800);font-size:.83rem;">
                            <?= !empty($l->patient_nome) ? htmlspecialchars($l->patient_nome) : '<span style="color:var(--muted);">Não identificado</span>' ?>
                        </div>
                        <?php if (!empty($l->study_uid)): ?>
                        <div style="font-size:.69rem;color:var(--muted);font-family:monospace;">
                            <?= htmlspecialchars(substr($l->study_uid, 0, 28)) ?>…
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($l->modalidade)): ?>
                        <span class="badge badge-pendente" style="background:var(--blue-50);color:var(--royal);border-color:var(--blue-200);">
                            <?= htmlspecialchars($l->modalidade) ?>
                        </span>
                        <?php else: ?>
                        <span style="color:var(--muted);">—</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge <?= $badgeCls ?>"><?= $badgeLabel ?></span></td>
                    <td style="font-size:.78rem;color:var(--muted);">
                        <?= date('d/m/Y H:i', strtotime($l->created_at ?? 'now')) ?>
                    </td>
                    <td>
                        <a href="/workspace/<?= (int)$l->id ?>" class="btn btn-ghost btn-xs" title="Abrir laudo">
                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
