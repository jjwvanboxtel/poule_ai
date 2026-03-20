<?php declare(strict_types=1);

namespace App\Application\Imports\Csv;

use App\Support\Validation\ValidationException;

final class EntityCsvValidator
{
    private const VALID_ENTITY_TYPES = ['country', 'team', 'player', 'referee', 'coach', 'other'];
    private const MAX_DISPLAY_NAME_LENGTH = 200;
    private const MAX_SHORT_CODE_LENGTH = 20;

    /**
     * Validate a parsed list of entity rows.
     *
     * @param list<array{display_name: string, entity_type: string, short_code: string|null, nationality: string|null, is_active: bool}> $rows
     * @throws ValidationException if any row fails validation.
     */
    public function validate(array $rows): void
    {
        if ($rows === []) {
            throw ValidationException::withMessages(['file' => 'Het CSV-bestand bevat geen geldige rijen.']);
        }

        $errors = [];
        foreach ($rows as $i => $row) {
            $lineNum = $i + 2; // +2 because line 1 is the header

            if ($row['display_name'] === '') {
                $errors["row_{$lineNum}"] = "Rij {$lineNum}: 'display_name' mag niet leeg zijn.";
                continue;
            }

            if (strlen($row['display_name']) > self::MAX_DISPLAY_NAME_LENGTH) {
                $errors["row_{$lineNum}"] = "Rij {$lineNum}: 'display_name' is te lang (max " . self::MAX_DISPLAY_NAME_LENGTH . " tekens).";
                continue;
            }

            if (!in_array($row['entity_type'], self::VALID_ENTITY_TYPES, true)) {
                $valid = implode(', ', self::VALID_ENTITY_TYPES);
                $errors["row_{$lineNum}"] = "Rij {$lineNum}: ongeldig 'entity_type' '{$row['entity_type']}'. Toegestaan: {$valid}.";
                continue;
            }

            if ($row['short_code'] !== null && strlen($row['short_code']) > self::MAX_SHORT_CODE_LENGTH) {
                $errors["row_{$lineNum}"] = "Rij {$lineNum}: 'short_code' is te lang (max " . self::MAX_SHORT_CODE_LENGTH . " tekens).";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
