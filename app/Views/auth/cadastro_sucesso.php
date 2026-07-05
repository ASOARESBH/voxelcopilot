<!-- Logo -->
<div class="auth-logo">
    <div class="auth-logo-icon" style="background:linear-gradient(135deg,#10b981,#06b6d4);">
        <i class="fa-solid fa-circle-check"></i>
    </div>
    <div class="auth-logo-text">
        <strong>VOXEL Copilot</strong>
        <span>Cadastro realizado</span>
    </div>
</div>

<div style="text-align:center;padding:20px 0;">
    <div style="width:72px;height:72px;border-radius:50%;background:rgba(16,185,129,.1);border:2px solid rgba(16,185,129,.3);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:1.8rem;color:#10b981;">
        <i class="fa-solid fa-envelope-circle-check"></i>
    </div>

    <h2 class="auth-title" style="text-align:center;">Cadastro realizado!</h2>
    <p class="auth-subtitle" style="text-align:center;margin-bottom:28px;">
        Enviamos sua senha de acesso para<br>
        <strong style="color:var(--primary);"><?= htmlspecialchars($email ?? '') ?></strong>
    </p>

    <div class="auth-alert info" style="text-align:left;">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            <strong>Próximos passos:</strong>
            <ul style="margin-top:6px;">
                <li>Verifique sua caixa de entrada (e o spam).</li>
                <li>Use a senha temporária para fazer login.</li>
                <li>Você poderá alterar a senha após o primeiro acesso.</li>
            </ul>
        </div>
    </div>

    <a href="/login?cadastro=ok" class="btn-primary" style="display:block;text-decoration:none;text-align:center;margin-top:8px;">
        <i class="fa-solid fa-right-to-bracket" style="margin-right:8px;"></i>
        Ir para o Login
    </a>
</div>

<div class="auth-footer">
    <p>Não recebeu o e-mail? <a href="/cadastro">Tente novamente</a> ou entre em contato com o suporte.</p>
</div>
