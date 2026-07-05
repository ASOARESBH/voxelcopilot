<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;

class IntegracoesController extends Controller {

    public function index(): void {
        AuthMiddleware::handle();

        $integracoes = [
            ['id'=>1,'nome'=>'VOXEL PACS','tipo'=>'PACS','status'=>'conectado','url'=>$_ENV['PACS_URL'] ?? 'http://localhost:8042','protocolo'=>'REST/DICOM','icone'=>'fa-server','cor'=>'#1a56db','ultima_sync'=>'2025-07-05 14:30'],
            ['id'=>2,'nome'=>'OpenAI GPT-4o','tipo'=>'IA','status'=>'conectado','url'=>'https://api.openai.com','protocolo'=>'REST','icone'=>'fa-robot','cor'=>'#059669','ultima_sync'=>'2025-07-05 14:45'],
            ['id'=>3,'nome'=>'Whisper API','tipo'=>'Speech','status'=>'conectado','url'=>'https://api.openai.com/v1/audio','protocolo'=>'REST','icone'=>'fa-microphone','cor'=>'#7c3aed','ultima_sync'=>'2025-07-05 13:00'],
            ['id'=>4,'nome'=>'HL7 FHIR','tipo'=>'HIS','status'=>'desconectado','url'=>'','protocolo'=>'HL7 FHIR R4','icone'=>'fa-hospital','cor'=>'#d97706','ultima_sync'=>null],
            ['id'=>5,'nome'=>'RIS','tipo'=>'RIS','status'=>'desconectado','url'=>'','protocolo'=>'HL7 v2','icone'=>'fa-list-check','cor'=>'#94a3b8','ultima_sync'=>null],
        ];

        $this->view('integracoes/index', [
            'title'        => 'Integrações — VOXEL Copilot',
            'pageTitle'    => 'Integrações',
            'pageSubtitle' => 'Conexões com PACS, HIS, RIS e serviços externos',
            'integracoes'  => $integracoes,
        ]);
    }

    public function testar(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $tipo  = $input['tipo'] ?? '';
        $url   = $input['url']  ?? '';

        if ($tipo === 'PACS') {
            try {
                $ch = curl_init(rtrim($url, '/') . '/system');
                curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>5]);
                $resp = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                echo json_encode(['ok' => $code === 200, 'status' => $code]);
            } catch (\Throwable $e) {
                echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['ok' => true, 'status' => 'simulado']);
        }
    }
}
