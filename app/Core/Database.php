<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                $host    = $_ENV['DB_HOST']     ?? 'localhost';
                $db      = $_ENV['DB_DATABASE'] ?? 'voxel_copilot';
                $user    = $_ENV['DB_USERNAME'] ?? 'root';
                $pass    = $_ENV['DB_PASSWORD'] ?? '';
                $port    = $_ENV['DB_PORT']     ?? '3306';
                $charset = 'utf8mb4';

                $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                Logger::error('Falha na conexão com o banco de dados', ['message' => $e->getMessage()]);
                throw new \RuntimeException('Erro de conexão com o banco de dados.', 500);
            }
        }
        return self::$instance;
    }

    public static function executeWrite(string $sql, array $params = []): bool {
        try {
            $stmt = self::getInstance()->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            Logger::error('Erro em operação de escrita', [
                'sql'    => $sql,
                'params' => $params,
                'error'  => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
