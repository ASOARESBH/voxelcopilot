<?php $this->layout('layout/copilot_header', ['title' => $title ?? 'Copilot IA', 'pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]); ?>

<div class="page-content">
  <div class="copilot-layout">

    <!-- Painel Lateral -->
    <div class="copilot-sidebar">
      <!-- Stats IA -->
      <div class="card mb-3">
        <div class="card-header-row"><h4 class="card-title"><i class="fa-solid fa-chart-line"></i> Seu Copilot</h4></div>
        <div class="ia-stats-grid">
          <div class="ia-stat"><span class="ia-stat-num"><?= $statsIA['laudos_gerados'] ?></span><span class="ia-stat-label">Laudos gerados</span></div>
          <div class="ia-stat"><span class="ia-stat-num"><?= $statsIA['tempo_economizado'] ?></span><span class="ia-stat-label">Tempo economizado</span></div>
          <div class="ia-stat"><span class="ia-stat-num"><?= $statsIA['precisao'] ?></span><span class="ia-stat-label">Precisão</span></div>
          <div class="ia-stat"><span class="ia-stat-num"><?= $statsIA['correcoes'] ?></span><span class="ia-stat-label">Correções</span></div>
        </div>
      </div>

      <!-- Ações Rápidas -->
      <div class="card mb-3">
        <div class="card-header-row"><h4 class="card-title"><i class="fa-solid fa-bolt"></i> Ações Rápidas</h4></div>
        <div class="quick-actions-list">
          <button onclick="enviarMensagem('Gere um laudo de TC de tórax com contraste normal')" class="quick-action-btn">
            <i class="fa-solid fa-file-medical"></i> Gerar laudo TC Tórax
          </button>
          <button onclick="enviarMensagem('Quais os critérios de Fleischner para nódulos pulmonares?')" class="quick-action-btn">
            <i class="fa-solid fa-book-medical"></i> Critérios de Fleischner
          </button>
          <button onclick="enviarMensagem('Sugira diagnóstico diferencial para hipodensidade cerebral em TC')" class="quick-action-btn">
            <i class="fa-solid fa-brain"></i> DD Hipodensidade TC
          </button>
          <button onclick="enviarMensagem('Gere uma conclusão para RM de encéfalo normal')" class="quick-action-btn">
            <i class="fa-solid fa-pen-nib"></i> Conclusão RM Encéfalo
          </button>
        </div>
      </div>
    </div>

    <!-- Chat Principal -->
    <div class="copilot-chat-area">
      <div class="chat-container" id="chatContainer">
        <div class="chat-welcome">
          <div class="chat-welcome-icon"><i class="fa-solid fa-robot"></i></div>
          <h3>Olá, Dr. <?= htmlspecialchars($_SESSION['user']->name ?? 'Médico') ?>!</h3>
          <p>Sou seu Copilot de diagnóstico por imagem. Posso ajudar com laudos, diagnósticos diferenciais, protocolos e pesquisa clínica.</p>
        </div>
      </div>

      <div class="chat-input-area">
        <div class="chat-input-wrapper">
          <textarea id="chatInput" placeholder="Digite sua mensagem... (Enter para enviar, Shift+Enter para nova linha)" rows="1"></textarea>
          <div class="chat-input-actions">
            <button onclick="toggleVoz()" class="chat-btn-icon" title="Ditado por voz" id="btnVoz">
              <i class="fa-solid fa-microphone"></i>
            </button>
            <button onclick="enviarChat()" class="btn btn-primary" id="btnEnviar">
              <i class="fa-solid fa-paper-plane"></i> Enviar
            </button>
          </div>
        </div>
        <div class="chat-disclaimer">
          <i class="fa-solid fa-circle-info"></i>
          O Copilot é uma ferramenta de apoio. O laudo final é de responsabilidade do médico.
        </div>
      </div>
    </div>

  </div>
</div>

<style>
.copilot-layout { display:grid; grid-template-columns:280px 1fr; gap:24px; height:calc(100vh - 160px); }
.copilot-sidebar { display:flex; flex-direction:column; overflow-y:auto; }
.ia-stats-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.ia-stat { text-align:center; padding:12px; background:#f8fafc; border-radius:8px; }
.ia-stat-num { display:block; font-size:20px; font-weight:700; color:#1a56db; }
.ia-stat-label { display:block; font-size:10px; color:#94a3b8; text-transform:uppercase; }
.quick-actions-list { display:flex; flex-direction:column; gap:6px; }
.quick-action-btn { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:10px 12px; text-align:left; font-size:12px; color:#475569; cursor:pointer; display:flex; align-items:center; gap:8px; transition:all .2s; }
.quick-action-btn:hover { background:#eff6ff; border-color:#bfdbfe; color:#1a56db; }
.copilot-chat-area { display:flex; flex-direction:column; background:#fff; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden; }
.chat-container { flex:1; overflow-y:auto; padding:24px; display:flex; flex-direction:column; gap:16px; }
.chat-welcome { text-align:center; padding:40px 20px; color:#64748b; }
.chat-welcome-icon { width:64px; height:64px; background:linear-gradient(135deg,#1a56db,#0d2244); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:28px; color:#fff; margin:0 auto 16px; }
.chat-welcome h3 { color:#1e293b; margin-bottom:8px; }
.chat-msg { display:flex; gap:12px; max-width:80%; }
.chat-msg--user { align-self:flex-end; flex-direction:row-reverse; }
.chat-msg--ia { align-self:flex-start; }
.chat-msg-avatar { width:36px; height:36px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:14px; }
.chat-msg--user .chat-msg-avatar { background:#1a56db; color:#fff; }
.chat-msg--ia .chat-msg-avatar { background:#f1f5f9; color:#1a56db; }
.chat-msg-bubble { padding:12px 16px; border-radius:12px; font-size:14px; line-height:1.6; white-space:pre-wrap; }
.chat-msg--user .chat-msg-bubble { background:#1a56db; color:#fff; border-bottom-right-radius:4px; }
.chat-msg--ia .chat-msg-bubble { background:#f8fafc; color:#1e293b; border:1px solid #e2e8f0; border-bottom-left-radius:4px; }
.chat-typing { display:flex; gap:4px; align-items:center; padding:12px 16px; }
.chat-typing span { width:8px; height:8px; background:#94a3b8; border-radius:50%; animation:typing .8s infinite; }
.chat-typing span:nth-child(2) { animation-delay:.2s; }
.chat-typing span:nth-child(3) { animation-delay:.4s; }
@keyframes typing { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-6px)} }
.chat-input-area { border-top:1px solid #e2e8f0; padding:16px; }
.chat-input-wrapper { display:flex; gap:8px; align-items:flex-end; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:8px 12px; }
.chat-input-wrapper:focus-within { border-color:#1a56db; box-shadow:0 0 0 3px rgba(26,86,219,.1); }
#chatInput { flex:1; border:none; background:transparent; resize:none; font-size:14px; color:#1e293b; outline:none; max-height:120px; line-height:1.5; }
.chat-input-actions { display:flex; gap:8px; align-items:center; flex-shrink:0; }
.chat-btn-icon { width:36px; height:36px; border:1px solid #e2e8f0; border-radius:8px; background:#fff; color:#64748b; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .2s; }
.chat-btn-icon:hover { border-color:#1a56db; color:#1a56db; }
.chat-btn-icon.recording { background:#dc2626; color:#fff; border-color:#dc2626; animation:pulse 1s infinite; }
.chat-disclaimer { font-size:11px; color:#94a3b8; text-align:center; margin-top:8px; display:flex; align-items:center; justify-content:center; gap:4px; }
@media(max-width:768px) { .copilot-layout { grid-template-columns:1fr; height:auto; } .copilot-sidebar { order:2; } }
</style>

<script>
const chatContainer = document.getElementById('chatContainer');
const chatInput     = document.getElementById('chatInput');
const btnEnviar     = document.getElementById('btnEnviar');
let isLoading = false;

chatInput.addEventListener('keydown', e => {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); enviarChat(); }
});

chatInput.addEventListener('input', () => {
  chatInput.style.height = 'auto';
  chatInput.style.height = Math.min(chatInput.scrollHeight, 120) + 'px';
});

function enviarMensagem(texto) {
  chatInput.value = texto;
  enviarChat();
}

function adicionarMsg(texto, tipo) {
  const div = document.createElement('div');
  div.className = `chat-msg chat-msg--${tipo}`;
  const icon = tipo === 'user' ? '<?= mb_substr($_SESSION['user']->name ?? 'DR', 0, 2) ?>' : '<i class="fa-solid fa-robot"></i>';
  div.innerHTML = `
    <div class="chat-msg-avatar">${icon}</div>
    <div class="chat-msg-bubble">${texto.replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>').replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>')}</div>
  `;
  chatContainer.appendChild(div);
  chatContainer.scrollTop = chatContainer.scrollHeight;
  return div;
}

function adicionarTyping() {
  const div = document.createElement('div');
  div.className = 'chat-msg chat-msg--ia';
  div.id = 'typingIndicator';
  div.innerHTML = `<div class="chat-msg-avatar"><i class="fa-solid fa-robot"></i></div><div class="chat-msg-bubble chat-typing"><span></span><span></span><span></span></div>`;
  chatContainer.appendChild(div);
  chatContainer.scrollTop = chatContainer.scrollHeight;
}

async function enviarChat() {
  const msg = chatInput.value.trim();
  if (!msg || isLoading) return;

  // Remove welcome se existir
  const welcome = chatContainer.querySelector('.chat-welcome');
  if (welcome) welcome.remove();

  adicionarMsg(msg, 'user');
  chatInput.value = '';
  chatInput.style.height = 'auto';
  isLoading = true;
  btnEnviar.disabled = true;
  adicionarTyping();

  try {
    const res = await fetch('/api/copilot/chat', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ mensagem: msg, contexto: {} })
    });
    const data = await res.json();
    document.getElementById('typingIndicator')?.remove();
    adicionarMsg(data.resposta || data.error || 'Erro ao processar.', 'ia');
  } catch(e) {
    document.getElementById('typingIndicator')?.remove();
    adicionarMsg('Erro de conexão. Verifique sua configuração de IA.', 'ia');
  } finally {
    isLoading = false;
    btnEnviar.disabled = false;
  }
}

// Speech Recognition
let recognition = null;
function toggleVoz() {
  const btn = document.getElementById('btnVoz');
  if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
    alert('Seu navegador não suporta reconhecimento de voz. Use o módulo Speech dedicado.');
    return;
  }
  if (recognition) { recognition.stop(); recognition = null; btn.classList.remove('recording'); return; }
  recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
  recognition.lang = 'pt-BR';
  recognition.continuous = false;
  recognition.interimResults = false;
  recognition.onresult = e => { chatInput.value += e.results[0][0].transcript + ' '; };
  recognition.onend = () => { btn.classList.remove('recording'); recognition = null; };
  recognition.start();
  btn.classList.add('recording');
}
</script>

<?php $this->layout('layout/copilot_footer'); ?>
