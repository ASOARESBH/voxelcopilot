<?php $this->layout('layout/copilot_header', ['title' => $title ?? 'Template', 'pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]); ?>

<?php
$isEdicao = !is_null($template);
$action   = $isEdicao ? '/templates/' . (is_array($template) ? $template['id'] : $template->id) . '/atualizar' : '/templates/criar';
$estrutura = $isEdicao ? json_decode(is_array($template) ? ($template['estrutura_json'] ?? '{}') : ($template->estrutura_json ?? '{}'), true) : ['indicacao'=>'','tecnica'=>'','achados'=>'','impressao'=>'','recomendacao'=>''];
$nomeAtual = $isEdicao ? (is_array($template) ? $template['nome'] : $template->nome) : ($old['nome'] ?? '');
$modAtual  = $isEdicao ? (is_array($template) ? $template['modalidade'] : $template->modalidade) : ($old['modalidade'] ?? '');
?>

<div class="page-content">

  <?php if (!empty($erro)): ?>
  <div class="alert alert-danger mb-4"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <form method="POST" action="<?= $action ?>">
    <div class="template-form-layout">

      <!-- Configurações -->
      <div class="card">
        <div class="card-header-row"><h3 class="card-title"><i class="fa-solid fa-gear"></i> Configurações</h3></div>
        <div class="form-group">
          <label class="form-label">Nome do Template <span class="required">*</span></label>
          <input type="text" name="nome" value="<?= htmlspecialchars($nomeAtual) ?>" class="form-control" placeholder="Ex: TC Tórax com Contraste — Padrão" required>
        </div>
        <div class="form-group">
          <label class="form-label">Modalidade <span class="required">*</span></label>
          <select name="modalidade" class="form-control" required>
            <option value="">Selecione...</option>
            <?php foreach ($modalidades as $m): ?>
            <option value="<?= $m ?>" <?= $modAtual === $m ? 'selected' : '' ?>><?= $m ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-check">
          <input type="checkbox" name="publico" id="publico" value="1" <?= ($isEdicao && (is_array($template) ? $template['publico'] : $template->publico)) ? 'checked' : '' ?>>
          <label for="publico">Compartilhar com outros médicos da clínica</label>
        </div>
      </div>

      <!-- Editor de Seções -->
      <div class="card">
        <div class="card-header-row">
          <h3 class="card-title"><i class="fa-solid fa-file-lines"></i> Estrutura do Laudo</h3>
          <button type="button" onclick="adicionarSecao()" class="btn btn-sm btn-outline">
            <i class="fa-solid fa-plus"></i> Adicionar Seção
          </button>
        </div>
        <div id="secoesList">
          <?php foreach ($estrutura as $chave => $conteudo): ?>
          <div class="secao-item" data-key="<?= htmlspecialchars($chave) ?>">
            <div class="secao-header">
              <input type="text" class="secao-nome-input" value="<?= htmlspecialchars(ucfirst($chave)) ?>" placeholder="Nome da seção">
              <button type="button" onclick="removerSecao(this)" class="btn-icon-danger" title="Remover seção">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
            <textarea class="form-control secao-conteudo" rows="4" placeholder="Conteúdo padrão da seção (pode ficar em branco)..."><?= htmlspecialchars($conteudo) ?></textarea>
          </div>
          <?php endforeach; ?>
        </div>
        <input type="hidden" name="estrutura" id="estruturaJson">
      </div>

    </div>

    <div class="form-footer">
      <a href="/templates" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Cancelar</a>
      <button type="submit" onclick="prepararEstrutura()" class="btn btn-primary">
        <i class="fa-solid fa-floppy-disk"></i> <?= $isEdicao ? 'Atualizar Template' : 'Criar Template' ?>
      </button>
    </div>
  </form>
</div>

<style>
.template-form-layout { display:grid; grid-template-columns:320px 1fr; gap:24px; margin-bottom:24px; }
.form-check { display:flex; align-items:center; gap:8px; margin-top:8px; }
.form-check input { width:16px; height:16px; accent-color:#1a56db; }
.form-check label { font-size:13px; color:#475569; cursor:pointer; }
.secao-item { border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-bottom:12px; background:#f8fafc; }
.secao-header { display:flex; gap:8px; align-items:center; margin-bottom:8px; }
.secao-nome-input { flex:1; border:1px solid #e2e8f0; border-radius:6px; padding:6px 10px; font-size:13px; font-weight:600; color:#1e293b; background:#fff; }
.btn-icon-danger { width:32px; height:32px; border:1px solid #fecaca; border-radius:6px; background:#fef2f2; color:#dc2626; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.secao-conteudo { font-size:13px; }
.form-footer { display:flex; justify-content:flex-end; gap:12px; padding-top:16px; border-top:1px solid #e2e8f0; }
.required { color:#dc2626; }
@media(max-width:768px) { .template-form-layout { grid-template-columns:1fr; } }
</style>

<script>
let secaoCount = <?= count($estrutura) ?>;

function adicionarSecao() {
  secaoCount++;
  const div = document.createElement('div');
  div.className = 'secao-item';
  div.dataset.key = 'secao_' + secaoCount;
  div.innerHTML = `
    <div class="secao-header">
      <input type="text" class="secao-nome-input" value="Nova Seção ${secaoCount}" placeholder="Nome da seção">
      <button type="button" onclick="removerSecao(this)" class="btn-icon-danger"><i class="fa-solid fa-trash"></i></button>
    </div>
    <textarea class="form-control secao-conteudo" rows="4" placeholder="Conteúdo padrão..."></textarea>
  `;
  document.getElementById('secoesList').appendChild(div);
}

function removerSecao(btn) {
  if (document.querySelectorAll('.secao-item').length <= 1) { alert('O template precisa ter ao menos uma seção.'); return; }
  btn.closest('.secao-item').remove();
}

function prepararEstrutura() {
  const estrutura = {};
  document.querySelectorAll('.secao-item').forEach(item => {
    const nome     = item.querySelector('.secao-nome-input').value.trim().toLowerCase().replace(/\s+/g,'_');
    const conteudo = item.querySelector('.secao-conteudo').value;
    if (nome) estrutura[nome] = conteudo;
  });
  document.getElementById('estruturaJson').value = JSON.stringify(estrutura);
}
</script>

<?php $this->layout('layout/copilot_footer'); ?>
