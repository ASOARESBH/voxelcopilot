<style>
.mp-tabs{display:flex;gap:4px;border-bottom:2px solid #e2e8f0;margin-bottom:24px;}
.mp-tab{padding:10px 20px;font-size:14px;font-weight:500;color:#64748b;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .15s;}
.mp-tab.active{color:#2563eb;border-color:#2563eb;font-weight:600;}
.mp-tab:hover:not(.active){color:#374151;}
.mp-panel{display:none;}.mp-panel.active{display:block;}
.air-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;margin-bottom:20px;}
.btn-primary{background:#2563eb;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;}
.btn-primary:hover{background:#1d4ed8;}
.btn-outline{background:#fff;color:#374151;border:1px solid #e2e8f0;padding:8px 16px;border-radius:8px;font-size:13px;cursor:pointer;}
.form-control{width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;color:#1e293b;box-sizing:border-box;}
.form-control:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1);}
.form-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;}
.form-group{margin-bottom:16px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.stat-box{background:#f8fafc;border-radius:10px;padding:16px;text-align:center;}
.stat-val{font-size:28px;font-weight:700;color:#1e293b;}
.stat-lbl{font-size:12px;color:#64748b;margin-top:4px;}
.at-item{display:flex;align-items:center;gap:12px;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:8px;background:#fff;}
.at-atalho{font-family:monospace;font-size:12px;background:#eff6ff;color:#2563eb;padding:3px 8px;border-radius:4px;font-weight:700;white-space:nowrap;}
.at-texto{font-size:13px;color:#374151;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.at-cat{font-size:11px;color:#64748b;background:#f1f5f9;padding:2px 8px;border-radius:4px;}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:16px;padding:32px;width:100%;max-width:520px;}
.learn-bar{height:8px;background:#f1f5f9;border-radius:4px;overflow:hidden;margin-top:6px;}
.learn-fill{height:100%;background:linear-gradient(90deg,#2563eb,#0891b2);border-radius:4px;}
.vocab-tag{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:20px;font-size:12px;color:#1d4ed8;margin:4px;}
.section-title{font-size:15px;font-weight:700;color:#1e293b;margin:0 0 16px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;}
</style>

<!-- Header da página -->
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:22px;font-weight:800;color:#1e293b;margin:0;">🧠 Medical Profile</h1>
        <p style="font-size:14px;color:#64748b;margin:4px 0 0;">Seu perfil de aprendizado — a IA aprende com você a cada laudo</p>
    </div>
    <div style="display:flex;gap:8px;">
        <button class="btn-outline" onclick="abrirModalAutotexto()">+ Autotexto</button>
        <button class="btn-primary" onclick="salvarPerfil()">💾 Salvar Configurações</button>
    </div>
</div>

<!-- Stats de Aprendizado -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:16px;margin-bottom:24px;">
    <div class="stat-box">
        <div class="stat-val"><?= number_format($data['total_laudos']) ?></div>
        <div class="stat-lbl">Laudos Assinados</div>
    </div>
    <div class="stat-box">
        <div class="stat-val"><?= number_format($data['laudos_ultimo_mes']) ?></div>
        <div class="stat-lbl">Último Mês</div>
    </div>
    <div class="stat-box">
        <div class="stat-val"><?= count($data['autotextos']) ?></div>
        <div class="stat-lbl">Autotextos</div>
    </div>
    <div class="stat-box">
        <div class="stat-val"><?= count($data['vocabulario']) ?></div>
        <div class="stat-lbl">Termos Aprendidos</div>
    </div>
    <div class="stat-box">
        <div class="stat-val" style="font-size:20px;">
            <?php
            $total = $data['total_laudos'];
            $nivel = $total < 10 ? 'Iniciante' : ($total < 50 ? 'Básico' : ($total < 200 ? 'Intermediário' : ($total < 500 ? 'Avançado' : 'Expert')));
            echo $nivel;
            ?>
        </div>
        <div class="stat-lbl">Nível de Aprendizado</div>
        <div class="learn-bar" style="margin-top:8px;">
            <div class="learn-fill" style="width:<?= min(100, $total > 0 ? round($total / 500 * 100) : 2) ?>%;"></div>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="mp-tabs">
    <div class="mp-tab active" onclick="trocarTab('config')">⚙️ Configurações de IA</div>
    <div class="mp-tab" onclick="trocarTab('autotextos')">📝 Autotextos (<?= count($data['autotextos']) ?>)</div>
    <div class="mp-tab" onclick="trocarTab('vocabulario')">🔤 Vocabulário Aprendido</div>
    <div class="mp-tab" onclick="trocarTab('modalidades')">📊 Meu Perfil Clínico</div>
    <div class="mp-tab" onclick="trocarTab('instrucoes')">📋 Instruções Personalizadas</div>
</div>

<!-- Tab: Configurações de IA -->
<div class="mp-panel active" id="tab-config">
    <div class="air-card">
        <div class="section-title">🤖 Comportamento da IA</div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Estilo de Laudo</label>
                <select class="form-control" id="cfgEstilo">
                    <option value="formal" <?= ($data['perfil']['estilo_laudo'] ?? 'formal') === 'formal' ? 'selected' : '' ?>>Formal e Técnico</option>
                    <option value="objetivo" <?= ($data['perfil']['estilo_laudo'] ?? '') === 'objetivo' ? 'selected' : '' ?>>Objetivo e Direto</option>
                    <option value="descritivo" <?= ($data['perfil']['estilo_laudo'] ?? '') === 'descritivo' ? 'selected' : '' ?>>Descritivo e Detalhado</option>
                    <option value="academico" <?= ($data['perfil']['estilo_laudo'] ?? '') === 'academico' ? 'selected' : '' ?>>Acadêmico</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nível de Detalhe</label>
                <select class="form-control" id="cfgNivel">
                    <option value="resumido" <?= ($data['perfil']['nivel_detalhe'] ?? '') === 'resumido' ? 'selected' : '' ?>>Resumido</option>
                    <option value="padrao" <?= ($data['perfil']['nivel_detalhe'] ?? '') === 'padrao' ? 'selected' : '' ?>>Padrão</option>
                    <option value="detalhado" <?= ($data['perfil']['nivel_detalhe'] ?? 'detalhado') === 'detalhado' ? 'selected' : '' ?>>Detalhado</option>
                    <option value="completo" <?= ($data['perfil']['nivel_detalhe'] ?? '') === 'completo' ? 'selected' : '' ?>>Completo (máximo)</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Idioma de Resposta</label>
                <select class="form-control" id="cfgLinguagem">
                    <option value="pt-BR" <?= ($data['perfil']['linguagem_preferida'] ?? 'pt-BR') === 'pt-BR' ? 'selected' : '' ?>>Português (Brasil)</option>
                    <option value="en" <?= ($data['perfil']['linguagem_preferida'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
                    <option value="es" <?= ($data['perfil']['linguagem_preferida'] ?? '') === 'es' ? 'selected' : '' ?>>Español</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Provider Preferido</label>
                <select class="form-control" id="cfgProvider">
                    <option value="">— Usar padrão do sistema —</option>
                    <?php foreach ($data['providers'] as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($data['perfil']['provider_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nome']) ?> — <?= htmlspecialchars($p['modelo_padrao']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Temperatura (criatividade: 0=preciso, 1=criativo)</label>
                <input type="range" min="0" max="1" step="0.05" value="<?= $data['perfil']['temperatura'] ?? 0.1 ?>" id="cfgTemp" oninput="document.getElementById('cfgTempVal').textContent=this.value" style="width:100%;">
                <div style="text-align:right;font-size:12px;color:#64748b;margin-top:4px;">Valor: <strong id="cfgTempVal"><?= $data['perfil']['temperatura'] ?? 0.1 ?></strong></div>
            </div>
            <div class="form-group">
                <label class="form-label">Max Tokens por Resposta</label>
                <input type="number" class="form-control" id="cfgMaxTokens" value="<?= $data['perfil']['max_tokens'] ?? 4000 ?>" min="500" max="8000" step="500">
            </div>
        </div>
    </div>
</div>

<!-- Tab: Autotextos -->
<div class="mp-panel" id="tab-autotextos">
    <div class="air-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div class="section-title" style="margin:0;border:none;padding:0;">📝 Meus Autotextos</div>
            <button class="btn-primary" onclick="abrirModalAutotexto()">+ Novo Autotexto</button>
        </div>
        <p style="font-size:13px;color:#64748b;margin:0 0 16px;">Digite <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;">;</code> seguido do atalho no editor para expandir automaticamente.</p>
        <?php if (empty($data['autotextos'])): ?>
        <div style="text-align:center;padding:32px;color:#94a3b8;">
            <div style="font-size:40px;margin-bottom:12px;">📝</div>
            <div>Nenhum autotexto cadastrado. Crie atalhos para agilizar seus laudos.</div>
        </div>
        <?php else: ?>
        <?php foreach ($data['autotextos'] as $at): ?>
        <div class="at-item">
            <span class="at-atalho">;<?= htmlspecialchars($at['atalho']) ?></span>
            <span class="at-texto"><?= htmlspecialchars($at['texto']) ?></span>
            <span class="at-cat"><?= htmlspecialchars($at['categoria']) ?></span>
            <span style="font-size:11px;color:#94a3b8;white-space:nowrap;"><?= $at['uso_count'] ?>x</span>
            <button onclick="excluirAutotexto(<?= $at['id'] ?>)" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:14px;">✕</button>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Tab: Vocabulário Aprendido -->
<div class="mp-panel" id="tab-vocabulario">
    <div class="air-card">
        <div class="section-title">🔤 Vocabulário Aprendido pela IA</div>
        <p style="font-size:13px;color:#64748b;margin:0 0 16px;">Termos e expressões que a IA aprendeu com seus laudos anteriores para personalizar as sugestões.</p>
        <?php if (empty($data['vocabulario'])): ?>
        <div style="text-align:center;padding:32px;color:#94a3b8;">
            <div style="font-size:40px;margin-bottom:12px;">🔤</div>
            <div>A IA ainda está aprendendo seu vocabulário. Continue laudando!</div>
            <div style="font-size:12px;margin-top:8px;">O vocabulário é construído automaticamente após <?= max(0, 10 - $data['total_laudos']) ?> laudos assinados.</div>
        </div>
        <?php else: ?>
        <div style="margin-bottom:16px;">
            <?php foreach ($data['vocabulario'] as $termo => $freq): ?>
            <span class="vocab-tag">
                <?= htmlspecialchars($termo) ?>
                <span style="background:#2563eb;color:#fff;border-radius:10px;padding:1px 6px;font-size:10px;"><?= $freq ?>x</span>
            </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tab: Perfil Clínico -->
<div class="mp-panel" id="tab-modalidades">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div class="air-card">
            <div class="section-title">📊 Modalidades Mais Laudadas</div>
            <?php if (empty($data['modalidades'])): ?>
            <div style="text-align:center;padding:32px;color:#94a3b8;">Nenhum dado ainda</div>
            <?php else: ?>
            <?php
            $maxMod = max(array_column($data['modalidades'], 'total'));
            foreach ($data['modalidades'] as $m):
            ?>
            <div style="margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                    <span style="font-size:13px;font-weight:600;color:#374151;"><?= htmlspecialchars($m['modalidade']) ?></span>
                    <span style="font-size:12px;color:#64748b;"><?= $m['total'] ?> laudos</span>
                </div>
                <div class="learn-bar">
                    <div class="learn-fill" style="width:<?= $maxMod > 0 ? round($m['total']/$maxMod*100) : 0 ?>%;"></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="air-card">
            <div class="section-title">🤖 Uso da IA por Tipo</div>
            <?php if (empty($data['interacoes_ia'])): ?>
            <div style="text-align:center;padding:32px;color:#94a3b8;">Nenhuma interação com IA ainda</div>
            <?php else: ?>
            <?php foreach ($data['interacoes_ia'] as $ia): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                <span style="font-size:13px;color:#374151;"><?= htmlspecialchars($ia['tipo']) ?></span>
                <div style="text-align:right;">
                    <div style="font-size:14px;font-weight:700;color:#1e293b;"><?= number_format($ia['total']) ?>x</div>
                    <div style="font-size:11px;color:#64748b;">~<?= number_format($ia['media_tokens']) ?> tokens/chamada</div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="air-card" style="grid-column:1/-1;">
            <div class="section-title">📋 Templates Mais Utilizados</div>
            <?php if (empty($data['templates_mais_usados'])): ?>
            <div style="text-align:center;padding:24px;color:#94a3b8;">Nenhum template utilizado ainda</div>
            <?php else: ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
                <?php foreach ($data['templates_mais_usados'] as $t): ?>
                <div style="background:#f8fafc;border-radius:8px;padding:12px;">
                    <div style="font-size:13px;font-weight:600;color:#1e293b;margin-bottom:4px;"><?= htmlspecialchars($t['nome']) ?></div>
                    <div style="font-size:11px;color:#64748b;"><?= htmlspecialchars($t['modalidade'] ?: $t['especialidade'] ?: 'Geral') ?></div>
                    <div style="font-size:18px;font-weight:700;color:#2563eb;margin-top:8px;"><?= $t['uso_count'] ?>x</div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Tab: Instruções Personalizadas -->
<div class="mp-panel" id="tab-instrucoes">
    <div class="air-card">
        <div class="section-title">📋 Instruções Personalizadas para a IA</div>
        <p style="font-size:13px;color:#64748b;margin:0 0 16px;">Escreva instruções específicas que a IA deve seguir em todos os seus laudos. Exemplos: "Sempre mencionar a dose de radiação em TC", "Usar a escala BI-RADS em mamografias", "Nunca usar a palavra 'normal', substituir por 'sem alterações'."</p>
        <div class="form-group">
            <label class="form-label">Instruções Gerais</label>
            <textarea class="form-control" id="cfgInstrucoes" rows="6" placeholder="Ex: Sempre incluir correlação clínica ao final. Usar terminologia ACR. Mencionar comparação com exame anterior quando disponível."><?= htmlspecialchars($data['perfil']['instrucoes_personalizadas'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Frases de Conclusão Preferidas</label>
            <textarea class="form-control" id="cfgFrasesConclusao" rows="4" placeholder="Ex: Sugere-se correlação clínica e laboratorial.&#10;Recomenda-se seguimento em 6 meses.&#10;Achados inespecíficos, correlacionar com quadro clínico."><?= htmlspecialchars($data['perfil']['frases_conclusao'] ?? '') ?></textarea>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">Uma frase por linha. A IA usará essas frases como base para as conclusões.</div>
        </div>
        <div style="display:flex;justify-content:flex-end;">
            <button class="btn-primary" onclick="salvarPerfil()">💾 Salvar Instruções</button>
        </div>
    </div>
</div>

<!-- Modal Autotexto -->
<div class="modal-overlay" id="modalAutotexto">
    <div class="modal">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin:0;">Novo Autotexto</h3>
            <button onclick="fecharModalAutotexto()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#64748b;">✕</button>
        </div>
        <div class="form-group">
            <label class="form-label">Atalho * <span style="font-size:11px;color:#64748b;">(sem espaços, ex: nodpulm)</span></label>
            <input type="text" class="form-control" id="atAtalho" placeholder="nodpulm">
        </div>
        <div class="form-group">
            <label class="form-label">Categoria</label>
            <select class="form-control" id="atCategoria">
                <option value="geral">Geral</option>
                <option value="achados">Achados</option>
                <option value="tecnica">Técnica</option>
                <option value="conclusao">Conclusão</option>
                <option value="recomendacao">Recomendação</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Texto Expandido *</label>
            <textarea class="form-control" id="atTexto" rows="5" placeholder="Nódulo pulmonar solitário de contornos espiculados, medindo aproximadamente X mm, localizado no lobo superior direito..."></textarea>
        </div>
        <div style="display:flex;gap:12px;justify-content:flex-end;">
            <button class="btn-outline" onclick="fecharModalAutotexto()">Cancelar</button>
            <button class="btn-primary" onclick="salvarAutotexto()">💾 Salvar</button>
        </div>
    </div>
</div>

<script>
function trocarTab(id) {
    document.querySelectorAll('.mp-tab').forEach((t,i) => t.classList.remove('active'));
    document.querySelectorAll('.mp-panel').forEach(p => p.classList.remove('active'));
    const tabs = ['config','autotextos','vocabulario','modalidades','instrucoes'];
    const idx = tabs.indexOf(id);
    if (idx >= 0) {
        document.querySelectorAll('.mp-tab')[idx].classList.add('active');
        document.getElementById('tab-'+id).classList.add('active');
    }
}

function salvarPerfil() {
    const body = new URLSearchParams({
        estilo_laudo: document.getElementById('cfgEstilo').value,
        nivel_detalhe: document.getElementById('cfgNivel').value,
        linguagem_preferida: document.getElementById('cfgLinguagem').value,
        provider_id: document.getElementById('cfgProvider').value,
        temperatura: document.getElementById('cfgTemp').value,
        max_tokens: document.getElementById('cfgMaxTokens').value,
        instrucoes_personalizadas: document.getElementById('cfgInstrucoes').value,
        frases_conclusao: document.getElementById('cfgFrasesConclusao').value,
        csrf_token: '<?= $data['csrf_token'] ?>'
    });
    fetch('/medical-profile/salvar', {method:'POST', body})
        .then(r => r.json())
        .then(d => {
            if (d.ok) {
                const btn = document.querySelector('.btn-primary');
                btn.textContent = '✓ Salvo!';
                btn.style.background = '#16a34a';
                setTimeout(() => { btn.textContent = '💾 Salvar Configurações'; btn.style.background = ''; }, 2000);
            } else {
                alert('Erro ao salvar: ' + (d.erro || 'Falha'));
            }
        });
}

function abrirModalAutotexto() {
    document.getElementById('atAtalho').value = '';
    document.getElementById('atTexto').value = '';
    document.getElementById('modalAutotexto').classList.add('open');
}
function fecharModalAutotexto() {
    document.getElementById('modalAutotexto').classList.remove('open');
}
function salvarAutotexto() {
    const atalho = document.getElementById('atAtalho').value.trim();
    const texto = document.getElementById('atTexto').value.trim();
    const categoria = document.getElementById('atCategoria').value;
    if (!atalho || !texto) { alert('Preencha o atalho e o texto'); return; }
    fetch('/medical-profile/autotexto/salvar', {
        method: 'POST',
        body: new URLSearchParams({atalho, texto, categoria, csrf_token: '<?= $data['csrf_token'] ?>'})
    }).then(r => r.json()).then(d => {
        if (d.ok) { fecharModalAutotexto(); location.reload(); }
        else { alert('Erro: ' + (d.erro || 'Falha')); }
    });
}
function excluirAutotexto(id) {
    if (!confirm('Excluir este autotexto?')) return;
    fetch('/medical-profile/autotexto/excluir', {
        method: 'POST',
        body: new URLSearchParams({id, csrf_token: '<?= $data['csrf_token'] ?>'})
    }).then(r => r.json()).then(d => { if (d.ok) location.reload(); });
}
</script>
