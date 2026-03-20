<?php declare(strict_types=1);

return new class {
    public function up(\PDO $pdo): void
    {
        // ── Match Groups ──────────────────────────────────────────────────────
        // Logical groups within a competition (e.g. "Groep A", "Groep B").
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS match_groups (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                competition_id  INT UNSIGNED   NOT NULL,
                name            VARCHAR(100)   NOT NULL,
                display_order   SMALLINT       NOT NULL DEFAULT 0,
                created_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_match_groups_competition (competition_id),
                CONSTRAINT fk_match_groups_competition
                    FOREIGN KEY (competition_id) REFERENCES competitions (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );

        // ── Venues ────────────────────────────────────────────────────────────
        // Stadiums / venues where matches are played.
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS venues (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                competition_id  INT UNSIGNED   NULL,
                name            VARCHAR(200)   NOT NULL,
                city            VARCHAR(100)   NOT NULL DEFAULT '',
                country         VARCHAR(100)   NOT NULL DEFAULT '',
                created_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_venues_competition (competition_id),
                CONSTRAINT fk_venues_competition
                    FOREIGN KEY (competition_id) REFERENCES competitions (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS venues');
        $pdo->exec('DROP TABLE IF EXISTS match_groups');
    }
};
