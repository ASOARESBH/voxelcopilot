<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'VOXEL Copilot — Acesso à Plataforma') ?></title>
    <meta name="description" content="VOXEL Copilot — Inteligência que acelera o diagnóstico por imagem">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/auth.css?v=<?= defined('ASSET_VERSION') ? ASSET_VERSION : '2.1.0' ?>">
</head>
<body class="<?= $bodyClass ?? '' ?>">

<!-- ── SELETOR DE IDIOMA ── -->
<div class="lang-bar" role="navigation" aria-label="Seleção de idioma">
    <button class="lang-btn active" aria-pressed="true">🇧🇷 PT</button>
    <span class="lang-sep">·</span>
    <button class="lang-btn" aria-pressed="false">🇺🇸 EN</button>
    <span class="lang-sep">·</span>
    <button class="lang-btn" aria-pressed="false">🇪🇸 ES</button>
</div>

<div class="auth-layout">

    <!-- ══════════════════════════════════════════════════════
         LADO ESQUERDO — 40% — BRAND AZUL INSTITUCIONAL
    ═══════════════════════════════════════════════════════ -->
    <aside class="auth-brand" aria-label="Informações do produto">

        <!-- Fundo abstrato -->
        <div class="brand-bg" aria-hidden="true">
            <svg class="voxel-grid" viewBox="0 0 340 340" fill="none" xmlns="http://www.w3.org/2000/svg">
                <?php
                $sz = 18; $gap = 4; $cols = 14; $rows = 14;
                for ($r = 0; $r < $rows; $r++) {
                    for ($c = 0; $c < $cols; $c++) {
                        $x  = $c * ($sz + $gap);
                        $y  = $r * ($sz + $gap);
                        $op = round(0.12 + ($r + $c) / ($rows + $cols) * 0.45, 2);
                        echo "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$sz}\" height=\"{$sz}\" rx=\"3\" fill=\"white\" fill-opacity=\"{$op}\"/>\n";
                    }
                }
                ?>
            </svg>
            <svg class="dicom-curves" viewBox="0 0 440 220" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 110 Q35 30 70 110 Q105 190 140 110 Q175 30 210 110 Q245 190 280 110 Q315 30 350 110 Q385 190 420 110" stroke="white" stroke-width="1.5" fill="none"/>
                <path d="M0 130 Q45 50 90 130 Q135 210 180 130 Q225 50 270 130 Q315 210 360 130 Q405 50 440 130" stroke="white" stroke-width="1" fill="none" opacity=".5"/>
                <path d="M0 90 Q55 20 110 90 Q165 160 220 90 Q275 20 330 90 Q385 160 440 90" stroke="white" stroke-width=".7" fill="none" opacity=".3"/>
                <circle cx="70"  cy="110" r="3" fill="white" opacity=".4"/>
                <circle cx="140" cy="110" r="2" fill="white" opacity=".3"/>
                <circle cx="210" cy="110" r="3" fill="white" opacity=".4"/>
                <circle cx="280" cy="110" r="2" fill="white" opacity=".3"/>
                <circle cx="350" cy="110" r="3" fill="white" opacity=".4"/>
            </svg>
            <div class="brand-orb brand-orb-1"></div>
            <div class="brand-orb brand-orb-2"></div>
        </div>

        <!-- ── LOGO no lado esquerdo (acima do headline) ── -->
        <div class="brand-logo">
            <img
                src="/assets/img/logo.png"
                alt="VOXEL Copilot — Inteligência que acelera o diagnóstico por imagem"
                class="brand-logo-img"
            >
        </div>

        <!-- Benefícios -->
        <div class="brand-benefits" role="list">
            <?php
            $benefits = [
                ['fa-brain',         'IA Clínica',           'Assistentes inteligentes para laudos e revisão diagnóstica.'],
                ['fa-plug',          'Integração Completa',  'PACS · RIS · HIS · Orthanc · HL7 · FHIR'],
                ['fa-shield-halved', 'Segurança',            'LGPD · HIPAA · ISO 27001 · Auditoria completa'],
                ['fa-gauge-high',    'Alta Performance',     'Projetado para hospitais e redes de diagnóstico.'],
            ];
            foreach ($benefits as [$icon, $title, $desc]):
            ?>
            <div class="benefit-item" role="listitem">
                <div class="benefit-icon" aria-hidden="true">
                    <i class="fa-solid <?= $icon ?>"></i>
                </div>
                <div class="benefit-text">
                    <strong><?= $title ?></strong>
                    <span><?= $desc ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Cards de compliance -->
        <div class="brand-footer" role="complementary" aria-label="Certificações de segurança">
            <div class="compliance-card">
                <div class="compliance-card-title">
                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                    Segurança Corporativa
                </div>
                <p class="compliance-desc">Dados protegidos com criptografia ponta a ponta.</p>
            </div>
            <div class="compliance-card">
                <div class="compliance-card-title">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                    Compliance
                </div>
                <div class="compliance-badges">
                    <span class="compliance-badge">LGPD</span>
                    <span class="compliance-badge">HIPAA</span>
                    <span class="compliance-badge">ISO 27001</span>
                </div>
            </div>
        </div>

    </aside>

    <!-- ══════════════════════════════════════════════════════
         LADO DIREITO — 60% — BRANCO / FORMULÁRIO
    ═══════════════════════════════════════════════════════ -->
    <main class="auth-panel" role="main">
        <div class="auth-box">
