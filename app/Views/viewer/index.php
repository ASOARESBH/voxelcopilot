<?php
$modIcons = [
    'TC'  => 'fa-circle-notch',
    'RM'  => 'fa-brain',
    'PET' => 'fa-radiation',
    'CR'  => 'fa-lungs',
    'DX'  => 'fa-bone',
    'US'  => 'fa-wave-square',
    'MG'  => 'fa-ribbon',
    'XA'  => 'fa-heart-pulse',
    'NM'  => 'fa-atom',
];

$iaClass = static function (string $label): string {
    $l = mb_strtolower($label);
    if (str_contains($l, 'pulm'))   return 'pulmao';
    if (str_contains($l, 'fratur')) return 'fratura';
    if (str_contains($l, 'avc'))    return 'avc';
    if (str_contains($l, 'nod'))    return 'nodulo';
    return 'outro';
};

$statusMeta = [
    'disponivel'  => ['badge' => 'badge-ativo',    'icon' => 'fa-circle', 'label' => 'Disponível'],
    'processando' => ['badge' => 'badge-pendente', 'icon' => 'fa-circle', 'label' => 'Processando'],
    'erro'        => ['badge' => 'badge-suspenso', 'icon' => 'fa-circle', 'label' => 'Erro'],
];
?>

<div class="page-content vw-page">

<?php if (!$viewerUrl): ?>

  <!-- ── Cabeçalho ────────────────────────────────────────────── -->
  <div class="page-header">
    <div class="page-header-left">
      <h1><i class="fa-solid fa-x-ray" style="color:var(--royal);margin-right:8px;font-size:1.1rem;"></i>Viewer DICOM</h1>
      <p>Visualizador Diagnóstico Integrado ao VOXEL PACS</p>
    </div>
    <div class="page-header-actions">
      <button type="button" class="btn btn-primary" onclick="toggleStudyUidBox(true)">
        <i class="fa-solid fa-play"></i> Abrir Viewer
      </button>
      <button type="button" class="btn btn-secondary" onclick="abrirPorAccession()">
        <i class="fa-solid fa-hashtag"></i> Abrir por Accession
      </button>
      <button type="button" class="btn btn-ghost" onclick="location.reload()">
        <i class="fa-solid fa-rotate"></i> Atualizar
      </button>
      <a href="/configuracoes/autorizacao" class="btn btn-ghost">
        <i class="fa-solid fa-gear"></i> Configurações
      </a>
    </div>
  </div>

  <!-- ── Cards de resumo ──────────────────────────────────────── -->
  <div class="stats-grid vw-stats-row">
    <div class="stat-card blue">
      <div class="stat-card-icon"><i class="fa-solid fa-calendar-day"></i></div>
      <div class="stat-card-value"><?= number_format($stats['estudos_hoje']) ?></div>
      <div class="stat-card-label">Estudos Hoje</div>
    </div>
    <div class="stat-card purple">
      <div class="stat-card-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
      <div class="stat-card-value"><?= (int) $stats['recentes'] ?></div>
      <div class="stat-card-label">Recentes</div>
    </div>
    <div class="stat-card yellow">
      <div class="stat-card-icon"><i class="fa-solid fa-star"></i></div>
      <div class="stat-card-value" id="favStatValue"><?= (int) $stats['favoritos'] ?></div>
      <div class="stat-card-label">Favoritos</div>
    </div>
    <div class="stat-card green">
      <div class="stat-card-icon"><i class="fa-solid fa-signal"></i></div>
      <div class="stat-card-value" style="font-size:1.5rem;"><?= htmlspecialchars($stats['ultimo_acesso']) ?></div>
      <div class="stat-card-label">Último Acesso</div>
    </div>
  </div>

  <!-- ── Pesquisa inteligente ─────────────────────────────────── -->
  <div class="card vw-search-card">
    <div class="vw-search-row">
      <div class="vw-search-input-wrap">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="smartSearch" class="vw-search-input"
               placeholder="Pesquisar por Paciente, Accession, Study UID, MRN, CPF, Nome, Data ou Modalidade...">
      </div>
      <button type="button" class="btn btn-primary btn-lg" onclick="applyFilters()">
        <i class="fa-solid fa-magnifying-glass"></i> Buscar
      </button>
      <button type="button" class="btn btn-secondary btn-lg" onclick="toggleStudyUidBox()">
        <i class="fa-solid fa-camera"></i> Abrir Viewer
      </button>
      <button type="button" class="btn btn-ghost btn-lg" onclick="alert('Leitor de QR Code em breve.')">
        <i class="fa-solid fa-qrcode"></i> QR Code
      </button>
    </div>

    <div class="vw-studyuid-box" id="studyUidBox" style="display:none;">
      <div class="input-icon-wrap" style="flex:1">
        <i class="fa-solid fa-fingerprint"></i>
        <input type="text" id="studyUidInput" placeholder="Cole o Study Instance UID..." class="form-control">
      </div>
      <button type="button" onclick="abrirViewerPorUid()" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-arrow-right"></i> Abrir
      </button>
    </div>

    <!-- Filtros rápidos -->
    <div class="vw-filters">
      <div class="vw-filter-group" data-group="periodo">
        <button type="button" class="vw-chip active" data-value="todos">Todos</button>
        <button type="button" class="vw-chip" data-value="hoje">Hoje</button>
        <button type="button" class="vw-chip" data-value="ontem">Ontem</button>
        <button type="button" class="vw-chip" data-value="7d">Últimos 7 dias</button>
        <button type="button" class="vw-chip" data-value="30d">Últimos 30 dias</button>
      </div>
      <div class="vw-filter-group" data-group="modalidade">
        <?php foreach (['TC','RM','CR','DX','US','PET','MG','XA','NM'] as $mod): ?>
        <button type="button" class="vw-chip" data-value="<?= $mod ?>"><?= $mod ?></button>
        <?php endforeach; ?>
      </div>
      <div class="vw-filter-group" data-group="extra">
        <button type="button" class="vw-chip" data-value="com_laudo">Com Laudo</button>
        <button type="button" class="vw-chip" data-value="sem_laudo">Sem Laudo</button>
        <button type="button" class="vw-chip" data-value="favoritos">Favoritos</button>
        <button type="button" class="vw-chip" data-value="urgente">Urgente</button>
        <button type="button" class="vw-chip" data-value="comparativos">Comparativos</button>
      </div>
    </div>
  </div>

  <!-- ── Grid principal: DataGrid (75%) + Painel lateral (25%) ─── -->
  <div class="vw-main-grid">

    <div class="vw-col-grid">
      <div class="card vw-grid-card">
        <div class="card-header">
          <span class="card-title"><i class="fa-solid fa-table-list"></i> Estudos Recentes</span>
          <span class="badge badge-ativo" id="resultCount"><?= count($estudosRecentes) ?> estudos</span>
        </div>
        <div class="table-wrap">
          <table class="table vw-datagrid" id="studiesTable">
            <thead>
              <tr>
                <th>Miniatura</th><th>Paciente</th><th>Sexo</th><th>Idade</th><th>Modalidade</th>
                <th>Descrição</th><th>Instituição</th><th>Data</th><th>Hora</th><th>Accession</th>
                <th>Status</th><th>IA</th><th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($estudosRecentes as $e): $sm = $statusMeta[$e['status']]; ?>
              <tr class="vw-row"
                  data-study="<?= htmlspecialchars($e['study_uid']) ?>"
                  data-paciente="<?= htmlspecialchars(mb_strtolower($e['paciente'])) ?>"
                  data-paciente-display="<?= htmlspecialchars($e['paciente']) ?>"
                  data-accession="<?= htmlspecialchars(mb_strtolower($e['accession'])) ?>"
                  data-modalidade="<?= htmlspecialchars($e['modalidade']) ?>"
                  data-data="<?= htmlspecialchars($e['data']) ?>"
                  data-hora="<?= htmlspecialchars($e['hora']) ?>"
                  data-laudo="<?= $e['status'] === 'disponivel' ? '1' : '0' ?>"
                  data-favorito="<?= $e['favorito'] ? '1' : '0' ?>"
                  data-urgente="<?= $e['urgente'] ? '1' : '0' ?>"
                  data-comparativo="<?= $e['comparativo'] ? '1' : '0' ?>"
                  data-instituicao="<?= htmlspecialchars($e['instituicao']) ?>"
                  data-descricao="<?= htmlspecialchars($e['descricao']) ?>"
                  data-equipamento="<?= htmlspecialchars($e['equipamento']) ?>"
                  data-series="<?= (int) $e['series'] ?>"
                  data-imagens="<?= (int) $e['num_imagens'] ?>"
                  data-tamanho="<?= htmlspecialchars($e['tamanho_mb']) ?>">
                <td>
                  <div class="vw-thumb vw-thumb-<?= strtolower($e['modalidade']) ?>">
                    <i class="fa-solid <?= $modIcons[$e['modalidade']] ?? 'fa-image' ?>"></i>
                    <span><?= htmlspecialchars($e['modalidade']) ?></span>
                  </div>
                </td>
                <td>
                  <div style="font-weight:600;color:var(--gray-800);font-size:.84rem;">
                    <?= htmlspecialchars($e['paciente']) ?>
                  </div>
                </td>
                <td>
                  <?php if ($e['sexo'] === 'M'): ?>
                    <span style="color:var(--blue-500);"><i class="fa-solid fa-mars"></i></span>
                  <?php else: ?>
                    <span style="color:#db2777;"><i class="fa-solid fa-venus"></i></span>
                  <?php endif; ?>
                </td>
                <td><?= (int) $e['idade'] ?> a</td>
                <td><span class="badge badge-trial"><?= htmlspecialchars($e['modalidade']) ?></span></td>
                <td style="max-width:220px;"><?= htmlspecialchars($e['descricao']) ?></td>
                <td style="font-size:.78rem;color:var(--muted);"><?= htmlspecialchars($e['instituicao']) ?></td>
                <td style="white-space:nowrap;"><?= date('d/m/Y', strtotime($e['data'])) ?></td>
                <td style="white-space:nowrap;"><?= htmlspecialchars($e['hora']) ?></td>
                <td class="font-mono" style="font-size:.75rem;"><?= htmlspecialchars($e['accession']) ?></td>
                <td>
                  <span class="badge <?= $sm['badge'] ?>">
                    <i class="fa-solid <?= $sm['icon'] ?>" style="font-size:.5rem;"></i> <?= $sm['label'] ?>
                  </span>
                  <?php if ($e['urgente']): ?>
                    <span class="badge badge-suspenso" style="margin-top:4px;"><i class="fa-solid fa-triangle-exclamation" style="font-size:.55rem;"></i> Urgente</span>
                  <?php endif; ?>
                </td>
                <td style="max-width:150px;">
                  <?php if (empty($e['ia'])): ?>
                    <span style="color:var(--muted);font-size:.72rem;">—</span>
                  <?php else: foreach ($e['ia'] as $tag): ?>
                    <span class="vw-ia-badge <?= $iaClass($tag) ?>"><i class="fa-solid fa-brain"></i> <?= htmlspecialchars($tag) ?></span>
                  <?php endforeach; endif; ?>
                  <?php if ($e['comparativo']): ?>
                    <span class="vw-ia-badge comparativo"><i class="fa-solid fa-code-compare"></i> Comparativo</span>
                  <?php endif; ?>
                </td>
                <td class="vw-row-actions" onclick="event.stopPropagation()">
                  <div class="vw-actions-row">
                    <a href="/viewer?study=<?= urlencode($e['study_uid']) ?>" class="btn btn-primary btn-xs vw-icon-btn" title="Abrir no Viewer">
                      <i class="fa-solid fa-eye"></i>
                    </a>
                    <button type="button" class="btn btn-ghost btn-xs vw-icon-btn"
                            title="<?= $e['comparativo'] ? 'Comparar com exame anterior' : 'Nenhum comparativo disponível' ?>"
                            <?= $e['comparativo'] ? '' : 'disabled' ?>
                            onclick="window.location.href='/viewer?study=<?= urlencode($e['study_uid']) ?>&compare=1'">
                      <i class="fa-solid fa-code-compare"></i>
                    </button>
                    <button type="button" class="btn btn-ghost btn-xs vw-icon-btn vw-fav-btn <?= $e['favorito'] ? 'active' : '' ?>"
                            title="Favoritar" onclick="toggleFavorito(event, this)">
                      <i class="fa-solid fa-star"></i>
                    </button>
                    <button type="button" class="btn btn-ghost btn-xs vw-icon-btn" title="Compartilhar"
                            onclick="compartilharEstudo(event, this, '<?= htmlspecialchars($e['study_uid'], ENT_QUOTES) ?>')">
                      <i class="fa-solid fa-share-nodes"></i>
                    </button>
                    <button type="button" class="btn btn-ghost btn-xs vw-icon-btn" title="Baixar"
                            onclick="baixarEstudo(event, this)">
                      <i class="fa-solid fa-download"></i>
                    </button>
                    <button type="button" class="btn btn-ghost btn-xs vw-icon-btn vw-kebab-btn" title="Mais opções"
                            onclick="openRowMenu(event, this)">
                      <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <tr id="noResultsRow" style="display:none;">
                <td colspan="13">
                  <div class="empty-state">
                    <div class="empty-state-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                    <h3>Nenhum estudo encontrado</h3>
                    <p>Ajuste a pesquisa ou os filtros rápidos.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── Painel lateral direito ────────────────────────────────── -->
    <div class="vw-col-side">

      <!-- Copilot IA -->
      <div class="card vw-copilot-card">
        <div class="vw-copilot-head">
          <div class="vw-copilot-avatar"><i class="fa-solid fa-brain"></i></div>
          <div>
            <div class="vw-copilot-title">Copilot IA</div>
            <div class="vw-copilot-sub">Assistente do exame selecionado</div>
          </div>
        </div>
        <div class="vw-copilot-actions">
          <a href="/copilot" class="vw-copilot-item"><i class="fa-solid fa-comment-medical"></i> Pergunte ao exame</a>
          <a href="/copilot" class="vw-copilot-item"><i class="fa-solid fa-file-lines"></i> Resumo do estudo</a>
          <a href="/copilot" class="vw-copilot-item"><i class="fa-solid fa-magnifying-glass-chart"></i> Buscar exames semelhantes</a>
          <a href="/copilot" class="vw-copilot-item"><i class="fa-solid fa-lightbulb"></i> Explicar achados</a>
          <a href="/copilot" class="vw-copilot-item"><i class="fa-solid fa-display"></i> Gerar apresentação clínica</a>
        </div>
        <a href="/copilot" class="btn btn-primary" style="width:100%;justify-content:center;">
          <i class="fa-solid fa-brain"></i> Abrir Copilot
        </a>
      </div>

      <!-- Tabs: Últimos / Favoritos / Compartilhados / Comparativos / Alertas -->
      <div class="card vw-side-panel">
        <div class="vw-side-tabs">
          <button type="button" class="vw-side-tab active" data-tab="acessos"><i class="fa-solid fa-clock-rotate-left"></i> Últimos</button>
          <button type="button" class="vw-side-tab" data-tab="favoritos"><i class="fa-solid fa-star"></i> Favoritos</button>
          <button type="button" class="vw-side-tab" data-tab="compartilhados"><i class="fa-solid fa-share-nodes"></i> Compart.</button>
          <button type="button" class="vw-side-tab" data-tab="comparativos"><i class="fa-solid fa-code-compare"></i> Comparar</button>
          <button type="button" class="vw-side-tab" data-tab="alertas"><i class="fa-solid fa-triangle-exclamation"></i> Alertas IA</button>
        </div>

        <div class="vw-side-panel-content active" data-panel="acessos">
          <div class="vw-mini-timeline">
            <?php foreach ($ultimosAcessos as $a): ?>
            <a href="/viewer?study=<?= urlencode($a['study_uid']) ?>" class="vw-mini-timeline-item">
              <span class="vw-mini-timeline-time"><?= htmlspecialchars($a['hora']) ?></span>
              <span class="vw-mini-timeline-dot"></span>
              <span class="vw-mini-timeline-text">
                <strong><?= htmlspecialchars($a['paciente']) ?></strong>
                <?= htmlspecialchars($a['descricao']) ?>
              </span>
            </a>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="vw-side-panel-content" data-panel="favoritos">
          <?php if (empty($favoritosList)): ?>
            <p class="vw-side-empty">Nenhum estudo favoritado ainda.</p>
          <?php else: foreach ($favoritosList as $f): ?>
          <div class="vw-fav-card">
            <div class="vw-fav-card-top">
              <strong><?= htmlspecialchars($f['paciente']) ?></strong>
              <span class="badge badge-trial"><?= htmlspecialchars($f['modalidade']) ?></span>
            </div>
            <div class="vw-fav-card-meta"><?= date('d/m/Y', strtotime($f['data'])) ?> · <?= htmlspecialchars($f['instituicao']) ?></div>
            <a href="/viewer?study=<?= urlencode($f['study_uid']) ?>" class="btn btn-ghost btn-xs" style="width:100%;justify-content:center;margin-top:8px;">
              <i class="fa-solid fa-rotate-right"></i> Abrir novamente
            </a>
          </div>
          <?php endforeach; endif; ?>
        </div>

        <div class="vw-side-panel-content" data-panel="compartilhados">
          <?php foreach ($compartilhados as $c): ?>
          <div class="vw-list-item">
            <div><strong><?= htmlspecialchars($c['paciente']) ?></strong></div>
            <div class="vw-list-item-sub"><?= htmlspecialchars($c['descricao']) ?></div>
            <div class="vw-list-item-meta"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($c['com']) ?> · <?= htmlspecialchars($c['quando']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="vw-side-panel-content" data-panel="comparativos">
          <?php foreach ($comparativosList as $cp): ?>
          <div class="vw-list-item">
            <div class="vw-fav-card-top"><strong><?= htmlspecialchars($cp['paciente']) ?></strong><span class="vw-pct"><?= (int) $cp['pct'] ?>%</span></div>
            <div class="vw-list-item-sub"><?= htmlspecialchars($cp['descricao']) ?></div>
            <div class="vw-progress"><div class="vw-progress-bar" style="width:<?= (int) $cp['pct'] ?>%"></div></div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="vw-side-panel-content" data-panel="alertas">
          <?php foreach ($alertasIA as $al): ?>
          <div class="vw-alert-item vw-alert-<?= htmlspecialchars($al['tipo']) ?>">
            <i class="fa-solid <?= $al['tipo'] === 'critico' ? 'fa-triangle-exclamation' : ($al['tipo'] === 'atencao' ? 'fa-circle-exclamation' : 'fa-circle-info') ?>"></i>
            <div>
              <div class="vw-alert-text"><?= htmlspecialchars($al['texto']) ?></div>
              <div class="vw-alert-time"><?= htmlspecialchars($al['quando']) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Drawer de detalhes do estudo ────────────────────────────── -->
  <div class="vw-drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>
  <aside class="vw-drawer" id="studyDrawer">
    <div class="vw-drawer-header">
      <span class="vw-drawer-title"><i class="fa-solid fa-file-waveform"></i> Detalhes do Estudo</span>
      <button type="button" class="vw-drawer-close" onclick="closeDrawer()" aria-label="Fechar">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="vw-drawer-body">
      <dl class="vw-drawer-grid">
        <div><dt>Paciente</dt><dd id="drawerPaciente">—</dd></div>
        <div><dt>Modalidade</dt><dd id="drawerModalidade">—</dd></div>
        <div><dt>Data</dt><dd id="drawerData">—</dd></div>
        <div><dt>Instituição</dt><dd id="drawerInstituicao">—</dd></div>
        <div><dt>Equipamento</dt><dd id="drawerEquipamento">—</dd></div>
        <div><dt>Séries</dt><dd id="drawerSeries">—</dd></div>
        <div><dt>Número de imagens</dt><dd id="drawerImagens">—</dd></div>
        <div><dt>Tamanho</dt><dd id="drawerTamanho">—</dd></div>
      </dl>
    </div>
    <div class="vw-drawer-footer">
      <a href="#" id="drawerOpenViewer" class="btn btn-primary" style="width:100%;justify-content:center;">
        <i class="fa-solid fa-play"></i> Abrir no Viewer
      </a>
    </div>
  </aside>

  <!-- Menu de contexto (Mais opções) -->
  <div class="vw-row-menu" id="rowMenu"></div>

<?php else: ?>
  <!-- ── Viewer embutido ──────────────────────────────────────────── -->
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
/* ══════════════════════════════════════════════════════════════
   VIEWER — estilos do módulo (prefixo vw-)
══════════════════════════════════════════════════════════════ */

.vw-stats-row { margin-bottom: 20px; }

/* Busca inteligente */
.vw-search-card { padding: 22px 24px 18px; margin-bottom: 20px; }
.vw-search-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.vw-search-input-wrap {
  flex: 1; min-width: 260px; display: flex; align-items: center;
  background: var(--bg); border: 1.5px solid var(--border); border-radius: var(--radius);
  padding: 0 16px; height: 52px; transition: border-color .2s, box-shadow .2s, background .2s;
}
.vw-search-input-wrap:focus-within { border-color: var(--royal); box-shadow: 0 0 0 3px rgba(26,86,219,.12); background: var(--white); }
.vw-search-input-wrap i { color: var(--muted); margin-right: 10px; font-size: 1rem; }
.vw-search-input { flex: 1; border: none; background: transparent; outline: none; font-size: .95rem; color: var(--text); }
.btn-lg { padding: 0 20px; height: 52px; font-size: .86rem; }
.vw-studyuid-box { display: flex; gap: 10px; margin-top: 14px; align-items: center; }

.vw-filters { display: flex; flex-direction: column; gap: 8px; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border); }
.vw-filter-group { display: flex; gap: 8px; flex-wrap: wrap; }
.vw-chip {
  border: 1px solid var(--border); background: var(--white); color: var(--gray-600);
  padding: 6px 14px; border-radius: 20px; font-size: .74rem; font-weight: 600; cursor: pointer; transition: all .15s;
}
.vw-chip:hover { border-color: var(--blue-200); color: var(--royal); background: var(--blue-50); }
.vw-chip.active { background: var(--royal); border-color: var(--royal); color: #fff; }

/* Grid principal */
.vw-main-grid { display: grid; grid-template-columns: 3fr 1fr; gap: 20px; align-items: start; }
.vw-col-grid { min-width: 0; }
.vw-col-side { display: flex; flex-direction: column; gap: 16px; position: sticky; top: 16px; min-width: 0; }

/* DataGrid */
.vw-datagrid tbody tr:nth-child(even) td { background: var(--gray-50); }
#studiesTable tbody tr:hover td { background: var(--blue-50) !important; cursor: pointer; }
.vw-thumb {
  width: 56px; height: 56px; border-radius: 10px; position: relative; overflow: hidden;
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px;
  color: #fff; font-size: 1rem;
}
.vw-thumb::after { content:''; position:absolute; inset:0; background:linear-gradient(135deg, rgba(255,255,255,.2), transparent 60%); }
.vw-thumb span { font-size: .58rem; font-weight: 800; letter-spacing: .04em; position: relative; }
.vw-thumb i { position: relative; }
.vw-thumb-tc { background: linear-gradient(135deg, #1a56db, #0d2244); }
.vw-thumb-rm { background: linear-gradient(135deg, #7c3aed, #4c1d95); }
.vw-thumb-pet { background: linear-gradient(135deg, #ea580c, #7c2d12); }
.vw-thumb-cr, .vw-thumb-dx { background: linear-gradient(135deg, #0284c7, #0c4a6e); }
.vw-thumb-us { background: linear-gradient(135deg, #059669, #065f46); }
.vw-thumb-mg { background: linear-gradient(135deg, #db2777, #831843); }
.vw-thumb-xa { background: linear-gradient(135deg, #64748b, #1e293b); }
.vw-thumb-nm { background: linear-gradient(135deg, #d97706, #78350f); }

.vw-ia-badge {
  display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 20px;
  font-size: .63rem; font-weight: 700; margin: 2px 3px 2px 0; white-space: nowrap;
}
.vw-ia-badge.pulmao { background: var(--blue-50); color: var(--royal); }
.vw-ia-badge.fratura { background: var(--warning-bg); color: var(--warning); }
.vw-ia-badge.avc { background: var(--danger-bg); color: var(--danger); }
.vw-ia-badge.nodulo, .vw-ia-badge.outro { background: #f5f3ff; color: #7c3aed; }
.vw-ia-badge.comparativo { background: var(--gray-100); color: var(--gray-600); }

.vw-actions-row { display: flex; gap: 4px; align-items: center; }
.vw-icon-btn { width: 26px; height: 26px; padding: 0 !important; display: inline-flex; align-items: center; justify-content: center; }
.vw-fav-btn i { color: var(--gray-400); }
.vw-fav-btn.active i, .vw-fav-btn.active { color: #d97706; }
.vw-fav-btn.active { background: var(--warning-bg); border-color: var(--warning-border); }
.vw-flash-ok { background: var(--success-bg) !important; border-color: var(--success-border) !important; color: var(--success) !important; }

/* Menu de contexto (mais opções) */
.vw-row-menu {
  display: none; position: fixed; z-index: 1200; min-width: 210px;
  background: var(--white); border: 1px solid var(--border); border-radius: var(--radius-sm);
  box-shadow: var(--shadow-lg); padding: 6px; flex-direction: column; gap: 2px;
}
.vw-row-menu.open { display: flex; }
.vw-row-menu button {
  display: flex; align-items: center; gap: 9px; width: 100%; text-align: left;
  padding: 9px 11px; border: none; background: transparent; border-radius: var(--radius-xs);
  font-size: .8rem; color: var(--gray-700); cursor: pointer; font-family: var(--font-body);
}
.vw-row-menu button:hover { background: var(--blue-50); color: var(--royal); }
.vw-row-menu button i { width: 14px; color: var(--muted); }
.vw-row-menu button:hover i { color: var(--royal); }

/* Copilot card */
.vw-copilot-card { padding: 18px; display: flex; flex-direction: column; gap: 14px; }
.vw-copilot-head { display: flex; align-items: center; gap: 12px; }
.vw-copilot-avatar {
  width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg, var(--royal), var(--blue-900));
  display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.1rem;
}
.vw-copilot-title { font-family: var(--font-head); font-weight: 800; font-size: .92rem; color: var(--gray-800); }
.vw-copilot-sub { font-size: .72rem; color: var(--muted); }
.vw-copilot-actions { display: flex; flex-direction: column; gap: 6px; }
.vw-copilot-item {
  display: flex; align-items: center; gap: 9px; padding: 9px 11px; border-radius: var(--radius-xs);
  background: var(--bg); border: 1px solid var(--border); color: var(--gray-700); font-size: .78rem; font-weight: 600;
  transition: all .15s;
}
.vw-copilot-item:hover { background: var(--blue-50); border-color: var(--blue-200); color: var(--royal); }
.vw-copilot-item i { color: var(--royal); width: 14px; text-align: center; }

/* Painel lateral com abas */
.vw-side-panel { padding: 0; }
.vw-side-tabs { display: flex; gap: 2px; padding: 10px 10px 0; overflow-x: auto; border-bottom: 1px solid var(--border); }
.vw-side-tab {
  flex-shrink: 0; border: none; background: transparent; padding: 8px 10px; font-size: .68rem; font-weight: 700;
  color: var(--muted); cursor: pointer; border-bottom: 2px solid transparent; display: flex; align-items: center; gap: 5px;
  text-transform: uppercase; letter-spacing: .03em; white-space: nowrap;
}
.vw-side-tab:hover { color: var(--royal); }
.vw-side-tab.active { color: var(--royal); border-bottom-color: var(--royal); }
.vw-side-panel-content { display: none; padding: 14px; max-height: 420px; overflow-y: auto; }
.vw-side-panel-content.active { display: block; }
.vw-side-empty { font-size: .78rem; color: var(--muted); text-align: center; padding: 20px 0; }

.vw-mini-timeline { display: flex; flex-direction: column; }
.vw-mini-timeline-item {
  display: grid; grid-template-columns: 40px 12px 1fr; align-items: start; gap: 8px;
  padding: 8px 4px; border-radius: var(--radius-xs); color: inherit;
}
.vw-mini-timeline-item:hover { background: var(--blue-50); }
.vw-mini-timeline-time { font-size: .68rem; color: var(--muted); font-weight: 700; padding-top: 2px; }
.vw-mini-timeline-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--royal); margin: 5px auto 0; box-shadow: 0 0 0 3px var(--blue-100); }
.vw-mini-timeline-text { font-size: .78rem; color: var(--gray-700); line-height: 1.4; }
.vw-mini-timeline-text strong { display: block; color: var(--gray-800); font-size: .8rem; }

.vw-fav-card { border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 12px; margin-bottom: 10px; background: var(--bg); }
.vw-fav-card-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: .82rem; }
.vw-fav-card-meta { font-size: .7rem; color: var(--muted); margin-top: 4px; }

.vw-list-item { border-bottom: 1px solid var(--border); padding: 10px 2px; font-size: .8rem; }
.vw-list-item:last-child { border-bottom: none; }
.vw-list-item-sub { font-size: .76rem; color: var(--gray-600); margin-top: 2px; }
.vw-list-item-meta { font-size: .68rem; color: var(--muted); margin-top: 4px; }
.vw-pct { font-weight: 800; color: var(--royal); font-size: .78rem; }
.vw-progress { height: 6px; border-radius: 4px; background: var(--gray-100); margin-top: 8px; overflow: hidden; }
.vw-progress-bar { height: 100%; background: linear-gradient(90deg, var(--royal), var(--blue-300)); border-radius: 4px; }

.vw-alert-item { display: flex; gap: 10px; padding: 10px 2px; border-bottom: 1px solid var(--border); font-size: .78rem; }
.vw-alert-item:last-child { border-bottom: none; }
.vw-alert-item i { margin-top: 2px; }
.vw-alert-critico i { color: var(--danger); }
.vw-alert-atencao i { color: var(--warning); }
.vw-alert-info i { color: var(--info); }
.vw-alert-text { color: var(--gray-700); line-height: 1.4; }
.vw-alert-time { font-size: .68rem; color: var(--muted); margin-top: 3px; }

/* Drawer de detalhes */
.vw-drawer-overlay {
  position: fixed; inset: 0; background: rgba(15,23,42,.45); backdrop-filter: blur(2px);
  opacity: 0; pointer-events: none; transition: opacity .2s; z-index: 1300;
}
.vw-drawer-overlay.open { opacity: 1; pointer-events: auto; }
.vw-drawer {
  position: fixed; top: 0; right: 0; bottom: 0; width: 380px; max-width: 92vw;
  background: var(--white); box-shadow: var(--shadow-lg); z-index: 1301;
  transform: translateX(100%); transition: transform .25s ease; display: flex; flex-direction: column;
}
.vw-drawer.open { transform: translateX(0); }
.vw-drawer-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px; border-bottom: 1px solid var(--border); }
.vw-drawer-title { font-family: var(--font-head); font-weight: 800; font-size: .92rem; color: var(--gray-800); display: flex; align-items: center; gap: 8px; }
.vw-drawer-title i { color: var(--royal); }
.vw-drawer-close { border: none; background: var(--gray-100); width: 30px; height: 30px; border-radius: 50%; color: var(--gray-600); cursor: pointer; }
.vw-drawer-close:hover { background: var(--gray-200); }
.vw-drawer-body { flex: 1; overflow-y: auto; padding: 20px; }
.vw-drawer-grid { display: flex; flex-direction: column; gap: 14px; }
.vw-drawer-grid dt { font-size: .66rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 3px; }
.vw-drawer-grid dd { font-size: .86rem; color: var(--gray-800); font-weight: 500; }
.vw-drawer-footer { padding: 16px 20px; border-top: 1px solid var(--border); }

/* Viewer embutido */
.viewer-frame-container { background: #000; border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow-lg); }
.viewer-toolbar { background: var(--blue-800); padding: 12px 20px; display: flex; align-items: center; gap: 16px; }
.viewer-study-uid { color: var(--sidebar-muted); font-size: 12px; font-family: monospace; flex: 1; }
.viewer-iframe { width: 100%; height: calc(100vh - 220px); border: none; display: block; }

/* Responsivo */
@media (max-width: 1200px) {
  .vw-main-grid { grid-template-columns: 1fr; }
  .vw-col-side { position: static; }
}
@media (max-width: 768px) {
  .vw-search-row { flex-direction: column; align-items: stretch; }
  .btn-lg { width: 100%; justify-content: center; }
  .vw-filters .vw-filter-group { flex-wrap: nowrap; overflow-x: auto; padding-bottom: 4px; }
}
@media (max-width: 480px) {
  .stats-grid.vw-stats-row { grid-template-columns: 1fr 1fr; }
}
</style>

<script>
(function () {
  var studyUidInput = document.getElementById('studyUidInput');
  if (studyUidInput) {
    studyUidInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') abrirViewerPorUid(); });
  }

  var smartSearch = document.getElementById('smartSearch');
  if (smartSearch) {
    var searchTimer;
    smartSearch.addEventListener('input', function () {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(applyFilters, 300);
    });
  }

  // Chips de filtro rápido
  document.querySelectorAll('.vw-filter-group').forEach(function (group) {
    var isRadio = group.dataset.group === 'periodo';
    group.querySelectorAll('.vw-chip').forEach(function (chip) {
      chip.addEventListener('click', function () {
        if (isRadio) {
          group.querySelectorAll('.vw-chip').forEach(function (c) { c.classList.remove('active'); });
          chip.classList.add('active');
        } else {
          chip.classList.toggle('active');
        }
        applyFilters();
      });
    });
  });

  // Abas do painel lateral
  document.querySelectorAll('.vw-side-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
      document.querySelectorAll('.vw-side-tab').forEach(function (t) { t.classList.remove('active'); });
      tab.classList.add('active');
      var name = tab.dataset.tab;
      document.querySelectorAll('.vw-side-panel-content').forEach(function (p) {
        p.classList.toggle('active', p.dataset.panel === name);
      });
    });
  });

  // Clique na linha abre o drawer (ações têm stopPropagation própria)
  var tbody = document.querySelector('#studiesTable tbody');
  if (tbody) {
    tbody.addEventListener('click', function (e) {
      var row = e.target.closest('tr.vw-row');
      if (row) openDrawer(row);
    });
  }

  document.addEventListener('click', function (e) {
    var menu = document.getElementById('rowMenu');
    if (menu && menu.classList.contains('open') && !e.target.closest('#rowMenu') && !e.target.closest('.vw-kebab-btn')) {
      menu.classList.remove('open');
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    closeDrawer();
    var menu = document.getElementById('rowMenu');
    if (menu) menu.classList.remove('open');
  });
})();

function toggleStudyUidBox(forceOpen) {
  var box = document.getElementById('studyUidBox');
  if (!box) return;
  var willShow = forceOpen === true || box.style.display === 'none';
  box.style.display = willShow ? 'flex' : 'none';
  if (willShow) {
    var input = document.getElementById('studyUidInput');
    if (input) input.focus();
  }
}

function abrirViewerPorUid() {
  var input = document.getElementById('studyUidInput');
  var uid = input ? input.value.trim() : '';
  if (uid) window.location.href = '/viewer?study=' + encodeURIComponent(uid);
}

function abrirPorAccession() {
  var acc = window.prompt('Digite o Accession Number do estudo:');
  if (!acc) return;
  var target = acc.trim().toLowerCase();
  var rows = document.querySelectorAll('#studiesTable tbody tr.vw-row');
  var found = null;
  rows.forEach(function (r) { if (!found && r.dataset.accession === target) found = r; });
  if (found) {
    window.location.href = '/viewer?study=' + encodeURIComponent(found.dataset.study);
  } else {
    alert('Nenhum estudo encontrado com o Accession "' + acc + '".');
  }
}

function getPeriodoAtivo() {
  var el = document.querySelector('.vw-filter-group[data-group="periodo"] .vw-chip.active');
  return el ? el.dataset.value : 'todos';
}

function matchesPeriodo(dateStr, periodo) {
  if (periodo === 'todos') return true;
  var rowDate = new Date(dateStr + 'T00:00:00');
  var today = new Date();
  today.setHours(0, 0, 0, 0);
  var diffDays = Math.round((today - rowDate) / 86400000);
  if (periodo === 'hoje') return diffDays === 0;
  if (periodo === 'ontem') return diffDays === 1;
  if (periodo === '7d') return diffDays >= 0 && diffDays <= 7;
  if (periodo === '30d') return diffDays >= 0 && diffDays <= 30;
  return true;
}

function applyFilters() {
  var search = (document.getElementById('smartSearch') || {}).value || '';
  search = search.trim().toLowerCase();

  var periodo = getPeriodoAtivo();
  var modalidades = Array.from(document.querySelectorAll('.vw-filter-group[data-group="modalidade"] .vw-chip.active')).map(function (c) { return c.dataset.value; });
  var extras = Array.from(document.querySelectorAll('.vw-filter-group[data-group="extra"] .vw-chip.active')).map(function (c) { return c.dataset.value; });

  var rows = document.querySelectorAll('#studiesTable tbody tr.vw-row');
  var visible = 0;

  rows.forEach(function (row) {
    var haystack = [row.dataset.paciente, row.dataset.accession, row.dataset.study, row.dataset.modalidade, row.dataset.data].join(' ');
    var matchSearch = !search || haystack.indexOf(search) !== -1;
    var matchPeriodo = matchesPeriodo(row.dataset.data, periodo);
    var matchModalidade = modalidades.length === 0 || modalidades.indexOf(row.dataset.modalidade) !== -1;

    var matchExtra = extras.every(function (ex) {
      if (ex === 'com_laudo') return row.dataset.laudo === '1';
      if (ex === 'sem_laudo') return row.dataset.laudo === '0';
      if (ex === 'favoritos') return row.dataset.favorito === '1';
      if (ex === 'urgente') return row.dataset.urgente === '1';
      if (ex === 'comparativos') return row.dataset.comparativo === '1';
      return true;
    });

    var show = matchSearch && matchPeriodo && matchModalidade && matchExtra;
    row.style.display = show ? '' : 'none';
    if (show) visible++;
  });

  var noResults = document.getElementById('noResultsRow');
  if (noResults) noResults.style.display = visible === 0 ? '' : 'none';

  var counter = document.getElementById('resultCount');
  if (counter) counter.textContent = visible + (visible === 1 ? ' estudo' : ' estudos');
}

function toggleFavorito(e, btn) {
  e.stopPropagation();
  var row = btn.closest('tr');
  var active = btn.classList.toggle('active');
  row.dataset.favorito = active ? '1' : '0';
  updateFavCount();
}

function updateFavCount() {
  var n = document.querySelectorAll('#studiesTable tbody tr.vw-row[data-favorito="1"]').length;
  var el = document.getElementById('favStatValue');
  if (el) el.textContent = n;
}

function flashIcon(btn) {
  var icon = btn.querySelector('i');
  if (!icon) return;
  var orig = icon.className;
  icon.className = 'fa-solid fa-check';
  btn.classList.add('vw-flash-ok');
  setTimeout(function () {
    icon.className = orig;
    btn.classList.remove('vw-flash-ok');
  }, 1200);
}

function compartilharEstudo(e, btn, uid) {
  e.stopPropagation();
  var link = window.location.origin + '/viewer?study=' + encodeURIComponent(uid);
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(link).catch(function () {});
  }
  flashIcon(btn);
}

function baixarEstudo(e, btn) {
  e.stopPropagation();
  flashIcon(btn);
}

function formatarDataBr(iso) {
  var parts = iso.split('-');
  return parts[2] + '/' + parts[1] + '/' + parts[0];
}

function openDrawer(row) {
  document.getElementById('drawerPaciente').textContent = row.dataset.pacienteDisplay;
  document.getElementById('drawerModalidade').textContent = row.dataset.modalidade + ' — ' + row.dataset.descricao;
  document.getElementById('drawerData').textContent = formatarDataBr(row.dataset.data) + ' às ' + row.dataset.hora;
  document.getElementById('drawerInstituicao').textContent = row.dataset.instituicao;
  document.getElementById('drawerEquipamento').textContent = row.dataset.equipamento;
  document.getElementById('drawerSeries').textContent = row.dataset.series;
  document.getElementById('drawerImagens').textContent = row.dataset.imagens;
  document.getElementById('drawerTamanho').textContent = row.dataset.tamanho + ' MB';
  document.getElementById('drawerOpenViewer').href = '/viewer?study=' + encodeURIComponent(row.dataset.study);
  document.getElementById('drawerOverlay').classList.add('open');
  document.getElementById('studyDrawer').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeDrawer() {
  var overlay = document.getElementById('drawerOverlay');
  var drawer = document.getElementById('studyDrawer');
  if (overlay) overlay.classList.remove('open');
  if (drawer) drawer.classList.remove('open');
  document.body.style.overflow = '';
}

function openRowMenu(e, btn) {
  e.stopPropagation();
  var row = btn.closest('tr');
  var menu = document.getElementById('rowMenu');
  var uid = row.dataset.study;
  var isFav = row.dataset.favorito === '1';

  menu.innerHTML =
    '<button onclick="copiarUid(\'' + uid + '\')"><i class="fa-solid fa-copy"></i> Copiar Study UID</button>' +
    '<button onclick="verDetalhesDoMenu(\'' + uid + '\')"><i class="fa-solid fa-circle-info"></i> Ver detalhes</button>' +
    '<button onclick="toggleFavoritoFromMenu(\'' + uid + '\')"><i class="fa-solid fa-star"></i> ' + (isFav ? 'Remover dos favoritos' : 'Adicionar aos favoritos') + '</button>';

  var rect = btn.getBoundingClientRect();
  menu.style.top = (rect.bottom + 6) + 'px';
  menu.style.left = Math.max(8, rect.right - 210) + 'px';
  menu.classList.add('open');
}

function copiarUid(uid) {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(uid).catch(function () {});
  }
  document.getElementById('rowMenu').classList.remove('open');
}

function verDetalhesDoMenu(uid) {
  var row = document.querySelector('#studiesTable tbody tr.vw-row[data-study="' + CSS.escape(uid) + '"]');
  document.getElementById('rowMenu').classList.remove('open');
  if (row) openDrawer(row);
}

function toggleFavoritoFromMenu(uid) {
  var row = document.querySelector('#studiesTable tbody tr.vw-row[data-study="' + CSS.escape(uid) + '"]');
  document.getElementById('rowMenu').classList.remove('open');
  if (!row) return;
  var starBtn = row.querySelector('.vw-fav-btn');
  if (starBtn) starBtn.click();
}
</script>
