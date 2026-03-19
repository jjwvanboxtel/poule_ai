<?php declare(strict_types=1);

namespace App\Application\Predictions;

use App\Domain\Competition\Competition;
use App\Domain\Competition\SectionType;
use App\Domain\Prediction\KnockoutRoundPrediction;
use DomainException;

final class KnockoutPredictionValidator
{
    public function __construct(
        private readonly CompetitionDataProviderInterface $competitionDataProvider,
        private readonly KnockoutRoundRepositoryInterface $knockoutRoundRepository,
    ) {
    }

    /**
     * @param array<int|string, mixed> $submittedRounds
     * @return list<KnockoutRoundPrediction>
     */
    public function validate(Competition $competition, array $submittedRounds): array
    {
        $sections = $this->competitionDataProvider->findActiveSections($competition->id);

        $hasKnockoutSection = false;
        foreach ($sections as $section) {
            if ($section->sectionType === SectionType::Knockout) {
                $hasKnockoutSection = true;
                break;
            }
        }

        if (!$hasKnockoutSection) {
            return [];
        }

        $rounds = $this->knockoutRoundRepository->findActiveRounds($competition->id);
        if ($rounds === []) {
            throw new DomainException('Er zijn geen actieve knock-outrondes geconfigureerd.');
        }

        $activeEntities = $this->competitionDataProvider->findActiveEntitiesForCompetition($competition->id);
        $allowedEntityIds = [];

        foreach ($activeEntities as $entity) {
            if (in_array($entity['entity_type'], ['country', 'team'], true)) {
                $allowedEntityIds[] = $entity['id'];
            }
        }

        $predictions = [];
        $errors = [];

        foreach ($rounds as $round) {
            $roundId = $round['id'];
            $roundPayload = $submittedRounds[(string) $roundId] ?? $submittedRounds[$roundId] ?? null;
            $slotSelections = is_array($roundPayload) ? $roundPayload : [];

            if (count($slotSelections) !== $round['team_slot_count']) {
                $errors[] = "Vul exact {$round['team_slot_count']} selecties in voor ronde {$round['label']}.";
                continue;
            }

            for ($slot = 1; $slot <= $round['team_slot_count']; $slot++) {
                $value = $slotSelections[(string) $slot] ?? $slotSelections[$slot] ?? null;
                $entityId = is_numeric($value) ? (int) $value : null;

                if ($entityId === null || !in_array($entityId, $allowedEntityIds, true)) {
                    $errors[] = "Kies alleen geldige actieve landen/teams voor ronde {$round['label']}.";
                    continue;
                }

                $predictions[] = new KnockoutRoundPrediction(
                    id: 0,
                    predictionSubmissionId: 0,
                    knockoutRoundId: $roundId,
                    catalogEntityId: $entityId,
                    slotNumber: $slot,
                    createdAt: date('Y-m-d H:i:s'),
                    updatedAt: date('Y-m-d H:i:s'),
                );
            }
        }

        if ($errors !== []) {
            throw new DomainException(implode(' ', array_unique($errors)));
        }

        return $predictions;
    }
}
