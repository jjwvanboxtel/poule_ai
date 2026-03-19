<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Application\Predictions\BonusAnswerRepositoryInterface;
use App\Domain\Prediction\BonusAnswer;

final class PdoBonusAnswerRepository extends AbstractPdoRepository implements BonusAnswerRepositoryInterface
{
    /**
     * @param list<BonusAnswer> $answers
     */
    public function insertBatch(int $submissionId, array $answers): void
    {
        foreach ($answers as $answer) {
            $this->execute(
                'INSERT INTO bonus_answers
                 (prediction_submission_id, bonus_question_id, answer_text, answer_number, answer_entity_id)
                 VALUES (?, ?, ?, ?, ?)',
                [
                    $submissionId,
                    $answer->bonusQuestionId,
                    $answer->answerText,
                    $answer->answerNumber,
                    $answer->answerEntityId,
                ],
            );
        }
    }

    /**
     * @return list<BonusAnswer>
     */
    public function findBySubmissionId(int $submissionId): array
    {
        return array_map(
            BonusAnswer::fromArray(...),
            $this->fetchAll(
                'SELECT * FROM bonus_answers WHERE prediction_submission_id = ? ORDER BY bonus_question_id ASC',
                [$submissionId],
            ),
        );
    }
}
