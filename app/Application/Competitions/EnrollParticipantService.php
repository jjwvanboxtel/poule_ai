<?php declare(strict_types=1);

namespace App\Application\Competitions;

use App\Infrastructure\Persistence\Pdo\PdoCompetitionParticipantRepository;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;

final class EnrollParticipantService
{
    public function __construct(
        private readonly PdoCompetitionRepository $competitions,
        private readonly PdoCompetitionParticipantRepository $participants,
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
