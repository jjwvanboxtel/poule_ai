<?php declare(strict_types=1);

return new class {
    public function up(\PDO $pdo): void
    {
        // ── Match Results ─────────────────────────────────────────────────────
        // Stores the actual final result for each match, used when calculating scores.
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS match_results (
                id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                match_id            INT UNSIGNED     NOT NULL,
                home_score          TINYINT UNSIGNED NOT NULL DEFAULT 0,
                away_score          TINYINT UNSIGNED NOT NULL DEFAULT 0,
                outcome             ENUM('home_win','draw','away_win') NOT NULL,
                yellow_cards_home   TINYINT UNSIGNED NOT NULL DEFAULT 0,
                yellow_cards_away   TINYINT UNSIGNED NOT NULL DEFAULT 0,
                red_cards_home      TINYINT UNSIGNED NOT NULL DEFAULT 0,
                red_cards_away      TINYINT UNSIGNED NOT NULL DEFAULT 0,
                recorded_at         DATETIME         NOT NULL,
                created_at          TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at          TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_match_results_match (match_id),
                CONSTRAINT fk_match_results_match
                    FOREIGN KEY (match_id) REFERENCES matches (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );

        // ── Entity Import Batches ─────────────────────────────────────────────
        // Tracks CSV import operations for the entity catalog.
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS entity_import_batches (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                competition_id  INT UNSIGNED   NULL,
                imported_by     INT UNSIGNED   NOT NULL,
                file_name       VARCHAR(255)   NOT NULL,
                row_count       INT UNSIGNED   NOT NULL DEFAULT 0,
                error_count     INT UNSIGNED   NOT NULL DEFAULT 0,
                status          ENUM('pending','completed','failed') NOT NULL DEFAULT 'completed',
                created_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_import_batches_competition (competition_id),
                CONSTRAINT fk_import_batches_competition
                    FOREIGN KEY (competition_id) REFERENCES competitions (id) ON DELETE CASCADE,
                CONSTRAINT fk_import_batches_user
                    FOREIGN KEY (imported_by) REFERENCES users (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS entity_import_batches');
        $pdo->exec('DROP TABLE IF EXISTS match_results');
    }
};
