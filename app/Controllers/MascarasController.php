<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Middlewares\AuthMiddleware;

/**
 * MascarasController
 * Gerencia a biblioteca de máscaras de laudo e a importação de DOCX.
 * Integrado ao módulo de Templates.
 */
class MascarasController extends Controller {

    // ──────────────────────────────────────────────────────────────────────────
    // LISTAGEM — /templates/mascaras
    // ──────────────────────────────────────────────────────────────────────────
    public function index(): void {
        AuthMiddleware::handle();

        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId() ?: null;

        $busca      = trim($_GET['busca']      ?? '');
        $modalidade = trim($_GET['modalidade'] ?? '');
        $origem     = trim($_GET['origem']     ?? ''); // 'biblioteca' | 'importadas' | ''

        // ── Biblioteca do sistema (máscaras pré-carregadas) ──────────────────
        $sqlBib  = "SELECT id, titulo, nome_amigavel, modalidade, especialidade, tags, origem_arquivo, 'biblioteca' AS origem_tipo FROM cop_mascaras_biblioteca WHERE ativo = 1";
        $pBib    = [];
        if ($busca) {
            $sqlBib .= " AND (nome_amigavel LIKE :busca OR titulo LIKE :busca2 OR corpo LIKE :busca3)";
            $pBib['busca'] = $pBib['busca2'] = $pBib['busca3'] = "%{$busca}%";
        }
        if ($modalidade) { $sqlBib .= " AND modalidade = :mod"; $pBib['mod'] = $modalidade; }
        $sqlBib .= " ORDER BY nome_amigavel ASC";

        $stmtBib = $pdo->prepare($sqlBib);
        $stmtBib->execute($pBib);
        $biblioteca = $stmtBib->fetchAll();

        // ── Templates importados pelo médico ─────────────────────────────────
        $sqlTpl  = "SELECT id, nome AS nome_amigavel, modalidade, especialidade, uso_count, origem, origem_arquivo, 'template' AS origem_tipo FROM cop_templates WHERE ativo = 1 AND origem IN ('importado','docx')";
        $pTpl    = [];
        if ($tenantId) {
            $sqlTpl .= " AND tenant_id = :tid";
            $pTpl['tid'] = $tenantId;
        } else {
            $sqlTpl .= " AND user_id = :uid";
            $pTpl['uid'] = $medicoId;
        }
        if ($busca) { $sqlTpl .= " AND nome LIKE :busca"; $pTpl['busca'] = "%{$busca}%"; }
        if ($modalidade) { $sqlTpl .= " AND modalidade = :mod2"; $pTpl['mod2'] = $modalidade; }
        $sqlTpl .= " ORDER BY nome ASC";

        try {
            $stmtTpl = $pdo->prepare($sqlTpl);
            $stmtTpl->execute($pTpl);
            $importados = $stmtTpl->fetchAll();
        } catch (\PDOException $e) {
            $importados = [];
            error_log('[MascarasController::index] ' . $e->getMessage());
        }

        // ── Histórico de importações ─────────────────────────────────────────
        try {
            $stmtHist = $pdo->prepare("SELECT * FROM cop_mascaras_importacoes WHERE user_id = :uid ORDER BY created_at DESC LIMIT 10");
            $stmtHist->execute(['uid' => $medicoId]);
            $historico = $stmtHist->fetchAll();
        } catch (\PDOException $e) {
            $historico = [];
        }

        $this->view('templates/mascaras', [
            'title'        => 'Máscaras de Laudo — VOXEL Copilot',
            'pageTitle'    => 'Máscaras de Laudo',
            'pageSubtitle' => 'Biblioteca e importação de máscaras estruturadas',
            'biblioteca'   => $biblioteca,
            'importados'   => $importados,
            'historico'    => $historico,
            'busca'        => $busca,
            'modalidade'   => $modalidade,
            'origem'       => $origem,
            'csrf_token'   => $this->csrfToken(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PREVIEW — GET /templates/mascaras/{id}/preview
    // ──────────────────────────────────────────────────────────────────────────
    public function preview(int $id): void {
        AuthMiddleware::handle();
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare("SELECT * FROM cop_mascaras_biblioteca WHERE id = :id AND ativo = 1 LIMIT 1");
        $stmt->execute(['id' => $id]);
        $mascara = $stmt->fetch();

        if (!$mascara) {
            $this->json(['ok' => false, 'msg' => 'Máscara não encontrada.'], 404);
            return;
        }

        $this->json([
            'ok'             => true,
            'id'             => $mascara->id,
            'titulo'         => $mascara->titulo,
            'nome_amigavel'  => $mascara->nome_amigavel,
            'modalidade'     => $mascara->modalidade,
            'especialidade'  => $mascara->especialidade,
            'corpo'          => $mascara->corpo,
            'secao_tecnica'  => $mascara->secao_tecnica,
            'secao_analise'  => $mascara->secao_analise,
            'secao_impressao'=> $mascara->secao_impressao,
            'tags'           => $mascara->tags,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // IMPORTAR DA BIBLIOTECA — POST /templates/mascaras/{id}/importar
    // Copia uma máscara da biblioteca para cop_templates do médico
    // ──────────────────────────────────────────────────────────────────────────
    public function importarDaBiblioteca(int $id): void {
        AuthMiddleware::handle();

        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId() ?: null;

        $stmt = $pdo->prepare("SELECT * FROM cop_mascaras_biblioteca WHERE id = :id AND ativo = 1 LIMIT 1");
        $stmt->execute(['id' => $id]);
        $mascara = $stmt->fetch();

        if (!$mascara) {
            $this->json(['ok' => false, 'msg' => 'Máscara não encontrada.'], 404);
            return;
        }

        // Verifica se já importou esta máscara
        try {
            $check = $pdo->prepare("SELECT id FROM cop_templates WHERE user_id = :uid AND origem_arquivo = :orig AND nome = :nome AND ativo = 1 LIMIT 1");
            $check->execute(['uid' => $medicoId, 'orig' => 'biblioteca:' . $id, 'nome' => $mascara->nome_amigavel]);
            if ($check->fetch()) {
                $this->json(['ok' => false, 'msg' => 'Você já importou esta máscara. Acesse Templates para editá-la.', 'ja_existe' => true]);
                return;
            }
        } catch (\PDOException $e) {
            // Coluna pode não existir ainda — ignora verificação duplicada
        }

        try {
            $pdo->prepare("
                INSERT INTO cop_templates
                    (tenant_id, user_id, nome, modalidade, especialidade, corpo,
                     secao_tecnica, secao_analise, secao_impressao, secao_adicional,
                     origem, origem_arquivo, tags, ativo, uso_count, created_at, updated_at)
                VALUES
                    (:tid, :uid, :nome, :mod, :esp, :corpo,
                     :tecnica, :analise, :impressao, :adicional,
                     'importado', :orig, :tags, 1, 0, NOW(), NOW())
            ")->execute([
                'tid'      => $tenantId,
                'uid'      => $medicoId,
                'nome'     => $mascara->nome_amigavel,
                'mod'      => $mascara->modalidade,
                'esp'      => $mascara->especialidade,
                'corpo'    => $mascara->corpo,
                'tecnica'  => $mascara->secao_tecnica,
                'analise'  => $mascara->secao_analise,
                'impressao'=> $mascara->secao_impressao,
                'adicional'=> $mascara->secao_adicional ?? null,
                'orig'     => 'biblioteca:' . $id,
                'tags'     => $mascara->tags,
            ]);
            $novoId = (int) $pdo->lastInsertId();

            $this->json([
                'ok'         => true,
                'msg'        => 'Máscara importada com sucesso! Você pode editá-la em Templates.',
                'template_id'=> $novoId,
                'edit_url'   => '/templates/' . $novoId . '/editar',
            ]);
        } catch (\PDOException $e) {
            error_log('[MascarasController::importarDaBiblioteca] ' . $e->getMessage());
            $this->json(['ok' => false, 'msg' => 'Erro ao importar: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // IMPORTAR DOCX — POST /templates/mascaras/importar-docx
    // Faz upload de um .docx e extrai as máscaras automaticamente
    // ──────────────────────────────────────────────────────────────────────────
    public function importarDocx(): void {
        AuthMiddleware::handle();

        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId() ?: null;

        if (empty($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['ok' => false, 'msg' => 'Nenhum arquivo enviado ou erro no upload.'], 400);
            return;
        }

        $file = $_FILES['arquivo'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['docx', 'doc'])) {
            $this->json(['ok' => false, 'msg' => 'Apenas arquivos .docx são suportados.'], 400);
            return;
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            $this->json(['ok' => false, 'msg' => 'Arquivo muito grande (máximo 10MB).'], 400);
            return;
        }

        // Salva temporariamente
        $tmpDir  = sys_get_temp_dir();
        $tmpPath = $tmpDir . '/mascara_' . uniqid() . '.docx';
        move_uploaded_file($file['tmp_name'], $tmpPath);

        $hash = md5_file($tmpPath);

        // Extrai máscaras via parser PHP (sem dependência Python no servidor)
        $mascaras = $this->parsearDocx($tmpPath);
        unlink($tmpPath);

        if (empty($mascaras)) {
            $this->json(['ok' => false, 'msg' => 'Nenhuma máscara encontrada no arquivo. Verifique se o DOCX contém títulos de exames em maiúsculas.'], 422);
            return;
        }

        // Registra importação
        $importacaoId = 0;
        try {
            $pdo->prepare("
                INSERT INTO cop_mascaras_importacoes (user_id, tenant_id, arquivo_nome, arquivo_hash, total_mascaras, status, created_at)
                VALUES (:uid, :tid, :nome, :hash, :total, 'processando', NOW())
            ")->execute([
                'uid'   => $medicoId,
                'tid'   => $tenantId,
                'nome'  => $file['name'],
                'hash'  => $hash,
                'total' => count($mascaras),
            ]);
            $importacaoId = (int) $pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log('[MascarasController::importarDocx] importacao log: ' . $e->getMessage());
        }

        // Retorna lista de máscaras para o usuário selecionar quais importar
        $this->json([
            'ok'           => true,
            'importacao_id'=> $importacaoId,
            'total'        => count($mascaras),
            'mascaras'     => $mascaras,
            'msg'          => count($mascaras) . ' máscaras encontradas. Selecione as que deseja importar.',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // CONFIRMAR IMPORTAÇÃO — POST /templates/mascaras/confirmar-importacao
    // Salva as máscaras selecionadas como templates do médico
    // ──────────────────────────────────────────────────────────────────────────
    public function confirmarImportacao(): void {
        AuthMiddleware::handle();

        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId() ?: null;

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (empty($data['mascaras']) || !is_array($data['mascaras'])) {
            $this->json(['ok' => false, 'msg' => 'Nenhuma máscara selecionada.'], 400);
            return;
        }

        $importacaoId = (int)($data['importacao_id'] ?? 0);
        $importadas   = 0;
        $log          = [];

        foreach ($data['mascaras'] as $m) {
            $nome      = trim($m['nome_amigavel'] ?? $m['titulo'] ?? '');
            $modalidade= trim($m['modalidade']    ?? 'TC');
            $corpo     = trim($m['corpo']          ?? '');

            if (!$nome || !$corpo) continue;

            try {
                $pdo->prepare("
                    INSERT INTO cop_templates
                        (tenant_id, user_id, nome, modalidade, especialidade, corpo,
                         secao_tecnica, secao_analise, secao_impressao, secao_adicional,
                         origem, origem_arquivo, ativo, uso_count, created_at, updated_at)
                    VALUES
                        (:tid, :uid, :nome, :mod, :esp, :corpo,
                         :tecnica, :analise, :impressao, :adicional,
                         'docx', :orig, 1, 0, NOW(), NOW())
                ")->execute([
                    'tid'      => $tenantId,
                    'uid'      => $medicoId,
                    'nome'     => mb_substr($nome, 0, 255),
                    'mod'      => $modalidade,
                    'esp'      => $m['especialidade'] ?? 'Radiologia',
                    'corpo'    => $corpo,
                    'tecnica'  => $m['secoes']['tecnica']   ?? null,
                    'analise'  => $m['secoes']['analise']   ?? null,
                    'impressao'=> $m['secoes']['impressao'] ?? null,
                    'adicional'=> $m['secoes']['adicional'] ?? null,
                    'orig'     => 'docx:' . ($data['arquivo_nome'] ?? 'upload'),
                ]);
                $importadas++;
                $log[] = ['nome' => $nome, 'status' => 'ok'];
            } catch (\PDOException $e) {
                error_log('[MascarasController::confirmarImportacao] ' . $e->getMessage() . ' | nome=' . $nome);
                $log[] = ['nome' => $nome, 'status' => 'erro', 'msg' => $e->getMessage()];
            }
        }

        // Atualiza registro de importação
        if ($importacaoId) {
            try {
                $pdo->prepare("UPDATE cop_mascaras_importacoes SET importadas=:imp, status='concluido', log_json=:log WHERE id=:id")
                    ->execute(['imp' => $importadas, 'log' => json_encode($log), 'id' => $importacaoId]);
            } catch (\PDOException $e) {
                // Não bloqueia
            }
        }

        $this->json([
            'ok'        => true,
            'importadas'=> $importadas,
            'total'     => count($data['mascaras']),
            'msg'       => "{$importadas} máscara(s) importada(s) com sucesso! Acesse Templates para usar.",
            'url'       => '/templates',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // API BUSCA — GET /api/mascaras/buscar?q=...&mod=TC
    // ──────────────────────────────────────────────────────────────────────────
    // GET CORPO — GET /api/mascaras/{id}/corpo
    public function getCorpo(int $id): void {
        AuthMiddleware::handle();
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare("SELECT corpo, secao_analise FROM cop_mascaras_biblioteca WHERE id = :id AND ativo = 1 LIMIT 1");
        $stmt->execute(["id" => $id]);
        $row = $stmt->fetch();
        if (!$row) { $this->json(["ok" => false, "msg" => "Mascara nao encontrada."], 404); return; }
        $corpo = $row->secao_analise ?: $row->corpo ?: "";
        $this->json(["ok" => true, "corpo" => $corpo]);
    }

    public function buscar(): void {
        AuthMiddleware::handle();

        $pdo  = Database::getInstance();
        $q    = trim($_GET['q']   ?? '');
        $mod  = trim($_GET['mod'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per  = 20;
        $off  = ($page - 1) * $per;

        $sql    = "SELECT id, nome_amigavel, modalidade, especialidade, tags FROM cop_mascaras_biblioteca WHERE ativo = 1";
        $params = [];
        if ($q) {
            $sql .= " AND (nome_amigavel LIKE :q OR titulo LIKE :q2)";
            $params['q'] = $params['q2'] = "%{$q}%";
        }
        if ($mod) { $sql .= " AND modalidade = :mod"; $params['mod'] = $mod; }
        $sql .= " ORDER BY nome_amigavel ASC LIMIT {$per} OFFSET {$off}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        $this->json(['ok' => true, 'data' => $results, 'page' => $page]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SEED — POST /templates/mascaras/seed (apenas admin)
    // Popula cop_mascaras_biblioteca com as máscaras do DOCX padrão
    // ──────────────────────────────────────────────────────────────────────────
    public function seed(): void {
        AuthMiddleware::handle();

        if (!Auth::isPlatformAdmin()) {
            $this->json(['ok' => false, 'msg' => 'Acesso negado.'], 403);
            return;
        }

        $pdo = Database::getInstance();

        // Verifica se já foi feito o seed
        $count = $pdo->query("SELECT COUNT(*) FROM cop_mascaras_biblioteca")->fetchColumn();
        if ($count > 0) {
            $this->json(['ok' => false, 'msg' => "Biblioteca já contém {$count} máscaras. Use /seed?force=1 para recriar.", 'total' => $count]);
            return;
        }

        // Carrega o JSON gerado pelo parser Python
        $jsonPath = APP_PATH . '/../database/seeds/mascaras_tc.json';
        if (!file_exists($jsonPath)) {
            $this->json(['ok' => false, 'msg' => 'Arquivo de seed não encontrado: ' . $jsonPath], 404);
            return;
        }

        $mascaras = json_decode(file_get_contents($jsonPath), true);
        if (!$mascaras) {
            $this->json(['ok' => false, 'msg' => 'Erro ao ler o JSON de seed.'], 500);
            return;
        }

        $inseridas = 0;
        foreach ($mascaras as $m) {
            if (empty($m['corpo'])) continue;
            try {
                $pdo->prepare("
                    INSERT INTO cop_mascaras_biblioteca
                        (titulo, nome_amigavel, modalidade, especialidade, corpo,
                         secao_tecnica, secao_analise, secao_impressao, secao_adicional,
                         tags, origem_arquivo, ativo, created_at)
                    VALUES
                        (:titulo, :nome, :mod, :esp, :corpo,
                         :tecnica, :analise, :impressao, :adicional,
                         :tags, 'MASCARASTC.docx', 1, NOW())
                ")->execute([
                    'titulo'   => mb_substr($m['titulo'], 0, 500),
                    'nome'     => mb_substr($m['nome_amigavel'], 0, 255),
                    'mod'      => $m['modalidade'] ?? 'TC',
                    'esp'      => $m['especialidade'] ?? 'Radiologia',
                    'corpo'    => $m['corpo'],
                    'tecnica'  => $m['secoes']['tecnica']   ?? null,
                    'analise'  => $m['secoes']['analise']   ?? null,
                    'impressao'=> $m['secoes']['impressao'] ?? null,
                    'adicional'=> $m['secoes']['adicional'] ?? null,
                    'tags'     => $m['tags'] ?? null,
                ]);
                $inseridas++;
            } catch (\PDOException $e) {
                error_log('[MascarasController::seed] ' . $e->getMessage());
            }
        }

        $this->json(['ok' => true, 'inseridas' => $inseridas, 'total' => count($mascaras)]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PARSER PHP — extrai máscaras de um arquivo DOCX sem dependência Python
    // ──────────────────────────────────────────────────────────────────────────
    private function parsearDocx(string $path): array {
        // DOCX é um ZIP — extrai word/document.xml
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) return [];

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xml) return [];

        // Remove namespaces para simplificar o XPath
        $xml = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xml);
        $xml = preg_replace('/<w:([a-zA-Z]+)/i', '<$1', $xml);
        $xml = preg_replace('/<\/w:([a-zA-Z]+)/i', '</$1', $xml);

        $dom = new \DOMDocument();
        @$dom->loadXML($xml);

        // Extrai parágrafos
        $paragrafos = [];
        foreach ($dom->getElementsByTagName('p') as $p) {
            $texto = '';
            foreach ($p->getElementsByTagName('t') as $t) {
                $texto .= $t->nodeValue;
            }
            $texto = trim($texto);
            if ($texto !== '') $paragrafos[] = $texto;
        }

        if (empty($paragrafos)) return [];

        // Prefixos de títulos de exame
        $prefixos = [
            'ANGIOTOMOGRAFIA', 'TOMOGRAFIA COMPUTADORIZADA', 'TOMOGRAFIA DE ',
            'TC DE ', 'TC DO ', 'TC DA ', 'TC DOS ', 'TC DAS ',
            'CORONARIOTOMOGRAFIA', 'ANGIO TC',
        ];

        $isTitle = function(string $t) use ($prefixos): bool {
            $up = strtoupper(trim($t));
            if (strlen($up) < 10) return false;
            foreach ($prefixos as $p) {
                if (str_starts_with($up, $p)) return true;
            }
            return false;
        };

        // Agrupa parágrafos em máscaras
        $mascaras    = [];
        $current     = null;
        $titleIndices= [];

        foreach ($paragrafos as $i => $texto) {
            if ($isTitle($texto)) {
                $titleIndices[] = $i;
            }
        }

        foreach ($titleIndices as $idx => $ti) {
            $titulo = $paragrafos[$ti];
            $fim    = $titleIndices[$idx + 1] ?? count($paragrafos);

            $corpo_linhas = [];
            for ($k = $ti + 1; $k < $fim; $k++) {
                $corpo_linhas[] = $paragrafos[$k];
            }

            if (empty($corpo_linhas)) continue;

            $corpo = implode("\n", $corpo_linhas);

            // Extrai seções
            $secoes  = ['tecnica' => [], 'analise' => [], 'impressao' => [], 'adicional' => []];
            $secAtual = 'analise';

            foreach ($corpo_linhas as $linha) {
                if (preg_match('/^(Técnica|Metodologia|Método|Protocolo):/i', $linha)) {
                    $secAtual = 'tecnica';
                    $resto = preg_replace('/^[^:]+:\s*/i', '', $linha);
                    if ($resto) $secoes['tecnica'][] = $resto;
                } elseif (preg_match('/^(Análise|Achados|Descrição):/i', $linha)) {
                    $secAtual = 'analise';
                    $resto = preg_replace('/^[^:]+:\s*/i', '', $linha);
                    if ($resto) $secoes['analise'][] = $resto;
                } elseif (preg_match('/^(Impressão|Conclusão|Diagnóstico):/i', $linha)) {
                    $secAtual = 'impressao';
                    $resto = preg_replace('/^[^:]+:\s*/i', '', $linha);
                    if ($resto) $secoes['impressao'][] = $resto;
                } elseif (preg_match('/^Achados adicionais:/i', $linha)) {
                    $secAtual = 'adicional';
                } else {
                    $secoes[$secAtual][] = $linha;
                }
            }

            // Nome amigável
            $nomeAmigavel = $titulo;
            $nomeAmigavel = str_ireplace('TOMOGRAFIA COMPUTADORIZADA', 'TC', $nomeAmigavel);
            $nomeAmigavel = str_ireplace('ANGIOTOMOGRAFIA COMPUTADORIZADA', 'Angio-TC', $nomeAmigavel);
            $nomeAmigavel = str_ireplace('TOMOGRAFIA DE ', 'TC ', $nomeAmigavel);
            $nomeAmigavel = mb_convert_case($nomeAmigavel, MB_CASE_TITLE, 'UTF-8');

            $mascaras[] = [
                'titulo'        => $titulo,
                'nome_amigavel' => mb_substr($nomeAmigavel, 0, 120),
                'modalidade'    => 'TC',
                'especialidade' => 'Radiologia',
                'corpo'         => $corpo,
                'secoes'        => [
                    'tecnica'   => implode("\n", $secoes['tecnica']),
                    'analise'   => implode("\n", $secoes['analise']),
                    'impressao' => implode("\n", $secoes['impressao']),
                    'adicional' => implode("\n", $secoes['adicional']),
                ],
            ];
        }

        return $mascaras;
    }
}
