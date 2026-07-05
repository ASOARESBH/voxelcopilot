<?php
$espec = json_decode($medico->especialidades ?? '[]', true) ?? [];
$statusMap = ['ativo'=>'badge-ativo','inativo'=>'badge-inativo','pendente'=>'badge-pendente'];
$statusCls = $statusMap[$medico->status] ?? 'badge-inativo';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><?= htmlspecialchars($medico->name) ?></h1>
        <p>Detalhes do cadastro médico</p>
    </div>
    <div class="page-header-actions">
        <a href="/platform/impersonar/<?= (int)$medico->id ?>" class="btn btn-warning">
            <i class="fa-solid fa-user-secret"></i> Ver como Médico
        </a>
        <a href="/platform/medicos/<?= (int)$medico->id ?>/toggle-status"
           class="btn <?= $medico->status === 'ativo' ? 'btn-danger' : 'btn-success' ?>"
           onclick="return confirm('Confirmar alteração de status?')">
            <i class="fa-solid <?= $medico->status === 'ativo' ? 'fa-ban' : 'fa-circle-check' ?>"></i>
            <?= $medico->status === 'ativo' ? 'Desativar' : 'Ativar' ?>
        </a>
        <a href="/platform/medicos" class="btn btn-ghost">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

    <!-- Dados pessoais -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-user"></i> Dados Pessoais</div>
            <span class="badge <?= $statusCls ?>"><?= ucfirst($medico->status) ?></span>
        </div>
        <div class="card-body">
            <table style="width:100%;border-collapse:collapse;">
                <?php $rows = [
                    ['Nome',        htmlspecialchars($medico->name)],
                    ['E-mail',      htmlspecialchars($medico->email)],
                    ['CRM',         $medico->crm ? htmlspecialchars($medico->crm . '/' . $medico->crm_uf) : '—'],
                    ['Telefone',    $medico->telefone ? htmlspecialchars($medico->telefone) : '—'],
                    ['Cadastro',    date('d/m/Y H:i', strtotime($medico->created_at))],
                    ['Último Login',$medico->ultimo_login ? date('d/m/Y H:i', strtotime($medico->ultimo_login)) : 'Nunca'],
                ]; ?>
                <?php foreach ($rows as [$label, $value]): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                    <td style="padding:10px 0;font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;width:40%;"><?= $label ?></td>
                    <td style="padding:10px 0;font-size:.85rem;color:#c8daea;"><?= $value ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <!-- Endereço -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-location-dot"></i> Endereço</div>
        </div>
        <div class="card-body">
            <?php if ($medico->logradouro): ?>
            <p style="font-size:.88rem;color:#c8daea;line-height:1.8;">
                <?= htmlspecialchars($medico->logradouro) ?>, <?= htmlspecialchars($medico->numero ?? 's/n') ?><br>
                <?php if ($medico->complemento): ?><?= htmlspecialchars($medico->complemento) ?><br><?php endif; ?>
                <?= htmlspecialchars($medico->bairro ?? '') ?><br>
                <?= htmlspecialchars($medico->cidade ?? '') ?>/<?= htmlspecialchars($medico->estado ?? '') ?><br>
                <span style="font-family:monospace;color:var(--primary);">CEP: <?= htmlspecialchars($medico->cep ?? '') ?></span>
            </p>
            <?php else: ?>
            <p style="color:var(--muted);font-size:.85rem;">Endereço não informado.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Especialidades -->
    <div class="card" style="grid-column:1/-1;">
        <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-stethoscope"></i> Especialidades</div>
        </div>
        <div class="card-body">
            <?php if (!empty($espec)): ?>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <?php foreach ($espec as $e): ?>
                <span style="background:rgba(14,165,233,.08);border:1px solid rgba(14,165,233,.2);color:var(--primary);padding:5px 14px;border-radius:100px;font-size:.78rem;">
                    <?= htmlspecialchars($e) ?>
                </span>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="color:var(--muted);font-size:.85rem;">Nenhuma especialidade cadastrada.</p>
            <?php endif; ?>
        </div>
    </div>

</div>
