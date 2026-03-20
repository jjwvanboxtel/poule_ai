<?php declare(strict_types=1);

namespace App\Application\Competitions;

use App\Domain\Competition\Competition;

interface CompetitionRepositoryInterface
{
    public function findById(int $id): ?Competition;

    public function findBySlug(string $slug): ?Competition;

    /**
     * Insert a new competition and return the new ID.
     *
     * @param array<string, mixed> $data
     */
    public function insert(array $data): int;

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): void;

    /**
     * @return array{id: int, payment_status: string, joined_at: string}|null
     */
    public function findParticipantRow(int $competitionId, int $userId): ?array;
}
