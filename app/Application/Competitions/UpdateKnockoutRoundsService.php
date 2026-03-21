<?php declare(strict_types=1);

namespace App\Application\Competitions;

use App\Domain\Competition\KnockoutRound;
use DomainException;
use PDO;

final class UpdateKnockoutRoundsService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly CompetitionRepositoryInterface $competitions,
    ) {
    }

    /**
     * Sync all knockout rounds for a competition and assign teams to slots.
     *
     * @param list<array{
     *     id?: int,
     *     label: string,
     *     round_order: int,
     *     team_slot_count: int,
     *     is_active: bool,
     *     teams?: list<array{slot_number: int, catalog_entity_id: int}>
     * }> $rounds
     * @return list<KnockoutRound>
     * @throws DomainException if competition is not found or slot validation fails.
     */
    public function update(int $competitionId, array $rounds): array
    {
        $competition = $this->competitions->findById($competitionId);
        if ($competition === null) {
            throw new DomainException('Competitie niet gevonden.');
        }

        foreach ($rounds as $round) {
            $teams = $round['teams'] ?? [];
            if (count($teams) > $round['team_slot_count']) {
                throw new DomainException(
                    "Ronde '{$round['label']}': te veel teams opgegeven (max {$round['team_slot_count']}).",
                );
            }
        }

        $this->pdo->beginTransaction();

        try {
            // Remove old rounds (cascades to knockout_round_teams)
            $this->pdo->prepare('DELETE FROM knockout_rounds WHERE competition_id = ?')->execute([$competitionId]);

            $inserted = [];
            foreach ($rounds as $round) {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO knockout_rounds (competition_id, label, round_order, team_slot_count, is_active)
                     VALUES (?, ?, ?, ?, ?)',
                );
                $stmt->execute([
                    $competitionId,
                    $round['label'],
                    $round['round_order'],
                    $round['team_slot_count'],
                    $round['is_active'] ? 1 : 0,
                ]);
                $roundId = (int) $this->pdo->lastInsertId();

                // Insert team slots
                foreach ($round['teams'] ?? [] as $team) {
                    $slotStmt = $this->pdo->prepare(
                        'INSERT INTO knockout_round_teams (knockout_round_id, catalog_entity_id, slot_number)
                         VALUES (?, ?, ?)',
                    );
                    $slotStmt->execute([$roundId, $team['catalog_entity_id'], $team['slot_number']]);
                }

                $inserted[] = new KnockoutRound(
                    id: $roundId,
                    competitionId: $competitionId,
                    label: $round['label'],
                    roundOrder: $round['round_order'],
                    teamSlotCount: $round['team_slot_count'],
                    isActive: $round['is_active'],
                    createdAt: date('Y-m-d H:i:s'),
                    updatedAt: date('Y-m-d H:i:s'),
                );
            }

            $this->pdo->commit();

            return $inserted;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
