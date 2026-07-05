<?php $this->layout('layout/copilot_header', ['title' => $title ?? 'Vision AI', 'pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]); ?>

<div class="page-content">
  <div class="vision-intro card mb-4">
    <div class="vision-intro-content">
      <div class="vision-intro-icon"><i class="fa-solid fa-eye"></i></div>
      <div>
        <h3>Análise Automática de Imagens DICOM</h3>
        <p>O Vision AI analisa imagens diretamente do PACS e sugere achados, classificações e diagnósticos diferenciais com base em modelos treinados em diagnóstico por imagem.</p>
      </div>
    </div>
    <button onclick="document.getElementById('modalNovaAnalise').style.display='flex'" class="btn btn-primary">
      <i class="fa-solid fa-plus"></i> Nova Análise
    </button>
  </div>

  <div class="card">
    <div class="card-header-row">
      <h3 class="card-title"><i class="fa-solid fa-list"></i> Análises Recentes</h3>
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr><th>Paciente</th><th>Modalidade</th><th>Exame</th><th>Data</th><th>Achados IA</th><th>Confiança</th><th>Status</th><th>Ação</th></tr>
        </thead>
        <tbody>
          <?php foreach ($analises as $a): ?>
          <tr>
            <td><strong><?= htmlspecialchars($a['paciente']) ?></strong></td>
            <td><span class="badge-mod"><?= htmlspecialchars($a['modalidade']) ?></span></td>
            <td><?= htmlspecialchars($a['descricao']) ?></td>
            <td><?= date('d/m/Y', strtotime($a['data'])) ?></td>
            <td>
              <?php if ($a['status'] === 'processando'): ?>
                <span class="text-muted text-sm">Processando...</span>
              <?php else: ?>
                <ul class="achados-list">
                  <?php foreach ($a['achados_ia'] as $achado): ?>
                    <li><?= htmlspecialchars($achado) ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($a['confianca'] > 0): ?>
                <div class="confianca-bar">
                  <div class="confianca-fill" style="width:<?= $a['confianca'] ?>%"></div>
                </div>
                <span class="text-sm font-bold text-blue"><?= $a['confianca'] ?>%</span>
              <?php else: ?>
                <span class="text-muted text-sm">—</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="status-badge <?= $a['status'] === 'concluido' ? 'status-ok' : 'status-processing' ?>">
                <?= $a['status'] === 'concluido' ? 'Concluído' : 'Processando' ?>
              </span>
            </td>
            <td>
              <a href="/workspace/<?= $a['id'] ?>" class="btn btn-sm btn-primary">
                <i class="fa-solid fa-file-pen"></i> Laudar
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Nova Análise -->
<div id="modalNovaAnalise" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
  <div class="modal-box">
    <div class="modal-header">
      <h3><i class="fa-solid fa-eye"></i> Nova Análise Vision AI</h3>
      <button onclick="document.getElementById('modalNovaAnalise').style.display='none'" class="modal-close">&times;</button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label>Study Instance UID ou Accession Number</label>
        <input type="text" id="visionStudyUid" class="form-control" placeholder="1.2.840.10008.5.1.4.1.1.2.001">
      </div>
      <div class="form-group">
        <label>Modalidade</label>
        <select id="visionModalidade" class="form-control">
          <option value="TC">TC — Tomografia Computadorizada</option>
          <option value="RM">RM — Ressonância Magnética</option>
          <option value="RX">RX — Radiografia</option>
          <option value="MG">MG — Mamografia</option>
          <option value="PET">PET — PET-CT</option>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button onclick="document.getElementById('modalNovaAnalise').style.display='none'" class="btn btn-outline">Cancelar</button>
      <button onclick="iniciarAnalise()" class="btn btn-primary"><i class="fa-solid fa-play"></i> Iniciar Análise</button>
    </div>
  </div>
</div>

<style>
.vision-intro { padding:24px; display:flex; align-items:center; gap:24px; }
.vision-intro-content { display:flex; align-items:flex-start; gap:16px; flex:1; }
.vision-intro-icon { width:56px; height:56px; background:linear-gradient(135deg,#1a56db,#0d2244); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:24px; color:#fff; flex-shrink:0; }
.vision-intro h3 { margin:0 0 4px; color:#1e293b; }
.vision-intro p { margin:0; font-size:13px; color:#64748b; }
.achados-list { margin:0; padding-left:16px; font-size:12px; color:#475569; }
.confianca-bar { width:80px; height:6px; background:#e2e8f0; border-radius:3px; overflow:hidden; margin-bottom:2px; }
.confianca-fill { height:100%; background:linear-gradient(90deg,#1a56db,#06b6d4); border-radius:3px; }
.status-processing { background:#fefce8; color:#ca8a04; padding:3px 8px; border-radius:20px; font-size:11px; font-weight:600; }
.modal-box { background:#fff; border-radius:16px; width:480px; max-width:90vw; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.3); }
.modal-header { padding:20px 24px; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between; }
.modal-header h3 { margin:0; font-size:16px; color:#1e293b; }
.modal-close { background:none; border:none; font-size:24px; color:#94a3b8; cursor:pointer; line-height:1; }
.modal-body { padding:24px; display:flex; flex-direction:column; gap:16px; }
.modal-footer { padding:16px 24px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:8px; }
</style>

<script>
async function iniciarAnalise() {
  const uid = document.getElementById('visionStudyUid').value.trim();
  const mod = document.getElementById('visionModalidade').value;
  if (!uid) { alert('Informe o Study UID ou Accession Number'); return; }
  document.getElementById('modalNovaAnalise').style.display = 'none';
  const res = await fetch('/api/vision/analisar', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({study_uid: uid, modalidade: mod})
  });
  const data = await res.json();
  alert(data.ok ? 'Análise iniciada! Atualize a página em instantes.' : 'Erro: ' + data.error);
}
</script>

<?php $this->layout('layout/copilot_footer'); ?>
