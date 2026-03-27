<?php declare(strict_types=1);

namespace App\Application\Auth;

interface AdminAuditLogRepositoryInterface
{
    /** @param array<string, mixed>|null $details */
    public function log(int $userId, string $action, string $entityType, ?int $entityId, ?array $details): void;
}
