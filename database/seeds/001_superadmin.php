<?php
/**
 * Gerador de hash para o superadmin
 * Execute: php database/seeds/001_superadmin.php
 * Copie o hash gerado para o SQL de migration
 */
$senha = 'Admin259087@';
$hash  = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
echo "Hash para '{$senha}':" . PHP_EOL;
echo $hash . PHP_EOL;
echo PHP_EOL;
echo "SQL:" . PHP_EOL;
echo "UPDATE cop_users SET password = '{$hash}' WHERE email = 'admin@voxelpacs.com.br';" . PHP_EOL;
