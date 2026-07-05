<?php $this->layout('layout/copilot_header', ['title' => $title ?? 'Configurações', 'pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]); ?>

<div class="page-content">

  <?php if (isset($_GET['sucesso'])): ?>
  <div class="alert alert-success mb-4"><i class="fa-solid fa-check-circle"></i> Configurações salvas com sucesso!</div>
  <?php endif; ?>

  <div class="config-layout">

    <!-- Menu Lateral de Abas -->
    <div class="config-nav">
      <a href="#perfil" class="config-nav-item active" onclick="trocarAba('perfil',this)">
        <i class="fa-solid fa-user"></i> Perfil Médico
      </a>
      <a href="#ia" class="config-nav-item" onclick="trocarAba('ia',this)">
        <i class="fa-solid fa-robot"></i> Configurações de IA
      </a>
      <a href="#assinatura" class="config-nav-item" onclick="trocarAba('assinatura',this)">
        <i class="fa-solid fa-signature"></i> Assinatura Digital
      </a>
      <a href="#notificacoes" class="config-nav-item" onclick="trocarAba('notificacoes',this)">
        <i class="fa-solid fa-bell"></i> Notificações
      </a>
      <a href="#seguranca" class="config-nav-item" onclick="trocarAba('seguranca',this)">
        <i class="fa-solid fa-shield"></i> Segurança
      </a>
    </div>

    <!-- Conteúdo das Abas -->
    <div class="config-content">

      <!-- Aba: Perfil Médico -->
      <div id="aba-perfil" class="config-aba active">
        <form method="POST" action="/configuracoes/perfil">
          <div class="card">
            <div class="card-header-row"><h3 class="card-title"><i class="fa-solid fa-user"></i> Dados Pessoais</h3></div>
            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label">Nome completo</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($medico['nome'] ?? '') ?>" class="form-control" required>
              </div>
              <div class="form-group">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" value="<?= htmlspecialchars($medico['email'] ?? '') ?>" class="form-control" required>
              </div>
              <div class="form-group">
                <label class="form-label">CRM</label>
                <input type="text" name="crm" value="<?= htmlspecialchars($medico['crm'] ?? '') ?>" class="form-control">
              </div>
              <div class="form-group">
                <label class="form-label">UF do CRM</label>
                <select name="crm_uf" class="form-control">
                  <?php foreach (['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'] as $uf): ?>
                    <option value="<?= $uf ?>" <?= ($medico['crm_uf'] ?? '') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" value="<?= htmlspecialchars($medico['telefone'] ?? '') ?>" class="form-control">
              </div>
              <div class="form-group">
                <label class="form-label">Especialidades</label>
                <input type="text" name="especialidades" value="<?= htmlspecialchars($medico['especialidades'] ?? '') ?>" class="form-control" placeholder="Radiologia, TC, RM...">
              </div>
            </div>
            <div class="form-footer">
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Salvar Perfil</button>
            </div>
          </div>
        </form>
      </div>

      <!-- Aba: IA -->
      <div id="aba-ia" class="config-aba" style="display:none">
        <form method="POST" action="/configuracoes/ia">
          <div class="card">
            <div class="card-header-row"><h3 class="card-title"><i class="fa-solid fa-robot"></i> Modelo de IA</h3></div>
            <div class="form-group">
              <label class="form-label">Modelo padrão</label>
              <select name="ia_modelo" class="form-control">
                <option value="gpt-4o" <?= ($iaConfig['ia_modelo'] ?? '') === 'gpt-4o' ? 'selected' : '' ?>>GPT-4o (Recomendado)</option>
                <option value="gpt-4o-mini" <?= ($iaConfig['ia_modelo'] ?? '') === 'gpt-4o-mini' ? 'selected' : '' ?>>GPT-4o Mini (Econômico)</option>
                <option value="gpt-4-turbo" <?= ($iaConfig['ia_modelo'] ?? '') === 'gpt-4-turbo' ? 'selected' : '' ?>>GPT-4 Turbo</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Temperatura (criatividade) — <?= $iaConfig['ia_temperatura'] ?? '0.3' ?></label>
              <input type="range" name="ia_temperatura" min="0" max="1" step="0.1" value="<?= $iaConfig['ia_temperatura'] ?? '0.3' ?>" class="form-range" oninput="this.previousElementSibling.textContent = 'Temperatura (criatividade) — ' + this.value">
              <div class="range-labels"><span>Conservador</span><span>Criativo</span></div>
            </div>
            <div class="form-group">
              <label class="form-label">Estilo de escrita preferido</label>
              <select name="ia_estilo" class="form-control">
                <option value="formal" <?= ($iaConfig['ia_estilo'] ?? '') === 'formal' ? 'selected' : '' ?>>Formal (padrão hospitalar)</option>
                <option value="tecnico" <?= ($iaConfig['ia_estilo'] ?? '') === 'tecnico' ? 'selected' : '' ?>>Técnico (termos médicos completos)</option>
                <option value="conciso" <?= ($iaConfig['ia_estilo'] ?? '') === 'conciso' ? 'selected' : '' ?>>Conciso (objetivo e direto)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Vocabulário personalizado</label>
              <textarea name="ia_vocabulario" class="form-control" rows="4" placeholder="Termos e frases que você usa frequentemente nos laudos..."><?= htmlspecialchars($iaConfig['ia_vocabulario'] ?? '') ?></textarea>
            </div>
            <div class="form-footer">
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Salvar Configurações IA</button>
            </div>
          </div>
        </form>
      </div>

      <!-- Aba: Assinatura -->
      <div id="aba-assinatura" class="config-aba" style="display:none">
        <div class="card">
          <div class="card-header-row"><h3 class="card-title"><i class="fa-solid fa-signature"></i> Assinatura Digital</h3></div>
          <div class="assinatura-area">
            <canvas id="canvasAssinatura" width="600" height="200" class="assinatura-canvas"></canvas>
            <div class="assinatura-actions">
              <button onclick="limparAssinatura()" class="btn btn-outline"><i class="fa-solid fa-eraser"></i> Limpar</button>
              <button onclick="salvarAssinatura()" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Salvar Assinatura</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Aba: Segurança -->
      <div id="aba-seguranca" class="config-aba" style="display:none">
        <form method="POST" action="/configuracoes/senha">
          <div class="card">
            <div class="card-header-row"><h3 class="card-title"><i class="fa-solid fa-shield"></i> Alterar Senha</h3></div>
            <div class="form-group">
              <label class="form-label">Senha atual</label>
              <input type="password" name="senha_atual" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">Nova senha</label>
              <input type="password" name="nova_senha" class="form-control" required minlength="8">
            </div>
            <div class="form-group">
              <label class="form-label">Confirmar nova senha</label>
              <input type="password" name="confirmar_senha" class="form-control" required>
            </div>
            <div class="form-footer">
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-key"></i> Alterar Senha</button>
            </div>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>

<style>
.config-layout { display:grid; grid-template-columns:220px 1fr; gap:24px; }
.config-nav { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:8px; height:fit-content; }
.config-nav-item { display:flex; align-items:center; gap:10px; padding:10px 14px; border-radius:8px; font-size:13px; color:#475569; text-decoration:none; transition:all .2s; }
.config-nav-item:hover { background:#f8fafc; color:#1e293b; }
.config-nav-item.active { background:#eff6ff; color:#1a56db; font-weight:600; }
.form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.form-range { width:100%; accent-color:#1a56db; }
.range-labels { display:flex; justify-content:space-between; font-size:11px; color:#94a3b8; margin-top:2px; }
.assinatura-canvas { border:2px dashed #e2e8f0; border-radius:8px; cursor:crosshair; display:block; width:100%; max-width:600px; }
.assinatura-actions { display:flex; gap:8px; margin-top:12px; }
@media(max-width:768px) { .config-layout { grid-template-columns:1fr; } .form-grid-2 { grid-template-columns:1fr; } }
</style>

<script>
function trocarAba(nome, el) {
  event.preventDefault();
  document.querySelectorAll('.config-aba').forEach(a => a.style.display = 'none');
  document.querySelectorAll('.config-nav-item').forEach(a => a.classList.remove('active'));
  document.getElementById('aba-' + nome).style.display = 'block';
  el.classList.add('active');
}

// Canvas de Assinatura
const canvas = document.getElementById('canvasAssinatura');
if (canvas) {
  const ctx = canvas.getContext('2d');
  let desenhando = false;
  canvas.addEventListener('mousedown', e => { desenhando=true; ctx.beginPath(); ctx.moveTo(e.offsetX, e.offsetY); });
  canvas.addEventListener('mousemove', e => { if(!desenhando) return; ctx.lineTo(e.offsetX, e.offsetY); ctx.strokeStyle='#1e293b'; ctx.lineWidth=2; ctx.lineCap='round'; ctx.stroke(); });
  canvas.addEventListener('mouseup', () => desenhando=false);
  canvas.addEventListener('mouseleave', () => desenhando=false);
}

function limparAssinatura() {
  const canvas = document.getElementById('canvasAssinatura');
  canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
}

function salvarAssinatura() {
  const canvas = document.getElementById('canvasAssinatura');
  const dataUrl = canvas.toDataURL('image/png');
  fetch('/api/configuracoes/assinatura', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ assinatura: dataUrl })
  }).then(r => r.json()).then(d => { alert(d.ok ? 'Assinatura salva!' : 'Erro ao salvar.'); });
}
</script>

<?php $this->layout('layout/copilot_footer'); ?>
