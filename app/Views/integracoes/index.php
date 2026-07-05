<?php $this->layout('layout/copilot_header', ['title' => $title ?? 'Integrações', 'pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]); ?>

<div class="page-content">
  <div class="integracoes-lista">
    <?php foreach ($integracoes as $integ): ?>
    <div class="integ-card">
      <div class="integ-icon" style="background:<?= $integ['cor'] ?>22;color:<?= $integ['cor'] ?>">
        <i class="fa-solid <?= $integ['icone'] ?>"></i>
      </div>
      <div class="integ-info">
        <div class="integ-header">
          <strong><?= htmlspecialchars($integ['nome']) ?></strong>
          <span class="integ-tipo"><?= htmlspecialchars($integ['tipo']) ?></span>
          <span class="integ-status <?= $integ['status'] === 'conectado' ? 'status-ok' : 'status-off' ?>">
            <i class="fa-solid <?= $integ['status'] === 'conectado' ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
            <?= $integ['status'] === 'conectado' ? 'Conectado' : 'Desconectado' ?>
          </span>
        </div>
        <div class="integ-details">
          <span><i class="fa-solid fa-link"></i> <?= $integ['url'] ?: 'URL não configurada' ?></span>
          <span><i class="fa-solid fa-code-branch"></i> <?= htmlspecialchars($integ['protocolo']) ?></span>
          <?php if ($integ['ultima_sync']): ?>
            <span><i class="fa-regular fa-clock"></i> Última sync: <?= date('d/m/Y H:i', strtotime($integ['ultima_sync'])) ?></span>
          <?php endif; ?>
        </div>
      </div>
      <div class="integ-actions">
        <button onclick="testarConexao(<?= $integ['id'] ?>, '<?= $integ['tipo'] ?>', '<?= addslashes($integ['url']) ?>')" class="btn btn-sm btn-outline">
          <i class="fa-solid fa-plug"></i> Testar
        </button>
        <button class="btn btn-sm btn-outline"><i class="fa-solid fa-gear"></i> Configurar</button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<style>
.integracoes-lista { display:flex; flex-direction:column; gap:16px; }
.integ-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; display:flex; align-items:center; gap:20px; }
.integ-icon { width:52px; height:52px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; }
.integ-info { flex:1; display:flex; flex-direction:column; gap:8px; }
.integ-header { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.integ-tipo { background:#f1f5f9; color:#64748b; padding:2px 8px; border-radius:4px; font-size:11px; }
.status-ok { color:#16a34a; font-size:12px; font-weight:600; display:flex; align-items:center; gap:4px; }
.status-off { color:#dc2626; font-size:12px; font-weight:600; display:flex; align-items:center; gap:4px; }
.integ-details { display:flex; gap:16px; flex-wrap:wrap; }
.integ-details span { font-size:12px; color:#94a3b8; display:flex; align-items:center; gap:4px; }
.integ-actions { display:flex; gap:8px; flex-shrink:0; }
</style>

<script>
async function testarConexao(id, tipo, url) {
  const btn = event.target.closest('button');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Testando...';
  try {
    const res = await fetch('/api/integracoes/testar', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({id, tipo, url})
    });
    const data = await res.json();
    btn.innerHTML = data.ok ? '<i class="fa-solid fa-check"></i> OK' : '<i class="fa-solid fa-xmark"></i> Falhou';
    btn.style.color = data.ok ? '#16a34a' : '#dc2626';
    setTimeout(() => { btn.disabled=false; btn.innerHTML='<i class="fa-solid fa-plug"></i> Testar'; btn.style.color=''; }, 3000);
  } catch(e) {
    btn.innerHTML = '<i class="fa-solid fa-xmark"></i> Erro';
    setTimeout(() => { btn.disabled=false; btn.innerHTML='<i class="fa-solid fa-plug"></i> Testar'; }, 3000);
  }
}
</script>

<?php $this->layout('layout/copilot_footer'); ?>
