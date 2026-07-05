<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Middlewares\AuthMiddleware;

class FilaController extends Controller {

    public function index(): void {
        AuthMiddleware::handle();
        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId();

        // Filtros
        $filtroStatus     = $_GET['status']     ?? 'todos';
        $filtroModalidade = $_GET['modalidade'] ?? 'todas';
        $filtroPrioridade = $_GET['prioridade'] ?? 'todas';
        $busca            = trim($_GET['busca']  ?? '');

        // Dados simulados da fila (integração PACS virá depois)
        $fila = $this->gerarFilaSimulada();

        // Aplica filtros
        if ($filtroStatus !== 'todos') {
            $fila = array_filter($fila, fn($e) => $e['status'] === $filtroStatus);
        }
        if ($filtroModalidade !== 'todas') {
            $fila = array_filter($fila, fn($e) => $e['modalidade'] === $filtroModalidade);
        }
        if ($filtroPrioridade !== 'todas') {
            $fila = array_filter($fila, fn($e) => $e['prioridade'] === $filtroPrioridade);
        }
        if ($busca) {
            $fila = array_filter($fila, fn($e) =>
                stripos($e['paciente'], $busca) !== false ||
                stripos($e['accession'], $busca) !== false
            );
        }

        $stats = [
            'urgentes'    => count(array_filter($this->gerarFilaSimulada(), fn($e) => $e['prioridade'] === 'urgente')),
            'normais'     => count(array_filter($this->gerarFilaSimulada(), fn($e) => $e['prioridade'] === 'normal')),
            'oncologicos' => count(array_filter($this->gerarFilaSimulada(), fn($e) => in_array('Oncológico', $e['tags']))),
            'total'       => count($this->gerarFilaSimulada()),
        ];

        $this->view('fila/index', [
            'title'            => 'Fila Inteligente — VOXEL Copilot',
            'pageTitle'        => 'Fila Inteligente',
            'pageSubtitle'     => 'Exames aguardando laudo · ordenados por IA',
            'fila'             => array_values($fila),
            'stats'            => $stats,
            'filtroStatus'     => $filtroStatus,
            'filtroModalidade' => $filtroModalidade,
            'filtroPrioridade' => $filtroPrioridade,
            'busca'            => $busca,
        ]);
    }

    private function gerarFilaSimulada(): array {
        return [
            ['id'=>1,'accession'=>'ACC001','paciente'=>'Maria Aparecida Santos','idade'=>62,'sexo'=>'F','modalidade'=>'TC','descricao'=>'TC Tórax com contraste · 128 cortes','prioridade'=>'urgente','tags'=>['Oncológico','IA pronta'],'tempo_espera'=>'45 min','porta_laudo'=>null,'status'=>'aguardando','ia_sugestao'=>'Nódulo pulmonar suspeito — comparar com PET anterior'],
            ['id'=>2,'accession'=>'ACC002','paciente'=>'Carlos Eduardo Lima','idade'=>71,'sexo'=>'M','modalidade'=>'TC','descricao'=>'TC Crânio sem contraste · Urgência','prioridade'=>'urgente','tags'=>['AVC','Alta prioridade'],'tempo_espera'=>'12 min','porta_laudo'=>'12 min','status'=>'aguardando','ia_sugestao'=>'Hipodensidade em território da ACM — possível AVC isquêmico'],
            ['id'=>3,'accession'=>'ACC003','paciente'=>'Roberto Ferreira Costa','idade'=>58,'sexo'=>'M','modalidade'=>'RM','descricao'=>'RM Cardíaca · Função ventricular','prioridade'=>'normal','tags'=>['Cardio','Template sugerido'],'tempo_espera'=>'1h 20min','porta_laudo'=>null,'status'=>'aguardando','ia_sugestao'=>'FE estimada 42% — padrão de disfunção sistólica leve'],
            ['id'=>4,'accession'=>'ACC004','paciente'=>'Ana Paula Rodrigues','idade'=>45,'sexo'=>'F','modalidade'=>'RM','descricao'=>'RM Encéfalo com gadolínio','prioridade'=>'normal','tags'=>['Neurológico'],'tempo_espera'=>'2h 10min','porta_laudo'=>null,'status'=>'aguardando','ia_sugestao'=>'Realce meníngeo focal — sugestivo de meningioma'],
            ['id'=>5,'accession'=>'ACC005','paciente'=>'José Antonio Pereira','idade'=>67,'sexo'=>'M','modalidade'=>'PET','descricao'=>'PET-CT Corpo inteiro · Estadiamento','prioridade'=>'urgente','tags'=>['Oncológico','PET'],'tempo_espera'=>'30 min','porta_laudo'=>null,'status'=>'aguardando','ia_sugestao'=>'Hipercaptação mediastinal — linfoma a excluir'],
            ['id'=>6,'accession'=>'ACC006','paciente'=>'Fernanda Lima Souza','idade'=>38,'sexo'=>'F','modalidade'=>'US','descricao'=>'Ultrassonografia abdominal total','prioridade'=>'normal','tags'=>['Rotina'],'tempo_espera'=>'3h 05min','porta_laudo'=>null,'status'=>'aguardando','ia_sugestao'=>null],
            ['id'=>7,'accession'=>'ACC007','paciente'=>'Marcos Vinícius Alves','idade'=>55,'sexo'=>'M','modalidade'=>'RX','descricao'=>'Radiografia de tórax PA e perfil','prioridade'=>'normal','tags'=>['Rotina','Comparativo'],'tempo_espera'=>'4h 20min','porta_laudo'=>null,'status'=>'aguardando','ia_sugestao'=>'Comparativo disponível — RX 2023 com padrão similar'],
            ['id'=>8,'accession'=>'ACC008','paciente'=>'Luciana Mendes Castro','idade'=>49,'sexo'=>'F','modalidade'=>'MG','descricao'=>'Mamografia bilateral · Rastreamento','prioridade'=>'normal','tags'=>['Oncológico','Rastreamento'],'tempo_espera'=>'5h 00min','porta_laudo'=>null,'status'=>'aguardando','ia_sugestao'=>'BIRADS 3 sugerido — nódulo oval de contornos definidos'],
            ['id'=>9,'accession'=>'ACC009','paciente'=>'Ricardo Santos Oliveira','idade'=>72,'sexo'=>'M','modalidade'=>'TC','descricao'=>'TC Abdome e pelve com contraste','prioridade'=>'normal','tags'=>['Rotina'],'tempo_espera'=>'5h 30min','porta_laudo'=>null,'status'=>'aguardando','ia_sugestao'=>null],
            ['id'=>10,'accession'=>'ACC010','paciente'=>'Patrícia Gomes Ferreira','idade'=>33,'sexo'=>'F','modalidade'=>'RM','descricao'=>'RM Coluna lombar sem contraste','prioridade'=>'normal','tags'=>['Musculoesquelético'],'tempo_espera'=>'6h 00min','porta_laudo'=>null,'status'=>'aguardando','ia_sugestao'=>'Protrusão discal L4-L5 com compressão radicular'],
        ];
    }
}
