<?php $this->layout('layout/copilot_header', $data); ?>
<style>
.air-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;}
.air-nav-btn{padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;border:1px solid #e2e8f0;background:#fff;color:#374151;text-decoration:none;transition:all .15s;}
.air-nav-btn:hover,.air-nav-btn.active{background:#2563eb;color:#fff;border-color:#2563eb;}
.air-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;}
.btn-primary{background:#2563eb;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;}
.btn-outline{background:#fff;color:#374151;border:1px solid #e2e8f0;padding:8px 16px;border-radius:8px;font-size:13px;cursor:pointer;}
.form-control{width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;color:#1e293b;box-sizing:border-box;}
.form-control:focus{outline:none;border-color:#2563eb;}
.form-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;}
.form-group{margin-bottom:16px;}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:16px;padding:32px;width:100%;max-width:720px;max-height:90vh;overflow-y:auto;}
.air-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.air-badge.blue{background:#eff6ff;color:#2563eb;}
.air-badge.green{background:#f0fdf4;color:#16a34a;}
.air-badge.gray{background:#f1f5f9;color:#64748b;}
.pt-card{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;transition:box-shadow .15s;}
.pt-card:hover{box-shadow:0 4px 12px rgba(37,99,235,.1);border-color:#bfdbfe;}
</style>
<div class="air-nav">
    <a href="/ai-router" class="air-nav-btn">📊 Dashboard</a>
    <a href="/ai-router/providers" class="air-nav-btn">🔌 Providers</a>
    <a href="/ai-router/modelos" class="air-nav-btn">🤖 Modelos</a>
    <a href="/ai-router/prompt-base" class="air-nav-btn">📋 Prompt Base</a>
    <a href="/ai-router/prompt-templates" class="air-nav-btn active">📝 Templates</a>
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
        <h2 style="font-size:18px;font-weight:700;color:#1e293b;margin:0;">Prompt Templates</h2>
        <p style="font-size:13px;color:#64748b;margin:4px 0 0;">Templates de prompt reutilizáveis para cada tipo de solicitação clínica</p>
    </div>
    <button class="btn-primary" onclick="abrirModal()">+ Novo Template</button>
</div>
<?php if (empty($data['templates'])): ?>
<div class="air-card" style="text-align:center;padding:48px;">
    <div style="font-size:48px;margin-bottom:16px;">📝</div>
    <h3 style="font-size:18px;font-weight:600;color:#1e293b;margin:0 0 8px;">Nenhum template criado</h3>
    <p style="color:#64748b;font-size:14px;margin:0 0 24px;">Crie templates de prompt para padronizar as solicitações ao AI Router</p>
    <button class="btn-primary" onclick="abrirModal()">+ Criar primeiro template</button>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;">
    <?php foreach ($data['templates'] as $t): ?>
    <div class="pt-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
            <div style="font-size:15px;font-weight:700;color:#1e293b;"><?= htmlspecialchars($t['nome']) ?></div>
            <span class="air-badge <?= $t['is_active'] ? 'green' : 'gray' ?>"><?= $t['is_active'] ? '● Ativo' : '○' ?></span>
        </div>
        <div style="display:flex;gap:6px;margin-bottom:8px;flex-wrap:wrap;">
            <span class="air-badge blue"><?= htmlspecialchars($t['tipo_solicitacao']) ?></span>
            <?php if ($t['especialidade']): ?><span class="air-badge gray"><?= htmlspecialchars($t['especialidade']) ?></span><?php endif; ?>
        </div>
        <div style="font-size:12px;color:#64748b;margin-bottom:12px;line-height:1.5;max-height:48px;overflow:hidden;"><?= htmlspecialchars(substr($t['template'],0,120)) ?>...</div>
        <div style="display:flex;gap:8px;">
            <button class="btn-outline" style="flex:1;" onclick="editarTemplate(<?= htmlspecialchars(json_encode($t)) ?>)">✏️ Editar</button>
            <button class="btn-outline" onclick="duplicarTemplate(<?= $t['id'] ?>)">📋</button>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="modal-overlay" id="modalTemplate">
    <div class="modal">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin:0;" id="modalTitle">Novo Template</h3>
            <button onclick="fecharModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#64748b;">✕</button>
        </div>
        <form onsubmit="salvarTemplate(event)">
            <input type="hidden" id="tplId" name="id" value="0">
            <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Nome *</label><input type="text" class="form-control" name="nome" id="tNome" placeholder="Ex: Gerar laudo TC tórax" required></div>
                <div class="form-group">
                    <label class="form-label">Tipo de Solicitação *</label>
                    <select class="form-control" name="tipo_solicitacao" required>
                        <option value="laudo_gerar">Gerar Laudo</option>
                        <option value="laudo_sugerir">Sugerir Achados</option>
                        <option value="laudo_revisar">Revisar Laudo</option>
                        <option value="laudo_cid">Sugerir CID</option>
                        <option value="chat">Chat</option>
                        <option value="resumo">Resumo</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Especialidade (opcional)</label>
                <select class="form-control" name="especialidade">
                    <option value="">Todas</option>
                    <option value="Radiologia Geral">Radiologia Geral</option>
                    <option value="Tomografia Computadorizada">TC</option>
                    <option value="Ressonância Magnética">RM</option>
                    <option value="Ultrassonografia">Ultrassonografia</option>
                    <option value="Medicina Nuclear">Medicina Nuclear</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Template do Prompt *</label>
                <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px;">
                    <?php foreach (['{{achados}}','{{indicacao}}','{{tecnica}}','{{historico}}','{{modalidade}}','{{nome_medico}}'] as $v): ?>
                    <button type="button" class="btn-outline" style="font-size:11px;padding:4px 8px;" onclick="inserirVar('<?= $v ?>')"><?= $v ?></button>
                    <?php endforeach; ?>
                </div>
                <textarea class="form-control" name="template" id="tTemplate" rows="10" placeholder="Analise os seguintes achados de {{modalidade}} e gere um laudo estruturado:&#10;&#10;Achados: {{achados}}&#10;Indicação: {{indicacao}}" required style="font-family:monospace;font-size:13px;resize:vertical;"></textarea>
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <button type="button" class="btn-outline" onclick="fecharModal()">Cancelar</button>
                <button type="submit" class="btn-primary">💾 Salvar Template</button>
            </div>
        </form>
    </div>
</div>
<script>
function abrirModal(){document.getElementById('modalTitle').textContent='Novo Template';document.getElementById('tplId').value=0;document.getElementById('tNome').value='';document.getElementById('tTemplate').value='';document.getElementById('modalTemplate').classList.add('open');}
function fecharModal(){document.getElementById('modalTemplate').classList.remove('open');}
function editarTemplate(t){document.getElementById('modalTitle').textContent='Editar Template';document.getElementById('tplId').value=t.id;document.getElementById('tNome').value=t.nome;document.getElementById('tTemplate').value=t.template;document.getElementById('modalTemplate').classList.add('open');}
function inserirVar(v){const t=document.getElementById('tTemplate');const s=t.selectionStart;t.value=t.value.substring(0,s)+v+t.value.substring(t.selectionEnd);t.selectionStart=t.selectionEnd=s+v.length;t.focus();}
function salvarTemplate(e){e.preventDefault();const fd=new FormData(e.target);fetch('/ai-router/prompt-templates/salvar',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{if(d.ok){fecharModal();location.reload();}else{alert('Erro: '+(d.erro||'Falha'));}});}
function duplicarTemplate(id){fetch('/ai-router/prompt-templates/duplicar',{method:'POST',body:new URLSearchParams({id,csrf_token:'<?= $data['csrf_token'] ?>'})}).then(r=>r.json()).then(d=>{if(d.ok)location.reload();});}
</script>
<?php $this->layout('layout/copilot_footer', $data); ?>
