<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Middlewares\AuthMiddleware;

class SpeechController extends Controller {

    public function index(): void {
        AuthMiddleware::handle();

        $historico = [
            ['id'=>1,'data'=>'2025-07-05 14:32','duracao'=>'2min 18s','palavras'=>312,'status'=>'transcrito','preview'=>'TC de tórax com contraste evidencia nódulo pulmonar no lobo superior direito...'],
            ['id'=>2,'data'=>'2025-07-05 11:15','duracao'=>'1min 45s','palavras'=>238,'status'=>'transcrito','preview'=>'Ressonância magnética do encéfalo sem contraste demonstra parênquima cerebral...'],
            ['id'=>3,'data'=>'2025-07-04 16:48','duracao'=>'3min 02s','palavras'=>415,'status'=>'transcrito','preview'=>'Ultrassonografia abdominal total revela fígado com dimensões normais...'],
        ];

        $this->view('speech/index', [
            'title'        => 'Speech — VOXEL Copilot',
            'pageTitle'    => 'Speech',
            'pageSubtitle' => 'Ditado de laudos por voz com transcrição automática',
            'historico'    => $historico,
        ]);
    }

    // API: transcrever áudio
    public function transcrever(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        if (empty($_FILES['audio'])) {
            echo json_encode(['error' => 'Arquivo de áudio não enviado']);
            return;
        }

        $arquivo = $_FILES['audio']['tmp_name'];
        $tipo    = $_FILES['audio']['type'];

        // Integração com Whisper API (OpenAI)
        try {
            $apiKey = $_ENV['OPENAI_API_KEY'] ?? '';
            if (!$apiKey) throw new \Exception('API Key não configurada');

            $ch = curl_init('https://api.openai.com/v1/audio/transcriptions');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
                CURLOPT_POSTFIELDS     => [
                    'file'  => new \CURLFile($arquivo, $tipo, 'audio.webm'),
                    'model' => 'whisper-1',
                    'language' => 'pt',
                    'prompt' => 'Laudo médico de radiologia em português brasileiro.',
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) throw new \Exception('Erro na API Whisper: ' . $response);

            $data = json_decode($response, true);
            echo json_encode(['ok' => true, 'texto' => $data['text'] ?? '']);
        } catch (\Throwable $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
