<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Middlewares\AuthMiddleware;

class TemplatesController extends Controller {

    public function index(): void {
        AuthMiddleware::handle();

        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId();

        $busca      = trim($_GET['busca']      ?? '');
        $modalidade = $_GET['modalidade'] ?? 'todas';

        if ($tenantId) {
            $sql = "SELECT * FROM cop_templates WHERE tenant_id = :tid AND ativo = 1";
            $params = ['tid' => $tenantId];
            if ($busca) { $sql .= " AND (nome LIKE :busca OR modalidade LIKE :busca2)"; $params['busca'] = "%$busca%"; $params['busca2'] = "%$busca%"; }
            if ($modalidade !== 'todas') { $sql .= " AND modalidade = :mod"; $params['mod'] = $modalidade; }
            $sql .= " ORDER BY uso_count DESC, nome ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $templates = $stmt->fetchAll();
        } else {
            $templates = $this->getTemplatesDefault();
        }

        $this->view('templates/index', [
            'title'        => 'Templates — VOXEL Copilot',
            'pageTitle'    => 'Templates de Laudo',
            'pageSubtitle' => 'Máscaras inteligentes personalizadas',
            'templates'    => $templates,
            'busca'        => $busca,
            'modalidade'   => $modalidade,
        ]);
    }

    public function novo(): void {
        AuthMiddleware::handle();
        $this->view('templates/form', [
            'title'        => 'Novo Template — VOXEL Copilot',
            'pageTitle'    => 'Novo Template',
            'pageSubtitle' => 'Criar máscara de laudo personalizada',
            'template'     => null,
            'modalidades'  => $this->getModalidades(),
        ]);
    }

    public function criar(): void {
        AuthMiddleware::handle();

        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId() ?? 0;

                $nome       = trim($_POST['nome']       ?? '');
        $modalidade = trim($_POST['modalidade'] ?? '');
        $estrutura  = $_POST['estrutura']       ?? '{}';
        $publico    = isset($_POST['publico']) ? 1 : 0;
        $dicomStudy = trim($_POST['dicom_study_description'] ?? '');

        if (!$nome || !$modalidade) {
            $this->view('templates/form', [
                'title'       => 'Novo Template — VOXEL Copilot',
                'pageTitle'   => 'Novo Template',
                'pageSubtitle'=> 'Criar máscara de laudo personalizada',
                'template'    => null,
                'modalidades' => $this->getModalidades(),
                'erro'        => 'Nome e modalidade são obrigatórios.',
                'old'         => $_POST,
            ]);
            return;
        }
        $corpo = trim($_POST['corpo'] ?? $_POST['estrutura'] ?? '');

        $pdo->prepare("
            INSERT INTO cop_templates (tenant_id, user_id, nome, modalidade, dicom_study_description, corpo, ativo, uso_count, created_at, updated_at)
            VALUES (:tid, :uid, :nome, :mod, :dicom, :corpo, 1, 0, NOW(), NOW())
        ")->execute([
            'tid'   => $tenantId,
            'uid'   => $medicoId,
            'nome'  => $nome,
            'mod'   => $modalidade,
            'dicom' => $dicomStudy ?: null,
            'corpo' => $corpo,
        ]);

        header('Location: /templates?sucesso=criado');
        exit;
    }

    public function editar(int $id): void {
        AuthMiddleware::handle();

        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId();

        if ($tenantId) {
            $stmt = $pdo->prepare("SELECT * FROM cop_templates WHERE id = :id AND tenant_id = :tid AND ativo = 1 LIMIT 1");
            $stmt->execute(['id' => $id, 'tid' => $tenantId]);
            $template = $stmt->fetch();
        } else {
            $stmt = $pdo->prepare("SELECT * FROM cop_templates WHERE id = :id AND user_id = :uid AND ativo = 1 LIMIT 1");
            $stmt->execute(['id' => $id, 'uid' => $medicoId]);
            $template = $stmt->fetch();
        }

        if (!$template) { header('Location: /templates'); exit; }

        $this->view('templates/form', [
            'title'        => 'Editar Template — VOXEL Copilot',
            'pageTitle'    => 'Editar Template',
            'pageSubtitle' => 'Atualizar máscara de laudo',
            'template'     => $template,
            'modalidades'  => $this->getModalidades(),
        ]);
    }

    public function atualizar(int $id): void {
        AuthMiddleware::handle();

        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId() ?? 0;

        $nome       = trim($_POST['nome']       ?? '');
        $modalidade = trim($_POST['modalidade'] ?? '');
        $estrutura  = $_POST['estrutura']       ?? '{}';
        $publico    = isset($_POST['publico']) ? 1 : 0;
        $dicomStudy = trim($_POST['dicom_study_description'] ?? '');

        if (!$nome || !$modalidade) {
            header('Location: /templates/' . $id . '/editar?erro=campos');
            exit;
        }

        $pdo->prepare("
            UPDATE cop_templates
            SET nome=:nome, modalidade=:mod, dicom_study_description=:dicom,
                estrutura_json=:estrutura, publico=:publico, updated_at=NOW()
            WHERE id=:id AND tenant_id=:tid
        ")->execute([
            'nome'     => $nome,
            'mod'      => $modalidade,
            'dicom'    => $dicomStudy ?: null,
            'estrutura'=> $estrutura,
            'publico'  => $publico,
            'id'       => $id,
            'tid'      => $tenantId,
        ]);

        header('Location: /templates?sucesso=atualizado');
        exit;
    }

    public function excluir(int $id): void {
        AuthMiddleware::handle();

        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId() ?? 0;

        $pdo->prepare("UPDATE cop_templates SET ativo=0, updated_at=NOW() WHERE id=:id AND tenant_id=:tid AND medico_id=:mid")
            ->execute(['id' => $id, 'tid' => $tenantId, 'mid' => $medicoId]);

        header('Location: /templates?sucesso=excluido');
        exit;
    }

    // API: retorna apenas o corpo do template (AJAX para workspace)
    public function getCorpo(int $id): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId();

        if ($tenantId) {
            $stmt = $pdo->prepare("SELECT id, nome, modalidade, corpo FROM cop_templates WHERE id = :id AND tenant_id = :tid AND ativo = 1 LIMIT 1");
            $stmt->execute(['id' => $id, 'tid' => $tenantId]);
        } else {
            $stmt = $pdo->prepare("SELECT id, nome, modalidade, corpo FROM cop_templates WHERE id = :id AND user_id = :uid AND ativo = 1 LIMIT 1");
            $stmt->execute(['id' => $id, 'uid' => $medicoId]);
        }
        $template = $stmt->fetch();

        if (!$template) {
            // Fallback: templates default em modo standalone
            $defaults = $this->getTemplatesDefault();
            foreach ($defaults as $t) {
                if ($t['id'] == $id) {
                    $estrutura = json_decode($t['estrutura_json'], true);
                    echo json_encode(['ok' => true, 'corpo' => $estrutura['achados'] ?? '', 'nome' => $t['nome']]);
                    return;
                }
            }
            echo json_encode(['ok' => false, 'error' => 'Template nao encontrado']);
            return;
        }

        echo json_encode(['ok' => true, 'corpo' => $template->corpo ?? '', 'nome' => $template->nome ?? '']);
    }

    // API: buscar template por ID (AJAX)
    public function get(int $id): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $pdo      = Database::getInstance();
        $tenantId = Auth::tenantId();

        if ($tenantId) {
            $stmt = $pdo->prepare("SELECT * FROM cop_templates WHERE id = :id AND tenant_id = :tid AND ativo = 1 LIMIT 1");
            $stmt->execute(['id' => $id, 'tid' => $tenantId]);
            $template = $stmt->fetch();
        } else {
            $templates = $this->getTemplatesDefault();
            $template  = null;
            foreach ($templates as $t) { if ($t['id'] == $id) { $template = $t; break; } }
        }

        echo json_encode($template ?: ['error' => 'Template não encontrado']);
    }

    // API: salvar/atualizar dicom_study_description de um template (AJAX)
    public function apiSalvarDicomStudy(int $id): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId() ?? 0;

        $body = json_decode(file_get_contents('php://input'), true);
        $dicomStudy = trim($body['dicom_study_description'] ?? '');

        // Verifica se o template pertence ao tenant/médico
        $check = $pdo->prepare("SELECT id FROM cop_templates WHERE id=:id AND tenant_id=:tid AND ativo=1 LIMIT 1");
        $check->execute(['id' => $id, 'tid' => $tenantId]);
        if (!$check->fetch()) {
            echo json_encode(['ok' => false, 'msg' => 'Template não encontrado ou sem permissão.']);
            return;
        }

        $pdo->prepare("
            UPDATE cop_templates
            SET dicom_study_description = :dicom, updated_at = NOW()
            WHERE id = :id AND tenant_id = :tid
        ")->execute([
            'dicom' => $dicomStudy ?: null,
            'id'    => $id,
            'tid'   => $tenantId,
        ]);

        echo json_encode(['ok' => true, 'msg' => 'TAG DICOM atualizada com sucesso.']);
    }

    // API: sugerir templates por StudyDescription do DICOM (AJAX chamado pelo Workspace)
    public function apiSugerirPorDicom(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $pdo           = Database::getInstance();
        $tenantId      = Auth::tenantId();
        $medicoId      = Auth::userId();
        $studyDesc     = trim($_GET['study_description'] ?? '');
        $modalidade    = trim($_GET['modalidade'] ?? '');

        if (!$studyDesc) {
            echo json_encode(['ok' => true, 'sugestoes' => []]);
            return;
        }

        // Normaliza: maiúsculas, sem acentos, sem espaços duplos
        $studyNorm = strtoupper(preg_replace('/\s+/', ' ', $studyDesc));

        $sugestoes = [];

        // 1. Busca templates do médico/tenant com dicom_study_description preenchida
        if ($tenantId) {
            $stmt = $pdo->prepare("
                SELECT id, nome, modalidade, dicom_study_description
                FROM cop_templates
                WHERE tenant_id = :tid AND ativo = 1
                  AND dicom_study_description IS NOT NULL
                  AND dicom_study_description != ''
                ORDER BY uso_count DESC
                LIMIT 50
            ");
            $stmt->execute(['tid' => $tenantId]);
        } else {
            $stmt = $pdo->prepare("
                SELECT id, nome, modalidade, dicom_study_description
                FROM cop_templates
                WHERE user_id = :uid AND ativo = 1
                  AND dicom_study_description IS NOT NULL
                  AND dicom_study_description != ''
                ORDER BY uso_count DESC
                LIMIT 50
            ");
            $stmt->execute(['uid' => $medicoId]);
        }
        $templates = $stmt->fetchAll();

        foreach ($templates as $t) {
            $tagNorm = strtoupper(preg_replace('/\s+/', ' ', trim($t->dicom_study_description ?? $t['dicom_study_description'] ?? '')));
            $tNome   = $t->nome ?? $t['nome'] ?? '';
            $tMod    = $t->modalidade ?? $t['modalidade'] ?? '';
            $tId     = $t->id ?? $t['id'] ?? 0;

            // Match exato ou parcial (study description contém a tag ou vice-versa)
            $score = 0;
            if ($tagNorm === $studyNorm) {
                $score = 100; // match exato
            } elseif (str_contains($studyNorm, $tagNorm) || str_contains($tagNorm, $studyNorm)) {
                $score = 80;  // match parcial
            } elseif ($modalidade && strtoupper($tMod) === strtoupper($modalidade)) {
                // Mesma modalidade, sem match de descrição
                $score = 0;
            }

            if ($score > 0) {
                $sugestoes[] = [
                    'id'         => (int)$tId,
                    'nome'       => $tNome,
                    'modalidade' => $tMod,
                    'score'      => $score,
                    'match_tipo' => $score === 100 ? 'exato' : 'parcial',
                ];
            }
        }

        // Ordena por score decrescente
        usort($sugestoes, fn($a, $b) => $b['score'] - $a['score']);

        echo json_encode(['ok' => true, 'sugestoes' => array_slice($sugestoes, 0, 5)]);
    }

    private function getModalidades(): array {
        return ['TC','RM','RX','US','PET','MG','NM','DO','ECO','DX'];
    }

    private function getTemplatesDefault(): array {
        return [
            ['id'=>1,'nome'=>'TC Tórax com Contraste','modalidade'=>'TC','uso_count'=>47,'publico'=>1,'created_at'=>'2025-07-01','estrutura_json'=>json_encode(['indicacao'=>'','tecnica'=>'TC de tórax realizada com administração endovenosa de meio de contraste iodado.','achados'=>'','impressao'=>'','recomendacao'=>''])],
            ['id'=>2,'nome'=>'RM Encéfalo sem Contraste','modalidade'=>'RM','uso_count'=>32,'publico'=>1,'created_at'=>'2025-07-01','estrutura_json'=>json_encode(['indicacao'=>'','tecnica'=>'RM do encéfalo realizada em aparelho de 1.5T, sem administração de contraste.','achados'=>'','impressao'=>'','recomendacao'=>''])],
            ['id'=>3,'nome'=>'RX Tórax PA e Perfil','modalidade'=>'RX','uso_count'=>89,'publico'=>1,'created_at'=>'2025-07-01','estrutura_json'=>json_encode(['indicacao'=>'','tecnica'=>'Radiografia do tórax em incidências PA e perfil.','achados'=>'','impressao'=>'','recomendacao'=>''])],
            ['id'=>4,'nome'=>'US Abdome Total','modalidade'=>'US','uso_count'=>28,'publico'=>1,'created_at'=>'2025-07-01','estrutura_json'=>json_encode(['indicacao'=>'','tecnica'=>'Ultrassonografia abdominal total com transdutor convexo.','achados'=>'','impressao'=>'','recomendacao'=>''])],
            ['id'=>5,'nome'=>'PET-CT Estadiamento Oncológico','modalidade'=>'PET','uso_count'=>15,'publico'=>1,'created_at'=>'2025-07-01','estrutura_json'=>json_encode(['indicacao'=>'','tecnica'=>'PET-CT de corpo inteiro com 18F-FDG para estadiamento oncológico.','achados'=>'','impressao'=>'','recomendacao'=>''])],
        ];
    }
}
