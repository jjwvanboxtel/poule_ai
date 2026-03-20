<?php declare(strict_types=1);

namespace App\Application\Competitions;

use DomainException;
use PDO;

final class UpdateBonusQuestionsService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly CompetitionRepositoryInterface $competitions,
    ) {
    }

    /**
     * Sync all bonus questions for a competition.
     *
     * @param list<array{
     *     id?: int,
     *     prompt: string,
     *     question_type: string,
     *     entity_type_constraint: string|null,
     *     is_active: bool,
     *     display_order: int
     * }> $questions
     * @throws DomainException if the competition is not found.
     */
    public function update(int $competitionId, array $questions): void
    {
        $competition = $this->competitions->findById($competitionId);
        if ($competition === null) {
            throw new DomainException('Competitie niet gevonden.');
        }

        $this->pdo->beginTransaction();

        try {
            // Deactivate all existing bonus questions for this competition
            $stmt = $this->pdo->prepare(
                'UPDATE bonus_questions SET is_active = 0 WHERE competition_id = ?',
            );
            $stmt->execute([$competitionId]);

            foreach ($questions as $index => $q) {
                $questionType = $q['question_type'];
                if (!in_array($questionType, ['entity', 'numeric', 'text'], true)) {
                    throw new DomainException("Ongeldig vraagtype: {$questionType}.");
                }

                $displayOrder = $q['display_order'];

                if (isset($q['id']) && $q['id'] > 0) {
                    $update = $this->pdo->prepare(
                        'UPDATE bonus_questions
                         SET prompt = ?, question_type = ?, entity_type_constraint = ?,
                             is_active = ?, display_order = ?
                         WHERE id = ? AND competition_id = ?',
                    );
                    $update->execute([
                        $q['prompt'],
                        $questionType,
                        $q['entity_type_constraint'],
                        $q['is_active'] ? 1 : 0,
                        $displayOrder,
                        $q['id'],
                        $competitionId,
                    ]);
                } else {
                    $insert = $this->pdo->prepare(
                        'INSERT INTO bonus_questions
                             (competition_id, prompt, question_type, entity_type_constraint, is_active, display_order)
                         VALUES (?, ?, ?, ?, ?, ?)',
                    );
                    $insert->execute([
                        $competitionId,
                        $q['prompt'],
                        $questionType,
                        $q['entity_type_constraint'],
                        $q['is_active'] ? 1 : 0,
                        $displayOrder,
                    ]);
                }
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
