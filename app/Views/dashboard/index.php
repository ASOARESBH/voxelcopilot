<?php use App\Core\Auth; $user = Auth::user(); ?>

<!-- Boas-vindas -->
<div style="margin-bottom:28px;">
    <div style="display:flex;align-items:center;gap:16px;">
        <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#fff;font-weight:700;flex-shrink:0;box-shadow:0 8px 24px rgba(14,165,233,.3);">
            <?= strtoupper(substr($user?->name ?? 'M', 0, 2)) ?>
        </div>
        <div>
            <h2 style="font-family:var(--font-head);font-size:1.3rem;font-weight:800;color:#fff;letter-spacing:-.02em;">
                Olá, <?= htmlspecialchars(explode(' ', $user?->name ?? 'Médico')[0]) ?>!
            </h2>
            <p style="font-size:.83rem;color:var(--muted);">
                <?= date('l, d \d\e F \d\e Y') ?> &nbsp;·&nbsp;
                <?php if ($perfil && $perfil->total_laudos > 0): ?>
                <span style="color:var(--primary);"><?= number_format($perfil->total_laudos) ?> laudos emitidos no total</span>
                <?php else: ?>
                <span>Bem-vindo ao VOXEL Copilot</span>
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-card-icon"><i class="fa-solid fa-file-medical"></i></div>
        <div class="stat-card-value"><?= number_format($laudosHoje) ?></div>
        <div class="stat-card-label">Laudos Hoje</div>
    </div>
    <div class="stat-card green">
        <div class="stat-card-icon"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="stat-card-value"><?= number_format($laudosMes) ?></div>
        <div class="stat-card-label">Laudos este Mês</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-card-icon"><i class="fa-solid fa-file-lines"></i></div>
        <div class="stat-card-value"><?= number_format($totalTemplates) ?></div>
        <div class="stat-card-label">Templates Ativos</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-card-icon"><i class="fa-solid fa-layer-group"></i></div>
        <div class="stat-card-value"><?= number_format($totalLaudos) ?></div>
        <div class="stat-card-label">Total de Laudos</div>
    </div>
</div>

<!-- Ações rápidas -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:28px;">
    <?php
    $actions = [
        ['/workspace/novo', 'fa-plus-circle', 'Novo Laudo', 'Iniciar laudo assistido por IA', 'primary'],
        ['/templates',      'fa-file-lines',  'Templates',  'Gerenciar máscaras de laudo',     'secondary'],
        ['/autotextos',     'fa-bolt',        'Autotextos', 'Frases e atalhos rápidos',         'secondary'],
        ['/perfil',         'fa-user-gear',   'Meu Perfil', 'Configurar preferências de IA',    'secondary'],
    ];
    foreach ($actions as [$url, $icon, $label, $desc, $type]):
    ?>
    <a href="<?= $url ?>" style="
        display:flex;align-items:center;gap:12px;
        background:<?= $type === 'primary' ? 'linear-gradient(135deg,var(--primary),var(--accent))' : 'rgba(255,255,255,.03)' ?>;
        border:1px solid <?= $type === 'primary' ? 'transparent' : 'rgba(255,255,255,.07)' ?>;
        border-radius:var(--radius-sm);padding:14px 16px;
        text-decoration:none;
        transition:all .2s;
        <?= $type === 'primary' ? 'box-shadow:0 4px 20px rgba(14,165,233,.25);' : '' ?>
    " onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
        <div style="width:36px;height:36px;border-radius:9px;
            background:<?= $type === 'primary' ? 'rgba(255,255,255,.15)' : 'rgba(14,165,233,.1)' ?>;
            display:flex;align-items:center;justify-content:center;
            font-size:.85rem;color:<?= $type === 'primary' ? '#fff' : 'var(--primary)' ?>;
            flex-shrink:0;">
            <i class="fa-solid <?= $icon ?>"></i>
        </div>
        <div>
            <div style="font-weight:700;font-size:.85rem;color:<?= $type === 'primary' ? '#fff' : '#c8daea' ?>;"><?= $label ?></div>
            <div style="font-size:.7rem;color:<?= $type === 'primary' ? 'rgba(255,255,255,.7)' : 'var(--muted)' ?>;"><?= $desc ?></div>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<!-- Últimos laudos -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-clock-rotate-left"></i>
            Laudos Recentes
        </div>
        <a href="/workspace" class="btn btn-secondary btn-sm">Ver todos</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Modalidade</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ultimosLaudos)): ?>
                <tr><td colspan="5">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fa-solid fa-file-medical"></i></div>
                        <h3>Nenhum laudo ainda</h3>
                        <p>Seus laudos aparecerão aqui. <a href="/workspace/novo" class="auth-link">Criar o primeiro laudo</a></p>
                    </div>
                </td></tr>
                <?php else: ?>
                <?php foreach ($ultimosLaudos as $l): ?>
                <?php
                    $statusMap = [
                        'rascunho' => ['badge-pendente', 'Rascunho'],
                        'assinado' => ['badge-ativo',    'Assinado'],
                        'cancelado'=> ['badge-inativo',  'Cancelado'],
                    ];
                    [$badgeCls, $badgeLabel] = $statusMap[$l->status] ?? ['badge-inativo', ucfirst($l->status)];
                ?>
                <tr>
                    <td>
                        <div style="font-weight:600;color:#e2e8f0;font-size:.85rem;">
                            <?= $l->patient_nome ? htmlspecialchars($l->patient_nome) : '<span style="color:var(--muted);">Paciente não identificado</span>' ?>
                        </div>
                        <div style="font-size:.7rem;color:var(--muted);font-family:monospace;">
                            <?= htmlspecialchars(substr($l->study_uid ?? '', 0, 30)) ?>...
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
                    <td>
                        <a href="/workspace/<?= (int)$l->id ?>" class="btn btn-ghost btn-xs">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
