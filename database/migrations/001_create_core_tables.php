<?php declare(strict_types=1);

return new class {
    public function up(\PDO $pdo): void
    {
        // ── Users ─────────────────────────────────────────────────────────────
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS users (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                first_name      VARCHAR(100)  NOT NULL,
                last_name       VARCHAR(100)  NOT NULL,
                email           VARCHAR(254)  NOT NULL,
                phone_number    VARCHAR(30)   NOT NULL DEFAULT '',
                password_hash   VARCHAR(255)  NOT NULL,
                role            ENUM('admin','participant') NOT NULL DEFAULT 'participant',
                is_active       TINYINT(1)    NOT NULL DEFAULT 1,
                last_login_at   TIMESTAMP     NULL,
                created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_users_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );

        // ── Competitions ──────────────────────────────────────────────────────
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS competitions (
                id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name                VARCHAR(200)   NOT NULL,
                slug                VARCHAR(200)   NOT NULL,
                description         TEXT           NOT NULL,
                start_date          DATE           NOT NULL,
                end_date            DATE           NOT NULL,
                submission_deadline DATETIME       NOT NULL,
                entry_fee_amount    DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
                prize_first_percent TINYINT UNSIGNED NOT NULL DEFAULT 60,
                prize_second_percent TINYINT UNSIGNED NOT NULL DEFAULT 30,
                prize_third_percent  TINYINT UNSIGNED NOT NULL DEFAULT 10,
                status              ENUM('draft','active','open','closed','archived') NOT NULL DEFAULT 'draft',
                is_public           TINYINT(1)     NOT NULL DEFAULT 1,
                logo_path           VARCHAR(500)   NULL,
                created_by_user_id  INT UNSIGNED   NOT NULL,
                created_at          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_competitions_slug (slug),
                KEY idx_competitions_status (status),
                CONSTRAINT fk_competitions_created_by
                    FOREIGN KEY (created_by_user_id) REFERENCES users (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );

        // ── Competition Sections ──────────────────────────────────────────────
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS competition_sections (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                competition_id  INT UNSIGNED   NOT NULL,
                section_type    ENUM('group_stage_scores','match_outcomes','cards','knockout','bonus_questions')
                                NOT NULL,
                label           VARCHAR(200)   NOT NULL,
                is_active       TINYINT(1)     NOT NULL DEFAULT 1,
                display_order   SMALLINT       NOT NULL DEFAULT 0,
                created_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_sections_competition_type (competition_id, section_type),
                CONSTRAINT fk_sections_competition
                    FOREIGN KEY (competition_id) REFERENCES competitions (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );

        // ── Competition Rules ─────────────────────────────────────────────────
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS competition_rules (
                id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                competition_id          INT UNSIGNED   NOT NULL,
                competition_section_id  INT UNSIGNED   NOT NULL,
                rule_key                VARCHAR(100)   NOT NULL,
                points_value            SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                rule_config_json        JSON           NULL,
                is_active               TINYINT(1)     NOT NULL DEFAULT 1,
                created_at              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_rules_competition (competition_id),
                CONSTRAINT fk_rules_competition
                    FOREIGN KEY (competition_id) REFERENCES competitions (id) ON DELETE CASCADE,
                CONSTRAINT fk_rules_section
                    FOREIGN KEY (competition_section_id) REFERENCES competition_sections (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );

        // ── Competition Participants ──────────────────────────────────────────
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS competition_participants (
                id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                competition_id       INT UNSIGNED   NOT NULL,
                user_id              INT UNSIGNED   NOT NULL,
                payment_status       ENUM('paid','unpaid') NOT NULL DEFAULT 'unpaid',
                payment_marked_at    TIMESTAMP      NULL,
                joined_at            TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_participants_competition_user (competition_id, user_id),
                CONSTRAINT fk_participants_competition
                    FOREIGN KEY (competition_id) REFERENCES competitions (id) ON DELETE CASCADE,
                CONSTRAINT fk_participants_user
                    FOREIGN KEY (user_id) REFERENCES users (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS competition_participants');
        $pdo->exec('DROP TABLE IF EXISTS competition_rules');
        $pdo->exec('DROP TABLE IF EXISTS competition_sections');
        $pdo->exec('DROP TABLE IF EXISTS competitions');
        $pdo->exec('DROP TABLE IF EXISTS users');
    }
};
