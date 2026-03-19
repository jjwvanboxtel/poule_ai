<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use PDO;
use PDOException;
use RuntimeException;

final class ConnectionFactory
{
    /**
     * Create a PDO connection from a configuration array.
     *
     * @param array{host: string, port: int, name: string, user: string, password: string, charset: string, options?: array<int, mixed>} $config
     */
    public static function fromConfig(array $config): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['name'],
            $config['charset'],
        );

        try {
            $pdo = new PDO(
                $dsn,
                $config['user'],
                $config['password'],
                $config['options'] ?? [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ],
            );
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Database connection failed: ' . $e->getMessage(),
                (int) $e->getCode(),
                $e,
            );
        }

        return $pdo;
    }
}
