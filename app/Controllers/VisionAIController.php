<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Middlewares\AuthMiddleware;

class VisionAIController extends Controller {

    public function index(): void {
        AuthMiddleware::handle();

        $analises = [
            ['id'=>1,'paciente'=>'Maria Aparecida Santos','modalidade'=>'TC','descricao'=>'TC Tórax com contraste','data'=>'2025-07-05','status'=>'concluido','achados_ia'=>['Nódulo pulmonar LSD 8mm','Padrão em vidro fosco bilateral leve'],'confianca'=>92,'accession'=>'ACC001'],
            ['id'=>2,'paciente'=>'Carlos Eduardo Lima','modalidade'=>'TC','descricao'=>'TC Crânio sem contraste','data'=>'2025-07-05','status'=>'concluido','achados_ia'=>['Hipodensidade ACM esquerda','Edema cerebral leve'],'confianca'=>88,'accession'=>'ACC002'],
            ['id'=>3,'paciente'=>'Luciana Mendes Castro','modalidade'=>'MG','descricao'=>'Mamografia bilateral','data'=>'2025-07-05','status'=>'processando','achados_ia'=>[],'confianca'=>0,'accession'=>'ACC008'],
        ];

        $this->view('vision/index', [
            'title'        => 'Vision AI — VOXEL Copilot',
            'pageTitle'    => 'Vision AI',
            'pageSubtitle' => 'Análise automática de imagens DICOM por inteligência artificial',
            'analises'     => $analises,
        ]);
    }

    public function analisar(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $input      = json_decode(file_get_contents('php://input'), true);
        $studyUid   = $input['study_uid'] ?? '';
        $modalidade = $input['modalidade'] ?? '';

        if (!$studyUid) {
            echo json_encode(['error' => 'Study UID obrigatório']);
            return;
        }

        // Simulação de análise de imagem
        $resultado = [
            'ok'       => true,
            'achados'  => ['Análise em processamento — resultado disponível em instantes'],
            'confianca'=> 0,
            'status'   => 'processando',
        ];

        echo json_encode($resultado);
    }
}
