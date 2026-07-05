<?php $this->layout('layout/copilot_header', ['title' => $title ?? 'Speech', 'pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]); ?>

<div class="page-content">
  <div class="speech-layout">

    <!-- Gravador -->
    <div class="card speech-recorder-card">
      <div class="speech-recorder">
        <div class="recorder-visual" id="recorderVisual">
          <div class="recorder-waves" id="recorderWaves">
            <?php for($i=0;$i<20;$i++): ?><div class="wave-bar"></div><?php endfor; ?>
          </div>
          <button id="btnGravar" onclick="toggleGravacao()" class="btn-record">
            <i class="fa-solid fa-microphone" id="iconGravar"></i>
          </button>
        </div>
        <div class="recorder-status" id="recorderStatus">Clique para iniciar o ditado</div>
        <div class="recorder-timer" id="recorderTimer">00:00</div>
      </div>

      <div class="speech-output">
        <label class="form-label-sm">Transcrição</label>
        <textarea id="transcricaoOutput" class="form-control speech-textarea" placeholder="A transcrição aparecerá aqui automaticamente..." rows="8" readonly></textarea>
        <div class="speech-actions">
          <button onclick="limparTranscricao()" class="btn btn-outline"><i class="fa-solid fa-trash"></i> Limpar</button>
          <button onclick="copiarTranscricao()" class="btn btn-outline"><i class="fa-solid fa-copy"></i> Copiar</button>
          <button onclick="usarNoWorkspace()" class="btn btn-primary"><i class="fa-solid fa-file-pen"></i> Usar no Workspace</button>
        </div>
      </div>
    </div>

    <!-- Histórico -->
    <div class="card speech-history-card">
      <div class="card-header-row">
        <h3 class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Histórico de Ditados</h3>
      </div>
      <div class="speech-history-list">
        <?php foreach ($historico as $h): ?>
        <div class="speech-history-item">
          <div class="speech-history-header">
            <span class="speech-history-data"><?= date('d/m/Y H:i', strtotime($h['data'])) ?></span>
            <span class="speech-history-dur"><i class="fa-regular fa-clock"></i> <?= $h['duracao'] ?></span>
            <span class="speech-history-words"><?= $h['palavras'] ?> palavras</span>
          </div>
          <p class="speech-history-preview"><?= htmlspecialchars(mb_substr($h['preview'], 0, 120)) ?>...</p>
          <div class="speech-history-actions">
            <button onclick="restaurarTranscricao(<?= $h['id'] ?>)" class="btn btn-sm btn-outline">
              <i class="fa-solid fa-rotate-left"></i> Restaurar
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<style>
.speech-layout { display:grid; grid-template-columns:1fr 360px; gap:24px; }
.speech-recorder-card { padding:24px; }
.speech-recorder { display:flex; flex-direction:column; align-items:center; gap:16px; padding:32px 0; border-bottom:1px solid #e2e8f0; margin-bottom:24px; }
.recorder-visual { position:relative; width:160px; height:160px; display:flex; align-items:center; justify-content:center; }
.recorder-waves { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; gap:3px; }
.wave-bar { width:4px; height:16px; background:#bfdbfe; border-radius:2px; transition:height .1s; }
.recording .wave-bar { animation:wave .6s ease-in-out infinite; background:#1a56db; }
.wave-bar:nth-child(odd) { animation-delay:.1s; }
.wave-bar:nth-child(3n) { animation-delay:.2s; }
@keyframes wave { 0%,100%{height:8px} 50%{height:40px} }
.btn-record { width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg,#1a56db,#0d2244); border:none; color:#fff; font-size:28px; cursor:pointer; position:relative; z-index:1; box-shadow:0 4px 20px rgba(26,86,219,.4); transition:all .2s; }
.btn-record:hover { transform:scale(1.05); }
.btn-record.recording { background:linear-gradient(135deg,#dc2626,#991b1b); animation:pulse 1.5s infinite; }
.recorder-status { font-size:14px; color:#64748b; font-weight:500; }
.recorder-timer { font-size:32px; font-weight:700; color:#1e293b; font-family:monospace; }
.speech-textarea { font-size:14px; line-height:1.7; resize:vertical; }
.speech-actions { display:flex; gap:8px; justify-content:flex-end; margin-top:12px; }
.speech-history-card { padding:0; overflow:hidden; }
.speech-history-list { display:flex; flex-direction:column; }
.speech-history-item { padding:16px 20px; border-bottom:1px solid #f1f5f9; }
.speech-history-item:last-child { border-bottom:none; }
.speech-history-header { display:flex; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap; }
.speech-history-data { font-size:12px; font-weight:600; color:#1e293b; }
.speech-history-dur, .speech-history-words { font-size:11px; color:#94a3b8; display:flex; align-items:center; gap:3px; }
.speech-history-preview { font-size:12px; color:#64748b; margin:0 0 8px; line-height:1.5; }
.speech-history-actions { display:flex; justify-content:flex-end; }
@media(max-width:768px) { .speech-layout { grid-template-columns:1fr; } }
</style>

<script>
let mediaRecorder = null, audioChunks = [], timerInterval = null, segundos = 0;

function toggleGravacao() {
  if (mediaRecorder && mediaRecorder.state === 'recording') {
    pararGravacao();
  } else {
    iniciarGravacao();
  }
}

async function iniciarGravacao() {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    mediaRecorder = new MediaRecorder(stream);
    audioChunks = [];
    mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
    mediaRecorder.onstop = () => transcreverAudio();
    mediaRecorder.start();

    document.getElementById('btnGravar').classList.add('recording');
    document.getElementById('iconGravar').className = 'fa-solid fa-stop';
    document.getElementById('recorderStatus').textContent = 'Gravando... fale seu laudo';
    document.getElementById('recorderWaves').classList.add('recording');

    segundos = 0;
    timerInterval = setInterval(() => {
      segundos++;
      const m = String(Math.floor(segundos/60)).padStart(2,'0');
      const s = String(segundos%60).padStart(2,'0');
      document.getElementById('recorderTimer').textContent = `${m}:${s}`;
    }, 1000);
  } catch(e) {
    alert('Erro ao acessar microfone: ' + e.message);
  }
}

function pararGravacao() {
  if (mediaRecorder) {
    mediaRecorder.stop();
    mediaRecorder.stream.getTracks().forEach(t => t.stop());
    mediaRecorder = null;
  }
  clearInterval(timerInterval);
  document.getElementById('btnGravar').classList.remove('recording');
  document.getElementById('iconGravar').className = 'fa-solid fa-microphone';
  document.getElementById('recorderStatus').textContent = 'Transcrevendo...';
  document.getElementById('recorderWaves').classList.remove('recording');
}

async function transcreverAudio() {
  const blob = new Blob(audioChunks, { type: 'audio/webm' });
  const form = new FormData();
  form.append('audio', blob, 'audio.webm');

  try {
    const res = await fetch('/api/speech/transcrever', { method: 'POST', body: form });
    const data = await res.json();
    if (data.ok) {
      document.getElementById('transcricaoOutput').value += (document.getElementById('transcricaoOutput').value ? '\n' : '') + data.texto;
      document.getElementById('recorderStatus').textContent = 'Transcrição concluída!';
    } else {
      document.getElementById('recorderStatus').textContent = 'Erro: ' + data.error;
    }
  } catch(e) {
    document.getElementById('recorderStatus').textContent = 'Erro de conexão.';
  }
}

function limparTranscricao() { document.getElementById('transcricaoOutput').value = ''; }
function copiarTranscricao() {
  navigator.clipboard.writeText(document.getElementById('transcricaoOutput').value);
  alert('Copiado!');
}
function usarNoWorkspace() {
  const texto = document.getElementById('transcricaoOutput').value;
  if (texto) {
    sessionStorage.setItem('speechText', texto);
    window.location.href = '/workspace/novo?speech=1';
  }
}
</script>

<?php $this->layout('layout/copilot_footer'); ?>
