<?php declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Application\Predictions\CompetitionDataProviderInterface;
use App\Application\Predictions\KnockoutRoundRepositoryInterface;
use App\Domain\Competition\Competition;
use App\Domain\Competition\CompetitionSection;
use App\Domain\Competition\SectionType;
use App\Domain\Prediction\BonusAnswer;
use App\Domain\Prediction\KnockoutRoundPrediction;
use App\Domain\Prediction\MatchPrediction;
use App\Domain\Prediction\PredictionSubmission;

final class PredictionFormViewModel
{
    public function __construct(
        private readonly CompetitionDataProviderInterface $competitionDataProvider,
        private readonly KnockoutRoundRepositoryInterface $knockoutRoundRepository,
    ) {
    }

    /**
     * @param array{id: int, payment_status: string, joined_at: string} $participant
     * @param array<int|string, mixed> $oldInput
     * @param array<int|string, mixed> $errors
     * @param list<MatchPrediction> $matchPredictions
     * @param list<BonusAnswer> $bonusAnswers
     * @param list<KnockoutRoundPrediction> $knockoutPredictions
     * @return array<string, mixed>
     */
    public function build(
        Competition $competition,
        array $participant,
        ?PredictionSubmission $submission,
        array $oldInput = [],
        array $errors = [],
        array $matchPredictions = [],
        array $bonusAnswers = [],
        array $knockoutPredictions = [],
    ): array {
        $activeSections = $this->competitionDataProvider->findActiveSections($competition->id);
        $sectionFlags = $this->sectionFlags($activeSections);

        $existingMatchPredictions = [];
        foreach ($matchPredictions as $prediction) {
            $existingMatchPredictions[$prediction->matchId] = $prediction;
        }

        $existingBonusAnswers = [];
        foreach ($bonusAnswers as $answer) {
            $existingBonusAnswers[$answer->bonusQuestionId] = $answer;
        }

        $existingKnockoutPredictions = [];
        foreach ($knockoutPredictions as $prediction) {
            $existingKnockoutPredictions[$prediction->knockoutRoundId][$prediction->slotNumber] = $prediction;
        }

        $matches = [];
        foreach ($this->competitionDataProvider->findMatchesForCompetition($competition->id) as $match) {
            $prediction = $existingMatchPredictions[$match['id']] ?? null;
            $matches[] = [
                'id' => $match['id'],
                'label' => sprintf('%s - %s', $match['home_label'], $match['away_label']),
                'stage' => $match['stage'],
                'kickoff_at' => $match['kickoff_at'],
                'values' => [
                    'predicted_home_score' => $this->fieldValue(
                        $oldInput,
                        ['matches', (string) $match['id'], 'predicted_home_score'],
                        $prediction?->predictedHomeScore,
                    ),
                    'predicted_away_score' => $this->fieldValue(
                        $oldInput,
                        ['matches', (string) $match['id'], 'predicted_away_score'],
                        $prediction?->predictedAwayScore,
                    ),
                    'predicted_outcome' => $this->fieldValue(
                        $oldInput,
                        ['matches', (string) $match['id'], 'predicted_outcome'],
                        $prediction?->predictedOutcome,
                    ),
                    'predicted_yellow_cards_home' => $this->fieldValue(
                        $oldInput,
                        ['matches', (string) $match['id'], 'predicted_yellow_cards_home'],
                        $prediction?->predictedYellowCardsHome,
                    ),
                    'predicted_yellow_cards_away' => $this->fieldValue(
                        $oldInput,
                        ['matches', (string) $match['id'], 'predicted_yellow_cards_away'],
                        $prediction?->predictedYellowCardsAway,
                    ),
                    'predicted_red_cards_home' => $this->fieldValue(
                        $oldInput,
                        ['matches', (string) $match['id'], 'predicted_red_cards_home'],
                        $prediction?->predictedRedCardsHome,
                    ),
                    'predicted_red_cards_away' => $this->fieldValue(
                        $oldInput,
                        ['matches', (string) $match['id'], 'predicted_red_cards_away'],
                        $prediction?->predictedRedCardsAway,
                    ),
                ],
            ];
        }

        $bonusQuestions = [];
        foreach ($this->competitionDataProvider->findActiveBonusQuestions($competition->id) as $question) {
            $answer = $existingBonusAnswers[$question['id']] ?? null;
            $options = $question['question_type'] === 'entity'
                ? $this->competitionDataProvider->findActiveEntitiesForCompetition(
                    $competition->id,
                    $question['entity_type_constraint'],
                )
                : [];

            $bonusQuestions[] = [
                'id' => $question['id'],
                'prompt' => $question['prompt'],
                'question_type' => $question['question_type'],
                'options' => $options,
                'value' => $this->bonusValue($oldInput, $question['id'], $answer),
            ];
        }

        $knockoutOptions = array_values(array_filter(
            $this->competitionDataProvider->findActiveEntitiesForCompetition($competition->id),
            static fn (array $entity): bool => in_array($entity['entity_type'], ['country', 'team'], true),
        ));

        $knockoutRounds = [];
        foreach ($this->knockoutRoundRepository->findActiveRounds($competition->id) as $round) {
            $slots = [];

            for ($slot = 1; $slot <= $round['team_slot_count']; $slot++) {
                $existingPrediction = $existingKnockoutPredictions[$round['id']][$slot] ?? null;
                $slots[] = [
                    'slot_number' => $slot,
                    'value' => $this->fieldValue(
                        $oldInput,
                        ['knockout_rounds', (string) $round['id'], (string) $slot],
                        $existingPrediction?->catalogEntityId,
                    ),
                ];
            }

            $knockoutRounds[] = [
                'id' => $round['id'],
                'label' => $round['label'],
                'team_slot_count' => $round['team_slot_count'],
                'options' => $knockoutOptions,
                'slots' => $slots,
            ];
        }

        return [
            'competition' => $competition,
            'participant' => $participant,
            'has_submission' => $submission !== null,
            'submission' => $submission,
            'read_only' => $submission !== null || !$competition->isOpen(),
            'errors' => $errors,
            'section_flags' => $sectionFlags,
            'matches' => $matches,
            'bonus_questions' => $bonusQuestions,
            'knockout_rounds' => $knockoutRounds,
            'payment_badge' => $participant['payment_status'] === 'unpaid' ? 'Onbetaald' : 'Betaald',
        ];
    }

    /**
     * @param list<CompetitionSection> $sections
     * @return array{scores: bool, outcomes: bool, cards: bool, knockout: bool, bonus_questions: bool}
     */
    private function sectionFlags(array $sections): array
    {
        $flags = [
            'scores' => false,
            'outcomes' => false,
            'cards' => false,
            'knockout' => false,
            'bonus_questions' => false,
        ];

        foreach ($sections as $section) {
            if ($section->sectionType === SectionType::GroupStageScores) {
                $flags['scores'] = true;
            }

            if ($section->sectionType === SectionType::MatchOutcomes) {
                $flags['outcomes'] = true;
            }

            if ($section->sectionType === SectionType::Cards) {
                $flags['cards'] = true;
            }

            if ($section->sectionType === SectionType::Knockout) {
                $flags['knockout'] = true;
            }

            if ($section->sectionType === SectionType::BonusQuestions) {
                $flags['bonus_questions'] = true;
            }
        }

        return $flags;
    }

    /**
     * @param array<int|string, mixed> $source
     * @param list<string> $path
     */
    private function fieldValue(array $source, array $path, mixed $fallback): mixed
    {
        $current = $source;

        foreach ($path as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return $fallback;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    /**
     * @param array<int|string, mixed> $oldInput
     */
    private function bonusValue(array $oldInput, int $questionId, ?BonusAnswer $answer): mixed
    {
        $oldValue = $this->fieldValue($oldInput, ['bonus_answers', (string) $questionId], null);
        if ($oldValue !== null) {
            return $oldValue;
        }

        if ($answer === null) {
            return null;
        }

        return $answer->answerEntityId ?? $answer->answerNumber ?? $answer->answerText;
    }
}
