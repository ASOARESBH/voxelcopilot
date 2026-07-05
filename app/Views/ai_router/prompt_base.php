<?php $this->layout('layout/copilot_header', $data); ?>
<style>
.air-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;}
.air-nav-btn{padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;border:1px solid #e2e8f0;background:#fff;color:#374151;text-decoration:none;transition:all .15s;}
.air-nav-btn:hover,.air-nav-btn.active{background:#2563eb;color:#fff;border-color:#2563eb;}
.air-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;}
.btn-primary{background:#2563eb;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;}
.btn-primary:hover{background:#1d4ed8;}
.btn-outline{background:#fff;color:#374151;border:1px solid #e2e8f0;padding:8px 16px;border-radius:8px;font-size:13px;cursor:pointer;}
.form-control{width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;color:#1e293b;box-sizing:border-box;}
.form-control:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1);}
.form-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;}
.form-group{margin-bottom:16px;}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:16px;padding:32px;width:100%;max-width:720px;max-height:90vh;overflow-y:auto;}
.pb-card{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;transition:box-shadow .15s;cursor:pointer;}
.pb-card:hover{box-shadow:0 4px 12px rgba(37,99,235,.1);border-color:#bfdbfe;}
.pb-card.default{border-color:#2563eb;}
.air-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.air-badge.blue{background:#eff6ff;color:#2563eb;}
.air-badge.green{background:#f0fdf4;color:#16a34a;}
.air-badge.gray{background:#f1f5f9;color:#64748b;}
</style>
<div class="air-nav">
    <a href="/ai-router" class="air-nav-btn">📊 Dashboard</a>
    <a href="/ai-router/providers" class="air-nav-btn">🔌 Providers</a>
    <a href="/ai-router/modelos" class="air-nav-btn">🤖 Modelos</a>
    <a href="/ai-router/prompt-base" class="air-nav-btn active">📋 Prompt Base</a>
    <a href="/ai-router/prompt-templates" class="air-nav-btn">📝 Templates</a>
    <a href="/ai-router/rotas" class="air-nav-btn">🔀 Rotas</a>
    <a href="/ai-router/historico" class="air-nav-btn">📜 Histórico</a>
    <a href="/ai-router/tokens" class="air-nav-btn">🪙 Tokens</a>
    <a href="/ai-router/custos" class="air-nav-btn">💰 Custos</a>
    <a href="/ai-router/logs" class="air-nav-btn">📋 Logs</a>
    <a href="/ai-router/testes" class="air-nav-btn">🧪 Testes</a>
    <a href="/ai-router/configuracoes" class="air-nav-btn">⚙️ Config</a>
</div>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <div>
        <h2 style="font-size:18px;font-weight:700;color:#1e293b;margin:0;">Prompt Base</h2>
        <p style="font-size:13px;color:#64748b;margin:4px 0 0;">Instruções de sistema que definem o comportamento da IA por especialidade</p>
    </div>
    <button class="btn-primary" onclick="abrirModal()">+ Novo Prompt Base</button>
</div>
<?php if (empty($data['prompts'])): ?>
<div class="air-card" style="text-align:center;padding:48px;">
    <div style="font-size:48px;margin-bottom:16px;">📋</div>
    <h3 style="font-size:18px;font-weight:600;color:#1e293b;margin:0 0 8px;">Nenhum prompt base configurado</h3>
    <p style="color:#64748b;font-size:14px;margin:0 0 24px;">O prompt base define como a IA se comporta ao gerar laudos médicos</p>
    <button class="btn-primary" onclick="abrirModal()">+ Criar primeiro prompt base</button>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;">
    <?php foreach ($data['prompts'] as $p): ?>
    <div class="pb-card <?= $p['is_default'] ? 'default' : '' ?>">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
            <div style="font-size:15px;font-weight:700;color:#1e293b;"><?= htmlspecialchars($p['nome']) ?></div>
            <div style="display:flex;gap:4px;">
                <?php if ($p['is_default']): ?><span class="air-badge blue">⭐ Padrão</span><?php endif; ?>
                <span class="air-badge <?= $p['is_active'] ? 'green' : 'gray' ?>"><?= $p['is_active'] ? '● Ativo' : '○' ?></span>
            </div>
        </div>
        <div style="font-size:12px;color:#2563eb;font-weight:500;margin-bottom:8px;"><?= htmlspecialchars($p['especialidade']) ?></div>
        <div style="font-size:12px;color:#64748b;margin-bottom:12px;line-height:1.5;max-height:48px;overflow:hidden;"><?= htmlspecialchars(substr($p['conteudo'],0,120)) ?>...</div>
        <div style="display:flex;gap:6px;font-size:11px;color:#94a3b8;margin-bottom:12px;">
            <span>v<?= $p['versao'] ?></span>
            <span>·</span>
            <span><?= strlen($p['conteudo']) ?> chars</span>
            <span>·</span>
            <span>Atualizado <?= date('d/m/Y', strtotime($p['updated_at'])) ?></span>
        </div>
        <div style="display:flex;gap:8px;">
            <button class="btn-outline" style="flex:1;" onclick="editarPrompt(<?= htmlspecialchars(json_encode($p)) ?>)">✏️ Editar</button>
            <button class="btn-outline" onclick="duplicarPrompt(<?= $p['id'] ?>)">📋 Duplicar</button>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="modal-overlay" id="modalPrompt">
    <div class="modal">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin:0;" id="modalTitle">Novo Prompt Base</h3>
            <button onclick="fecharModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#64748b;">✕</button>
        </div>
        <form onsubmit="salvarPrompt(event)">
            <input type="hidden" id="promptId" name="id" value="0">
            <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Nome *</label>
                    <input type="text" class="form-control" name="nome" id="pNome" placeholder="Ex: Radiologista Sênior" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Especialidade *</label>
                    <select class="form-control" name="especialidade" id="pEsp" required>
                        <option value="Radiologia Geral">Radiologia Geral</option>
                        <option value="Tomografia Computadorizada">Tomografia Computadorizada</option>
                        <option value="Ressonância Magnética">Ressonância Magnética</option>
                        <option value="Ultrassonografia">Ultrassonografia</option>
                        <option value="Medicina Nuclear">Medicina Nuclear</option>
                        <option value="Mamografia">Mamografia</option>
                        <option value="Radiologia Intervencionista">Radiologia Intervencionista</option>
                        <option value="Neuroradiologia">Neuroradiologia</option>
                        <option value="Cardiologia">Cardiologia</option>
                        <option value="Geral">Geral</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Conteúdo do Prompt *</label>
                <div style="display:flex;gap:8px;margin-bottom:8px;">
                    <button type="button" class="btn-outline" style="font-size:12px;" onclick="inserirVariavel('{{nome_medico}}')">{{nome_medico}}</button>
                    <button type="button" class="btn-outline" style="font-size:12px;" onclick="inserirVariavel('{{especialidade}}')">{{especialidade}}</button>
                    <button type="button" class="btn-outline" style="font-size:12px;" onclick="inserirVariavel('{{modalidade}}')">{{modalidade}}</button>
                    <button type="button" class="btn-outline" style="font-size:12px;" onclick="inserirVariavel('{{historico_paciente}}')">{{historico_paciente}}</button>
                </div>
                <textarea class="form-control" name="conteudo" id="pConteudo" rows="12" placeholder="Você é um assistente especializado em radiologia médica. Seu papel é auxiliar o Dr. {{nome_medico}}, especialista em {{especialidade}}..." required style="font-family:monospace;font-size:13px;resize:vertical;"></textarea>
                <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Use variáveis como {{nome_medico}}, {{especialidade}}, {{modalidade}}, {{historico_paciente}}, {{achados_anteriores}}</div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
                <input type="checkbox" name="is_default" id="pDefault" value="1" style="width:16px;height:16px;">
                <label for="pDefault" style="font-size:14px;color:#374151;cursor:pointer;">Definir como prompt base padrão</label>
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <button type="button" class="btn-outline" onclick="fecharModal()">Cancelar</button>
                <button type="submit" class="btn-primary">💾 Salvar Prompt Base</button>
            </div>
        </form>
    </div>
</div>
<script>
function abrirModal(){document.getElementById('modalTitle').textContent='Novo Prompt Base';document.getElementById('promptId').value=0;document.getElementById('pNome').value='';document.getElementById('pConteudo').value='';document.getElementById('pDefault').checked=false;document.getElementById('modalPrompt').classList.add('open');}
function fecharModal(){document.getElementById('modalPrompt').classList.remove('open');}
function editarPrompt(p){document.getElementById('modalTitle').textContent='Editar Prompt Base';document.getElementById('promptId').value=p.id;document.getElementById('pNome').value=p.nome;document.getElementById('pEsp').value=p.especialidade;document.getElementById('pConteudo').value=p.conteudo;document.getElementById('pDefault').checked=p.is_default==1;document.getElementById('modalPrompt').classList.add('open');}
function inserirVariavel(v){const t=document.getElementById('pConteudo');const s=t.selectionStart;t.value=t.value.substring(0,s)+v+t.value.substring(t.selectionEnd);t.selectionStart=t.selectionEnd=s+v.length;t.focus();}
function salvarPrompt(e){e.preventDefault();const fd=new FormData(e.target);fetch('/ai-router/prompt-base/salvar',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{if(d.ok){fecharModal();location.reload();}else{alert('Erro: '+(d.erro||'Falha'));}});}
function duplicarPrompt(id){fetch('/ai-router/prompt-base/duplicar',{method:'POST',body:new URLSearchParams({id,csrf_token:'<?= $data['csrf_token'] ?>'})}).then(r=>r.json()).then(d=>{if(d.ok)location.reload();});}
</script>
<?php $this->layout('layout/copilot_footer', $data); ?>
