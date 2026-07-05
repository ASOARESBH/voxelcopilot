<?php
namespace App\Core\Audit;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Logger;

class AuditLogger {
    public static function log(
        string $action,
        string $entity   = '',
        ?int   $entityId = null,
        array  $details  = []
    ): void {
        try {
            $pdo = Database::getInstance();
            $pdo->prepare("
                INSERT INTO cop_audit_logs (tenant_id, user_id, action, entity, entity_id, details, ip, created_at)
                VALUES (:tenant_id, :user_id, :action, :entity, :entity_id, :details, :ip, NOW())
            ")->execute([
                'tenant_id' => Auth::tenantId(),
                'user_id'   => Auth::userId(),
                'action'    => $action,
                'entity'    => $entity,
                'entity_id' => $entityId,
                'details'   => json_encode($details, JSON_UNESCAPED_UNICODE),
                'ip'        => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Logger::error('Falha ao registrar auditoria', ['error' => $e->getMessage()]);
        }
    }
}
