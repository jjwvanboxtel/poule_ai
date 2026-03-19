<?php declare(strict_types=1);

namespace App\Application\Predictions;

use App\Domain\Competition\Competition;
use App\Domain\Competition\SectionType;
use App\Domain\Prediction\MatchPrediction;
use DomainException;

final class PredictionPayloadValidator
{
    public function __construct(
        private readonly CompetitionDataProviderInterface $competitionDataProvider,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<MatchPrediction>
     */
    public function validate(Competition $competition, array $payload): array
    {
        $sections = $this->competitionDataProvider->findActiveSections($competition->id);
        $requiresScores = false;
        $requiresOutcome = false;
        $requiresCards = false;

        foreach ($sections as $section) {
            if ($section->sectionType === SectionType::GroupStageScores) {
                $requiresScores = true;
            }

            if ($section->sectionType === SectionType::MatchOutcomes) {
                $requiresOutcome = true;
            }

            if ($section->sectionType === SectionType::Cards) {
                $requiresCards = true;
            }
        }

        if (!$requiresScores && !$requiresOutcome && !$requiresCards) {
            return [];
        }

        $matches = $this->competitionDataProvider->findMatchesForCompetition($competition->id);
        $submittedMatches = $this->arrayValue($payload['matches'] ?? []);
        $errors = [];
        $predictions = [];

        foreach ($matches as $match) {
            $matchId = $match['id'];
            $submitted = $this->arrayValue($submittedMatches[(string) $matchId] ?? $submittedMatches[$matchId] ?? []);
            $label = sprintf('%s - %s', $match['home_label'], $match['away_label']);

            $homeScore = null;
            $awayScore = null;
            $outcome = null;
            $yellowCardsHome = null;
            $yellowCardsAway = null;
            $redCardsHome = null;
            $redCardsAway = null;

            if ($requiresScores) {
                $homeScore = $this->nullableInteger($submitted['predicted_home_score'] ?? null);
                $awayScore = $this->nullableInteger($submitted['predicted_away_score'] ?? null);

                if ($homeScore === null || $awayScore === null) {
                    $errors[] = "Vul de score volledig in voor wedstrijd {$label}.";
                }
            }

            if ($requiresOutcome) {
                $outcome = $this->nullableString($submitted['predicted_outcome'] ?? null);

                if (!in_array($outcome, ['home_win', 'draw', 'away_win'], true)) {
                    $errors[] = "Kies een geldige uitslagoptie voor wedstrijd {$label}.";
                }
            }

            if ($requiresCards) {
                $yellowCardsHome = $this->nullableInteger($submitted['predicted_yellow_cards_home'] ?? null);
                $yellowCardsAway = $this->nullableInteger($submitted['predicted_yellow_cards_away'] ?? null);
                $redCardsHome = $this->nullableInteger($submitted['predicted_red_cards_home'] ?? null);
                $redCardsAway = $this->nullableInteger($submitted['predicted_red_cards_away'] ?? null);

                if (
                    $yellowCardsHome === null
                    || $yellowCardsAway === null
                    || $redCardsHome === null
                    || $redCardsAway === null
                ) {
                    $errors[] = "Vul alle kaarten in voor wedstrijd {$label}.";
                }
            }

            $predictions[] = new MatchPrediction(
                id: 0,
                predictionSubmissionId: 0,
                matchId: $matchId,
                predictedHomeScore: $homeScore,
                predictedAwayScore: $awayScore,
                predictedOutcome: $outcome,
                predictedYellowCardsHome: $yellowCardsHome,
                predictedYellowCardsAway: $yellowCardsAway,
                predictedRedCardsHome: $redCardsHome,
                predictedRedCardsAway: $redCardsAway,
                predictedKnockoutWinnerEntityId: null,
            );
        }

        if ($errors !== []) {
            throw new DomainException(implode(' ', $errors));
        }

        return $predictions;
    }

    /**
     * @param mixed $value
     * @return array<int|string, mixed>
     */
    private function arrayValue(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    private function nullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private function nullableString(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
