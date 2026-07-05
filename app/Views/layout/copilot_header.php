<?php
use App\Core\Auth;
$user            = Auth::user();
$isPlatform      = Auth::isPlatformAdmin();
$isImpersonating = Auth::isImpersonating();
$currentUri      = strtok($_SERVER['REQUEST_URI'], '?');
$initials = '';
if ($user) {
    $parts    = explode(' ', $user->name ?? '');
    $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'VOXEL Copilot') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/copilot.css?v=<?= defined('ASSET_VERSION') ? ASSET_VERSION : '2.0.0' ?>">
    <?php if (isset($extraCss)): foreach ($extraCss as $css): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
    <?php endforeach; endif; ?>
</head>
<body<?= $isImpersonating ? ' class="impersonating"' : '' ?>>

<?php if ($isImpersonating): ?>
<!-- Barra de impersonação -->
<div class="impersonate-bar" role="alert">
    <span>
        <i class="fa-solid fa-eye" aria-hidden="true"></i>
        Você está visualizando como <strong style="margin-left:4px;"><?= htmlspecialchars($_SESSION['user']->name ?? 'Médico') ?></strong>
    </span>
    <a href="/platform/sair-impersonacao">
        <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
        Voltar ao Admin
    </a>
</div>
<?php endif; ?>

<!-- Overlay sidebar mobile -->
<div class="sidebar-overlay" id="sidebar-overlay" aria-hidden="true"></div>

<div class="app-layout">

<!-- ══════════════════════════════════════════════════════════
     SIDEBAR — Azul institucional
═══════════════════════════════════════════════════════════ -->
<aside class="sidebar" id="sidebar" role="navigation" aria-label="Menu principal">

    <!-- Logo -->
    <a href="<?= $isPlatform ? '/platform/dashboard' : '/dashboard' ?>" class="sidebar-logo" aria-label="VOXEL Copilot — Início">
        <img
            src="/assets/img/logo.png"
            alt="VOXEL Copilot"
            class="sidebar-logo-img"
            onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
        >
        <!-- Fallback se logo não carregar -->
        <div class="sidebar-logo-icon" style="display:none;">
            <i class="fa-solid fa-brain" aria-hidden="true"></i>
        </div>
    </a>

    <?php if ($isPlatform && !$isImpersonating): ?>
    <!-- ── NAV SUPERADMIN ── -->
    <div class="sidebar-section">
        <div class="sidebar-section-label">Plataforma</div>
        <ul class="sidebar-nav">
            <li>
                <a href="/platform/dashboard" class="<?= $currentUri === '/platform/dashboard' ? 'active' : '' ?>">
                    <i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="/platform/medicos" class="<?= str_starts_with($currentUri, '/platform/medicos') ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-doctor" aria-hidden="true"></i> Médicos
                </a>
            </li>
            <li>
                <a href="/platform/planos" class="<?= str_starts_with($currentUri, '/platform/planos') ? 'active' : '' ?>">
                    <i class="fa-solid fa-layer-group" aria-hidden="true"></i> Planos
                </a>
            </li>
        </ul>
    </div>
    <div class="sidebar-section">
        <div class="sidebar-section-label">Sistema</div>
        <ul class="sidebar-nav">
            <li>
                <a href="/platform/auditoria" class="<?= str_starts_with($currentUri, '/platform/auditoria') ? 'active' : '' ?>">
                    <i class="fa-solid fa-scroll" aria-hidden="true"></i> Auditoria
                </a>
            </li>
        </ul>
    </div>

    <?php else: ?>
    <!-- ── NAV MÉDICO — Menu Definitivo ── -->
    <div class="sidebar-section">
        <div class="sidebar-section-label">Central de Trabalho</div>
        <ul class="sidebar-nav">
            <li>
                <a href="/dashboard" class="<?= $currentUri === '/dashboard' ? 'active' : '' ?>">
                    <i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="/workspace" class="<?= str_starts_with($currentUri, '/workspace') ? 'active' : '' ?>">
                    <i class="fa-solid fa-desktop" aria-hidden="true"></i> Meu Workspace
                </a>
            </li>
            <li>
                <a href="/fila" class="<?= str_starts_with($currentUri, '/fila') ? 'active' : '' ?>">
                    <i class="fa-solid fa-list-check" aria-hidden="true"></i> Fila Inteligente
                    <span class="sidebar-badge">18</span>
                </a>
            </li>
            <li>
                <a href="/pacientes" class="<?= str_starts_with($currentUri, '/pacientes') ? 'active' : '' ?>">
                    <i class="fa-solid fa-users" aria-hidden="true"></i> Pacientes
                </a>
            </li>
            <li>
                <a href="/timeline" class="<?= str_starts_with($currentUri, '/timeline') ? 'active' : '' ?>">
                    <i class="fa-solid fa-timeline" aria-hidden="true"></i> Timeline Clínica
                </a>
            </li>
            <li>
                <a href="/comparativos" class="<?= str_starts_with($currentUri, '/comparativos') ? 'active' : '' ?>">
                    <i class="fa-solid fa-code-compare" aria-hidden="true"></i> Comparativos
                </a>
            </li>
            <li>
                <a href="/viewer" class="<?= str_starts_with($currentUri, '/viewer') ? 'active' : '' ?>">
                    <i class="fa-solid fa-image" aria-hidden="true"></i> Viewer
                </a>
            </li>
        </ul>
    </div>
    <div class="sidebar-section">
        <div class="sidebar-section-label">Inteligência</div>
        <ul class="sidebar-nav">
            <li>
                <a href="/copilot" class="<?= str_starts_with($currentUri, '/copilot') ? 'active' : '' ?>">
                    <i class="fa-solid fa-brain" aria-hidden="true"></i> Copilot IA
                    <span class="sidebar-badge sidebar-badge-blue">12</span>
                </a>
            </li>
            <li>
                <a href="/vision" class="<?= str_starts_with($currentUri, '/vision') ? 'active' : '' ?>">
                    <i class="fa-solid fa-eye" aria-hidden="true"></i> Vision AI
                </a>
            </li>
            <li>
                <a href="/speech" class="<?= str_starts_with($currentUri, '/speech') ? 'active' : '' ?>">
                    <i class="fa-solid fa-microphone" aria-hidden="true"></i> Speech
                </a>
            </li>
            <li>
                <a href="/templates" class="<?= str_starts_with($currentUri, '/templates') ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-lines" aria-hidden="true"></i> Templates
                </a>
            </li>
            <li>
                <a href="/pesquisa" class="<?= str_starts_with($currentUri, '/pesquisa') ? 'active' : '' ?>">
                    <i class="fa-solid fa-flask" aria-hidden="true"></i> Pesquisa Clínica
                </a>
            </li>
            <li>
                <a href="/medical-profile" class="<?= str_starts_with($currentUri, '/medical-profile') ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-gear" aria-hidden="true"></i> Medical Profile
                </a>
            </li>
        </ul>
    </div>
    <div class="sidebar-section">
        <div class="sidebar-section-label">Gestão</div>
        <ul class="sidebar-nav">
            <li>
                <a href="/analytics" class="<?= str_starts_with($currentUri, '/analytics') ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-bar" aria-hidden="true"></i> Analytics
                </a>
            </li>
            <li>
                <a href="/ai-router" class="<?= str_starts_with($currentUri, '/ai-router') ? 'active' : '' ?>">
                    <i class="fa-solid fa-route" aria-hidden="true"></i> AI Router
                    <span class="sidebar-badge sidebar-badge-blue" style="background:#7c3aed;">IA</span>
                </a>
            </li>
            <li>
                <a href="/marketplace" class="<?= str_starts_with($currentUri, '/marketplace') ? 'active' : '' ?>">
                    <i class="fa-solid fa-store" aria-hidden="true"></i> Marketplace
                </a>
            </li>
            <li>
                <a href="/integracoes" class="<?= str_starts_with($currentUri, '/integracoes') ? 'active' : '' ?>">
                    <i class="fa-solid fa-plug" aria-hidden="true"></i> Integrações
                </a>
            </li>
            <li>
                <a href="/configuracoes" class="<?= str_starts_with($currentUri, '/configuracoes') ? 'active' : '' ?>">
                    <i class="fa-solid fa-gear" aria-hidden="true"></i> Configurações
                </a>
            </li>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Usuário no rodapé -->
    <div class="sidebar-footer">
        <a href="/logout" class="sidebar-user" title="Sair da plataforma">
            <div class="sidebar-avatar" aria-hidden="true"><?= htmlspecialchars($initials ?: 'U') ?></div>
            <div class="sidebar-user-info">
                <strong><?= htmlspecialchars($user?->name ?? 'Usuário') ?></strong>
                <span><?= $isPlatform ? 'Super Admin' : 'Dr(a).' ?></span>
            </div>
            <i class="fa-solid fa-right-from-bracket" style="color:rgba(255,255,255,.35);font-size:.68rem;margin-left:auto;" aria-hidden="true"></i>
        </a>
    </div>

</aside>

<!-- ══════════════════════════════════════════════════════════
     TOPBAR — Branco limpo
═══════════════════════════════════════════════════════════ -->
<header class="topbar" role="banner">
    <button class="btn btn-ghost btn-sm" id="sidebar-toggle" style="display:none;" title="Abrir menu" aria-label="Abrir menu lateral" aria-expanded="false" aria-controls="sidebar">
        <i class="fa-solid fa-bars" aria-hidden="true"></i>
    </button>
    <div class="topbar-title">
        <?= htmlspecialchars($pageTitle ?? $title ?? 'VOXEL Copilot') ?>
        <?php if (!empty($pageSubtitle)): ?>
        <span><?= htmlspecialchars($pageSubtitle) ?></span>
        <?php endif; ?>
    </div>
    <div class="topbar-actions">
        <?php if ($isPlatform && !$isImpersonating): ?>
        <span class="topbar-badge-admin">
            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
            Plataforma Admin
        </span>
        <?php endif; ?>
        <a href="/logout" class="btn btn-ghost btn-sm" title="Sair" aria-label="Sair da plataforma">
            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
        </a>
    </div>
</header>

<!-- ══════════════════════════════════════════════════════════
     CONTEÚDO PRINCIPAL
═══════════════════════════════════════════════════════════ -->
<main class="main-content" id="main-content">
