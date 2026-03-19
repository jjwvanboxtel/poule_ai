<?php declare(strict_types=1);

namespace App\Application\Imports\Csv;

final class EntityCsvValidator
{
    /**
     * @param array<string, mixed> $row
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate(array $row): array
    {
        $errors = [];

        $entityType = is_string($row['entity_type'] ?? null) ? trim($row['entity_type']) : '';
        $displayName = is_string($row['display_name'] ?? null) ? trim($row['display_name']) : '';

        if ($entityType === '') {
            $errors[] = 'entity_type is required';
        }
        if ($displayName === '') {
            $errors[] = 'display_name is required';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}
