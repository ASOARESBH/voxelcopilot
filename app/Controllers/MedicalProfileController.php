<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Middlewares\AuthMiddleware;

class MedicalProfileController extends Controller
{
    private $db;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->db = Database::getInstance();
    }

    /**
     * Exibe o perfil de aprendizado de IA do médico
     */
    public function index(): void
    {
        $userId = Auth::id();

        // Busca ou cria o perfil do médico
        $perfil = $this->db->fetch(
            "SELECT * FROM cop_medico_perfil WHERE user_id = ? LIMIT 1",
            [$userId]
        );

        if (!$perfil) {
            // Cria perfil padrão
            $this->db->query(
                "INSERT INTO cop_medico_perfil (user_id, estilo_laudo, nivel_detalhe, linguagem_preferida, created_at, updated_at)
                 VALUES (?, 'formal', 'detalhado', 'pt-BR', NOW(), NOW())",
                [$userId]
            );
            $perfil = $this->db->fetch(
                "SELECT * FROM cop_medico_perfil WHERE user_id = ? LIMIT 1",
                [$userId]
            );
        }

        // Estatísticas de aprendizado
        $totalLaudos = $this->db->fetch(
            "SELECT COUNT(*) as total FROM cop_laudos WHERE user_id = ? AND status IN ('assinado','revisado')",
            [$userId]
        );

        $laudosUltimoMes = $this->db->fetch(
            "SELECT COUNT(*) as total FROM cop_laudos WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            [$userId]
        );

        // Modalidades mais usadas
        $modalidades = $this->db->fetchAll(
            "SELECT modalidade, COUNT(*) as total FROM cop_laudos WHERE user_id = ? AND modalidade IS NOT NULL GROUP BY modalidade ORDER BY total DESC LIMIT 8",
            [$userId]
        );

        // Autotextos do médico
        $autotextos = $this->db->fetchAll(
            "SELECT * FROM cop_autotextos WHERE user_id = ? ORDER BY uso_count DESC LIMIT 20",
            [$userId]
        );

        // Histórico de interações com IA
        $interacoesIA = $this->db->fetchAll(
            "SELECT tipo, COUNT(*) as total, AVG(tokens_total) as media_tokens
             FROM cop_ai_conversas
             WHERE user_id = ?
             GROUP BY tipo
             ORDER BY total DESC",
            [$userId]
        );

        // Vocabulário aprendido (frases frequentes)
        $vocabulario = [];
        if (!empty($perfil['vocabulario_aprendido'])) {
            $vocabulario = json_decode($perfil['vocabulario_aprendido'], true) ?: [];
        }

        // Templates mais usados
        $templatesMaisUsados = $this->db->fetchAll(
            "SELECT t.nome, t.modalidade, t.especialidade, COUNT(l.id) as uso_count
             FROM cop_templates t
             LEFT JOIN cop_laudos l ON l.template_id = t.id AND l.user_id = ?
             WHERE t.user_id = ?
             GROUP BY t.id
             ORDER BY uso_count DESC
             LIMIT 5",
            [$userId, $userId]
        );

        // Providers disponíveis para configuração
        $providers = $this->db->fetchAll(
            "SELECT id, nome, tipo, modelo_padrao, is_active FROM cop_ai_providers WHERE is_active = 1 ORDER BY nome ASC"
        );

        $this->render('medical_profile/index', [
            'title' => 'Meu Perfil de IA — Medical Profile',
            'perfil' => $perfil,
            'total_laudos' => $totalLaudos['total'] ?? 0,
            'laudos_ultimo_mes' => $laudosUltimoMes['total'] ?? 0,
            'modalidades' => $modalidades,
            'autotextos' => $autotextos,
            'interacoes_ia' => $interacoesIA,
            'vocabulario' => $vocabulario,
            'templates_mais_usados' => $templatesMaisUsados,
            'providers' => $providers,
            'csrf_token' => bin2hex(random_bytes(16)),
        ]);
    }

    /**
     * Salva as configurações do perfil de IA
     */
    public function salvar(): void
    {
        header('Content-Type: application/json');
        $userId = Auth::id();

        $estilo = $_POST['estilo_laudo'] ?? 'formal';
        $nivel = $_POST['nivel_detalhe'] ?? 'detalhado';
        $linguagem = $_POST['linguagem_preferida'] ?? 'pt-BR';
        $providerId = !empty($_POST['provider_id']) ? (int)$_POST['provider_id'] : null;
        $temperatura = isset($_POST['temperatura']) ? (float)$_POST['temperatura'] : 0.1;
        $maxTokens = isset($_POST['max_tokens']) ? (int)$_POST['max_tokens'] : 4000;
        $instrucoes = trim($_POST['instrucoes_personalizadas'] ?? '');
        $frasesConclusao = trim($_POST['frases_conclusao'] ?? '');

        // Verifica se perfil existe
        $existe = $this->db->fetch(
            "SELECT id FROM cop_medico_perfil WHERE user_id = ? LIMIT 1",
            [$userId]
        );

        if ($existe) {
            $this->db->query(
                "UPDATE cop_medico_perfil SET
                    estilo_laudo = ?,
                    nivel_detalhe = ?,
                    linguagem_preferida = ?,
                    provider_id = ?,
                    temperatura = ?,
                    max_tokens = ?,
                    instrucoes_personalizadas = ?,
                    frases_conclusao = ?,
                    updated_at = NOW()
                 WHERE user_id = ?",
                [$estilo, $nivel, $linguagem, $providerId, $temperatura, $maxTokens, $instrucoes, $frasesConclusao, $userId]
            );
        } else {
            $this->db->query(
                "INSERT INTO cop_medico_perfil (user_id, estilo_laudo, nivel_detalhe, linguagem_preferida, provider_id, temperatura, max_tokens, instrucoes_personalizadas, frases_conclusao, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [$userId, $estilo, $nivel, $linguagem, $providerId, $temperatura, $maxTokens, $instrucoes, $frasesConclusao]
            );
        }

        echo json_encode(['ok' => true, 'msg' => 'Perfil salvo com sucesso']);
    }

    /**
     * Adiciona um autotexto ao perfil
     */
    public function salvarAutotexto(): void
    {
        header('Content-Type: application/json');
        $userId = Auth::id();

        $atalho = trim($_POST['atalho'] ?? '');
        $texto = trim($_POST['texto'] ?? '');
        $categoria = trim($_POST['categoria'] ?? 'geral');

        if (empty($atalho) || empty($texto)) {
            echo json_encode(['ok' => false, 'erro' => 'Atalho e texto são obrigatórios']);
            return;
        }

        // Verifica se atalho já existe
        $existe = $this->db->fetch(
            "SELECT id FROM cop_autotextos WHERE user_id = ? AND atalho = ? LIMIT 1",
            [$userId, $atalho]
        );

        if ($existe) {
            $this->db->query(
                "UPDATE cop_autotextos SET texto = ?, categoria = ?, updated_at = NOW() WHERE id = ?",
                [$texto, $categoria, $existe['id']]
            );
        } else {
            $this->db->query(
                "INSERT INTO cop_autotextos (user_id, atalho, texto, categoria, uso_count, created_at, updated_at)
                 VALUES (?, ?, ?, ?, 0, NOW(), NOW())",
                [$userId, $atalho, $texto, $categoria]
            );
        }

        echo json_encode(['ok' => true, 'msg' => 'Autotexto salvo']);
    }

    /**
     * Exclui um autotexto
     */
    public function excluirAutotexto(): void
    {
        header('Content-Type: application/json');
        $userId = Auth::id();
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['ok' => false, 'erro' => 'ID inválido']);
            return;
        }

        $this->db->query(
            "DELETE FROM cop_autotextos WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );

        echo json_encode(['ok' => true]);
    }

    /**
     * Retorna autotextos do médico para uso no Workspace
     */
    public function getAutotextos(): void
    {
        header('Content-Type: application/json');
        $userId = Auth::id();
        $busca = trim($_GET['q'] ?? '');

        $sql = "SELECT id, atalho, texto, categoria FROM cop_autotextos WHERE user_id = ?";
        $params = [$userId];

        if ($busca) {
            $sql .= " AND (atalho LIKE ? OR texto LIKE ?)";
            $params[] = "%{$busca}%";
            $params[] = "%{$busca}%";
        }

        $sql .= " ORDER BY uso_count DESC LIMIT 50";
        $autotextos = $this->db->fetchAll($sql, $params);

        echo json_encode(['ok' => true, 'autotextos' => $autotextos]);
    }

    /**
     * Registra uso de autotexto (para aprendizado)
     */
    public function registrarUsoAutotexto(): void
    {
        header('Content-Type: application/json');
        $userId = Auth::id();
        $id = (int)($_POST['id'] ?? 0);

        if ($id) {
            $this->db->query(
                "UPDATE cop_autotextos SET uso_count = uso_count + 1, updated_at = NOW() WHERE id = ? AND user_id = ?",
                [$id, $userId]
            );
        }

        echo json_encode(['ok' => true]);
    }

    /**
     * Retorna o perfil de IA do médico para uso no AI Router
     */
    public function getPerfil(): void
    {
        header('Content-Type: application/json');
        $userId = Auth::id();

        $perfil = $this->db->fetch(
            "SELECT mp.*, p.nome as provider_nome, p.tipo as provider_tipo, p.modelo_padrao
             FROM cop_medico_perfil mp
             LEFT JOIN cop_ai_providers p ON p.id = mp.provider_id
             WHERE mp.user_id = ? LIMIT 1",
            [$userId]
        );

        echo json_encode(['ok' => true, 'perfil' => $perfil]);
    }
}
