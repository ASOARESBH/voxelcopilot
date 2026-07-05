<?php
use App\Core\Router;

// ─── AUTENTICAÇÃO ─────────────────────────────────────────────────────────────
Router::get('/login',                'AuthController@showLogin');
Router::post('/login',               'AuthController@login');
Router::get('/logout',               'AuthController@logout');
Router::get('/cadastro',             'AuthController@showCadastro');
Router::post('/cadastro',            'AuthController@doCadastro');
Router::get('/selecionar-empresa',   'AuthController@selectTenant');
Router::post('/selecionar-empresa',  'AuthController@doSelectTenant');

// ─── DASHBOARD DO MÉDICO ──────────────────────────────────────────────────────
Router::get('/dashboard',            'DashboardController@index');

// ─── WORKSPACE (Laudos) ───────────────────────────────────────────────────────
Router::get('/workspace',            'WorkspaceController@index');
Router::get('/workspace/novo',       'WorkspaceController@novo');
Router::post('/workspace/novo',      'WorkspaceController@criar');
Router::get('/workspace/{id}',       'WorkspaceController@show');
Router::post('/workspace/{id}/salvar', 'WorkspaceController@salvar');
Router::post('/workspace/{id}/assinar', 'WorkspaceController@assinar');

// ─── TEMPLATES ────────────────────────────────────────────────────────────────
Router::get('/templates',            'TemplatesController@index');
Router::get('/templates/novo',       'TemplatesController@novo');
Router::post('/templates/novo',      'TemplatesController@criar');
Router::get('/templates/{id}/editar','TemplatesController@editar');
Router::post('/templates/{id}/editar','TemplatesController@atualizar');
Router::post('/templates/{id}/excluir','TemplatesController@excluir');

// ─── AUTOTEXTOS ───────────────────────────────────────────────────────────────
Router::get('/autotextos',           'AutotextosController@index');
Router::post('/autotextos',          'AutotextosController@criar');
Router::post('/autotextos/{id}/excluir','AutotextosController@excluir');

// ─── PERFIL DO MÉDICO ─────────────────────────────────────────────────────────
Router::get('/perfil',               'PerfilController@index');
Router::post('/perfil',              'PerfilController@atualizar');

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

// ─── API COPILOT (AJAX) ──────────────────────────────────────────────────────
Router::post('/api/copilot/chat',     'CopilotApiController@chat');
Router::post('/api/copilot/sugestao', 'CopilotApiController@sugestao');

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
