<div class="page-header">
    <div class="page-header-left">
        <h1>Planos</h1>
        <p>Gerencie os planos disponíveis na plataforma</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">
    <?php foreach ($planos as $p): ?>
    <div class="card" style="position:relative;overflow:visible;">
        <!-- Topo colorido -->
        <div style="height:3px;background:linear-gradient(90deg,var(--primary),var(--accent));border-radius:14px 14px 0 0;"></div>
        <div class="card-body" style="padding:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                <div>
                    <div style="font-family:var(--font-head);font-size:1.1rem;font-weight:800;color:#fff;">
                        <?= htmlspecialchars($p->nome) ?>
                    </div>
                    <div style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;">
                        <?= htmlspecialchars($p->slug) ?>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-family:var(--font-head);font-size:1.5rem;font-weight:800;color:var(--primary);">
                        R$ <?= number_format($p->preco_mensal, 2, ',', '.') ?>
                    </div>
                    <div style="font-size:.7rem;color:var(--muted);">/mês</div>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:20px;">
                <?php
                $features = [
                    ["fa-user-doctor", "Até {$p->max_medicos} médico(s)", true],
                    ["fa-file-medical", number_format($p->max_laudos_mes) . " laudos/mês", true],
                    ["fa-brain", "Inteligência Artificial", (bool)$p->permite_ia],
                    ["fa-microphone", "Speech-to-Text", (bool)$p->permite_speech],
                    ["fa-eye", "Vision AI", (bool)$p->permite_vision_ai],
                    ["fa-store", "Marketplace de IA", (bool)$p->permite_marketplace],
                ];
                foreach ($features as [$icon, $label, $enabled]):
                ?>
                <div style="display:flex;align-items:center;gap:10px;font-size:.8rem;">
                    <i class="fa-solid <?= $icon ?>" style="width:14px;color:<?= $enabled ? 'var(--primary)' : 'var(--muted-2)' ?>;"></i>
                    <span style="color:<?= $enabled ? '#c8daea' : 'var(--muted-2)' ?>;<?= $enabled ? '' : 'text-decoration:line-through;' ?>">
                        <?= htmlspecialchars($label) ?>
                    </span>
                    <?php if ($enabled): ?>
                    <i class="fa-solid fa-check" style="margin-left:auto;color:var(--success);font-size:.65rem;"></i>
                    <?php else: ?>
                    <i class="fa-solid fa-xmark" style="margin-left:auto;color:var(--muted-2);font-size:.65rem;"></i>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="display:flex;gap:8px;">
                <span class="badge <?= $p->ativo ? 'badge-ativo' : 'badge-inativo' ?>" style="flex:1;justify-content:center;">
                    <?= $p->ativo ? 'Ativo' : 'Inativo' ?>
                </span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
