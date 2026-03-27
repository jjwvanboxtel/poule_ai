<?php declare(strict_types=1);

namespace App\Application\Competitions;

final class UpdateBonusQuestionsService
{
    public function __construct(private readonly \PDO $pdo) {}

    /** @param array<string, mixed> $data */
    public function save(int $competitionId, array $data): void
    {
        $prompt = is_string($data['prompt'] ?? null) ? trim($data['prompt']) : '';
        $questionType = is_string($data['question_type'] ?? null) ? $data['question_type'] : 'text';
        $entityTypeConstraint = is_string($data['entity_type_constraint'] ?? null) && $data['entity_type_constraint'] !== ''
            ? $data['entity_type_constraint']
            : null;
        $displayOrder = is_numeric($data['display_order'] ?? null) ? (int) $data['display_order'] : 0;
        $isActive = !empty($data['is_active']) ? 1 : 0;

        if (isset($data['id']) && is_numeric($data['id'])) {
            $stmt = $this->pdo->prepare(
                'UPDATE bonus_questions SET prompt = ?, question_type = ?, entity_type_constraint = ?, display_order = ?, is_active = ?
                 WHERE id = ? AND competition_id = ?',
            );
            $stmt->execute([$prompt, $questionType, $entityTypeConstraint, $displayOrder, $isActive, (int) $data['id'], $competitionId]);
        } else {
            $stmt = $this->pdo->prepare(
                'INSERT INTO bonus_questions (competition_id, prompt, question_type, entity_type_constraint, display_order, is_active)
                 VALUES (?, ?, ?, ?, ?, ?)',
            );
            $stmt->execute([$competitionId, $prompt, $questionType, $entityTypeConstraint, $displayOrder, $isActive]);
        }
    }

    public function delete(int $id, int $competitionId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE bonus_questions SET is_active = 0 WHERE id = ? AND competition_id = ?',
        );
        $stmt->execute([$id, $competitionId]);
    }
}
