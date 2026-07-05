<?php
// Injeta workspace.css via extraCss
$extraCss = ['/assets/css/workspace.css?v=2.0.0'];
?>

<!-- ── WORKSPACE TOPBAR ── -->
<div class="ws-topbar">
    <div class="ws-topbar-left">
        <a href="/workspace" class="ws-back-btn" title="Voltar à lista">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="ws-patient-info">
            <div class="ws-patient-name">
                <?= htmlspecialchars($laudo->patient_nome ?? 'Paciente não identificado') ?>
            </div>
            <div class="ws-patient-meta">
                <?php if ($laudo->modalidade): ?>
                <span><i class="fa-solid fa-x-ray"></i> <?= htmlspecialchars($laudo->modalidade) ?></span>
                <?php endif; ?>
                <?php if ($laudo->study_uid): ?>
                <span><i class="fa-solid fa-barcode"></i> <?= htmlspecialchars(substr($laudo->study_uid, 0, 20)) ?>...</span>
                <?php endif; ?>
                <span><i class="fa-regular fa-clock"></i> <?= date('d/m/Y H:i', strtotime($laudo->created_at)) ?></span>
            </div>
        </div>
        <?php if ($laudo->status === 'assinado'): ?>
        <span class="ws-badge ws-badge-assinado"><i class="fa-solid fa-signature"></i> Assinado</span>
        <?php else: ?>
        <span class="ws-badge ws-badge-rascunho"><i class="fa-solid fa-pen"></i> Rascunho</span>
        <?php endif; ?>
    </div>

    <div class="ws-topbar-actions">
        <!-- Troca de template -->
        <?php if (!empty($templates) && $laudo->status === 'rascunho'): ?>
        <select class="ws-template-select" id="template-select" onchange="trocarTemplate(this.value)" title="Aplicar template">
            <option value="">Template...</option>
            <?php foreach ($templates as $t): ?>
            <option value="<?= (int)$t->id ?>"><?= htmlspecialchars($t->nome) ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>

        <!-- Viewer PACS -->
        <?php if ($pacsViewerUrl): ?>
        <a href="<?= htmlspecialchars($pacsViewerUrl) ?>" target="_blank" class="ws-btn ws-btn-ghost" title="Abrir no Viewer PACS">
            <i class="fa-solid fa-eye"></i> Viewer
        </a>
        <?php endif; ?>

        <!-- Auto-save indicator -->
        <div class="ws-save-indicator" id="save-indicator">
            <span class="ws-save-dot" id="save-dot"></span>
            <span id="save-status">Salvo</span>
        </div>

        <!-- Assinar -->
        <?php if ($laudo->status === 'rascunho'): ?>
        <button class="ws-btn ws-btn-primary" id="btn-assinar" onclick="assinarLaudo()">
            <i class="fa-solid fa-signature"></i> Assinar Laudo
        </button>
        <?php else: ?>
        <button class="ws-btn ws-btn-ghost" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Imprimir
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- ── LAYOUT PRINCIPAL ── -->
<div class="ws-body">

    <!-- ── EDITOR ── -->
    <div class="ws-editor">

        <!-- Indicação -->
        <div class="ws-section-card">
            <div class="ws-section-header">
                <div class="ws-section-title">
                    <i class="fa-solid fa-clipboard-question"></i>
                    Indicação Clínica
                </div>
            </div>
            <div class="ws-section-body">
                <textarea class="ws-textarea" id="indicacao" name="indicacao"
                    placeholder="Descreva a indicação clínica do exame..."
                    style="min-height:64px;"
                    <?= $laudo->status !== 'rascunho' ? 'readonly' : '' ?>
                ><?= htmlspecialchars($laudo->indicacao ?? '') ?></textarea>
            </div>
        </div>

        <!-- Técnica -->
        <div class="ws-section-card">
            <div class="ws-section-header">
                <div class="ws-section-title">
                    <i class="fa-solid fa-gears"></i>
                    Técnica
                </div>
            </div>
            <div class="ws-section-body">
                <textarea class="ws-textarea" id="tecnica" name="tecnica"
                    placeholder="Descreva a técnica utilizada..."
                    style="min-height:64px;"
                    <?= $laudo->status !== 'rascunho' ? 'readonly' : '' ?>
                ><?= htmlspecialchars($laudo->tecnica ?? '') ?></textarea>
            </div>
        </div>

        <!-- Achados -->
        <div class="ws-section-card">
            <div class="ws-section-header">
                <div class="ws-section-title">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Achados
                </div>
                <?php if ($laudo->status === 'rascunho'): ?>
                <div class="ws-section-actions">
                    <button class="ws-btn ws-btn-ai ws-btn-xs" onclick="gerarSugestaoIA()" id="btn-ia-achados">
                        <i class="fa-solid fa-brain"></i> Sugerir com IA
                    </button>
                    <?php if (!empty($autotextos)): ?>
                    <button class="ws-btn ws-btn-ghost ws-btn-xs" onclick="toggleAutotextos()">
                        <i class="fa-solid fa-bolt"></i> Autotextos
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <!-- Autotextos dropdown -->
            <?php if (!empty($autotextos)): ?>
            <div class="ws-autotextos" id="autotextos-panel" style="display:none;">
                <?php foreach ($autotextos as $at): ?>
                <button class="ws-autotexto-btn" onclick="inserirAutotexto('achados', <?= json_encode($at->texto) ?>)">
                    <strong><?= htmlspecialchars($at->atalho) ?></strong>
                    <span><?= htmlspecialchars(substr($at->texto, 0, 60)) ?>...</span>
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="ws-section-body">
                <textarea class="ws-textarea" id="achados" name="achados"
                    placeholder="Descreva os achados do exame em detalhes..."
                    style="min-height:180px;"
                    <?= $laudo->status !== 'rascunho' ? 'readonly' : '' ?>
                ><?= htmlspecialchars($laudo->achados ?? '') ?></textarea>
            </div>
        </div>

        <!-- Impressão Diagnóstica -->
        <div class="ws-section-card">
            <div class="ws-section-header">
                <div class="ws-section-title">
                    <i class="fa-solid fa-lightbulb"></i>
                    Impressão Diagnóstica
                </div>
            </div>
            <div class="ws-section-body">
                <textarea class="ws-textarea" id="impressao" name="impressao"
                    placeholder="Conclusão diagnóstica..."
                    style="min-height:90px;"
                    <?= $laudo->status !== 'rascunho' ? 'readonly' : '' ?>
                ><?= htmlspecialchars($laudo->impressao ?? '') ?></textarea>
            </div>
        </div>

        <!-- Recomendações + CID -->
        <div class="ws-row-2col">
            <div class="ws-section-card">
                <div class="ws-section-header">
                    <div class="ws-section-title">
                        <i class="fa-solid fa-notes-medical"></i>
                        Recomendações
                    </div>
                </div>
                <div class="ws-section-body">
                    <textarea class="ws-textarea" id="recomendacao" name="recomendacao"
                        placeholder="Recomendações para o clínico solicitante..."
                        style="min-height:64px;"
                        <?= $laudo->status !== 'rascunho' ? 'readonly' : '' ?>
                    ><?= htmlspecialchars($laudo->recomendacao ?? '') ?></textarea>
                </div>
            </div>
            <div class="ws-section-card">
                <div class="ws-section-header">
                    <div class="ws-section-title">
                        <i class="fa-solid fa-tag"></i>
                        CID-10
                    </div>
                </div>
                <div class="ws-section-body">
                    <input type="text" id="cid" name="cid"
                        class="ws-textarea" style="min-height:auto;padding:12px 14px;"
                        placeholder="Ex: R93.8"
                        value="<?= htmlspecialchars($laudo->cid ?? '') ?>"
                        <?= $laudo->status !== 'rascunho' ? 'readonly' : '' ?>>
                </div>
            </div>
        </div>

        <!-- Assinado em -->
        <?php if ($laudo->status === 'assinado' && $laudo->assinado_em): ?>
        <div class="ws-signed-banner">
            <i class="fa-solid fa-circle-check"></i>
            Laudo assinado digitalmente em <?= date('d/m/Y \à\s H:i', strtotime($laudo->assinado_em)) ?>
        </div>
        <?php endif; ?>

    </div><!-- /.ws-editor -->

    <!-- ── COPILOT SIDEBAR ── -->
    <div class="ws-copilot">

        <!-- Info do estudo -->
        <div class="ws-study-card">
            <div class="ws-study-header">
                <i class="fa-solid fa-microscope"></i>
                Informações do Estudo
            </div>
            <div class="ws-study-rows">
                <div class="ws-study-row">
                    <span class="ws-study-label">Paciente</span>
                    <span class="ws-study-value"><?= htmlspecialchars($laudo->patient_nome ?? 'N/I') ?></span>
                </div>
                <?php if ($laudo->patient_uid): ?>
                <div class="ws-study-row">
                    <span class="ws-study-label">ID Paciente</span>
                    <span class="ws-study-value" style="font-family:monospace;font-size:11px;"><?= htmlspecialchars($laudo->patient_uid) ?></span>
                </div>
                <?php endif; ?>
                <div class="ws-study-row">
                    <span class="ws-study-label">Modalidade</span>
                    <span class="ws-study-value"><?= htmlspecialchars($laudo->modalidade ?? '—') ?></span>
                </div>
                <div class="ws-study-row">
                    <span class="ws-study-label">Status</span>
                    <span class="ws-study-value">
                        <?= $laudo->status === 'assinado'
                            ? '<span style="color:#059669;font-weight:600;">Assinado</span>'
                            : '<span style="color:#d97706;font-weight:600;">Rascunho</span>' ?>
                    </span>
                </div>
                <div class="ws-study-row">
                    <span class="ws-study-label">Study UID</span>
                    <span class="ws-study-value" style="font-family:monospace;font-size:10px;word-break:break-all;" title="<?= htmlspecialchars($laudo->study_uid ?? '') ?>">
                        <?= htmlspecialchars(substr($laudo->study_uid ?? '', 0, 22)) ?>...
                    </span>
                </div>
                <div class="ws-study-row">
                    <span class="ws-study-label">Criado em</span>
                    <span class="ws-study-value"><?= date('d/m/Y H:i', strtotime($laudo->created_at)) ?></span>
                </div>
            </div>
        </div>

        <!-- Chat Copilot IA -->
        <div class="ws-copilot-chat">
            <div class="ws-copilot-header">
                <div class="ws-copilot-status"></div>
                <div class="ws-copilot-title">
                    <i class="fa-solid fa-brain"></i>
                    VOXEL Copilot IA
                </div>
                <div class="ws-copilot-model">GPT-4o</div>
            </div>

            <!-- Sugestões rápidas -->
            <div class="ws-quick-actions">
                <button class="ws-quick-btn" onclick="sendQuick('Gere uma sugestão de impressão diagnóstica baseada nos achados')">
                    <i class="fa-solid fa-lightbulb"></i> Impressão
                </button>
                <button class="ws-quick-btn" onclick="sendQuick('Verifique inconsistências e erros no laudo atual')">
                    <i class="fa-solid fa-spell-check"></i> Revisar
                </button>
                <button class="ws-quick-btn" onclick="sendQuick('Sugira o CID-10 mais adequado para este caso')">
                    <i class="fa-solid fa-tag"></i> CID
                </button>
                <button class="ws-quick-btn" onclick="sendQuick('Sugira recomendações para o médico solicitante')">
                    <i class="fa-solid fa-notes-medical"></i> Recomen.
                </button>
            </div>

            <!-- Mensagens -->
            <div class="ws-chat-messages" id="ai-messages">
                <?php if (empty($conversas)): ?>
                <div class="ws-chat-msg ws-chat-msg-ai">
                    <div class="ws-chat-bubble">
                        <strong>Olá, Dr. <?= htmlspecialchars(explode(' ', $_SESSION['user']->name ?? 'Médico')[0]) ?>!</strong>
                        Sou o VOXEL Copilot. Estou pronto para auxiliar na elaboração deste laudo.<br><br>
                        Posso sugerir achados, gerar impressões diagnósticas, verificar inconsistências, indicar CID-10 e muito mais.
                    </div>
                    <div class="ws-chat-time">Agora</div>
                </div>
                <?php else: ?>
                <?php foreach ($conversas as $c): ?>
                <div class="ws-chat-msg <?= $c->role === 'user' ? 'ws-chat-msg-user' : 'ws-chat-msg-ai' ?>">
                    <div class="ws-chat-bubble"><?= nl2br(htmlspecialchars($c->conteudo)) ?></div>
                    <div class="ws-chat-time"><?= date('H:i', strtotime($c->created_at)) ?></div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Input -->
            <div class="ws-chat-input-area">
                <textarea class="ws-chat-input" id="ai-input"
                    placeholder="Pergunte ao Copilot..." rows="1"
                    <?= $laudo->status !== 'rascunho' ? 'disabled' : '' ?>></textarea>
                <button class="ws-chat-send" id="ai-send-btn" onclick="sendChat()"
                    <?= $laudo->status !== 'rascunho' ? 'disabled' : '' ?>>
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </div>

    </div><!-- /.ws-copilot -->

</div><!-- /.ws-body -->

<!-- Hidden fields -->
<input type="hidden" id="laudo-id"     value="<?= (int)$laudo->id ?>">
<input type="hidden" id="workspace-id" value="<?= (int)$laudo->workspace_id ?>">
<input type="hidden" id="csrf-token"   value="<?= htmlspecialchars($csrf_token ?? '') ?>">

<script>
const laudoId     = parseInt(document.getElementById('laudo-id').value, 10);
const workspaceId = parseInt(document.getElementById('workspace-id').value, 10);
const csrfToken   = document.getElementById('csrf-token').value;
const isReadonly  = <?= $laudo->status !== 'rascunho' ? 'true' : 'false' ?>;

// ── AUTO-SAVE ──────────────────────────────────────────────────
let saveTimer = null;
let isDirty   = false;

function markDirty() {
    if (isReadonly) return;
    isDirty = true;
    const dot = document.getElementById('save-dot');
    const lbl = document.getElementById('save-status');
    if (dot) dot.classList.add('unsaved');
    if (lbl) lbl.textContent = 'Não salvo...';
    clearTimeout(saveTimer);
    saveTimer = setTimeout(autoSave, 3000);
}

function autoSave() {
    if (!isDirty || isReadonly) return;
    salvarLaudo();
}

function salvarLaudo() {
    const data = new FormData();
    data.append('csrf_token',   csrfToken);
    data.append('indicacao',    document.getElementById('indicacao').value);
    data.append('tecnica',      document.getElementById('tecnica').value);
    data.append('achados',      document.getElementById('achados').value);
    data.append('impressao',    document.getElementById('impressao').value);
    data.append('recomendacao', document.getElementById('recomendacao').value);
    data.append('cid',          document.getElementById('cid').value);

    fetch('/workspace/' + laudoId + '/salvar', { method: 'POST', body: data })
        .then(r => r.json())
        .then(function(res) {
            const dot = document.getElementById('save-dot');
            const lbl = document.getElementById('save-status');
            if (res.ok) {
                isDirty = false;
                if (dot) dot.classList.remove('unsaved');
                if (lbl) lbl.textContent = 'Salvo às ' + new Date().toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'});
            } else {
                if (lbl) lbl.textContent = 'Erro ao salvar';
            }
        })
        .catch(function() {
            const lbl = document.getElementById('save-status');
            if (lbl) lbl.textContent = 'Erro de conexão';
        });
}

// Eventos de digitação
['indicacao','tecnica','achados','impressao','recomendacao','cid'].forEach(function(id) {
    const el = document.getElementById(id);
    if (el && !isReadonly) el.addEventListener('input', markDirty);
});

// ── ASSINAR ───────────────────────────────────────────────────
function assinarLaudo() {
    if (!confirm('Deseja assinar e finalizar este laudo?\n\nEsta ação não pode ser desfeita.')) return;

    const btn = document.getElementById('btn-assinar');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Assinando...'; }

    salvarLaudo();
    setTimeout(function() {
        const data = new FormData();
        data.append('csrf_token', csrfToken);
        fetch('/workspace/' + laudoId + '/assinar', { method: 'POST', body: data })
            .then(r => r.json())
            .then(function(res) {
                if (res.ok) {
                    window.location.reload();
                } else {
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-signature"></i> Assinar Laudo'; }
                    alert(res.msg || 'Erro ao assinar o laudo.');
                }
            })
            .catch(function() {
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-signature"></i> Assinar Laudo'; }
                alert('Erro de conexão ao assinar.');
            });
    }, 600);
}

// ── TROCAR TEMPLATE ───────────────────────────────────────────
function trocarTemplate(templateId) {
    if (!templateId || isReadonly) return;
    if (!confirm('Aplicar este template irá substituir o conteúdo atual dos Achados. Deseja continuar?')) {
        document.getElementById('template-select').value = '';
        return;
    }

    fetch('/api/templates/' + templateId + '/corpo')
        .then(r => r.json())
        .then(function(res) {
            if (res.ok && res.corpo !== undefined) {
                document.getElementById('achados').value = res.corpo;
                markDirty();
            }
            document.getElementById('template-select').value = '';
        })
        .catch(function() {
            document.getElementById('template-select').value = '';
        });
}

// ── AUTOTEXTOS ────────────────────────────────────────────────
function toggleAutotextos() {
    const panel = document.getElementById('autotextos-panel');
    if (panel) panel.style.display = panel.style.display === 'none' ? 'flex' : 'none';
}

function inserirAutotexto(campo, texto) {
    const el = document.getElementById(campo);
    if (!el || isReadonly) return;
    const start = el.selectionStart;
    const end   = el.selectionEnd;
    el.value = el.value.substring(0, start) + texto + el.value.substring(end);
    el.selectionStart = el.selectionEnd = start + texto.length;
    el.focus();
    markDirty();
    const panel = document.getElementById('autotextos-panel');
    if (panel) panel.style.display = 'none';
}

// ── IA CHAT ───────────────────────────────────────────────────
function addMessage(role, content) {
    const msgs = document.getElementById('ai-messages');
    if (!msgs) return;
    const div  = document.createElement('div');
    div.className = 'ws-chat-msg ' + (role === 'user' ? 'ws-chat-msg-user' : 'ws-chat-msg-ai');
    div.innerHTML =
        '<div class="ws-chat-bubble">' + content.replace(/\n/g, '<br>') + '</div>' +
        '<div class="ws-chat-time">' + new Date().toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'}) + '</div>';
    msgs.appendChild(div);
    msgs.scrollTop = msgs.scrollHeight;
}

function setLoading(loading) {
    const btn = document.getElementById('ai-send-btn');
    if (!btn) return;
    btn.disabled = loading;
    btn.innerHTML = loading
        ? '<i class="fa-solid fa-spinner fa-spin"></i>'
        : '<i class="fa-solid fa-paper-plane"></i>';
}

function sendChat() {
    const input = document.getElementById('ai-input');
    if (!input) return;
    const msg = input.value.trim();
    if (!msg) return;

    addMessage('user', msg);
    input.value = '';
    input.style.height = 'auto';
    setLoading(true);

    const data = new FormData();
    data.append('csrf_token',  csrfToken);
    data.append('mensagem',    msg);
    data.append('workspace_id', workspaceId);
    // Contexto do laudo atual
    data.append('indicacao',   document.getElementById('indicacao')?.value  || '');
    data.append('achados',     document.getElementById('achados')?.value    || '');
    data.append('impressao',   document.getElementById('impressao')?.value  || '');

    fetch('/api/copilot/chat', { method: 'POST', body: data })
        .then(r => r.json())
        .then(function(res) {
            setLoading(false);
            addMessage('assistant', res.ok ? res.content : ('⚠️ ' + (res.error || 'Erro ao processar.')));
        })
        .catch(function() {
            setLoading(false);
            addMessage('assistant', '⚠️ Erro de conexão com o Copilot.');
        });
}

function sendQuick(msg) {
    const input = document.getElementById('ai-input');
    if (input) { input.value = msg; }
    sendChat();
}

function gerarSugestaoIA() {
    const btn = document.getElementById('btn-ia-achados');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Gerando...'; }

    const data = new FormData();
    data.append('csrf_token',   csrfToken);
    data.append('workspace_id', workspaceId);
    data.append('modalidade',   <?= json_encode($laudo->modalidade ?? '') ?>);
    data.append('indicacao',    document.getElementById('indicacao')?.value  || '');
    data.append('achados',      document.getElementById('achados')?.value    || '');

    fetch('/api/copilot/sugestao', { method: 'POST', body: data })
        .then(r => r.json())
        .then(function(res) {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-brain"></i> Sugerir com IA'; }
            addMessage('assistant', res.ok ? ('**Sugestão gerada:**\n\n' + res.content) : ('⚠️ ' + (res.error || 'Erro ao gerar sugestão.')));
        })
        .catch(function() {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-brain"></i> Sugerir com IA'; }
            addMessage('assistant', '⚠️ Erro de conexão com o Copilot.');
        });
}

// Enter no chat (Shift+Enter = nova linha)
document.getElementById('ai-input')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendChat();
    }
});

// Auto-resize do textarea do chat
document.getElementById('ai-input')?.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
});

// Scroll ao fundo das mensagens ao carregar
const msgs = document.getElementById('ai-messages');
if (msgs) msgs.scrollTop = msgs.scrollHeight;
</script>
