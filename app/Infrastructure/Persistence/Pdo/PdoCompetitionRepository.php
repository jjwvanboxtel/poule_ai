<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Application\Competitions\CompetitionRepositoryInterface;
use App\Application\Predictions\CompetitionDataProviderInterface;
use App\Domain\Competition\Competition;
use App\Domain\Competition\CompetitionSection;
use App\Domain\Competition\CompetitionStatus;

final class PdoCompetitionRepository extends AbstractPdoRepository implements CompetitionDataProviderInterface, CompetitionRepositoryInterface
{
    public function findById(int $id): ?Competition
    {
        $row = $this->fetchOne(
            'SELECT * FROM competitions WHERE id = ? LIMIT 1',
            [$id],
        );

        return $row !== null ? Competition::fromArray($row) : null;
    }

    public function findBySlug(string $slug): ?Competition
    {
        $row = $this->fetchOne(
            'SELECT * FROM competitions WHERE slug = ? LIMIT 1',
            [$slug],
        );

        return $row !== null ? Competition::fromArray($row) : null;
    }

    /**
     * @return list<Competition>
     */
    public function findPublicActive(): array
    {
        return array_map(
            Competition::fromArray(...),
            $this->fetchAll(
                "SELECT * FROM competitions WHERE is_public = 1 AND status = 'active'
                 ORDER BY start_date DESC",
            ),
        );
    }

    /**
     * @return list<Competition>
     */
    public function findAll(): array
    {
        return array_map(
            Competition::fromArray(...),
            $this->fetchAll('SELECT * FROM competitions ORDER BY created_at DESC'),
        );
    }

    /**
     * @return list<CompetitionSection>
     */
    public function findSectionsByCompetitionId(int $competitionId): array
    {
        return array_map(
            CompetitionSection::fromArray(...),
            $this->fetchAll(
                'SELECT * FROM competition_sections WHERE competition_id = ? ORDER BY display_order ASC',
                [$competitionId],
            ),
        );
    }

    /**
     * @return list<CompetitionSection>
     */
    public function findActiveSections(int $competitionId): array
    {
        return array_map(
            CompetitionSection::fromArray(...),
            $this->fetchAll(
                'SELECT * FROM competition_sections
                 WHERE competition_id = ? AND is_active = 1
                 ORDER BY display_order ASC',
                [$competitionId],
            ),
        );
    }

    public function hasActiveSection(int $competitionId): bool
    {
        $row = $this->fetchOne(
            'SELECT 1 FROM competition_sections WHERE competition_id = ? AND is_active = 1 LIMIT 1',
            [$competitionId],
        );

        return $row !== null;
    }

    /**
     * Insert a new competition and return the new ID.
     *
     * @param array<string, mixed> $data
     */
    public function insert(array $data): int
    {
        $this->execute(
            'INSERT INTO competitions
             (name, slug, description, start_date, end_date, submission_deadline,
              entry_fee_amount, prize_first_percent, prize_second_percent, prize_third_percent,
              status, is_public, logo_path, created_by_user_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['name'],
                $data['slug'],
                $data['description'] ?? '',
                $data['start_date'],
                $data['end_date'],
                $data['submission_deadline'],
                $data['entry_fee_amount'] ?? 0.00,
                $data['prize_first_percent'] ?? 60,
                $data['prize_second_percent'] ?? 30,
                $data['prize_third_percent'] ?? 10,
                CompetitionStatus::Draft->value,
                $data['is_public'] ?? 1,
                $data['logo_path'] ?? null,
                $data['created_by_user_id'],
            ],
        );

        return $this->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): void
    {
        $this->execute(
            'UPDATE competitions SET
                name                 = ?,
                slug                 = ?,
                description          = ?,
                start_date           = ?,
                end_date             = ?,
                submission_deadline  = ?,
                entry_fee_amount     = ?,
                prize_first_percent  = ?,
                prize_second_percent = ?,
                prize_third_percent  = ?,
                status               = ?,
                is_public            = ?,
                logo_path            = ?
             WHERE id = ?',
            [
                $data['name'],
                $data['slug'],
                $data['description'] ?? '',
                $data['start_date'],
                $data['end_date'],
                $data['submission_deadline'],
                $data['entry_fee_amount'] ?? 0.00,
                $data['prize_first_percent'] ?? 60,
                $data['prize_second_percent'] ?? 30,
                $data['prize_third_percent'] ?? 10,
                $data['status'] ?? CompetitionStatus::Draft->value,
                $data['is_public'] ?? 1,
                $data['logo_path'] ?? null,
                $id,
            ],
        );
    }

    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     slug: string,
     *     submission_deadline: string,
     *     status: string,
     *     payment_status: string,
     *     joined_at: string
     * }>
     */
    public function findCompetitionsForUser(int $userId): array
    {
        $rows = $this->fetchAll(
            'SELECT c.id, c.name, c.slug, c.submission_deadline, c.status, cp.payment_status, cp.joined_at
             FROM competition_participants cp
             INNER JOIN competitions c ON c.id = cp.competition_id
             WHERE cp.user_id = ?
             ORDER BY c.start_date ASC, c.name ASC',
            [$userId],
        );

        return array_map(
            static fn (array $row): array => [
                'id' => is_numeric($row['id'] ?? null) ? (int) $row['id'] : 0,
                'name' => is_scalar($row['name'] ?? null) ? (string) $row['name'] : '',
                'slug' => is_scalar($row['slug'] ?? null) ? (string) $row['slug'] : '',
                'submission_deadline' => is_scalar($row['submission_deadline'] ?? null) ? (string) $row['submission_deadline'] : '',
                'status' => is_scalar($row['status'] ?? null) ? (string) $row['status'] : '',
                'payment_status' => is_scalar($row['payment_status'] ?? null) ? (string) $row['payment_status'] : 'unpaid',
                'joined_at' => is_scalar($row['joined_at'] ?? null) ? (string) $row['joined_at'] : '',
            ],
            $rows,
        );
    }

    /**
     * @return list<array{id: int, name: string, slug: string, submission_deadline: string}>
     */
    public function findOpenCompetitionsForUser(int $userId): array
    {
        $rows = $this->fetchAll(
            "SELECT c.id, c.name, c.slug, c.submission_deadline
             FROM competitions c
             LEFT JOIN competition_participants cp
               ON cp.competition_id = c.id AND cp.user_id = ?
             WHERE c.status = 'open' AND cp.id IS NULL
             ORDER BY c.start_date ASC, c.name ASC",
            [$userId],
        );

        return array_map(
            static fn (array $row): array => [
                'id' => is_numeric($row['id'] ?? null) ? (int) $row['id'] : 0,
                'name' => is_scalar($row['name'] ?? null) ? (string) $row['name'] : '',
                'slug' => is_scalar($row['slug'] ?? null) ? (string) $row['slug'] : '',
                'submission_deadline' => is_scalar($row['submission_deadline'] ?? null) ? (string) $row['submission_deadline'] : '',
            ],
            $rows,
        );
    }

    /**
     * @return array{id: int, payment_status: string, joined_at: string}|null
     */
    public function findParticipantRow(int $competitionId, int $userId): ?array
    {
        $row = $this->fetchOne(
            'SELECT id, payment_status, joined_at
             FROM competition_participants
             WHERE competition_id = ? AND user_id = ?
             LIMIT 1',
            [$competitionId, $userId],
        );

        if ($row === null) {
            return null;
        }

        return [
            'id' => is_numeric($row['id'] ?? null) ? (int) $row['id'] : 0,
            'payment_status' => is_scalar($row['payment_status'] ?? null) ? (string) $row['payment_status'] : 'unpaid',
            'joined_at' => is_scalar($row['joined_at'] ?? null) ? (string) $row['joined_at'] : '',
        ];
    }

    public function enrollUserInCompetition(int $competitionId, int $userId): void
    {
        $this->execute(
            'INSERT INTO competition_participants (competition_id, user_id, payment_status)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE user_id = user_id',
            [$competitionId, $userId, 'unpaid'],
        );
    }

    public function enrollUserInOpenCompetitions(int $userId): void
    {
        $openCompetitions = $this->findOpenCompetitionsForUser($userId);

        foreach ($openCompetitions as $competition) {
            $this->enrollUserInCompetition($competition['id'], $userId);
        }
    }

    /**
     * @return list<array{
     *     id: int,
     *     home_entity_id: int,
     *     away_entity_id: int,
     *     home_label: string,
     *     away_label: string,
     *     stage: string,
     *     kickoff_at: string
     * }>
     */
    public function findMatchesForCompetition(int $competitionId): array
    {
        $rows = $this->fetchAll(
            'SELECT m.id, m.home_entity_id, m.away_entity_id, m.stage, m.kickoff_at,
                    home.display_name AS home_label, away.display_name AS away_label
             FROM matches m
             INNER JOIN catalog_entities home ON home.id = m.home_entity_id
             INNER JOIN catalog_entities away ON away.id = m.away_entity_id
             WHERE m.competition_id = ?
             ORDER BY m.kickoff_at ASC, m.id ASC',
            [$competitionId],
        );

        return array_map(
            static fn (array $row): array => [
                'id' => is_numeric($row['id'] ?? null) ? (int) $row['id'] : 0,
                'home_entity_id' => is_numeric($row['home_entity_id'] ?? null) ? (int) $row['home_entity_id'] : 0,
                'away_entity_id' => is_numeric($row['away_entity_id'] ?? null) ? (int) $row['away_entity_id'] : 0,
                'home_label' => is_scalar($row['home_label'] ?? null) ? (string) $row['home_label'] : '',
                'away_label' => is_scalar($row['away_label'] ?? null) ? (string) $row['away_label'] : '',
                'stage' => is_scalar($row['stage'] ?? null) ? (string) $row['stage'] : '',
                'kickoff_at' => is_scalar($row['kickoff_at'] ?? null) ? (string) $row['kickoff_at'] : '',
            ],
            $rows,
        );
    }

    /**
     * @return list<array{
     *     id: int,
     *     prompt: string,
     *     question_type: string,
     *     entity_type_constraint: ?string,
     *     display_order: int
     * }>
     */
    public function findActiveBonusQuestions(int $competitionId): array
    {
        $rows = $this->fetchAll(
            'SELECT id, prompt, question_type, entity_type_constraint, display_order
             FROM bonus_questions
             WHERE competition_id = ? AND is_active = 1
             ORDER BY display_order ASC, id ASC',
            [$competitionId],
        );

        return array_map(
            static fn (array $row): array => [
                'id' => is_numeric($row['id'] ?? null) ? (int) $row['id'] : 0,
                'prompt' => is_scalar($row['prompt'] ?? null) ? (string) $row['prompt'] : '',
                'question_type' => is_scalar($row['question_type'] ?? null) ? (string) $row['question_type'] : '',
                'entity_type_constraint' => is_scalar($row['entity_type_constraint'] ?? null)
                    ? (string) $row['entity_type_constraint']
                    : null,
                'display_order' => is_numeric($row['display_order'] ?? null) ? (int) $row['display_order'] : 0,
            ],
            $rows,
        );
    }

    /**
     * @return list<array{
     *     id: int,
     *     entity_type: string,
     *     display_name: string,
     *     short_code: ?string
     * }>
     */
    public function findActiveEntitiesForCompetition(int $competitionId, ?string $entityType = null): array
    {
        $sql = 'SELECT id, entity_type, display_name, short_code
                FROM catalog_entities
                WHERE is_active = 1 AND (competition_id = ? OR competition_id IS NULL)';
        $params = [$competitionId];

        if ($entityType !== null) {
            $sql .= ' AND entity_type = ?';
            $params[] = $entityType;
        }

        $sql .= ' ORDER BY display_name ASC';
        $rows = $this->fetchAll($sql, $params);

        return array_map(
            static fn (array $row): array => [
                'id' => is_numeric($row['id'] ?? null) ? (int) $row['id'] : 0,
                'entity_type' => is_scalar($row['entity_type'] ?? null) ? (string) $row['entity_type'] : '',
                'display_name' => is_scalar($row['display_name'] ?? null) ? (string) $row['display_name'] : '',
                'short_code' => is_scalar($row['short_code'] ?? null) ? (string) $row['short_code'] : null,
            ],
            $rows,
        );
    }
}
