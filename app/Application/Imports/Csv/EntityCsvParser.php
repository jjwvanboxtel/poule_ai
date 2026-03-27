<?php declare(strict_types=1);

namespace App\Application\Imports\Csv;

final class EntityCsvParser
{
    /** @return list<array<string, string>> */
    public function parse(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Cannot open file: {$filePath}");
        }

        $rows = [];
        $headers = null;

        while (($line = fgetcsv($handle)) !== false) {
            if (!is_array($line)) {
                continue;
            }
            if ($headers === null) {
                $headers = array_map('trim', $line);
                continue;
            }
            if ($headers === []) {
                continue;
            }
            $assoc = [];
            foreach ($headers as $i => $key) {
                $assoc[$key] = isset($line[$i]) && is_string($line[$i]) ? trim($line[$i]) : '';
            }
            $rows[] = $assoc;
        }

        fclose($handle);
        return $rows;
    }
}
