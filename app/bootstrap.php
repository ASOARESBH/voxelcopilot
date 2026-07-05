<?php
/**
 * VOXEL Copilot — Bootstrap
 * Compatível com hospedagem compartilhada (HostGator / cPanel)
 * ORDEM OBRIGATÓRIA: ini_set → session → headers → autoload → env
 */

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH',  BASE_PATH . '/app');
define('STORAGE_PATH', BASE_PATH . '/storage');

// Configurações de erro
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', STORAGE_PATH . '/logs/php_errors.log');
error_reporting(E_ALL);

// ─── SESSÃO ──────────────────────────────────────────────────────────────────
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

$sessionPath = STORAGE_PATH . '/sessions';
if (!is_dir($sessionPath)) {
    @mkdir($sessionPath, 0755, true);
}
if (is_dir($sessionPath) && is_writable($sessionPath)) {
    session_save_path($sessionPath);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ─────────────────────────────────────────────────────────────────────────────

// Headers de segurança
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Autoloader customizado (sem dependência do Composer em produção)
require_once APP_PATH . '/autoload.php';

// Carregador de .env
if (!function_exists('loadEnv')) {
    function loadEnv(string $path): void {
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) continue;
            $name  = trim($parts[0]);
            $value = trim($parts[1]);
            if (preg_match('/^"(.*)"$/s', $value, $m)) $value = $m[1];
            elseif (preg_match("/^'(.*)'$/s", $value, $m)) $value = $m[1];
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("{$name}={$value}");
                $_ENV[$name]    = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

loadEnv(BASE_PATH . '/.env');

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Sao_Paulo');

if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
    ini_set('display_errors', 1);
}
