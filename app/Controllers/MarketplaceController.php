<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;

class MarketplaceController extends Controller {

    public function index(): void {
        AuthMiddleware::handle();

        $plugins = [
            ['id'=>1,'nome'=>'Lung AI','categoria'=>'Vision AI','descricao'=>'Detecção automática de nódulos pulmonares com 94% de sensibilidade.','preco'=>'R$ 299/mês','instalado'=>true,'versao'=>'2.1.0','icone'=>'fa-lungs','cor'=>'#1a56db'],
            ['id'=>2,'nome'=>'Cardio AI','categoria'=>'Vision AI','descricao'=>'Análise de função ventricular e detecção de isquemia em RM cardíaca.','preco'=>'R$ 399/mês','instalado'=>true,'versao'=>'1.8.2','icone'=>'fa-heart-pulse','cor'=>'#dc2626'],
            ['id'=>3,'nome'=>'Speech Pro','categoria'=>'Speech','descricao'=>'Ditado de laudos com vocabulário médico especializado e pontuação automática.','preco'=>'R$ 149/mês','instalado'=>true,'versao'=>'3.0.1','icone'=>'fa-microphone','cor'=>'#059669'],
            ['id'=>4,'nome'=>'Research Assistant','categoria'=>'Pesquisa','descricao'=>'Acesso a base de dados com 50.000+ artigos de radiologia indexados.','preco'=>'R$ 199/mês','instalado'=>true,'versao'=>'1.2.0','icone'=>'fa-flask','cor'=>'#7c3aed'],
            ['id'=>5,'nome'=>'Workflow Optimizer','categoria'=>'Workflow','descricao'=>'Priorização inteligente da fila com base em urgência clínica e SLA.','preco'=>'R$ 249/mês','instalado'=>true,'versao'=>'1.5.3','icone'=>'fa-diagram-project','cor'=>'#d97706'],
            ['id'=>6,'nome'=>'Neuro AI','categoria'=>'Vision AI','descricao'=>'Segmentação automática de estruturas cerebrais e detecção de AVC.','preco'=>'R$ 449/mês','instalado'=>false,'versao'=>'1.0.0','icone'=>'fa-brain','cor'=>'#0284c7'],
            ['id'=>7,'nome'=>'Mammo AI','categoria'=>'Vision AI','descricao'=>'Classificação BIRADS automática com análise de densidade mamária.','preco'=>'R$ 349/mês','instalado'=>false,'versao'=>'2.0.0','icone'=>'fa-circle-dot','cor'=>'#db2777'],
            ['id'=>8,'nome'=>'Report Builder','categoria'=>'Templates','descricao'=>'Construtor visual de templates com IA generativa para cada especialidade.','preco'=>'R$ 179/mês','instalado'=>false,'versao'=>'1.1.0','icone'=>'fa-file-pen','cor'=>'#0891b2'],
        ];

        $categorias = array_unique(array_column($plugins, 'categoria'));

        $this->view('marketplace/index', [
            'title'        => 'Marketplace — VOXEL Copilot',
            'pageTitle'    => 'Marketplace',
            'pageSubtitle' => 'Módulos e plugins para expandir seu Copilot',
            'plugins'      => $plugins,
            'categorias'   => $categorias,
        ]);
    }
}
