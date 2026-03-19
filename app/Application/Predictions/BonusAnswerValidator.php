<?php declare(strict_types=1);

namespace App\Application\Predictions;

use App\Domain\Competition\Competition;
use App\Domain\Competition\SectionType;
use App\Domain\Prediction\BonusAnswer;
use DomainException;

final class BonusAnswerValidator
{
    public function __construct(
        private readonly CompetitionDataProviderInterface $competitionDataProvider,
    ) {
    }

    /**
     * @param array<int|string, mixed> $submittedAnswers
     * @return list<BonusAnswer>
     */
    public function validate(Competition $competition, array $submittedAnswers): array
    {
        $sections = $this->competitionDataProvider->findActiveSections($competition->id);

        $hasBonusQuestions = false;
        foreach ($sections as $section) {
            if ($section->sectionType === SectionType::BonusQuestions) {
                $hasBonusQuestions = true;
                break;
            }
        }

        if (!$hasBonusQuestions) {
            return [];
        }

        $questions = $this->competitionDataProvider->findActiveBonusQuestions($competition->id);
        $answers = [];
        $errors = [];

        foreach ($questions as $question) {
            $questionId = $question['id'];
            $value = $submittedAnswers[(string) $questionId] ?? $submittedAnswers[$questionId] ?? null;

            if ($question['question_type'] === 'entity') {
                $entityId = is_numeric($value) ? (int) $value : null;
                $availableEntities = $this->competitionDataProvider
                    ->findActiveEntitiesForCompetition($competition->id, $question['entity_type_constraint']);
                $allowedIds = array_column($availableEntities, 'id');

                if ($entityId === null || !in_array($entityId, $allowedIds, true)) {
                    $errors[] = "Kies een geldige actieve optie voor bonusvraag \"{$question['prompt']}\".";
                    continue;
                }

                $answers[] = new BonusAnswer(
                    id: 0,
                    predictionSubmissionId: 0,
                    bonusQuestionId: $questionId,
                    answerText: null,
                    answerNumber: null,
                    answerEntityId: $entityId,
                );

                continue;
            }

            if ($question['question_type'] === 'numeric') {
                if (!is_numeric($value)) {
                    $errors[] = "Vul een numeriek antwoord in voor bonusvraag \"{$question['prompt']}\".";
                    continue;
                }

                $answers[] = new BonusAnswer(
                    id: 0,
                    predictionSubmissionId: 0,
                    bonusQuestionId: $questionId,
                    answerText: null,
                    answerNumber: (float) $value,
                    answerEntityId: null,
                );

                continue;
            }

            $text = is_scalar($value) ? trim((string) $value) : '';
            if ($text === '') {
                $errors[] = "Vul een antwoord in voor bonusvraag \"{$question['prompt']}\".";
                continue;
            }

            $answers[] = new BonusAnswer(
                id: 0,
                predictionSubmissionId: 0,
                bonusQuestionId: $questionId,
                answerText: $text,
                answerNumber: null,
                answerEntityId: null,
            );
        }

        if ($errors !== []) {
            throw new DomainException(implode(' ', $errors));
        }

        return $answers;
    }
}
