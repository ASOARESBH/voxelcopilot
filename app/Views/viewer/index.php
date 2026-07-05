<?php $this->layout('layout/copilot_header', ['title' => $title ?? 'Viewer DICOM', 'pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]); ?>

<div class="page-content">

  <?php if (!$viewerUrl): ?>
  <!-- Seleção de Estudo -->
  <div class="card mb-4">
    <div class="card-header-row">
      <h3 class="card-title"><i class="fa-solid fa-magnifying-glass"></i> Abrir Estudo no Viewer</h3>
    </div>
    <div class="viewer-search">
      <div class="input-icon-wrap" style="flex:1">
        <i class="fa-solid fa-fingerprint"></i>
        <input type="text" id="studyUidInput" placeholder="Cole o Study Instance UID ou Accession Number..." class="form-control">
      </div>
      <button onclick="abrirViewer()" class="btn btn-primary">
        <i class="fa-solid fa-play"></i> Abrir Viewer
      </button>
    </div>
  </div>

  <!-- Estudos Recentes -->
  <div class="card">
    <div class="card-header-row">
      <h3 class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Estudos Recentes</h3>
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr><th>Paciente</th><th>Modalidade</th><th>Descrição</th><th>Data</th><th>Accession</th><th>Ação</th></tr>
        </thead>
        <tbody>
          <?php foreach ($estudosRecentes as $e): ?>
          <tr>
            <td><strong><?= htmlspecialchars($e['paciente']) ?></strong></td>
            <td><span class="badge-mod"><?= htmlspecialchars($e['modalidade']) ?></span></td>
            <td><?= htmlspecialchars($e['descricao']) ?></td>
            <td><?= date('d/m/Y', strtotime($e['data'])) ?></td>
            <td class="font-mono text-sm"><?= htmlspecialchars($e['accession']) ?></td>
            <td>
              <a href="/viewer?study=<?= urlencode($e['study_uid']) ?>" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-eye"></i> Abrir
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php else: ?>
  <!-- Viewer Embutido -->
  <div class="viewer-frame-container">
    <div class="viewer-toolbar">
      <a href="/viewer" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
      <span class="viewer-study-uid"><i class="fa-solid fa-fingerprint"></i> <?= htmlspecialchars($studyUid) ?></span>
      <a href="<?= htmlspecialchars($viewerUrl) ?>" target="_blank" class="btn btn-outline btn-sm">
        <i class="fa-solid fa-external-link"></i> Abrir em nova aba
      </a>
    </div>
    <iframe src="<?= htmlspecialchars($viewerUrl) ?>" class="viewer-iframe" allowfullscreen></iframe>
  </div>
  <?php endif; ?>

</div>

<style>
.viewer-search { display:flex; gap:12px; align-items:center; }
.badge-mod { background:#eff6ff; color:#1a56db; padding:3px 8px; border-radius:4px; font-size:11px; font-weight:700; }
.viewer-frame-container { background:#000; border-radius:12px; overflow:hidden; }
.viewer-toolbar { background:#0d2244; padding:12px 20px; display:flex; align-items:center; gap:16px; }
.viewer-study-uid { color:#94a3b8; font-size:12px; font-family:monospace; flex:1; }
.viewer-iframe { width:100%; height:calc(100vh - 220px); border:none; display:block; }
</style>

<script>
function abrirViewer() {
  const uid = document.getElementById('studyUidInput').value.trim();
  if (uid) window.location.href = '/viewer?study=' + encodeURIComponent(uid);
}
document.getElementById('studyUidInput')?.addEventListener('keydown', e => { if (e.key === 'Enter') abrirViewer(); });
</script>

<?php $this->layout('layout/copilot_footer'); ?>
