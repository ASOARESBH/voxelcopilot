<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Middlewares\AuthMiddleware;

class AnalyticsController extends Controller {

    public function index(): void {
        AuthMiddleware::handle();

        $pdo      = Database::getInstance();
        $medicoId = Auth::userId();
        $tenantId = Auth::tenantId();
        $periodo  = $_GET['periodo'] ?? '30d';

        // Dados reais se tiver tenant, simulados caso contrário
        $stats = $this->getStatsSimulados($periodo);

        $this->view('analytics/index', [
            'title'        => 'Analytics — VOXEL Copilot',
            'pageTitle'    => 'Analytics',
            'pageSubtitle' => 'Desempenho e produtividade clínica',
            'stats'        => $stats,
            'periodo'      => $periodo,
        ]);
    }

    private function getStatsSimulados(string $periodo): array {
        $multiplicador = match($periodo) {
            '7d'  => 0.25,
            '30d' => 1,
            '90d' => 3,
            '1y'  => 12,
            default => 1,
        };

        return [
            'total_laudos'       => (int)(127 * $multiplicador),
            'tempo_medio'        => '8min 42s',
            'taxa_revisao'       => '6.3%',
            'produtividade_hora' => 4.2,
            'por_modalidade'     => [
                ['modalidade'=>'TC',  'total'=>(int)(52*$multiplicador), 'cor'=>'#1a56db'],
                ['modalidade'=>'RM',  'total'=>(int)(38*$multiplicador), 'cor'=>'#7c3aed'],
                ['modalidade'=>'RX',  'total'=>(int)(21*$multiplicador), 'cor'=>'#059669'],
                ['modalidade'=>'US',  'total'=>(int)(10*$multiplicador), 'cor'=>'#d97706'],
                ['modalidade'=>'PET', 'total'=>(int)(6*$multiplicador),  'cor'=>'#dc2626'],
            ],
            'por_dia'            => $this->gerarDadosDiarios($periodo),
            'ia_stats'           => [
                'sugestoes_aceitas'  => (int)(89 * $multiplicador),
                'sugestoes_editadas' => (int)(31 * $multiplicador),
                'sugestoes_rejeitadas'=> (int)(7 * $multiplicador),
                'tempo_economizado'  => (int)(4.5 * $multiplicador) . 'h',
            ],
        ];
    }

    private function gerarDadosDiarios(string $periodo): array {
        $dias = match($periodo) { '7d'=>7, '30d'=>30, '90d'=>90, default=>30 };
        $dados = [];
        for ($i = $dias - 1; $i >= 0; $i--) {
            $dados[] = [
                'data'   => date('d/m', strtotime("-{$i} days")),
                'laudos' => rand(2, 12),
            ];
        }
        return $dados;
    }
}
