<?php
namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Logger;

class PacsService {

    private string $baseUrl;
    private string $token;

    public function __construct(?string $baseUrl = null, ?string $token = null) {
        if ($baseUrl && $token) {
            $this->baseUrl = rtrim($baseUrl, '/');
            $this->token   = $token;
        } else {
            // Carrega configuração do tenant atual
            $pdo  = Database::getInstance();
            $stmt = $pdo->prepare("SELECT pacs_api_url, pacs_api_token FROM cop_tenants WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => Auth::tenantId()]);
            $config = $stmt->fetch();

            $this->baseUrl = rtrim($config?->pacs_api_url ?? $_ENV['PACS_API_URL'] ?? '', '/');
            $this->token   = $config?->pacs_api_token ?? $_ENV['PACS_API_TOKEN'] ?? '';
        }
    }

    /**
     * Busca estudos do PACS com filtros
     */
    public function buscarEstudos(array $filtros = []): array {
        $params = http_build_query(array_filter([
            'PatientName'   => $filtros['paciente']   ?? null,
            'PatientID'     => $filtros['patient_id'] ?? null,
            'StudyDate'     => $filtros['data']        ?? null,
            'Modality'      => $filtros['modalidade']  ?? null,
            'limit'         => $filtros['limit']       ?? 50,
        ]));

        $response = $this->get("/api/studies?{$params}");
        return $response['data'] ?? $response ?? [];
    }

    /**
     * Busca detalhes de um estudo pelo StudyInstanceUID
     */
    public function buscarEstudo(string $studyUid): ?object {
        $response = $this->get("/api/studies/{$studyUid}");
        if (empty($response)) return null;
        return (object) $response;
    }

    /**
     * Busca séries de um estudo
     */
    public function buscarSeries(string $studyUid): array {
        $response = $this->get("/api/studies/{$studyUid}/series");
        return $response['data'] ?? $response ?? [];
    }

    /**
     * Retorna a URL do viewer OHIF/Orthanc para um estudo
     */
    public function urlViewer(string $studyUid): string {
        return "{$this->baseUrl}/viewer?StudyInstanceUIDs={$studyUid}";
    }

    /**
     * Retorna a URL do thumbnail de uma série
     */
    public function urlThumbnail(string $studyUid, string $seriesUid): string {
        return "{$this->baseUrl}/api/series/{$seriesUid}/thumbnail?token={$this->token}";
    }

    /**
     * Verifica se a conexão com o PACS está ativa
     */
    public function testarConexao(): array {
        try {
            $response = $this->get('/api/health');
            return ['ok' => true, 'data' => $response];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    // ─── HTTP ─────────────────────────────────────────────────────────────────

    private function get(string $path): array {
        if (empty($this->baseUrl)) {
            throw new \RuntimeException('URL do PACS não configurada.');
        }

        $url = $this->baseUrl . $path;
        $ch  = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$this->token}",
                "Accept: application/json",
                "X-Copilot-Tenant: " . (Auth::tenantId() ?? ''),
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $body  = curl_exec($ch);
        $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Logger::error('PACS cURL error', ['url' => $url, 'error' => $error]);
            throw new \RuntimeException("Erro ao conectar ao PACS: {$error}");
        }

        if ($code >= 400) {
            Logger::error('PACS HTTP error', ['url' => $url, 'code' => $code, 'body' => $body]);
            throw new \RuntimeException("PACS retornou erro HTTP {$code}");
        }

        $decoded = json_decode($body, true);
        return $decoded ?? [];
    }
}
