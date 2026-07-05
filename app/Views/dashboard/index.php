<?php
use App\Core\Auth;
$user     = Auth::user();
$nome     = explode(' ', $user?->name ?? 'Médico')[0];
$initials = strtoupper(substr($user?->name ?? 'M', 0, 2));
$hora     = (int) date('H');
$saudacao = $hora < 12 ? 'Bom dia' : ($hora < 18 ? 'Boa tarde' : 'Boa noite');
$extraCss = ['/assets/css/dashboard.css'];
?>

<!-- ══════════════════════════════════════════════════════════
     HERO — Cabeçalho inteligente
═══════════════════════════════════════════════════════════ -->
<div class="ctw-hero">
    <div class="ctw-hero-left">
        <div class="ctw-hero-greeting">
            <?= $saudacao ?>, Dr. <?= htmlspecialchars($nome) ?> 👋
        </div>
        <div class="ctw-hero-metrics">
            <span class="ctw-hero-pill waiting">
                <i class="fa-solid fa-inbox" aria-hidden="true"></i>
                <?= number_format($laudosHoje ?? 18) ?> exames aguardando
            </span>
            <span class="ctw-hero-pill critical">
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                3 críticos
            </span>
            <span class="ctw-hero-pill compare">
                <i class="fa-solid fa-code-compare" aria-hidden="true"></i>
                2 comparações sugeridas
            </span>
            <span class="ctw-hero-pill perf">
                <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                98% produtividade
            </span>
        </div>
        <div class="ctw-hero-copilot">
            <i class="fa-solid fa-brain" aria-hidden="true"></i>
            Copilot possui <strong>12 sugestões</strong> para hoje.
        </div>
        <!-- Workspace interrompido -->
        <div class="ctw-interrupted">
            <i class="fa-solid fa-circle-pause" aria-hidden="true"></i>
            <span>Workspace interrompido — você saiu do exame <strong>TC Tórax / João Silva</strong> ontem às 22:18.
                <a href="/workspace/ultimo">Deseja continuar?</a>
            </span>
        </div>
    </div>

    <!-- Spotlight universal -->
    <div class="ctw-spotlight-wrap">
        <button class="ctw-spotlight-btn" id="spotlight-open" aria-label="Pesquisa universal (Ctrl+K)">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <span>Pesquisar qualquer coisa...</span>
            <span class="ctw-spotlight-shortcut">Ctrl+K</span>
        </button>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     PAINEL DE SITUAÇÃO CLÍNICA
═══════════════════════════════════════════════════════════ -->
<div class="ctw-situation">
    <div class="situation-title">
        <i class="fa-solid fa-brain" aria-hidden="true"></i>
        COPILOT ANALISOU SUA FILA
    </div>
    <div class="situation-items">
        <div class="situation-item"><i class="fa-solid fa-check-circle" aria-hidden="true"></i> 6 pacientes oncológicos identificados</div>
        <div class="situation-item"><i class="fa-solid fa-check-circle" aria-hidden="true"></i> 3 exames precisam de comparação</div>
        <div class="situation-item"><i class="fa-solid fa-check-circle" aria-hidden="true"></i> 1 laudo possui inconsistência detectada</div>
        <div class="situation-item"><i class="fa-solid fa-check-circle" aria-hidden="true"></i> 2 pacientes com exames anteriores disponíveis</div>
    </div>
    <div class="situation-economy">
        <i class="fa-solid fa-clock" aria-hidden="true"></i>
        Economia prevista hoje:
        <strong>32 min</strong>
        <span style="color:rgba(255,255,255,.5);font-size:.75rem;">— como ter um residente organizando seu trabalho</span>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     LINHA 1 — Minha Fila + Agenda
═══════════════════════════════════════════════════════════ -->
<div class="ctw-section-title">
    <h2><i class="fa-solid fa-list-check" aria-hidden="true"></i> Minha Fila</h2>
    <a href="/workspace">Ver todos <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
</div>
<div class="ctw-queue-grid">
    <div class="queue-card urgent" onclick="window.location='/workspace?fila=urgentes'" role="button" tabindex="0">
        <div class="queue-card-top">
            <div class="queue-card-icon red"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></div>
            <span class="queue-sla crit">SLA 1h</span>
        </div>
        <div class="queue-card-count">3</div>
        <div class="queue-card-label">Urgentes</div>
        <div class="queue-card-meta">Tempo médio: 8 min</div>
    </div>
    <div class="queue-card" onclick="window.location='/workspace?fila=normais'" role="button" tabindex="0">
        <div class="queue-card-top">
            <div class="queue-card-icon blue"><i class="fa-solid fa-file-medical" aria-hidden="true"></i></div>
            <span class="queue-sla ok">SLA 4h</span>
        </div>
        <div class="queue-card-count">11</div>
        <div class="queue-card-label">Normais</div>
        <div class="queue-card-meta">Tempo médio: 12 min</div>
    </div>
    <div class="queue-card onco" onclick="window.location='/workspace?fila=oncologicos'" role="button" tabindex="0">
        <div class="queue-card-top">
            <div class="queue-card-icon orange"><i class="fa-solid fa-ribbon" aria-hidden="true"></i></div>
            <span class="queue-sla warn">SLA 2h</span>
        </div>
        <div class="queue-card-count">6</div>
        <div class="queue-card-label">Oncológicos</div>
        <div class="queue-card-meta">Comparação disponível</div>
    </div>
    <div class="queue-card" onclick="window.location='/workspace?fila=comparativos'" role="button" tabindex="0">
        <div class="queue-card-top">
            <div class="queue-card-icon purple"><i class="fa-solid fa-code-compare" aria-hidden="true"></i></div>
            <span class="queue-sla ok">SLA 6h</span>
        </div>
        <div class="queue-card-count">2</div>
        <div class="queue-card-label">Comparativos</div>
        <div class="queue-card-meta">IA pronta</div>
    </div>
    <div class="queue-card" onclick="window.location='/workspace?fila=revisao'" role="button" tabindex="0">
        <div class="queue-card-top">
            <div class="queue-card-icon yellow"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></div>
            <span class="queue-sla warn">SLA 3h</span>
        </div>
        <div class="queue-card-count">4</div>
        <div class="queue-card-label">Revisão</div>
        <div class="queue-card-meta">Tempo médio: 6 min</div>
    </div>
    <div class="queue-card" onclick="window.location='/workspace?fila=assinatura'" role="button" tabindex="0">
        <div class="queue-card-top">
            <div class="queue-card-icon green"><i class="fa-solid fa-signature" aria-hidden="true"></i></div>
            <span class="queue-sla ok">SLA 8h</span>
        </div>
        <div class="queue-card-count">7</div>
        <div class="queue-card-label">Assinatura</div>
        <div class="queue-card-meta">Aguardando revisão</div>
    </div>
</div>

<!-- Agenda do dia -->
<div class="ctw-agenda">
    <div class="ctw-agenda-title">
        <i class="fa-solid fa-calendar-day" aria-hidden="true"></i>
        Agenda — <?= date('d/m/Y') ?>
    </div>
    <div class="agenda-items">
        <div class="agenda-item">
            <span class="agenda-item-time">08:00</span>
            <span class="agenda-item-text">Início do plantão — Radiologia Geral</span>
            <span class="agenda-item-badge">Plantão</span>
        </div>
        <div class="agenda-item">
            <span class="agenda-item-time">10:30</span>
            <span class="agenda-item-text">Reunião de casos oncológicos — Equipe Multidisciplinar</span>
            <span class="agenda-item-badge">Reunião</span>
        </div>
        <div class="agenda-item">
            <span class="agenda-item-time">14:00</span>
            <span class="agenda-item-text">Laudo de exames de alta complexidade (RM Encéfalo)</span>
            <span class="agenda-item-badge">Laudo</span>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     LINHA 2 — Produtividade + Modalidades + IA Insights
═══════════════════════════════════════════════════════════ -->
<div class="ctw-section-title">
    <h2><i class="fa-solid fa-chart-bar" aria-hidden="true"></i> Análise de Desempenho</h2>
</div>
<div class="ctw-row2">
    <!-- Timeline produtividade -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Produtividade</div>
        </div>
        <div class="card-body">
            <div class="prod-timeline">
                <div class="prod-metric">
                    <span class="prod-metric-label">Exames/hora</span>
                    <div class="prod-bar-wrap"><div class="prod-bar" style="width:78%"></div></div>
                    <span class="prod-metric-val">4.2</span>
                </div>
                <div class="prod-metric">
                    <span class="prod-metric-label">Tempo médio</span>
                    <div class="prod-bar-wrap"><div class="prod-bar green" style="width:65%"></div></div>
                    <span class="prod-metric-val">14 min</span>
                </div>
                <div class="prod-metric">
                    <span class="prod-metric-label">Tempo revisão</span>
                    <div class="prod-bar-wrap"><div class="prod-bar yellow" style="width:45%"></div></div>
                    <span class="prod-metric-val">6 min</span>
                </div>
                <div class="prod-metric">
                    <span class="prod-metric-label">Tempo assinatura</span>
                    <div class="prod-bar-wrap"><div class="prod-bar" style="width:30%"></div></div>
                    <span class="prod-metric-val">2 min</span>
                </div>
            </div>
            <!-- Painel aprendizado -->
            <div style="margin-top:14px;padding:10px 12px;background:var(--blue-50);border:1px solid var(--blue-100);border-radius:8px;font-size:.75rem;color:var(--gray-700);">
                <div style="font-weight:700;color:var(--royal);margin-bottom:4px;display:flex;align-items:center;gap:5px;">
                    <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i> Copilot aprendeu hoje
                </div>
                Você corrigiu: <strong>Parênquima Hepático</strong><br>
                Precisão aumentou: <span style="color:var(--success);font-weight:700;">+3%</span>
            </div>
        </div>
    </div>

    <!-- Distribuição modalidades -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i> Modalidades</div>
        </div>
        <div class="card-body">
            <div class="modalities-wrap">
                <div class="donut-chart">
                    <svg width="100" height="100" viewBox="0 0 100 100">
                        <!-- TC 38% -->
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#e2e8f0" stroke-width="12"/>
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#1a56db" stroke-width="12"
                            stroke-dasharray="<?= round(38 * 2 * M_PI * 38 / 100) ?> 239" stroke-dashoffset="0"/>
                        <!-- RM 25% -->
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#7c3aed" stroke-width="12"
                            stroke-dasharray="<?= round(25 * 2 * M_PI * 38 / 100) ?> 239"
                            stroke-dashoffset="-<?= round(38 * 2 * M_PI * 38 / 100) ?>"/>
                        <!-- RX 20% -->
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#64748b" stroke-width="12"
                            stroke-dasharray="<?= round(20 * 2 * M_PI * 38 / 100) ?> 239"
                            stroke-dashoffset="-<?= round(63 * 2 * M_PI * 38 / 100) ?>"/>
                        <!-- US 12% -->
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#059669" stroke-width="12"
                            stroke-dasharray="<?= round(12 * 2 * M_PI * 38 / 100) ?> 239"
                            stroke-dashoffset="-<?= round(83 * 2 * M_PI * 38 / 100) ?>"/>
                        <!-- PET+MG 5% -->
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#ea580c" stroke-width="12"
                            stroke-dasharray="<?= round(5 * 2 * M_PI * 38 / 100) ?> 239"
                            stroke-dashoffset="-<?= round(95 * 2 * M_PI * 38 / 100) ?>"/>
                    </svg>
                    <div class="donut-center">
                        <span class="donut-center-val"><?= number_format($totalLaudos ?? 33) ?></span>
                        <span class="donut-center-label">exames</span>
                    </div>
                </div>
                <div class="modalities-legend">
                    <div class="mod-item"><div class="mod-dot" style="background:#1a56db"></div> TC 38%</div>
                    <div class="mod-item"><div class="mod-dot" style="background:#7c3aed"></div> RM 25%</div>
                    <div class="mod-item"><div class="mod-dot" style="background:#64748b"></div> RX 20%</div>
                    <div class="mod-item"><div class="mod-dot" style="background:#059669"></div> US 12%</div>
                    <div class="mod-item"><div class="mod-dot" style="background:#ea580c"></div> PET 3%</div>
                    <div class="mod-item"><div class="mod-dot" style="background:#db2777"></div> MG 2%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- IA Insights -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-brain" aria-hidden="true"></i> IA Insights</div>
        </div>
        <div class="card-body">
            <div class="ia-insights">
                <div class="ia-insight-item" onclick="window.location='/workspace?filtro=comparacao'">
                    <div class="ia-insight-icon blue"><i class="fa-solid fa-code-compare" aria-hidden="true"></i></div>
                    <span class="ia-insight-text">Exames com comparação sugerida</span>
                    <span class="ia-insight-count">4</span>
                </div>
                <div class="ia-insight-item" onclick="window.location='/workspace?filtro=nodular'">
                    <div class="ia-insight-icon orange"><i class="fa-solid fa-circle-dot" aria-hidden="true"></i></div>
                    <span class="ia-insight-text">Pacientes com crescimento nodular</span>
                    <span class="ia-insight-count">2</span>
                </div>
                <div class="ia-insight-item" onclick="window.location='/workspace?filtro=protocolo'">
                    <div class="ia-insight-icon red"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></div>
                    <span class="ia-insight-text">Protocolo inconsistente detectado</span>
                    <span class="ia-insight-count">1</span>
                </div>
                <div class="ia-insight-item" onclick="window.location='/workspace?filtro=similares'">
                    <div class="ia-insight-icon purple"><i class="fa-solid fa-copy" aria-hidden="true"></i></div>
                    <span class="ia-insight-text">Laudos com padrão semelhante</span>
                    <span class="ia-insight-count">3</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     LINHA 3 — Exames Inteligentes
═══════════════════════════════════════════════════════════ -->
<div class="ctw-section-title">
    <h2><i class="fa-solid fa-star" aria-hidden="true"></i> Exames Inteligentes</h2>
    <a href="/workspace">Ver fila completa <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
</div>
<div class="ctw-exames">
    <div class="exam-card">
        <div class="exam-card-top">
            <div class="exam-priority-dot orange"></div>
            <span class="exam-type-badge onco">🟠 Oncológico</span>
        </div>
        <div class="exam-card-patient">Maria Aparecida Santos</div>
        <div class="exam-card-modality">TC Tórax com contraste · 128 cortes</div>
        <div class="exam-card-tags">
            <span class="exam-tag ai"><i class="fa-solid fa-brain" style="font-size:.6rem"></i> IA pronta</span>
            <span class="exam-tag">Comparar com PET</span>
            <span class="exam-tag">Nódulo pulmonar</span>
        </div>
        <div class="exam-card-footer">
            <span class="exam-time"><i class="fa-solid fa-clock" aria-hidden="true"></i> Aguardando 45 min</span>
            <a href="/workspace/novo?study=001" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-play" aria-hidden="true"></i> Abrir Workspace
            </a>
        </div>
    </div>
    <div class="exam-card">
        <div class="exam-card-top">
            <div class="exam-priority-dot red"></div>
            <span class="exam-type-badge neuro">🔴 AVC</span>
        </div>
        <div class="exam-card-patient">Carlos Eduardo Lima</div>
        <div class="exam-card-modality">TC Crânio sem contraste · Urgência</div>
        <div class="exam-card-tags">
            <span class="exam-tag ai"><i class="fa-solid fa-brain" style="font-size:.6rem"></i> Alta prioridade</span>
            <span class="exam-tag">Porta-laudo</span>
        </div>
        <div class="exam-card-footer">
            <span class="exam-time crit"><i class="fa-solid fa-stopwatch" aria-hidden="true"></i> 12 min porta-laudo</span>
            <a href="/workspace/novo?study=002" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-play" aria-hidden="true"></i> Abrir Workspace
            </a>
        </div>
    </div>
    <div class="exam-card">
        <div class="exam-card-top">
            <div class="exam-priority-dot blue"></div>
            <span class="exam-type-badge cardio">Cardio</span>
        </div>
        <div class="exam-card-patient">Roberto Ferreira Costa</div>
        <div class="exam-card-modality">RM Cardíaca · Função ventricular</div>
        <div class="exam-card-tags">
            <span class="exam-tag ai"><i class="fa-solid fa-brain" style="font-size:.6rem"></i> Template sugerido</span>
            <span class="exam-tag">Seguimento</span>
        </div>
        <div class="exam-card-footer">
            <span class="exam-time"><i class="fa-solid fa-clock" aria-hidden="true"></i> Aguardando 1h 20min</span>
            <a href="/workspace/novo?study=003" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-play" aria-hidden="true"></i> Abrir Workspace
            </a>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     LINHA 4 — Copilot Painel
═══════════════════════════════════════════════════════════ -->
<div class="ctw-copilot-panel">
    <div>
        <div class="copilot-panel-greeting">
            <i class="fa-solid fa-brain" aria-hidden="true"></i>
            Bom dia, Dr. <?= htmlspecialchars($nome) ?>. Analisei sua fila.
        </div>
        <div class="copilot-panel-items">
            <div class="copilot-panel-item">
                <i class="fa-solid fa-code-compare" aria-hidden="true"></i>
                <strong>5</strong> comparações automáticas disponíveis
            </div>
            <div class="copilot-panel-item">
                <i class="fa-solid fa-copy" aria-hidden="true"></i>
                <strong>2</strong> exames com padrão semelhante
            </div>
            <div class="copilot-panel-item">
                <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                <strong>4</strong> templates sugeridos para hoje
            </div>
        </div>
        <div class="copilot-panel-learn">
            <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
            Última correção: <strong>Parênquima Hepático</strong> →
            Precisão: <span class="pct">+3%</span>
        </div>
    </div>
    <div class="copilot-panel-actions">
        <a href="/workspace/primeiro" class="copilot-action-btn primary">
            <i class="fa-solid fa-play" aria-hidden="true"></i> Abrir primeiro exame
        </a>
        <a href="/workspace?filtro=comparacao" class="copilot-action-btn ghost">
            <i class="fa-solid fa-code-compare" aria-hidden="true"></i> Comparar estudos
        </a>
        <a href="/workspace?filtro=criticos" class="copilot-action-btn ghost">
            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> Mostrar críticos
        </a>
        <a href="/workspace?filtro=revisao" class="copilot-action-btn ghost">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Revisar laudos
        </a>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     LINHA 5 — Timeline Clínica do Paciente
═══════════════════════════════════════════════════════════ -->
<div class="ctw-section-title">
    <h2><i class="fa-solid fa-timeline" aria-hidden="true"></i> Timeline Clínica — Paciente Recente</h2>
    <a href="/pacientes">Ver todos os pacientes <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
</div>
<div class="card ctw-timeline">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-user" aria-hidden="true"></i>
            João Carlos Pereira &nbsp;·&nbsp;
            <span style="font-size:.75rem;font-weight:400;color:var(--muted);">M, 58 anos · CPF 123.456.789-00</span>
        </div>
        <a href="/workspace/novo?patient=joao" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-play" aria-hidden="true"></i> Abrir Workspace
        </a>
    </div>
    <div class="card-body" style="padding:8px 16px;">
        <div class="clinical-timeline">
            <div class="timeline-event" title="2018 — RX Tórax" onclick="window.location='/workspace/study/2018rx'">
                <div class="timeline-event-dot rx">RX</div>
                <div class="timeline-event-year">2018</div>
                <div class="timeline-event-mod">Tórax</div>
            </div>
            <div class="timeline-event" title="2019 — TC Abdome" onclick="window.location='/workspace/study/2019tc'">
                <div class="timeline-event-dot tc">TC</div>
                <div class="timeline-event-year">2019</div>
                <div class="timeline-event-mod">Abdome</div>
            </div>
            <div class="timeline-event" title="2020 — RM Encéfalo" onclick="window.location='/workspace/study/2020rm'">
                <div class="timeline-event-dot rm">RM</div>
                <div class="timeline-event-year">2020</div>
                <div class="timeline-event-mod">Encéfalo</div>
            </div>
            <div class="timeline-event" title="2022 — PET-CT" onclick="window.location='/workspace/study/2022pet'">
                <div class="timeline-event-dot pet">PET</div>
                <div class="timeline-event-year">2022</div>
                <div class="timeline-event-mod">Corpo inteiro</div>
            </div>
            <div class="timeline-event" title="2025 — TC Tórax (atual)" onclick="window.location='/workspace/study/2025tc'">
                <div class="timeline-event-dot tc" style="background:var(--royal);color:#fff;border-color:var(--royal);">TC</div>
                <div class="timeline-event-year" style="color:var(--royal);font-weight:800;">2025</div>
                <div class="timeline-event-mod" style="color:var(--royal);">Atual</div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     LINHA 6 — Casos Similares
═══════════════════════════════════════════════════════════ -->
<div class="ctw-section-title">
    <h2><i class="fa-solid fa-magnifying-glass-chart" aria-hidden="true"></i> Casos Similares</h2>
    <a href="/comparativos">Ver todos <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
</div>
<div class="ctw-similar">
    <div class="similar-card">
        <div class="similar-card-top">
            <span class="similar-card-modality">TC Pulmão — Nódulo Solitário</span>
            <span class="similar-pct">87%</span>
        </div>
        <div class="similar-progress">
            <div class="similar-progress-bar" style="width:87%"></div>
        </div>
        <div class="similar-institution"><i class="fa-solid fa-hospital" aria-hidden="true"></i> Caso Einstein · 2024</div>
        <a href="/comparativos/001" class="btn btn-secondary btn-sm" style="width:100%;justify-content:center;">
            <i class="fa-solid fa-code-compare" aria-hidden="true"></i> Abrir comparação
        </a>
    </div>
    <div class="similar-card">
        <div class="similar-card-top">
            <span class="similar-card-modality">RM Encéfalo — Glioma</span>
            <span class="similar-pct">79%</span>
        </div>
        <div class="similar-progress">
            <div class="similar-progress-bar" style="width:79%"></div>
        </div>
        <div class="similar-institution"><i class="fa-solid fa-hospital" aria-hidden="true"></i> Caso Sírio-Libanês · 2023</div>
        <a href="/comparativos/002" class="btn btn-secondary btn-sm" style="width:100%;justify-content:center;">
            <i class="fa-solid fa-code-compare" aria-hidden="true"></i> Abrir comparação
        </a>
    </div>
    <div class="similar-card">
        <div class="similar-card-top">
            <span class="similar-card-modality">PET-CT — Estadiamento</span>
            <span class="similar-pct">73%</span>
        </div>
        <div class="similar-progress">
            <div class="similar-progress-bar" style="width:73%"></div>
        </div>
        <div class="similar-institution"><i class="fa-solid fa-hospital" aria-hidden="true"></i> Caso INCA · 2024</div>
        <a href="/comparativos/003" class="btn btn-secondary btn-sm" style="width:100%;justify-content:center;">
            <i class="fa-solid fa-code-compare" aria-hidden="true"></i> Abrir comparação
        </a>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     LINHA 7 — Marketplace
═══════════════════════════════════════════════════════════ -->
<div class="ctw-section-title">
    <h2><i class="fa-solid fa-store" aria-hidden="true"></i> Marketplace — Plugins Instalados</h2>
    <a href="/marketplace">Ver todos <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
</div>
<div class="ctw-marketplace">
    <div class="marketplace-plugins">
        <a href="/marketplace/lung-ai" class="plugin-card">
            <div class="plugin-icon blue"><i class="fa-solid fa-lungs" aria-hidden="true"></i></div>
            <div>
                <div class="plugin-name">Lung AI</div>
                <div class="plugin-status"><i class="fa-solid fa-circle" style="font-size:.5rem"></i> Ativo</div>
            </div>
        </a>
        <a href="/marketplace/cardio-ai" class="plugin-card">
            <div class="plugin-icon pink"><i class="fa-solid fa-heart-pulse" aria-hidden="true"></i></div>
            <div>
                <div class="plugin-name">Cardio AI</div>
                <div class="plugin-status"><i class="fa-solid fa-circle" style="font-size:.5rem"></i> Ativo</div>
            </div>
        </a>
        <a href="/marketplace/speech" class="plugin-card">
            <div class="plugin-icon purple"><i class="fa-solid fa-microphone" aria-hidden="true"></i></div>
            <div>
                <div class="plugin-name">Speech</div>
                <div class="plugin-status"><i class="fa-solid fa-circle" style="font-size:.5rem"></i> Ativo</div>
            </div>
        </a>
        <a href="/marketplace/research" class="plugin-card">
            <div class="plugin-icon green"><i class="fa-solid fa-flask" aria-hidden="true"></i></div>
            <div>
                <div class="plugin-name">Research</div>
                <div class="plugin-status"><i class="fa-solid fa-circle" style="font-size:.5rem"></i> Ativo</div>
            </div>
        </a>
        <a href="/marketplace/workflow" class="plugin-card">
            <div class="plugin-icon orange"><i class="fa-solid fa-diagram-project" aria-hidden="true"></i></div>
            <div>
                <div class="plugin-name">Workflow</div>
                <div class="plugin-status"><i class="fa-solid fa-circle" style="font-size:.5rem"></i> Ativo</div>
            </div>
        </a>
        <a href="/marketplace" class="plugin-add">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            Adicionar módulo
        </a>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL SPOTLIGHT
═══════════════════════════════════════════════════════════ -->
<div class="spotlight-modal" id="spotlight-modal" role="dialog" aria-modal="true" aria-label="Pesquisa universal">
    <div class="spotlight-box">
        <div class="spotlight-input-wrap">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input type="text" class="spotlight-input" id="spotlight-input"
                placeholder="Pesquisar paciente, exame, CID, SNOMED, laudo..."
                autocomplete="off" aria-label="Campo de pesquisa universal">
            <span class="spotlight-kbd" id="spotlight-close" role="button" tabindex="0" aria-label="Fechar pesquisa">ESC</span>
        </div>
        <div class="spotlight-categories">
            <button class="spotlight-cat active" data-cat="todos">Todos</button>
            <button class="spotlight-cat" data-cat="paciente">Paciente</button>
            <button class="spotlight-cat" data-cat="accession">Accession</button>
            <button class="spotlight-cat" data-cat="study">Study UID</button>
            <button class="spotlight-cat" data-cat="cid">CID</button>
            <button class="spotlight-cat" data-cat="snomed">SNOMED</button>
            <button class="spotlight-cat" data-cat="laudo">Laudo</button>
            <button class="spotlight-cat" data-cat="exame">Exame</button>
            <button class="spotlight-cat" data-cat="medico">Médico</button>
            <button class="spotlight-cat" data-cat="instituicao">Instituição</button>
        </div>
        <div class="spotlight-results" id="spotlight-results">
            <div class="spotlight-result-item focused">
                <div class="spotlight-result-icon blue"><i class="fa-solid fa-user" aria-hidden="true"></i></div>
                <div class="spotlight-result-text">
                    <strong>João Carlos Pereira</strong>
                    <span>Paciente · TC Tórax · 2025</span>
                </div>
            </div>
            <div class="spotlight-result-item">
                <div class="spotlight-result-icon orange"><i class="fa-solid fa-file-medical" aria-hidden="true"></i></div>
                <div class="spotlight-result-text">
                    <strong>Laudo #2025-0042</strong>
                    <span>TC Abdome · Maria Santos · Assinado</span>
                </div>
            </div>
            <div class="spotlight-result-item">
                <div class="spotlight-result-icon green"><i class="fa-solid fa-code" aria-hidden="true"></i></div>
                <div class="spotlight-result-text">
                    <strong>C34.1 — Neoplasia maligna do lobo superior</strong>
                    <span>CID-10 · Oncologia</span>
                </div>
            </div>
            <div class="spotlight-result-item">
                <div class="spotlight-result-icon purple"><i class="fa-solid fa-hospital" aria-hidden="true"></i></div>
                <div class="spotlight-result-text">
                    <strong>Hospital das Clínicas — SP</strong>
                    <span>Instituição · 142 estudos</span>
                </div>
            </div>
        </div>
        <div class="spotlight-footer">
            <span><kbd>↑</kbd><kbd>↓</kbd> navegar</span>
            <span><kbd>Enter</kbd> abrir</span>
            <span><kbd>ESC</kbd> fechar</span>
        </div>
    </div>
</div>

<script>
// ── SPOTLIGHT ──
(function() {
    var modal  = document.getElementById('spotlight-modal');
    var input  = document.getElementById('spotlight-input');
    var openBtn = document.getElementById('spotlight-open');
    var closeBtn = document.getElementById('spotlight-close');

    function open() {
        modal.classList.add('open');
        setTimeout(function() { if (input) input.focus(); }, 50);
        document.body.style.overflow = 'hidden';
    }
    function close() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }

    if (openBtn) openBtn.addEventListener('click', open);
    if (closeBtn) closeBtn.addEventListener('click', close);
    if (modal) modal.addEventListener('click', function(e) {
        if (e.target === modal) close();
    });

    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); open(); }
        if (e.key === 'Escape' && modal && modal.classList.contains('open')) close();
    });

    // Categorias
    var cats = document.querySelectorAll('.spotlight-cat');
    cats.forEach(function(btn) {
        btn.addEventListener('click', function() {
            cats.forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
        });
    });
})();
</script>
