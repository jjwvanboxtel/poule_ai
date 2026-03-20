<?php declare(strict_types=1);

namespace App\Application\Auth;

use App\Application\Auth\AuditLogRepositoryInterface;
use App\Application\Auth\UserRepositoryInterface;
use App\Domain\User\UserRole;
use DomainException;

final class UpdateUserRoleService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AuditLogRepositoryInterface $auditLog,
    ) {
    }

    /**
     * Change the role of a user.
     *
     * Guards: if downgrading from admin, ensure at least one other active admin remains.
     *
     * @throws DomainException if user not found, or last active admin protection triggers.
     */
    public function update(int $targetUserId, string $newRole, int $actingAdminId, string $ipAddress = ''): void
    {
        $targetUser = $this->users->findById($targetUserId);
        if ($targetUser === null) {
            throw new DomainException('Gebruiker niet gevonden.');
        }

        $role = UserRole::tryFrom($newRole);
        if ($role === null) {
            throw new DomainException("Ongeldig rol: {$newRole}.");
        }

        $oldRole = $targetUser->role->value;

        // Guard: prevent removing the last active admin
        if ($targetUser->role === UserRole::Admin && $role !== UserRole::Admin) {
            $activeAdminCount = $this->users->countActiveAdmins();
            if ($activeAdminCount <= 1 && $targetUser->isActive) {
                throw new DomainException(
                    'Kan de laatste actieve beheerder niet degraderen. Wijs eerst een andere beheerder aan.',
                );
            }
        }

        $updatedUser = new \App\Domain\User\User(
            id: $targetUser->id,
            firstName: $targetUser->firstName,
            lastName: $targetUser->lastName,
            email: $targetUser->email,
            phoneNumber: $targetUser->phoneNumber,
            role: $role,
            isActive: $targetUser->isActive,
            lastLoginAt: $targetUser->lastLoginAt,
            createdAt: $targetUser->createdAt,
            updatedAt: $targetUser->updatedAt,
        );

        $this->users->save($updatedUser);

        $this->auditLog->log(
            $actingAdminId,
            'update_user_role',
            'user',
            $targetUserId,
            ['role' => $oldRole],
            ['role' => $newRole],
            $ipAddress,
        );
    }
}
