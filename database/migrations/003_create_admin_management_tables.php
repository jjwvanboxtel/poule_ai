<?php declare(strict_types=1);

return new class {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS admin_audit_logs (
                id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id     INT UNSIGNED NOT NULL,
                action      VARCHAR(100) NOT NULL,
                entity_type VARCHAR(100) NOT NULL,
                entity_id   INT UNSIGNED NULL,
                details_json JSON NULL,
                created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_audit_logs_user
                    FOREIGN KEY (user_id) REFERENCES users(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS match_results (
                id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                match_id            INT UNSIGNED NOT NULL UNIQUE,
                home_score          TINYINT UNSIGNED NULL,
                away_score          TINYINT UNSIGNED NULL,
                result_outcome      ENUM('home_win','draw','away_win') NULL,
                yellow_cards_home   TINYINT UNSIGNED NOT NULL DEFAULT 0,
                yellow_cards_away   TINYINT UNSIGNED NOT NULL DEFAULT 0,
                red_cards_home      TINYINT UNSIGNED NOT NULL DEFAULT 0,
                red_cards_away      TINYINT UNSIGNED NOT NULL DEFAULT 0,
                recorded_at         TIMESTAMP NULL,
                created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_match_results_match
                    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS match_results');
        $pdo->exec('DROP TABLE IF EXISTS admin_audit_logs');
    }
};
