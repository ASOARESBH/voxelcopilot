<?php $this->layout('layout/copilot_header', ['title' => $title ?? 'Templates', 'pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]); ?>

<div class="page-content">

  <?php if (isset($_GET['sucesso'])): ?>
  <div class="alert alert-success mb-4">
    <i class="fa-solid fa-check-circle"></i>
    <?= $_GET['sucesso'] === 'criado' ? 'Template criado com sucesso!' : ($_GET['sucesso'] === 'atualizado' ? 'Template atualizado!' : 'Template removido.') ?>
  </div>
  <?php endif; ?>

  <!-- Filtros -->
  <div class="card mb-4">
    <form method="GET" action="/templates" class="d-flex gap-3 align-items-end flex-wrap">
      <div style="flex:1;min-width:200px">
        <label class="form-label-sm">Buscar</label>
        <div class="input-icon-wrap">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Nome do template..." class="form-control">
        </div>
      </div>
      <div>
        <label class="form-label-sm">Modalidade</label>
        <select name="modalidade" class="form-control">
          <option value="todas" <?= $modalidade==='todas'?'selected':'' ?>>Todas</option>
          <?php foreach(['TC','RM','RX','US','PET','MG','NM','DO','ECO','DX'] as $m): ?>
          <option value="<?= $m ?>" <?= $modalidade===$m?'selected':'' ?>><?= $m ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-outline"><i class="fa-solid fa-filter"></i> Filtrar</button>
      <a href="/templates/novo" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Novo Template</a>
    </form>
  </div>

  <!-- Grid de Templates -->
  <?php if (empty($templates)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-file-circle-plus"></i>
      <h3>Nenhum template encontrado</h3>
      <p>Crie seu primeiro template de laudo personalizado.</p>
      <a href="/templates/novo" class="btn btn-primary mt-3">Criar Template</a>
    </div>
  <?php else: ?>
  <div class="templates-grid">
    <?php foreach ($templates as $t): ?>
    <?php $estrutura = json_decode(is_array($t) ? ($t['estrutura_json'] ?? '{}') : ($t->estrutura_json ?? '{}'), true); ?>
    <div class="template-card">
      <div class="template-card-header">
        <span class="template-modalidade"><?= htmlspecialchars(is_array($t) ? $t['modalidade'] : $t->modalidade) ?></span>
        <span class="template-uso"><i class="fa-solid fa-chart-bar"></i> <?= is_array($t) ? $t['uso_count'] : $t->uso_count ?> usos</span>
      </div>
      <h4 class="template-nome"><?= htmlspecialchars(is_array($t) ? $t['nome'] : $t->nome) ?></h4>
      <div class="template-secoes">
        <?php foreach (array_keys($estrutura) as $secao): ?>
          <span class="template-secao-tag"><?= ucfirst($secao) ?></span>
        <?php endforeach; ?>
      </div>
      <div class="template-actions">
        <a href="/templates/<?= is_array($t) ? $t['id'] : $t->id ?>/editar" class="btn btn-sm btn-outline">
          <i class="fa-solid fa-pen"></i> Editar
        </a>
        <a href="/workspace/novo?template=<?= is_array($t) ? $t['id'] : $t->id ?>" class="btn btn-sm btn-primary">
          <i class="fa-solid fa-play"></i> Usar
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>

<style>
.templates-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; }
.template-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; display:flex; flex-direction:column; gap:12px; transition:box-shadow .2s; }
.template-card:hover { box-shadow:0 4px 16px rgba(26,86,219,.1); border-color:#bfdbfe; }
.template-card-header { display:flex; align-items:center; justify-content:space-between; }
.template-modalidade { background:#eff6ff; color:#1a56db; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:700; }
.template-uso { font-size:11px; color:#94a3b8; display:flex; align-items:center; gap:4px; }
.template-nome { margin:0; font-size:15px; color:#1e293b; font-weight:600; line-height:1.3; }
.template-secoes { display:flex; gap:4px; flex-wrap:wrap; }
.template-secao-tag { background:#f1f5f9; color:#64748b; padding:2px 8px; border-radius:4px; font-size:10px; }
.template-actions { display:flex; gap:8px; margin-top:auto; }
</style>

<?php $this->layout('layout/copilot_footer'); ?>
