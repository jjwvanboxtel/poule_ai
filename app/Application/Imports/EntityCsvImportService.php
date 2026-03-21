<?php declare(strict_types=1);

namespace App\Application\Imports;

use App\Application\Imports\Csv\EntityCsvParser;
use App\Application\Imports\Csv\EntityCsvValidator;
use App\Support\Validation\ValidationException;
use PDO;

final class EntityCsvImportService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly EntityCsvParser $parser,
        private readonly EntityCsvValidator $validator,
    ) {
    }

    /**
     * Import entities from CSV content into the catalog.
     *
     * Returns the number of rows imported.
     *
     * @throws ValidationException if parsing or validation fails.
     */
    public function import(int $competitionId, string $csvContent, int $importedByUserId): int
    {
        $rows = $this->parser->parse($csvContent);
        $this->validator->validate($rows);

        $this->pdo->beginTransaction();

        try {
            $count = 0;

            foreach ($rows as $row) {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO catalog_entities
                         (competition_id, entity_type, display_name, short_code, nationality, is_active)
                     VALUES (?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                         entity_type  = VALUES(entity_type),
                         short_code   = VALUES(short_code),
                         nationality  = VALUES(nationality),
                         is_active    = VALUES(is_active)',
                );
                $stmt->execute([
                    $competitionId > 0 ? $competitionId : null,
                    $row['entity_type'],
                    $row['display_name'],
                    $row['short_code'],
                    $row['nationality'],
                    $row['is_active'] ? 1 : 0,
                ]);
                $count++;
            }

            // Record the batch
            $batchStmt = $this->pdo->prepare(
                'INSERT INTO entity_import_batches (competition_id, imported_by, file_name, row_count, error_count, status)
                 VALUES (?, ?, ?, ?, 0, ?)',
            );
            $batchStmt->execute([
                $competitionId > 0 ? $competitionId : null,
                $importedByUserId,
                'upload_' . date('YmdHis') . '.csv',
                $count,
                'completed',
            ]);

            $this->pdo->commit();

            return $count;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
