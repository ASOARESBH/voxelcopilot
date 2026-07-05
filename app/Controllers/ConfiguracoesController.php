<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Middlewares\AuthMiddleware;

class ConfiguracoesController extends Controller {

    public function index(): void {
        AuthMiddleware::handle();

        $pdo    = Database::getInstance();
        $user   = Auth::user();
        $userId = Auth::userId();

        $this->view('configuracoes/index', [
            'title'        => 'Configurações — VOXEL Copilot',
            'pageTitle'    => 'Configurações',
            'pageSubtitle' => 'Perfil, preferências e configurações de IA',
            'user'         => $user,
        ]);
    }

    public function salvarPerfil(): void {
        AuthMiddleware::handle();

        $pdo    = Database::getInstance();
        $userId = Auth::userId();

        $nome     = trim($_POST['nome']     ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $bio      = trim($_POST['bio']      ?? '');

        if (!$nome) {
            header('Location: /configuracoes?erro=nome');
            exit;
        }

        $pdo->prepare("UPDATE cop_users SET name=:nome, telefone=:tel, updated_at=NOW() WHERE id=:id")
            ->execute(['nome'=>$nome, 'tel'=>$telefone, 'id'=>$userId]);

        // Atualiza sessão
        $_SESSION['user']->name = $nome;

        header('Location: /configuracoes?sucesso=perfil');
        exit;
    }

    public function salvarSenha(): void {
        AuthMiddleware::handle();

        $pdo    = Database::getInstance();
        $userId = Auth::userId();

        $senhaAtual = $_POST['senha_atual']    ?? '';
        $novaSenha  = $_POST['nova_senha']     ?? '';
        $confirmar  = $_POST['confirmar_senha']?? '';

        // Busca hash atual
        $stmt = $pdo->prepare("SELECT password FROM cop_users WHERE id=:id LIMIT 1");
        $stmt->execute(['id'=>$userId]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($senhaAtual, $row->password)) {
            header('Location: /configuracoes?erro=senha_atual&tab=seguranca');
            exit;
        }

        if (strlen($novaSenha) < 8) {
            header('Location: /configuracoes?erro=senha_curta&tab=seguranca');
            exit;
        }

        if ($novaSenha !== $confirmar) {
            header('Location: /configuracoes?erro=senha_diferente&tab=seguranca');
            exit;
        }

        $hash = password_hash($novaSenha, PASSWORD_BCRYPT, ['cost'=>12]);
        $pdo->prepare("UPDATE cop_users SET password=:pwd, updated_at=NOW() WHERE id=:id")
            ->execute(['pwd'=>$hash, 'id'=>$userId]);

        header('Location: /configuracoes?sucesso=senha&tab=seguranca');
        exit;
    }

    public function salvarIA(): void {
        AuthMiddleware::handle();

        $pdo      = Database::getInstance();
        $userId   = Auth::userId();
        $tenantId = Auth::tenantId() ?? 0;

        $estilo    = $_POST['estilo_conclusao'] ?? 'normal';
        $vocab     = $_POST['vocabulario']      ?? '{}';
        $frases    = $_POST['frases']           ?? '[]';

        // Upsert no perfil de IA
        $stmt = $pdo->prepare("SELECT id FROM cop_medico_perfil WHERE user_id=:uid AND tenant_id=:tid LIMIT 1");
        $stmt->execute(['uid'=>$userId, 'tid'=>$tenantId]);
        $existe = $stmt->fetch();

        if ($existe) {
            $pdo->prepare("UPDATE cop_medico_perfil SET estilo_conclusao=:estilo, vocabulario_json=:vocab, frases_favoritas_json=:frases, updated_at=NOW() WHERE user_id=:uid AND tenant_id=:tid")
                ->execute(['estilo'=>$estilo,'vocab'=>$vocab,'frases'=>$frases,'uid'=>$userId,'tid'=>$tenantId]);
        } else {
            $pdo->prepare("INSERT INTO cop_medico_perfil (user_id,tenant_id,estilo_conclusao,vocabulario_json,frases_favoritas_json,total_laudos,total_correcoes) VALUES (:uid,:tid,:estilo,:vocab,:frases,0,0)")
                ->execute(['uid'=>$userId,'tid'=>$tenantId,'estilo'=>$estilo,'vocab'=>$vocab,'frases'=>$frases]);
        }

        header('Location: /configuracoes?sucesso=ia&tab=ia');
        exit;
    }
}
