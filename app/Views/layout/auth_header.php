<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'VOXEL Copilot') ?></title>
    <meta name="description" content="VOXEL Copilot — Sistema Operacional de Laudos com Inteligência Artificial">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/auth.css?v=<?= defined('ASSET_VERSION') ? ASSET_VERSION : '1.0.0' ?>">
</head>
<body>

<!-- Fundo animado -->
<div id="auth-bg"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>
<div class="orb orb-4"></div>

<!-- Layout split -->
<div class="auth-layout">

    <!-- ── LADO ESQUERDO: BRANDING ── -->
    <div class="auth-brand">
        <div class="auth-brand-badge">
            <span>Sistema Operacional de Laudos</span>
        </div>

        <h1>
            O futuro do<br>
            laudo médico<br>
            <span class="gradient-text">começa aqui.</span>
        </h1>

        <p>
            O VOXEL Copilot aprende o seu estilo, entende o contexto clínico
            e transforma a elaboração de laudos em uma experiência inteligente,
            fluida e precisa.
        </p>

        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-brain"></i></div>
                <div class="feature-card-text">
                    <strong>IA Adaptativa</strong>
                    <span>Aprende seu vocabulário e estilo de redação</span>
                </div>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-microscope"></i></div>
                <div class="feature-card-text">
                    <strong>Integração PACS</strong>
                    <span>Conecta direto com seu sistema de imagens</span>
                </div>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <div class="feature-card-text">
                    <strong>Quality Engine</strong>
                    <span>Revisa lateralidade, CID e consistência</span>
                </div>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-microphone-lines"></i></div>
                <div class="feature-card-text">
                    <strong>Voz para Texto</strong>
                    <span>Dite o laudo com vocabulário médico</span>
                </div>
            </div>
        </div>

        <div class="auth-stats">
            <div class="stat-item">
                <div class="stat-num">10×</div>
                <div class="stat-label">Mais rápido</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">99%</div>
                <div class="stat-label">Precisão</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">24/7</div>
                <div class="stat-label">Disponível</div>
            </div>
        </div>
    </div>

    <!-- ── LADO DIREITO: PAINEL DE AUTH ── -->
    <div class="auth-panel">
        <div class="auth-box">
