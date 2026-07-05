<?php
namespace App\Core;

class Logger {
    public static function error(string $message, array $context = []): void {
        self::write('ERROR', $message, $context);
    }
    public static function info(string $message, array $context = []): void {
        self::write('INFO', $message, $context);
    }
    private static function write(string $level, string $message, array $context): void {
        $logDir = STORAGE_PATH . '/logs';
        if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
        $date    = date('Y-m-d');
        $time    = date('Y-m-d H:i:s');
        $ctx     = empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        $line    = "[{$time}] [{$level}] {$message}{$ctx}" . PHP_EOL;
        @file_put_contents("{$logDir}/copilot-{$date}.log", $line, FILE_APPEND | LOCK_EX);
    }
}
