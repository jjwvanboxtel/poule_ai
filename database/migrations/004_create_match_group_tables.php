<?php declare(strict_types=1);

return new class {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS match_groups (
                id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                competition_id INT UNSIGNED NOT NULL,
                name           VARCHAR(50) NOT NULL,
                display_order  SMALLINT NOT NULL DEFAULT 0,
                created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_match_groups_competition_name (competition_id, name),
                CONSTRAINT fk_match_groups_competition
                    FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS match_venues (
                id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                competition_id INT UNSIGNED NOT NULL,
                name           VARCHAR(200) NOT NULL,
                city           VARCHAR(100) NOT NULL DEFAULT '',
                created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_match_venues_competition
                    FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        // Add columns to matches if they don't exist
        try {
            $pdo->exec('ALTER TABLE matches ADD COLUMN IF NOT EXISTS group_id INT UNSIGNED NULL AFTER competition_id');
        } catch (\PDOException $e) {
            // Column may already exist
        }
        try {
            $pdo->exec('ALTER TABLE matches ADD COLUMN IF NOT EXISTS venue_id INT UNSIGNED NULL AFTER group_id');
        } catch (\PDOException $e) {
            // Column may already exist
        }
        try {
            $pdo->exec('ALTER TABLE matches ADD CONSTRAINT fk_matches_group_id FOREIGN KEY (group_id) REFERENCES match_groups(id) ON DELETE SET NULL');
        } catch (\PDOException $e) {
            // Constraint may already exist
        }
        try {
            $pdo->exec('ALTER TABLE matches ADD CONSTRAINT fk_matches_venue_id FOREIGN KEY (venue_id) REFERENCES match_venues(id) ON DELETE SET NULL');
        } catch (\PDOException $e) {
            // Constraint may already exist
        }
    }

    public function down(\PDO $pdo): void
    {
        try {
            $pdo->exec('ALTER TABLE matches DROP FOREIGN KEY fk_matches_group_id');
        } catch (\PDOException $e) {
        }
        try {
            $pdo->exec('ALTER TABLE matches DROP FOREIGN KEY fk_matches_venue_id');
        } catch (\PDOException $e) {
        }
        try {
            $pdo->exec('ALTER TABLE matches DROP COLUMN IF EXISTS group_id');
        } catch (\PDOException $e) {
        }
        try {
            $pdo->exec('ALTER TABLE matches DROP COLUMN IF EXISTS venue_id');
        } catch (\PDOException $e) {
        }
        $pdo->exec('DROP TABLE IF EXISTS match_venues');
        $pdo->exec('DROP TABLE IF EXISTS match_groups');
    }
};
