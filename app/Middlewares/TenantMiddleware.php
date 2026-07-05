<?php
namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Database;

class TenantMiddleware {
    public static function handle(): void {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        // Superadmin não precisa de tenant
        if (Auth::isPlatformAdmin()) return;

        $tenantId = Auth::tenantId();
        if (!$tenantId) {
            $tenants = Auth::userTenants();
            if (count($tenants) === 1) {
                Auth::setTenant((int) $tenants[0]->tenant_id);
            } else {
                header('Location: /selecionar-empresa');
                exit;
            }
        }

        // Verifica se tenant está ativo
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare("SELECT status FROM cop_tenants WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => Auth::tenantId()]);
        $tenant = $stmt->fetch();

        if (!$tenant || $tenant->status !== 'ativo') {
            Auth::logout();
            header('Location: /login?error=tenant_inativo');
            exit;
        }
    }
}
