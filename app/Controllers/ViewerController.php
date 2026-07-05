<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Middlewares\AuthMiddleware;

class ViewerController extends Controller {

    public function index(): void {
        AuthMiddleware::handle();

        $studyUid  = $_GET['study']  ?? '';
        $pacsUrl   = $_ENV['PACS_URL'] ?? 'http://localhost:8042';

        // Se vier com study_uid, abre o viewer diretamente
        if ($studyUid) {
            $viewerUrl = rtrim($pacsUrl, '/') . '/web-viewer/app/viewer.html?StudyInstanceUIDs=' . urlencode($studyUid);
        } else {
            $viewerUrl = null;
        }

        // Estudos recentes para seleção
        $estudosRecentes = [
            ['study_uid'=>'1.2.840.10008.5.1.4.1.1.2.001','paciente'=>'Maria Aparecida Santos','modalidade'=>'TC','descricao'=>'TC Tórax com contraste','data'=>'2025-07-05','accession'=>'ACC001'],
            ['study_uid'=>'1.2.840.10008.5.1.4.1.1.2.002','paciente'=>'Carlos Eduardo Lima','modalidade'=>'TC','descricao'=>'TC Crânio sem contraste','data'=>'2025-07-05','accession'=>'ACC002'],
            ['study_uid'=>'1.2.840.10008.5.1.4.1.1.4.001','paciente'=>'Roberto Ferreira Costa','modalidade'=>'RM','descricao'=>'RM Cardíaca','data'=>'2025-07-05','accession'=>'ACC003'],
            ['study_uid'=>'1.2.840.10008.5.1.4.1.1.4.002','paciente'=>'Ana Paula Rodrigues','modalidade'=>'RM','descricao'=>'RM Encéfalo com gadolínio','data'=>'2025-07-04','accession'=>'ACC004'],
            ['study_uid'=>'1.2.840.10008.5.1.4.1.1.128.001','paciente'=>'José Antonio Pereira','modalidade'=>'PET','descricao'=>'PET-CT Corpo inteiro','data'=>'2025-07-05','accession'=>'ACC005'],
        ];

        $this->view('viewer/index', [
            'title'           => 'Viewer DICOM — VOXEL Copilot',
            'pageTitle'       => 'Viewer',
            'pageSubtitle'    => 'Visualizador DICOM integrado ao VOXEL PACS',
            'viewerUrl'       => $viewerUrl,
            'studyUid'        => $studyUid,
            'pacsUrl'         => $pacsUrl,
            'estudosRecentes' => $estudosRecentes,
        ]);
    }
}
