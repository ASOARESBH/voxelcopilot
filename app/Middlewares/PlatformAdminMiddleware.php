<?php
namespace App\Middlewares;

use App\Core\Auth;

class PlatformAdminMiddleware {
    public static function handle(): void {
        if (!Auth::check() || !Auth::isPlatformAdmin()) {
            header('Location: /login');
            exit;
        }
    }
}
