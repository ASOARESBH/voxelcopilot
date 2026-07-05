<div class="page-header">
    <div class="page-header-left">
        <h1>Novo Laudo</h1>
        <p>Selecione o estudo do PACS ou informe manualmente</p>
    </div>
    <a href="/workspace" class="btn btn-ghost">
        <i class="fa-solid fa-arrow-left"></i> Voltar
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

    <!-- Busca no PACS -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-server"></i> Buscar no PACS</div>
        </div>
        <div class="card-body">
            <?php if (!$pacsConfig || !$pacsConfig->pacs_api_url): ?>
            <div class="alert alert-warning">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div>PACS não configurado. <a href="/pacs" class="auth-link">Configure a conexão</a> para buscar estudos automaticamente.</div>
            </div>
            <?php else: ?>
            <div class="form-group">
                <label class="form-label">Buscar Paciente</label>
                <div style="display:flex;gap:8px;">
                    <input type="text" id="pacs-search" class="form-control" placeholder="Nome do paciente ou ID...">
                    <button class="btn btn-secondary" onclick="buscarPacs()">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </div>
            <div id="pacs-results" style="display:none;">
                <div style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;">Estudos encontrados</div>
                <div id="pacs-list" style="display:flex;flex-direction:column;gap:8px;max-height:300px;overflow-y:auto;"></div>
            </div>
            <div id="pacs-loading" style="display:none;text-align:center;padding:20px;color:var(--muted);">
                <i class="fa-solid fa-spinner fa-spin"></i> Buscando...
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Formulário manual -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-keyboard"></i> Informar Manualmente</div>
        </div>
        <div class="card-body">
            <form method="POST" action="/workspace/novo" id="form-novo-laudo">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="form-group">
                    <label class="form-label">Study UID <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="study_uid" id="f-study-uid" class="form-control"
                        placeholder="1.2.840.10008.5.1.4.1.1.2..." required>
                </div>

                <div class="form-group">
                    <label class="form-label">Nome do Paciente</label>
                    <input type="text" name="patient_nome" id="f-patient-nome" class="form-control"
                        placeholder="Nome completo do paciente">
                </div>

                <div class="form-group">
                    <label class="form-label">ID do Paciente</label>
                    <input type="text" name="patient_uid" id="f-patient-uid" class="form-control"
                        placeholder="ID / Prontuário">
                </div>

                <div class="form-group">
                    <label class="form-label">Modalidade</label>
                    <select name="modalidade" id="f-modalidade" class="form-select">
                        <option value="">Selecionar...</option>
                        <?php foreach (['TC','RM','RX','US','PET','MAMOGRAFIA','DENSITOMETRIA','CINTILOGRAFIA','ANGIOGRAFIA','FLUOROSCOPIA'] as $m): ?>
                        <option value="<?= $m ?>"><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (!empty($templates)): ?>
                <div class="form-group">
                    <label class="form-label">Template Inicial</label>
                    <select name="template_id" class="form-select">
                        <option value="">Sem template (em branco)</option>
                        <?php foreach ($templates as $t): ?>
                        <option value="<?= (int)$t->id ?>">
                            <?= htmlspecialchars($t->nome) ?>
                            <?= $t->modalidade ? "({$t->modalidade})" : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;">
                    <i class="fa-solid fa-plus"></i> Criar Laudo
                </button>
            </form>
        </div>
    </div>

</div>

<script>
function buscarPacs() {
    const q = document.getElementById('pacs-search').value.trim();
    if (!q) return;

    document.getElementById('pacs-loading').style.display = 'block';
    document.getElementById('pacs-results').style.display = 'none';

    fetch('/api/pacs/buscar?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(function(res) {
            document.getElementById('pacs-loading').style.display = 'none';
            const list = document.getElementById('pacs-list');
            list.innerHTML = '';

            if (!res.ok || !res.data || res.data.length === 0) {
                list.innerHTML = '<p style="color:var(--muted);font-size:.82rem;text-align:center;padding:16px;">Nenhum estudo encontrado.</p>';
            } else {
                res.data.forEach(function(study) {
                    const item = document.createElement('div');
                    item.style.cssText = 'background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:8px;padding:12px;cursor:pointer;transition:all .18s;';
                    item.innerHTML = `
                        <div style="font-weight:600;color:#e2e8f0;font-size:.85rem;">${study.patient_name || 'Paciente não identificado'}</div>
                        <div style="font-size:.72rem;color:var(--muted);margin-top:3px;">
                            ${study.modality || '—'} · ${study.study_date || '—'}
                        </div>
                        <div style="font-size:.65rem;color:var(--muted-2);font-family:monospace;margin-top:3px;">${(study.study_uid || '').substring(0,32)}...</div>
                    `;
                    item.addEventListener('mouseover', function() { this.style.borderColor = 'rgba(14,165,233,.3)'; this.style.background = 'rgba(14,165,233,.06)'; });
                    item.addEventListener('mouseout',  function() { this.style.borderColor = 'rgba(255,255,255,.07)'; this.style.background = 'rgba(255,255,255,.03)'; });
                    item.addEventListener('click', function() {
                        document.getElementById('f-study-uid').value    = study.study_uid || '';
                        document.getElementById('f-patient-nome').value = study.patient_name || '';
                        document.getElementById('f-patient-uid').value  = study.patient_id || '';
                        if (study.modality) {
                            const sel = document.getElementById('f-modalidade');
                            for (let i = 0; i < sel.options.length; i++) {
                                if (sel.options[i].value === study.modality) { sel.selectedIndex = i; break; }
                            }
                        }
                        // Scroll para o formulário
                        document.getElementById('form-novo-laudo').scrollIntoView({ behavior: 'smooth' });
                    });
                    list.appendChild(item);
                });
            }

            document.getElementById('pacs-results').style.display = 'block';
        })
        .catch(function() {
            document.getElementById('pacs-loading').style.display = 'none';
            document.getElementById('pacs-list').innerHTML = '<p style="color:var(--danger);font-size:.82rem;text-align:center;padding:16px;">Erro ao conectar ao PACS.</p>';
            document.getElementById('pacs-results').style.display = 'block';
        });
}

document.getElementById('pacs-search')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); buscarPacs(); }
});
</script>
