<?php declare(strict_types=1);

namespace App\Application\Competitions;

use DomainException;

final class EnrollParticipantService
{
    public function __construct(
        private readonly CompetitionRepositoryInterface $competitions,
        private readonly ParticipantRepositoryInterface $participants,
        private readonly UserReadRepositoryInterface $users,
    ) {
    }

    /**
     * Enroll a user in a competition. Admin-initiated enrollment ignores the is_public / open-status guard.
     *
     * @throws DomainException if competition or user is not found, or participant is already enrolled.
     */
    public function enroll(int $competitionId, int $userId): void
    {
        $competition = $this->competitions->findById($competitionId);
        if ($competition === null) {
            throw new DomainException('Competitie niet gevonden.');
        }

        if ($competition->isArchived()) {
            throw new DomainException('Kan geen deelnemer toevoegen aan een gearchiveerde competitie.');
        }

        $user = $this->users->findById($userId);
        if ($user === null) {
            throw new DomainException('Gebruiker niet gevonden.');
        }

        if (!$user->isActive) {
            throw new DomainException('Inactieve gebruikers kunnen niet worden ingeschreven.');
        }

        $existing = $this->competitions->findParticipantRow($competitionId, $userId);
        if ($existing !== null) {
            throw new DomainException('Deze gebruiker is al ingeschreven voor deze competitie.');
        }

        $this->participants->enroll($competitionId, $userId);
    }

    /**
     * Remove a participant enrollment from a competition.
     *
     * @throws DomainException if the participant row is not found.
     */
    public function unenroll(int $participantId): void
    {
        $participant = $this->participants->findById($participantId);
        if ($participant === null) {
            throw new DomainException('Inschrijving niet gevonden.');
        }

        $this->participants->remove($participantId);
    }
}
