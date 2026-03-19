<?php declare(strict_types=1);

namespace App\Application\Predictions;

use App\Domain\Prediction\BonusAnswer;

interface BonusAnswerRepositoryInterface
{
    /**
     * @param list<BonusAnswer> $answers
     */
    public function insertBatch(int $submissionId, array $answers): void;

    /**
     * @return list<BonusAnswer>
     */
    public function findBySubmissionId(int $submissionId): array;
}
