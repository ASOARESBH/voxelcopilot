<?php $this->layout('layout/copilot_header', ['title' => $title ?? 'Analytics', 'pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]); ?>

<div class="page-content">

  <!-- Filtro de Período -->
  <div class="card mb-4">
    <form method="GET" action="/analytics" class="d-flex gap-3 align-items-center">
      <label class="form-label-sm mb-0">Período:</label>
      <?php foreach (['7d'=>'7 dias','30d'=>'30 dias','90d'=>'90 dias','1y'=>'1 ano'] as $v=>$l): ?>
        <a href="?periodo=<?= $v ?>" class="btn btn-sm <?= $periodo===$v ? 'btn-primary' : 'btn-outline' ?>"><?= $l ?></a>
      <?php endforeach; ?>
    </form>
  </div>

  <!-- KPIs -->
  <div class="kpi-grid mb-4">
    <div class="kpi-card">
      <div class="kpi-icon blue"><i class="fa-solid fa-file-medical"></i></div>
      <div class="kpi-info"><span class="kpi-num"><?= number_format($stats['total_laudos']) ?></span><span class="kpi-label">Laudos emitidos</span></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon green"><i class="fa-solid fa-clock"></i></div>
      <div class="kpi-info"><span class="kpi-num"><?= $stats['tempo_medio'] ?></span><span class="kpi-label">Tempo médio/laudo</span></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon purple"><i class="fa-solid fa-gauge-high"></i></div>
      <div class="kpi-info"><span class="kpi-num"><?= $stats['produtividade_hora'] ?></span><span class="kpi-label">Laudos/hora</span></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon orange"><i class="fa-solid fa-rotate"></i></div>
      <div class="kpi-info"><span class="kpi-num"><?= $stats['taxa_revisao'] ?></span><span class="kpi-label">Taxa de revisão</span></div>
    </div>
  </div>

  <div class="analytics-grid">
    <!-- Gráfico de Laudos por Dia -->
    <div class="card">
      <div class="card-header-row"><h3 class="card-title"><i class="fa-solid fa-chart-line"></i> Laudos por Dia</h3></div>
      <canvas id="chartLaudosDia" height="200"></canvas>
    </div>

    <!-- Distribuição por Modalidade -->
    <div class="card">
      <div class="card-header-row"><h3 class="card-title"><i class="fa-solid fa-chart-pie"></i> Por Modalidade</h3></div>
      <div class="modalidade-list">
        <?php $totalMod = array_sum(array_column($stats['por_modalidade'], 'total')); ?>
        <?php foreach ($stats['por_modalidade'] as $m): ?>
        <?php $pct = $totalMod > 0 ? round($m['total']/$totalMod*100) : 0; ?>
        <div class="mod-row">
          <span class="mod-name"><?= $m['modalidade'] ?></span>
          <div class="mod-bar-wrap">
            <div class="mod-bar" style="width:<?= $pct ?>%;background:<?= $m['cor'] ?>"></div>
          </div>
          <span class="mod-num"><?= $m['total'] ?></span>
          <span class="mod-pct"><?= $pct ?>%</span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Stats de IA -->
    <div class="card">
      <div class="card-header-row"><h3 class="card-title"><i class="fa-solid fa-robot"></i> Uso do Copilot IA</h3></div>
      <div class="ia-usage-stats">
        <div class="ia-usage-item">
          <div class="ia-usage-bar">
            <?php $total_ia = $stats['ia_stats']['sugestoes_aceitas'] + $stats['ia_stats']['sugestoes_editadas'] + $stats['ia_stats']['sugestoes_rejeitadas']; ?>
            <div class="ia-bar-aceitas" style="width:<?= $total_ia > 0 ? round($stats['ia_stats']['sugestoes_aceitas']/$total_ia*100) : 0 ?>%"></div>
            <div class="ia-bar-editadas" style="width:<?= $total_ia > 0 ? round($stats['ia_stats']['sugestoes_editadas']/$total_ia*100) : 0 ?>%"></div>
            <div class="ia-bar-rejeitadas" style="width:<?= $total_ia > 0 ? round($stats['ia_stats']['sugestoes_rejeitadas']/$total_ia*100) : 0 ?>%"></div>
          </div>
          <div class="ia-bar-legend">
            <span class="legend-aceitas">Aceitas: <?= $stats['ia_stats']['sugestoes_aceitas'] ?></span>
            <span class="legend-editadas">Editadas: <?= $stats['ia_stats']['sugestoes_editadas'] ?></span>
            <span class="legend-rejeitadas">Rejeitadas: <?= $stats['ia_stats']['sugestoes_rejeitadas'] ?></span>
          </div>
        </div>
        <div class="ia-economia">
          <i class="fa-solid fa-clock"></i>
          Tempo economizado com IA: <strong><?= $stats['ia_stats']['tempo_economizado'] ?></strong>
        </div>
      </div>
    </div>
  </div>

</div>

<style>
.kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
.kpi-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px; }
.kpi-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
.kpi-icon.blue { background:#eff6ff; color:#1a56db; }
.kpi-icon.green { background:#f0fdf4; color:#16a34a; }
.kpi-icon.purple { background:#fdf4ff; color:#9333ea; }
.kpi-icon.orange { background:#fff7ed; color:#ea580c; }
.kpi-num { display:block; font-size:24px; font-weight:700; color:#1e293b; line-height:1; }
.kpi-label { display:block; font-size:11px; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-top:2px; }
.analytics-grid { display:grid; grid-template-columns:2fr 1fr 1fr; gap:24px; }
.modalidade-list { display:flex; flex-direction:column; gap:12px; padding:8px 0; }
.mod-row { display:grid; grid-template-columns:40px 1fr 40px 40px; align-items:center; gap:8px; }
.mod-name { font-size:12px; font-weight:700; color:#475569; }
.mod-bar-wrap { height:8px; background:#f1f5f9; border-radius:4px; overflow:hidden; }
.mod-bar { height:100%; border-radius:4px; transition:width .5s; }
.mod-num { font-size:12px; font-weight:600; color:#1e293b; text-align:right; }
.mod-pct { font-size:11px; color:#94a3b8; text-align:right; }
.ia-usage-bar { height:24px; border-radius:8px; overflow:hidden; display:flex; margin-bottom:8px; }
.ia-bar-aceitas { background:#16a34a; }
.ia-bar-editadas { background:#d97706; }
.ia-bar-rejeitadas { background:#dc2626; }
.ia-bar-legend { display:flex; gap:12px; flex-wrap:wrap; }
.legend-aceitas { font-size:11px; color:#16a34a; font-weight:600; }
.legend-editadas { font-size:11px; color:#d97706; font-weight:600; }
.legend-rejeitadas { font-size:11px; color:#dc2626; font-weight:600; }
.ia-economia { margin-top:16px; background:#f0fdf4; border-radius:8px; padding:12px; font-size:13px; color:#166534; display:flex; align-items:center; gap:8px; }
@media(max-width:1024px) { .kpi-grid { grid-template-columns:repeat(2,1fr); } .analytics-grid { grid-template-columns:1fr; } }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const dadosDia = <?= json_encode($stats['por_dia']) ?>;
new Chart(document.getElementById('chartLaudosDia'), {
  type: 'line',
  data: {
    labels: dadosDia.map(d => d.data),
    datasets: [{
      label: 'Laudos',
      data: dadosDia.map(d => d.laudos),
      borderColor: '#1a56db',
      backgroundColor: 'rgba(26,86,219,.08)',
      borderWidth: 2,
      fill: true,
      tension: 0.4,
      pointBackgroundColor: '#1a56db',
      pointRadius: 3,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { font: { size: 11 }, maxTicksLimit: 10 } },
      y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } }
    }
  }
});
</script>

<?php $this->layout('layout/copilot_footer'); ?>
