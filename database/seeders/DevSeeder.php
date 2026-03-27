<?php declare(strict_types=1);

namespace Database\Seeders;

use PDO;

final class DevSeeder
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function run(): void
    {
        $this->seedUsers();
        $competitionId = $this->seedCompetition();
        $this->seedParticipantEnrollment($competitionId);
        $this->seedSections($competitionId);
        $entityIds = $this->seedEntities($competitionId);
        $this->seedMatches($competitionId, $entityIds);
        $this->seedBonusQuestions($competitionId);
        $this->seedKnockoutRounds($competitionId, $entityIds);
    }

    private function seedUsers(): void
    {
        // Admin user
        $stmt = $this->pdo->prepare(
            'SELECT id FROM users WHERE email = ? LIMIT 1',
        );
        $stmt->execute(['admin@example.com']);

        if ($stmt->fetchColumn() === false) {
            $this->pdo->prepare(
                'INSERT INTO users (first_name, last_name, email, phone_number, password_hash, role)
                 VALUES (?, ?, ?, ?, ?, ?)',
            )->execute([
                'Admin',
                'Gebruiker',
                'admin@example.com',
                '0600000001',
                password_hash('secret', PASSWORD_BCRYPT, ['cost' => 12]),
                'admin',
            ]);
            echo "  Seeded: admin@example.com (password: secret)\n";
        }

        // Participant user
        $stmt->execute(['deelnemer@example.com']);
        if ($stmt->fetchColumn() === false) {
            $this->pdo->prepare(
                'INSERT INTO users (first_name, last_name, email, phone_number, password_hash, role)
                 VALUES (?, ?, ?, ?, ?, ?)',
            )->execute([
                'Test',
                'Deelnemer',
                'deelnemer@example.com',
                '0600000002',
                password_hash('secret', PASSWORD_BCRYPT, ['cost' => 12]),
                'participant',
            ]);
            echo "  Seeded: deelnemer@example.com (password: secret)\n";
        }
    }

    private function seedCompetition(): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT id FROM competitions WHERE slug = ? LIMIT 1',
        );
        $stmt->execute(['ek-2026']);

        $existingId = $stmt->fetchColumn();

        if ($existingId === false) {
            $adminQuery = $this->pdo->query(
                "SELECT id FROM users WHERE role = 'admin' LIMIT 1",
            );
            $adminId = (int) ($adminQuery !== false ? $adminQuery->fetchColumn() : 0);

            $this->pdo->prepare(
                 'INSERT INTO competitions
                  (name, slug, description, start_date, end_date, submission_deadline,
                   entry_fee_amount, prize_first_percent, prize_second_percent, prize_third_percent,
                   status, is_public, created_by_user_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            )->execute([
                'EK 2026',
                'ek-2026',
                'Europees kampioenschap voetbal 2026 - voorspelcompetitie.',
                '2026-06-01',
                '2026-06-30',
                '2026-12-31 23:59:59',
                10.00,
                60,
                30,
                10,
                'open',
                1,
                $adminId,
            ]);
            echo "  Seeded: competition 'EK 2026' (slug: ek-2026)\n";

            return (int) $this->pdo->lastInsertId();
        }

        $competitionId = (int) $existingId;

        $this->pdo->prepare(
            'UPDATE competitions
             SET status = ?, submission_deadline = ?, start_date = ?, end_date = ?
             WHERE id = ?',
        )->execute([
            'open',
            '2026-12-31 23:59:59',
            '2026-06-01',
            '2026-06-30',
            $competitionId,
        ]);

        return $competitionId;
    }

    private function seedParticipantEnrollment(int $competitionId): void
    {
        $participantId = $this->userIdByEmail('deelnemer@example.com');
        if ($participantId === 0) {
            return;
        }

        $this->pdo->prepare(
            'INSERT INTO competition_participants (competition_id, user_id, payment_status)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE payment_status = VALUES(payment_status)',
        )->execute([$competitionId, $participantId, 'unpaid']);
    }

    private function seedSections(int $competitionId): void
    {
        $sections = [
            ['group_stage_scores', 'Uitslagen', 1],
            ['match_outcomes', 'Winnaar / gelijkspel', 2],
            ['cards', 'Kaarten', 3],
            ['knockout', 'Knock-out', 4],
            ['bonus_questions', 'Bonusvragen', 5],
        ];

        foreach ($sections as [$type, $label, $order]) {
            $this->pdo->prepare(
                'INSERT INTO competition_sections (competition_id, section_type, label, is_active, display_order)
                 VALUES (?, ?, ?, 1, ?)
                 ON DUPLICATE KEY UPDATE label = VALUES(label), is_active = VALUES(is_active), display_order = VALUES(display_order)',
            )->execute([$competitionId, $type, $label, $order]);
        }
    }

    /**
     * @return array<string, int>
     */
    private function seedEntities(int $competitionId): array
    {
        $entities = [
            ['Nederland', 'NED'],
            ['Duitsland', 'GER'],
            ['Spanje', 'ESP'],
            ['Frankrijk', 'FRA'],
        ];

        $ids = [];

        foreach ($entities as [$name, $shortCode]) {
            $stmt = $this->pdo->prepare(
                'SELECT id FROM catalog_entities WHERE competition_id = ? AND display_name = ? LIMIT 1',
            );
            $stmt->execute([$competitionId, $name]);
            $entityId = $stmt->fetchColumn();

            if ($entityId === false) {
                $this->pdo->prepare(
                    'INSERT INTO catalog_entities (competition_id, entity_type, display_name, short_code, is_active)
                     VALUES (?, ?, ?, ?, 1)',
                )->execute([$competitionId, 'country', $name, $shortCode]);
                $entityId = $this->pdo->lastInsertId();
                echo "  Seeded: entity {$name}\n";
            } else {
                $this->pdo->prepare(
                    'UPDATE catalog_entities SET short_code = ?, is_active = 1 WHERE id = ?',
                )->execute([$shortCode, $entityId]);
            }

            $ids[$name] = (int) $entityId;
        }

        return $ids;
    }

    /**
     * @param array<string, int> $entityIds
     */
    private function seedMatches(int $competitionId, array $entityIds): void
    {
        $matches = [
            [$entityIds['Nederland'], $entityIds['Duitsland'], 'group', '2026-06-05 20:00:00'],
            [$entityIds['Spanje'], $entityIds['Frankrijk'], 'group', '2026-06-06 20:00:00'],
        ];

        foreach ($matches as [$homeId, $awayId, $stage, $kickoffAt]) {
            $stmt = $this->pdo->prepare(
                'SELECT id FROM matches
                 WHERE competition_id = ? AND home_entity_id = ? AND away_entity_id = ? AND kickoff_at = ?
                 LIMIT 1',
            );
            $stmt->execute([$competitionId, $homeId, $awayId, $kickoffAt]);

            if ($stmt->fetchColumn() === false) {
                $this->pdo->prepare(
                    'INSERT INTO matches (competition_id, home_entity_id, away_entity_id, stage, kickoff_at, status)
                     VALUES (?, ?, ?, ?, ?, ?)',
                )->execute([$competitionId, $homeId, $awayId, $stage, $kickoffAt, 'scheduled']);
            }
        }
    }

    private function seedBonusQuestions(int $competitionId): void
    {
        $this->pdo->prepare(
            'UPDATE bonus_questions SET is_active = 0 WHERE competition_id = ?',
        )->execute([$competitionId]);

        $questions = [
            ['Welk land wint het toernooi?', 'entity', 'country', 1],
            ['Hoeveel goals vallen er in de finale?', 'numeric', null, 2],
            ['Welke speler wordt topscorer?', 'text', null, 3],
        ];

        foreach ($questions as [$prompt, $type, $constraint, $order]) {
            $stmt = $this->pdo->prepare(
                'SELECT id FROM bonus_questions WHERE competition_id = ? AND prompt = ? LIMIT 1',
            );
            $stmt->execute([$competitionId, $prompt]);

            if ($stmt->fetchColumn() === false) {
                $this->pdo->prepare(
                    'INSERT INTO bonus_questions
                     (competition_id, prompt, question_type, entity_type_constraint, is_active, display_order)
                     VALUES (?, ?, ?, ?, 1, ?)',
                )->execute([$competitionId, $prompt, $type, $constraint, $order]);
            } else {
                $this->pdo->prepare(
                    'UPDATE bonus_questions
                     SET question_type = ?, entity_type_constraint = ?, is_active = 1, display_order = ?
                     WHERE competition_id = ? AND prompt = ?',
                )->execute([$type, $constraint, $order, $competitionId, $prompt]);
            }
        }
    }

    /**
     * @param array<string, int> $entityIds
     */
    private function seedKnockoutRounds(int $competitionId, array $entityIds): void
    {
        $stmt = $this->pdo->prepare(
            'SELECT id FROM knockout_rounds WHERE competition_id = ? AND round_order = 1 LIMIT 1',
        );
        $stmt->execute([$competitionId]);
        $roundId = $stmt->fetchColumn();

        if ($roundId === false) {
            $this->pdo->prepare(
                'INSERT INTO knockout_rounds (competition_id, label, round_order, team_slot_count, is_active)
                 VALUES (?, ?, ?, ?, 1)',
            )->execute([$competitionId, 'Finale', 1, 2]);
            $roundId = $this->pdo->lastInsertId();
        } else {
            $this->pdo->prepare(
                'UPDATE knockout_rounds SET label = ?, team_slot_count = ?, is_active = 1 WHERE id = ?',
            )->execute(['Finale', 2, $roundId]);
        }

        $slots = [
            1 => $entityIds['Nederland'],
            2 => $entityIds['Spanje'],
        ];

        foreach ($slots as $slot => $entityId) {
            $this->pdo->prepare(
                'INSERT INTO knockout_round_teams (knockout_round_id, catalog_entity_id, slot_number)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE catalog_entity_id = VALUES(catalog_entity_id)',
            )->execute([(int) $roundId, $entityId, $slot]);
        }
    }

    private function userIdByEmail(string $email): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);

        $userId = $stmt->fetchColumn();

        return is_numeric($userId) ? (int) $userId : 0;
    }
}
