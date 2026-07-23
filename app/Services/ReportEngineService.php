<?php
namespace App\Services;

use App\Core\Auth;
use App\Core\Database;

/**
 * ReportEngineService — Motor de Laudos Estruturados do VOXEL Copilot
 *
 * Responsabilidades:
 *  - Prompt Builder: monta prompts enriquecidos com contexto clínico completo
 *  - Quality Engine: valida consistência do laudo antes de exibir ao médico
 *  - Dicionário Radiológico: padroniza terminologia automaticamente
 *  - Técnica Auto: gera texto de técnica baseado na modalidade
 */
class ReportEngineService
{
    // ─── DICIONÁRIO RADIOLÓGICO ────────────────────────────────────────────────
    // Termos genéricos → terminologia técnica padronizada
    private const DICIONARIO = [
        // Anatomia
        'barriga'          => 'abdome',
        'barriga'          => 'abdome',
        'pescoço'          => 'região cervical',
        'cabeça'           => 'crânio',
        'pulmão'           => 'parênquima pulmonar',
        'fígado'           => 'parênquima hepático',
        'rim'              => 'parênquima renal',
        'bexiga'           => 'bexiga urinária',
        'coração'          => 'coração (estrutura cardíaca)',
        'coluna'           => 'coluna vertebral',
        'joelho'           => 'articulação do joelho',
        'ombro'            => 'articulação do ombro',
        'quadril'          => 'articulação coxofemoral',
        'tornozelo'        => 'articulação do tornozelo',
        // Achados
        'pedra'            => 'cálculo',
        'pedras'           => 'cálculos',
        'tumor'            => 'lesão expansiva',
        'mancha'           => 'área de hipossinal / hipodensidade',
        'nódulo pequeno'   => 'micronódulo',
        'inchaço'          => 'edema',
        'inflamação'       => 'processo inflamatório',
        'entupimento'      => 'obstrução',
        'entupido'         => 'obstrutivo',
        'entupida'         => 'obstrutiva',
        'acúmulo de líquido' => 'coleção líquida',
        'líquido livre'    => 'líquido livre na cavidade',
        // Qualificadores
        'grande'           => 'de grandes dimensões',
        'pequeno'          => 'de pequenas dimensões',
        'normal'           => 'dentro dos limites da normalidade',
        'sem alteração'    => 'sem alterações significativas',
        'sem nada'         => 'sem alterações significativas',
        'tudo normal'      => 'sem alterações significativas ao método',
    ];

    // ─── TÉCNICAS PADRÃO POR MODALIDADE ───────────────────────────────────────
    private const TECNICAS = [
        'TC'  => 'Tomografia computadorizada realizada em equipamento multislice, com aquisição helicoidal e reconstruções multiplanares nos planos axial, coronal e sagital.',
        'RM'  => 'Ressonância magnética realizada em equipamento de alto campo, com sequências ponderadas em T1, T2, FLAIR, DWI e pós-contraste com gadolínio.',
        'RX'  => 'Radiografia digital realizada nas incidências solicitadas, com técnica e penetração adequadas.',
        'US'  => 'Ultrassonografia realizada com transdutor de alta frequência, em modo B e Doppler colorido quando indicado.',
        'MN'  => 'Cintilografia realizada com radiofármaco adequado ao protocolo solicitado, com aquisições planares e SPECT/CT quando indicado.',
        'PET' => 'PET-CT realizado com 18F-FDG, após período de jejum de 6 horas e glicemia verificada antes da administração do radiofármaco.',
        'DX'  => 'Radiografia digital realizada nas incidências solicitadas, com técnica e penetração adequadas.',
        'CR'  => 'Radiografia computadorizada realizada nas incidências solicitadas.',
        'MG'  => 'Mamografia digital bilateral realizada nas incidências craniocaudal (CC) e médio-lateral oblíqua (MLO).',
        'ECO' => 'Ecocardiograma transtorácico realizado em repouso, com avaliação bidimensional, modo M e Doppler.',
    ];

    // ─── EXPRESSÕES PROIBIDAS NOS ACHADOS ─────────────────────────────────────
    // Essas expressões pertencem exclusivamente à Impressão Diagnóstica
    private const EXPRESSOES_PROIBIDAS_ACHADOS = [
        'provável',
        'compatível com',
        'sugere',
        'indica',
        'pode representar',
        'favorece',
        'muito sugestivo',
        'sugestivo de',
        'sugestiva de',
        'compatível',
        'diagnóstico de',
        'diagnóstico provável',
        'provavelmente',
        'possivelmente',
        'suspeito para',
        'suspeita de',
        'altamente suspeito',
        'altamente sugestivo',
        'não se pode excluir',
        'não exclui',
    ];

    // ─── TERMOS INCOMPATÍVEIS POR MODALIDADE ──────────────────────────────────
    private const INCOMPATIBILIDADES_MODALIDADE = [
        'RX' => [
            'proibidos' => ['sequência t2', 'sequência t1', 'flair', 'dwi', 'swi', 'difusão', 'pós-contraste', 'gadolínio', 'ecogenicidade', 'doppler'],
            'msg'       => 'Termos de RM/US detectados em laudo de RX',
        ],
        'RM' => [
            'proibidos' => ['janela pulmonar', 'janela mediastinal', 'janela óssea', 'unidade hounsfield', 'hu ', 'ecogenicidade', 'doppler'],
            'msg'       => 'Termos de TC/US detectados em laudo de RM',
        ],
        'TC' => [
            'proibidos' => ['sequência t2', 'sequência t1', 'flair', 'dwi', 'swi', 'difusão', 'gadolínio', 'ecogenicidade', 'doppler'],
            'msg'       => 'Termos de RM/US detectados em laudo de TC',
        ],
        'US' => [
            'proibidos' => ['janela pulmonar', 'unidade hounsfield', 'hu ', 'sequência t2', 'sequência t1', 'flair', 'dwi'],
            'msg'       => 'Termos de TC/RM detectados em laudo de US',
        ],
    ];

    // ─── TERMOS INCOMPATÍVEIS POR SEXO ────────────────────────────────────────
    private const INCOMPATIBILIDADES_SEXO = [
        'M' => [
            'proibidos' => ['útero', 'ovário', 'ovários', 'endométrio', 'tuba uterina', 'tubas uterinas', 'vagina', 'colo uterino', 'mama', 'mamas'],
            'msg'       => 'Estruturas femininas descritas em paciente do sexo masculino',
        ],
        'F' => [
            'proibidos' => ['próstata', 'prostático', 'prostática', 'vesícula seminal', 'vesículas seminais', 'testículo', 'testículos', 'epidídimo'],
            'msg'       => 'Estruturas masculinas descritas em paciente do sexo feminino',
        ],
    ];

    // ─── PROMPT BUILDER ───────────────────────────────────────────────────────

    /**
     * Constrói um prompt enriquecido com todo o contexto clínico disponível.
     * Nunca envia apenas "Faça um laudo." — inclui paciente, indicação, modalidade,
     * protocolo, histórico, templates, Medical Profile, Prompt Base e idioma.
     */
    public function buildPrompt(array $ctx): string
    {
        $pdo = Database::getInstance();

        // ── Dados do contexto ──
        $workspaceId = (int)($ctx['workspace_id'] ?? 0);
        $medicoId    = Auth::userId();
        $tenantId    = Auth::tenantId();
        $modalidade  = strtoupper(trim($ctx['modalidade'] ?? ''));
        $indicacao   = trim($ctx['indicacao']   ?? '');
        $achados     = trim($ctx['achados']     ?? '');
        $impressao   = trim($ctx['impressao']   ?? '');
        $tecnica     = trim($ctx['tecnica']     ?? '');
        $mensagem    = trim($ctx['mensagem']    ?? '');
        $acao        = trim($ctx['acao']        ?? 'chat'); // chat|sugestao|revisar|impressao

        // ── Medical Profile do médico ──
        $perfil = null;
        try {
            $stmt = $pdo->prepare("SELECT * FROM cop_medico_perfil WHERE user_id = :uid LIMIT 1");
            $stmt->execute(['uid' => $medicoId]);
            $perfil = $stmt->fetch();
        } catch (\Throwable $e) {}

        // ── Prompt Base do tenant (especialidade) ──
        $promptBase = '';
        try {
            if ($tenantId) {
                $pbStmt = $pdo->prepare("
                    SELECT conteudo FROM cop_prompt_base
                    WHERE tenant_id = :tid AND ativo = 1
                    ORDER BY prioridade DESC LIMIT 1
                ");
                $pbStmt->execute(['tid' => $tenantId]);
                $pb = $pbStmt->fetch();
                if ($pb) $promptBase = $pb->conteudo;
            }
        } catch (\Throwable $e) {}

        // ── Dados do workspace/paciente ──
        $workspace = null;
        try {
            $wsStmt = $pdo->prepare("SELECT * FROM cop_workspaces WHERE id = :id LIMIT 1");
            $wsStmt->execute(['id' => $workspaceId]);
            $workspace = $wsStmt->fetch();
        } catch (\Throwable $e) {}

        $patNome     = $workspace->patient_nome ?? 'Não informado';
        $patSexo     = $workspace->patient_sexo ?? null;
        $patIdade    = $workspace->patient_idade ?? null;
        $patNasc     = $workspace->patient_nascimento ?? null;
        $accession   = $workspace->accession_number ?? null;
        $convenio    = $workspace->convenio ?? null;
        $solicitante = $workspace->medico_solicitante ?? null;

        // ── Laudos anteriores do paciente ──
        $historico = '';
        try {
            if ($workspace && ($workspace->patient_uid ?? null)) {
                $hStmt = $pdo->prepare("
                    SELECT l.achados, l.impressao, l.indicacao, w.modalidade, l.assinado_em
                    FROM cop_laudos l
                    JOIN cop_workspaces w ON w.id = l.workspace_id
                    WHERE w.patient_uid = :puid AND l.status = 'assinado'
                    ORDER BY l.assinado_em DESC LIMIT 3
                ");
                $hStmt->execute(['puid' => $workspace->patient_uid]);
                $anteriores = $hStmt->fetchAll();
                if ($anteriores) {
                    $historico = "\n\n=== HISTÓRICO DO PACIENTE (laudos anteriores) ===\n";
                    foreach ($anteriores as $ant) {
                        $data = $ant->assinado_em ? date('d/m/Y', strtotime($ant->assinado_em)) : 'N/D';
                        $historico .= "\n[{$ant->modalidade} — {$data}]\n";
                        if ($ant->indicacao)  $historico .= "Indicação: {$ant->indicacao}\n";
                        if ($ant->achados)    $historico .= "Achados: " . substr($ant->achados, 0, 500) . "\n";
                        if ($ant->impressao)  $historico .= "Impressão: {$ant->impressao}\n";
                    }
                }
            }
        } catch (\Throwable $e) {}

        // ── Vocabulário preferido do médico ──
        $vocab = '';
        try {
            if ($perfil && !empty($perfil->vocabulario)) {
                $vocabArr = json_decode($perfil->vocabulario, true) ?: [];
                if ($vocabArr) {
                    $vocab = "\n\n=== VOCABULÁRIO PREFERIDO DO MÉDICO ===\n";
                    foreach ($vocabArr as $orig => $pref) {
                        $vocab .= "- Use \"{$pref}\" em vez de \"{$orig}\"\n";
                    }
                }
            }
        } catch (\Throwable $e) {}

        // ── Técnica padrão ──
        $tecnicaPadrao = self::TECNICAS[$modalidade] ?? '';

        // ── Monta o System Prompt ──
        $idioma     = $perfil->idioma_laudo ?? 'pt-BR';
        $estilo     = $perfil->estilo_laudo ?? 'formal';
        $especialidade = $perfil->especialidade ?? '';

        $system = "Você é o VOXEL Copilot, assistente de IA especializado em radiologia médica.\n";
        $system .= "Idioma de resposta: {$idioma}. Estilo: {$estilo}.\n";
        if ($especialidade) $system .= "Especialidade do médico: {$especialidade}.\n";

        $system .= "\n=== REGRAS ABSOLUTAS ===\n";
        $system .= "1. Na seção ACHADOS: apenas descrição objetiva. NUNCA use: provável, compatível, sugere, indica, pode representar, favorece, muito sugestivo.\n";
        $system .= "2. Na seção IMPRESSÃO DIAGNÓSTICA: apenas interpretação clínica, resumo objetivo em tópicos com bullet points (•).\n";
        $system .= "3. NUNCA misture opinião clínica dentro dos Achados.\n";
        $system .= "4. NUNCA sugira tratamento.\n";
        $system .= "5. NUNCA altere o sentido clínico sem aprovação do médico.\n";
        $system .= "6. Respeite sempre a separação: Achados = descrição objetiva | Impressão = interpretação.\n";
        $system .= "7. Use terminologia radiológica padronizada (RadLex/SNOMED quando aplicável).\n";

        if ($promptBase) {
            $system .= "\n=== PROMPT BASE DO SERVIÇO ===\n{$promptBase}\n";
        }

        $system .= $vocab;

        // ── Monta o User Prompt ──
        $user = "=== CONTEXTO DO EXAME ===\n";
        $user .= "Paciente: {$patNome}\n";
        if ($patSexo)    $user .= "Sexo: {$patSexo}\n";
        if ($patIdade)   $user .= "Idade: {$patIdade} anos\n";
        if ($patNasc)    $user .= "Nascimento: " . date('d/m/Y', strtotime($patNasc)) . "\n";
        if ($convenio)   $user .= "Convênio: {$convenio}\n";
        if ($solicitante) $user .= "Médico solicitante: {$solicitante}\n";
        if ($accession)  $user .= "Accession Number: {$accession}\n";
        $user .= "Modalidade: {$modalidade}\n";

        if ($indicacao) {
            $user .= "\n=== INDICAÇÃO CLÍNICA ===\n{$indicacao}\n";
        }

        if ($tecnica) {
            $user .= "\n=== TÉCNICA ===\n{$tecnica}\n";
        } elseif ($tecnicaPadrao) {
            $user .= "\n=== TÉCNICA (padrão para {$modalidade}) ===\n{$tecnicaPadrao}\n";
        }

        if ($achados) {
            $user .= "\n=== ACHADOS ATUAIS ===\n{$achados}\n";
        }

        if ($impressao) {
            $user .= "\n=== IMPRESSÃO DIAGNÓSTICA ATUAL ===\n{$impressao}\n";
        }

        $user .= $historico;

        // ── Instrução específica por ação ──
        switch ($acao) {
            case 'sugestao':
                $user .= "\n=== TAREFA ===\n";
                $user .= "Gere uma sugestão completa de achados para este exame de {$modalidade}.\n";
                $user .= "Siga rigorosamente as regras: apenas descrição objetiva, sem interpretação.\n";
                $user .= "Organize por sistemas/órgãos relevantes para a modalidade e indicação.\n";
                break;

            case 'impressao':
                $user .= "\n=== TAREFA ===\n";
                $user .= "Com base nos achados acima, gere a Impressão Diagnóstica.\n";
                $user .= "Formato: bullet points (•), objetiva, em tópicos.\n";
                $user .= "Inclua apenas interpretação clínica. Não repita os achados.\n";
                break;

            case 'revisar':
                $user .= "\n=== TAREFA: REVISÃO COMPLETA DO LAUDO ===\n";
                $user .= "Execute as seguintes validações e retorne um relatório estruturado:\n";
                $user .= "1. Ortografia e gramática\n";
                $user .= "2. Padronização de terminologia radiológica\n";
                $user .= "3. Consistência (achados vs impressão)\n";
                $user .= "4. Lateralidade (direita/esquerda mencionadas corretamente)\n";
                $user .= "5. Compatibilidade com a modalidade ({$modalidade})\n";
                $user .= "6. Expressões de interpretação indevidas nos Achados\n";
                $user .= "7. Repetições e redundâncias\n";
                $user .= "8. Estrutura do laudo\n";
                $user .= "\nFormato da resposta:\n";
                $user .= "- Para cada problema: [TIPO] Descrição do problema → Sugestão de correção\n";
                $user .= "- Se não houver problemas em uma categoria: [OK] Categoria — sem problemas\n";
                $user .= "- Ao final: SCORE DE QUALIDADE: X/10\n";
                $user .= "\nNUNCA altere o sentido clínico. Apenas aponte e sugira.\n";
                break;

            default: // chat
                $user .= "\n=== PERGUNTA DO MÉDICO ===\n{$mensagem}\n";
                break;
        }

        return json_encode([
            'system' => $system,
            'user'   => $user,
        ]);
    }

    // ─── QUALITY ENGINE ───────────────────────────────────────────────────────

    /**
     * Valida o laudo antes de exibir ao médico.
     * Retorna array de alertas com tipo (erro|aviso|info) e mensagem.
     */
    public function validate(array $laudo): array
    {
        $alertas = [];

        $modalidade  = strtoupper(trim($laudo['modalidade']  ?? ''));
        $sexo        = strtoupper(trim($laudo['sexo']        ?? ''));
        $achados     = strtolower($laudo['achados']    ?? '');
        $impressao   = strtolower($laudo['impressao']  ?? '');
        $tecnica     = strtolower($laudo['tecnica']    ?? '');
        $indicacao   = trim($laudo['indicacao'] ?? '');
        $recomendacao = trim($laudo['recomendacao'] ?? '');

        // 1. Indicação clínica obrigatória
        if (empty($indicacao)) {
            $alertas[] = [
                'tipo' => 'aviso',
                'campo' => 'indicacao',
                'msg'  => 'Indicação clínica não preenchida. Recomenda-se sempre informar a indicação.',
            ];
        }

        // 2. Achados não podem estar vazios
        if (empty(trim($laudo['achados'] ?? ''))) {
            $alertas[] = [
                'tipo' => 'erro',
                'campo' => 'achados',
                'msg'  => 'A seção de Achados está vazia.',
            ];
        }

        // 3. Impressão não pode estar vazia
        if (empty(trim($laudo['impressao'] ?? ''))) {
            $alertas[] = [
                'tipo' => 'aviso',
                'campo' => 'impressao',
                'msg'  => 'A Impressão Diagnóstica está vazia.',
            ];
        }

        // 4. Expressões proibidas nos Achados
        foreach (self::EXPRESSOES_PROIBIDAS_ACHADOS as $expr) {
            if (strpos($achados, $expr) !== false) {
                $alertas[] = [
                    'tipo' => 'erro',
                    'campo' => 'achados',
                    'msg'  => "Expressão de interpretação detectada nos Achados: \"{$expr}\". Esta expressão pertence à Impressão Diagnóstica.",
                ];
            }
        }

        // 5. Incompatibilidade por modalidade
        if ($modalidade && isset(self::INCOMPATIBILIDADES_MODALIDADE[$modalidade])) {
            $incompat = self::INCOMPATIBILIDADES_MODALIDADE[$modalidade];
            $textoCompleto = $achados . ' ' . $impressao . ' ' . $tecnica;
            foreach ($incompat['proibidos'] as $termo) {
                if (strpos($textoCompleto, $termo) !== false) {
                    $alertas[] = [
                        'tipo' => 'erro',
                        'campo' => 'achados',
                        'msg'  => $incompat['msg'] . ": termo \"{$termo}\" incompatível com {$modalidade}.",
                    ];
                }
            }
        }

        // 6. Incompatibilidade por sexo
        if ($sexo && isset(self::INCOMPATIBILIDADES_SEXO[$sexo])) {
            $incompat = self::INCOMPATIBILIDADES_SEXO[$sexo];
            $textoCompleto = $achados . ' ' . $impressao;
            foreach ($incompat['proibidos'] as $termo) {
                if (strpos($textoCompleto, $termo) !== false) {
                    $alertas[] = [
                        'tipo' => 'erro',
                        'campo' => 'achados',
                        'msg'  => $incompat['msg'] . ": \"{$termo}\" em paciente {$sexo}.",
                    ];
                }
            }
        }

        // 7. Contraste: se técnica menciona "sem contraste" mas achados mencionam "após contraste"
        if (strpos($tecnica, 'sem contraste') !== false &&
            (strpos($achados, 'após administração do contraste') !== false ||
             strpos($achados, 'pós-contraste') !== false ||
             strpos($achados, 'fase arterial') !== false)) {
            $alertas[] = [
                'tipo' => 'erro',
                'campo' => 'tecnica',
                'msg'  => 'Inconsistência de contraste: técnica indica "sem contraste" mas achados descrevem fases contrastadas.',
            ];
        }

        // 8. Lateralidade: verifica menções contraditórias
        $temDireita   = strpos($achados, 'direito') !== false || strpos($achados, 'direita') !== false;
        $temEsquerda  = strpos($achados, 'esquerdo') !== false || strpos($achados, 'esquerda') !== false;
        $temBilateral = strpos($achados, 'bilateral') !== false || strpos($achados, 'bilaterais') !== false;

        // Avisa se menciona lateralidade mas não menciona bilateral (pode ser intencional)
        if ($temDireita && $temEsquerda && !$temBilateral) {
            $alertas[] = [
                'tipo' => 'info',
                'campo' => 'achados',
                'msg'  => 'Achados mencionam estruturas direitas e esquerdas. Verifique se o uso de "bilateral" seria mais adequado.',
            ];
        }

        // 9. Recomendações com protocolos estruturados
        $protocolos = ['lung-rads', 'bi-rads', 'pi-rads', 'li-rads', 'fleischner', 'bosniak', 'acr'];
        foreach ($protocolos as $protocolo) {
            if (strpos(strtolower($achados . ' ' . $impressao), $protocolo) !== false &&
                empty($recomendacao)) {
                $alertas[] = [
                    'tipo' => 'aviso',
                    'campo' => 'recomendacao',
                    'msg'  => "Protocolo {$protocolo} mencionado. Considere preencher a seção de Recomendações com o score e conduta sugerida.",
                ];
                break;
            }
        }

        return $alertas;
    }

    // ─── DICIONÁRIO RADIOLÓGICO ───────────────────────────────────────────────

    /**
     * Aplica o dicionário radiológico ao texto, substituindo termos genéricos
     * por terminologia técnica padronizada.
     * Preserva maiúsculas/minúsculas do contexto.
     */
    public function aplicarDicionario(string $texto): string
    {
        foreach (self::DICIONARIO as $generico => $tecnico) {
            // Substituição case-insensitive, palavra inteira
            $pattern = '/\b' . preg_quote($generico, '/') . '\b/iu';
            $texto = preg_replace_callback($pattern, function($m) use ($tecnico) {
                // Preserva capitalização: se original começa com maiúscula, aplica no substituto
                if (ctype_upper(mb_substr($m[0], 0, 1))) {
                    return mb_strtoupper(mb_substr($tecnico, 0, 1)) . mb_substr($tecnico, 1);
                }
                return $tecnico;
            }, $texto);
        }
        return $texto;
    }

    // ─── TÉCNICA AUTO ─────────────────────────────────────────────────────────

    /**
     * Retorna o texto de técnica padrão para a modalidade informada.
     * Aceita variações como "TC com contraste", "RM sem contraste", etc.
     */
    public function getTecnicaPadrao(string $modalidade, bool $comContraste = true): string
    {
        $mod = strtoupper(trim($modalidade));

        // Extrai sigla base (ex: "TC COM CONTRASTE" → "TC")
        foreach (array_keys(self::TECNICAS) as $sigla) {
            if (strpos($mod, $sigla) === 0) {
                $base = self::TECNICAS[$sigla];
                if (!$comContraste) {
                    // Remove menções a contraste
                    $base = preg_replace('/,?\s*(realizada\s+)?com\s+contraste\s+\w+/i', '', $base);
                    $base = preg_replace('/\s+nas\s+fases\s+[\w\s,]+\./i', '.', $base);
                    $base = trim($base);
                }
                return $base;
            }
        }

        return '';
    }

    // ─── GERADOR DE CABEÇALHO ─────────────────────────────────────────────────

    /**
     * Gera os dados do cabeçalho do laudo (instituição, exame, data/hora).
     * Esses dados são gerados automaticamente e nunca editados manualmente.
     */
    public function getCabecalho(int $workspaceId, ?int $tenantId): array
    {
        $pdo = Database::getInstance();

        // Dados do tenant (instituição)
        $tenant = null;
        if ($tenantId) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM cop_tenants WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => $tenantId]);
                $tenant = $stmt->fetch();
            } catch (\Throwable $e) {}
        }

        // Dados do workspace
        $workspace = null;
        try {
            $stmt = $pdo->prepare("SELECT * FROM cop_workspaces WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $workspaceId]);
            $workspace = $stmt->fetch();
        } catch (\Throwable $e) {}

        return [
            'instituicao'   => $tenant->nome ?? 'VOXEL Copilot',
            'logo_url'      => $tenant->logo_url ?? null,
            'endereco'      => $tenant->endereco ?? null,
            'telefone'      => $tenant->telefone ?? null,
            'cnpj'          => $tenant->cnpj ?? null,
            'numero_exame'  => $workspace->accession_number ?? ('EX-' . str_pad($workspaceId, 6, '0', STR_PAD_LEFT)),
            'codigo_interno'=> $workspace->id ?? $workspaceId,
            'codigo_tiss'   => $workspace->codigo_tiss ?? null,
            'data_exame'    => $workspace->created_at ? date('d/m/Y', strtotime($workspace->created_at)) : date('d/m/Y'),
            'hora_exame'    => $workspace->created_at ? date('H:i', strtotime($workspace->created_at)) : date('H:i'),
            'modalidade'    => $workspace->modalidade ?? null,
        ];
    }

    // ─── GERADOR DE ASSINATURA ────────────────────────────────────────────────

    /**
     * Gera os dados da assinatura digital do laudo.
     * A assinatura é gerada automaticamente e nunca editável.
     */
    public function getAssinatura(int $laudoId, int $medicoId): array
    {
        $pdo = Database::getInstance();

        // Dados do médico
        $medico = null;
        try {
            $stmt = $pdo->prepare("SELECT * FROM cop_users WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $medicoId]);
            $medico = $stmt->fetch();
        } catch (\Throwable $e) {}

        // Dados do laudo para hash
        $laudo = null;
        try {
            $stmt = $pdo->prepare("SELECT * FROM cop_laudos WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $laudoId]);
            $laudo = $stmt->fetch();
        } catch (\Throwable $e) {}

        // Hash do documento (SHA-256 do conteúdo)
        $conteudo = ($laudo->indicacao ?? '') . ($laudo->tecnica ?? '') .
                    ($laudo->achados ?? '') . ($laudo->impressao ?? '') .
                    ($laudo->recomendacao ?? '');
        $hash = hash('sha256', $conteudo . $laudoId . ($laudo->assinado_em ?? ''));

        return [
            'nome'          => $medico->name ?? 'Médico',
            'crm'           => $medico->crm ?? null,
            'rqe'           => $medico->rqe ?? null,
            'especialidade' => $medico->especialidades ?? null,
            'assinado_em'   => $laudo->assinado_em ?? null,
            'hash'          => strtoupper(substr($hash, 0, 16)) . '...',
            'hash_completo' => strtoupper($hash),
            'versao'        => $laudo->versao ?? 1,
        ];
    }
}
