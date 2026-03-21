<?php declare(strict_types=1);

return new class {
    public function up(\PDO $pdo): void
    {
        // ── Admin Audit Logs ──────────────────────────────────────────────────
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS admin_audit_logs (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id         INT UNSIGNED NOT NULL,
                action          VARCHAR(100)   NOT NULL,
                entity_type     VARCHAR(100)   NOT NULL,
                entity_id       INT UNSIGNED   NULL,
                old_value_json  JSON           NULL,
                new_value_json  JSON           NULL,
                ip_address      VARCHAR(45)    NOT NULL DEFAULT '',
                created_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_audit_user   (user_id),
                KEY idx_audit_entity (entity_type, entity_id),
                CONSTRAINT fk_audit_user
                    FOREIGN KEY (user_id) REFERENCES users (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS admin_audit_logs');
    }
};
