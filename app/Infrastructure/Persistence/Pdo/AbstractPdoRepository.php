<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use PDO;
use PDOStatement;

abstract class AbstractPdoRepository
{
    public function __construct(protected readonly PDO $pdo)
    {
    }

    /**
     * Execute a prepared statement and return the statement.
     *
     * @param array<int|string, mixed> $params
     */
    protected function execute(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    /**
     * Fetch a single row as an associative array.
     *
     * @param array<int|string, mixed> $params
     * @return array<string, mixed>|null
     */
    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $row = $this->execute($sql, $params)->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    /**
     * Fetch all rows as associative arrays.
     *
     * @param array<int|string, mixed> $params
     * @return list<array<string, mixed>>
     */
    protected function fetchAll(string $sql, array $params = []): array
    {
        $rows = $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);

        return array_values(array_filter($rows, static fn (mixed $row): bool => is_array($row)));
    }

    /**
     * Return the last inserted auto-increment ID.
     */
    protected function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Begin a transaction and run $callback. Rolls back on exception.
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    protected function transactional(callable $callback): mixed
    {
        $this->pdo->beginTransaction();

        try {
            $result = $callback();
            $this->pdo->commit();

            return $result;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
