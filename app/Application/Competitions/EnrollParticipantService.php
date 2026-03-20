<?php declare(strict_types=1);

namespace App\Application\Competitions;

use App\Application\Competitions\CompetitionParticipantRepositoryInterface;
use App\Application\Competitions\CompetitionRepositoryInterface;

final class EnrollParticipantService
{
    public function __construct(
        private readonly CompetitionRepositoryInterface $competitions,
        private readonly CompetitionParticipantRepositoryInterface $participants,
    ) {}

    public function enroll(int $competitionId, int $userId): void
    {
        $competition = $this->competitions->findById($competitionId);
        if ($competition === null) {
            throw new \DomainException('Competition not found.');
        }
        if (!$competition->isOpen()) {
            throw new \DomainException('Competition is not open for enrollment.');
        }
        $existing = $this->participants->findByCompetitionAndUser($competitionId, $userId);
        if ($existing !== null) {
            throw new \DomainException('User is already enrolled in this competition.');
        }
        $this->participants->enroll($competitionId, $userId);
    }
}
