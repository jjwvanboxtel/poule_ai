<?php declare(strict_types=1);

namespace App\Application\Imports\Csv;

use App\Support\Validation\ValidationException;

final class EntityCsvParser
{
    private const REQUIRED_HEADERS = ['display_name', 'entity_type'];

    /**
     * Parse a CSV string into an array of entity rows.
     *
     * @return list<array{display_name: string, entity_type: string, short_code: string|null, nationality: string|null, is_active: bool}>
     * @throws ValidationException if the CSV cannot be parsed or headers are missing.
     */
    public function parse(string $csvContent): array
    {
        if (trim($csvContent) === '') {
            throw ValidationException::withMessages(['file' => 'Het CSV-bestand is leeg.']);
        }

        $lines = explode("\n", str_replace("\r\n", "\n", $csvContent));
        $lines = array_filter($lines, static fn (string $l): bool => trim($l) !== '');
        $lines = array_values($lines);

        if (count($lines) < 2) {
            throw ValidationException::withMessages(['file' => 'Het CSV-bestand bevat geen gegevensrijen.']);
        }

        $rawHeaders = str_getcsv(array_shift($lines));
        $headers = array_map(static fn (string|null $h): string => strtolower(trim((string) $h)), $rawHeaders);

        foreach (self::REQUIRED_HEADERS as $required) {
            if (!in_array($required, $headers, true)) {
                throw ValidationException::withMessages([
                    'file' => "Verplichte kolom '{$required}' ontbreekt in het CSV-bestand.",
                ]);
            }
        }

        $rows = [];
        foreach ($lines as $line) {
            $cols = str_getcsv($line);

            // Skip completely empty lines
            if (count(array_filter($cols)) === 0) {
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = trim($cols[$index] ?? '');
            }

            $rows[] = [
                'display_name' => $row['display_name'] ?? '',
                'entity_type' => $row['entity_type'] ?? '',
                'short_code' => isset($row['short_code']) && $row['short_code'] !== '' ? $row['short_code'] : null,
                'nationality' => isset($row['nationality']) && $row['nationality'] !== '' ? $row['nationality'] : null,
                'is_active' => isset($row['is_active'])
                    ? !in_array(strtolower($row['is_active']), ['0', 'false', 'no', 'nee', ''], true)
                    : true,
            ];
        }

        return $rows;
    }
}
