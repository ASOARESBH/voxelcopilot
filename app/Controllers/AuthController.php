<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Logger;
use App\Services\MailService;

class AuthController extends Controller {

    // ─── LOGIN ───────────────────────────────────────────────────────────────

    public function showLogin(): void {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if (Auth::check()) {
            $this->redirect(Auth::isPlatformAdmin() ? '/platform/dashboard' : '/dashboard');
        }

        $error = match ($_GET['error'] ?? '') {
            'sem_acesso'     => 'Sua conta não possui acesso a nenhuma clínica.',
            'tenant_inativo' => 'A clínica associada à sua conta está inativa.',
            default          => null,
        };

        $this->view('auth/login', [
            'title'      => 'Entrar — VOXEL Copilot',
            'error'      => $error,
            'csrf_token' => $_SESSION['csrf_token'],
        ], 'auth');
    }

    public function login(): void {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $this->view('auth/login', [
                'title'      => 'Entrar — VOXEL Copilot',
                'error'      => 'Preencha todos os campos.',
                'csrf_token' => $_SESSION['csrf_token'],
            ], 'auth');
            return;
        }

        if (!Auth::login($email, $password)) {
            $this->view('auth/login', [
                'title'      => 'Entrar — VOXEL Copilot',
                'error'      => 'E-mail ou senha incorretos.',
                'csrf_token' => $_SESSION['csrf_token'],
            ], 'auth');
            return;
        }

        // Superadmin → painel da plataforma
        if (Auth::isPlatformAdmin()) {
            $this->redirect('/platform/dashboard');
        }

        // Médico: verifica tenants
        $tenants = Auth::userTenants();

        if (count($tenants) === 0) {
            Auth::logout();
            $this->redirect('/login?error=sem_acesso');
        } elseif (count($tenants) === 1) {
            $this->redirect('/dashboard');
        } else {
            $this->redirect('/selecionar-empresa');
        }
    }

    public function logout(): void {
        Auth::logout();
        $this->redirect('/login');
    }

    // ─── SELECIONAR EMPRESA ──────────────────────────────────────────────────

    public function selectTenant(): void {
        if (!Auth::check()) $this->redirect('/login');

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $tenants = Auth::userTenants();
        if (empty($tenants)) {
            Auth::logout();
            $this->redirect('/login?error=sem_acesso');
        }

        $this->view('auth/select_tenant', [
            'title'      => 'Selecionar Clínica — VOXEL Copilot',
            'tenants'    => $tenants,
            'csrf_token' => $_SESSION['csrf_token'],
        ], 'auth');
    }

    public function doSelectTenant(): void {
        if (!Auth::check()) $this->redirect('/login');

        $tenantId = (int) ($_POST['tenant_id'] ?? 0);
        $allowed  = array_column(Auth::userTenants(), 'tenant_id');

        if (!$tenantId || !in_array($tenantId, $allowed)) {
            $this->redirect('/selecionar-empresa');
        }

        Auth::setTenant($tenantId);
        $this->redirect('/dashboard');
    }

    // ─── CADASTRO DO MÉDICO ──────────────────────────────────────────────────

    public function showCadastro(): void {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $especialidades = $this->listaEspecialidades();

        $this->view('auth/cadastro', [
            'title'          => 'Primeiro Acesso — VOXEL Copilot',
            'csrf_token'     => $_SESSION['csrf_token'],
            'especialidades' => $especialidades,
        ], 'auth');
    }

    public function doCadastro(): void {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $especialidades = $this->listaEspecialidades();

        // Validação básica
        $nome   = trim($_POST['nome']   ?? '');
        $email  = trim($_POST['email']  ?? '');
        $crm    = trim($_POST['crm']    ?? '');
        $crmUf  = strtoupper(trim($_POST['crm_uf'] ?? ''));
        $espec  = $_POST['especialidades'] ?? [];
        $tel    = trim($_POST['telefone'] ?? '');
        $cep    = preg_replace('/\D/', '', $_POST['cep'] ?? '');
        $logr   = trim($_POST['logradouro']  ?? '');
        $num    = trim($_POST['numero']      ?? '');
        $comp   = trim($_POST['complemento'] ?? '');
        $bairro = trim($_POST['bairro']      ?? '');
        $cidade = trim($_POST['cidade']      ?? '');
        $estado = strtoupper(trim($_POST['estado'] ?? ''));

        $erros = [];
        if (!$nome)   $erros[] = 'Nome completo é obrigatório.';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
        if (!$crm)    $erros[] = 'CRM é obrigatório.';
        if (!$crmUf)  $erros[] = 'UF do CRM é obrigatória.';
        if (empty($espec)) $erros[] = 'Selecione ao menos uma especialidade.';
        if (!$cep || strlen($cep) !== 8) $erros[] = 'CEP inválido.';
        if (!$logr)   $erros[] = 'Logradouro é obrigatório.';
        if (!$cidade) $erros[] = 'Cidade é obrigatória.';
        if (!$estado) $erros[] = 'Estado é obrigatório.';

        if (!empty($erros)) {
            $this->view('auth/cadastro', [
                'title'          => 'Primeiro Acesso — VOXEL Copilot',
                'csrf_token'     => $_SESSION['csrf_token'],
                'especialidades' => $especialidades,
                'erros'          => $erros,
                'old'            => $_POST,
            ], 'auth');
            return;
        }

        $pdo = Database::getInstance();

        // Verifica e-mail duplicado
        $stmt = $pdo->prepare("SELECT id FROM cop_users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $this->view('auth/cadastro', [
                'title'          => 'Primeiro Acesso — VOXEL Copilot',
                'csrf_token'     => $_SESSION['csrf_token'],
                'especialidades' => $especialidades,
                'erros'          => ['Este e-mail já está cadastrado. Faça login ou recupere sua senha.'],
                'old'            => $_POST,
            ], 'auth');
            return;
        }

        // Gera senha temporária
        $senhaTemp = $this->gerarSenha();
        $hash      = password_hash($senhaTemp, PASSWORD_BCRYPT, ['cost' => 12]);
        $token     = bin2hex(random_bytes(32));

        // Insere médico
        $pdo->prepare("
            INSERT INTO cop_users
                (name, email, password, role, status, crm, crm_uf, especialidades,
                 telefone, cep, logradouro, numero, complemento, bairro, cidade, estado,
                 email_verificado, token_senha, token_expira_em, created_at, updated_at)
            VALUES
                (:name, :email, :password, 'medico', 'ativo', :crm, :crm_uf, :especialidades,
                 :telefone, :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :estado,
                 0, :token, DATE_ADD(NOW(), INTERVAL 48 HOUR), NOW(), NOW())
        ")->execute([
            'name'          => $nome,
            'email'         => $email,
            'password'      => $hash,
            'crm'           => $crm,
            'crm_uf'        => $crmUf,
            'especialidades'=> json_encode($espec, JSON_UNESCAPED_UNICODE),
            'telefone'      => $tel,
            'cep'           => $cep,
            'logradouro'    => $logr,
            'numero'        => $num,
            'complemento'   => $comp,
            'bairro'        => $bairro,
            'cidade'        => $cidade,
            'estado'        => $estado,
            'token'         => $token,
        ]);

        // Envia e-mail com a senha
        try {
            $html = MailService::templateBoasVindas($nome, $email, $senhaTemp);
            MailService::send($email, $nome, 'Bem-vindo ao VOXEL Copilot — Suas credenciais de acesso', $html);
        } catch (\Throwable $e) {
            Logger::error('Falha ao enviar e-mail de boas-vindas', ['email' => $email, 'error' => $e->getMessage()]);
        }

        $this->view('auth/cadastro_sucesso', [
            'title' => 'Cadastro Realizado — VOXEL Copilot',
            'email' => $email,
        ], 'auth');
    }

    // ─── HELPERS ─────────────────────────────────────────────────────────────

    private function gerarSenha(int $length = 12): string {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789!@#$';
        $senha = '';
        for ($i = 0; $i < $length; $i++) {
            $senha .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $senha;
    }

    private function listaEspecialidades(): array {
        return [
            'Radiologia e Diagnóstico por Imagem',
            'Tomografia Computadorizada',
            'Ressonância Magnética',
            'Ultrassonografia',
            'Mamografia e Senologia',
            'Radiologia Intervencionista',
            'Neurorradiologia',
            'Radiologia Musculoesquelética',
            'Radiologia Cardiovascular',
            'Radiologia Pediátrica',
            'Radiologia Torácica',
            'Radiologia Abdominal',
            'Medicina Nuclear',
            'PET-CT',
            'Densitometria Óssea',
            'Radiologia Odontológica',
            'Radiologia de Urgência e Emergência',
            'Radiologia Oncológica',
            'Radiologia Vascular',
            'Ecocardiografia',
            'Doppler Vascular',
            'Radiologia Gastrointestinal',
            'Radiologia Geniturinária',
            'Radiologia de Cabeça e Pescoço',
            'Radiologia de Coluna',
        ];
    }
}
