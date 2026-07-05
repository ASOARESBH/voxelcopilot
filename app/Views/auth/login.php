<?php /* VOXEL Copilot — Login Enterprise */ ?>

<!-- Card de login -->
<div class="login-card" role="region" aria-label="Formulário de acesso">

    <!-- Linha decorativa topo -->
    <div class="card-accent-line" aria-hidden="true"></div>

    <!-- Cabeçalho -->
    <div class="card-header">
        <h2>Bem-vindo ao VOXEL Copilot</h2>
        <p>Entre com suas credenciais para acessar sua plataforma.</p>
    </div>

    <!-- Alertas -->
    <?php if (!empty($error)): ?>
    <div class="login-alert danger" role="alert" aria-live="assertive">
        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['cadastro']) && $_GET['cadastro'] === 'ok'): ?>
    <div class="login-alert success" role="status" aria-live="polite">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        <span>Cadastro realizado! Verifique seu e-mail para obter a senha de acesso.</span>
    </div>
    <?php endif; ?>

    <!-- Formulário -->
    <form method="POST" action="/login" id="form-login" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

        <!-- E-mail -->
        <div class="field-group">
            <label class="field-label" for="email">E-mail institucional</label>
            <div class="field-wrap">
                <i class="fa-regular fa-envelope field-icon" aria-hidden="true"></i>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="field-input"
                    placeholder="seu@hospital.com.br"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    autocomplete="email"
                    required
                    aria-required="true"
                    autofocus
                >
            </div>
        </div>

        <!-- Senha -->
        <div class="field-group">
            <label class="field-label" for="password">Senha</label>
            <div class="field-wrap">
                <i class="fa-solid fa-lock field-icon" aria-hidden="true"></i>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="field-input"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                    aria-required="true"
                >
                <button type="button" class="btn-eye" aria-label="Mostrar senha" title="Mostrar/ocultar senha">
                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <!-- Manter conectado + Esqueci senha -->
        <div class="field-row-options">
            <label class="checkbox-wrap">
                <input type="checkbox" name="remember" id="remember" value="1">
                <span class="checkbox-label">Manter conectado</span>
            </label>
            <a href="/recuperar-senha" class="forgot-link">Esqueci minha senha</a>
        </div>

        <!-- Botão entrar -->
        <button type="submit" class="btn-login" id="btn-login" aria-label="Entrar na plataforma">
            <span class="btn-text">
                <i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i>
                Entrar
            </span>
            <span class="btn-spinner" aria-hidden="true" aria-live="polite">
                <i class="fa-solid fa-spinner fa-spin"></i>
                Autenticando...
            </span>
        </button>

    </form>

    <!-- Separador -->
    <div class="auth-divider" aria-hidden="true">
        <span>ou continue com</span>
    </div>

    <!-- Botões SSO -->
    <div class="sso-buttons" role="group" aria-label="Autenticação corporativa">

        <!-- Google -->
        <button type="button" class="btn-sso" aria-label="Entrar com Google" disabled title="Em breve">
            <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true" focusable="false">
                <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
                <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
                <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
                <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
            </svg>
            Entrar com Google
            <span class="sso-badge">Em breve</span>
        </button>

        <!-- Microsoft -->
        <button type="button" class="btn-sso" aria-label="Entrar com Microsoft" disabled title="Em breve">
            <div class="ms-icon" aria-hidden="true">
                <span class="ms-r"></span>
                <span class="ms-g"></span>
                <span class="ms-b"></span>
                <span class="ms-y"></span>
            </div>
            Entrar com Microsoft
            <span class="sso-badge">Em breve</span>
        </button>

        <!-- SSO Corporativo -->
        <button type="button" class="btn-sso enterprise" aria-label="Entrar com SSO Corporativo" disabled title="Em breve">
            <i class="fa-solid fa-building-columns" aria-hidden="true"></i>
            Entrar com SSO Corporativo
            <span class="sso-badge enterprise-badge">Azure · Okta · SAML</span>
        </button>

    </div>

    <!-- Link cadastro -->
    <div class="card-footer-text">
        Ainda não tem acesso?
        <a href="/cadastro">Solicitar cadastro</a>
    </div>

</div><!-- /.login-card -->

<script>
// Loading no submit
document.getElementById('form-login').addEventListener('submit', function(e) {
    var email = document.getElementById('email').value.trim();
    var pwd   = document.getElementById('password').value;
    if (!email || !pwd) { e.preventDefault(); return; }

    var btn = document.getElementById('btn-login');
    btn.classList.add('loading');
    btn.disabled = true;
    btn.setAttribute('aria-busy', 'true');
});

// Limpa estado de erro ao digitar
['email','password'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
});
</script>
