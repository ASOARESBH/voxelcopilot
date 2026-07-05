<?php
namespace App\Core;

class View {
    private const ASSET_VERSION = '1.0.0';

    public static function render(string $view, array $data = [], string $layout = 'copilot'): void {
        if (!defined('ASSET_VERSION')) {
            define('ASSET_VERSION', self::ASSET_VERSION);
        }

        extract($data);

        $viewPath = APP_PATH . "/Views/{$view}.php";
        if (!file_exists($viewPath)) {
            throw new \Exception("View não encontrada: {$view}");
        }

        ob_start();
        try {
            require $viewPath;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        $content = ob_get_clean();

        $headerPath = APP_PATH . "/Views/layout/{$layout}_header.php";
        $footerPath = APP_PATH . "/Views/layout/{$layout}_footer.php";

        if (file_exists($headerPath)) require $headerPath;
        echo $content;
        if (file_exists($footerPath)) require $footerPath;
    }

    public static function asset(string $path): string {
        $v = defined('ASSET_VERSION') ? ASSET_VERSION : self::ASSET_VERSION;
        return '/assets/' . ltrim($path, '/') . '?v=' . $v;
    }
}
