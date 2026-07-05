<?php
namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Database;

class TenantMiddleware {
    /**
     * Verifica autenticação e, se houver tenant vinculado, valida se está ativo.
     * Médicos sem tenant (modo standalone) passam sem bloqueio.
     */
    public static function handle(): void {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        // Superadmin não precisa de tenant
        if (Auth::isPlatformAdmin()) return;

        $tenantId = Auth::tenantId();

        // Tenta resolver tenant automaticamente se houver exatamente 1 vínculo
        if (!$tenantId) {
            $tenants = Auth::userTenants();
            if (count($tenants) === 1) {
                Auth::setTenant((int) $tenants[0]->tenant_id);
                $tenantId = Auth::tenantId();
            } elseif (count($tenants) > 1) {
                // Múltiplos tenants: redireciona para seleção
                header('Location: /selecionar-empresa');
                exit;
            }
            // count === 0: modo standalone — permite acesso sem tenant
        }

        // Se há tenant definido, verifica se está ativo
        if ($tenantId) {
            $pdo  = Database::getInstance();
            $stmt = $pdo->prepare("SELECT status FROM cop_tenants WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $tenantId]);
            $tenant = $stmt->fetch();

            if (!$tenant || $tenant->status !== 'ativo') {
                Auth::logout();
                header('Location: /login?error=tenant_inativo');
                exit;
            }
        }
    }
}
