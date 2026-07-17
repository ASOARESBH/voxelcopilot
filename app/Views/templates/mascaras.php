<?php
$totalBib     = count($biblioteca ?? []);
$totalImport  = count($importados ?? []);
$modAtiva     = $modalidade ?? '';
$buscaAtiva   = $busca ?? '';
?>

<!-- ── CABEÇALHO ─────────────────────────────────────────── -->
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Máscaras de Laudo</h1>
        <p class="page-subtitle">Biblioteca de máscaras estruturadas e importação de DOCX</p>
    </div>
    <div class="page-header-actions">
        <a href="/templates" class="btn btn-ghost">
            <i class="fa-solid fa-arrow-left"></i> Templates
        </a>
        <button class="btn btn-primary" onclick="abrirModalUpload()">
            <i class="fa-solid fa-file-import"></i> Importar DOCX
        </button>
    </div>
</div>

<!-- ── STATS ─────────────────────────────────────────────── -->
<div class="stats-row" style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff;color:#1a56db;"><i class="fa-solid fa-book-medical"></i></div>
        <div class="stat-body">
            <div class="stat-value"><?= $totalBib ?></div>
            <div class="stat-label">Na Biblioteca</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;color:#16a34a;"><i class="fa-solid fa-check-circle"></i></div>
        <div class="stat-body">
            <div class="stat-value"><?= $totalImport ?></div>
            <div class="stat-label">Importadas por Você</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fefce8;color:#ca8a04;"><i class="fa-solid fa-clock-rotate-left"></i></div>
        <div class="stat-body">
            <div class="stat-value"><?= count($historico ?? []) ?></div>
            <div class="stat-label">Importações Recentes</div>
        </div>
    </div>
</div>

<!-- ── ABAS ──────────────────────────────────────────────── -->
<div class="tab-nav" style="display:flex;gap:4px;border-bottom:2px solid #e2e8f0;margin-bottom:20px;">
    <button class="tab-btn active" data-tab="biblioteca" onclick="trocarAba(this,'biblioteca')">
        <i class="fa-solid fa-book-medical"></i> Biblioteca
        <?php if ($totalBib): ?><span class="badge badge-ativo" style="margin-left:6px;"><?= $totalBib ?></span><?php endif; ?>
    </button>
    <button class="tab-btn" data-tab="importadas" onclick="trocarAba(this,'importadas')">
        <i class="fa-solid fa-file-check"></i> Minhas Importadas
        <?php if ($totalImport): ?><span class="badge badge-ativo" style="margin-left:6px;"><?= $totalImport ?></span><?php endif; ?>
    </button>
    <button class="tab-btn" data-tab="historico" onclick="trocarAba(this,'historico')">
        <i class="fa-solid fa-history"></i> Histórico
    </button>
</div>

<!-- ── ABA: BIBLIOTECA ───────────────────────────────────── -->
<div id="tab-biblioteca" class="tab-content">

    <!-- Filtros -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-body" style="padding:16px;">
            <form method="GET" action="/templates/mascaras" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Buscar máscara</label>
                    <div class="search-wrap">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text" name="busca" class="form-control search-input"
                               value="<?= htmlspecialchars($buscaAtiva) ?>"
                               placeholder="TC Tórax, Abdome, Crânio...">
                    </div>
                </div>
                <div>
                    <label class="form-label">Modalidade</label>
                    <select name="modalidade" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach (['TC','RM','RX','US','PET','MG','NM'] as $m): ?>
                        <option value="<?= $m ?>" <?= $modAtiva === $m ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">
                    <i class="fa-solid fa-filter"></i> Filtrar
                </button>
                <?php if ($buscaAtiva || $modAtiva): ?>
                <a href="/templates/mascaras" class="btn btn-ghost">
                    <i class="fa-solid fa-xmark"></i> Limpar
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Grid de máscaras da biblioteca -->
    <?php if (empty($biblioteca)): ?>
    <div class="empty-state">
        <i class="fa-solid fa-book-medical"></i>
        <h3>Biblioteca vazia</h3>
        <p>A biblioteca ainda não foi populada. Importe um DOCX ou peça ao administrador para executar o seed.</p>
        <button class="btn btn-primary" onclick="abrirModalUpload()">
            <i class="fa-solid fa-file-import"></i> Importar DOCX
        </button>
    </div>
    <?php else: ?>
    <div class="mascaras-grid" id="gridBiblioteca">
        <?php foreach ($biblioteca as $m): ?>
        <?php
            $id    = is_array($m) ? $m['id']           : $m->id;
            $nome  = is_array($m) ? $m['nome_amigavel'] : $m->nome_amigavel;
            $mod   = is_array($m) ? $m['modalidade']    : $m->modalidade;
            $esp   = is_array($m) ? ($m['especialidade'] ?? '') : ($m->especialidade ?? '');
            $tags  = is_array($m) ? ($m['tags'] ?? '')  : ($m->tags ?? '');
        ?>
        <div class="mascara-card" data-id="<?= $id ?>" data-nome="<?= htmlspecialchars($nome) ?>">
            <div class="mascara-card-top">
                <span class="badge badge-mod"><?= htmlspecialchars($mod) ?></span>
                <button class="btn-icon-ghost" title="Pré-visualizar" onclick="previewMascara(<?= $id ?>, this)">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            <h4 class="mascara-nome"><?= htmlspecialchars($nome) ?></h4>
            <?php if ($esp): ?>
            <div class="mascara-esp"><i class="fa-solid fa-stethoscope"></i> <?= htmlspecialchars($esp) ?></div>
            <?php endif; ?>
            <?php if ($tags): ?>
            <div class="mascara-tags">
                <?php foreach (array_slice(explode(',', $tags), 0, 3) as $tag): ?>
                <span class="tag-chip"><?= htmlspecialchars(trim($tag)) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="mascara-actions">
                <button class="btn btn-sm btn-ghost" onclick="previewMascara(<?= $id ?>, this)">
                    <i class="fa-solid fa-eye"></i> Ver
                </button>
                <button class="btn btn-sm btn-primary" onclick="importarDaBiblioteca(<?= $id ?>, '<?= htmlspecialchars(addslashes($nome)) ?>', this)">
                    <i class="fa-solid fa-plus"></i> Importar
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- ── ABA: MINHAS IMPORTADAS ────────────────────────────── -->
<div id="tab-importadas" class="tab-content" style="display:none;">
    <?php if (empty($importados)): ?>
    <div class="empty-state">
        <i class="fa-solid fa-file-circle-plus"></i>
        <h3>Nenhuma máscara importada</h3>
        <p>Importe máscaras da biblioteca ou faça upload de um arquivo DOCX.</p>
        <button class="btn btn-primary" onclick="trocarAba(document.querySelector('[data-tab=biblioteca]'),'biblioteca')">
            <i class="fa-solid fa-book-medical"></i> Ver Biblioteca
        </button>
    </div>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Modalidade</th>
                    <th>Origem</th>
                    <th>Usos</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($importados as $t): ?>
                <?php
                    $tid   = is_array($t) ? $t['id']           : $t->id;
                    $tnome = is_array($t) ? $t['nome_amigavel'] : $t->nome_amigavel;
                    $tmod  = is_array($t) ? $t['modalidade']    : $t->modalidade;
                    $torig = is_array($t) ? ($t['origem_arquivo'] ?? '') : ($t->origem_arquivo ?? '');
                    $tusos = is_array($t) ? ($t['uso_count'] ?? 0) : ($t->uso_count ?? 0);
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($tnome) ?></strong></td>
                    <td><span class="badge badge-mod"><?= htmlspecialchars($tmod) ?></span></td>
                    <td><span style="font-size:.75rem;color:var(--muted);"><?= htmlspecialchars($torig) ?></span></td>
                    <td><?= (int)$tusos ?></td>
                    <td>
                        <a href="/templates/<?= $tid ?>/editar" class="btn btn-sm btn-ghost">
                            <i class="fa-solid fa-pen"></i> Editar
                        </a>
                        <a href="/workspace/novo?template=<?= $tid ?>" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-play"></i> Usar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- ── ABA: HISTÓRICO ────────────────────────────────────── -->
<div id="tab-historico" class="tab-content" style="display:none;">
    <?php if (empty($historico)): ?>
    <div class="empty-state">
        <i class="fa-solid fa-history"></i>
        <h3>Sem histórico de importações</h3>
        <p>Quando você importar um DOCX, o histórico aparecerá aqui.</p>
    </div>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Arquivo</th>
                    <th>Total</th>
                    <th>Importadas</th>
                    <th>Status</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historico as $h): ?>
                <?php
                    $hnome = is_array($h) ? $h['arquivo_nome'] : $h->arquivo_nome;
                    $htot  = is_array($h) ? $h['total_mascaras'] : $h->total_mascaras;
                    $himp  = is_array($h) ? $h['importadas'] : $h->importadas;
                    $hst   = is_array($h) ? $h['status'] : $h->status;
                    $hdt   = is_array($h) ? $h['created_at'] : $h->created_at;
                    $stClass = $hst === 'concluido' ? 'badge-ativo' : ($hst === 'processando' ? 'badge-pendente' : 'badge-inativo');
                ?>
                <tr>
                    <td><i class="fa-solid fa-file-word" style="color:#2563eb;margin-right:6px;"></i><?= htmlspecialchars($hnome) ?></td>
                    <td><?= (int)$htot ?></td>
                    <td><?= (int)$himp ?></td>
                    <td><span class="badge <?= $stClass ?>"><?= htmlspecialchars($hst) ?></span></td>
                    <td style="font-size:.8rem;color:var(--muted);"><?= htmlspecialchars(substr($hdt, 0, 16)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL: UPLOAD DOCX
══════════════════════════════════════════════════════════ -->
<div id="modalUpload" class="modal-overlay" style="display:none;" onclick="fecharModalUpload(event)">
    <div class="modal-box" style="max-width:680px;" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3><i class="fa-solid fa-file-import"></i> Importar Máscaras de DOCX</h3>
            <button class="btn-icon-ghost" onclick="fecharModalUpload()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">

            <!-- Passo 1: Upload -->
            <div id="passo1">
                <div class="upload-zone" id="uploadZone"
                     ondragover="dragOver(event)" ondragleave="dragLeave(event)" ondrop="dropFile(event)"
                     onclick="document.getElementById('fileInput').click()">
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size:2.5rem;color:#1a56db;margin-bottom:12px;"></i>
                    <p style="font-weight:600;color:#1e293b;margin:0 0 4px;">Arraste o arquivo DOCX aqui</p>
                    <p style="font-size:.82rem;color:var(--muted);margin:0;">ou clique para selecionar — máximo 10MB</p>
                    <input type="file" id="fileInput" accept=".docx,.doc" style="display:none;" onchange="arquivoSelecionado(this)">
                </div>
                <div id="arquivoInfo" style="display:none;margin-top:12px;" class="alert alert-info">
                    <i class="fa-solid fa-file-word"></i>
                    <span id="arquivoNome"></span>
                    <button class="btn btn-sm btn-ghost" style="margin-left:auto;" onclick="limparArquivo()">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div id="uploadProgress" style="display:none;margin-top:12px;">
                    <div style="display:flex;align-items:center;gap:10px;color:var(--muted);font-size:.85rem;">
                        <i class="fa-solid fa-spinner fa-spin"></i> Analisando o arquivo...
                    </div>
                </div>
            </div>

            <!-- Passo 2: Seleção de máscaras -->
            <div id="passo2" style="display:none;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                    <div>
                        <strong id="totalEncontradas"></strong>
                        <span style="font-size:.82rem;color:var(--muted);margin-left:8px;">Selecione as que deseja importar</span>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button class="btn btn-sm btn-ghost" onclick="selecionarTodas(true)">Selecionar todas</button>
                        <button class="btn btn-sm btn-ghost" onclick="selecionarTodas(false)">Desmarcar todas</button>
                    </div>
                </div>

                <!-- Filtro rápido -->
                <input type="text" id="filtroMascaras" class="form-control" placeholder="Filtrar por nome..."
                       style="margin-bottom:12px;" oninput="filtrarListaMascaras(this.value)">

                <div id="listaMascaras" style="max-height:340px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:8px;"></div>

                <div id="resumoSelecao" style="margin-top:12px;padding:10px 14px;background:#f0fdf4;border-radius:8px;font-size:.85rem;color:#16a34a;display:none;">
                    <i class="fa-solid fa-check-circle"></i>
                    <span id="textoResumo"></span>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="fecharModalUpload()">Cancelar</button>
            <button id="btnAnalisar" class="btn btn-secondary" onclick="analisarDocx()" disabled>
                <i class="fa-solid fa-magnifying-glass"></i> Analisar
            </button>
            <button id="btnImportar" class="btn btn-primary" onclick="confirmarImportacao()" style="display:none;">
                <i class="fa-solid fa-file-import"></i> Importar Selecionadas
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL: PREVIEW DE MÁSCARA
══════════════════════════════════════════════════════════ -->
<div id="modalPreview" class="modal-overlay" style="display:none;" onclick="fecharPreview(event)">
    <div class="modal-box" style="max-width:780px;max-height:85vh;overflow-y:auto;" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 id="previewTitulo"><i class="fa-solid fa-file-lines"></i> Pré-visualização</h3>
            <button class="btn-icon-ghost" onclick="fecharPreview()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="previewBody">
            <div style="text-align:center;padding:40px;color:var(--muted);">
                <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="fecharPreview()">Fechar</button>
            <button class="btn btn-primary" id="btnImportarPreview" onclick="">
                <i class="fa-solid fa-plus"></i> Importar esta Máscara
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     TOAST
══════════════════════════════════════════════════════════ -->
<div id="toast" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:none;max-width:360px;">
    <div id="toastInner" class="alert" style="box-shadow:0 4px 20px rgba(0,0,0,.15);"></div>
</div>

<!-- ══════════════════════════════════════════════════════════
     CSS
══════════════════════════════════════════════════════════ -->
<style>
/* Abas */
.tab-btn { background:none; border:none; padding:10px 18px; font-size:.88rem; font-weight:500; color:var(--muted); cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; display:inline-flex; align-items:center; gap:6px; transition:all .18s; }
.tab-btn:hover { color:var(--blue-600); }
.tab-btn.active { color:var(--blue-600); border-bottom-color:var(--blue-600); font-weight:600; }

/* Grid de máscaras */
.mascaras-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; }
.mascara-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:18px; display:flex; flex-direction:column; gap:10px; transition:box-shadow .2s,border-color .2s; }
.mascara-card:hover { box-shadow:0 4px 18px rgba(26,86,219,.1); border-color:#bfdbfe; }
.mascara-card-top { display:flex; align-items:center; justify-content:space-between; }
.badge-mod { background:#eff6ff; color:#1a56db; padding:3px 10px; border-radius:6px; font-size:11px; font-weight:700; }
.mascara-nome { margin:0; font-size:.9rem; font-weight:600; color:#1e293b; line-height:1.35; }
.mascara-esp { font-size:.75rem; color:var(--muted); display:flex; align-items:center; gap:5px; }
.mascara-tags { display:flex; gap:4px; flex-wrap:wrap; }
.tag-chip { background:#f1f5f9; color:#64748b; padding:2px 7px; border-radius:4px; font-size:.7rem; }
.mascara-actions { display:flex; gap:8px; margin-top:auto; }

/* Upload zone */
.upload-zone { border:2px dashed #bfdbfe; border-radius:12px; padding:40px 20px; text-align:center; cursor:pointer; transition:all .2s; background:#f8faff; }
.upload-zone:hover, .upload-zone.drag-over { border-color:#1a56db; background:#eff6ff; }

/* Modal */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; display:flex; align-items:center; justify-content:center; padding:20px; }
.modal-box { background:#fff; border-radius:16px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,.2); display:flex; flex-direction:column; }
.modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px 16px; border-bottom:1px solid #e2e8f0; }
.modal-header h3 { margin:0; font-size:1rem; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px; }
.modal-body { padding:20px 24px; flex:1; }
.modal-footer { padding:16px 24px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:10px; }

/* Lista de máscaras no modal */
.mascara-item { display:flex; align-items:flex-start; gap:12px; padding:12px 14px; border-bottom:1px solid #f1f5f9; cursor:pointer; transition:background .15s; }
.mascara-item:last-child { border-bottom:none; }
.mascara-item:hover { background:#f8faff; }
.mascara-item input[type=checkbox] { margin-top:3px; accent-color:#1a56db; width:16px; height:16px; flex-shrink:0; }
.mascara-item-body { flex:1; min-width:0; }
.mascara-item-nome { font-size:.85rem; font-weight:600; color:#1e293b; }
.mascara-item-sub { font-size:.75rem; color:var(--muted); margin-top:2px; }

/* Preview seções */
.preview-secao { margin-bottom:16px; }
.preview-secao-titulo { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--blue-600); margin-bottom:6px; }
.preview-secao-corpo { background:#f8fafc; border-radius:8px; padding:12px 14px; font-size:.83rem; color:#334155; white-space:pre-wrap; line-height:1.6; border-left:3px solid #bfdbfe; }

/* Stat card */
.stat-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px 20px; display:flex; align-items:center; gap:14px; }
.stat-icon { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
.stat-value { font-size:1.5rem; font-weight:700; color:#1e293b; line-height:1; }
.stat-label { font-size:.75rem; color:var(--muted); margin-top:3px; }
</style>

<!-- ══════════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════════ -->
<script>
// ── Abas ──────────────────────────────────────────────────
function trocarAba(btn, aba) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
    if (typeof btn === 'object' && btn.classList) btn.classList.add('active');
    else document.querySelector('[data-tab="' + aba + '"]').classList.add('active');
    document.getElementById('tab-' + aba).style.display = 'block';
}

// ── Modal Upload ──────────────────────────────────────────
let arquivoAtual = null;
let importacaoId = 0;
let mascarasEncontradas = [];

function abrirModalUpload() {
    document.getElementById('modalUpload').style.display = 'flex';
    resetarModal();
}

function fecharModalUpload(e) {
    if (!e || e.target === document.getElementById('modalUpload')) {
        document.getElementById('modalUpload').style.display = 'none';
    }
}

function resetarModal() {
    arquivoAtual = null;
    mascarasEncontradas = [];
    document.getElementById('passo1').style.display = 'block';
    document.getElementById('passo2').style.display = 'none';
    document.getElementById('arquivoInfo').style.display = 'none';
    document.getElementById('uploadProgress').style.display = 'none';
    document.getElementById('btnAnalisar').disabled = true;
    document.getElementById('btnAnalisar').style.display = '';
    document.getElementById('btnImportar').style.display = 'none';
    document.getElementById('fileInput').value = '';
    document.getElementById('resumoSelecao').style.display = 'none';
}

function arquivoSelecionado(input) {
    if (!input.files[0]) return;
    arquivoAtual = input.files[0];
    document.getElementById('arquivoNome').textContent = arquivoAtual.name + ' (' + (arquivoAtual.size / 1024).toFixed(0) + ' KB)';
    document.getElementById('arquivoInfo').style.display = 'flex';
    document.getElementById('btnAnalisar').disabled = false;
}

function limparArquivo() {
    arquivoAtual = null;
    document.getElementById('arquivoInfo').style.display = 'none';
    document.getElementById('btnAnalisar').disabled = true;
    document.getElementById('fileInput').value = '';
}

function dragOver(e) { e.preventDefault(); document.getElementById('uploadZone').classList.add('drag-over'); }
function dragLeave(e) { document.getElementById('uploadZone').classList.remove('drag-over'); }
function dropFile(e) {
    e.preventDefault();
    document.getElementById('uploadZone').classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) {
        arquivoAtual = file;
        document.getElementById('arquivoNome').textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
        document.getElementById('arquivoInfo').style.display = 'flex';
        document.getElementById('btnAnalisar').disabled = false;
    }
}

function analisarDocx() {
    if (!arquivoAtual) return;

    document.getElementById('uploadProgress').style.display = 'block';
    document.getElementById('btnAnalisar').disabled = true;
    document.getElementById('btnAnalisar').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Analisando...';

    const formData = new FormData();
    formData.append('arquivo', arquivoAtual);
    formData.append('csrf_token', '<?= htmlspecialchars($csrf_token ?? '') ?>');

    fetch('/templates/mascaras/importar-docx', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(function(res) {
            document.getElementById('uploadProgress').style.display = 'none';
            document.getElementById('btnAnalisar').innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Analisar';

            if (!res.ok) {
                mostrarToast(res.msg || 'Erro ao analisar o arquivo.', 'danger');
                document.getElementById('btnAnalisar').disabled = false;
                return;
            }

            importacaoId = res.importacao_id || 0;
            mascarasEncontradas = res.mascaras || [];

            document.getElementById('totalEncontradas').textContent = res.total + ' máscaras encontradas';
            renderizarListaMascaras(mascarasEncontradas);

            document.getElementById('passo1').style.display = 'none';
            document.getElementById('passo2').style.display = 'block';
            document.getElementById('btnAnalisar').style.display = 'none';
            document.getElementById('btnImportar').style.display = '';
            atualizarResumo();
        })
        .catch(function() {
            document.getElementById('uploadProgress').style.display = 'none';
            document.getElementById('btnAnalisar').disabled = false;
            document.getElementById('btnAnalisar').innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Analisar';
            mostrarToast('Erro de conexão ao analisar o arquivo.', 'danger');
        });
}

function renderizarListaMascaras(lista) {
    const container = document.getElementById('listaMascaras');
    container.innerHTML = '';
    lista.forEach(function(m, i) {
        const div = document.createElement('div');
        div.className = 'mascara-item';
        div.dataset.idx = i;
        div.innerHTML = `
            <input type="checkbox" checked id="chk_${i}" onchange="atualizarResumo()">
            <div class="mascara-item-body">
                <div class="mascara-item-nome">${escHtml(m.nome_amigavel || m.titulo)}</div>
                <div class="mascara-item-sub">
                    <span class="badge-mod" style="font-size:10px;">${escHtml(m.modalidade || 'TC')}</span>
                    <span style="margin-left:6px;">${escHtml(m.especialidade || 'Radiologia')}</span>
                </div>
            </div>
        `;
        div.querySelector('label, .mascara-item-body').addEventListener('click', function() {
            const chk = div.querySelector('input[type=checkbox]');
            chk.checked = !chk.checked;
            atualizarResumo();
        });
        container.appendChild(div);
    });
}

function filtrarListaMascaras(q) {
    const items = document.querySelectorAll('.mascara-item');
    const ql = q.toLowerCase();
    items.forEach(function(item) {
        const nome = item.querySelector('.mascara-item-nome').textContent.toLowerCase();
        item.style.display = nome.includes(ql) ? '' : 'none';
    });
}

function selecionarTodas(val) {
    document.querySelectorAll('#listaMascaras input[type=checkbox]').forEach(c => c.checked = val);
    atualizarResumo();
}

function atualizarResumo() {
    const total = document.querySelectorAll('#listaMascaras input[type=checkbox]').length;
    const sel   = document.querySelectorAll('#listaMascaras input[type=checkbox]:checked').length;
    const el    = document.getElementById('resumoSelecao');
    document.getElementById('textoResumo').textContent = sel + ' de ' + total + ' máscaras selecionadas para importação.';
    el.style.display = sel > 0 ? 'flex' : 'none';
    document.getElementById('btnImportar').disabled = sel === 0;
}

function confirmarImportacao() {
    const checkboxes = document.querySelectorAll('#listaMascaras input[type=checkbox]:checked');
    const selecionadas = [];
    checkboxes.forEach(function(chk) {
        const idx = parseInt(chk.id.replace('chk_', ''));
        if (!isNaN(idx) && mascarasEncontradas[idx]) {
            selecionadas.push(mascarasEncontradas[idx]);
        }
    });

    if (!selecionadas.length) { mostrarToast('Selecione ao menos uma máscara.', 'warning'); return; }

    const btn = document.getElementById('btnImportar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importando...';

    fetch('/templates/mascaras/confirmar-importacao', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            importacao_id: importacaoId,
            arquivo_nome: arquivoAtual ? arquivoAtual.name : 'upload.docx',
            mascaras: selecionadas,
        })
    })
    .then(r => r.json())
    .then(function(res) {
        fecharModalUpload();
        if (res.ok) {
            mostrarToast(res.msg, 'success');
            setTimeout(function() { window.location.href = '/templates'; }, 2000);
        } else {
            mostrarToast(res.msg || 'Erro ao importar.', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-file-import"></i> Importar Selecionadas';
        }
    })
    .catch(function() {
        mostrarToast('Erro de conexão.', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-file-import"></i> Importar Selecionadas';
    });
}

// ── Preview de máscara da biblioteca ─────────────────────
let previewIdAtual = 0;

function previewMascara(id, btn) {
    previewIdAtual = id;
    document.getElementById('previewTitulo').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Carregando...';
    document.getElementById('previewBody').innerHTML = '<div style="text-align:center;padding:40px;color:var(--muted);"><i class="fa-solid fa-spinner fa-spin fa-2x"></i></div>';
    document.getElementById('modalPreview').style.display = 'flex';

    document.getElementById('btnImportarPreview').onclick = function() {
        fecharPreview();
        importarDaBiblioteca(id, '', null);
    };

    fetch('/templates/mascaras/' + id + '/preview')
        .then(r => r.json())
        .then(function(res) {
            if (!res.ok) { document.getElementById('previewBody').innerHTML = '<p style="color:var(--danger);">Erro ao carregar.</p>'; return; }

            document.getElementById('previewTitulo').innerHTML = '<i class="fa-solid fa-file-lines"></i> ' + escHtml(res.nome_amigavel);
            document.getElementById('btnImportarPreview').onclick = function() {
                fecharPreview();
                importarDaBiblioteca(id, res.nome_amigavel, null);
            };

            let html = '<div style="margin-bottom:12px;display:flex;gap:8px;flex-wrap:wrap;">';
            html += '<span class="badge-mod">' + escHtml(res.modalidade) + '</span>';
            if (res.especialidade) html += '<span style="font-size:.75rem;color:var(--muted);padding:3px 8px;background:#f1f5f9;border-radius:6px;">' + escHtml(res.especialidade) + '</span>';
            html += '</div>';

            const secoes = [
                { key: 'secao_tecnica',   label: 'Técnica / Metodologia' },
                { key: 'secao_analise',   label: 'Análise / Achados' },
                { key: 'secao_impressao', label: 'Impressão' },
            ];
            secoes.forEach(function(s) {
                if (res[s.key] && res[s.key].trim()) {
                    html += '<div class="preview-secao">';
                    html += '<div class="preview-secao-titulo">' + escHtml(s.label) + '</div>';
                    html += '<div class="preview-secao-corpo">' + escHtml(res[s.key]) + '</div>';
                    html += '</div>';
                }
            });

            if (!res.secao_tecnica && !res.secao_analise && !res.secao_impressao && res.corpo) {
                html += '<div class="preview-secao">';
                html += '<div class="preview-secao-titulo">Corpo do Laudo</div>';
                html += '<div class="preview-secao-corpo">' + escHtml(res.corpo.substring(0, 1500)) + (res.corpo.length > 1500 ? '\n...' : '') + '</div>';
                html += '</div>';
            }

            document.getElementById('previewBody').innerHTML = html;
        })
        .catch(function() {
            document.getElementById('previewBody').innerHTML = '<p style="color:var(--danger);">Erro de conexão.</p>';
        });
}

function fecharPreview(e) {
    if (!e || e.target === document.getElementById('modalPreview')) {
        document.getElementById('modalPreview').style.display = 'none';
    }
}

// ── Importar da biblioteca ────────────────────────────────
function importarDaBiblioteca(id, nome, btn) {
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    }

    fetch('/templates/mascaras/' + id + '/importar', { method: 'POST' })
        .then(r => r.json())
        .then(function(res) {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-plus"></i> Importar'; }

            if (res.ok) {
                mostrarToast(res.msg, 'success');
                // Atualiza o botão do card para "Editar"
                if (btn) {
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Importada';
                    btn.className = 'btn btn-sm btn-ghost';
                    btn.onclick = function() { window.location.href = res.edit_url; };
                }
            } else if (res.ja_existe) {
                mostrarToast(res.msg, 'warning');
            } else {
                mostrarToast(res.msg || 'Erro ao importar.', 'danger');
            }
        })
        .catch(function() {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-plus"></i> Importar'; }
            mostrarToast('Erro de conexão.', 'danger');
        });
}

// ── Toast ─────────────────────────────────────────────────
function mostrarToast(msg, tipo) {
    const toast = document.getElementById('toast');
    const inner = document.getElementById('toastInner');
    const icons = { success: 'fa-check-circle', danger: 'fa-triangle-exclamation', warning: 'fa-exclamation-circle', info: 'fa-info-circle' };
    inner.className = 'alert alert-' + (tipo || 'info');
    inner.innerHTML = '<i class="fa-solid ' + (icons[tipo] || icons.info) + '"></i> ' + escHtml(msg);
    toast.style.display = 'block';
    clearTimeout(toast._timer);
    toast._timer = setTimeout(function() { toast.style.display = 'none'; }, 5000);
}

// ── Utilitário ────────────────────────────────────────────
function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
