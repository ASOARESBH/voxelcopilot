<?php use App\Core\Auth; $user = Auth::user(); ?>

<!-- Logo -->
<div class="auth-logo">
    <div class="auth-logo-icon">
        <i class="fa-solid fa-hospital"></i>
    </div>
    <div class="auth-logo-text">
        <strong>VOXEL Copilot</strong>
        <span>Selecionar clínica</span>
    </div>
</div>

<h2 class="auth-title">Selecione a clínica</h2>
<p class="auth-subtitle">
    Olá, <strong style="color:var(--primary);"><?= htmlspecialchars($user?->name ?? 'Médico') ?></strong>.
    Você possui acesso a mais de uma clínica.
</p>

<form method="POST" action="/selecionar-empresa">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px;">
        <?php foreach ($tenants as $t): ?>
        <label style="cursor:pointer;">
            <input type="radio" name="tenant_id" value="<?= (int)$t->tenant_id ?>" style="display:none;" class="tenant-radio">
            <div class="tenant-card" data-id="<?= (int)$t->tenant_id ?>">
                <div class="tenant-icon">
                    <i class="fa-solid fa-hospital-user"></i>
                </div>
                <div class="tenant-info">
                    <strong><?= htmlspecialchars($t->nome) ?></strong>
                    <span><?= htmlspecialchars(ucfirst($t->role)) ?></span>
                </div>
                <div class="tenant-arrow">
                    <i class="fa-solid fa-chevron-right"></i>
                </div>
            </div>
        </label>
        <?php endforeach; ?>
    </div>

    <button type="submit" class="btn-primary" id="btn-selecionar" disabled>
        <i class="fa-solid fa-arrow-right" style="margin-right:8px;"></i>
        Acessar Clínica Selecionada
    </button>
</form>

<div style="text-align:center;margin-top:20px;">
    <a href="/logout" class="auth-link" style="font-size:.83rem;">
        <i class="fa-solid fa-right-from-bracket" style="margin-right:6px;"></i>
        Sair da conta
    </a>
</div>

<style>
.tenant-card {
    display: flex; align-items: center; gap: 14px;
    background: rgba(255,255,255,.03);
    border: 1.5px solid rgba(255,255,255,.07);
    border-radius: 12px; padding: 14px 16px;
    transition: all .2s;
}
.tenant-card:hover {
    border-color: var(--border);
    background: rgba(14,165,233,.05);
}
.tenant-card.selected {
    border-color: var(--primary);
    background: rgba(14,165,233,.08);
    box-shadow: 0 0 0 3px rgba(14,165,233,.1);
}
.tenant-icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: linear-gradient(135deg, rgba(14,165,233,.2), rgba(6,182,212,.1));
    border: 1px solid rgba(14,165,233,.2);
    display: flex; align-items: center; justify-content: center;
    color: var(--primary); font-size: .9rem; flex-shrink: 0;
}
.tenant-info { flex: 1; }
.tenant-info strong { display: block; font-size: .9rem; color: #e2e8f0; }
.tenant-info span { font-size: .75rem; color: var(--muted); }
.tenant-arrow { color: var(--muted); font-size: .75rem; }
.tenant-card.selected .tenant-arrow { color: var(--primary); }
</style>

<script>
document.querySelectorAll('.tenant-radio').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.tenant-card').forEach(function(c) { c.classList.remove('selected'); });
        this.closest('label').querySelector('.tenant-card').classList.add('selected');
        document.getElementById('btn-selecionar').disabled = false;
    });
});
document.querySelectorAll('.tenant-card').forEach(function(card) {
    card.addEventListener('click', function() {
        const radio = this.closest('label').querySelector('input[type="radio"]');
        if (radio) { radio.checked = true; radio.dispatchEvent(new Event('change')); }
    });
});
</script>
