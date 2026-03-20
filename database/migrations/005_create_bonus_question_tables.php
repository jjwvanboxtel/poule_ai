<?php declare(strict_types=1);

return new class {
    public function up(\PDO $pdo): void
    {
        // ── Bonus Question Options ─────────────────────────────────────────────
        // Pre-defined answer options for entity-backed bonus questions with dropdowns.
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS bonus_question_options (
                id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                bonus_question_id   INT UNSIGNED   NOT NULL,
                catalog_entity_id   INT UNSIGNED   NULL,
                option_label        VARCHAR(200)   NOT NULL,
                display_order       SMALLINT       NOT NULL DEFAULT 0,
                is_active           TINYINT(1)     NOT NULL DEFAULT 1,
                created_at          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_bonus_options_question (bonus_question_id),
                CONSTRAINT fk_bonus_options_question
                    FOREIGN KEY (bonus_question_id) REFERENCES bonus_questions (id) ON DELETE CASCADE,
                CONSTRAINT fk_bonus_options_entity
                    FOREIGN KEY (catalog_entity_id) REFERENCES catalog_entities (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS bonus_question_options');
    }
};
