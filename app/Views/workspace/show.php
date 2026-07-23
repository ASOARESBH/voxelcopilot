<?php
$extraCss = ['/assets/css/workspace.css?v=3.0.0'];
$isRadiologista = !empty($layoutRadiologista);

// Cabeçalho e assinatura via ReportEngineService
$cabecalho = $reportCabecalho ?? [];
$assinatura = $reportAssinatura ?? [];
$qualityAlertas = $qualityAlertas ?? [];
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

        <!-- Buscar Template -->
        <?php if ($laudo->status === 'rascunho' && (!empty($templates) || !empty($mascarasBiblioteca))): ?>
        <div class="ws-template-search-wrap" id="tpl-wrap">
            <div class="ws-template-search-box" onclick="abrirBuscaTemplate()" id="tpl-box">
                <i class="fa-solid fa-magnifying-glass" style="color:var(--muted);font-size:.8rem;"></i>
                <span id="tpl-placeholder">Buscar Template...</span>
            </div>
            <div class="ws-template-dropdown" id="tpl-dropdown" style="display:none;">
                <div class="ws-template-search-input-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="tpl-search-input" placeholder="Buscar por nome ou máscara..."
                        oninput="filtrarTemplates(this.value)" autocomplete="off">
                </div>
                <div class="ws-template-list" id="tpl-list">
                    <?php if (!empty($templates)): ?>
                    <div class="ws-tpl-group-label">Meus Templates</div>
                    <?php foreach ($templates as $t): ?>
                    <div class="ws-tpl-item" data-id="<?= (int)$t->id ?>" data-tipo="template"
                         data-nome="<?= htmlspecialchars(strtolower($t->nome)) ?>"
                         onclick="selecionarTemplate(<?= (int)$t->id ?>, 'template', <?= json_encode($t->nome) ?>)">
                        <i class="fa-regular fa-file-lines" style="color:var(--blue-500);"></i>
                        <div>
                            <div class="ws-tpl-nome"><?= htmlspecialchars($t->nome) ?></div>
                            <?php if ($t->modalidade): ?>
                            <div class="ws-tpl-meta"><?= htmlspecialchars($t->modalidade) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!empty($mascarasBiblioteca)): ?>
                    <div class="ws-tpl-group-label">Biblioteca de Máscaras</div>
                    <?php foreach ($mascarasBiblioteca as $m): ?>
                    <div class="ws-tpl-item" data-id="<?= (int)$m->id ?>" data-tipo="mascara"
                         data-nome="<?= htmlspecialchars(strtolower($m->nome)) ?>"
                         onclick="selecionarTemplate(<?= (int)$m->id ?>, 'mascara', <?= json_encode($m->nome) ?>)">
                        <i class="fa-solid fa-layer-group" style="color:var(--blue-400);"></i>
                        <div>
                            <div class="ws-tpl-nome"><?= htmlspecialchars($m->nome) ?></div>
                            <?php if ($m->modalidade): ?>
                            <div class="ws-tpl-meta"><?= htmlspecialchars($m->modalidade) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Viewer PACS -->
        <?php if ($pacsViewerUrl): ?>
        <a href="<?= htmlspecialchars($pacsViewerUrl) ?>" target="_blank" class="ws-btn ws-btn-ghost" title="Abrir no Viewer PACS">
            <i class="fa-solid fa-eye"></i> Viewer
        </a>
        <?php endif; ?>

        <!-- Revisar Laudo (Report Engine) -->
        <?php if ($laudo->status === 'rascunho'): ?>
        <button class="ws-btn ws-btn-review" id="btn-revisar" onclick="revisarLaudo()" title="Revisão completa por IA: ortografia, terminologia, consistência, lateralidade">
            <i class="fa-solid fa-spell-check"></i> Revisar Laudo
        </button>
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

<!-- ── QUALITY ENGINE — Alertas de validação ── -->
<?php if (!empty($qualityAlertas)): ?>
<div class="ws-quality-bar" id="quality-bar">
    <div class="ws-quality-bar-header" onclick="toggleQualityBar()">
        <span>
            <i class="fa-solid fa-shield-halved"></i>
            <strong>Quality Engine</strong> —
            <?php
            $erros   = array_filter($qualityAlertas, fn($a) => $a['tipo'] === 'erro');
            $avisos  = array_filter($qualityAlertas, fn($a) => $a['tipo'] === 'aviso');
            $infos   = array_filter($qualityAlertas, fn($a) => $a['tipo'] === 'info');
            $total   = count($qualityAlertas);
            echo "{$total} " . ($total === 1 ? 'alerta' : 'alertas');
            if ($erros)  echo ' · <span class="qe-badge qe-erro">' . count($erros)  . ' erro'  . (count($erros)  > 1 ? 's' : '') . '</span>';
            if ($avisos) echo ' · <span class="qe-badge qe-aviso">' . count($avisos) . ' aviso' . (count($avisos) > 1 ? 's' : '') . '</span>';
            if ($infos)  echo ' · <span class="qe-badge qe-info">' . count($infos)  . ' info'  . (count($infos)  > 1 ? 's' : '') . '</span>';
            ?>
        </span>
        <i class="fa-solid fa-chevron-down" id="qe-chevron"></i>
    </div>
    <div class="ws-quality-list" id="quality-list">
        <?php foreach ($qualityAlertas as $alerta): ?>
        <div class="ws-quality-item ws-quality-<?= htmlspecialchars($alerta['tipo']) ?>">
            <?php if ($alerta['tipo'] === 'erro'):  echo '<i class="fa-solid fa-circle-xmark"></i>'; endif; ?>
            <?php if ($alerta['tipo'] === 'aviso'): echo '<i class="fa-solid fa-triangle-exclamation"></i>'; endif; ?>
            <?php if ($alerta['tipo'] === 'info'):  echo '<i class="fa-solid fa-circle-info"></i>'; endif; ?>
            <span><?= htmlspecialchars($alerta['msg']) ?></span>
            <?php if (!empty($alerta['campo']) && $alerta['campo'] !== 'geral'): ?>
            <button class="qe-ir-btn" onclick="irParaCampo('<?= htmlspecialchars($alerta['campo']) ?>')">
                Ir para o campo
            </button>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ── LAYOUT PRINCIPAL ── -->
<div class="ws-body">

    <!-- ── EDITOR ── -->
    <div class="ws-editor">

        <!-- ═══════════════════════════════════════════════════════════
             BLOCO 1: CABEÇALHO (gerado automaticamente, não editável)
             ═══════════════════════════════════════════════════════════ -->
        <div class="ws-section-card ws-section-cabecalho">
            <div class="ws-section-header">
                <div class="ws-section-title">
                    <i class="fa-solid fa-building-columns"></i>
                    Cabeçalho
                </div>
                <span class="ws-section-badge ws-badge-auto">
                    <i class="fa-solid fa-lock"></i> Gerado automaticamente
                </span>
            </div>
            <div class="ws-cabecalho-grid">
                <div class="ws-cabecalho-inst">
                    <?php if (!empty($cabecalho['logo_url'])): ?>
                    <img src="<?= htmlspecialchars($cabecalho['logo_url']) ?>" alt="Logo" class="ws-cabecalho-logo">
                    <?php else: ?>
                    <div class="ws-cabecalho-logo-placeholder">
                        <i class="fa-solid fa-hospital"></i>
                    </div>
                    <?php endif; ?>
                    <div class="ws-cabecalho-inst-info">
                        <strong><?= htmlspecialchars($cabecalho['instituicao'] ?? 'VOXEL Copilot') ?></strong>
                        <?php if (!empty($cabecalho['endereco'])): ?>
                        <span><?= htmlspecialchars($cabecalho['endereco']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($cabecalho['telefone'])): ?>
                        <span><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($cabecalho['telefone']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($cabecalho['cnpj'])): ?>
                        <span>CNPJ: <?= htmlspecialchars($cabecalho['cnpj']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="ws-cabecalho-meta">
                    <div class="ws-cabecalho-meta-row">
                        <span class="ws-cabecalho-label">Nº do Exame</span>
                        <span class="ws-cabecalho-value"><?= htmlspecialchars($cabecalho['numero_exame'] ?? '—') ?></span>
                    </div>
                    <div class="ws-cabecalho-meta-row">
                        <span class="ws-cabecalho-label">Código Interno</span>
                        <span class="ws-cabecalho-value"><?= htmlspecialchars($cabecalho['codigo_interno'] ?? '—') ?></span>
                    </div>
                    <?php if (!empty($cabecalho['codigo_tiss'])): ?>
                    <div class="ws-cabecalho-meta-row">
                        <span class="ws-cabecalho-label">Código TISS</span>
                        <span class="ws-cabecalho-value"><?= htmlspecialchars($cabecalho['codigo_tiss']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="ws-cabecalho-meta-row">
                        <span class="ws-cabecalho-label">Data</span>
                        <span class="ws-cabecalho-value"><?= htmlspecialchars($cabecalho['data_exame'] ?? date('d/m/Y')) ?></span>
                    </div>
                    <div class="ws-cabecalho-meta-row">
                        <span class="ws-cabecalho-label">Hora</span>
                        <span class="ws-cabecalho-value"><?= htmlspecialchars($cabecalho['hora_exame'] ?? date('H:i')) ?></span>
                    </div>
                    <?php if (!empty($cabecalho['modalidade'])): ?>
                    <div class="ws-cabecalho-meta-row">
                        <span class="ws-cabecalho-label">Modalidade</span>
                        <span class="ws-cabecalho-value"><?= htmlspecialchars($cabecalho['modalidade']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             BLOCO 2: IDENTIFICAÇÃO (importado do RIS/PACS, não editável)
             ═══════════════════════════════════════════════════════════ -->
        <div class="ws-section-card ws-section-identificacao">
            <div class="ws-section-header">
                <div class="ws-section-title">
                    <i class="fa-solid fa-id-card"></i>
                    Identificação
                </div>
                <span class="ws-section-badge ws-badge-auto">
                    <i class="fa-solid fa-database"></i> RIS/PACS
                </span>
            </div>
            <div class="ws-identificacao-grid">
                <div class="ws-id-row">
                    <span class="ws-id-label">Paciente</span>
                    <span class="ws-id-value ws-id-destaque"><?= htmlspecialchars($laudo->patient_nome ?? 'Não identificado') ?></span>
                </div>
                <?php if (!empty($laudo->patient_sexo)): ?>
                <div class="ws-id-row">
                    <span class="ws-id-label">Sexo</span>
                    <span class="ws-id-value"><?= $laudo->patient_sexo === 'M' ? 'Masculino' : ($laudo->patient_sexo === 'F' ? 'Feminino' : htmlspecialchars($laudo->patient_sexo)) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($laudo->patient_idade)): ?>
                <div class="ws-id-row">
                    <span class="ws-id-label">Idade</span>
                    <span class="ws-id-value"><?= htmlspecialchars($laudo->patient_idade) ?> anos</span>
                </div>
                <?php endif; ?>
                <?php if (!empty($laudo->patient_nascimento)): ?>
                <div class="ws-id-row">
                    <span class="ws-id-label">Nascimento</span>
                    <span class="ws-id-value"><?= date('d/m/Y', strtotime($laudo->patient_nascimento)) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($laudo->patient_uid)): ?>
                <div class="ws-id-row">
                    <span class="ws-id-label">Prontuário</span>
                    <span class="ws-id-value" style="font-family:monospace;font-size:11px;"><?= htmlspecialchars($laudo->patient_uid) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($laudo->convenio)): ?>
                <div class="ws-id-row">
                    <span class="ws-id-label">Convênio</span>
                    <span class="ws-id-value"><?= htmlspecialchars($laudo->convenio) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($laudo->medico_solicitante)): ?>
                <div class="ws-id-row">
                    <span class="ws-id-label">Solicitante</span>
                    <span class="ws-id-value"><?= htmlspecialchars($laudo->medico_solicitante) ?></span>
                </div>
                <?php endif; ?>
                <div class="ws-id-row">
                    <span class="ws-id-label">Data do Exame</span>
                    <span class="ws-id-value"><?= date('d/m/Y', strtotime($laudo->created_at)) ?></span>
                </div>
                <div class="ws-id-row">
                    <span class="ws-id-label">Data do Laudo</span>
                    <span class="ws-id-value"><?= $laudo->assinado_em ? date('d/m/Y', strtotime($laudo->assinado_em)) : '<em style="color:var(--muted)">Pendente assinatura</em>' ?></span>
                </div>
                <?php if (!empty($laudo->study_uid)): ?>
                <div class="ws-id-row">
                    <span class="ws-id-label">Study UID</span>
                    <span class="ws-id-value" style="font-family:monospace;font-size:10px;word-break:break-all;"><?= htmlspecialchars($laudo->study_uid) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($laudo->accession_number)): ?>
                <div class="ws-id-row">
                    <span class="ws-id-label">Accession</span>
                    <span class="ws-id-value" style="font-family:monospace;"><?= htmlspecialchars($laudo->accession_number) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             BLOCO 3: INDICAÇÃO CLÍNICA (nunca deixar vazia)
             ═══════════════════════════════════════════════════════════ -->
        <div class="ws-section-card <?= empty($laudo->indicacao) ? 'ws-section-alerta' : '' ?>" id="card-indicacao">
            <div class="ws-section-header">
                <div class="ws-section-title">
                    <i class="fa-solid fa-clipboard-question"></i>
                    Indicação Clínica
                    <?php if (empty($laudo->indicacao)): ?>
                    <span class="ws-section-obrigatorio" title="Indicação clínica é obrigatória">
                        <i class="fa-solid fa-triangle-exclamation"></i> Obrigatória
                    </span>
                    <?php endif; ?>
                </div>
                <?php if ($laudo->status === 'rascunho'): ?>
                <div class="ws-section-actions">
                    <button class="ws-btn ws-btn-ghost ws-btn-xs" onclick="sugerirIndicacao()" title="Sugerir indicação clínica com IA">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Sugerir
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <div class="ws-section-body">
                <textarea class="ws-textarea" id="indicacao" name="indicacao"
                    placeholder="Informe a indicação clínica do exame. Ex: Dor abdominal. Controle oncológico. Investigação de metástases."
                    style="min-height:64px;"
                    <?= $laudo->status !== 'rascunho' ? 'readonly' : '' ?>
                ><?= htmlspecialchars($laudo->indicacao ?? '') ?></textarea>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             BLOCO 4: TÉCNICA (gerada automaticamente por modalidade)
             ═══════════════════════════════════════════════════════════ -->
        <div class="ws-section-card" id="card-tecnica">
            <div class="ws-section-header">
                <div class="ws-section-title">
                    <i class="fa-solid fa-gears"></i>
                    Técnica
                </div>
                <?php if ($laudo->status === 'rascunho'): ?>
                <div class="ws-section-actions">
                    <button class="ws-btn ws-btn-ghost ws-btn-xs" onclick="gerarTecnicaAuto()" title="Gerar técnica padrão para a modalidade">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Gerar Auto
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <div class="ws-section-body">
                <textarea class="ws-textarea" id="tecnica" name="tecnica"
                    placeholder="Descreva a técnica utilizada. Use 'Gerar Auto' para preencher automaticamente com base na modalidade."
                    style="min-height:80px;"
                    <?= $laudo->status !== 'rascunho' ? 'readonly' : '' ?>
                ><?= htmlspecialchars($laudo->tecnica ?? '') ?></textarea>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             BLOCO 5: ACHADOS (descrição objetiva — nunca interpretação)
             ═══════════════════════════════════════════════════════════ -->
        <div class="ws-section-card ws-section-achados" id="card-achados">
            <div class="ws-section-header">
                <div class="ws-section-title">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Achados
                    <span class="ws-section-regra" title="Apenas descrição objetiva. Nunca diagnóstico ou interpretação.">
                        <i class="fa-solid fa-circle-info"></i> Apenas descrição objetiva
                    </span>
                </div>
                <?php if ($laudo->status === 'rascunho'): ?>
                <div class="ws-section-actions">
                    <button class="ws-btn ws-btn-ai ws-btn-xs" onclick="gerarSugestaoIA()" id="btn-ia-achados">
                        <i class="fa-solid fa-brain"></i> Sugerir com IA
                    </button>
                    <button class="ws-btn ws-btn-ghost ws-btn-xs" onclick="aplicarDicionario('achados')" title="Padronizar terminologia radiológica">
                        <i class="fa-solid fa-book-medical"></i> Padronizar
                    </button>
                    <?php if (!empty($autotextos)): ?>
                    <button class="ws-btn ws-btn-ghost ws-btn-xs" onclick="toggleAutotextos()">
                        <i class="fa-solid fa-bolt"></i> Autotextos
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
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
                    placeholder="Descreva os achados objetivamente, por sistemas/órgãos. Não use: provável, compatível, sugere, indica, pode representar, favorece. Essas expressões pertencem à Impressão Diagnóstica."
                    style="min-height:<?= $isRadiologista ? '340px' : '200px' ?>;"
                    <?= $laudo->status !== 'rascunho' ? 'readonly' : '' ?>
                ><?= htmlspecialchars($laudo->achados ?? '') ?></textarea>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             BLOCO 6: IMPRESSÃO DIAGNÓSTICA (somente interpretação)
             ═══════════════════════════════════════════════════════════ -->
        <div class="ws-section-card ws-section-impressao" id="card-impressao">
            <div class="ws-section-header">
                <div class="ws-section-title">
                    <i class="fa-solid fa-lightbulb"></i>
                    Impressão Diagnóstica
                    <span class="ws-section-regra" title="Somente interpretação clínica, em tópicos com bullet points.">
                        <i class="fa-solid fa-circle-info"></i> Somente interpretação
                    </span>
                </div>
                <?php if ($laudo->status === 'rascunho'): ?>
                <div class="ws-section-actions">
                    <button class="ws-btn ws-btn-ai ws-btn-xs" onclick="gerarImpressaoIA()" id="btn-ia-impressao">
                        <i class="fa-solid fa-brain"></i> Gerar com IA
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <div class="ws-section-body">
                <textarea class="ws-textarea" id="impressao" name="impressao"
                    placeholder="• Interpretação clínica em tópicos&#10;• Resumo objetivo dos achados mais relevantes&#10;• Correlação com a indicação clínica&#10;• Recomendações de conduta (se aplicável)"
                    style="min-height:120px;"
                    <?= $laudo->status !== 'rascunho' ? 'readonly' : '' ?>
                ><?= htmlspecialchars($laudo->impressao ?? '') ?></textarea>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             BLOCO 7: RECOMENDAÇÕES + CID (opcional)
             ═══════════════════════════════════════════════════════════ -->
        <div class="ws-row-2col">
            <div class="ws-section-card" id="card-recomendacao">
                <div class="ws-section-header">
                    <div class="ws-section-title">
                        <i class="fa-solid fa-notes-medical"></i>
                        Recomendações
                        <span class="ws-section-opcional">opcional</span>
                    </div>
                    <?php if ($laudo->status === 'rascunho'): ?>
                    <div class="ws-section-actions">
                        <button class="ws-btn ws-btn-ghost ws-btn-xs" onclick="sendQuick('Sugira recomendações baseadas nos achados e impressão diagnóstica, incluindo protocolos aplicáveis (Lung-RADS, BI-RADS, PI-RADS, LI-RADS, Fleischner, Bosniak, ACR)')" title="Sugerir recomendações com IA">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Sugerir
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="ws-section-body">
                    <textarea class="ws-textarea" id="recomendacao" name="recomendacao"
                        placeholder="Recomendações para o clínico solicitante. Gerado quando há protocolos aplicáveis (Lung-RADS, BI-RADS, PI-RADS, LI-RADS, Fleischner, Bosniak, ACR) ou por decisão do médico."
                        style="min-height:80px;"
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
                    <?php if ($laudo->status === 'rascunho'): ?>
                    <div class="ws-section-actions">
                        <button class="ws-btn ws-btn-ghost ws-btn-xs" onclick="sendQuick('Sugira o CID-10 mais adequado para este caso')" title="Sugerir CID-10 com IA">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Sugerir
                        </button>
                    </div>
                    <?php endif; ?>
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

        <!-- ═══════════════════════════════════════════════════════════
             EXAMES ANTERIORES
             ═══════════════════════════════════════════════════════════ -->
        <?php if (!empty($examesAnteriores)): ?>
        <div class="ws-section-card ws-exames-anteriores" id="ws-exames-ant">
            <div class="ws-section-header">
                <div class="ws-section-title">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Exames Anteriores
                    <span class="ws-exames-badge"><?= count($examesAnteriores) ?></span>
                </div>
                <div class="ws-section-actions">
                    <span class="ws-exames-hint">Laudos anteriores deste paciente</span>
                </div>
            </div>
            <div class="ws-exames-tabs" id="ws-exames-tabs">
                <?php foreach ($examesAnteriores as $i => $ex): ?>
                <button class="ws-exame-tab <?= $i === 0 ? 'active' : '' ?>"
                        onclick="abrirAbaExame(<?= $i ?>)"
                        data-idx="<?= $i ?>">
                    <span class="ws-exame-tab-mod"><?= htmlspecialchars($ex->modalidade ?? '?') ?></span>
                    <span class="ws-exame-tab-data"><?= $ex->assinado_em ? date('d/m/Y', strtotime($ex->assinado_em)) : date('d/m/Y', strtotime($ex->created_at)) ?></span>
                </button>
                <?php endforeach; ?>
            </div>
            <?php foreach ($examesAnteriores as $i => $ex): ?>
            <div class="ws-exame-content <?= $i === 0 ? 'active' : '' ?>" id="ws-exame-<?= $i ?>">
                <div class="ws-exame-meta">
                    <span><i class="fa-solid fa-stethoscope"></i> <?= htmlspecialchars($ex->modalidade ?? 'N/A') ?></span>
                    <span><i class="fa-solid fa-calendar"></i> <?= $ex->assinado_em ? date('d/m/Y', strtotime($ex->assinado_em)) : '—' ?></span>
                    <?php if ($ex->cid): ?>
                    <span><i class="fa-solid fa-tag"></i> CID: <?= htmlspecialchars($ex->cid) ?></span>
                    <?php endif; ?>
                    <a href="/workspace/<?= $ex->id ?>" target="_blank" class="ws-exame-link">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Ver laudo
                    </a>
                </div>
                <?php if ($ex->achados): ?>
                <div class="ws-exame-secao">
                    <div class="ws-exame-secao-titulo"><i class="fa-solid fa-magnifying-glass"></i> Achados</div>
                    <div class="ws-exame-texto"><?= nl2br(htmlspecialchars($ex->achados)) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($ex->impressao): ?>
                <div class="ws-exame-secao">
                    <div class="ws-exame-secao-titulo"><i class="fa-solid fa-lightbulb"></i> Impressão Diagnóstica</div>
                    <div class="ws-exame-texto"><?= nl2br(htmlspecialchars($ex->impressao)) ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ═══════════════════════════════════════════════════════════
             BLOCO 8: ASSINATURA DIGITAL (gerada automaticamente)
             ═══════════════════════════════════════════════════════════ -->
        <?php if ($laudo->status === 'assinado' && !empty($assinatura)): ?>
        <div class="ws-section-card ws-section-assinatura">
            <div class="ws-section-header">
                <div class="ws-section-title">
                    <i class="fa-solid fa-signature"></i>
                    Assinatura Digital
                </div>
                <span class="ws-section-badge ws-badge-assinado-badge">
                    <i class="fa-solid fa-circle-check"></i> Assinado
                </span>
            </div>
            <div class="ws-assinatura-grid">
                <div class="ws-assinatura-medico">
                    <div class="ws-assinatura-nome"><?= htmlspecialchars($assinatura['nome'] ?? '') ?></div>
                    <?php if (!empty($assinatura['crm'])): ?>
                    <div class="ws-assinatura-crm">CRM: <?= htmlspecialchars($assinatura['crm']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($assinatura['rqe'])): ?>
                    <div class="ws-assinatura-rqe">RQE: <?= htmlspecialchars($assinatura['rqe']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($assinatura['especialidade'])): ?>
                    <div class="ws-assinatura-esp"><?= htmlspecialchars($assinatura['especialidade']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="ws-assinatura-meta">
                    <?php if (!empty($assinatura['assinado_em'])): ?>
                    <div class="ws-assinatura-meta-row">
                        <span class="ws-assinatura-label">Assinado em</span>
                        <span class="ws-assinatura-value"><?= date('d/m/Y \à\s H:i', strtotime($assinatura['assinado_em'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="ws-assinatura-meta-row">
                        <span class="ws-assinatura-label">Hash</span>
                        <span class="ws-assinatura-value ws-hash" title="<?= htmlspecialchars($assinatura['hash_completo'] ?? '') ?>">
                            <?= htmlspecialchars($assinatura['hash'] ?? '') ?>
                        </span>
                    </div>
                    <div class="ws-assinatura-meta-row">
                        <span class="ws-assinatura-label">Versão</span>
                        <span class="ws-assinatura-value">v<?= htmlspecialchars($assinatura['versao'] ?? '1') ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php elseif ($laudo->status === 'assinado'): ?>
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
                <span class="ws-study-dot" style="background:<?= $laudo->status === 'assinado' ? '#059669' : '#d97706' ?>;"></span>
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
                <?php if ($isRadiologista): ?>
                <div class="ws-study-row">
                    <span class="ws-study-label">Layout</span>
                    <span class="ws-study-value" style="color:var(--blue-600);font-size:.75rem;font-weight:600;">
                        <i class="fa-solid fa-x-ray"></i> Radiologista
                    </span>
                </div>
                <?php endif; ?>
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
                <div class="ws-copilot-model">Report Engine</div>
            </div>

            <!-- Sugestões rápidas -->
            <div class="ws-quick-actions ws-quick-actions-row">
                <button class="ws-quick-btn" onclick="gerarImpressaoIA()" title="Gerar Impressão Diagnóstica">
                    <i class="fa-solid fa-lightbulb"></i> Impressão
                </button>
                <button class="ws-quick-btn" onclick="revisarLaudo()" title="Revisar Laudo completo">
                    <i class="fa-solid fa-spell-check"></i> Revisar
                </button>
                <button class="ws-quick-btn" onclick="sendQuick('Sugira recomendações para o médico solicitante baseadas nos achados e impressão diagnóstica')" title="Sugerir Recomendações">
                    <i class="fa-solid fa-notes-medical"></i> Recomen.
                </button>
                <button class="ws-quick-btn" onclick="sendQuick('Sugira o CID-10 mais adequado para este caso')" title="Sugerir CID-10">
                    <i class="fa-solid fa-tag"></i> CID
                </button>
                <button class="ws-quick-btn ws-quick-btn-chat" onclick="focarChat()" title="Chat com o Copilot">
                    <i class="fa-solid fa-comments"></i> Chat
                </button>
            </div>

            <!-- Mensagens -->
            <div class="ws-chat-messages" id="ai-messages">
                <?php if (empty($conversas)): ?>
                <div class="ws-chat-msg ws-chat-msg-ai">
                    <div class="ws-chat-bubble">
                        <strong>Olá, Dr. <?= htmlspecialchars(explode(' ', $_SESSION['user']->name ?? 'Médico')[0]) ?>!</strong>
                        Sou o VOXEL Copilot com <strong>Report Engine</strong>. Posso sugerir achados, gerar impressões diagnósticas, revisar o laudo completo, verificar consistências, indicar CID-10 e padronizar terminologia radiológica.
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
<input type="hidden" id="modalidade"   value="<?= htmlspecialchars($laudo->modalidade ?? '') ?>">

<script>
const laudoId     = parseInt(document.getElementById('laudo-id').value, 10);
const workspaceId = parseInt(document.getElementById('workspace-id').value, 10);
const csrfToken   = document.getElementById('csrf-token').value;
const isReadonly  = <?= $laudo->status !== 'rascunho' ? 'true' : 'false' ?>;
const modalidade  = document.getElementById('modalidade').value;

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
    data.append('indicacao',    document.getElementById('indicacao')?.value    || '');
    data.append('tecnica',      document.getElementById('tecnica')?.value      || '');
    data.append('achados',      document.getElementById('achados')?.value      || '');
    data.append('impressao',    document.getElementById('impressao')?.value    || '');
    data.append('recomendacao', document.getElementById('recomendacao')?.value || '');
    data.append('cid',          document.getElementById('cid')?.value          || '');

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

// ── QUALITY ENGINE — Toggle ────────────────────────────────────
function toggleQualityBar() {
    const list = document.getElementById('quality-list');
    const chevron = document.getElementById('qe-chevron');
    if (!list) return;
    const isOpen = list.style.display !== 'none';
    list.style.display = isOpen ? 'none' : 'block';
    if (chevron) chevron.style.transform = isOpen ? 'rotate(-90deg)' : 'rotate(0deg)';
}

function irParaCampo(campo) {
    const el = document.getElementById(campo);
    if (!el) return;
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el.focus();
    el.classList.add('ws-field-highlight');
    setTimeout(function() { el.classList.remove('ws-field-highlight'); }, 2000);
}

// ── TÉCNICA AUTO ──────────────────────────────────────────────
function gerarTecnicaAuto() {
    if (isReadonly) return;
    const data = new FormData();
    data.append('csrf_token',  csrfToken);
    data.append('workspace_id', workspaceId);
    data.append('acao',        'tecnica_auto');
    data.append('modalidade',  modalidade);

    fetch('/api/copilot/report-engine', { method: 'POST', body: data })
        .then(r => r.json())
        .then(function(res) {
            if (res.ok && res.tecnica) {
                const el = document.getElementById('tecnica');
                if (el) { el.value = res.tecnica; markDirty(); }
            } else {
                addMessage('assistant', '⚠️ ' + (res.error || 'Não foi possível gerar a técnica para esta modalidade.'));
            }
        })
        .catch(function() {
            addMessage('assistant', '⚠️ Erro de conexão.');
        });
}

// ── SUGERIR INDICAÇÃO ─────────────────────────────────────────
function sugerirIndicacao() {
    sendQuick('Com base na modalidade ' + modalidade + ' e no contexto do exame, sugira uma indicação clínica adequada para este laudo.');
}

// ── APLICAR DICIONÁRIO RADIOLÓGICO ────────────────────────────
function aplicarDicionario(campo) {
    if (isReadonly) return;
    const el = document.getElementById(campo);
    if (!el || !el.value.trim()) return;

    const data = new FormData();
    data.append('csrf_token',  csrfToken);
    data.append('workspace_id', workspaceId);
    data.append('acao',        'dicionario');
    data.append('texto',       el.value);

    fetch('/api/copilot/report-engine', { method: 'POST', body: data })
        .then(r => r.json())
        .then(function(res) {
            if (res.ok && res.texto !== undefined) {
                if (res.texto !== el.value) {
                    if (confirm('O dicionário radiológico encontrou termos para padronizar. Aplicar as substituições?')) {
                        el.value = res.texto;
                        markDirty();
                        addMessage('assistant', '✅ Terminologia padronizada com sucesso.');
                    }
                } else {
                    addMessage('assistant', '✅ Terminologia já está padronizada — nenhuma substituição necessária.');
                }
            }
        })
        .catch(function() {
            addMessage('assistant', '⚠️ Erro ao aplicar o dicionário.');
        });
}

// ── REVISAR LAUDO (Report Engine) ─────────────────────────────
function revisarLaudo() {
    if (isReadonly) return;
    const btn = document.getElementById('btn-revisar');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Revisando...'; }

    addMessage('user', '🔍 Revisar laudo completo (ortografia, terminologia, consistência, lateralidade, estrutura)');

    const data = new FormData();
    data.append('csrf_token',   csrfToken);
    data.append('workspace_id', workspaceId);
    data.append('acao',         'revisar');
    data.append('indicacao',    document.getElementById('indicacao')?.value    || '');
    data.append('tecnica',      document.getElementById('tecnica')?.value      || '');
    data.append('achados',      document.getElementById('achados')?.value      || '');
    data.append('impressao',    document.getElementById('impressao')?.value    || '');
    data.append('recomendacao', document.getElementById('recomendacao')?.value || '');
    data.append('modalidade',   modalidade);

    fetch('/api/copilot/report-engine', { method: 'POST', body: data })
        .then(r => r.json())
        .then(function(res) {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-spell-check"></i> Revisar Laudo'; }
            addMessage('assistant', res.ok ? res.content : ('⚠️ ' + (res.error || 'Erro ao revisar.')));
        })
        .catch(function() {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-spell-check"></i> Revisar Laudo'; }
            addMessage('assistant', '⚠️ Erro de conexão com o Copilot.');
        });
}

// ── GERAR IMPRESSÃO DIAGNÓSTICA ───────────────────────────────
function gerarImpressaoIA() {
    const btn = document.getElementById('btn-ia-impressao');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Gerando...'; }

    addMessage('user', '💡 Gerar Impressão Diagnóstica com base nos achados');

    const data = new FormData();
    data.append('csrf_token',   csrfToken);
    data.append('workspace_id', workspaceId);
    data.append('acao',         'impressao');
    data.append('indicacao',    document.getElementById('indicacao')?.value || '');
    data.append('achados',      document.getElementById('achados')?.value   || '');
    data.append('modalidade',   modalidade);

    fetch('/api/copilot/report-engine', { method: 'POST', body: data })
        .then(r => r.json())
        .then(function(res) {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-brain"></i> Gerar com IA'; }
            if (res.ok && res.content) {
                if (!isReadonly) {
                    const el = document.getElementById('impressao');
                    if (el && !el.value.trim()) {
                        el.value = res.content;
                        markDirty();
                        addMessage('assistant', '✅ Impressão Diagnóstica gerada e inserida no campo.');
                    } else {
                        addMessage('assistant', '**Sugestão de Impressão Diagnóstica:**\n\n' + res.content);
                    }
                } else {
                    addMessage('assistant', res.content);
                }
            } else {
                addMessage('assistant', '⚠️ ' + (res.error || 'Erro ao gerar impressão.'));
            }
        })
        .catch(function() {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-brain"></i> Gerar com IA'; }
            addMessage('assistant', '⚠️ Erro de conexão com o Copilot.');
        });
}

// ── BUSCAR TEMPLATE ───────────────────────────────────────────
function abrirBuscaTemplate() {
    if (isReadonly) return;
    const dd = document.getElementById('tpl-dropdown');
    if (!dd) return;
    dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
    if (dd.style.display === 'block') {
        const inp = document.getElementById('tpl-search-input');
        if (inp) { inp.value = ''; filtrarTemplates(''); inp.focus(); }
    }
}

function filtrarTemplates(q) {
    q = q.toLowerCase().trim();
    const items = document.querySelectorAll('#tpl-list .ws-tpl-item');
    items.forEach(function(item) {
        const nome = item.getAttribute('data-nome') || '';
        item.style.display = (!q || nome.includes(q)) ? 'flex' : 'none';
    });
    const groups = document.querySelectorAll('#tpl-list .ws-tpl-group-label');
    groups.forEach(function(label) {
        let next = label.nextElementSibling;
        let hasVisible = false;
        while (next && !next.classList.contains('ws-tpl-group-label')) {
            if (next.style.display !== 'none') hasVisible = true;
            next = next.nextElementSibling;
        }
        label.style.display = hasVisible ? 'block' : 'none';
    });
}

function selecionarTemplate(id, tipo, nome) {
    if (isReadonly) return;
    const dd = document.getElementById('tpl-dropdown');
    if (dd) dd.style.display = 'none';
    const ph = document.getElementById('tpl-placeholder');
    if (ph) ph.textContent = nome;

    const endpoint = tipo === 'mascara'
        ? '/api/mascaras/' + id + '/corpo'
        : '/api/templates/' + id + '/corpo';

    if (!confirm('Aplicar "' + nome + '" irá substituir o conteúdo atual dos Achados. Deseja continuar?')) {
        if (ph) ph.textContent = 'Buscar Template...';
        return;
    }

    fetch(endpoint)
        .then(r => r.json())
        .then(function(res) {
            if (res.ok && res.corpo !== undefined) {
                const el = document.getElementById('achados');
                if (el) { el.value = res.corpo; markDirty(); }
            }
            if (ph) ph.textContent = 'Buscar Template...';
        })
        .catch(function() {
            if (ph) ph.textContent = 'Buscar Template...';
        });
}

document.addEventListener('click', function(e) {
    const wrap = document.getElementById('tpl-wrap');
    if (wrap && !wrap.contains(e.target)) {
        const dd = document.getElementById('tpl-dropdown');
        if (dd) dd.style.display = 'none';
    }
});

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
    data.append('csrf_token',   csrfToken);
    data.append('mensagem',     msg);
    data.append('workspace_id', workspaceId);
    data.append('acao',         'chat');
    data.append('indicacao',    document.getElementById('indicacao')?.value  || '');
    data.append('achados',      document.getElementById('achados')?.value    || '');
    data.append('impressao',    document.getElementById('impressao')?.value  || '');
    data.append('modalidade',   modalidade);

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
    data.append('acao',         'sugestao');
    data.append('modalidade',   modalidade);
    data.append('indicacao',    document.getElementById('indicacao')?.value || '');
    data.append('achados',      document.getElementById('achados')?.value   || '');

    fetch('/api/copilot/sugestao', { method: 'POST', body: data })
        .then(r => r.json())
        .then(function(res) {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-brain"></i> Sugerir com IA'; }
            addMessage('assistant', res.ok ? ('**Sugestão de Achados:**\n\n' + res.content) : ('⚠️ ' + (res.error || 'Erro ao gerar sugestão.')));
        })
        .catch(function() {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-brain"></i> Sugerir com IA'; }
            addMessage('assistant', '⚠️ Erro de conexão com o Copilot.');
        });
}

// Enter no chat
document.getElementById('ai-input')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendChat(); }
});

// Auto-resize textarea do chat
document.getElementById('ai-input')?.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
});

// Scroll ao fundo das mensagens
const msgs = document.getElementById('ai-messages');
if (msgs) msgs.scrollTop = msgs.scrollHeight;

// Exames anteriores — troca de abas
function abrirAbaExame(idx) {
    document.querySelectorAll('.ws-exame-tab').forEach(function(btn) { btn.classList.remove('active'); });
    document.querySelectorAll('.ws-exame-content').forEach(function(div) { div.classList.remove('active'); });
    var tab = document.querySelector('.ws-exame-tab[data-idx="' + idx + '"]');
    var content = document.getElementById('ws-exame-' + idx);
    if (tab) tab.classList.add('active');
    if (content) content.classList.add('active');
}

function focarChat() {
    var input = document.getElementById('ai-input');
    if (input) { input.focus(); input.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
}
</script>
