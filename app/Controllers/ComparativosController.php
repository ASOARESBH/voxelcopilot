<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Middlewares\AuthMiddleware;

class ComparativosController extends Controller {

    public function index(): void {
        AuthMiddleware::handle();

        $comparativos = [
            ['id'=>1,'paciente'=>'Maria Aparecida Santos','modalidade'=>'TC','descricao_atual'=>'TC Tórax com contraste 2025','descricao_anterior'=>'TC Tórax com contraste 2023','data_atual'=>'2025-07-05','data_anterior'=>'2023-06-12','status'=>'pendente','ia_delta'=>'Aumento de 4mm no nódulo do LSD — progressão suspeita'],
            ['id'=>2,'paciente'=>'Marcos Vinícius Alves','modalidade'=>'RX','descricao_atual'=>'RX Tórax 2025','descricao_anterior'=>'RX Tórax 2024','data_atual'=>'2025-07-03','data_anterior'=>'2024-08-20','status'=>'pendente','ia_delta'=>'Padrão similar — sem alterações significativas'],
            ['id'=>3,'paciente'=>'João Carlos Pereira','modalidade'=>'RM','descricao_atual'=>'RM Encéfalo 2025','descricao_anterior'=>'RM Encéfalo 2020','data_atual'=>'2025-07-05','data_anterior'=>'2020-11-15','status'=>'concluido','ia_delta'=>'Atrofia cortical leve — compatível com envelhecimento'],
            ['id'=>4,'paciente'=>'Ricardo Santos Oliveira','modalidade'=>'TC','descricao_atual'=>'TC Abdome 2025','descricao_anterior'=>'TC Abdome 2024','data_atual'=>'2025-07-05','data_anterior'=>'2024-03-10','status'=>'pendente','ia_delta'=>'Cisto hepático estável — sem crescimento'],
        ];

        $this->view('comparativos/index', [
            'title'        => 'Comparativos — VOXEL Copilot',
            'pageTitle'    => 'Comparativos',
            'pageSubtitle' => 'Análise evolutiva de exames pelo Copilot IA',
            'comparativos' => $comparativos,
        ]);
    }

    public function show(int $id): void {
        AuthMiddleware::handle();

        $this->view('comparativos/show', [
            'title'        => 'Comparativo — VOXEL Copilot',
            'pageTitle'    => 'Comparativo de Exames',
            'pageSubtitle' => 'Análise lado a lado com IA',
            'id'           => $id,
        ]);
    }
}
