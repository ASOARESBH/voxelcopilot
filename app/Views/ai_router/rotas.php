<style>
.air-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;}
.air-nav-btn{padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;border:1px solid #e2e8f0;background:#fff;color:#374151;text-decoration:none;transition:all .15s;}
.air-nav-btn:hover,.air-nav-btn.active{background:#2563eb;color:#fff;border-color:#2563eb;}
.air-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;}
.air-table{width:100%;border-collapse:collapse;font-size:13px;}
.air-table th{background:#f8fafc;color:#64748b;font-weight:600;text-transform:uppercase;font-size:11px;letter-spacing:.5px;padding:10px 12px;text-align:left;border-bottom:1px solid #e2e8f0;}
.air-table td{padding:10px 12px;border-bottom:1px solid #f1f5f9;color:#374151;vertical-align:middle;}
.air-table tr:last-child td{border-bottom:none;}
.air-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.air-badge.ok{background:#f0fdf4;color:#16a34a;}
.air-badge.off{background:#f1f5f9;color:#64748b;}
.btn-primary{background:#2563eb;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;}
.btn-outline{background:#fff;color:#374151;border:1px solid #e2e8f0;padding:6px 12px;border-radius:6px;font-size:12px;cursor:pointer;}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:16px;padding:32px;width:100%;max-width:600px;max-height:90vh;overflow-y:auto;}
.form-control{width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;color:#1e293b;box-sizing:border-box;}
.form-control:focus{outline:none;border-color:#2563eb;}
.form-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;}
.form-group{margin-bottom:16px;}
</style>
<div class="air-nav">
    <a href="/ai-router" class="air-nav-btn">📊 Dashboard</a>
    <a href="/ai-router/providers" class="air-nav-btn">🔌 Providers</a>
    <a href="/ai-router/modelos" class="air-nav-btn">🤖 Modelos</a>
    <a href="/ai-router/prompt-base" class="air-nav-btn">📋 Prompt Base</a>
    <a href="/ai-router/prompt-templates" class="air-nav-btn">📝 Templates</a>
    <a href="/ai-router/rotas" class="air-nav-btn active">🔀 Rotas</a>
    <a href="/ai-router/historico" class="air-nav-btn">📜 Histórico</a>
    <a href="/ai-router/tokens" class="air-nav-btn">🪙 Tokens</a>
    <a href="/ai-router/custos" class="air-nav-btn">💰 Custos</a>
    <a href="/ai-router/logs" class="air-nav-btn">📋 Logs</a>
    <a href="/ai-router/testes" class="air-nav-btn">🧪 Testes</a>
    <a href="/ai-router/configuracoes" class="air-nav-btn">⚙️ Config</a>
</div>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <div>
        <h2 style="font-size:18px;font-weight:700;color:#1e293b;margin:0;">Rotas Inteligentes</h2>
        <p style="font-size:13px;color:#64748b;margin:4px 0 0;">Defina qual provider/modelo usar para cada tipo de solicitação ou especialidade</p>
    </div>
    <button class="btn-primary" onclick="abrirModal()">+ Nova Rota</button>
</div>
<div class="air-card">
    <?php if (empty($data['rotas'])): ?>
    <div style="text-align:center;padding:48px;color:#94a3b8;">
        <div style="font-size:48px;margin-bottom:16px;">🔀</div>
        <div style="font-size:16px;font-weight:600;color:#374151;margin-bottom:8px;">Nenhuma rota configurada</div>
        <div style="font-size:13px;margin-bottom:24px;">Sem rotas, o AI Router usa o provider padrão para todas as solicitações</div>
        <button class="btn-primary" onclick="abrirModal()">+ Criar primeira rota</button>
    </div>
    <?php else: ?>
    <table class="air-table">
        <thead><tr><th>Nome</th><th>Tipo Solicitação</th><th>Especialidade</th><th>Provider</th><th>Modelo Override</th><th>Prioridade</th><th>Status</th><th>Ações</th></tr></thead>
        <tbody>
            <?php foreach ($data['rotas'] as $r): ?>
            <tr>
                <td style="font-weight:600;"><?= htmlspecialchars($r['nome']) ?></td>
                <td><?= htmlspecialchars($r['tipo_solicitacao'] ?: 'Todos') ?></td>
                <td><?= htmlspecialchars($r['especialidade'] ?: 'Todas') ?></td>
                <td><?= htmlspecialchars($r['provider_nome']) ?></td>
                <td style="font-family:monospace;font-size:11px;"><?= htmlspecialchars($r['modelo_override'] ?: '—') ?></td>
                <td style="text-align:center;"><?= $r['prioridade'] ?></td>
                <td><span class="air-badge <?= $r['is_active'] ? 'ok' : 'off' ?>"><?= $r['is_active'] ? '● Ativo' : '○' ?></span></td>
                <td>
                    <button class="btn-outline" onclick="editarRota(<?= htmlspecialchars(json_encode($r)) ?>)">✏️</button>
                    <button class="btn-outline" style="color:#dc2626;border-color:#fecaca;" onclick="excluirRota(<?= $r['id'] ?>)">🗑</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<div class="modal-overlay" id="modalRota">
    <div class="modal">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin:0;" id="modalTitle">Nova Rota</h3>
            <button onclick="fecharModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#64748b;">✕</button>
        </div>
        <form onsubmit="salvarRota(event)">
            <input type="hidden" id="rotaId" name="id" value="0">
            <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
            <div class="form-group"><label class="form-label">Nome *</label><input type="text" class="form-control" name="nome" id="rNome" placeholder="Ex: Laudos Oncológicos" required></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Tipo de Solicitação</label>
                    <select class="form-control" name="tipo_solicitacao">
                        <option value="">Todos</option>
                        <option value="laudo_gerar">Gerar Laudo</option>
                        <option value="laudo_sugerir">Sugerir Achados</option>
                        <option value="laudo_revisar">Revisar Laudo</option>
                        <option value="laudo_cid">Sugerir CID</option>
                        <option value="chat">Chat</option>
                        <option value="resumo">Resumo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Especialidade</label>
                    <select class="form-control" name="especialidade">
                        <option value="">Todas</option>
                        <option value="Radiologia Geral">Radiologia Geral</option>
                        <option value="Tomografia Computadorizada">TC</option>
                        <option value="Ressonância Magnética">RM</option>
                        <option value="Ultrassonografia">Ultrassonografia</option>
                        <option value="Medicina Nuclear">Medicina Nuclear</option>
                        <option value="Mamografia">Mamografia</option>
                        <option value="Neuroradiologia">Neuroradiologia</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Provider *</label>
                <select class="form-control" name="provider_id" required>
                    <?php foreach ($data['providers'] as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Modelo Override (opcional)</label><input type="text" class="form-control" name="modelo_override" placeholder="Ex: gpt-4o-mini"></div>
                <div class="form-group"><label class="form-label">Prioridade (menor = primeiro)</label><input type="number" class="form-control" name="prioridade" value="10" min="1" max="100"></div>
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <button type="button" class="btn-outline" onclick="fecharModal()">Cancelar</button>
                <button type="submit" class="btn-primary">💾 Salvar Rota</button>
            </div>
        </form>
    </div>
</div>
<script>
function abrirModal(){document.getElementById('modalTitle').textContent='Nova Rota';document.getElementById('rotaId').value=0;document.getElementById('rNome').value='';document.getElementById('modalRota').classList.add('open');}
function fecharModal(){document.getElementById('modalRota').classList.remove('open');}
function editarRota(r){document.getElementById('modalTitle').textContent='Editar Rota';document.getElementById('rotaId').value=r.id;document.getElementById('rNome').value=r.nome;document.getElementById('modalRota').classList.add('open');}
function salvarRota(e){e.preventDefault();const fd=new FormData(e.target);fetch('/ai-router/rotas/salvar',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{if(d.ok){fecharModal();location.reload();}else{alert('Erro: '+(d.erro||'Falha'));}});}
function excluirRota(id){if(!confirm('Excluir esta rota?'))return;fetch('/ai-router/rotas/excluir',{method:'POST',body:new URLSearchParams({id,csrf_token:'<?= $data['csrf_token'] ?>'})}).then(r=>r.json()).then(d=>{if(d.ok)location.reload();});}
</script>
