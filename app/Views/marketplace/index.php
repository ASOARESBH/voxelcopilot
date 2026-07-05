
<div class="page-content">
  <div class="marketplace-grid">
    <?php foreach ($plugins as $p): ?>
    <div class="plugin-card <?= $p['instalado'] ? 'plugin-card--instalado' : '' ?>">
      <div class="plugin-card-header">
        <div class="plugin-icon" style="background:<?= $p['cor'] ?>22;color:<?= $p['cor'] ?>">
          <i class="fa-solid <?= $p['icone'] ?>"></i>
        </div>
        <?php if ($p['instalado']): ?>
          <span class="plugin-badge-instalado"><i class="fa-solid fa-check"></i> Instalado</span>
        <?php endif; ?>
      </div>
      <h4 class="plugin-nome"><?= htmlspecialchars($p['nome']) ?></h4>
      <span class="plugin-categoria"><?= htmlspecialchars($p['categoria']) ?></span>
      <p class="plugin-descricao"><?= htmlspecialchars($p['descricao']) ?></p>
      <div class="plugin-footer">
        <span class="plugin-versao">v<?= $p['versao'] ?></span>
        <span class="plugin-preco"><?= $p['preco'] ?></span>
      </div>
      <div class="plugin-actions">
        <?php if ($p['instalado']): ?>
          <button class="btn btn-outline btn-sm w-full" disabled>Configurar</button>
        <?php else: ?>
          <button class="btn btn-primary btn-sm w-full">
            <i class="fa-solid fa-download"></i> Instalar
          </button>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<style>
.marketplace-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:20px; }
.plugin-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; display:flex; flex-direction:column; gap:10px; transition:all .2s; }
.plugin-card:hover { box-shadow:0 8px 24px rgba(26,86,219,.1); transform:translateY(-2px); }
.plugin-card--instalado { border-color:#bfdbfe; }
.plugin-card-header { display:flex; align-items:center; justify-content:space-between; }
.plugin-icon { width:52px; height:52px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; }
.plugin-badge-instalado { background:#f0fdf4; color:#16a34a; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; display:flex; align-items:center; gap:4px; }
.plugin-nome { margin:0; font-size:16px; font-weight:700; color:#1e293b; }
.plugin-categoria { font-size:11px; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; }
.plugin-descricao { font-size:13px; color:#64748b; line-height:1.5; margin:0; flex:1; }
.plugin-footer { display:flex; align-items:center; justify-content:space-between; }
.plugin-versao { font-size:11px; color:#94a3b8; font-family:monospace; }
.plugin-preco { font-size:13px; font-weight:700; color:#1a56db; }
.plugin-actions { margin-top:4px; }
</style>

