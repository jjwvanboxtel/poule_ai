<?php declare(strict_types=1);

namespace App\Application\Auth;

use App\Application\Auth\AuditLogRepositoryInterface;
use App\Application\Auth\UserRepositoryInterface;
use App\Domain\User\UserRole;
use DomainException;

final class UpdateUserStatusService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AuditLogRepositoryInterface $auditLog,
    ) {
    }

    /**
     * Activate or deactivate a user.
     *
     * Guards: if deactivating an admin, ensure at least one other active admin remains.
     *
     * @throws DomainException if user not found, or last active admin protection triggers.
     */
    public function update(int $targetUserId, bool $isActive, int $actingAdminId, string $ipAddress = ''): void
    {
        $targetUser = $this->users->findById($targetUserId);
        if ($targetUser === null) {
            throw new DomainException('Gebruiker niet gevonden.');
        }

        $oldStatus = $targetUser->isActive;

        // Guard: prevent deactivating the last active admin
        if ($targetUser->role === UserRole::Admin && !$isActive && $targetUser->isActive) {
            $activeAdminCount = $this->users->countActiveAdmins();
            if ($activeAdminCount <= 1) {
                throw new DomainException(
                    'Kan de laatste actieve beheerder niet deactiveren. Wijs eerst een andere beheerder aan.',
                );
            }
        }

        $updatedUser = new \App\Domain\User\User(
            id: $targetUser->id,
            firstName: $targetUser->firstName,
            lastName: $targetUser->lastName,
            email: $targetUser->email,
            phoneNumber: $targetUser->phoneNumber,
            role: $targetUser->role,
            isActive: $isActive,
            lastLoginAt: $targetUser->lastLoginAt,
            createdAt: $targetUser->createdAt,
            updatedAt: $targetUser->updatedAt,
        );

        $this->users->save($updatedUser);

        $this->auditLog->log(
            $actingAdminId,
            'update_user_status',
            'user',
            $targetUserId,
            ['is_active' => $oldStatus],
            ['is_active' => $isActive],
            $ipAddress,
        );
    }
}
