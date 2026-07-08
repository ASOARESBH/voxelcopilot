
<!-- ── Aba: Autorização PACS ─────────────────────────────────── -->
<div id="aba-autorizacao" class="config-aba" style="display:none">

  <!-- Stats rápidos -->
  <div class="auth-stats-row">
    <div class="auth-stat-card auth-stat-blue">
      <div class="auth-stat-icon"><i class="fa-solid fa-hospital"></i></div>
      <div class="auth-stat-body">
        <div class="auth-stat-num"><?= $stats['total'] ?? 0 ?></div>
        <div class="auth-stat-label">Unidades vinculadas</div>
      </div>
    </div>
    <div class="auth-stat-card auth-stat-green">
      <div class="auth-stat-icon"><i class="fa-solid fa-circle-check"></i></div>
      <div class="auth-stat-body">
        <div class="auth-stat-num"><?= $stats['ativas'] ?? 0 ?></div>
        <div class="auth-stat-label">Autorizações ativas</div>
      </div>
    </div>
    <div class="auth-stat-card auth-stat-yellow">
      <div class="auth-stat-icon"><i class="fa-solid fa-clock"></i></div>
      <div class="auth-stat-body">
        <div class="auth-stat-num"><?= $stats['pendentes'] ?? 0 ?></div>
        <div class="auth-stat-label">Pendentes</div>
      </div>
    </div>
    <div class="auth-stat-card auth-stat-purple">
      <div class="auth-stat-icon"><i class="fa-solid fa-file-medical"></i></div>
      <div class="auth-stat-body">
        <div class="auth-stat-num"><?= number_format($stats['laudos'] ?? 0) ?></div>
        <div class="auth-stat-label">Laudos enviados</div>
      </div>
    </div>
  </div>

  <!-- Alertas de feedback -->
  <?php if (isset($_GET['sucesso'])): ?>
    <?php $msgs = [
      'vinculo_criado'   => 'Autorização cadastrada com sucesso! Você já pode laudar exames desta unidade.',
      'vinculo_revogado' => 'Autorização revogada. O vínculo com a unidade foi encerrado.',
    ]; ?>
    <div class="alert alert-success" style="margin-bottom:16px;">
      <i class="fa-solid fa-circle-check"></i>
      <?= $msgs[$_GET['sucesso']] ?? 'Operação realizada com sucesso.' ?>
    </div>
  <?php endif; ?>
  <?php if (isset($_GET['erro'])): ?>
    <?php $erros = [
      'campos_obrigatorios'   => 'Preencha o código e o token da unidade.',
      'unidade_nao_encontrada'=> 'Código de unidade não encontrado. Verifique com a clínica.',
      'unidade_suspensa'      => 'Esta unidade está suspensa e não aceita novos vínculos.',
      'ja_vinculado'          => 'Você já possui um vínculo com esta unidade.',
      'id_invalido'           => 'Identificador inválido.',
      'nao_autorizado'        => 'Operação não permitida.',
    ]; ?>
    <div class="alert alert-danger" style="margin-bottom:16px;">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <?= $erros[$_GET['erro']] ?? 'Ocorreu um erro. Tente novamente.' ?>
    </div>
  <?php endif; ?>

  <!-- ── Formulário: Cadastrar nova autorização ── -->
  <div class="card" style="margin-bottom:20px;">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-plus-circle"></i> Vincular nova unidade PACS
      </span>
      <button class="btn btn-ghost btn-sm" type="button" onclick="toggleFormVinculo()" id="btnToggleForm">
        <i class="fa-solid fa-chevron-down" id="iconToggleForm"></i> Cadastrar
      </button>
    </div>
    <div id="formVinculoWrap" style="display:none;">
      <div class="card-body" style="padding:20px 24px;">

        <!-- Explicação do fluxo -->
        <div class="auth-info-box">
          <div class="auth-info-icon"><i class="fa-solid fa-circle-info"></i></div>
          <div class="auth-info-text">
            <strong>Como funciona a integração?</strong>
            A clínica ou hospital que possui o PACS deve fornecer a você um
            <strong>Código de Unidade</strong> e um <strong>Token de Integração</strong>.
            Após o cadastro, o VOXEL Copilot poderá receber estudos DICOM desta unidade
            e enviar laudos estruturados de volta ao PACS automaticamente.
          </div>
        </div>

        <form method="POST" action="/configuracoes/autorizacao/cadastrar" style="margin-top:20px;">
          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label">
                <i class="fa-solid fa-qrcode" style="color:var(--royal);margin-right:4px;"></i>
                Código da Unidade <span style="color:var(--danger);">*</span>
              </label>
              <input
                type="text"
                name="codigo_medico"
                class="form-control"
                placeholder="Ex: HOSP-BH-2025-001"
                required
                autocomplete="off"
              >
              <div class="form-hint">Fornecido pela clínica/hospital que possui o PACS.</div>
            </div>
            <div class="form-group">
              <label class="form-label">
                <i class="fa-solid fa-key" style="color:var(--royal);margin-right:4px;"></i>
                Token de Integração <span style="color:var(--danger);">*</span>
              </label>
              <div style="position:relative;">
                <input
                  type="password"
                  name="token_integracao"
                  id="inputToken"
                  class="form-control"
                  placeholder="Token fornecido pela unidade"
                  required
                  autocomplete="off"
                  style="padding-right:44px;"
                >
                <button type="button" onclick="toggleToken()" title="Mostrar/ocultar token"
                  style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);padding:0;">
                  <i class="fa-solid fa-eye" id="iconToken"></i>
                </button>
              </div>
              <div class="form-hint">Chave de autenticação para comunicação com o PACS.</div>
            </div>
          </div>

          <!-- Modalidades permitidas -->
          <div class="form-group">
            <label class="form-label">
              <i class="fa-solid fa-stethoscope" style="color:var(--royal);margin-right:4px;"></i>
              Modalidades autorizadas
            </label>
            <div class="auth-modalidades-grid">
              <?php foreach ([
                'CT' => 'Tomografia (CT)',
                'MR' => 'Ressonância (MR)',
                'CR' => 'Radiografia (CR)',
                'DX' => 'Digital X-Ray (DX)',
                'PT' => 'PET Scan (PT)',
                'NM' => 'Medicina Nuclear (NM)',
                'US' => 'Ultrassom (US)',
                'MG' => 'Mamografia (MG)',
                'XA' => 'Angiografia (XA)',
                'RF' => 'Fluoroscopia (RF)',
              ] as $cod => $label): ?>
              <label class="auth-mod-check">
                <input type="checkbox" name="modalidades[]" value="<?= $cod ?>" checked>
                <span class="auth-mod-badge"><?= $cod ?></span>
                <span class="auth-mod-label"><?= $label ?></span>
              </label>
              <?php endforeach; ?>
            </div>
            <div class="form-hint">Deixe todas marcadas para aceitar qualquer modalidade.</div>
          </div>

          <div style="display:flex;gap:10px;margin-top:8px;">
            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-link"></i> Vincular Unidade
            </button>
            <button type="button" class="btn btn-ghost" onclick="toggleFormVinculo()">
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ── Tabela de autorizações ── -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-hospital"></i> Unidades autorizadas
      </span>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Instituição</th>
            <th>CNPJ</th>
            <th>Cidade / Estado</th>
            <th>PACS</th>
            <th>Modalidades</th>
            <th>Laudos</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($autorizacoes)): ?>
          <tr>
            <td colspan="9">
              <div class="empty-state">
                <div class="empty-state-icon">
                  <i class="fa-solid fa-hospital-user"></i>
                </div>
                <h3>Nenhuma unidade vinculada</h3>
                <p>Clique em "Cadastrar" acima e informe o código e token fornecidos pela clínica.</p>
              </div>
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($autorizacoes as $a): ?>
          <tr>
            <!-- ID -->
            <td>
              <span style="font-family:'Courier New',monospace;font-size:.72rem;color:var(--muted);background:var(--gray-100);padding:2px 7px;border-radius:4px;border:1px solid var(--border);">
                #<?= $a->unidade_id ?>
              </span>
            </td>

            <!-- Instituição -->
            <td>
              <div style="font-weight:600;color:var(--gray-800);font-size:.84rem;">
                <?= htmlspecialchars($a->nome_instituicao) ?>
              </div>
              <div style="font-size:.72rem;color:var(--muted);margin-top:2px;font-family:monospace;">
                <i class="fa-solid fa-qrcode" style="font-size:.65rem;"></i>
                <?= htmlspecialchars($a->codigo_unidade) ?>
              </div>
            </td>

            <!-- CNPJ -->
            <td style="font-size:.78rem;font-family:'Courier New',monospace;color:var(--gray-600);letter-spacing:.03em;">
              <?= htmlspecialchars($a->cnpj) ?>
            </td>

            <!-- Cidade / Estado -->
            <td style="font-size:.82rem;color:var(--text-2);">
              <i class="fa-solid fa-location-dot" style="color:var(--muted);font-size:.72rem;margin-right:3px;"></i>
              <?= htmlspecialchars($a->cidade) ?>/<?= htmlspecialchars($a->estado) ?>
            </td>

            <!-- PACS -->
            <td>
              <?php if ($a->pacs_tipo): ?>
              <span style="font-size:.72rem;font-weight:600;background:var(--blue-50);color:var(--royal);border:1px solid var(--blue-100);padding:2px 8px;border-radius:5px;">
                <?= htmlspecialchars($a->pacs_tipo) ?>
              </span>
              <?php if ($a->pacs_ae_title): ?>
              <div style="font-size:.67rem;color:var(--muted);margin-top:3px;font-family:monospace;">
                AE: <?= htmlspecialchars($a->pacs_ae_title) ?>
              </div>
              <?php endif; ?>
              <?php else: ?>
              <span style="color:var(--muted);font-size:.78rem;">—</span>
              <?php endif; ?>
            </td>

            <!-- Modalidades -->
            <td>
              <?php
                $mods = $a->modalidades_permitidas ? explode(',', $a->modalidades_permitidas) : [];
                if (empty($mods)): ?>
                <span style="font-size:.72rem;color:var(--muted);">Todas</span>
              <?php else: ?>
                <div style="display:flex;flex-wrap:wrap;gap:3px;">
                  <?php foreach (array_slice($mods, 0, 4) as $m): ?>
                  <span style="font-size:.67rem;font-weight:700;background:var(--gray-100);color:var(--gray-700);border:1px solid var(--border);padding:1px 6px;border-radius:4px;"><?= htmlspecialchars(trim($m)) ?></span>
                  <?php endforeach; ?>
                  <?php if (count($mods) > 4): ?>
                  <span style="font-size:.67rem;color:var(--muted);">+<?= count($mods)-4 ?></span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </td>

            <!-- Laudos -->
            <td>
              <span class="badge badge-ativo" style="font-size:.72rem;">
                <?= number_format($a->total_laudos) ?>
              </span>
            </td>

            <!-- Status -->
            <td>
              <?php
                $statusMap = [
                  'ativo'    => ['badge-ativo',    'Autorizado'],
                  'pendente' => ['badge-pendente', 'Pendente'],
                  'inativo'  => ['badge-inativo',  'Inativo'],
                  'revogado' => ['badge-suspenso', 'Revogado'],
                ];
                [$badgeClass, $badgeLabel] = $statusMap[$a->auth_status] ?? ['badge-inativo', ucfirst($a->auth_status)];
              ?>
              <span class="badge <?= $badgeClass ?>">
                <?= $badgeLabel ?>
              </span>
            </td>

            <!-- Ações -->
            <td>
              <div style="display:flex;gap:5px;align-items:center;">
                <a href="/configuracoes/autorizacao/<?= $a->id ?>"
                   class="btn btn-ghost btn-xs"
                   title="Ver detalhes e tags DICOM">
                  <i class="fa-solid fa-eye"></i>
                </a>
                <?php if ($a->auth_status === 'ativo' || $a->auth_status === 'pendente'): ?>
                <button
                  class="btn btn-danger btn-xs"
                  title="Revogar autorização"
                  onclick="confirmarRevogacao(<?= $a->id ?>, '<?= htmlspecialchars(addslashes($a->nome_instituicao)) ?>')">
                  <i class="fa-solid fa-ban"></i>
                </button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── Referência de Tags DICOM ── -->
  <div class="card" style="margin-top:20px;">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-tags"></i> Tags DICOM utilizadas na integração
      </span>
      <button class="btn btn-ghost btn-sm" type="button" onclick="toggleDicomRef()" id="btnDicomRef">
        <i class="fa-solid fa-chevron-down"></i> Ver referência
      </button>
    </div>
    <div id="dicomRefWrap" style="display:none;">
      <div class="card-body" style="padding:0;">
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Tag DICOM</th>
                <th>Atributo</th>
                <th>Módulo</th>
                <th>Descrição</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ([
                ['(0008,0016)', 'SOPClassUID',                  'SOP Common',       'UID da classe SOP — 1.2.840.10008.5.1.4.1.1.88.33 para SR'],
                ['(0008,0018)', 'SOPInstanceUID',               'SOP Common',       'UID único da instância do laudo, gerado pelo Copilot'],
                ['(0008,0020)', 'StudyDate',                    'General Study',    'Data do estudo no formato YYYYMMDD'],
                ['(0008,0023)', 'ContentDate',                  'General Image',    'Data de criação do laudo'],
                ['(0008,0050)', 'AccessionNumber',              'General Study',    'Número de acesso do exame no RIS/HIS'],
                ['(0008,0060)', 'Modality',                     'General Series',   'Modalidade: CT, MR, CR, DX, PT, NM, US, MG...'],
                ['(0008,0070)', 'Manufacturer',                 'General Equipment','Fabricante do equipamento de aquisição'],
                ['(0008,0080)', 'InstitutionName',              'General Equipment','Nome da instituição — preenchido com dados da unidade'],
                ['(0008,0090)', 'ReferringPhysicianName',       'General Study',    'Médico solicitante — formato Sobrenome^Nome'],
                ['(0008,1010)', 'StationName',                  'General Equipment','Nome da estação de trabalho'],
                ['(0008,1030)', 'StudyDescription',             'General Study',    'Descrição do estudo (ex: TC Tórax com contraste)'],
                ['(0008,1048)', 'PhysicianOfRecord',            'General Study',    'Médico responsável pelo laudo'],
                ['(0008,1060)', 'NameOfPhysiciansReadingStudy', 'General Study',    'Médico laudador — formato Sobrenome^Nome'],
                ['(0010,0010)', 'PatientName',                  'Patient',          'Nome do paciente — formato Sobrenome^Nome'],
                ['(0010,0020)', 'PatientID',                    'Patient',          'ID do paciente no sistema da unidade'],
                ['(0010,0030)', 'PatientBirthDate',             'Patient',          'Data de nascimento YYYYMMDD'],
                ['(0010,0040)', 'PatientSex',                   'Patient',          'Sexo: M (masculino), F (feminino), O (outro)'],
                ['(0020,000D)', 'StudyInstanceUID',             'General Study',    'UID único do estudo — gerado pelo PACS'],
                ['(0020,000E)', 'SeriesInstanceUID',            'General Series',   'UID único da série do laudo SR'],
                ['(0020,0011)', 'SeriesNumber',                 'General Series',   'Número da série'],
                ['(0020,0013)', 'InstanceNumber',               'General Image',    'Número da instância'],
                ['(0040,A491)', 'CompletionFlag',               'SR Document',      'COMPLETE = laudo finalizado, PARTIAL = parcial'],
                ['(0040,A493)', 'VerificationFlag',             'SR Document',      'VERIFIED = assinado pelo médico, UNVERIFIED = rascunho'],
              ] as [$tag, $attr, $modulo, $desc]): ?>
              <tr>
                <td style="font-family:'Courier New',monospace;font-size:.72rem;color:var(--royal);white-space:nowrap;"><?= $tag ?></td>
                <td style="font-size:.78rem;font-weight:600;color:var(--gray-800);"><?= $attr ?></td>
                <td><span style="font-size:.67rem;background:var(--gray-100);border:1px solid var(--border);padding:2px 7px;border-radius:4px;color:var(--gray-600);"><?= $modulo ?></span></td>
                <td style="font-size:.78rem;color:var(--text-2);"><?= $desc ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div><!-- /aba-autorizacao -->

<!-- Modal de confirmação de revogação -->
<div id="modalRevogar" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:12px;padding:28px;max-width:440px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
      <div style="width:44px;height:44px;border-radius:10px;background:var(--danger-bg);border:1px solid var(--danger-border);display:flex;align-items:center;justify-content:center;color:var(--danger);font-size:1.1rem;flex-shrink:0;">
        <i class="fa-solid fa-ban"></i>
      </div>
      <div>
        <div style="font-weight:700;font-size:.95rem;color:var(--gray-900);">Revogar autorização</div>
        <div style="font-size:.8rem;color:var(--muted);" id="modalRevogarNome"></div>
      </div>
    </div>
    <p style="font-size:.84rem;color:var(--text-2);margin-bottom:16px;line-height:1.6;">
      Ao revogar, você não poderá mais receber exames nem enviar laudos para esta unidade.
      Esta ação pode ser revertida entrando em contato com a clínica para um novo vínculo.
    </p>
    <form method="POST" action="/configuracoes/autorizacao/revogar">
      <input type="hidden" name="autorizacao_id" id="modalRevogarId">
      <div class="form-group">
        <label class="form-label">Motivo (opcional)</label>
        <input type="text" name="motivo" class="form-control" placeholder="Ex: Encerramento de contrato">
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
        <button type="button" class="btn btn-ghost" onclick="fecharModal()">Cancelar</button>
        <button type="submit" class="btn btn-danger">
          <i class="fa-solid fa-ban"></i> Confirmar Revogação
        </button>
      </div>
    </form>
  </div>
</div>

<style>
/* ── Stats da aba Autorização ── */
.auth-stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 20px;
}
.auth-stat-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 16px 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: var(--shadow-xs);
  position: relative;
  overflow: hidden;
}
.auth-stat-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
}
.auth-stat-blue::before   { background: linear-gradient(90deg, var(--royal), var(--blue-400)); }
.auth-stat-green::before  { background: linear-gradient(90deg, var(--success), #34d399); }
.auth-stat-yellow::before { background: linear-gradient(90deg, var(--warning), #fbbf24); }
.auth-stat-purple::before { background: linear-gradient(90deg, #7c3aed, #a78bfa); }

.auth-stat-icon {
  width: 40px; height: 40px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: .95rem;
  flex-shrink: 0;
}
.auth-stat-blue   .auth-stat-icon { background: var(--blue-50);    color: var(--royal); }
.auth-stat-green  .auth-stat-icon { background: var(--success-bg); color: var(--success); }
.auth-stat-yellow .auth-stat-icon { background: var(--warning-bg); color: var(--warning); }
.auth-stat-purple .auth-stat-icon { background: #f5f3ff;           color: #7c3aed; }

.auth-stat-num {
  font-family: var(--font-head);
  font-size: 1.6rem;
  font-weight: 800;
  color: var(--gray-900);
  line-height: 1;
  letter-spacing: -.03em;
}
.auth-stat-label {
  font-size: .67rem;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: .07em;
  font-weight: 500;
  margin-top: 3px;
}

/* ── Info box ── */
.auth-info-box {
  display: flex;
  gap: 12px;
  background: var(--info-bg);
  border: 1px solid var(--info-border);
  border-radius: var(--radius-sm);
  padding: 13px 16px;
}
.auth-info-icon { color: var(--info); font-size: 1rem; flex-shrink: 0; margin-top: 1px; }
.auth-info-text { font-size: .82rem; color: #1e40af; line-height: 1.6; }
.auth-info-text strong { color: #1e3a8a; }

/* ── Hint de campo ── */
.form-hint {
  font-size: .72rem;
  color: var(--muted);
  margin-top: 4px;
  line-height: 1.4;
}

/* ── Grid de modalidades ── */
.auth-modalidades-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 6px;
}
.auth-mod-check {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  padding: 5px 10px;
  border: 1.5px solid var(--border);
  border-radius: var(--radius-xs);
  background: var(--white);
  transition: all .15s;
  font-size: .78rem;
}
.auth-mod-check:hover { border-color: var(--blue-200); background: var(--blue-50); }
.auth-mod-check input[type=checkbox] { display: none; }
.auth-mod-check input:checked ~ .auth-mod-badge { background: var(--royal); color: var(--white); border-color: var(--royal); }
.auth-mod-check input:checked ~ .auth-mod-label { color: var(--royal); font-weight: 600; }
.auth-mod-check input:checked { /* parent */ }
.auth-mod-badge {
  font-size: .67rem;
  font-weight: 700;
  background: var(--gray-100);
  color: var(--gray-700);
  border: 1px solid var(--border);
  padding: 2px 6px;
  border-radius: 4px;
  transition: all .15s;
}
.auth-mod-label { color: var(--gray-600); }

/* Responsividade */
@media (max-width: 900px) {
  .auth-stats-row { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 600px) {
  .auth-stats-row { grid-template-columns: 1fr; }
}
</style>

<script>
function toggleFormVinculo() {
  const wrap = document.getElementById('formVinculoWrap');
  const icon = document.getElementById('iconToggleForm');
  const btn  = document.getElementById('btnToggleForm');
  if (wrap.style.display === 'none') {
    wrap.style.display = 'block';
    icon.className = 'fa-solid fa-chevron-up';
    btn.innerHTML  = '<i class="fa-solid fa-chevron-up"></i> Fechar';
  } else {
    wrap.style.display = 'none';
    icon.className = 'fa-solid fa-chevron-down';
    btn.innerHTML  = '<i class="fa-solid fa-chevron-down"></i> Cadastrar';
  }
}

function toggleToken() {
  const inp  = document.getElementById('inputToken');
  const icon = document.getElementById('iconToken');
  if (inp.type === 'password') {
    inp.type = 'text';
    icon.className = 'fa-solid fa-eye-slash';
  } else {
    inp.type = 'password';
    icon.className = 'fa-solid fa-eye';
  }
}

function toggleDicomRef() {
  const wrap = document.getElementById('dicomRefWrap');
  const btn  = document.getElementById('btnDicomRef');
  if (wrap.style.display === 'none') {
    wrap.style.display = 'block';
    btn.innerHTML = '<i class="fa-solid fa-chevron-up"></i> Ocultar';
  } else {
    wrap.style.display = 'none';
    btn.innerHTML = '<i class="fa-solid fa-chevron-down"></i> Ver referência';
  }
}

function confirmarRevogacao(id, nome) {
  document.getElementById('modalRevogarId').value = id;
  document.getElementById('modalRevogarNome').textContent = nome;
  document.getElementById('modalRevogar').style.display = 'flex';
}

function fecharModal() {
  document.getElementById('modalRevogar').style.display = 'none';
}

// Checkboxes de modalidade — estilo visual
document.querySelectorAll('.auth-mod-check input[type=checkbox]').forEach(cb => {
  cb.addEventListener('change', function() {
    const label = this.closest('.auth-mod-check');
    if (this.checked) {
      label.style.borderColor = 'var(--royal)';
      label.style.background  = 'var(--blue-50)';
    } else {
      label.style.borderColor = 'var(--border)';
      label.style.background  = 'var(--white)';
    }
  });
  // Estado inicial
  if (cb.checked) {
    const label = cb.closest('.auth-mod-check');
    label.style.borderColor = 'var(--royal)';
    label.style.background  = 'var(--blue-50)';
  }
});

// Abrir formulário automaticamente se veio de erro
<?php if (isset($_GET['erro'])): ?>
document.addEventListener('DOMContentLoaded', () => {
  const wrap = document.getElementById('formVinculoWrap');
  if (wrap) wrap.style.display = 'block';
});
<?php endif; ?>
</script>
