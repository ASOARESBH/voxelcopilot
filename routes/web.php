<?php
use App\Core\Router;

// ─── AUTENTICAÇÃO ─────────────────────────────────────────────────────────────
Router::get('/login',                'AuthController@showLogin');
Router::post('/login',               'AuthController@login');
Router::get('/logout',               'AuthController@logout');
Router::get('/cadastro',             'AuthController@showCadastro');
Router::post('/cadastro',            'AuthController@doCadastro');
Router::get('/cadastro/sucesso',     'AuthController@cadastroSucesso');
Router::get('/selecionar-empresa',   'AuthController@selectTenant');
Router::post('/selecionar-empresa',  'AuthController@doSelectTenant');

// ─── DASHBOARD DO MÉDICO ──────────────────────────────────────────────────────
Router::get('/dashboard',            'DashboardController@index');

// ─── WORKSPACE (Laudos) ───────────────────────────────────────────────────────
Router::get('/workspace',            'WorkspaceController@index');
Router::get('/workspace/novo',       'WorkspaceController@novo');
Router::post('/workspace/novo',      'WorkspaceController@criar');
// Rota removida — use /workspace/{id} diretamente
Router::get('/workspace/{id}',       'WorkspaceController@show');
Router::post('/workspace/{id}/salvar',  'WorkspaceController@salvar');
Router::post('/workspace/{id}/assinar', 'WorkspaceController@assinar');

// ─── FILA INTELIGENTE ─────────────────────────────────────────────────────────
Router::get('/fila',                 'FilaController@index');
Router::get('/fila/urgentes',        'FilaController@urgentes');
Router::post('/fila/sincronizar',    'FilaController@sincronizar');

// ─── PACIENTES ────────────────────────────────────────────────────────────────
Router::get('/pacientes',            'PacientesController@index');
Router::get('/pacientes/{id}',       'PacientesController@show');

// ─── TIMELINE CLÍNICA ─────────────────────────────────────────────────────────
Router::get('/timeline',             'TimelineController@index');
Router::get('/timeline/paciente/{id}','TimelineController@paciente');

// ─── COMPARATIVOS ─────────────────────────────────────────────────────────────
Router::get('/comparativos',         'ComparativosController@index');
Router::get('/comparativos/{id}',    'ComparativosController@show');
Router::post('/comparativos/criar',  'ComparativosController@criar');

// ─── VIEWER DICOM ─────────────────────────────────────────────────────────────
Router::get('/viewer',               'ViewerController@index');

// ─── COPILOT IA ───────────────────────────────────────────────────────────────
Router::get('/copilot',              'CopilotController@index');
Router::get('/copilot/historico',    'CopilotController@historico');

// ─── VISION AI ────────────────────────────────────────────────────────────────
Router::get('/vision',               'VisionAIController@index');
Router::get('/vision/{id}',          'VisionAIController@show');

// ─── SPEECH ───────────────────────────────────────────────────────────────────
Router::get('/speech',               'SpeechController@index');

// ─── TEMPLATES ────────────────────────────────────────────────────────────────
Router::get('/templates',            'TemplatesController@index');
Router::get('/templates/novo',       'TemplatesController@novo');
Router::post('/templates/criar',     'TemplatesController@criar');
Router::get('/templates/{id}/editar','TemplatesController@editar');
Router::post('/templates/{id}/atualizar','TemplatesController@atualizar');
Router::post('/templates/{id}/excluir','TemplatesController@excluir');

// ─── MÁSCARAS DE LAUDO ───────────────────────────────────────────────────────
Router::get('/templates/mascaras',                        'MascarasController@index');
Router::get('/templates/mascaras/{id}/preview',           'MascarasController@preview');
Router::post('/templates/mascaras/{id}/importar',         'MascarasController@importarDaBiblioteca');
Router::post('/templates/mascaras/importar-docx',         'MascarasController@importarDocx');
Router::post('/templates/mascaras/confirmar-importacao',  'MascarasController@confirmarImportacao');
Router::post('/templates/mascaras/seed',                  'MascarasController@seed');

// ─── PESQUISA CLÍNICA ─────────────────────────────────────────────────────────
Router::get('/pesquisa',             'PesquisaController@index');
Router::post('/pesquisa/buscar',     'PesquisaController@buscar');

// ─── ANALYTICS ────────────────────────────────────────────────────────────────
Router::get('/analytics',            'AnalyticsController@index');

// ─── MARKETPLACE ──────────────────────────────────────────────────────────────
Router::get('/marketplace',          'MarketplaceController@index');
Router::post('/marketplace/instalar','MarketplaceController@instalar');

// ─── INTEGRAÇÕES ──────────────────────────────────────────────────────────────
Router::get('/integracoes',          'IntegracoesController@index');
Router::post('/integracoes/salvar',  'IntegracoesController@salvar');

// ─── CONFIGURAÇÕES ────────────────────────────────────────────────────────────
Router::get('/configuracoes',        'ConfiguracoesController@index');
Router::post('/configuracoes/perfil','ConfiguracoesController@salvarPerfil');
Router::post('/configuracoes/ia',    'ConfiguracoesController@salvarIA');
Router::post('/configuracoes/senha', 'ConfiguracoesController@alterarSenha');

// ─── AUTORIZAÇÃO PACS ────────────────────────────────────────────────────────────────────────────────
Router::get( '/configuracoes/autorizacao',           'AutorizacaoPacsController@index');
Router::post('/configuracoes/autorizacao/cadastrar', 'AutorizacaoPacsController@cadastrar');
Router::post('/configuracoes/autorizacao/revogar',   'AutorizacaoPacsController@revogar');
Router::get( '/configuracoes/autorizacao/{id}',      'AutorizacaoPacsController@detalhe');
Router::post('/api/pacs/validar-token',              'AutorizacaoPacsController@apiValidarToken');

// ─── AUTOTEXTOS ───────────────────────────────────────────────────────────────
Router::get('/autotextos',           'AutotextosController@index');
Router::post('/autotextos',          'AutotextosController@criar');
Router::post('/autotextos/{id}/excluir','AutotextosController@excluir');

// ─── PACS ─────────────────────────────────────────────────────────────────────
Router::get('/pacs',                 'PacsController@index');
Router::post('/pacs',                'PacsController@salvar');

// ─── PLATFORM (SUPER ADMIN) ───────────────────────────────────────────────────
Router::get('/platform/dashboard',   'Platform\PlatformController@dashboard');
Router::get('/platform/medicos',                    'Platform\PlatformController@medicos');
Router::get('/platform/medicos/novo',               'Platform\PlatformController@medicoNovo');
Router::post('/platform/medicos/criar',             'Platform\PlatformController@medicoCreate');
Router::get('/platform/medicos/{id}',               'Platform\PlatformController@medicoShow');
Router::get('/platform/medicos/{id}/editar',        'Platform\PlatformController@medicoEditar');
Router::post('/platform/medicos/{id}/atualizar',    'Platform\PlatformController@medicoAtualizar');
Router::get('/platform/medicos/{id}/toggle-status', 'Platform\PlatformController@medicoToggleStatus');
Router::get('/platform/grupos',                     'Platform\PlatformController@grupos');
Router::get('/platform/grupos/novo',                'Platform\PlatformController@grupoNovo');
Router::post('/platform/grupos/criar',              'Platform\PlatformController@grupoCreate');
Router::get('/platform/grupos/{id}/editar',         'Platform\PlatformController@grupoEditar');
Router::post('/platform/grupos/{id}/atualizar',     'Platform\PlatformController@grupoAtualizar');
Router::get('/platform/planos',                     'Platform\PlatformController@planos');
Router::get('/platform/impersonar/{id}',            'Platform\PlatformController@impersonate');
Router::get('/platform/sair-impersonacao',          'Platform\PlatformController@exitImpersonate');

// ─── AI ROUTER ───────────────────────────────────────────────────────────────
Router::get('/ai-router',                        'AiRouterController@dashboard');

// Providers — Wizard de 5 etapas (substitui a view antiga)
Router::get('/ai-router/providers',              'ProviderWizardController@index');
Router::post('/ai-router/providers/salvar',      'ProviderWizardController@salvar');
Router::post('/ai-router/providers/excluir',     'ProviderWizardController@excluir');
Router::get('/ai-router/providers/wizard',       'ProviderWizardController@index');

// API REST do Wizard (AJAX)
Router::post('/api/ai/provider/test',            'ProviderWizardController@apiTest');
Router::post('/api/ai/provider/discover-models', 'ProviderWizardController@apiDiscoverModels');
Router::post('/api/ai/provider/validate',        'ProviderWizardController@apiValidate');
Router::get('/api/ai/provider/models',           'ProviderWizardController@apiModels');
Router::get('/api/ai/provider/capabilities',     'ProviderWizardController@apiCapabilities');
Router::get('/api/ai/provider/{id}',             'ProviderWizardController@apiGetProvider');
Router::get('/ai-router/modelos',                'AiRouterController@modelos');
Router::post('/ai-router/modelos/salvar',        'AiRouterController@salvarModelo');
Router::get('/ai-router/prompt-base',            'AiRouterController@promptBase');
Router::post('/ai-router/prompt-base/salvar',    'AiRouterController@salvarPromptBase');
Router::post('/ai-router/prompt-base/excluir',   'AiRouterController@excluirPromptBase');
Router::get('/ai-router/prompt-templates',       'AiRouterController@promptTemplates');
Router::post('/ai-router/prompt-templates/salvar',   'AiRouterController@salvarPromptTemplate');
Router::post('/ai-router/prompt-templates/duplicar', 'AiRouterController@duplicarPromptTemplate');
Router::get('/ai-router/rotas',                  'AiRouterController@rotas');
Router::post('/ai-router/rotas/salvar',          'AiRouterController@salvarRota');
Router::post('/ai-router/rotas/excluir',         'AiRouterController@excluirRota');
Router::get('/ai-router/historico',              'AiRouterController@historico');
Router::get('/ai-router/tokens',                 'AiRouterController@tokens');
Router::get('/ai-router/custos',                 'AiRouterController@custos');
Router::get('/ai-router/logs',                   'AiRouterController@logs');
Router::get('/ai-router/testes',                 'AiRouterController@testes');
Router::get('/ai-router/configuracoes',          'AiRouterController@configuracoes');
Router::post('/ai-router/configuracoes/salvar',  'AiRouterController@salvarConfiguracoes');

// ─── MEDICAL PROFILE ──────────────────────────────────────────────────────────
Router::get('/medical-profile',                       'MedicalProfileController@index');
Router::post('/medical-profile/salvar',               'MedicalProfileController@salvar');
Router::post('/medical-profile/autotexto/salvar',     'MedicalProfileController@salvarAutotexto');
Router::post('/medical-profile/autotexto/excluir',    'MedicalProfileController@excluirAutotexto');
Router::get('/api/medical-profile/autotextos',        'MedicalProfileController@getAutotextos');
Router::post('/api/medical-profile/uso-autotexto',    'MedicalProfileController@registrarUsoAutotexto');
Router::get('/api/medical-profile/perfil',            'MedicalProfileController@getPerfil');

// ─── API AI ROUTER (AJAX) ─────────────────────────────────────────────────────
Router::post('/api/ai/router',                        'AiRouterController@apiRouter');

// ─── API PACS (AJAX) ───────────────────────────────────────────────────────
Router::get('/api/pacs/buscar',           'PacsController@buscar');

// ─── API TEMPLATES (AJAX) ───────────────────────────────────────────────────
Router::get('/api/templates/{id}/corpo',  'TemplatesController@getCorpo');
Router::get('/api/mascaras/buscar',        'MascarasController@buscar');
Router::get('/api/mascaras/{id}/corpo',    'MascarasController@getCorpo');

// ─── API COPILOT (AJAX) ──────────────────────────────────────────────────────
Router::post('/api/copilot/chat',          'CopilotApiController@chat');
Router::post('/api/copilot/sugestao',      'CopilotApiController@sugestao');
Router::post('/api/copilot/report-engine', 'CopilotApiController@reportEngine');
Router::post('/api/vision/analisar',      'VisionAIController@analisar');
Router::post('/api/speech/transcrever',   'SpeechController@transcrever');
Router::post('/api/integracoes/testar',   'IntegracoesController@testar');
Router::post('/api/configuracoes/assinatura', 'ConfiguracoesController@salvarAssinatura');

// ─── REDIRECT RAIZ ────────────────────────────────────────────────────────────
Router::get('/', function() {
    if (\App\Core\Auth::check()) {
        $dest = \App\Core\Auth::isPlatformAdmin() ? '/platform/dashboard' : '/dashboard';
        header('Location: ' . $dest);
    } else {
        header('Location: /login');
    }
    exit;
});
