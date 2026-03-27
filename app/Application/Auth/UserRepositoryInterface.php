<?php declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\User\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function save(User $user): void;

    public function countActiveAdmins(): int;
}
