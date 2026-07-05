<?php
$old = $old ?? [];
$ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
?>

<!-- Logo -->
<div class="auth-logo">
    <div class="auth-logo-icon">
        <i class="fa-solid fa-user-doctor"></i>
    </div>
    <div class="auth-logo-text">
        <strong>VOXEL Copilot</strong>
        <span>Primeiro acesso</span>
    </div>
</div>

<h2 class="auth-title">Criar sua conta</h2>
<p class="auth-subtitle">Preencha seus dados para começar. Sua senha será enviada por e-mail.</p>

<?php if (!empty($erros)): ?>
<div class="auth-alert danger">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
        <strong>Corrija os erros abaixo:</strong>
        <ul>
            <?php foreach ($erros as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<form method="POST" action="/cadastro" id="form-cadastro">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

    <!-- DADOS PESSOAIS -->
    <div style="font-size:.7rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.1em;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid rgba(14,165,233,.12);">
        <i class="fa-solid fa-user" style="margin-right:6px;"></i>Dados Pessoais
    </div>

    <div class="field-group">
        <label class="field-label" for="nome">Nome completo <span class="required">*</span></label>
        <div class="field-wrap">
            <i class="fa-solid fa-user field-icon"></i>
            <input type="text" id="nome" name="nome" class="field-input"
                placeholder="Dr. Nome Completo"
                value="<?= htmlspecialchars($old['nome'] ?? '') ?>" required>
        </div>
    </div>

    <div class="field-group">
        <label class="field-label" for="email">E-mail <span class="required">*</span></label>
        <div class="field-wrap">
            <i class="fa-solid fa-envelope field-icon"></i>
            <input type="email" id="email" name="email" class="field-input"
                placeholder="seu@email.com.br"
                value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
        </div>
    </div>

    <div class="field-row">
        <div class="field-group">
            <label class="field-label" for="crm">CRM <span class="required">*</span></label>
            <div class="field-wrap">
                <i class="fa-solid fa-id-card field-icon"></i>
                <input type="text" id="crm" name="crm" class="field-input"
                    placeholder="000000"
                    value="<?= htmlspecialchars($old['crm'] ?? '') ?>" required>
            </div>
        </div>
        <div class="field-group">
            <label class="field-label" for="crm_uf">UF do CRM <span class="required">*</span></label>
            <div class="field-wrap">
                <i class="fa-solid fa-map-pin field-icon"></i>
                <select id="crm_uf" name="crm_uf" class="field-select" required>
                    <option value="">UF</option>
                    <?php foreach ($ufs as $uf): ?>
                    <option value="<?= $uf ?>" <?= ($old['crm_uf'] ?? '') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="field-group">
        <label class="field-label" for="telefone">Telefone / WhatsApp</label>
        <div class="field-wrap">
            <i class="fa-solid fa-phone field-icon"></i>
            <input type="tel" id="telefone" name="telefone" class="field-input"
                placeholder="(11) 99999-9999"
                value="<?= htmlspecialchars($old['telefone'] ?? '') ?>">
        </div>
    </div>

    <!-- ESPECIALIDADES -->
    <div style="font-size:.7rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.1em;margin:20px 0 14px;padding-bottom:8px;border-bottom:1px solid rgba(14,165,233,.12);">
        <i class="fa-solid fa-stethoscope" style="margin-right:6px;"></i>Especialidades <span class="required">*</span>
    </div>

    <div class="field-group">
        <div class="espec-grid">
            <?php foreach ($especialidades as $esp): ?>
            <label class="espec-item">
                <input type="checkbox" name="especialidades[]" value="<?= htmlspecialchars($esp) ?>"
                    <?= in_array($esp, (array)($old['especialidades'] ?? [])) ? 'checked' : '' ?>>
                <span class="espec-check"><i class="fa-solid fa-check"></i></span>
                <span><?= htmlspecialchars($esp) ?></span>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ENDEREÇO -->
    <div style="font-size:.7rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.1em;margin:20px 0 14px;padding-bottom:8px;border-bottom:1px solid rgba(14,165,233,.12);">
        <i class="fa-solid fa-location-dot" style="margin-right:6px;"></i>Endereço <span class="required">*</span>
    </div>

    <div class="field-group">
        <label class="field-label" for="cep">CEP <span class="required">*</span></label>
        <div class="field-wrap">
            <i class="fa-solid fa-map-location-dot field-icon"></i>
            <input type="text" id="cep" name="cep" class="field-input"
                placeholder="00000-000" maxlength="9"
                data-mask="cep" data-cep-trigger
                value="<?= htmlspecialchars($old['cep'] ?? '') ?>" required>
        </div>
    </div>

    <div class="field-group">
        <label class="field-label" for="logradouro">Logradouro <span class="required">*</span></label>
        <div class="field-wrap">
            <i class="fa-solid fa-road field-icon"></i>
            <input type="text" id="logradouro" name="logradouro" class="field-input"
                placeholder="Rua, Avenida..."
                value="<?= htmlspecialchars($old['logradouro'] ?? '') ?>" required>
        </div>
    </div>

    <div class="field-row">
        <div class="field-group">
            <label class="field-label" for="numero">Número <span class="required">*</span></label>
            <div class="field-wrap">
                <i class="fa-solid fa-hashtag field-icon"></i>
                <input type="text" id="numero" name="numero" class="field-input"
                    placeholder="123"
                    value="<?= htmlspecialchars($old['numero'] ?? '') ?>">
            </div>
        </div>
        <div class="field-group">
            <label class="field-label" for="complemento">Complemento</label>
            <div class="field-wrap">
                <i class="fa-solid fa-building field-icon"></i>
                <input type="text" id="complemento" name="complemento" class="field-input"
                    placeholder="Sala, Apto..."
                    value="<?= htmlspecialchars($old['complemento'] ?? '') ?>">
            </div>
        </div>
    </div>

    <div class="field-group">
        <label class="field-label" for="bairro">Bairro</label>
        <div class="field-wrap">
            <i class="fa-solid fa-map field-icon"></i>
            <input type="text" id="bairro" name="bairro" class="field-input"
                placeholder="Bairro"
                value="<?= htmlspecialchars($old['bairro'] ?? '') ?>">
        </div>
    </div>

    <div class="field-row">
        <div class="field-group">
            <label class="field-label" for="cidade">Cidade <span class="required">*</span></label>
            <div class="field-wrap">
                <i class="fa-solid fa-city field-icon"></i>
                <input type="text" id="cidade" name="cidade" class="field-input"
                    placeholder="Cidade"
                    value="<?= htmlspecialchars($old['cidade'] ?? '') ?>" required>
            </div>
        </div>
        <div class="field-group">
            <label class="field-label" for="estado">Estado <span class="required">*</span></label>
            <div class="field-wrap">
                <i class="fa-solid fa-flag field-icon"></i>
                <select id="estado" name="estado" class="field-select" required>
                    <option value="">UF</option>
                    <?php foreach ($ufs as $uf): ?>
                    <option value="<?= $uf ?>" <?= ($old['estado'] ?? '') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <button type="submit" class="btn-primary" id="btn-cadastro" style="margin-top:12px;">
        <i class="fa-solid fa-paper-plane" style="margin-right:8px;"></i>
        Finalizar Cadastro — Receber Senha por E-mail
    </button>
</form>

<div style="text-align:center;margin-top:20px;">
    <a href="/login" class="auth-link" style="font-size:.83rem;">
        <i class="fa-solid fa-arrow-left" style="margin-right:6px;"></i>
        Já tenho conta — Fazer login
    </a>
</div>

<div class="auth-footer">
    <p>Ao se cadastrar, você concorda com nossos <a href="#">Termos de Uso</a> e <a href="#">Política de Privacidade</a>.</p>
</div>

<script>
document.getElementById('form-cadastro').addEventListener('submit', function() {
    const btn = document.getElementById('btn-cadastro');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:8px;"></i>Enviando...';
});
</script>
