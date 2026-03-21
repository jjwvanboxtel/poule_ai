<?php declare(strict_types=1);

namespace App\Application\Competitions;

use App\Domain\User\User;

interface UserReadRepositoryInterface
{
    public function findById(int $id): ?User;
}
