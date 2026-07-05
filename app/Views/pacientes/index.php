<?php $this->layout('layout/copilot_header', ['title' => $title ?? 'Pacientes', 'pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]); ?>

<div class="page-content">

  <!-- Busca -->
  <div class="card mb-4">
    <form method="GET" action="/pacientes" class="d-flex gap-3 align-items-end">
      <div style="flex:1">
        <label class="form-label-sm">Buscar paciente</label>
        <div class="input-icon-wrap">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Nome, CPF ou accession..." class="form-control">
        </div>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
      <?php if ($busca): ?><a href="/pacientes" class="btn btn-outline">Limpar</a><?php endif; ?>
    </form>
  </div>

  <!-- Tabela de Pacientes -->
  <div class="card">
    <div class="card-header-row">
      <h3 class="card-title"><i class="fa-solid fa-users"></i> <?= $total ?> paciente<?= $total !== 1 ? 's' : '' ?></h3>
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Paciente</th>
            <th>CPF</th>
            <th>Idade / Sexo</th>
            <th>Cidade / Estado</th>
            <th>Exames</th>
            <th>Último Exame</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pacientes)): ?>
          <tr><td colspan="7" class="text-center text-muted py-5">Nenhum paciente encontrado.</td></tr>
          <?php else: ?>
          <?php foreach ($pacientes as $p): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="avatar-sm"><?= mb_substr($p['nome'], 0, 2) ?></div>
                <div>
                  <strong><?= htmlspecialchars($p['nome']) ?></strong>
                  <div class="text-xs text-muted"><?= htmlspecialchars($p['accession']) ?></div>
                </div>
              </div>
            </td>
            <td class="font-mono text-sm"><?= htmlspecialchars($p['cpf']) ?></td>
            <td><?= $p['idade'] ?> anos · <?= $p['sexo'] === 'M' ? 'Masculino' : 'Feminino' ?></td>
            <td><?= htmlspecialchars($p['cidade']) ?>/<?= htmlspecialchars($p['estado']) ?></td>
            <td><span class="badge-count"><?= $p['total_exames'] ?></span></td>
            <td class="text-sm text-muted"><?= date('d/m/Y', strtotime($p['ultimo_exame'])) ?></td>
            <td>
              <div class="action-btns">
                <a href="/pacientes/<?= $p['id'] ?>" class="btn btn-sm btn-outline" title="Ver histórico">
                  <i class="fa-solid fa-timeline"></i> Histórico
                </a>
                <a href="/workspace/novo?paciente=<?= $p['id'] ?>" class="btn btn-sm btn-primary" title="Novo laudo">
                  <i class="fa-solid fa-file-pen"></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Paginação -->
    <?php if ($totalPaginas > 1): ?>
    <div class="pagination-row">
      <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
        <a href="?pagina=<?= $i ?><?= $busca ? '&busca='.urlencode($busca) : '' ?>"
           class="page-btn <?= $i === $pagina ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>

</div>

<style>
.avatar-sm { width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#1a56db,#0d2244); color:#fff; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; flex-shrink:0; text-transform:uppercase; }
.badge-count { background:#eff6ff; color:#1a56db; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
.action-btns { display:flex; gap:6px; }
.pagination-row { display:flex; gap:4px; justify-content:center; padding:16px; }
.page-btn { width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:8px; border:1px solid #e2e8f0; color:#475569; font-size:13px; text-decoration:none; transition:all .2s; }
.page-btn:hover, .page-btn.active { background:#1a56db; color:#fff; border-color:#1a56db; }
</style>

<?php $this->layout('layout/copilot_footer'); ?>
