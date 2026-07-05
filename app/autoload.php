<?php
/**
 * VOXEL Copilot — Autoloader customizado
 * Compatível com hospedagem compartilhada sem Composer
 */
spl_autoload_register(function (string $class): void {
    $prefix   = 'App\\';
    $base_dir = __DIR__ . '/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
    $relative = substr($class, strlen($prefix));
    $file     = $base_dir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) require $file;
});
