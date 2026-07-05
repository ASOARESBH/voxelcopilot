<?php
namespace App\Core;

class Router {
    private static array $routes = [];

    private static array $publicRoutes = [
        '/login',
        '/logout',
        '/cadastro',
        '/selecionar-empresa',
    ];

    public static function get(string $path, $handler): void {
        self::$routes[] = ['method' => 'GET', 'path' => $path, 'handler' => $handler];
    }

    public static function post(string $path, $handler): void {
        self::$routes[] = ['method' => 'POST', 'path' => $path, 'handler' => $handler];
    }

    private static function isPublicRoute(string $uri): bool {
        foreach (self::$publicRoutes as $pub) {
            if ($uri === $pub || strpos($uri, $pub) === 0) return true;
        }
        return false;
    }

    public static function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = strtok($_SERVER['REQUEST_URI'], '?');

        if (!self::isPublicRoute($uri) && !Auth::check()) {
            header('Location: /login');
            exit;
        }

        foreach (self::$routes as $route) {
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                if (is_callable($route['handler'])) {
                    try { call_user_func_array($route['handler'], $matches); }
                    catch (\Throwable $e) { self::handleError($e); }
                    return;
                }

                [$controllerName, $action] = explode('@', $route['handler']);

                // Suporte a controllers em subpastas (Platform\NomeController)
                if (strpos($controllerName, '\\') !== false) {
                    $class = "App\\Controllers\\{$controllerName}";
                } else {
                    $class = "App\\Controllers\\{$controllerName}";
                }

                if (!class_exists($class)) {
                    Logger::error("Controller não encontrado: {$class}");
                    self::renderErrorPage(500, "Controller não encontrado",
                        "O controlador <code>{$class}</code> não foi encontrado.");
                    return;
                }

                try {
                    $controller = new $class();
                    call_user_func_array([$controller, $action], $matches);
                } catch (\Throwable $e) {
                    self::handleError($e);
                }
                return;
            }
        }

        self::renderErrorPage(404, "Página não encontrada",
            "A página que você está procurando não existe ou foi movida.");
    }

    private static function handleError(\Throwable $e): void {
        http_response_code(500);
        Logger::error('Erro não tratado', [
            'exception' => $e->getMessage(),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
        ]);

        if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
            self::renderErrorPage(500, "Erro Interno",
                "<strong>" . htmlspecialchars($e->getMessage()) . "</strong><br><small>" .
                htmlspecialchars($e->getFile()) . " linha " . $e->getLine() . "</small>",
                $e->getTraceAsString());
        } else {
            self::renderErrorPage(500, "Erro Interno do Servidor",
                "Ocorreu um erro ao processar sua requisição. Tente novamente mais tarde.");
        }
    }

    private static function renderErrorPage(int $code, string $title, string $message, string $trace = ''): void {
        http_response_code($code);
        $icon    = $code === 404 ? '&#128269;' : '&#9888;&#65039;';
        $backUrl = Auth::isPlatformAdmin() ? '/platform/dashboard' : '/dashboard';
        echo '<!DOCTYPE html><html lang="pt-BR"><head>';
        echo '<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">';
        echo '<title>Erro ' . $code . ' — VOXEL Copilot</title>';
        echo '<style>';
        echo '*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}';
        echo 'body{font-family:system-ui,-apple-system,sans-serif;background:#020c1b;color:#e2e8f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}';
        echo '.ec{background:#0a1628;border:1px solid rgba(14,165,233,.2);border-radius:16px;padding:2.5rem;max-width:560px;width:100%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.6)}';
        echo '.ei{font-size:3rem;margin-bottom:1rem;display:block}';
        echo '.ecode{font-size:4rem;font-weight:800;background:linear-gradient(135deg,#0ea5e9,#06b6d4);-webkit-background-clip:text;-webkit-text-fill-color:transparent;line-height:1;margin-bottom:.5rem}';
        echo '.etitle{font-size:1.2rem;font-weight:600;color:#e2e8f0;margin-bottom:.75rem}';
        echo '.emsg{font-size:.9rem;color:#6b8aad;line-height:1.6;margin-bottom:1.5rem}';
        echo '.emsg code{background:rgba(14,165,233,.1);padding:.1em .35em;border-radius:4px;font-size:.85em;color:#0ea5e9}';
        echo '.etrace{background:#060f1a;border:1px solid rgba(14,165,233,.15);border-radius:8px;padding:1rem;text-align:left;font-size:.72rem;color:#64748b;overflow:auto;max-height:200px;margin-bottom:1.5rem;font-family:monospace;white-space:pre-wrap;word-break:break-all}';
        echo '.btn{display:inline-flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,#0ea5e9,#06b6d4);color:#fff;padding:.65rem 1.5rem;border-radius:10px;text-decoration:none;font-weight:600;font-size:.9rem}';
        echo '.brand{margin-top:1.5rem;font-size:.75rem;color:#2d4a6a;letter-spacing:.05em}';
        echo '</style></head><body>';
        echo '<div class="ec">';
        echo '<span class="ei">' . $icon . '</span>';
        echo '<div class="ecode">' . $code . '</div>';
        echo '<div class="etitle">' . htmlspecialchars($title) . '</div>';
        echo '<div class="emsg">' . $message . '</div>';
        if ($trace) echo '<div class="etrace">' . htmlspecialchars($trace) . '</div>';
        echo '<a href="' . $backUrl . '" class="btn">&#8592; Voltar</a>';
        echo '<div class="brand">VOXEL Copilot</div>';
        echo '</div></body></html>';
        exit;
    }
}
