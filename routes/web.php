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

// ─── AUTOTEXTOS ───────────────────────────────────────────────────────────────
Router::get('/autotextos',           'AutotextosController@index');
Router::post('/autotextos',          'AutotextosController@criar');
Router::post('/autotextos/{id}/excluir','AutotextosController@excluir');

// ─── PACS ─────────────────────────────────────────────────────────────────────
Router::get('/pacs',                 'PacsController@index');
Router::post('/pacs',                'PacsController@salvar');

// ─── PLATFORM (SUPER ADMIN) ───────────────────────────────────────────────────
Router::get('/platform/dashboard',   'Platform\PlatformController@dashboard');
Router::get('/platform/medicos',     'Platform\PlatformController@medicos');
Router::get('/platform/medicos/{id}','Platform\PlatformController@medicoShow');
Router::get('/platform/medicos/{id}/toggle-status', 'Platform\PlatformController@medicoToggleStatus');
Router::get('/platform/planos',      'Platform\PlatformController@planos');
Router::get('/platform/impersonar/{id}', 'Platform\PlatformController@impersonate');
Router::get('/platform/sair-impersonacao', 'Platform\PlatformController@exitImpersonate');

// ─── API PACS (AJAX) ───────────────────────────────────────────────────────
Router::get('/api/pacs/buscar',           'PacsController@buscar');

// ─── API TEMPLATES (AJAX) ───────────────────────────────────────────────────
Router::get('/api/templates/{id}/corpo',  'TemplatesController@getCorpo');

// ─── API COPILOT (AJAX) ──────────────────────────────────────────────────────
Router::post('/api/copilot/chat',         'CopilotApiController@chat');
Router::post('/api/copilot/sugestao',     'CopilotApiController@sugestao');
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
