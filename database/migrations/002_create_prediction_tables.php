<?php declare(strict_types=1);

return new class {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS catalog_entities (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                competition_id  INT UNSIGNED NULL,
                entity_type     ENUM('country','team','player','referee','coach','other') NOT NULL,
                display_name    VARCHAR(200) NOT NULL,
                short_code      VARCHAR(20) NULL,
                nationality     VARCHAR(100) NULL,
                is_active       TINYINT(1) NOT NULL DEFAULT 1,
                metadata_json   JSON NULL,
                created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_catalog_competition (competition_id),
                KEY idx_catalog_type_active (entity_type, is_active),
                CONSTRAINT fk_catalog_entities_competition
                    FOREIGN KEY (competition_id) REFERENCES competitions (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS matches (
                id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                competition_id   INT UNSIGNED NOT NULL,
                home_entity_id   INT UNSIGNED NOT NULL,
                away_entity_id   INT UNSIGNED NOT NULL,
                stage            ENUM('group','round_of_16','quarter_final','semi_final','final','other') NOT NULL DEFAULT 'group',
                kickoff_at       DATETIME NOT NULL,
                status           ENUM('scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'scheduled',
                created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_matches_competition (competition_id),
                CONSTRAINT fk_matches_competition
                    FOREIGN KEY (competition_id) REFERENCES competitions (id) ON DELETE CASCADE,
                CONSTRAINT fk_matches_home_entity
                    FOREIGN KEY (home_entity_id) REFERENCES catalog_entities (id),
                CONSTRAINT fk_matches_away_entity
                    FOREIGN KEY (away_entity_id) REFERENCES catalog_entities (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS bonus_questions (
                id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                competition_id          INT UNSIGNED NOT NULL,
                prompt                  VARCHAR(255) NOT NULL,
                question_type           ENUM('entity','numeric','text') NOT NULL,
                entity_type_constraint  VARCHAR(50) NULL,
                is_active               TINYINT(1) NOT NULL DEFAULT 1,
                display_order           SMALLINT NOT NULL DEFAULT 0,
                answer_validation_json  JSON NULL,
                created_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_bonus_questions_competition (competition_id),
                CONSTRAINT fk_bonus_questions_competition
                    FOREIGN KEY (competition_id) REFERENCES competitions (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS knockout_rounds (
                id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                competition_id   INT UNSIGNED NOT NULL,
                label            VARCHAR(150) NOT NULL,
                round_order      SMALLINT NOT NULL,
                team_slot_count  SMALLINT NOT NULL,
                is_active        TINYINT(1) NOT NULL DEFAULT 1,
                created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_knockout_rounds_competition_order (competition_id, round_order),
                CONSTRAINT fk_knockout_rounds_competition
                    FOREIGN KEY (competition_id) REFERENCES competitions (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS knockout_round_teams (
                id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                knockout_round_id  INT UNSIGNED NOT NULL,
                catalog_entity_id  INT UNSIGNED NOT NULL,
                slot_number        SMALLINT NOT NULL,
                created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_knockout_round_slot (knockout_round_id, slot_number),
                CONSTRAINT fk_knockout_round_teams_round
                    FOREIGN KEY (knockout_round_id) REFERENCES knockout_rounds (id) ON DELETE CASCADE,
                CONSTRAINT fk_knockout_round_teams_entity
                    FOREIGN KEY (catalog_entity_id) REFERENCES catalog_entities (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS prediction_submissions (
                id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                competition_id   INT UNSIGNED NOT NULL,
                user_id          INT UNSIGNED NOT NULL,
                submitted_at     DATETIME NOT NULL,
                submission_hash  CHAR(64) NOT NULL,
                is_locked        TINYINT(1) NOT NULL DEFAULT 1,
                created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_prediction_submissions_competition_user (competition_id, user_id),
                KEY idx_prediction_submissions_user (user_id),
                CONSTRAINT fk_prediction_submissions_competition
                    FOREIGN KEY (competition_id) REFERENCES competitions (id) ON DELETE CASCADE,
                CONSTRAINT fk_prediction_submissions_user
                    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS match_predictions (
                id                                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                prediction_submission_id           INT UNSIGNED NOT NULL,
                match_id                           INT UNSIGNED NOT NULL,
                predicted_home_score               SMALLINT NULL,
                predicted_away_score               SMALLINT NULL,
                predicted_outcome                  ENUM('home_win','draw','away_win') NULL,
                predicted_yellow_cards_home        SMALLINT NULL,
                predicted_yellow_cards_away        SMALLINT NULL,
                predicted_red_cards_home           SMALLINT NULL,
                predicted_red_cards_away           SMALLINT NULL,
                predicted_knockout_winner_entity_id INT UNSIGNED NULL,
                created_at                         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at                         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_match_predictions_submission_match (prediction_submission_id, match_id),
                CONSTRAINT fk_match_predictions_submission
                    FOREIGN KEY (prediction_submission_id) REFERENCES prediction_submissions (id) ON DELETE CASCADE,
                CONSTRAINT fk_match_predictions_match
                    FOREIGN KEY (match_id) REFERENCES matches (id) ON DELETE CASCADE,
                CONSTRAINT fk_match_predictions_knockout_entity
                    FOREIGN KEY (predicted_knockout_winner_entity_id) REFERENCES catalog_entities (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS knockout_round_predictions (
                id                        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                prediction_submission_id  INT UNSIGNED NOT NULL,
                knockout_round_id         INT UNSIGNED NOT NULL,
                catalog_entity_id         INT UNSIGNED NOT NULL,
                slot_number               SMALLINT NOT NULL,
                created_at                TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at                TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_knockout_predictions_round_slot (prediction_submission_id, knockout_round_id, slot_number),
                CONSTRAINT fk_knockout_predictions_submission
                    FOREIGN KEY (prediction_submission_id) REFERENCES prediction_submissions (id) ON DELETE CASCADE,
                CONSTRAINT fk_knockout_predictions_round
                    FOREIGN KEY (knockout_round_id) REFERENCES knockout_rounds (id) ON DELETE CASCADE,
                CONSTRAINT fk_knockout_predictions_entity
                    FOREIGN KEY (catalog_entity_id) REFERENCES catalog_entities (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS bonus_answers (
                id                        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                prediction_submission_id  INT UNSIGNED NOT NULL,
                bonus_question_id         INT UNSIGNED NOT NULL,
                answer_text               TEXT NULL,
                answer_number             DECIMAL(10,2) NULL,
                answer_entity_id          INT UNSIGNED NULL,
                created_at                TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at                TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_bonus_answers_submission_question (prediction_submission_id, bonus_question_id),
                CONSTRAINT fk_bonus_answers_submission
                    FOREIGN KEY (prediction_submission_id) REFERENCES prediction_submissions (id) ON DELETE CASCADE,
                CONSTRAINT fk_bonus_answers_question
                    FOREIGN KEY (bonus_question_id) REFERENCES bonus_questions (id) ON DELETE CASCADE,
                CONSTRAINT fk_bonus_answers_entity
                    FOREIGN KEY (answer_entity_id) REFERENCES catalog_entities (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS bonus_answers');
        $pdo->exec('DROP TABLE IF EXISTS knockout_round_predictions');
        $pdo->exec('DROP TABLE IF EXISTS match_predictions');
        $pdo->exec('DROP TABLE IF EXISTS prediction_submissions');
        $pdo->exec('DROP TABLE IF EXISTS knockout_round_teams');
        $pdo->exec('DROP TABLE IF EXISTS knockout_rounds');
        $pdo->exec('DROP TABLE IF EXISTS bonus_questions');
        $pdo->exec('DROP TABLE IF EXISTS matches');
        $pdo->exec('DROP TABLE IF EXISTS catalog_entities');
    }
};
