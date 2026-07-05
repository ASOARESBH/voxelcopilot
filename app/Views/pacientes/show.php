
<div class="page-content">

  <div class="page-actions mb-4">
    <a href="/pacientes" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
    <a href="/workspace/novo?paciente=<?= $paciente['id'] ?>" class="btn btn-primary"><i class="fa-solid fa-file-pen"></i> Novo Laudo</a>
  </div>

  <div class="grid-2-1">
    <!-- Dados do Paciente -->
    <div class="card">
      <div class="card-header-row">
        <h3 class="card-title"><i class="fa-solid fa-user"></i> Dados Pessoais</h3>
      </div>
      <div class="detail-grid">
        <div class="detail-item"><label>Nome</label><span><?= htmlspecialchars($paciente['nome']) ?></span></div>
        <div class="detail-item"><label>CPF</label><span class="font-mono"><?= htmlspecialchars($paciente['cpf']) ?></span></div>
        <div class="detail-item"><label>Idade</label><span><?= $paciente['idade'] ?> anos</span></div>
        <div class="detail-item"><label>Sexo</label><span><?= $paciente['sexo'] === 'M' ? 'Masculino' : 'Feminino' ?></span></div>
        <div class="detail-item"><label>Nascimento</label><span><?= date('d/m/Y', strtotime($paciente['nascimento'])) ?></span></div>
        <div class="detail-item"><label>Telefone</label><span><?= htmlspecialchars($paciente['telefone']) ?></span></div>
        <div class="detail-item"><label>E-mail</label><span><?= htmlspecialchars($paciente['email']) ?></span></div>
        <div class="detail-item"><label>Cidade/Estado</label><span><?= htmlspecialchars($paciente['cidade']) ?>/<?= htmlspecialchars($paciente['estado']) ?></span></div>
      </div>
    </div>

    <!-- Stats -->
    <div class="card">
      <div class="card-header-row">
        <h3 class="card-title"><i class="fa-solid fa-chart-bar"></i> Resumo</h3>
      </div>
      <div class="stats-mini">
        <div class="stat-mini-item">
          <span class="stat-mini-num"><?= $paciente['total_exames'] ?></span>
          <span class="stat-mini-label">Total de Exames</span>
        </div>
        <div class="stat-mini-item">
          <span class="stat-mini-num"><?= date('d/m/Y', strtotime($paciente['ultimo_exame'])) ?></span>
          <span class="stat-mini-label">Último Exame</span>
        </div>
      </div>
      <div class="mt-3">
        <a href="/timeline/paciente/<?= $paciente['id'] ?>" class="btn btn-outline w-full">
          <i class="fa-solid fa-timeline"></i> Ver Timeline Completa
        </a>
      </div>
    </div>
  </div>

  <!-- Timeline do Paciente -->
  <div class="card mt-4">
    <div class="card-header-row">
      <h3 class="card-title"><i class="fa-solid fa-timeline"></i> Histórico de Exames</h3>
    </div>
    <div class="timeline-horizontal">
      <?php foreach ($timeline as $item): ?>
      <div class="timeline-item <?= $item['status'] === 'aguardando' ? 'timeline-item--atual' : '' ?>">
        <div class="timeline-dot <?= $item['status'] === 'aguardando' ? 'dot-atual' : 'dot-laudado' ?>">
          <?= $item['modalidade'] ?>
        </div>
        <div class="timeline-info">
          <span class="timeline-ano"><?= $item['ano'] ?></span>
          <span class="timeline-desc"><?= htmlspecialchars($item['descricao']) ?></span>
          <span class="timeline-inst"><?= htmlspecialchars($item['instituicao'] ?? '') ?></span>
          <?php if ($item['status'] === 'aguardando'): ?>
            <a href="/workspace/novo?accession=<?= urlencode($item['accession']) ?>" class="btn btn-primary btn-xs mt-1">Laudar</a>
          <?php else: ?>
            <a href="/workspace/laudo/<?= urlencode($item['accession']) ?>" class="btn btn-outline btn-xs mt-1">Ver laudo</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<style>
.grid-2-1 { display:grid; grid-template-columns:2fr 1fr; gap:24px; }
.detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.detail-item label { display:block; font-size:11px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:2px; }
.detail-item span { font-size:14px; color:#1e293b; }
.stats-mini { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.stat-mini-num { display:block; font-size:24px; font-weight:700; color:#1a56db; }
.stat-mini-label { display:block; font-size:11px; color:#94a3b8; text-transform:uppercase; }
.timeline-horizontal { display:flex; gap:0; overflow-x:auto; padding:24px 0; position:relative; }
.timeline-horizontal::before { content:''; position:absolute; top:44px; left:40px; right:40px; height:2px; background:#e2e8f0; z-index:0; }
.timeline-item { display:flex; flex-direction:column; align-items:center; gap:8px; min-width:120px; position:relative; z-index:1; }
.timeline-dot { width:56px; height:56px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; border:3px solid #fff; box-shadow:0 0 0 2px #e2e8f0; }
.dot-laudado { background:#eff6ff; color:#1a56db; }
.dot-atual { background:#1a56db; color:#fff; box-shadow:0 0 0 4px rgba(26,86,219,.2); }
.timeline-info { text-align:center; display:flex; flex-direction:column; gap:2px; }
.timeline-ano { font-size:13px; font-weight:700; color:#1e293b; }
.timeline-desc { font-size:11px; color:#64748b; }
.timeline-inst { font-size:10px; color:#94a3b8; }
.btn-xs { padding:3px 10px; font-size:11px; }
.w-full { width:100%; justify-content:center; }
@media(max-width:768px) { .grid-2-1 { grid-template-columns:1fr; } .detail-grid { grid-template-columns:1fr; } }
</style>

