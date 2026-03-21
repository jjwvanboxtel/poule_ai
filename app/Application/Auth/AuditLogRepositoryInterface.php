<?php declare(strict_types=1);

namespace App\Application\Auth;

interface AuditLogRepositoryInterface
{
    /**
     * @param array<string, mixed>|null $oldValue
     * @param array<string, mixed>|null $newValue
     */
    public function log(
        int $userId,
        string $action,
        string $entityType,
        ?int $entityId,
        ?array $oldValue,
        ?array $newValue,
        string $ipAddress = '',
    ): void;
}
