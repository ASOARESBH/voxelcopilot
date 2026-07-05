<?php
use App\Core\Auth;
$user     = Auth::user();
$isPlatform = Auth::isPlatformAdmin();
$isImpersonating = Auth::isImpersonating();
$currentUri = strtok($_SERVER['REQUEST_URI'], '?');
$initials = '';
if ($user) {
    $parts = explode(' ', $user->name ?? '');
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
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/copilot.css?v=<?= defined('ASSET_VERSION') ? ASSET_VERSION : '1.0.0' ?>">
    <?php if (isset($extraCss)): foreach ($extraCss as $css): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
    <?php endforeach; endif; ?>
</head>
<body<?= $isImpersonating ? ' class="impersonating"' : '' ?>>

<?php if ($isImpersonating): ?>
<!-- Barra de impersonação -->
<div class="impersonate-bar">
    <span>
        <i class="fa-solid fa-eye" style="margin-right:6px;"></i>
        Você está visualizando como <strong style="margin-left:4px;"><?= htmlspecialchars($_SESSION['user']->name ?? 'Médico') ?></strong>
    </span>
    <a href="/platform/sair-impersonacao">
        <i class="fa-solid fa-arrow-right-from-bracket" style="margin-right:4px;"></i>
        Voltar ao Admin
    </a>
</div>
<?php endif; ?>

<div class="app-layout">

<!-- ══════════════════════════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════════════════════════ -->
<aside class="sidebar" id="sidebar">

    <!-- Logo -->
    <a href="<?= $isPlatform ? '/platform/dashboard' : '/dashboard' ?>" class="sidebar-logo">
        <div class="sidebar-logo-icon">
            <i class="fa-solid fa-stethoscope"></i>
        </div>
        <div class="sidebar-logo-text">
            <strong>VOXEL Copilot</strong>
            <span><?= $isPlatform ? 'Plataforma' : 'Workspace' ?></span>
        </div>
    </a>

    <?php if ($isPlatform && !$isImpersonating): ?>
    <!-- ── NAV SUPERADMIN ── -->
    <div class="sidebar-section">
        <div class="sidebar-section-label">Plataforma</div>
        <ul class="sidebar-nav">
            <li><a href="/platform/dashboard" class="<?= $currentUri === '/platform/dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a></li>
            <li><a href="/platform/medicos" class="<?= str_starts_with($currentUri, '/platform/medicos') ? 'active' : '' ?>">
                <i class="fa-solid fa-user-doctor"></i> Médicos
            </a></li>
            <li><a href="/platform/planos" class="<?= str_starts_with($currentUri, '/platform/planos') ? 'active' : '' ?>">
                <i class="fa-solid fa-layer-group"></i> Planos
            </a></li>
        </ul>
    </div>
    <div class="sidebar-section">
        <div class="sidebar-section-label">Sistema</div>
        <ul class="sidebar-nav">
            <li><a href="/platform/auditoria" class="<?= str_starts_with($currentUri, '/platform/auditoria') ? 'active' : '' ?>">
                <i class="fa-solid fa-scroll"></i> Auditoria
            </a></li>
        </ul>
    </div>

    <?php else: ?>
    <!-- ── NAV MÉDICO ── -->
    <div class="sidebar-section">
        <div class="sidebar-section-label">Workspace</div>
        <ul class="sidebar-nav">
            <li><a href="/dashboard" class="<?= $currentUri === '/dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a></li>
            <li><a href="/workspace" class="<?= str_starts_with($currentUri, '/workspace') ? 'active' : '' ?>">
                <i class="fa-solid fa-file-medical"></i> Laudos
            </a></li>
            <li><a href="/templates" class="<?= str_starts_with($currentUri, '/templates') ? 'active' : '' ?>">
                <i class="fa-solid fa-file-lines"></i> Templates
            </a></li>
            <li><a href="/autotextos" class="<?= str_starts_with($currentUri, '/autotextos') ? 'active' : '' ?>">
                <i class="fa-solid fa-bolt"></i> Autotextos
            </a></li>
        </ul>
    </div>
    <div class="sidebar-section">
        <div class="sidebar-section-label">Configurações</div>
        <ul class="sidebar-nav">
            <li><a href="/perfil" class="<?= str_starts_with($currentUri, '/perfil') ? 'active' : '' ?>">
                <i class="fa-solid fa-user-gear"></i> Meu Perfil
            </a></li>
            <li><a href="/pacs" class="<?= str_starts_with($currentUri, '/pacs') ? 'active' : '' ?>">
                <i class="fa-solid fa-server"></i> Conexão PACS
            </a></li>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Usuário -->
    <div class="sidebar-footer">
        <a href="/logout" class="sidebar-user" title="Sair">
            <div class="sidebar-avatar"><?= htmlspecialchars($initials ?: 'U') ?></div>
            <div class="sidebar-user-info">
                <strong><?= htmlspecialchars($user?->name ?? 'Usuário') ?></strong>
                <span><?= $isPlatform ? 'Super Admin' : 'Dr(a).' ?></span>
            </div>
            <i class="fa-solid fa-right-from-bracket" style="color:var(--muted);font-size:.7rem;margin-left:auto;"></i>
        </a>
    </div>

</aside>

<!-- ══════════════════════════════════════════════════════════
     TOPBAR
═══════════════════════════════════════════════════════════ -->
<header class="topbar">
    <button class="btn btn-ghost btn-sm" id="sidebar-toggle" style="display:none;" title="Menu">
        <i class="fa-solid fa-bars"></i>
    </button>
    <div class="topbar-title">
        <?= htmlspecialchars($pageTitle ?? $title ?? 'VOXEL Copilot') ?>
        <?php if (!empty($pageSubtitle)): ?>
        <span><?= htmlspecialchars($pageSubtitle) ?></span>
        <?php endif; ?>
    </div>
    <div class="topbar-actions">
        <?php if ($isPlatform && !$isImpersonating): ?>
        <span style="font-size:.72rem;color:var(--muted);background:rgba(14,165,233,.08);border:1px solid var(--border);padding:4px 12px;border-radius:100px;">
            <i class="fa-solid fa-shield-halved" style="color:var(--primary);margin-right:5px;"></i>
            Plataforma Admin
        </span>
        <?php endif; ?>
        <a href="/logout" class="btn btn-ghost btn-sm" title="Sair">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>
</header>

<!-- ══════════════════════════════════════════════════════════
     CONTEÚDO PRINCIPAL
═══════════════════════════════════════════════════════════ -->
<main class="main-content">
