<?php
$isEdit = !empty($grupo);
$action = $isEdit ? "/platform/grupos/{$grupo->id}/atualizar" : '/platform/grupos/criar';

$erroMsg = '';
if ($erro === 'nome_obrigatorio') $erroMsg = 'O nome do grupo é obrigatório.';
elseif ($erro === 'duplicado')    $erroMsg = 'Já existe um grupo com este nome.';

$icones = [
    'fa-user-doctor'  => 'Médico',
    'fa-stethoscope'  => 'Estetoscópio',
    'fa-x-ray'        => 'Raio-X',
    'fa-heart-pulse'  => 'Cardiologia',
    'fa-brain'        => 'Neurologia',
    'fa-ribbon'       => 'Oncologia',
    'fa-bone'         => 'Ortopedia',
    'fa-baby'         => 'Pediatria',
    'fa-venus'        => 'Ginecologia',
    'fa-droplet'      => 'Urologia',
    'fa-lungs'        => 'Pneumologia',
    'fa-flask'        => 'Endocrinologia',
    'fa-atom'         => 'Medicina Nuclear',
    'fa-wave-square'  => 'Ultrassom',
    'fa-graduation-cap'=> 'Residentes',
    'fa-hospital'     => 'Hospital',
    'fa-microscope'   => 'Laboratório',
    'fa-pills'        => 'Farmacologia',
    'fa-eye'          => 'Oftalmologia',
    'fa-ear-deaf'     => 'Otorrino',
];

$coresPreset = [
    '#1a56db','#16a34a','#dc2626','#7c3aed','#ea580c',
    '#0891b2','#0d9488','#db2777','#2563eb','#ca8a04',
    '#9333ea','#b45309','#0f766e','#64748b','#374151',
];

function gv($grupo, string $campo, $default = ''): string {
    if (!$grupo) return htmlspecialchars((string)$default);
    return htmlspecialchars((string)($grupo->$campo ?? $default));
}
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><?= $isEdit ? 'Editar Grupo' : 'Novo Grupo' ?></h1>
        <p><?= $isEdit ? 'Atualizar dados do grupo de médicos' : 'Criar novo grupo de médicos' ?></p>
    </div>
    <div class="page-header-actions">
        <a href="/platform/grupos" class="btn btn-ghost">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<?php if ($erroMsg): ?>
<div class="alert alert-danger" style="margin-bottom:18px;">
    <i class="fa-solid fa-circle-exclamation"></i> <?= $erroMsg ?>
</div>
<?php endif; ?>

<div class="card" style="max-width:680px;">
    <div class="card-header">
        <span><i class="fa-solid fa-layer-group" style="color:var(--blue-600);margin-right:7px;"></i>Dados do Grupo</span>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= $action ?>">

            <div style="margin-bottom:18px;">
                <label class="form-label">Nome do grupo <span style="color:var(--danger);">*</span></label>
                <input type="text" name="nome" class="form-control"
                    value="<?= gv($grupo, 'nome') ?>"
                    placeholder="Ex: Radiologistas" required>
            </div>

            <div style="margin-bottom:18px;">
                <label class="form-label">Descrição</label>
                <input type="text" name="descricao" class="form-control"
                    value="<?= gv($grupo, 'descricao') ?>"
                    placeholder="Breve descrição do grupo">
            </div>

            <div style="margin-bottom:18px;">
                <label class="form-label">Cor do grupo</label>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px;">
                    <?php foreach ($coresPreset as $c): ?>
                    <button type="button" onclick="selecionarCor('<?= $c ?>')"
                        style="width:28px;height:28px;border-radius:50%;background:<?= $c ?>;border:2px solid transparent;cursor:pointer;transition:transform .15s;"
                        title="<?= $c ?>"
                        id="cor-<?= ltrim($c, '#') ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <input type="color" name="cor" id="corInput"
                        value="<?= gv($grupo, 'cor', '#1a56db') ?>"
                        style="width:40px;height:36px;border:1px solid var(--border);border-radius:7px;cursor:pointer;padding:2px;">
                    <span id="corHex" style="font-size:.82rem;color:var(--muted);font-family:monospace;"><?= gv($grupo, 'cor', '#1a56db') ?></span>
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label class="form-label">Ícone</label>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(80px,1fr));gap:8px;">
                    <?php foreach ($icones as $cls => $label): ?>
                    <label style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:10px 6px;border:2px solid var(--border);border-radius:8px;cursor:pointer;font-size:.72rem;color:var(--text-2);text-align:center;transition:border-color .15s;"
                        id="icone-label-<?= str_replace('-','_',$cls) ?>">
                        <input type="radio" name="icone" value="<?= $cls ?>"
                            <?= gv($grupo, 'icone', 'fa-user-doctor') === $cls ? 'checked' : '' ?>
                            onchange="marcarIcone('<?= $cls ?>')"
                            style="display:none;">
                        <i class="fa-solid <?= $cls ?>" style="font-size:1.2rem;color:var(--blue-600);"></i>
                        <?= $label ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($isEdit): ?>
            <div style="margin-bottom:18px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.85rem;color:var(--text-1);">
                    <input type="checkbox" name="ativo" value="1"
                        <?= ($grupo->ativo ?? 1) ? 'checked' : '' ?>
                        style="accent-color:var(--blue-600);width:16px;height:16px;">
                    Grupo ativo (visível na listagem de médicos)
                </label>
            </div>
            <?php endif; ?>

            <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:24px;">
                <a href="/platform/grupos" class="btn btn-ghost">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> <?= $isEdit ? 'Salvar Alterações' : 'Criar Grupo' ?>
                </button>
            </div>

        </form>
    </div>
</div>

<script>
const corInput = document.getElementById('corInput');
const corHex   = document.getElementById('corHex');

corInput.addEventListener('input', function() {
    corHex.textContent = this.value;
});

function selecionarCor(hex) {
    corInput.value = hex;
    corHex.textContent = hex;
}

function marcarIcone(cls) {
    document.querySelectorAll('[id^="icone-label-"]').forEach(el => {
        el.style.borderColor = 'var(--border)';
        el.style.background  = '';
    });
    const key = 'icone-label-' + cls.replace(/-/g, '_');
    const el  = document.getElementById(key);
    if (el) {
        el.style.borderColor = 'var(--blue-600)';
        el.style.background  = 'var(--blue-50)';
    }
}

// Marca o ícone já selecionado ao carregar
document.addEventListener('DOMContentLoaded', function() {
    const checked = document.querySelector('input[name="icone"]:checked');
    if (checked) marcarIcone(checked.value);
});
</script>
