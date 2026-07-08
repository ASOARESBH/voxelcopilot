
<!-- ── Cabeçalho da página ─────────────────────────────────────── -->
<div class="page-header">
  <div class="page-header-left">
    <h1>
      <i class="fa-solid fa-user" style="color:var(--royal);margin-right:8px;font-size:1.1rem;"></i>
      <?= htmlspecialchars($paciente['nome']) ?>
    </h1>
    <p>Accession: <?= htmlspecialchars($paciente['accession'] ?? '—') ?> &nbsp;·&nbsp; Paciente #<?= $paciente['id'] ?></p>
  </div>
  <div class="page-header-actions">
    <a href="/pacientes" class="btn btn-ghost">
      <i class="fa-solid fa-arrow-left"></i> Voltar
    </a>
    <a href="/workspace/novo?paciente=<?= $paciente['id'] ?>" class="btn btn-primary">
      <i class="fa-solid fa-file-pen"></i> Novo Laudo
    </a>
  </div>
</div>

<!-- ── Grid principal: dados + resumo ────────────────────────── -->
<div class="pac-grid-main">

  <!-- Dados Pessoais -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-id-card"></i> Dados Pessoais
      </span>
    </div>
    <div class="card-body" style="padding:0;">
      <dl class="pac-detail-grid">
        <div class="pac-detail-item">
          <dt>Nome completo</dt>
          <dd><?= htmlspecialchars($paciente['nome']) ?></dd>
        </div>
        <div class="pac-detail-item">
          <dt>CPF</dt>
          <dd style="font-family:'Courier New',monospace;letter-spacing:.04em;"><?= htmlspecialchars($paciente['cpf']) ?></dd>
        </div>
        <div class="pac-detail-item">
          <dt>Data de nascimento</dt>
          <dd><?= date('d/m/Y', strtotime($paciente['nascimento'])) ?></dd>
        </div>
        <div class="pac-detail-item">
          <dt>Idade</dt>
          <dd><?= $paciente['idade'] ?> anos</dd>
        </div>
        <div class="pac-detail-item">
          <dt>Sexo</dt>
          <dd>
            <?php if ($paciente['sexo'] === 'M'): ?>
              <span style="color:var(--blue-500);font-weight:600;">
                <i class="fa-solid fa-mars"></i> Masculino
              </span>
            <?php else: ?>
              <span style="color:#db2777;font-weight:600;">
                <i class="fa-solid fa-venus"></i> Feminino
              </span>
            <?php endif; ?>
          </dd>
        </div>
        <div class="pac-detail-item">
          <dt>Telefone</dt>
          <dd><?= htmlspecialchars($paciente['telefone']) ?></dd>
        </div>
        <div class="pac-detail-item">
          <dt>E-mail</dt>
          <dd><?= htmlspecialchars($paciente['email']) ?></dd>
        </div>
        <div class="pac-detail-item">
          <dt>Cidade / Estado</dt>
          <dd>
            <i class="fa-solid fa-location-dot" style="color:var(--muted);font-size:.72rem;margin-right:3px;"></i>
            <?= htmlspecialchars($paciente['cidade']) ?>/<?= htmlspecialchars($paciente['estado']) ?>
          </dd>
        </div>
      </dl>
    </div>
  </div>

  <!-- Resumo / Stats -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-chart-bar"></i> Resumo Clínico
      </span>
    </div>
    <div class="card-body">
      <div class="pac-stats-grid">
        <div class="pac-stat-item">
          <div class="pac-stat-value"><?= $paciente['total_exames'] ?></div>
          <div class="pac-stat-label">Total de Exames</div>
        </div>
        <div class="pac-stat-item">
          <div class="pac-stat-value" style="font-size:1.2rem;">
            <?= date('d/m/Y', strtotime($paciente['ultimo_exame'])) ?>
          </div>
          <div class="pac-stat-label">Último Exame</div>
        </div>
      </div>
      <div style="margin-top:20px;">
        <a href="/timeline/paciente/<?= $paciente['id'] ?>" class="btn btn-ghost" style="width:100%;justify-content:center;">
          <i class="fa-solid fa-timeline"></i> Ver Timeline Completa
        </a>
      </div>
    </div>
  </div>

</div>

<!-- ── Histórico de Exames (timeline) ────────────────────────── -->
<div class="card" style="margin-top:20px;">
  <div class="card-header">
    <span class="card-title">
      <i class="fa-solid fa-timeline"></i> Histórico de Exames
    </span>
    <span class="badge badge-ativo" style="font-size:.67rem;">
      <?= count($timeline) ?> exame<?= count($timeline) !== 1 ? 's' : '' ?>
    </span>
  </div>
  <div class="card-body" style="padding:24px 28px;">
    <div class="pac-timeline">
      <div class="pac-timeline-line"></div>
      <?php foreach ($timeline as $item): ?>
      <div class="pac-timeline-item <?= $item['status'] === 'aguardando' ? 'pac-timeline-item--atual' : '' ?>">
        <div class="pac-timeline-dot <?= $item['status'] === 'aguardando' ? 'pac-dot-atual' : 'pac-dot-laudado' ?>">
          <?= htmlspecialchars($item['modalidade']) ?>
        </div>
        <div class="pac-timeline-info">
          <span class="pac-timeline-ano"><?= $item['ano'] ?></span>
          <span class="pac-timeline-desc"><?= htmlspecialchars($item['descricao']) ?></span>
          <?php if (!empty($item['instituicao'])): ?>
          <span class="pac-timeline-inst"><?= htmlspecialchars($item['instituicao']) ?></span>
          <?php endif; ?>
          <?php if ($item['status'] === 'aguardando'): ?>
            <a href="/workspace/novo?accession=<?= urlencode($item['accession']) ?>"
               class="btn btn-primary btn-xs" style="margin-top:6px;">
              <i class="fa-solid fa-pen-to-square"></i> Laudar
            </a>
          <?php else: ?>
            <a href="/workspace/laudo/<?= urlencode($item['accession']) ?>"
               class="btn btn-ghost btn-xs" style="margin-top:6px;">
              <i class="fa-solid fa-eye"></i> Ver laudo
            </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ── Estilos locais do módulo de pacientes ─────────────────── -->
<style>
/* Grid principal 2/3 + 1/3 */
.pac-grid-main {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 20px;
  align-items: start;
}

/* Grade de detalhes do paciente */
.pac-detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
  margin: 0;
  padding: 0;
}
.pac-detail-item {
  padding: 13px 22px;
  border-bottom: 1px solid var(--border);
  border-right: 1px solid var(--border);
}
.pac-detail-item:nth-child(even) {
  border-right: none;
}
.pac-detail-item:nth-last-child(-n+2) {
  border-bottom: none;
}
.pac-detail-item dt {
  font-size: .67rem;
  font-weight: 700;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: .07em;
  margin-bottom: 4px;
}
.pac-detail-item dd {
  font-size: .84rem;
  color: var(--gray-800);
  font-weight: 500;
  margin: 0;
}

/* Stats do resumo */
.pac-stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.pac-stat-item {
  background: var(--blue-50);
  border: 1px solid var(--blue-100);
  border-radius: var(--radius-sm);
  padding: 16px;
  text-align: center;
}
.pac-stat-value {
  font-family: var(--font-head);
  font-size: 1.8rem;
  font-weight: 800;
  color: var(--royal);
  line-height: 1;
  letter-spacing: -.03em;
  margin-bottom: 6px;
}
.pac-stat-label {
  font-size: .67rem;
  font-weight: 600;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: .07em;
}

/* Timeline horizontal */
.pac-timeline {
  display: flex;
  gap: 0;
  overflow-x: auto;
  padding: 8px 0 16px;
  position: relative;
  scrollbar-width: thin;
}
.pac-timeline-line {
  position: absolute;
  top: 36px;
  left: 40px;
  right: 40px;
  height: 2px;
  background: var(--border);
  z-index: 0;
}
.pac-timeline-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  min-width: 130px;
  flex: 1;
  position: relative;
  z-index: 1;
}
.pac-timeline-dot {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .72rem;
  font-weight: 700;
  border: 3px solid var(--white);
  flex-shrink: 0;
  transition: all .2s;
}
.pac-dot-laudado {
  background: var(--blue-50);
  color: var(--royal);
  box-shadow: 0 0 0 2px var(--blue-200);
}
.pac-dot-atual {
  background: var(--royal);
  color: var(--white);
  box-shadow: 0 0 0 4px rgba(26,86,219,.2);
}
.pac-timeline-item--atual .pac-timeline-dot {
  transform: scale(1.1);
}
.pac-timeline-info {
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 3px;
}
.pac-timeline-ano {
  font-size: .82rem;
  font-weight: 700;
  color: var(--gray-800);
}
.pac-timeline-desc {
  font-size: .72rem;
  color: var(--gray-600);
  line-height: 1.4;
  max-width: 110px;
}
.pac-timeline-inst {
  font-size: .67rem;
  color: var(--muted);
}

/* Responsividade */
@media (max-width: 768px) {
  .pac-grid-main {
    grid-template-columns: 1fr;
  }
  .pac-detail-grid {
    grid-template-columns: 1fr;
  }
  .pac-detail-item {
    border-right: none;
  }
  .pac-detail-item:nth-last-child(-n+2) {
    border-bottom: 1px solid var(--border);
  }
  .pac-detail-item:last-child {
    border-bottom: none;
  }
  .pac-stats-grid {
    grid-template-columns: 1fr 1fr;
  }
}
</style>
