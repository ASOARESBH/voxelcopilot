
<div class="page-content">

  <!-- Stats da Fila -->
  <div class="fila-stats-row">
    <div class="fila-stat-card urgente">
      <div class="fila-stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <div class="fila-stat-info">
        <span class="fila-stat-num"><?= $stats['urgentes'] ?></span>
        <span class="fila-stat-label">Urgentes</span>
      </div>
    </div>
    <div class="fila-stat-card normal">
      <div class="fila-stat-icon"><i class="fa-solid fa-clock"></i></div>
      <div class="fila-stat-info">
        <span class="fila-stat-num"><?= $stats['normais'] ?></span>
        <span class="fila-stat-label">Normais</span>
      </div>
    </div>
    <div class="fila-stat-card oncologico">
      <div class="fila-stat-icon"><i class="fa-solid fa-ribbon"></i></div>
      <div class="fila-stat-info">
        <span class="fila-stat-num"><?= $stats['oncologicos'] ?></span>
        <span class="fila-stat-label">Oncológicos</span>
      </div>
    </div>
    <div class="fila-stat-card total">
      <div class="fila-stat-icon"><i class="fa-solid fa-layer-group"></i></div>
      <div class="fila-stat-info">
        <span class="fila-stat-num"><?= $stats['total'] ?></span>
        <span class="fila-stat-label">Total na fila</span>
      </div>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card mb-4">
    <form method="GET" action="/fila" class="fila-filtros">
      <div class="fila-filtro-group">
        <label>Buscar</label>
        <div class="input-icon-wrap">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Paciente ou accession..." class="form-control">
        </div>
      </div>
      <div class="fila-filtro-group">
        <label>Modalidade</label>
        <select name="modalidade" class="form-control">
          <option value="todas" <?= $filtroModalidade==='todas'?'selected':'' ?>>Todas</option>
          <?php foreach(['TC','RM','RX','US','PET','MG','NM'] as $m): ?>
          <option value="<?= $m ?>" <?= $filtroModalidade===$m?'selected':'' ?>><?= $m ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="fila-filtro-group">
        <label>Prioridade</label>
        <select name="prioridade" class="form-control">
          <option value="todas" <?= $filtroPrioridade==='todas'?'selected':'' ?>>Todas</option>
          <option value="urgente" <?= $filtroPrioridade==='urgente'?'selected':'' ?>>Urgente</option>
          <option value="normal" <?= $filtroPrioridade==='normal'?'selected':'' ?>>Normal</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filtrar</button>
      <?php if ($busca || $filtroModalidade!=='todas' || $filtroPrioridade!=='todas'): ?>
        <a href="/fila" class="btn btn-outline">Limpar</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Lista de Exames -->
  <?php if (empty($fila)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-inbox"></i>
      <h3>Fila vazia</h3>
      <p>Nenhum exame encontrado com os filtros aplicados.</p>
    </div>
  <?php else: ?>
    <div class="fila-lista">
      <?php foreach ($fila as $exame): ?>
      <div class="fila-card <?= $exame['prioridade'] === 'urgente' ? 'fila-card--urgente' : '' ?>">
        <div class="fila-card-prioridade">
          <?php if ($exame['prioridade'] === 'urgente'): ?>
            <span class="badge badge-urgente"><i class="fa-solid fa-triangle-exclamation"></i> Urgente</span>
          <?php else: ?>
            <span class="badge badge-normal">Normal</span>
          <?php endif; ?>
          <span class="fila-modalidade-badge"><?= htmlspecialchars($exame['modalidade']) ?></span>
        </div>

        <div class="fila-card-body">
          <div class="fila-card-paciente">
            <div class="avatar-circle"><?= mb_substr($exame['paciente'], 0, 2) ?></div>
            <div>
              <strong><?= htmlspecialchars($exame['paciente']) ?></strong>
              <span class="fila-accession"><?= htmlspecialchars($exame['accession']) ?></span>
            </div>
          </div>
          <div class="fila-card-descricao"><?= htmlspecialchars($exame['descricao']) ?></div>

          <?php if (!empty($exame['tags'])): ?>
          <div class="fila-tags">
            <?php foreach ($exame['tags'] as $tag): ?>
              <span class="tag"><?= htmlspecialchars($tag) ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <?php if ($exame['ia_sugestao']): ?>
          <div class="fila-ia-hint">
            <i class="fa-solid fa-robot"></i>
            <?= htmlspecialchars($exame['ia_sugestao']) ?>
          </div>
          <?php endif; ?>
        </div>

        <div class="fila-card-meta">
          <div class="fila-tempo">
            <?php if ($exame['porta_laudo']): ?>
              <span class="porta-laudo-alert"><i class="fa-solid fa-circle-exclamation"></i> <?= $exame['porta_laudo'] ?> porta-laudo</span>
            <?php else: ?>
              <span class="tempo-espera"><i class="fa-regular fa-clock"></i> Aguardando <?= $exame['tempo_espera'] ?></span>
            <?php endif; ?>
          </div>
          <a href="/workspace/<?= $exame['id'] ?>" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-play"></i> Abrir Workspace
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

<style>
.fila-stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
.fila-stat-card { display:flex; align-items:center; gap:16px; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; }
.fila-stat-card.urgente .fila-stat-icon { background:#fef2f2; color:#dc2626; }
.fila-stat-card.normal .fila-stat-icon { background:#eff6ff; color:#1a56db; }
.fila-stat-card.oncologico .fila-stat-icon { background:#fdf4ff; color:#9333ea; }
.fila-stat-card.total .fila-stat-icon { background:#f0fdf4; color:#16a34a; }
.fila-stat-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
.fila-stat-num { font-size:28px; font-weight:700; color:#1e293b; display:block; line-height:1; }
.fila-stat-label { font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:.5px; }
.fila-filtros { display:flex; align-items:flex-end; gap:16px; flex-wrap:wrap; }
.fila-filtro-group { display:flex; flex-direction:column; gap:4px; }
.fila-filtro-group label { font-size:12px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:.5px; }
.fila-lista { display:flex; flex-direction:column; gap:12px; }
.fila-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; display:grid; grid-template-columns:140px 1fr auto; gap:20px; align-items:center; transition:box-shadow .2s; }
.fila-card:hover { box-shadow:0 4px 16px rgba(26,86,219,.1); }
.fila-card--urgente { border-left:4px solid #dc2626; }
.fila-card-prioridade { display:flex; flex-direction:column; gap:8px; }
.badge-urgente { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; display:inline-flex; align-items:center; gap:4px; }
.badge-normal { background:#eff6ff; color:#1a56db; border:1px solid #bfdbfe; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; }
.fila-modalidade-badge { background:#f1f5f9; color:#475569; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:700; text-align:center; }
.fila-card-paciente { display:flex; align-items:center; gap:12px; margin-bottom:8px; }
.avatar-circle { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#1a56db,#0d2244); color:#fff; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; flex-shrink:0; text-transform:uppercase; }
.fila-accession { display:block; font-size:11px; color:#94a3b8; font-family:monospace; }
.fila-card-descricao { font-size:13px; color:#475569; margin-bottom:8px; }
.fila-tags { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:8px; }
.tag { background:#f1f5f9; color:#475569; padding:3px 8px; border-radius:4px; font-size:11px; }
.fila-ia-hint { background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:8px 12px; font-size:12px; color:#1a56db; display:flex; align-items:flex-start; gap:6px; }
.fila-card-meta { display:flex; flex-direction:column; align-items:flex-end; gap:12px; }
.porta-laudo-alert { color:#dc2626; font-size:12px; font-weight:600; display:flex; align-items:center; gap:4px; }
.tempo-espera { color:#64748b; font-size:12px; display:flex; align-items:center; gap:4px; }
.empty-state { text-align:center; padding:80px 20px; color:#94a3b8; }
.empty-state i { font-size:48px; margin-bottom:16px; display:block; }
.empty-state h3 { color:#475569; margin-bottom:8px; }
@media(max-width:768px) {
  .fila-stats-row { grid-template-columns:repeat(2,1fr); }
  .fila-card { grid-template-columns:1fr; }
  .fila-card-meta { align-items:flex-start; }
}
</style>

