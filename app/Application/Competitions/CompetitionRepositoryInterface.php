<?php declare(strict_types=1);

namespace App\Application\Competitions;

use App\Domain\Competition\Competition;

interface CompetitionRepositoryInterface
{
    public function findById(int $id): ?Competition;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): void;

    public function hasActiveSection(int $competitionId): bool;
}
