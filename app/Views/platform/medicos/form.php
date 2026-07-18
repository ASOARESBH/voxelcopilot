<?php
$isEdit   = !empty($medico);
$action   = $isEdit ? "/platform/medicos/{$medico->id}/atualizar" : '/platform/medicos/criar';
$especArr = $isEdit ? (json_decode($medico->especialidades ?? '[]', true) ?? []) : [];

$especialidadesOpcoes = [
    'Radiologia e Diagnóstico por Imagem',
    'Tomografia Computadorizada',
    'Ressonância Magnética',
    'Medicina Nuclear',
    'Ultrassonografia',
    'PET-CT',
    'Radiologia Intervencionista',
    'Mamografia',
    'Densitometria Óssea',
    'Clínica Médica',
    'Clínico Geral',
    'Cardiologia',
    'Neurologia',
    'Oncologia',
    'Ortopedia e Traumatologia',
    'Pediatria',
    'Ginecologia e Obstetrícia',
    'Urologia',
    'Gastroenterologia',
    'Pneumologia',
    'Endocrinologia',
    'Nefrologia',
    'Hematologia',
    'Infectologia',
    'Dermatologia',
    'Oftalmologia',
    'Otorrinolaringologia',
    'Reumatologia',
    'Psiquiatria',
    'Cirurgia Geral',
    'Cirurgia Cardiovascular',
    'Cirurgia Torácica',
    'Neurocirurgia',
    'Anestesiologia',
    'Medicina de Emergência',
    'Medicina Intensiva',
    'Medicina do Trabalho',
    'Medicina Esportiva',
    'Geriatria',
    'Medicina de Família e Comunidade',
];

$ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];

$erroMsg = '';
if ($erro === 'campos_obrigatorios') $erroMsg = 'Nome e e-mail são obrigatórios.';
elseif ($erro === 'email_duplicado')  $erroMsg = 'Este e-mail já está cadastrado no sistema.';
elseif ($erro === 'db_error')         $erroMsg = 'Erro ao salvar no banco de dados. Verifique os logs.';

function fv($medico, string $campo, $default = ''): string {
    if (!$medico) return htmlspecialchars((string)$default);
    $v = $medico->$campo ?? $default;
    return htmlspecialchars((string)$v);
}
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><?= $isEdit ? 'Editar Médico' : 'Novo Médico' ?></h1>
        <p><?= $isEdit ? 'Atualizar dados do cadastro médico' : 'Cadastrar novo médico na plataforma' ?></p>
    </div>
    <div class="page-header-actions">
        <a href="/platform/medicos" class="btn btn-ghost">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<?php if ($erroMsg): ?>
<div class="alert alert-danger" style="margin-bottom:18px;">
    <i class="fa-solid fa-circle-exclamation"></i> <?= $erroMsg ?>
</div>
<?php endif; ?>

<form method="POST" action="<?= $action ?>" id="formMedico">

<!-- ── DADOS PESSOAIS ─────────────────────────────────────────────────────── -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <span><i class="fa-solid fa-user" style="color:var(--blue-600);margin-right:7px;"></i>Dados Pessoais</span>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">

            <div>
                <label class="form-label">Nome completo <span style="color:var(--danger);">*</span></label>
                <input type="text" name="name" class="form-control"
                    value="<?= fv($medico, 'name') ?>"
                    placeholder="Dr. Nome Completo" required>
            </div>

            <div>
                <label class="form-label">E-mail <span style="color:var(--danger);">*</span></label>
                <input type="email" name="email" class="form-control"
                    value="<?= fv($medico, 'email') ?>"
                    placeholder="medico@exemplo.com.br" required>
            </div>

            <div>
                <label class="form-label">Senha <?= $isEdit ? '(deixe em branco para não alterar)' : '<span style="color:var(--danger);">*</span>' ?></label>
                <input type="password" name="password" class="form-control"
                    placeholder="<?= $isEdit ? 'Nova senha (opcional)' : 'Mínimo 8 caracteres' ?>"
                    <?= $isEdit ? '' : 'required' ?>>
            </div>

            <div>
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" class="form-control"
                    value="<?= fv($medico, 'telefone') ?>"
                    placeholder="(31) 99999-0000">
            </div>

            <div>
                <label class="form-label">CRM</label>
                <input type="text" name="crm" class="form-control"
                    value="<?= fv($medico, 'crm') ?>"
                    placeholder="12345">
            </div>

            <div>
                <label class="form-label">UF do CRM</label>
                <select name="crm_uf" class="form-select">
                    <option value="">Selecione</option>
                    <?php foreach ($ufs as $uf): ?>
                    <option value="<?= $uf ?>" <?= fv($medico, 'crm_uf') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="ativo"    <?= fv($medico, 'status', 'ativo') === 'ativo'    ? 'selected' : '' ?>>Ativo</option>
                    <option value="pendente" <?= fv($medico, 'status', 'ativo') === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="inativo"  <?= fv($medico, 'status', 'ativo') === 'inativo'  ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>

            <div>
                <label class="form-label">Grupo</label>
                <select name="grupo_id" class="form-select">
                    <option value="">Sem grupo</option>
                    <?php foreach ($grupos as $g): ?>
                    <option value="<?= (int)$g->id ?>"
                        <?= ($isEdit && (int)($medico->grupo_id ?? 0) === (int)$g->id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g->nome) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>
    </div>
</div>

<!-- ── ESPECIALIDADES ─────────────────────────────────────────────────────── -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <span><i class="fa-solid fa-stethoscope" style="color:var(--blue-600);margin-right:7px;"></i>Especialidades</span>
    </div>
    <div class="card-body">
        <label class="form-label" style="margin-bottom:12px;">Selecione as especialidades do médico</label>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:8px;">
            <?php foreach ($especialidadesOpcoes as $esp): ?>
            <label style="display:flex;align-items:center;gap:8px;padding:7px 10px;border:1px solid var(--border);border-radius:7px;cursor:pointer;font-size:.82rem;color:var(--text-1);background:var(--surface);">
                <input type="checkbox" name="especialidades[]" value="<?= htmlspecialchars($esp) ?>"
                    <?= in_array($esp, $especArr) ? 'checked' : '' ?>
                    style="accent-color:var(--blue-600);width:15px;height:15px;">
                <?= htmlspecialchars($esp) ?>
            </label>
            <?php endforeach; ?>
        </div>
        <div style="margin-top:14px;">
            <label class="form-label">Outras especialidades (separadas por vírgula)</label>
            <input type="text" name="especialidades_outras" class="form-control"
                placeholder="Ex: Medicina Hiperbárica, Acupuntura..."
                value="">
        </div>
    </div>
</div>

<!-- ── ENDEREÇO ───────────────────────────────────────────────────────────── -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <span><i class="fa-solid fa-location-dot" style="color:var(--blue-600);margin-right:7px;"></i>Endereço</span>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">

            <div>
                <label class="form-label">CEP</label>
                <input type="text" name="cep" id="cep" class="form-control"
                    value="<?= fv($medico, 'cep') ?>"
                    placeholder="00000-000" maxlength="9"
                    oninput="buscarCep(this.value)">
            </div>

            <div>
                <label class="form-label">Logradouro</label>
                <input type="text" name="logradouro" id="logradouro" class="form-control"
                    value="<?= fv($medico, 'logradouro') ?>"
                    placeholder="Av. Afonso Pena">
            </div>

            <div>
                <label class="form-label">Número</label>
                <input type="text" name="numero" class="form-control"
                    value="<?= fv($medico, 'numero') ?>"
                    placeholder="1234">
            </div>

            <div>
                <label class="form-label">Complemento</label>
                <input type="text" name="complemento" class="form-control"
                    value="<?= fv($medico, 'complemento') ?>"
                    placeholder="Sala 501">
            </div>

            <div>
                <label class="form-label">Bairro</label>
                <input type="text" name="bairro" id="bairro" class="form-control"
                    value="<?= fv($medico, 'bairro') ?>"
                    placeholder="Centro">
            </div>

            <div>
                <label class="form-label">Cidade</label>
                <input type="text" name="cidade" id="cidade" class="form-control"
                    value="<?= fv($medico, 'cidade') ?>"
                    placeholder="Belo Horizonte">
            </div>

            <div>
                <label class="form-label">Estado (UF)</label>
                <select name="estado" id="estado" class="form-select">
                    <option value="">Selecione</option>
                    <?php foreach ($ufs as $uf): ?>
                    <option value="<?= $uf ?>" <?= fv($medico, 'estado') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>
    </div>
</div>

<!-- ── CONFIGURAÇÕES DE IA ────────────────────────────────────────────────── -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-header">
        <span><i class="fa-solid fa-robot" style="color:var(--blue-600);margin-right:7px;"></i>Configurações de IA</span>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px;">

            <div>
                <label class="form-label">Modelo padrão</label>
                <select name="ia_modelo" class="form-select">
                    <option value="gpt-4o"           <?= fv($medico, 'ia_modelo', 'gpt-4o') === 'gpt-4o'           ? 'selected' : '' ?>>GPT-4o</option>
                    <option value="gpt-4o-mini"      <?= fv($medico, 'ia_modelo', 'gpt-4o') === 'gpt-4o-mini'      ? 'selected' : '' ?>>GPT-4o Mini</option>
                    <option value="gpt-4-turbo"      <?= fv($medico, 'ia_modelo', 'gpt-4o') === 'gpt-4-turbo'      ? 'selected' : '' ?>>GPT-4 Turbo</option>
                    <option value="claude-3-5-sonnet" <?= fv($medico, 'ia_modelo', 'gpt-4o') === 'claude-3-5-sonnet'? 'selected' : '' ?>>Claude 3.5 Sonnet</option>
                    <option value="gemini-1.5-pro"   <?= fv($medico, 'ia_modelo', 'gpt-4o') === 'gemini-1.5-pro'   ? 'selected' : '' ?>>Gemini 1.5 Pro</option>
                </select>
            </div>

            <div>
                <label class="form-label">Temperatura <span id="tempVal" style="color:var(--blue-600);font-weight:600;"><?= fv($medico, 'ia_temperatura', '0.30') ?></span></label>
                <input type="range" name="ia_temperatura" min="0" max="1" step="0.05"
                    value="<?= fv($medico, 'ia_temperatura', '0.30') ?>"
                    oninput="document.getElementById('tempVal').textContent=this.value"
                    style="width:100%;accent-color:var(--blue-600);">
            </div>

            <div>
                <label class="form-label">Estilo de escrita</label>
                <select name="ia_estilo" class="form-select">
                    <option value="formal"    <?= fv($medico, 'ia_estilo', 'formal') === 'formal'    ? 'selected' : '' ?>>Formal</option>
                    <option value="tecnico"   <?= fv($medico, 'ia_estilo', 'formal') === 'tecnico'   ? 'selected' : '' ?>>Técnico</option>
                    <option value="didatico"  <?= fv($medico, 'ia_estilo', 'formal') === 'didatico'  ? 'selected' : '' ?>>Didático</option>
                    <option value="conciso"   <?= fv($medico, 'ia_estilo', 'formal') === 'conciso'   ? 'selected' : '' ?>>Conciso</option>
                </select>
            </div>

        </div>
    </div>
</div>

<!-- ── BOTÕES ─────────────────────────────────────────────────────────────── -->
<div style="display:flex;gap:12px;justify-content:flex-end;margin-bottom:40px;">
    <a href="/platform/medicos" class="btn btn-ghost">
        <i class="fa-solid fa-xmark"></i> Cancelar
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-floppy-disk"></i> <?= $isEdit ? 'Salvar Alterações' : 'Cadastrar Médico' ?>
    </button>
</div>

</form>

<script>
// Busca CEP via ViaCEP
function buscarCep(cep) {
    cep = cep.replace(/\D/g, '');
    if (cep.length !== 8) return;
    fetch('https://viacep.com.br/ws/' + cep + '/json/')
        .then(r => r.json())
        .then(d => {
            if (d.erro) return;
            document.getElementById('logradouro').value = d.logradouro || '';
            document.getElementById('bairro').value     = d.bairro     || '';
            document.getElementById('cidade').value     = d.localidade || '';
            const sel = document.getElementById('estado');
            for (let i = 0; i < sel.options.length; i++) {
                if (sel.options[i].value === d.uf) { sel.selectedIndex = i; break; }
            }
        })
        .catch(() => {});
}

// Formata CEP ao digitar
document.getElementById('cep').addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '');
    if (v.length > 5) v = v.slice(0,5) + '-' + v.slice(5,8);
    this.value = v;
});
</script>
