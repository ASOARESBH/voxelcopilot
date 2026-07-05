<?php use App\Core\View; ?>
<style>
.ws-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 16px;
    height: calc(100vh - var(--topbar-h) - 56px);
}
.ws-editor { display: flex; flex-direction: column; gap: 12px; overflow-y: auto; }
.ws-sidebar { display: flex; flex-direction: column; gap: 12px; overflow-y: auto; }

.editor-section {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}
.editor-section-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 16px;
    border-bottom: 1px solid var(--border);
    background: rgba(255,255,255,.02);
}
.editor-section-title {
    font-size: .72rem; font-weight: 700;
    color: var(--muted); text-transform: uppercase; letter-spacing: .08em;
    display: flex; align-items: center; gap: 7px;
}
.editor-section-title i { color: var(--primary); }
.editor-textarea {
    width: 100%; min-height: 80px;
    background: transparent; border: none; outline: none;
    padding: 14px 16px;
    font-family: var(--font-body); font-size: .88rem;
    color: #e2e8f0; line-height: 1.7;
    resize: vertical;
}
.editor-textarea::placeholder { color: rgba(255,255,255,.15); }

/* Barra de ações do editor */
.editor-toolbar {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 16px;
    border-top: 1px solid var(--border);
    background: rgba(255,255,255,.01);
    flex-wrap: wrap;
}

/* Chat IA */
.ai-chat {
    display: flex; flex-direction: column;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    flex: 1;
    min-height: 0;
}
.ai-chat-header {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    background: linear-gradient(135deg, rgba(14,165,233,.08), rgba(6,182,212,.04));
    flex-shrink: 0;
}
.ai-chat-title {
    font-size: .82rem; font-weight: 700; color: #e2e8f0;
    display: flex; align-items: center; gap: 7px;
}
.ai-status {
    width: 7px; height: 7px; border-radius: 50%;
    background: var(--success);
    animation: pulse 2.5s ease-in-out infinite;
}
.ai-messages {
    flex: 1; overflow-y: auto;
    padding: 12px;
    display: flex; flex-direction: column; gap: 10px;
    min-height: 0;
}
.ai-messages::-webkit-scrollbar { width: 3px; }
.ai-messages::-webkit-scrollbar-thumb { background: rgba(14,165,233,.2); border-radius: 3px; }

.ai-msg {
    max-width: 90%; padding: 10px 14px;
    border-radius: 12px; font-size: .8rem; line-height: 1.6;
}
.ai-msg.user {
    align-self: flex-end;
    background: rgba(14,165,233,.12);
    border: 1px solid rgba(14,165,233,.2);
    color: #c8daea;
    border-bottom-right-radius: 4px;
}
.ai-msg.assistant {
    align-self: flex-start;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    color: #e2e8f0;
    border-bottom-left-radius: 4px;
}
.ai-msg.assistant strong { color: var(--primary); }
.ai-msg-time { font-size: .65rem; color: var(--muted); margin-top: 4px; }

.ai-input-area {
    padding: 10px;
    border-top: 1px solid var(--border);
    display: flex; gap: 8px; align-items: flex-end;
    flex-shrink: 0;
}
.ai-input {
    flex: 1; background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 10px; padding: 8px 12px;
    font-family: var(--font-body); font-size: .82rem;
    color: #fff; outline: none; resize: none;
    max-height: 100px;
    transition: border-color .2s;
}
.ai-input:focus { border-color: var(--primary); }
.ai-input::placeholder { color: rgba(255,255,255,.2); }
.ai-send {
    width: 36px; height: 36px; border-radius: 10px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border: none; cursor: pointer; color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem; flex-shrink: 0;
    transition: all .2s;
}
.ai-send:hover { transform: scale(1.05); box-shadow: 0 4px 14px rgba(14,165,233,.4); }
.ai-send:disabled { opacity: .5; transform: none; cursor: not-allowed; }

/* Sugestões rápidas */
.ai-quick {
    display: flex; flex-wrap: wrap; gap: 6px;
    padding: 8px 10px 0;
}
.ai-quick-btn {
    background: rgba(14,165,233,.06);
    border: 1px solid rgba(14,165,233,.15);
    border-radius: 100px; padding: 4px 12px;
    font-size: .7rem; color: var(--primary);
    cursor: pointer; transition: all .2s;
    white-space: nowrap;
}
.ai-quick-btn:hover { background: rgba(14,165,233,.14); }

/* Info do estudo */
.study-info {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 14px 16px;
    flex-shrink: 0;
}
.study-info-row {
    display: flex; justify-content: space-between;
    font-size: .75rem; padding: 4px 0;
    border-bottom: 1px solid rgba(255,255,255,.04);
}
.study-info-row:last-child { border-bottom: none; }
.study-info-row span:first-child { color: var(--muted); }
.study-info-row span:last-child { color: #c8daea; font-weight: 500; }

/* Status bar */
.ws-statusbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 16px;
    background: rgba(255,255,255,.02);
    border: 1px solid var(--border);
    border-radius: var(--radius-xs);
    font-size: .72rem; color: var(--muted);
    margin-bottom: 12px;
    flex-shrink: 0;
}
.ws-statusbar .save-indicator {
    display: flex; align-items: center; gap: 6px;
}
.ws-statusbar .save-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--success);
}
.ws-statusbar .save-dot.unsaved { background: var(--warning); animation: pulse 1.5s infinite; }
</style>

<!-- Status bar -->
<div class="ws-statusbar">
    <div class="save-indicator">
        <div class="save-dot" id="save-dot"></div>
        <span id="save-status">Salvo automaticamente</span>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
        <?php if ($laudo->status === 'rascunho'): ?>
        <button class="btn btn-success btn-sm" id="btn-assinar" onclick="assinarLaudo()">
            <i class="fa-solid fa-signature"></i> Assinar Laudo
        </button>
        <?php else: ?>
        <span class="badge badge-ativo"><i class="fa-solid fa-signature"></i> Assinado</span>
        <?php endif; ?>
        <a href="/workspace" class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="ws-layout">

    <!-- ── EDITOR ── -->
    <div class="ws-editor">

        <!-- Indicação -->
        <div class="editor-section">
            <div class="editor-section-header">
                <div class="editor-section-title"><i class="fa-solid fa-clipboard-question"></i> Indicação Clínica</div>
            </div>
            <textarea class="editor-textarea" id="indicacao" name="indicacao"
                placeholder="Descreva a indicação clínica do exame..."
                style="min-height:60px;"
                <?= $laudo->status !== 'rascunho' ? 'readonly' : '' ?>
            ><?= htmlspecialchars($laudo->indicacao ?? '') ?></textarea>
        </div>

        <!-- Técnica -->
        <div class="editor-section">
            <div class="editor-section-header">
                <div class="editor-section-title"><i class="fa-solid fa-gears"></i> Técnica</div>
            </div>
            <textarea class="editor-textarea" id="tecnica" name="tecnica"
                placeholder="Descreva a técnica utilizada..."
                style="min-height:60px;"
                <?= $laudo->status !== 'rascunho' ? 'readonly' : '' ?>
            ><?= htmlspecialchars($laudo->tecnica ?? '') ?></textarea>
        </div>

        <!-- Achados -->
        <div class="editor-section">
            <div class="editor-section-header">
                <div class="editor-section-title"><i class="fa-solid fa-magnifying-glass"></i> Achados</div>
                <?php if ($laudo->status === 'rascunho'): ?>
                <button class="btn btn-secondary btn-xs" onclick="gerarSugestaoIA()" id="btn-ia-achados">
                    <i class="fa-solid fa-brain"></i> Sugerir com IA
                </button>
                <?php endif; ?>
            </div>
            <textarea class="editor-textarea" id="achados" name="achados"
                placeholder="Descreva os achados do exame..."
                style="min-height:160px;"
                <?= $laudo->status !== 'rascunho' ? 'readonly' : '' ?>
            ><?= htmlspecialchars($laudo->achados ?? '') ?></textarea>
        </div>

        <!-- Impressão -->
        <div class="editor-section">
            <div class="editor-section-header">
                <div class="editor-section-title"><i class="fa-solid fa-lightbulb"></i> Impressão Diagnóstica</div>
            </div>
            <textarea class="editor-textarea" id="impressao" name="impressao"
                placeholder="Conclusão diagnóstica..."
                style="min-height:80px;"
                <?= $laudo->status !== 'rascunho' ? 'readonly' : '' ?>
            ><?= htmlspecialchars($laudo->impressao ?? '') ?></textarea>
        </div>

        <!-- Recomendação + CID -->
        <div style="display:grid;grid-template-columns:1fr 200px;gap:12px;">
            <div class="editor-section">
                <div class="editor-section-header">
                    <div class="editor-section-title"><i class="fa-solid fa-notes-medical"></i> Recomendações</div>
                </div>
                <textarea class="editor-textarea" id="recomendacao" name="recomendacao"
                    placeholder="Recomendações para o clínico..."
                    style="min-height:60px;"
                    <?= $laudo->status !== 'rascunho' ? 'readonly' : '' ?>
                ><?= htmlspecialchars($laudo->recomendacao ?? '') ?></textarea>
            </div>
            <div class="editor-section">
                <div class="editor-section-header">
                    <div class="editor-section-title"><i class="fa-solid fa-tag"></i> CID</div>
                </div>
                <input type="text" id="cid" name="cid"
                    class="editor-textarea" style="min-height:auto;padding:14px 16px;"
                    placeholder="Ex: R93.8"
                    value="<?= htmlspecialchars($laudo->cid ?? '') ?>"
                    <?= $laudo->status !== 'rascunho' ? 'readonly' : '' ?>>
            </div>
        </div>

    </div>

    <!-- ── SIDEBAR ── -->
    <div class="ws-sidebar">

        <!-- Info do estudo -->
        <div class="study-info">
            <div style="font-size:.72rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;">
                <i class="fa-solid fa-microscope" style="margin-right:6px;"></i>Estudo
            </div>
            <div class="study-info-row">
                <span>Paciente</span>
                <span><?= htmlspecialchars($laudo->patient_nome ?? 'N/I') ?></span>
            </div>
            <div class="study-info-row">
                <span>Modalidade</span>
                <span><?= htmlspecialchars($laudo->modalidade ?? '—') ?></span>
            </div>
            <div class="study-info-row">
                <span>Status</span>
                <span><?= $laudo->status === 'assinado' ? '<span style="color:#10b981;">Assinado</span>' : '<span style="color:#f59e0b;">Rascunho</span>' ?></span>
            </div>
            <div class="study-info-row">
                <span>Study UID</span>
                <span style="font-family:monospace;font-size:.65rem;color:var(--muted);" title="<?= htmlspecialchars($laudo->study_uid) ?>">
                    <?= htmlspecialchars(substr($laudo->study_uid, 0, 18)) ?>...
                </span>
            </div>
            <?php if ($laudo->assinado_em): ?>
            <div class="study-info-row">
                <span>Assinado em</span>
                <span><?= date('d/m/Y H:i', strtotime($laudo->assinado_em)) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Chat IA -->
        <div class="ai-chat">
            <div class="ai-chat-header">
                <div class="ai-status"></div>
                <div class="ai-chat-title">
                    <i class="fa-solid fa-brain" style="color:var(--primary);"></i>
                    VOXEL Copilot IA
                </div>
            </div>

            <!-- Sugestões rápidas -->
            <div class="ai-quick">
                <button class="ai-quick-btn" onclick="sendQuick('Gere uma sugestão de impressão diagnóstica')">Impressão</button>
                <button class="ai-quick-btn" onclick="sendQuick('Verifique inconsistências no laudo')">Revisar</button>
                <button class="ai-quick-btn" onclick="sendQuick('Sugira o CID mais adequado')">CID</button>
                <button class="ai-quick-btn" onclick="sendQuick('Sugira recomendações para o clínico')">Recomendações</button>
            </div>

            <div class="ai-messages" id="ai-messages">
                <?php if (empty($conversas)): ?>
                <div class="ai-msg assistant">
                    <strong>Olá!</strong> Sou o VOXEL Copilot. Estou aqui para auxiliar na elaboração do laudo.
                    <br><br>Posso sugerir achados, gerar impressões diagnósticas, verificar inconsistências e muito mais.
                    <div class="ai-msg-time">Agora</div>
                </div>
                <?php else: ?>
                <?php foreach ($conversas as $c): ?>
                <div class="ai-msg <?= htmlspecialchars($c->role) ?>">
                    <?= nl2br(htmlspecialchars($c->conteudo)) ?>
                    <div class="ai-msg-time"><?= date('H:i', strtotime($c->created_at)) ?></div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="ai-input-area">
                <textarea class="ai-input" id="ai-input" placeholder="Pergunte ao Copilot..." rows="1"
                    <?= $laudo->status !== 'rascunho' ? 'disabled' : '' ?>></textarea>
                <button class="ai-send" id="ai-send-btn" onclick="sendChat()"
                    <?= $laudo->status !== 'rascunho' ? 'disabled' : '' ?>>
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </div>

    </div>
</div>

<input type="hidden" id="laudo-id" value="<?= (int)$laudo->id ?>">
<input type="hidden" id="workspace-id" value="<?= (int)$laudo->workspace_id ?>">
<input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

<script>
const laudoId     = document.getElementById('laudo-id').value;
const workspaceId = document.getElementById('workspace-id').value;
const csrfToken   = document.getElementById('csrf-token').value;
const isReadonly  = <?= $laudo->status !== 'rascunho' ? 'true' : 'false' ?>;

// ── AUTO-SAVE ──
let saveTimer = null;
let isDirty   = false;

function markDirty() {
    if (isReadonly) return;
    isDirty = true;
    document.getElementById('save-dot').classList.add('unsaved');
    document.getElementById('save-status').textContent = 'Alterações não salvas...';
    clearTimeout(saveTimer);
    saveTimer = setTimeout(autoSave, 3000);
}

function autoSave() {
    if (!isDirty || isReadonly) return;
    salvarLaudo();
}

function salvarLaudo() {
    const data = new FormData();
    data.append('csrf_token',  csrfToken);
    data.append('indicacao',   document.getElementById('indicacao').value);
    data.append('tecnica',     document.getElementById('tecnica').value);
    data.append('achados',     document.getElementById('achados').value);
    data.append('impressao',   document.getElementById('impressao').value);
    data.append('recomendacao',document.getElementById('recomendacao').value);
    data.append('cid',         document.getElementById('cid').value);

    fetch('/workspace/' + laudoId + '/salvar', { method: 'POST', body: data })
        .then(r => r.json())
        .then(function(res) {
            if (res.ok) {
                isDirty = false;
                document.getElementById('save-dot').classList.remove('unsaved');
                document.getElementById('save-status').textContent = 'Salvo às ' + new Date().toLocaleTimeString('pt-BR', {hour:'2-digit',minute:'2-digit'});
            }
        })
        .catch(function() {
            document.getElementById('save-status').textContent = 'Erro ao salvar';
        });
}

// Eventos de digitação
['indicacao','tecnica','achados','impressao','recomendacao','cid'].forEach(function(id) {
    const el = document.getElementById(id);
    if (el && !isReadonly) el.addEventListener('input', markDirty);
});

// ── ASSINAR ──
function assinarLaudo() {
    if (!confirm('Deseja assinar e finalizar este laudo? Esta ação não pode ser desfeita.')) return;
    salvarLaudo();
    setTimeout(function() {
        const data = new FormData();
        data.append('csrf_token', csrfToken);
        fetch('/workspace/' + laudoId + '/assinar', { method: 'POST', body: data })
            .then(r => r.json())
            .then(function(res) {
                if (res.ok) { window.location.reload(); }
                else { alert(res.msg || 'Erro ao assinar.'); }
            });
    }, 500);
}

// ── IA CHAT ──
function addMessage(role, content) {
    const msgs = document.getElementById('ai-messages');
    const div  = document.createElement('div');
    div.className = 'ai-msg ' + role;
    div.innerHTML = content.replace(/\n/g, '<br>') +
        '<div class="ai-msg-time">' + new Date().toLocaleTimeString('pt-BR', {hour:'2-digit',minute:'2-digit'}) + '</div>';
    msgs.appendChild(div);
    msgs.scrollTop = msgs.scrollHeight;
}

function setLoading(loading) {
    const btn = document.getElementById('ai-send-btn');
    if (btn) {
        btn.disabled = loading;
        btn.innerHTML = loading
            ? '<i class="fa-solid fa-spinner fa-spin"></i>'
            : '<i class="fa-solid fa-paper-plane"></i>';
    }
}

function sendChat() {
    const input = document.getElementById('ai-input');
    const msg   = input.value.trim();
    if (!msg) return;

    addMessage('user', msg);
    input.value = '';
    setLoading(true);

    const data = new FormData();
    data.append('csrf_token', csrfToken);
    data.append('mensagem',   msg);
    data.append('workspace_id', workspaceId);

    fetch('/api/copilot/chat', { method: 'POST', body: data })
        .then(r => r.json())
        .then(function(res) {
            setLoading(false);
            if (res.ok) {
                addMessage('assistant', res.content);
            } else {
                addMessage('assistant', '⚠️ ' + (res.error || 'Erro ao processar sua mensagem.'));
            }
        })
        .catch(function() {
            setLoading(false);
            addMessage('assistant', '⚠️ Erro de conexão com o Copilot.');
        });
}

function sendQuick(msg) {
    document.getElementById('ai-input').value = msg;
    sendChat();
}

function gerarSugestaoIA() {
    const btn = document.getElementById('btn-ia-achados');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Gerando...'; }

    const data = new FormData();
    data.append('csrf_token',   csrfToken);
    data.append('workspace_id', workspaceId);
    data.append('modalidade',   '<?= htmlspecialchars($laudo->modalidade ?? '') ?>');
    data.append('indicacao',    document.getElementById('indicacao').value);
    data.append('achados',      document.getElementById('achados').value);

    fetch('/api/copilot/sugestao', { method: 'POST', body: data })
        .then(r => r.json())
        .then(function(res) {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-brain"></i> Sugerir com IA'; }
            if (res.ok) {
                addMessage('assistant', '**Sugestão gerada:**\n\n' + res.content);
            } else {
                addMessage('assistant', '⚠️ ' + (res.error || 'Erro ao gerar sugestão.'));
            }
        })
        .catch(function() {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-brain"></i> Sugerir com IA'; }
            addMessage('assistant', '⚠️ Erro de conexão com o Copilot.');
        });
}

// Enter no chat
document.getElementById('ai-input')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendChat();
    }
});

// Scroll ao fundo das mensagens
document.getElementById('ai-messages').scrollTop = document.getElementById('ai-messages').scrollHeight;
</script>
