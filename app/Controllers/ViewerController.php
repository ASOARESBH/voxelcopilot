<?php
namespace App\Controllers;

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

        // ── Cards de resumo ──────────────────────────────────────────
        $stats = [
            'estudos_hoje'  => 245,
            'recentes'      => 18,
            'favoritos'     => 12,
            'ultimo_acesso' => '15:42',
        ];

        // ── Estudos recentes (DataGrid) ───────────────────────────────
        $estudosRecentes = [
            ['study_uid'=>'1.2.840.10008.5.1.4.1.1.2.001','paciente'=>'Maria Aparecida Santos','sexo'=>'F','idade'=>58,'modalidade'=>'TC','descricao'=>'TC Tórax com contraste','instituicao'=>'Hospital Einstein','data'=>'2026-07-08','hora'=>'15:42','accession'=>'ACC-2026-0001','status'=>'disponivel','ia'=>['Pulmão','Nódulo'],'comparativo'=>true,'favorito'=>true,'urgente'=>false],
            ['study_uid'=>'1.2.840.10008.5.1.4.1.1.2.002','paciente'=>'Carlos Eduardo Lima','sexo'=>'M','idade'=>67,'modalidade'=>'TC','descricao'=>'TC Crânio sem contraste','instituicao'=>'Hospital Sírio-Libanês','data'=>'2026-07-08','hora'=>'14:11','accession'=>'ACC-2026-0002','status'=>'processando','ia'=>['AVC'],'comparativo'=>false,'favorito'=>false,'urgente'=>true],
            ['study_uid'=>'1.2.840.10008.5.1.4.1.1.4.001','paciente'=>'Roberto Ferreira Costa','sexo'=>'M','idade'=>72,'modalidade'=>'RM','descricao'=>'RM Cardíaca — função ventricular','instituicao'=>'Hospital das Clínicas','data'=>'2026-07-08','hora'=>'13:08','accession'=>'ACC-2026-0003','status'=>'disponivel','ia'=>['Fratura'],'comparativo'=>true,'favorito'=>true,'urgente'=>false],
            ['study_uid'=>'1.2.840.10008.5.1.4.1.1.4.002','paciente'=>'Ana Paula Rodrigues','sexo'=>'F','idade'=>41,'modalidade'=>'RM','descricao'=>'RM Encéfalo com gadolínio','instituicao'=>'Hospital Albert Einstein','data'=>'2026-07-07','hora'=>'18:27','accession'=>'ACC-2026-0004','status'=>'disponivel','ia'=>['AVC','Nódulo'],'comparativo'=>false,'favorito'=>false,'urgente'=>false],
            ['study_uid'=>'1.2.840.10008.5.1.4.1.1.128.001','paciente'=>'José Antonio Pereira','sexo'=>'M','idade'=>63,'modalidade'=>'PET','descricao'=>'PET-CT corpo inteiro — estadiamento','instituicao'=>'Hospital INCA','data'=>'2026-07-07','hora'=>'11:52','accession'=>'ACC-2026-0005','status'=>'erro','ia'=>[],'comparativo'=>true,'favorito'=>true,'urgente'=>false],
            ['study_uid'=>'1.2.840.10008.5.1.4.1.1.1.001','paciente'=>'Fernanda Costa Almeida','sexo'=>'F','idade'=>34,'modalidade'=>'CR','descricao'=>'Radiografia de tórax PA e perfil','instituicao'=>'Clínica Santa Helena','data'=>'2026-07-07','hora'=>'09:15','accession'=>'ACC-2026-0006','status'=>'disponivel','ia'=>['Pulmão'],'comparativo'=>false,'favorito'=>false,'urgente'=>false],
            ['study_uid'=>'1.2.840.10008.5.1.4.1.1.6.001','paciente'=>'Paulo Henrique Souza','sexo'=>'M','idade'=>55,'modalidade'=>'US','descricao'=>'Ultrassom de abdome total','instituicao'=>'Hospital Einstein','data'=>'2026-07-06','hora'=>'16:33','accession'=>'ACC-2026-0007','status'=>'disponivel','ia'=>[],'comparativo'=>false,'favorito'=>false,'urgente'=>false],
            ['study_uid'=>'1.2.840.10008.5.1.4.1.1.1.2.001','paciente'=>'Beatriz Nunes Oliveira','sexo'=>'F','idade'=>29,'modalidade'=>'MG','descricao'=>'Mamografia digital bilateral','instituicao'=>'Clínica Santa Helena','data'=>'2026-07-06','hora'=>'10:04','accession'=>'ACC-2026-0008','status'=>'disponivel','ia'=>['Nódulo'],'comparativo'=>true,'favorito'=>false,'urgente'=>false],
        ];

        // ── Painel lateral direito ───────────────────────────────────
        $ultimosAcessos = [
            ['hora'=>'15:42','paciente'=>'Maria Aparecida Santos','descricao'=>'TC Crânio','study_uid'=>'1.2.840.10008.5.1.4.1.1.2.001'],
            ['hora'=>'14:11','paciente'=>'Carlos Eduardo Lima','descricao'=>'RM Coluna','study_uid'=>'1.2.840.10008.5.1.4.1.1.2.002'],
            ['hora'=>'13:08','paciente'=>'Roberto Ferreira Costa','descricao'=>'PET CT','study_uid'=>'1.2.840.10008.5.1.4.1.1.4.001'],
            ['hora'=>'11:52','paciente'=>'José Antonio Pereira','descricao'=>'PET-CT corpo inteiro','study_uid'=>'1.2.840.10008.5.1.4.1.1.128.001'],
        ];

        // Metadados de equipamento/séries usados no drawer de detalhes do estudo
        $equipamentoPorModalidade = [
            'TC'  => 'Philips Ingenuity CT 128',
            'RM'  => 'Siemens Magnetom Skyra 3T',
            'PET' => 'GE Discovery PET/CT 710',
            'CR'  => 'Carestream DRX-Evolution',
            'US'  => 'Philips Affiniti 70',
            'MG'  => 'Hologic 3Dimensions',
        ];
        foreach ($estudosRecentes as &$estudo) {
            $estudo['equipamento']  = $equipamentoPorModalidade[$estudo['modalidade']] ?? 'Equipamento não informado';
            $estudo['series']       = match ($estudo['modalidade']) {
                'TC', 'RM' => random_int(3, 8),
                'PET'      => random_int(2, 4),
                default    => random_int(1, 2),
            };
            $estudo['num_imagens']  = match ($estudo['modalidade']) {
                'TC'  => random_int(150, 600),
                'RM'  => random_int(80, 300),
                'PET' => random_int(200, 450),
                default => random_int(1, 4),
            };
            $estudo['tamanho_mb']   = round($estudo['num_imagens'] * 0.6, 1);
        }
        unset($estudo);

        $favoritosList = array_values(array_filter($estudosRecentes, fn($e) => $e['favorito']));

        $compartilhados = [
            ['paciente'=>'Maria Aparecida Santos','descricao'=>'TC Tórax com contraste','com'=>'Dr. Ricardo Alves','quando'=>'há 2h'],
            ['paciente'=>'José Antonio Pereira','descricao'=>'PET-CT corpo inteiro','com'=>'Dra. Camila Torres','quando'=>'há 5h'],
        ];

        $comparativosList = [
            ['paciente'=>'Roberto Ferreira Costa','descricao'=>'RM Cardíaca vs. exame 2024','pct'=>87],
            ['paciente'=>'Beatriz Nunes Oliveira','descricao'=>'Mamografia bilateral vs. exame anterior','pct'=>74],
        ];

        $alertasIA = [
            ['tipo'=>'critico','texto'=>'Possível AVC agudo detectado — Carlos Eduardo Lima','quando'=>'há 12 min'],
            ['tipo'=>'atencao','texto'=>'Nódulo pulmonar com crescimento — Maria Aparecida Santos','quando'=>'há 40 min'],
            ['tipo'=>'info','texto'=>'Comparativo automático disponível — Beatriz Nunes Oliveira','quando'=>'há 1h'],
        ];

        $this->view('viewer/index', [
            'title'            => 'Viewer DICOM — VOXEL Copilot',
            'pageTitle'        => 'Viewer',
            'pageSubtitle'     => 'Visualizador DICOM integrado ao VOXEL PACS',
            'viewerUrl'        => $viewerUrl,
            'studyUid'         => $studyUid,
            'pacsUrl'          => $pacsUrl,
            'stats'            => $stats,
            'estudosRecentes'  => $estudosRecentes,
            'ultimosAcessos'   => $ultimosAcessos,
            'favoritosList'    => $favoritosList,
            'compartilhados'   => $compartilhados,
            'comparativosList' => $comparativosList,
            'alertasIA'        => $alertasIA,
        ]);
    }
}
