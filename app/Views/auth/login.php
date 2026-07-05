<?php use App\Core\Auth; ?>

<!-- Logo -->
<div class="auth-logo">
    <div class="auth-logo-icon">
        <i class="fa-solid fa-stethoscope"></i>
    </div>
    <div class="auth-logo-text">
        <strong>VOXEL Copilot</strong>
        <span>Acesso ao sistema</span>
    </div>
</div>

<h2 class="auth-title">Bem-vindo de volta</h2>
<p class="auth-subtitle">Entre com suas credenciais para acessar o sistema.</p>

<?php if (!empty($error)): ?>
<div class="auth-alert danger">
    <i class="fa-solid fa-circle-exclamation"></i>
    <span><?= htmlspecialchars($error) ?></span>
</div>
<?php endif; ?>

<?php if (isset($_GET['cadastro']) && $_GET['cadastro'] === 'ok'): ?>
<div class="auth-alert success">
    <i class="fa-solid fa-circle-check"></i>
    <span>Cadastro realizado! Verifique seu e-mail para obter a senha de acesso.</span>
</div>
<?php endif; ?>

<form method="POST" action="/login" id="form-login">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

    <div class="field-group">
        <label class="field-label" for="email">E-mail <span class="required">*</span></label>
        <div class="field-wrap">
            <i class="fa-solid fa-envelope field-icon"></i>
            <input
                type="email"
                id="email"
                name="email"
                class="field-input"
                placeholder="seu@email.com.br"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                autocomplete="email"
                required
            >
        </div>
    </div>

    <div class="field-group">
        <label class="field-label" for="password">Senha <span class="required">*</span></label>
        <div class="field-wrap">
            <i class="fa-solid fa-lock field-icon"></i>
            <input
                type="password"
                id="password"
                name="password"
                class="field-input"
                placeholder="••••••••••"
                autocomplete="current-password"
                required
            >
            <button type="button" class="btn-eye" title="Mostrar/ocultar senha">
                <i class="fa-solid fa-eye"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn-primary" id="btn-login">
        <i class="fa-solid fa-right-to-bracket" style="margin-right:8px;"></i>
        Entrar no Copilot
    </button>
</form>

<div class="auth-divider"><span>ou</span></div>

<div style="text-align:center;">
    <a href="/cadastro" class="btn-secondary" style="width:100%;">
        <i class="fa-solid fa-user-plus"></i>
        Primeiro acesso — Cadastrar-se
    </a>
</div>

<div class="auth-footer" style="margin-top:28px;">
    <p>© 2026 VOXEL Copilot &nbsp;·&nbsp; <a href="#">Termos de uso</a> &nbsp;·&nbsp; <a href="#">Privacidade</a></p>
</div>

<script>
document.getElementById('form-login').addEventListener('submit', function() {
    const btn = document.getElementById('btn-login');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:8px;"></i>Entrando...';
});
</script>
