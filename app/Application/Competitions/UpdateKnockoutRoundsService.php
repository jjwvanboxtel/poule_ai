<?php declare(strict_types=1);

namespace App\Application\Competitions;

use App\Infrastructure\Persistence\Pdo\PdoKnockoutRoundRepository;

final class UpdateKnockoutRoundsService
{
    public function __construct(private readonly PdoKnockoutRoundRepository $repo) {}

    /** @param list<array<string, mixed>> $rounds */
    public function update(int $competitionId, array $rounds): void
    {
        foreach ($rounds as $round) {
            $label = is_string($round['label'] ?? null) ? trim($round['label']) : '';
            $roundOrder = is_numeric($round['round_order'] ?? null) ? (int) $round['round_order'] : 0;
            $isActive = !empty($round['is_active']);
            $hasId = isset($round['id']) && is_numeric($round['id']);

            // Skip the blank "new round" row at the bottom of the form
            if (!$hasId && $label === '') {
                continue;
            }

            $teamSlotCount = is_numeric($round['team_slot_count'] ?? null) ? (int) $round['team_slot_count'] : 0;
            if ($teamSlotCount <= 0) {
                throw new \DomainException('Team slot count must be greater than 0.');
            }

            if ($hasId) {
                $roundId = is_numeric($round['id'] ?? null) ? (int) $round['id'] : 0;
                $this->repo->updateRound($roundId, $label, $roundOrder, $teamSlotCount, $isActive);
            } else {
                try {
                    $this->repo->saveRound($competitionId, $label, $roundOrder, $teamSlotCount, $isActive);
                } catch (\PDOException $e) {
                    if (str_contains($e->getMessage(), '1062') || str_contains($e->getMessage(), 'Duplicate entry')) {
                        throw new \DomainException("Volgorde {$roundOrder} bestaat al voor deze competitie. Kies een andere volgorde.");
                    }
                    throw $e;
                }
            }
        }
    }
}
