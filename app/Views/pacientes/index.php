
<!-- ── Cabeçalho da página ─────────────────────────────────────── -->
<div class="page-header">
  <div class="page-header-left">
    <h1><i class="fa-solid fa-users" style="color:var(--royal);margin-right:8px;font-size:1.1rem;"></i>Pacientes</h1>
    <p>Histórico clínico e exames por paciente</p>
  </div>
</div>

<!-- ── Barra de busca ─────────────────────────────────────────── -->
<div class="card" style="margin-bottom:20px;">
  <div class="card-header">
    <span class="card-title">
      <i class="fa-solid fa-magnifying-glass"></i> Buscar paciente
    </span>
  </div>
  <div class="card-body" style="padding:16px 22px;">
    <form method="GET" action="/pacientes" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
      <div style="flex:1;min-width:220px;">
        <label class="form-label">Nome, CPF ou Accession</label>
        <div class="search-wrap">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input
            type="text"
            name="busca"
            value="<?= htmlspecialchars($busca) ?>"
            placeholder="Ex: João Silva, 123.456.789-00 ou ACC-2025-001..."
            class="search-input"
          >
        </div>
      </div>
      <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-magnifying-glass"></i> Buscar
      </button>
      <?php if ($busca): ?>
      <a href="/pacientes" class="btn btn-ghost">
        <i class="fa-solid fa-xmark"></i> Limpar
      </a>
      <?php endif; ?>
    </form>
  </div>
</div>

<!-- ── Tabela de pacientes ────────────────────────────────────── -->
<div class="card">
  <div class="card-header">
    <span class="card-title">
      <i class="fa-solid fa-users"></i>
      <?= number_format($total) ?> paciente<?= $total !== 1 ? 's' : '' ?>
      <?php if ($busca): ?>
        <span class="badge badge-pendente" style="margin-left:8px;font-size:.67rem;">
          Filtrado: "<?= htmlspecialchars($busca) ?>"
        </span>
      <?php endif; ?>
    </span>
  </div>

  <div class="table-wrap">
    <table class="table">
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
        <tr>
          <td colspan="7">
            <div class="empty-state">
              <div class="empty-state-icon">
                <i class="fa-solid fa-user-slash"></i>
              </div>
              <h3>Nenhum paciente encontrado</h3>
              <p><?= $busca ? 'Tente ajustar os termos da busca.' : 'Nenhum paciente cadastrado ainda.' ?></p>
            </div>
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($pacientes as $p): ?>
        <tr>
          <!-- Paciente -->
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              <div class="avatar avatar-md avatar-blue" style="border-radius:50%;text-transform:uppercase;">
                <?= mb_substr($p['nome'], 0, 2) ?>
              </div>
              <div>
                <div style="font-weight:600;color:var(--gray-800);font-size:.84rem;line-height:1.3;">
                  <?= htmlspecialchars($p['nome']) ?>
                </div>
                <div style="font-size:.72rem;color:var(--muted);margin-top:2px;">
                  <?= htmlspecialchars($p['accession']) ?>
                </div>
              </div>
            </div>
          </td>

          <!-- CPF -->
          <td style="font-size:.78rem;font-family:'Courier New',monospace;color:var(--gray-600);letter-spacing:.03em;">
            <?= htmlspecialchars($p['cpf']) ?>
          </td>

          <!-- Idade / Sexo -->
          <td>
            <div style="font-size:.82rem;color:var(--text-2);">
              <?= $p['idade'] ?> anos
            </div>
            <div style="font-size:.72rem;color:var(--muted);margin-top:2px;">
              <?php if ($p['sexo'] === 'M'): ?>
                <span style="color:var(--blue-500);"><i class="fa-solid fa-mars"></i> Masculino</span>
              <?php else: ?>
                <span style="color:#db2777;"><i class="fa-solid fa-venus"></i> Feminino</span>
              <?php endif; ?>
            </div>
          </td>

          <!-- Cidade / Estado -->
          <td style="font-size:.82rem;color:var(--text-2);">
            <i class="fa-solid fa-location-dot" style="color:var(--muted);font-size:.72rem;margin-right:4px;"></i>
            <?= htmlspecialchars($p['cidade']) ?>/<?= htmlspecialchars($p['estado']) ?>
          </td>

          <!-- Exames -->
          <td>
            <span class="badge badge-ativo" style="font-size:.72rem;padding:4px 10px;">
              <?= $p['total_exames'] ?>
            </span>
          </td>

          <!-- Último Exame -->
          <td style="font-size:.78rem;color:var(--muted);white-space:nowrap;">
            <i class="fa-regular fa-calendar" style="margin-right:4px;"></i>
            <?= date('d/m/Y', strtotime($p['ultimo_exame'])) ?>
          </td>

          <!-- Ações -->
          <td>
            <div style="display:flex;gap:6px;align-items:center;">
              <a href="/pacientes/<?= $p['id'] ?>"
                 class="btn btn-ghost btn-sm"
                 title="Ver histórico clínico">
                <i class="fa-solid fa-timeline"></i> Histórico
              </a>
              <a href="/workspace/novo?paciente=<?= $p['id'] ?>"
                 class="btn btn-primary btn-sm"
                 title="Novo laudo">
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

  <!-- ── Paginação ──────────────────────────────────────────── -->
  <?php if ($totalPaginas > 1): ?>
  <div class="pagination">
    <?php if ($pagina > 1): ?>
    <a href="?pagina=<?= $pagina - 1 ?><?= $busca ? '&busca='.urlencode($busca) : '' ?>">
      <i class="fa-solid fa-chevron-left"></i>
    </a>
    <?php else: ?>
    <span class="disabled"><i class="fa-solid fa-chevron-left"></i></span>
    <?php endif; ?>

    <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
    <a href="?pagina=<?= $i ?><?= $busca ? '&busca='.urlencode($busca) : '' ?>"
       class="<?= $i === $pagina ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($pagina < $totalPaginas): ?>
    <a href="?pagina=<?= $pagina + 1 ?><?= $busca ? '&busca='.urlencode($busca) : '' ?>">
      <i class="fa-solid fa-chevron-right"></i>
    </a>
    <?php else: ?>
    <span class="disabled"><i class="fa-solid fa-chevron-right"></i></span>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>
