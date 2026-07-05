
<div class="page-content">
  <div class="comparativos-lista">
    <?php foreach ($comparativos as $c): ?>
    <div class="card comp-card">
      <div class="comp-header">
        <div class="comp-modalidade"><?= htmlspecialchars($c['modalidade']) ?></div>
        <div class="comp-paciente">
          <strong><?= htmlspecialchars($c['paciente']) ?></strong>
        </div>
        <span class="comp-status <?= $c['status'] === 'concluido' ? 'status-ok' : 'status-pendente' ?>">
          <?= $c['status'] === 'concluido' ? 'Concluído' : 'Pendente' ?>
        </span>
      </div>
      <div class="comp-exames">
        <div class="comp-exame">
          <span class="comp-exame-label">Atual</span>
          <span class="comp-exame-desc"><?= htmlspecialchars($c['descricao_atual']) ?></span>
          <span class="comp-exame-data"><?= date('d/m/Y', strtotime($c['data_atual'])) ?></span>
        </div>
        <div class="comp-vs"><i class="fa-solid fa-arrows-left-right"></i></div>
        <div class="comp-exame">
          <span class="comp-exame-label">Anterior</span>
          <span class="comp-exame-desc"><?= htmlspecialchars($c['descricao_anterior']) ?></span>
          <span class="comp-exame-data"><?= date('d/m/Y', strtotime($c['data_anterior'])) ?></span>
        </div>
      </div>
      <?php if ($c['ia_delta']): ?>
      <div class="comp-ia-delta">
        <i class="fa-solid fa-robot"></i>
        <span><?= htmlspecialchars($c['ia_delta']) ?></span>
      </div>
      <?php endif; ?>
      <div class="comp-actions">
        <a href="/comparativos/<?= $c['id'] ?>" class="btn btn-primary btn-sm">
          <i class="fa-solid fa-magnifying-glass-chart"></i> Analisar Comparativo
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<style>
.comparativos-lista { display:flex; flex-direction:column; gap:16px; }
.comp-card { padding:20px; }
.comp-header { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
.comp-modalidade { background:#eff6ff; color:#1a56db; padding:4px 12px; border-radius:6px; font-size:12px; font-weight:700; }
.comp-paciente { flex:1; }
.status-ok { background:#f0fdf4; color:#16a34a; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; }
.status-pendente { background:#fefce8; color:#ca8a04; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; }
.comp-exames { display:flex; align-items:center; gap:16px; margin-bottom:12px; }
.comp-exame { flex:1; background:#f8fafc; border-radius:8px; padding:12px; }
.comp-exame-label { display:block; font-size:10px; font-weight:600; color:#94a3b8; text-transform:uppercase; margin-bottom:4px; }
.comp-exame-desc { display:block; font-size:13px; color:#1e293b; font-weight:500; }
.comp-exame-data { display:block; font-size:11px; color:#94a3b8; }
.comp-vs { color:#94a3b8; font-size:18px; flex-shrink:0; }
.comp-ia-delta { background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:10px 14px; font-size:12px; color:#1a56db; display:flex; align-items:center; gap:8px; margin-bottom:12px; }
.comp-actions { display:flex; justify-content:flex-end; }
</style>

