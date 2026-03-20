<?php declare(strict_types=1);

namespace App\Application\Competitions;

interface CompetitionParticipantRepositoryInterface
{
    /** @return array<string, mixed>|null */
    public function findByCompetitionAndUser(int $competitionId, int $userId): ?array;

    public function enroll(int $competitionId, int $userId): void;
}
