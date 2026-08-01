<?php
/**
 * Workspace — Worklist PACS
 * Exibe todos os exames assumidos pelo médico no VoxelPACS que estão
 * aguardando laudo no VOXEL Copilot.
 *
 * Auto-refresh a cada 60 segundos via AJAX.
 * Botão "Atualizar" manual no header.
 */

$statusMap = [
    'aguardando' => ['#f59e0b', 'fa-clock',          'Aguardando'],
    'em_laudo'   => ['#3b82f6', 'fa-pen-to-square',  'Em Laudo'],
    'rascunho'   => ['#8b5cf6', 'fa-file-pen',       'Rascunho'],
    'enviado'    => ['#22c55e', 'fa-circle-check',   'Enviado'],
    'erro'       => ['#ef4444', 'fa-circle-xmark',   'Erro'],
];

$prioridadeMap = [
    'urgente' => ['#ef4444', 'URGENTE'],
    'alta'    => ['#f59e0b', 'ALTA'],
    'normal'  => ['#64748b', 'NORMAL'],
];
?>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- HEADER DA PÁGINA                                                        -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
    <div>
        <h1 style="margin:0;display:flex;align-items:center;gap:10px;">
            <i class="fa-solid fa-list-check" style="color:var(--primary);"></i>
            Worklist PACS
        </h1>
        <p style="margin:4px 0 0;color:var(--muted);font-size:.85rem;">
            Exames assumidos no VoxelPACS aguardando laudo no Copilot.
            <span id="wl-last-update" style="margin-left:8px;font-size:.75rem;"></span>
        </p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;">
        <!-- Contador regressivo -->
        <span id="wl-countdown" style="font-size:.75rem;color:var(--muted);background:rgba(255,255,255,.05);
              border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:4px 12px;">
            <i class="fa-solid fa-rotate fa-spin" style="margin-right:4px;"></i>
            Atualizando em <strong id="wl-secs">60</strong>s
        </span>
        <!-- Botão atualizar manual -->
        <button id="btn-refresh-wl" class="btn btn-primary" onclick="wlRefresh(true)">
            <i class="fa-solid fa-arrows-rotate"></i> Atualizar
        </button>
        <!-- Link para workspace geral -->
        <a href="/workspace" class="btn btn-ghost">
            <i class="fa-solid fa-file-medical"></i> Meus Laudos
        </a>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- BANNER: sem vínculo PACS                                                -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<?php if (empty($itens)): ?>
<div id="wl-empty" class="card" style="text-align:center;padding:60px 20px;">
    <div style="font-size:3rem;margin-bottom:16px;opacity:.4;">
        <i class="fa-solid fa-satellite-dish"></i>
    </div>
    <h3 style="color:var(--muted);margin:0 0 8px;">Nenhum exame na fila</h3>
    <p style="color:var(--muted);font-size:.85rem;margin:0 0 20px;">
        Quando você assumir um exame no VoxelPACS, ele aparecerá aqui automaticamente.
    </p>
    <a href="/configuracoes?tab=autorizacao" class="btn btn-ghost" style="margin:0 auto;">
        <i class="fa-solid fa-link"></i> Verificar Vínculo PACS
    </a>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- TABELA DE EXAMES                                                         -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div id="wl-container">
<?php if (!empty($itens)): ?>
<div class="card" style="overflow:hidden;">
    <div class="table-wrap" style="overflow-x:auto;">
        <table class="table" id="wl-table">
            <thead>
                <tr>
                    <th style="min-width:180px;">Paciente</th>
                    <th>Unidade / Instituição</th>
                    <th>Modalidade</th>
                    <th>Data Estudo</th>
                    <th>Prioridade</th>
                    <th>Status Copilot</th>
                    <th>Assumido em</th>
                    <th style="min-width:160px;">Ações</th>
                </tr>
            </thead>
            <tbody id="wl-tbody">
                <?php foreach ($itens as $item): ?>
                <?php
                    [$statusColor, $statusIcon, $statusLabel] = $statusMap[$item->status] ?? ['#64748b', 'fa-circle', ucfirst($item->status)];
                    [$prioColor, $prioLabel] = $prioridadeMap[$item->prioridade ?? 'normal'] ?? ['#64748b', 'NORMAL'];
                    $studyDate = $item->study_date ? date('d/m/Y', strtotime($item->study_date)) : '—';
                    $assumidoEm = $item->assumido_em ? date('d/m H:i', strtotime($item->assumido_em)) : '—';
                ?>
                <tr data-wl-id="<?= (int)$item->id ?>">
                    <td>
                        <div style="font-weight:600;color:#e2e8f0;line-height:1.3;">
                            <?= $item->patient_nome ? htmlspecialchars($item->patient_nome) : '<span style="color:var(--muted);">Não identificado</span>' ?>
                        </div>
                        <?php if ($item->patient_id): ?>
                        <div style="font-size:.7rem;color:var(--muted);">ID: <?= htmlspecialchars($item->patient_id) ?></div>
                        <?php endif; ?>
                        <?php if ($item->study_description): ?>
                        <div style="font-size:.7rem;color:var(--muted);margin-top:2px;">
                            <?= htmlspecialchars(substr($item->study_description, 0, 50)) ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-size:.8rem;color:#e2e8f0;">
                            <?= htmlspecialchars($item->nome_instituicao ?? $item->institution_name ?? '—') ?>
                        </div>
                        <div style="font-size:.7rem;color:var(--muted);">
                            <?= htmlspecialchars($item->codigo_unidade ?? '') ?>
                        </div>
                    </td>
                    <td>
                        <?php if ($item->modalidade): ?>
                        <span style="background:rgba(14,165,233,.08);border:1px solid rgba(14,165,233,.2);
                              color:var(--primary);padding:3px 10px;border-radius:100px;font-size:.72rem;font-weight:700;">
                            <?= htmlspecialchars($item->modalidade) ?>
                        </span>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td style="font-size:.8rem;color:var(--muted);"><?= $studyDate ?></td>
                    <td>
                        <span style="background:<?= $prioColor ?>22;border:1px solid <?= $prioColor ?>44;
                              color:<?= $prioColor ?>;padding:2px 8px;border-radius:100px;font-size:.68rem;font-weight:700;">
                            <?= $prioLabel ?>
                        </span>
                    </td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:5px;
                              color:<?= $statusColor ?>;font-size:.78rem;font-weight:600;">
                            <i class="fa-solid <?= $statusIcon ?>"></i>
                            <?= $statusLabel ?>
                        </span>
                    </td>
                    <td style="font-size:.75rem;color:var(--muted);"><?= $assumidoEm ?></td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                            <?php if ($item->laudo_id): ?>
                            <a href="/workspace/<?= (int)$item->laudo_id ?>"
                               class="btn btn-primary btn-xs"
                               title="Abrir laudo no Copilot">
                                <i class="fa-solid fa-pen-to-square"></i> Laudar
                            </a>
                            <?php endif; ?>
                            <?php if ($item->study_instance_uid): ?>
                            <button class="btn btn-ghost btn-xs"
                                    onclick="abrirViewer('<?= htmlspecialchars($item->study_instance_uid) ?>')"
                                    title="Abrir imagens no viewer PACS">
                                <i class="fa-solid fa-eye"></i> Imagens
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- JAVASCRIPT: Auto-refresh 60s + botão manual                             -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<script>
(function () {
    'use strict';

    var INTERVAL_SECS = 60;
    var countdown     = INTERVAL_SECS;
    var timer         = null;

    // ── Formata timestamp ─────────────────────────────────────────────────
    function formatTime(d) {
        return d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    // ── Atualiza o contador regressivo no header ──────────────────────────
    function tickCountdown() {
        countdown--;
        var el = document.getElementById('wl-secs');
        if (el) el.textContent = countdown;
        if (countdown <= 0) {
            countdown = INTERVAL_SECS;
            wlRefresh(false);
        }
    }

    // ── Busca os itens via AJAX e re-renderiza a tabela ───────────────────
    window.wlRefresh = function (manual) {
        if (manual) {
            countdown = INTERVAL_SECS;
            var el = document.getElementById('wl-secs');
            if (el) el.textContent = countdown;
        }

        var btn = document.getElementById('btn-refresh-wl');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-arrows-rotate fa-spin"></i> Atualizando...';
        }

        fetch('/api/pacs/worklist', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.ok) return;
            renderWorklist(data.itens || []);
            var lu = document.getElementById('wl-last-update');
            if (lu) lu.textContent = '(última atualização: ' + formatTime(new Date()) + ')';
        })
        .catch(function (e) {
            console.warn('[Worklist PACS] Falha ao atualizar:', e);
        })
        .finally(function () {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> Atualizar';
            }
        });
    };

    // ── Re-renderiza a tabela com os dados recebidos ──────────────────────
    function renderWorklist(itens) {
        var container = document.getElementById('wl-container');
        var empty     = document.getElementById('wl-empty');

        if (!itens.length) {
            if (empty)     empty.style.display     = '';
            if (container) container.innerHTML      = '';
            return;
        }

        if (empty) empty.style.display = 'none';

        var statusMap = {
            'aguardando': ['#f59e0b', 'fa-clock',         'Aguardando'],
            'em_laudo':   ['#3b82f6', 'fa-pen-to-square', 'Em Laudo'],
            'rascunho':   ['#8b5cf6', 'fa-file-pen',      'Rascunho'],
            'enviado':    ['#22c55e', 'fa-circle-check',  'Enviado'],
            'erro':       ['#ef4444', 'fa-circle-xmark',  'Erro'],
        };
        var prioMap = {
            'urgente': ['#ef4444', 'URGENTE'],
            'alta':    ['#f59e0b', 'ALTA'],
            'normal':  ['#64748b', 'NORMAL'],
        };

        var rows = itens.map(function (item) {
            var sm     = statusMap[item.status]   || ['#64748b', 'fa-circle', item.status];
            var pm     = prioMap[item.prioridade] || ['#64748b', 'NORMAL'];
            var sd     = item.study_date  ? new Date(item.study_date).toLocaleDateString('pt-BR')  : '—';
            var ae     = item.assumido_em ? new Date(item.assumido_em).toLocaleString('pt-BR', {day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'}) : '—';
            var pNome  = item.patient_nome ? escHtml(item.patient_nome) : '<span style="color:var(--muted);">Não identificado</span>';
            var laudarBtn = item.laudo_id
                ? '<a href="/workspace/' + item.laudo_id + '" class="btn btn-primary btn-xs"><i class="fa-solid fa-pen-to-square"></i> Laudar</a>'
                : '';
            var viewerBtn = item.study_instance_uid
                ? '<button class="btn btn-ghost btn-xs" onclick="abrirViewer(\'' + escHtml(item.study_instance_uid) + '\')"><i class="fa-solid fa-eye"></i> Imagens</button>'
                : '';

            return '<tr data-wl-id="' + item.id + '">' +
                '<td>' +
                    '<div style="font-weight:600;color:#e2e8f0;">' + pNome + '</div>' +
                    (item.patient_id ? '<div style="font-size:.7rem;color:var(--muted);">ID: ' + escHtml(item.patient_id) + '</div>' : '') +
                    (item.study_description ? '<div style="font-size:.7rem;color:var(--muted);">' + escHtml(item.study_description.substring(0,50)) + '</div>' : '') +
                '</td>' +
                '<td><div style="font-size:.8rem;color:#e2e8f0;">' + escHtml(item.nome_instituicao || '—') + '</div>' +
                    '<div style="font-size:.7rem;color:var(--muted);">' + escHtml(item.codigo_unidade || '') + '</div></td>' +
                '<td>' + (item.modalidade ? '<span style="background:rgba(14,165,233,.08);border:1px solid rgba(14,165,233,.2);color:var(--primary);padding:3px 10px;border-radius:100px;font-size:.72rem;font-weight:700;">' + escHtml(item.modalidade) + '</span>' : '—') + '</td>' +
                '<td style="font-size:.8rem;color:var(--muted);">' + sd + '</td>' +
                '<td><span style="background:' + pm[0] + '22;border:1px solid ' + pm[0] + '44;color:' + pm[0] + ';padding:2px 8px;border-radius:100px;font-size:.68rem;font-weight:700;">' + pm[1] + '</span></td>' +
                '<td><span style="display:inline-flex;align-items:center;gap:5px;color:' + sm[0] + ';font-size:.78rem;font-weight:600;"><i class="fa-solid ' + sm[1] + '"></i>' + sm[2] + '</span></td>' +
                '<td style="font-size:.75rem;color:var(--muted);">' + ae + '</td>' +
                '<td><div style="display:flex;gap:6px;flex-wrap:wrap;">' + laudarBtn + viewerBtn + '</div></td>' +
                '</tr>';
        }).join('');

        container.innerHTML =
            '<div class="card" style="overflow:hidden;">' +
            '<div class="table-wrap" style="overflow-x:auto;">' +
            '<table class="table" id="wl-table"><thead><tr>' +
            '<th style="min-width:180px;">Paciente</th><th>Unidade / Instituição</th>' +
            '<th>Modalidade</th><th>Data Estudo</th><th>Prioridade</th>' +
            '<th>Status Copilot</th><th>Assumido em</th><th style="min-width:160px;">Ações</th>' +
            '</tr></thead><tbody>' + rows + '</tbody></table></div></div>';
    }

    // ── Abre o viewer PACS em nova aba ────────────────────────────────────
    window.abrirViewer = function (studyUid) {
        // Tenta buscar a URL do viewer via API
        fetch('/api/pacs/viewer-url?study_uid=' + encodeURIComponent(studyUid))
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.url) {
                    window.open(d.url, '_blank');
                } else {
                    alert('URL do viewer não disponível. Verifique as configurações de integração PACS.');
                }
            })
            .catch(function () {
                alert('Não foi possível obter a URL do viewer PACS.');
            });
    };

    // ── Escapa HTML para evitar XSS ───────────────────────────────────────
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ── Inicializa o timer ────────────────────────────────────────────────
    function init() {
        // Atualiza timestamp inicial
        var lu = document.getElementById('wl-last-update');
        if (lu) lu.textContent = '(última atualização: ' + formatTime(new Date()) + ')';

        // Inicia countdown
        timer = setInterval(tickCountdown, 1000);

        // Pausa quando a aba fica invisível (economiza requisições)
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                clearInterval(timer);
            } else {
                countdown = INTERVAL_SECS;
                timer = setInterval(tickCountdown, 1000);
                wlRefresh(false); // Atualiza imediatamente ao voltar
            }
        });
    }

    // Aguarda DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
