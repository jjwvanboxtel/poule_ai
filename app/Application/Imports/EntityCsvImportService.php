<?php declare(strict_types=1);

namespace App\Application\Imports;

use App\Application\Imports\Csv\EntityCsvParser;
use App\Application\Imports\Csv\EntityCsvValidator;

final class EntityCsvImportService
{
    private readonly EntityCsvParser $parser;
    private readonly EntityCsvValidator $validator;

    public function __construct(private readonly \PDO $pdo)
    {
        $this->parser = new EntityCsvParser();
        $this->validator = new EntityCsvValidator();
    }

    public function import(string $filePath, int $competitionId): int
    {
        $rows = $this->parser->parse($filePath);
        $count = 0;

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO catalog_entities (competition_id, entity_type, display_name, short_code, nationality, is_active)
                 VALUES (?, ?, ?, ?, ?, ?)',
            );
            foreach ($rows as $row) {
                $result = $this->validator->validate($row);
                if (!$result['valid']) {
                    continue;
                }

                $entityType = is_string($row['entity_type'] ?? null) ? $row['entity_type'] : 'other';
                $displayName = is_string($row['display_name'] ?? null) ? trim($row['display_name']) : '';
                $shortCode = isset($row['short_code']) && $row['short_code'] !== '' ? $row['short_code'] : null;
                $nationality = isset($row['nationality']) && $row['nationality'] !== '' ? $row['nationality'] : null;
                $isActive = !isset($row['is_active']) || $row['is_active'] === '' || $row['is_active'] === '1' ? 1 : 0;

                $stmt->execute([$competitionId, $entityType, $displayName, $shortCode, $nationality, $isActive]);
                $count++;
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        return $count;
    }
}
