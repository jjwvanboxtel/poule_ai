<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Domain\Competition\MatchGroup;
use App\Domain\Competition\MatchVenue;

final class PdoMatchManagementRepository extends AbstractPdoRepository
{
    /** @return list<array<string, mixed>> */
    public function findAllForCompetition(int $competitionId): array
    {
        return $this->fetchAll(
            'SELECT m.*, home.display_name AS home_label, away.display_name AS away_label,
                    mg.name AS group_name, mv.name AS venue_name, mv.city AS venue_city
             FROM matches m
             JOIN catalog_entities home ON home.id = m.home_entity_id
             JOIN catalog_entities away ON away.id = m.away_entity_id
             LEFT JOIN match_groups mg ON mg.id = m.group_id
             LEFT JOIN match_venues mv ON mv.id = m.venue_id
             WHERE m.competition_id = ?
             ORDER BY m.kickoff_at ASC, m.id ASC',
            [$competitionId],
        );
    }

    /** @return array<string, mixed>|null */
    public function findMatchById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT m.*, home.display_name AS home_label, away.display_name AS away_label
             FROM matches m
             JOIN catalog_entities home ON home.id = m.home_entity_id
             JOIN catalog_entities away ON away.id = m.away_entity_id
             WHERE m.id = ? LIMIT 1',
            [$id],
        );
    }

    public function createMatch(int $competitionId, int $homeEntityId, int $awayEntityId, string $stage, string $kickoffAt, ?int $groupId, ?int $venueId): int
    {
        $this->execute(
            'INSERT INTO matches (competition_id, home_entity_id, away_entity_id, stage, kickoff_at, group_id, venue_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$competitionId, $homeEntityId, $awayEntityId, $stage, $kickoffAt, $groupId, $venueId],
        );
        return $this->lastInsertId();
    }

    public function updateMatch(int $id, int $homeEntityId, int $awayEntityId, string $stage, string $kickoffAt, ?int $groupId, ?int $venueId): void
    {
        $this->execute(
            'UPDATE matches SET home_entity_id = ?, away_entity_id = ?, stage = ?, kickoff_at = ?, group_id = ?, venue_id = ? WHERE id = ?',
            [$homeEntityId, $awayEntityId, $stage, $kickoffAt, $groupId, $venueId, $id],
        );
    }

    public function deleteMatch(int $id): void
    {
        $this->execute('DELETE FROM matches WHERE id = ?', [$id]);
    }

    /** @return list<MatchGroup> */
    public function findGroups(int $competitionId): array
    {
        return array_map(
            MatchGroup::fromArray(...),
            $this->fetchAll(
                'SELECT * FROM match_groups WHERE competition_id = ? ORDER BY display_order ASC, name ASC',
                [$competitionId],
            ),
        );
    }

    public function createGroup(int $competitionId, string $name, int $displayOrder): int
    {
        $this->execute(
            'INSERT INTO match_groups (competition_id, name, display_order) VALUES (?, ?, ?)',
            [$competitionId, $name, $displayOrder],
        );
        return $this->lastInsertId();
    }

    public function deleteGroup(int $id): void
    {
        $this->execute('DELETE FROM match_groups WHERE id = ?', [$id]);
    }

    /** @return list<MatchVenue> */
    public function findVenues(int $competitionId): array
    {
        return array_map(
            MatchVenue::fromArray(...),
            $this->fetchAll(
                'SELECT * FROM match_venues WHERE competition_id = ? ORDER BY name ASC',
                [$competitionId],
            ),
        );
    }

    public function createVenue(int $competitionId, string $name, string $city): int
    {
        $this->execute(
            'INSERT INTO match_venues (competition_id, name, city) VALUES (?, ?, ?)',
            [$competitionId, $name, $city],
        );
        return $this->lastInsertId();
    }

    public function deleteVenue(int $id): void
    {
        $this->execute('DELETE FROM match_venues WHERE id = ?', [$id]);
    }
}
