<?php $this->layout('layout/copilot_header', ['title' => $title ?? 'Timeline Clínica', 'pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]); ?>

<div class="page-content">

  <div class="card mb-4">
    <form method="GET" action="/timeline" class="d-flex gap-3 align-items-end">
      <div style="flex:1">
        <label class="form-label-sm">Buscar paciente</label>
        <div class="input-icon-wrap">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Nome ou CPF..." class="form-control">
        </div>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
    </form>
  </div>

  <div class="timeline-cards">
    <?php foreach ($pacientes as $p): ?>
    <div class="card timeline-patient-card">
      <div class="timeline-patient-header">
        <div class="avatar-lg"><?= mb_substr($p['nome'], 0, 2) ?></div>
        <div>
          <strong><?= htmlspecialchars($p['nome']) ?></strong>
          <span class="text-sm text-muted"><?= $p['idade'] ?> anos · <?= $p['sexo'] === 'M' ? 'M' : 'F' ?> · CPF <?= htmlspecialchars($p['cpf']) ?></span>
        </div>
        <a href="/timeline/paciente/<?= $p['id'] ?>" class="btn btn-primary btn-sm ml-auto">
          <i class="fa-solid fa-timeline"></i> Ver Timeline
        </a>
      </div>
      <div class="timeline-mini-bar">
        <span class="timeline-mini-label"><?= $p['total_exames'] ?> exames · Último: <?= date('d/m/Y', strtotime($p['ultimo_exame'])) ?></span>
        <div class="timeline-mini-dots">
          <?php for ($i = 0; $i < min($p['total_exames'], 8); $i++): ?>
            <div class="mini-dot <?= $i === $p['total_exames']-1 ? 'mini-dot--atual' : '' ?>"></div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

</div>

<style>
.timeline-cards { display:flex; flex-direction:column; gap:12px; }
.timeline-patient-card { padding:20px; }
.timeline-patient-header { display:flex; align-items:center; gap:16px; margin-bottom:16px; }
.avatar-lg { width:48px; height:48px; border-radius:50%; background:linear-gradient(135deg,#1a56db,#0d2244); color:#fff; display:flex; align-items:center; justify-content:center; font-size:16px; font-weight:700; flex-shrink:0; text-transform:uppercase; }
.timeline-patient-header > div:nth-child(2) { display:flex; flex-direction:column; gap:2px; flex:1; }
.ml-auto { margin-left:auto; }
.timeline-mini-bar { display:flex; align-items:center; gap:16px; }
.timeline-mini-label { font-size:12px; color:#94a3b8; white-space:nowrap; }
.timeline-mini-dots { display:flex; gap:6px; align-items:center; }
.mini-dot { width:12px; height:12px; border-radius:50%; background:#bfdbfe; }
.mini-dot--atual { background:#1a56db; width:16px; height:16px; box-shadow:0 0 0 3px rgba(26,86,219,.2); }
</style>

<?php $this->layout('layout/copilot_footer'); ?>
